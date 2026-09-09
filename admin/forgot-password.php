<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../lib/mail-sender.php';

if (!config_exists()) {
    header('Location: /admin/setup.php');
    exit;
}

const RESET_TOKEN_TTL_SECONDS = 1800; // 30 Minuten

$sent = false;
$debug = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $config = admin_config();

    $rawToken = bin2hex(random_bytes(32));
    $tokenHash = hash('sha256', $rawToken);

    write_admin_config([
        'reset_token_hash' => $tokenHash,
        'reset_token_expires' => time() + RESET_TOKEN_TTL_SECONDS,
    ]);

    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $resetLink = "{$scheme}://{$host}/admin/reset-password.php?token={$rawToken}";

    $subject = 'Passwort zuruecksetzen — Skulptur + Raum Admin';
    $body = "Hallo,\n\n"
        . "es wurde ein Zuruecksetzen des Admin-Passworts fuer die Skulptur + Raum Website angefordert.\n\n"
        . "Link zum Setzen eines neuen Passworts (30 Minuten gueltig):\n{$resetLink}\n\n"
        . "Wenn du das nicht angefordert hast, kannst du diese Mail ignorieren.\n";

    $result = send_mail($config['admin_email'], $subject, $body);
    $sent = $result['sent'];
    $debug = $result['debug'];
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Passwort vergessen — Skulptur + Raum</title>
    <link rel="stylesheet" href="/admin/admin.css">
</head>
<body class="admin-body admin-body--login">
    <main class="admin-login">
        <h1>Passwort vergessen</h1>

        <?php if ($sent): ?>
            <p class="admin-success" role="status">
                Ein Link zum Zuruecksetzen wurde an die hinterlegte Admin-E-Mail-Adresse geschickt.
                Bitte pruefe auch den Spam-Ordner.
            </p>
        <?php elseif ($debug !== null): ?>
            <p class="admin-error" role="alert">
                Die Mail konnte nicht versendet werden.<br>
                <strong>Debug:</strong> <?= htmlspecialchars($debug) ?>
            </p>
        <?php else: ?>
            <p>Klicke auf den Button, um einen Link zum Zuruecksetzen des Admin-Passworts per E-Mail zu erhalten.</p>
            <form method="post" action="/admin/forgot-password.php">
                <button type="submit">Reset-Link anfordern</button>
            </form>
        <?php endif; ?>

        <p class="admin-forgot-link"><a href="/admin/login.php">Zurück zum Login</a></p>
    </main>
</body>
</html>
