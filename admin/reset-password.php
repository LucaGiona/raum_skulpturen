<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';

if (!config_exists()) {
    header('Location: /admin/setup.php');
    exit;
}

function valid_reset_token(array $config, string $token): bool
{
    if ($token === '' || empty($config['reset_token_hash']) || empty($config['reset_token_expires'])) {
        return false;
    }

    if (time() > (int) $config['reset_token_expires']) {
        return false;
    }

    return hash_equals($config['reset_token_hash'], hash('sha256', $token));
}

$config = admin_config();
$token = $_GET['token'] ?? $_POST['token'] ?? '';
$errors = [];
$success = false;

if (!valid_reset_token($config, (string) $token)) {
    $errors['token'] = 'Der Link ist ungültig oder abgelaufen. Bitte fordere einen neuen an.';
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = (string) ($_POST['password'] ?? '');
    $passwordConfirm = (string) ($_POST['password_confirm'] ?? '');

    if (strlen($password) < 8) {
        $errors['password'] = 'Das Passwort muss mindestens 8 Zeichen lang sein.';
    } elseif ($password !== $passwordConfirm) {
        $errors['password'] = 'Die Passwörter stimmen nicht überein.';
    }

    if (empty($errors)) {
        write_admin_config([
            'admin_password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'reset_token_hash' => null,
            'reset_token_expires' => null,
        ]);

        $success = true;
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Neues Passwort setzen — Skulptur + Raum</title>
    <link rel="stylesheet" href="/admin/admin.css">
</head>
<body class="admin-body admin-body--login">
    <main class="admin-login">
        <h1>Neues Passwort setzen</h1>

        <?php if ($success): ?>
            <p class="admin-success" role="status">Passwort wurde geändert.</p>
            <p class="admin-forgot-link"><a href="/admin/login.php">Jetzt anmelden</a></p>
        <?php elseif (!empty($errors['token'])): ?>
            <p class="admin-error" role="alert"><?= htmlspecialchars($errors['token']) ?></p>
            <p class="admin-forgot-link"><a href="/admin/forgot-password.php">Neuen Link anfordern</a></p>
        <?php else: ?>
            <?php if (!empty($errors['password'])): ?>
                <p class="admin-error" role="alert"><?= htmlspecialchars($errors['password']) ?></p>
            <?php endif; ?>
            <form method="post" action="/admin/reset-password.php">
                <input type="hidden" name="token" value="<?= htmlspecialchars((string) $token) ?>">
                <div class="admin-field">
                    <label for="password">Neues Passwort (mind. 8 Zeichen)</label>
                    <input type="password" id="password" name="password" required minlength="8">
                </div>
                <div class="admin-field">
                    <label for="password_confirm">Passwort wiederholen</label>
                    <input type="password" id="password_confirm" name="password_confirm" required minlength="8">
                </div>
                <button type="submit">Passwort setzen</button>
            </form>
        <?php endif; ?>
    </main>
</body>
</html>
