<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !check_csrf_token($_POST['csrf_token'] ?? '')) {
    header('Location: /admin/index.php');
    exit;
}

$_SESSION = [];
session_destroy();

header('Location: /admin/login.php');
exit;
