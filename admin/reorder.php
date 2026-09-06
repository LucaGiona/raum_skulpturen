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
$filename = basename($_POST['filename'] ?? '');
$direction = $_POST['direction'] ?? '';

if (!in_array($direction, ['up', 'down'], true)) {
    header('Location: /admin/index.php?error=' . urlencode('Ungueltige Richtung.'));
    exit;
}

try {
    move_image($category, $filename, $direction);
} catch (Throwable $e) {
    header('Location: /admin/index.php?error=' . urlencode($e->getMessage()));
    exit;
}

header('Location: /admin/index.php?category=' . urlencode($category));
exit;
