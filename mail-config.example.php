<?php
// Kopiere diese Datei nach mail-config.php und trage die echten Zugangsdaten
// des STRATO-Postfachs ein, ueber das Mails versendet werden sollen.
// mail-config.php wird NICHT ins Git-Repo committet (siehe .gitignore).

return [
    // SMTP-Server, i.d.R. smtp.strato.de bei STRATO-Postfaechern.
    'smtp_host' => 'smtp.strato.de',
    'smtp_port' => 587,
    'smtp_secure' => 'tls', // 'tls' (Port 587) oder 'ssl' (Port 465)

    // Zugangsdaten des Postfachs, ueber das versendet wird (volle Mailadresse als Username).
    'smtp_username' => 'kontakt@sculptur-raum.de',
    'smtp_password' => 'dein-postfach-passwort',

    // Absenderadresse fuer ausgehende Mails (Kontaktformular + Passwort-Reset).
    // Sollte i.d.R. mit smtp_username uebereinstimmen, das ist bei STRATO fuer eine
    // zuverlaessige Zustellung wichtig.
    'from_email' => 'kontakt@sculptur-raum.de',
    'from_name'  => 'Skulptur + Raum Website',

    // Empfaenger-Adresse fuer Kontaktformular-Anfragen.
    'contact_to_email' => 'info@gipsmir.com',
];
