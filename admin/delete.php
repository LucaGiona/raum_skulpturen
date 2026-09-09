<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/lib/gallery-data.php';

require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /admin/index.php');
    exit;
}

if (!check_csrf_token($_POST['csrf_token'] ?? '')) {
    header('Location: /admin/index.php?error=' . urlencode('Sicherheits-Token ungueltig.'));
    exit;
}

$category = $_POST['category'] ?? '';
$filename = $_POST['filename'] ?? '';

$galleries = load_galleries();

if (!isset($galleries[$category]) || $filename === '') {
    header('Location: /admin/index.php?error=' . urlencode('Ungueltige Anfrage.'));
    exit;
}

// Nur den reinen Dateinamen zulassen, keine Pfad-Traversierung.
$safeFilename = basename($filename);

// Nur loeschen, was tatsaechlich als Bild dieser Kategorie gefuehrt wird -
// verhindert, dass ueber ein manipuliertes Formularfeld beliebige Dateien
// im Zielordner geloescht werden koennen.
if (!in_array($safeFilename, $galleries[$category]['images'], true)) {
    header('Location: /admin/index.php?error=' . urlencode('Bild gehoert nicht zu dieser Kategorie.'));
    exit;
}

$filePath = __DIR__ . '/../' . ltrim($galleries[$category]['path'], '/') . $safeFilename;

if (is_file($filePath)) {
    @unlink($filePath);
}

remove_image_from_gallery($category, $safeFilename);

header('Location: /admin/index.php?deleted=1');
exit;
