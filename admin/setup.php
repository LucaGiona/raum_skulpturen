<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';

// Ist das Admin-Konto schon eingerichtet? Dann ist diese Seite gesperrt.
if (config_exists()) {
    header('Location: /admin/login.php');
    exit;
}

const SETUP_CODE_PATH = __DIR__ . '/setup-code.php';

if (!file_exists(SETUP_CODE_PATH)) {
    http_response_code(500);
    exit(
        'admin/setup-code.php fehlt. Bitte admin/setup-code.example.php kopieren, '
        . 'einen eigenen Einrichtungscode eintragen und per FTP hochladen.'
    );
}

$setupCode = (string) require SETUP_CODE_PATH;

$errors = [];
$values = ['username' => 'admin', 'email' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = trim($_POST['setup_code'] ?? '');
    $values['username'] = trim($_POST['username'] ?? 'admin');
    $values['email'] = trim($_POST['email'] ?? '');
    $password = (string) ($_POST['password'] ?? '');
    $passwordConfirm = (string) ($_POST['password_confirm'] ?? '');

    if (!hash_equals($setupCode, $code)) {
        $errors['setup_code'] = 'Einrichtungscode ist falsch.';
    }

    if ($values['username'] === '') {
        $errors['username'] = 'Bitte einen Benutzernamen angeben.';
    }

    if ($values['email'] === '' || !filter_var($values['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Bitte eine gültige E-Mail-Adresse angeben (wird für "Passwort vergessen" genutzt).';
    }

    if (strlen($password) < 8) {
        $errors['password'] = 'Das Passwort muss mindestens 8 Zeichen lang sein.';
    } elseif ($password !== $passwordConfirm) {
        $errors['password'] = 'Die Passwörter stimmen nicht überein.';
    }

    if (empty($errors)) {
        write_admin_config([
            'admin_username' => $values['username'],
            'admin_password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'admin_email' => $values['email'],
            'reset_token_hash' => null,
            'reset_token_expires' => null,
        ]);

        header('Location: /admin/login.php?setup=1');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin einrichten — Skulptur + Raum</title>
    <link rel="stylesheet" href="/admin/admin.css">
</head>
<body class="admin-body admin-body--login">
    <main class="admin-login">
        <h1>Admin-Zugang einrichten</h1>
        <p>Dieses Formular ist nur einmalig sichtbar, solange noch kein Admin-Passwort vergeben wurde.</p>

        <?php if (!empty($errors['setup_code'])): ?>
            <p class="admin-error" role="alert"><?= htmlspecialchars($errors['setup_code']) ?></p>
        <?php endif; ?>

        <form method="post" action="/admin/setup.php">
            <div class="admin-field">
                <label for="setup_code">Einrichtungscode</label>
                <input type="text" id="setup_code" name="setup_code" required autofocus>
            </div>

            <div class="admin-field">
                <label for="username">Benutzername</label>
                <input type="text" id="username" name="username" value="<?= htmlspecialchars($values['username']) ?>" required>
                <?php if (!empty($errors['username'])): ?>
                    <span class="field-error"><?= htmlspecialchars($errors['username']) ?></span>
                <?php endif; ?>
            </div>

            <div class="admin-field">
                <label for="email">E-Mail (für "Passwort vergessen")</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($values['email']) ?>" required>
                <?php if (!empty($errors['email'])): ?>
                    <span class="field-error"><?= htmlspecialchars($errors['email']) ?></span>
                <?php endif; ?>
            </div>

            <div class="admin-field">
                <label for="password">Passwort (mind. 8 Zeichen)</label>
                <input type="password" id="password" name="password" required minlength="8">
            </div>

            <div class="admin-field">
                <label for="password_confirm">Passwort wiederholen</label>
                <input type="password" id="password_confirm" name="password_confirm" required minlength="8">
                <?php if (!empty($errors['password'])): ?>
                    <span class="field-error"><?= htmlspecialchars($errors['password']) ?></span>
                <?php endif; ?>
            </div>

            <button type="submit">Admin-Zugang einrichten</button>
        </form>
    </main>
</body>
</html>
