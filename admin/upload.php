<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/lib/gallery-data.php';

require_login();

require_once __DIR__ . '/lib/image-upload.php';

function fail(string $message): void
{
    header('Location: /admin/index.php?error=' . urlencode($message));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    fail('Ungueltige Anfrage.');
}

if (empty($_POST) && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
    fail('Upload nicht angekommen. Möglicherweise überschreitet die Anfrage das PHP-Limit post_max_size. Bitte die Servereinstellungen prüfen.');
}

if (!check_csrf_token($_POST['csrf_token'] ?? '')) {
    fail('Sicherheits-Token ungueltig. Bitte Seite neu laden und erneut versuchen.');
}

$category = $_POST['category'] ?? '';
$galleries = load_galleries();

if (!isset($galleries[$category])) {
    fail('Unbekannte Kategorie.');
}

if (isset($_FILES['image']) && in_array($_FILES['image']['error'], [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)) {
    fail('Die Datei ist zu groß. Maximal 20 MB pro Foto. Falls die Datei kleiner ist, muss das PHP-Upload-Limit auf dem Server erhöht werden.');
}

if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    fail('Bild-Upload fehlgeschlagen (keine Datei oder Upload-Fehler).');
}

$file = $_FILES['image'];

if (!is_uploaded_file($file['tmp_name'])) {
    fail('Ungültige Upload-Datei.');
}

try {
    $image = inspect_upload_image($file['tmp_name']);
} catch (Throwable $e) {
    fail($e->getMessage());
}
$extension = $image['extension'];

// Sicheren, eindeutigen Dateinamen erzeugen (Originalname als Basis, aber bereinigt).
$originalBase = pathinfo($file['name'], PATHINFO_FILENAME);
$safeBase = preg_replace('/[^a-z0-9\-]+/', '-', strtolower($originalBase));
$safeBase = substr(trim($safeBase, '-'), 0, 100);
if ($safeBase === '') {
    $safeBase = 'bild';
}

$filename = $safeBase . '-' . date('Ymd-His') . '-' . bin2hex(random_bytes(3)) . '.' . $extension;

$targetDir = gallery_dir($galleries[$category]['path']);
if (!is_dir($targetDir)) {
    fail('Zielordner fuer diese Kategorie existiert nicht: ' . $targetDir);
}

$targetPath = $targetDir . $filename;

// Erst vollständig verarbeiten; bei Fehlern niemals das Original übernehmen.
try {
    optimize_upload_image($file['tmp_name'], $targetPath, $image);
} catch (Throwable $e) {
    @unlink($targetPath);
    fail($e->getMessage());
}

try {
    add_image_to_gallery($category, $filename);
} catch (Throwable $e) {
    // Rollback: bereits gespeicherte Datei wieder entfernen, damit keine verwaisten
    // Bilder auf dem Server liegen bleiben, die in keiner Galerie auftauchen.
    if (is_file($targetPath)) {
        @unlink($targetPath);
    }
    fail('Bild konnte nicht gespeichert werden, Galerie-Daten konnten nicht aktualisiert werden: ' . $e->getMessage());
}

header('Location: /admin/index.php?success=1&category=' . urlencode($category));
exit;
