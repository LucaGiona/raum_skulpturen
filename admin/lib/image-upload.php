<?php
declare(strict_types=1);

const MAX_FILE_SIZE_BYTES = 20 * 1024 * 1024;
const MAX_IMAGE_SIDE = 2560;
const MAX_IMAGE_PIXELS = 60_000_000;

function upload_ini_bytes(string $value): int
{
    $value = trim($value);
    return (int) ((float) $value * match (strtolower(substr($value, -1))) {
        'g' => 1024 ** 3, 'm' => 1024 ** 2, 'k' => 1024, default => 1,
    });
}

/** Prüft Dateityp, Abmessungen und Speicherbedarf vor dem vollständigen Dekodieren. */
function inspect_upload_image(string $path): array
{
    $size = filesize($path);
    if ($size === false || $size < 1 || $size > MAX_FILE_SIZE_BYTES) {
        throw new RuntimeException('Die Datei ist leer oder zu groß (maximal 20 MB).');
    }
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($path);
    if (in_array($mime, ['image/heic', 'image/heif', 'image/heic-sequence', 'image/heif-sequence'], true)) {
        throw new RuntimeException('HEIC/HEIF wird von der vorhandenen Bildverarbeitung nicht unterstützt. Bitte das Foto als JPG exportieren und erneut hochladen.');
    }
    $formats = ['image/jpeg' => ['jpg', IMAGETYPE_JPEG, 'jpeg'], 'image/png' => ['png', IMAGETYPE_PNG, 'png'], 'image/webp' => ['webp', IMAGETYPE_WEBP, 'webp']];
    if (!isset($formats[$mime])) {
        throw new RuntimeException('Ungültiges Bildformat. Erlaubt sind JPG, PNG und WebP. HEIC/HEIF bitte als JPG exportieren.');
    }
    [$extension, $type, $codec] = $formats[$mime];
    $info = @getimagesize($path);
    if ($info === false || $info[2] !== $type || $info[0] < 1 || $info[1] < 1) {
        throw new RuntimeException('Die Datei ist kein gültiges Bild oder ist beschädigt.');
    }
    if (!function_exists('imagecreatefrom' . $codec) || !function_exists('image' . $codec)) {
        throw new RuntimeException('Dieses Bildformat wird auf dem Server nicht unterstützt. Bitte GD mit JPG-, PNG- und WebP-Unterstützung aktivieren lassen.');
    }
    if ($mime === 'image/jpeg' && !function_exists('exif_read_data')) {
        throw new RuntimeException('Die EXIF-Erweiterung fehlt auf dem Server. Bitte aktivieren lassen, damit Handyfotos richtig ausgerichtet werden.');
    }
    $pixels = (float) $info[0] * $info[1];
    // Reserve für Dekoder, Quellbild, EXIF-Rotation und Zielbild; zusätzlich harte Grenzen.
    $required = $pixels * 12 + MAX_IMAGE_SIDE ** 2 * 8 + $size * 2 + 16 * 1024 ** 2;
    $limit = upload_ini_bytes((string) ini_get('memory_limit'));
    if ($pixels > MAX_IMAGE_PIXELS || max($info[0], $info[1]) > 20000
        || ($limit > 0 && $required > $limit - memory_get_usage(true))) {
        throw new RuntimeException('Das Foto hat für den verfügbaren Server-Arbeitsspeicher zu große Pixelmaße. Bitte eine kleinere Bildversion verwenden oder das PHP-Speicherlimit erhöhen lassen.');
    }
    return ['extension' => $extension, 'codec' => $codec, 'width' => $info[0], 'height' => $info[1]];
}

function optimize_upload_image(string $sourcePath, string $targetPath, array $info): void
{
    $source = $target = null;
    try {
        $decoder = 'imagecreatefrom' . $info['codec'];
        $source = @$decoder($sourcePath);
        if ($source === false) {
            throw new RuntimeException('Das Foto ist beschädigt oder konnte nicht gelesen werden.');
        }
        $orientation = 1;
        if ($info['codec'] === 'jpeg') {
            $exif = @exif_read_data($sourcePath, 'IFD0', true, false);
            $orientation = (int) ($exif['IFD0']['Orientation'] ?? 1);
        }
        if (in_array($orientation, [2, 4, 5, 7], true)) {
            imageflip($source, in_array($orientation, [4, 5], true) ? IMG_FLIP_VERTICAL : IMG_FLIP_HORIZONTAL);
        }
        $angle = match ($orientation) { 3 => 180, 5, 6, 7 => -90, 8 => 90, default => 0 };
        if ($angle !== 0) {
            $rotated = imagerotate($source, $angle, 0);
            if ($rotated === false) {
                throw new RuntimeException('Die Bildausrichtung konnte nicht verarbeitet werden.');
            }
            $source = $rotated;
        }
        $width = imagesx($source);
        $height = imagesy($source);
        $scale = min(1, MAX_IMAGE_SIDE / max($width, $height));
        $newWidth = max(1, (int) round($width * $scale));
        $newHeight = max(1, (int) round($height * $scale));
        $target = imagecreatetruecolor($newWidth, $newHeight);
        if ($target === false) {
            throw new RuntimeException('Nicht genügend Speicher für die Bildverarbeitung.');
        }
        imagealphablending($target, false);
        imagesavealpha($target, true);
        if (!imagecopyresampled($target, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height)) {
            throw new RuntimeException('Das Foto konnte nicht verkleinert werden.');
        }
        $saved = match ($info['codec']) {
            'jpeg' => imagejpeg($target, $targetPath, 85),
            'png' => imagepng($target, $targetPath, 6),
            'webp' => imagewebp($target, $targetPath, 85),
        };
        if (!$saved || !is_file($targetPath) || filesize($targetPath) === 0) {
            throw new RuntimeException('Die optimierte Bilddatei konnte nicht gespeichert werden.');
        }
    } finally {
        // GD-Objekte werden beim Freigeben der Referenzen zerstört (auch bei Fehlern).
        unset($source, $target, $rotated);
    }
}
