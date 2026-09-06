<?php
declare(strict_types=1);

session_start();

const ADMIN_CONFIG_PATH = __DIR__ . '/config.php';

function admin_config(): array
{
    if (!file_exists(ADMIN_CONFIG_PATH)) {
        http_response_code(500);
        exit('admin/config.php fehlt. Bitte admin/config.example.php kopieren und ausfuellen.');
    }

    return require ADMIN_CONFIG_PATH;
}

function is_logged_in(): bool
{
    return !empty($_SESSION['admin_logged_in']);
}

function require_login(): void
{
    if (!is_logged_in()) {
        header('Location: /admin/login.php');
        exit;
    }
}

/** Einfacher CSRF-Schutz fuer die Admin-Formulare. */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function check_csrf_token(string $token): bool
{
    return !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
