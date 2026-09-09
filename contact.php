<?php
declare(strict_types=1);

require_once __DIR__ . '/lib/mail-sender.php';

$errors = [];
$success = false;
$firstname = '';
$lastname = '';
$email = '';
$phone = '';
$message = '';

$isAjax = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'fetch'
    || str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstname = trim($_POST['firstname'] ?? '');
    $lastname = trim($_POST['lastname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $honeypot = trim($_POST['website'] ?? '');

    if ($honeypot !== '') {
        // Vermutlich ein Bot: Erfolg vortäuschen, ohne etwas zu versenden.
        $success = true;
        $firstname = $lastname = $email = $phone = $message = '';
    } else {
        if ($firstname === '') {
            $errors['firstname'] = 'Bitte einen Vornamen angeben.';
        }

        if ($lastname === '') {
            $errors['lastname'] = 'Bitte einen Namen angeben.';
        }

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Bitte eine gültige E-Mail-Adresse angeben.';
        }

        if ($phone !== '' && !preg_match('/^[0-9+\-\/\s()]{3,30}$/', $phone)) {
            $errors['phone'] = 'Bitte eine gültige Telefonnummer angeben.';
        }

        if ($message === '') {
            $errors['message'] = 'Bitte eine Nachricht eingeben.';
        } elseif (mb_strlen($message) < 10) {
            $errors['message'] = 'Die Nachricht ist zu kurz (mindestens 10 Zeichen).';
        } elseif (mb_strlen($message) > 5000) {
            $errors['message'] = 'Die Nachricht ist zu lang (maximal 5000 Zeichen).';
        }

        if (empty($errors)) {
            $config = mail_config();
            $to = $config['contact_to_email'] ?? 'info@gipsmir.com';
            $subject = 'Neue Kontaktanfrage über die Website';
            $body = "Name: {$firstname} {$lastname}\n"
                . "E-Mail: {$email}\n"
                . "Telefon: " . ($phone !== '' ? $phone : '-') . "\n\n"
                . "Nachricht:\n{$message}\n";

            $result = send_mail($to, $subject, $body, $email);

            if ($result['sent']) {
                $success = true;
                $firstname = $lastname = $email = $phone = $message = '';
            } else {
                $errors['general'] = 'Die Nachricht konnte nicht gesendet werden. Bitte versuche es später erneut.';
                if ($result['debug'] !== null) {
                    $errors['debug'] = $result['debug'];
                }
            }
        }
    }

    if ($isAjax) {
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode([
            'success' => $success,
            'errors' => $errors,
        ]);
        exit;
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

        <?php if (!empty($errors['debug'])): ?>
            <p class="contact-error" role="alert"><strong>Debug (nur zum Testen sichtbar):</strong> <?= htmlspecialchars($errors['debug']) ?></p>
        <?php endif; ?>

        <form class="contact-form" method="post" action="/contact.php" novalidate>
            <div class="form-row">
                <div class="form-field">
                    <label for="firstname">Vorname</label>
                    <input type="text" id="firstname" name="firstname" required value="<?= htmlspecialchars($firstname) ?>">
                    <?php if (!empty($errors['firstname'])): ?>
                        <span class="field-error"><?= htmlspecialchars($errors['firstname']) ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-field">
                    <label for="lastname">Name</label>
                    <input type="text" id="lastname" name="lastname" required value="<?= htmlspecialchars($lastname) ?>">
                    <?php if (!empty($errors['lastname'])): ?>
                        <span class="field-error"><?= htmlspecialchars($errors['lastname']) ?></span>
                    <?php endif; ?>
                </div>
            </div>

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
                <label for="phone">Telefon</label>
                <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($phone) ?>">
                <?php if (!empty($errors['phone'])): ?>
                    <span class="field-error"><?= htmlspecialchars($errors['phone']) ?></span>
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
