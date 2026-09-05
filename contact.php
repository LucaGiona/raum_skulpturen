<?php
declare(strict_types=1);

$errors = [];
$success = false;
$email = '';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $honeypot = trim($_POST['website'] ?? '');

    if ($honeypot !== '') {
        // Likely a bot: pretend success without sending anything.
        $success = true;
        $email = '';
        $message = '';
    } else {
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Bitte eine gültige E-Mail-Adresse angeben.';
        }

        if ($message === '') {
            $errors['message'] = 'Bitte eine Nachricht eingeben.';
        } elseif (mb_strlen($message) < 10) {
            $errors['message'] = 'Die Nachricht ist zu kurz (mindestens 10 Zeichen).';
        } elseif (mb_strlen($message) > 5000) {
            $errors['message'] = 'Die Nachricht ist zu lang (maximal 5000 Zeichen).';
        }

        if (empty($errors)) {
            $to = 'info@gipsmir.com';
            $subject = 'Neue Kontaktanfrage über die Website';
            $body = "E-Mail: {$email}\n\nNachricht:\n{$message}\n";
            $headers = "From: no-reply@gipsmir.com\r\n"
                . "Reply-To: {$email}\r\n"
                . "Content-Type: text/plain; charset=UTF-8";

            $sent = mail($to, $subject, $body, $headers);

            if ($sent) {
                $success = true;
                $email = '';
                $message = '';
            } else {
                $errors['general'] = 'Die Nachricht konnte nicht gesendet werden. Bitte versuche es später erneut.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/contact.css">
    <title>Kontakt — Skulptur + Raum</title>
</head>
<body>
    <header class="site-header">
        <div class="logo-test">
            <a href="/index.html">
                <img src="/assets/S+R-Logo_1.png" alt="Skulptur und Raum">
            </a>
        </div>
    </header>

    <section class="contact">
        <h1 class="contact-title">Kontakt</h1>

        <?php if ($success): ?>
            <p class="contact-success" role="status">Danke für deine Nachricht! Wir melden uns bald bei dir.</p>
        <?php endif; ?>

        <?php if (!empty($errors['general'])): ?>
            <p class="contact-error" role="alert"><?= htmlspecialchars($errors['general']) ?></p>
        <?php endif; ?>

        <form class="contact-form" method="post" action="/contact.php" novalidate>
            <div class="form-field">
                <label for="email">E-Mail</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    required
                    value="<?= htmlspecialchars($email) ?>"
                    aria-invalid="<?= !empty($errors['email']) ? 'true' : 'false' ?>"
                >
                <?php if (!empty($errors['email'])): ?>
                    <span class="field-error"><?= htmlspecialchars($errors['email']) ?></span>
                <?php endif; ?>
            </div>

            <div class="form-field">
                <label for="message">Nachricht</label>
                <textarea
                    id="message"
                    name="message"
                    rows="6"
                    required
                    aria-invalid="<?= !empty($errors['message']) ? 'true' : 'false' ?>"
                ><?= htmlspecialchars($message) ?></textarea>
                <?php if (!empty($errors['message'])): ?>
                    <span class="field-error"><?= htmlspecialchars($errors['message']) ?></span>
                <?php endif; ?>
            </div>

            <div class="form-field form-field--honeypot" aria-hidden="true">
                <label for="website">Website</label>
                <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
            </div>

            <button type="submit" class="contact-submit">Absenden</button>
        </form>
    </section>

    <footer class="site-footer">
        <div class="footer-brand">
            <p class="footer-company">Skulptur + Raum</p>
            <p class="footer-name">Gøran Thie</p>
        </div>
        <div class="footer-legal">
            <a href="/index.html" class="footer-link">Zurück zur Startseite</a>
        </div>
    </footer>
</body>
</html>
