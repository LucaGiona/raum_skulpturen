# Übergabe-Checkliste: Deployment auf STRATO

Kurze Checkliste für das Gespräch mit Gøran und für dich selbst vor dem Go-Live.

## Was du von Gøran brauchst

- **STRATO-Zugangsdaten** (Kunden-Login), um die PHP-Version einzustellen und ggf. den PHP Extended Support zu deaktivieren.
- **Ein E-Mail-Postfach bei STRATO**, über das die Website Mails verschicken soll (Kontaktformular + Passwort-Reset). Du brauchst davon: Mailadresse + Passwort (SMTP-Zugangsdaten).
- **Die E-Mail-Adresse**, an die "Passwort vergessen"-Links für den Admin-Bereich geschickt werden sollen (kann dieselbe wie oben sein oder eine andere).
- Bestätigung, ob `info@gipsmir.com` als Empfänger-Adresse fürs Kontaktformular weiterhin stimmt (steht aktuell so im Footer der Seite).
- Gewünschten Admin-Benutzernamen (Standard: `admin`) — nur falls er etwas anderes möchte.

## Was du (Luca) vor dem Deployment erledigen musst

1. **`mail-config.php` anlegen** (Kopie von `mail-config.example.php`) mit den echten STRATO-Postfach-Zugangsdaten und per FTP hochladen. Ohne diese Datei fällt das System auf PHP `mail()` zurück, das auf STRATO unzuverlässig ist.
2. **`admin/setup-code.php` anlegen** (Kopie von `admin/setup-code.example.php`) mit einem selbst gewählten Einrichtungscode und hochladen. Diesen Code teilst du Gøran **persönlich** mit (nicht per unverschlüsselter Mail) — er braucht ihn einmalig für die Ersteinrichtung seines Passworts.
3. **PHP-Version im STRATO Kunden-Login prüfen/setzen**: Datenbanken und Webspace → PHP-Version → mindestens 8.4 auswählen (vermeidet den kostenpflichtigen PHP Extended Support).
4. **Deployment per FTP/FileZilla**: alles außer `.git/`, `.gitignore`, `AGENTS.md`, `README.md`, `.DS_Store`, `admin/config.php`, `admin/config.example.php`-Inhalte mit echten Daten, `mail-config.php` (das kommt separat, siehe Punkt 1) hochladen.
5. Nach dem ersten Deployment: **`https://<domain>/admin/setup.php` aufrufen**, mit dem Einrichtungscode aus Schritt 2 das Admin-Passwort und die Admin-E-Mail-Adresse festlegen.

## Danach gemeinsam testen (idealerweise beim Termin)

- [ ] Admin-Login funktioniert mit dem neu gesetzten Passwort.
- [ ] Ein Bild hochladen, verschieben, löschen — funktioniert und Berechtigungen (Schreibrechte auf `js/data/` und die Bilderordner) passen.
- [ ] Kontaktformular auf der Startseite absenden — Mail kommt bei `info@gipsmir.com` an (nicht im Spam).
- [ ] "Passwort vergessen" auf der Login-Seite auslösen — Reset-Mail kommt bei der hinterlegten Admin-Adresse an, Link funktioniert, neues Passwort lässt sich setzen.
- [ ] Seite läuft über HTTPS (grünes Schloss im Browser).
- [ ] `.htaccess`-Schutz prüfen: `https://<domain>/admin/config.php`, `https://<domain>/mail-config.php` und `https://<domain>/admin/setup-code.php` im Browser aufrufen — muss jeweils **403 Forbidden** zeigen (nicht den Datei-Inhalt). Lässt sich nur auf dem echten Apache-Server bei STRATO testen, nicht lokal.

## Bekannte Einschränkungen (bewusst einfach gehalten)

- Der Login-Schutz gegen Brute-Force ist session-basiert (5 Fehlversuche → 5 Minuten Sperre), nicht IP-basiert. Für eine kleine Seite mit einem Admin-Account ausreichend.
- Es gibt nur einen Admin-Account, keine Benutzerverwaltung.
- Der Einrichtungscode (Schritt 2) ist der einzige Schutz der Setup-Seite vor der Ersteinrichtung — danach ist die Seite automatisch gesperrt.
