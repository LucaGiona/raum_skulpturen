<?php
declare(strict_types=1);

require_once __DIR__ . '/PHPMailer/Exception.php';
require_once __DIR__ . '/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

const MAIL_CONFIG_PATH = __DIR__ . '/../mail-config.php';

/** @return array<string, mixed>|null */
function mail_config(): ?array
{
    if (!file_exists(MAIL_CONFIG_PATH)) {
        return null;
    }

    /** @var array<string, mixed> $config */
    $config = require MAIL_CONFIG_PATH;

    return $config;
}

/**
 * Verschickt eine Mail.
 *
 * Wenn mail-config.php existiert, wird per SMTP ueber den dort konfigurierten
 * STRATO-Account versendet (zuverlaessiger, funktioniert auch lokal). Fehlt die
 * Datei, wird als Fallback die einfache PHP mail()-Funktion genutzt.
 *
 * @return array{sent: bool, debug: ?string}
 */
function send_mail(string $toEmail, string $subject, string $body, ?string $replyToEmail = null): array
{
    $config = mail_config();

    if ($config === null) {
        $fromEmail = 'no-reply@' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
        $headers = "From: {$fromEmail}\r\n";

        if ($replyToEmail !== null) {
            $headers .= "Reply-To: {$replyToEmail}\r\n";
        }

        $headers .= 'Content-Type: text/plain; charset=UTF-8';

        $sent = @mail($toEmail, $subject, $body, $headers);

        return [
            'sent' => $sent,
            'debug' => $sent ? null : 'Keine mail-config.php gefunden und mail() ist fehlgeschlagen. '
                . 'Siehe mail-config.example.php fuer SMTP-Versand ueber STRATO.',
        ];
    }

    $mailer = new PHPMailer(true);

    try {
        $mailer->isSMTP();
        $mailer->Host = $config['smtp_host'];
        $mailer->Port = $config['smtp_port'];
        $mailer->SMTPAuth = true;
        $mailer->Username = $config['smtp_username'];
        $mailer->Password = $config['smtp_password'];
        $mailer->SMTPSecure = $config['smtp_secure'] === 'ssl'
            ? PHPMailer::ENCRYPTION_SMTPS
            : PHPMailer::ENCRYPTION_STARTTLS;
        $mailer->CharSet = 'UTF-8';

        $mailer->setFrom($config['from_email'], $config['from_name'] ?? '');
        $mailer->addAddress($toEmail);

        if ($replyToEmail !== null) {
            $mailer->addReplyTo($replyToEmail);
        }

        $mailer->Subject = $subject;
        $mailer->Body = $body;
        $mailer->isHTML(false);

        $mailer->send();

        return ['sent' => true, 'debug' => null];
    } catch (PHPMailerException $e) {
        return ['sent' => false, 'debug' => $mailer->ErrorInfo];
    }
}
