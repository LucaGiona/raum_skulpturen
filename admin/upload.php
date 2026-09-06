<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/lib/gallery-data.php';

require_login();

const ALLOWED_MIME_TYPES = [
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/webp' => 'webp',
];

const MAX_FILE_SIZE_BYTES = 15 * 1024 * 1024; // 15 MB
const MAX_IMAGE_WIDTH = 2400; // groessere Uploads werden herunterskaliert

function fail(string $message): void
{
    header('Location: /admin/index.php?error=' . urlencode($message));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    fail('Ungueltige Anfrage.');
}

if (!check_csrf_token($_POST['csrf_token'] ?? '')) {
    fail('Sicherheits-Token ungueltig. Bitte Seite neu laden und erneut versuchen.');
}

$category = $_POST['category'] ?? '';
$galleries = load_galleries();

if (!isset($galleries[$category])) {
    fail('Unbekannte Kategorie.');
}

if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    fail('Bild-Upload fehlgeschlagen (keine Datei oder Upload-Fehler).');
}

$file = $_FILES['image'];

if ($file['size'] > MAX_FILE_SIZE_BYTES) {
    fail('Die Datei ist zu gross (maximal 15 MB).');
}

$finfo = new finfo(FILEINFO_MIME_TYPE);
$mimeType = $finfo->file($file['tmp_name']);

if (!isset(ALLOWED_MIME_TYPES[$mimeType])) {
    fail('Nur JPG, PNG oder WebP Bilder sind erlaubt.');
}

$extension = ALLOWED_MIME_TYPES[$mimeType];

// Sicheren, eindeutigen Dateinamen erzeugen (Originalname als Basis, aber bereinigt).
$originalBase = pathinfo($file['name'], PATHINFO_FILENAME);
$safeBase = preg_replace('/[^a-z0-9\-]+/', '-', strtolower($originalBase));
$safeBase = trim($safeBase, '-');
if ($safeBase === '') {
    $safeBase = 'bild';
}

$filename = $safeBase . '-' . date('Ymd-His') . '-' . bin2hex(random_bytes(3)) . '.' . $extension;

$targetDir = __DIR__ . '/../' . ltrim($galleries[$category]['path'], '/');
if (!is_dir($targetDir)) {
    fail('Zielordner fuer diese Kategorie existiert nicht: ' . $targetDir);
}

$targetPath = $targetDir . $filename;

// Bild ggf. verkleinern (haelt die Dateigroessen im Rahmen), sonst einfach kopieren.
$resized = false;

if (function_exists('getimagesize')) {
    $imageInfo = @getimagesize($file['tmp_name']);

    if ($imageInfo !== false && $imageInfo[0] > MAX_IMAGE_WIDTH) {
        $resized = resize_and_save_image($file['tmp_name'], $targetPath, $mimeType, MAX_IMAGE_WIDTH);
    }
}

if (!$resized) {
    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        fail('Datei konnte nicht gespeichert werden.');
    }
}

try {
    add_image_to_gallery($category, $filename);
} catch (Throwable $e) {
    fail('Bild wurde gespeichert, aber Galerie-Daten konnten nicht aktualisiert werden: ' . $e->getMessage());
}

header('Location: /admin/index.php?success=1');
exit;

function resize_and_save_image(string $sourcePath, string $targetPath, string $mimeType, int $maxWidth): bool
{
    if (!function_exists('imagecreatefromjpeg')) {
        return false; // GD nicht verfuegbar, Original wird stattdessen kopiert.
    }

    $source = match ($mimeType) {
        'image/jpeg' => @imagecreatefromjpeg($sourcePath),
        'image/png' => @imagecreatefrompng($sourcePath),
        'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($sourcePath) : false,
        default => false,
    };

    if ($source === false) {
        return false;
    }

    $width = imagesx($source);
    $height = imagesy($source);
    $newWidth = $maxWidth;
    $newHeight = (int) round($height * ($newWidth / $width));

    $resized = imagecreatetruecolor($newWidth, $newHeight);

    if ($mimeType === 'image/png' || $mimeType === 'image/webp') {
        imagealphablending($resized, false);
        imagesavealpha($resized, true);
    }

    imagecopyresampled($resized, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

    $saved = match ($mimeType) {
        'image/jpeg' => imagejpeg($resized, $targetPath, 85),
        'image/png' => imagepng($resized, $targetPath),
        'image/webp' => function_exists('imagewebp') ? imagewebp($resized, $targetPath, 85) : false,
        default => false,
    };

    imagedestroy($source);
    imagedestroy($resized);

    return $saved;
}
