<?php
// Kopiere diese Datei nach admin/config.php und trage deine echten Zugangsdaten ein.
// admin/config.php wird NICHT ins Git-Repo committet (siehe .gitignore).
//
// Hinweis: Normalerweise erzeugt admin/setup.php diese Datei automatisch bei der
// Ersteinrichtung im Browser (siehe admin/setup-code.example.php). Diese Beispieldatei
// ist nur fuer den Fall gedacht, dass du die Zugangsdaten manuell setzen willst.

return [
    'admin_username' => 'admin',

    // Passwort-Hash, NICHT das Klartext-Passwort!
    // Erzeugen z.B. im Terminal mit:
    //   php -r "echo password_hash('dein-passwort', PASSWORD_DEFAULT), PHP_EOL;"
    'admin_password_hash' => '$2y$10$replaceThisWithARealHashFromTheCommandAbove',

    // Wird fuer "Passwort vergessen" genutzt.
    'admin_email' => 'admin@example.com',

    // Wird automatisch von admin/forgot-password.php / reset-password.php verwaltet.
    'reset_token_hash' => null,
    'reset_token_expires' => null,
];
