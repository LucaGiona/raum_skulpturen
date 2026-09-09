<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';

if (!config_exists()) {
    header('Location: /admin/setup.php');
    exit;
}

$error = '';

if (is_logged_in()) {
    header('Location: /admin/index.php');
    exit;
}

$lockedSeconds = login_locked_seconds_remaining();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $lockedSeconds > 0) {
    $error = 'Zu viele Fehlversuche. Bitte in ' . ceil($lockedSeconds / 60) . ' Minute(n) erneut versuchen.';
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $config = admin_config();

    $username = trim($_POST['username'] ?? '');
    $password = (string) ($_POST['password'] ?? '');

    $validUsername = hash_equals($config['admin_username'], $username);
    $validPassword = password_verify($password, $config['admin_password_hash']);

    if ($validUsername && $validPassword) {
        reset_login_failures();
        session_regenerate_id(true);
        $_SESSION['admin_logged_in'] = true;
        header('Location: /admin/index.php');
        exit;
    }

    register_login_failure();
    $error = 'Benutzername oder Passwort ist falsch.';
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login — Skulptur + Raum</title>
    <link rel="stylesheet" href="/admin/admin.css">
</head>
<body class="admin-body admin-body--login">
    <main class="admin-login">
        <h1>Admin Login</h1>

        <?php if ($error !== ''): ?>
            <p class="admin-error" role="alert"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <form method="post" action="/admin/login.php">
            <div class="admin-field">
                <label for="username">Benutzername</label>
                <input type="text" id="username" name="username" required autofocus>
            </div>
            <div class="admin-field">
                <label for="password">Passwort</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Anmelden</button>
        </form>

        <p class="admin-forgot-link"><a href="/admin/forgot-password.php">Passwort vergessen?</a></p>
    </main>
</body>
</html>
