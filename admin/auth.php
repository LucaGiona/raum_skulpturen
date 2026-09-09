<?php
declare(strict_types=1);

const ADMIN_CONFIG_PATH = __DIR__ . '/config.php';
const LOGIN_MAX_ATTEMPTS = 5;
const LOGIN_LOCKOUT_SECONDS = 300; // 5 Minuten

// Session-Cookies absichern, bevor die Session gestartet wird.
if (session_status() === PHP_SESSION_NONE) {
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_start();
}

/** Existiert bereits eine admin/config.php (d.h. wurde das Admin-Konto schon eingerichtet)? */
function config_exists(): bool
{
    return file_exists(ADMIN_CONFIG_PATH);
}

/** @return array<string, mixed> */
function admin_config(): array
{
    if (!config_exists()) {
        header('Location: /admin/setup.php');
        exit;
    }

    return require ADMIN_CONFIG_PATH;
}

/**
 * Schreibt admin/config.php neu. Bestehende, nicht übergebene Felder bleiben erhalten.
 *
 * @param array<string, mixed> $changes
 */
function write_admin_config(array $changes): void
{
    $current = config_exists() ? (require ADMIN_CONFIG_PATH) : [];
    $updated = array_merge($current, $changes);

    $export = var_export($updated, true);
    $contents = "<?php\n"
        . "// Automatisch erzeugt ueber admin/setup.php bzw. admin/reset-password.php.\n"
        . "// Diese Datei enthaelt echte Zugangsdaten und wird NICHT ins Git-Repo committet (siehe .gitignore).\n"
        . "return {$export};\n";

    $tmpPath = ADMIN_CONFIG_PATH . '.tmp';
    file_put_contents($tmpPath, $contents, LOCK_EX);
    rename($tmpPath, ADMIN_CONFIG_PATH);
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

/**
 * Einfacher Schutz gegen Brute-Force-Login-Versuche (session-basiert).
 * Kein Ersatz fuer IP-basiertes Rate-Limiting, aber besser als nichts.
 */
function login_locked_seconds_remaining(): int
{
    $lockedUntil = $_SESSION['login_locked_until'] ?? 0;
    $remaining = $lockedUntil - time();

    return $remaining > 0 ? $remaining : 0;
}

function register_login_failure(): void
{
    $attempts = ($_SESSION['login_attempts'] ?? 0) + 1;
    $_SESSION['login_attempts'] = $attempts;

    if ($attempts >= LOGIN_MAX_ATTEMPTS) {
        $_SESSION['login_locked_until'] = time() + LOGIN_LOCKOUT_SECONDS;
        $_SESSION['login_attempts'] = 0;
    }
}

function reset_login_failures(): void
{
    unset($_SESSION['login_attempts'], $_SESSION['login_locked_until']);
}
