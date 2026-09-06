<?php
// Kopiere diese Datei nach admin/config.php und trage deine echten Zugangsdaten ein.
// admin/config.php wird NICHT ins Git-Repo committet (siehe .gitignore).

return [
    'admin_username' => 'admin',

    // Passwort-Hash, NICHT das Klartext-Passwort!
    // Erzeugen z.B. im Terminal mit:
    //   php -r "echo password_hash('dein-passwort', PASSWORD_DEFAULT), PHP_EOL;"
    'admin_password_hash' => '$2y$10$replaceThisWithARealHashFromTheCommandAbove',
];
