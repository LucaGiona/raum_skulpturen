# Skulptur + Raum

Neuaufbau der Website von **Gøran Thie – Skulptur + Raum**.

Die bestehende Website wird technisch und strukturell überarbeitet. Ziel ist eine schlanke, responsive und wartbare Künstler-Website, bei der die Arbeiten und Bilder im Mittelpunkt stehen.

## Ziele

* bestehende Gestaltungsidee und Charakter der Website bewahren
* saubere HTML-, CSS- und JavaScript-Struktur
* responsive Darstellung für Desktop, Tablet und Smartphone
* optimierte Bildgrößen und Ladezeiten
* möglichst wenig externe Abhängigkeiten
* Deployment auf der bestehenden Domain

## Stack

* HTML5
* CSS3
* Vanilla JavaScript (ES-Module)
* PHP (Kontaktformular via PHPMailer, Admin-Bereich)

## Status

Die Seite ist funktionsfähig und wird laufend weiterentwickelt. Umgesetzt sind bisher:

* Startseite mit Hero, Intro-Leistungen, Kafka-Zitat, Kunst-am-Bau-/Skulpturen-Galerie mit Lightbox, Bio-Sektion, Kontaktformular und Footer
* Gästebuch-Unterseite mit Foto-Karussell der eingescannten Original-Einträge
* Admin-Bereich (`/admin`) zur Verwaltung der Galerie-Bilder (Upload, Löschen, Sortieren) inkl. Login
* Sprachumschaltung Deutsch/Englisch (`js/i18n.js`, `js/data/translations.json`)
* Hell-/Dunkelmodus, Hintergrundmusik-Toggle, Parallax-Effekte, Back-to-top-Button

Die bestehende Live-Website dient weiterhin als inhaltliche und visuelle Referenz.

## Lokal starten

```
php -S localhost:8000
```

Danach ist die Seite unter `http://localhost:8000` erreichbar. Für den Admin-Bereich (`/admin`) müssen `admin/config.php` (aus `admin/config.example.php`) und für den Mailversand `mail-config.php` (aus `mail-config.example.php`) lokal angelegt werden – beide sind aus Sicherheitsgründen nicht Teil des Repos.

## Galerie-Aktualisierung

Die Kunst-am-Bau- und Skulpturen-Galerie auf der Startseite lädt beim Öffnen bzw. Neuladen und beim Zurückwechseln zum Website-Tab die aktuelle `js/data/images.json` ohne Browser-Cache. Auch beim Wiederherstellen aus dem Browser-Seitencache werden die Daten aktualisiert. Es gibt keine regelmäßigen Hintergrundabfragen; eine dauerhaft geöffnete, aktive Seite aktualisiert sich daher nicht allein durch einen Upload.

Die gewählte Kategorie und die Anzahl aufgeklappter Bilder bleiben erhalten. Nur geänderte Daten lösen eine neue Darstellung aus. Bei Netzwerkfehlern oder ungültigen Daten bleibt die bisherige Galerie sichtbar. Das Gästebuch-Karussell ist von dieser Aktualisierung nicht betroffen.

Für dieses Update nur `js/gallery.js` per FileZilla in den Ordner `js/` des Website-Stammverzeichnisses hochladen (aktuell `/website-live-okt26/js/gallery.js`). Bereits geöffnete Browser einmal mit Hard Refresh aktualisieren, damit sie den neuen JavaScript-Code laden. Die live vom Admin gepflegten Dateien in `js/data/` nicht mit älteren lokalen Daten überschreiben.

Prüfung nach dem Upload: Website öffnen, im Admin-Tab ein Bild hochladen und zurück zur Website wechseln. Bilderzahl bzw. Galerie müssen sich aktualisieren; ein am Ende hinzugefügtes Bild wird gegebenenfalls erst über „Mehr“ sichtbar.

## Projektstruktur

* `index.html` – Startseite
* `html/` – weitere Seiten (Gästebuch, Impressum, Datenschutz)
* `css/` – Stylesheets
* `js/` – Frontend-Logik, `js/data/` – Übersetzungen & Galerie-Daten (JSON, automatisch generiert)
* `admin/` – PHP-Verwaltungsbereich für die Galerien
* `assets/` – Bilder, Logos, Fonts
* `audio/` – Hintergrundmusik
* `lib/` – PHPMailer für den Kontaktformular-Versand

## Bestehende Website

https://www.sculptur-raum.de/

## Künstler

**Gøran Thie**
Skulptur + Raum
Berlin

## Foto-Upload bis 20 MB

Neue Uploads werden mit GD immer neu kodiert: JPEG/WebP mit Qualität 85, PNG verlustfrei mit Kompressionsstufe 6. Die längste Seite wird auf höchstens 2560 Pixel begrenzt; kleinere Bilder werden nicht vergrößert. JPEG-EXIF-Ausrichtungen einschließlich Spiegelungen werden angewendet. Metadaten werden nicht übernommen. PNG/WebP behalten Transparenz. Die optimierte Datei ersetzt nur den neuen Upload, bestehende Fotos bleiben unverändert.

Dateigröße, tatsächlicher MIME-Typ, Bildheader und Pixelmaße werden vor dem Dekodieren geprüft. Maximal 60 Megapixel bzw. 20000 Pixel je Seite sind zulässig; abhängig vom verfügbaren PHP-Speicher greift eine niedrigere Grenze. Die konservative Speicherprüfung kann besonders große Handyfotos ablehnen, auch wenn sie unter 20 MB liegen. Beschädigte Dateien oder Verarbeitungsfehler führen zu einer Fehlermeldung, niemals zum Speichern des ungeprüften Originals.

HEIC/HEIF: Die vorhandene GD-Verarbeitung unterstützt diese Formate nicht. Solche Dateien werden verständlich abgewiesen und müssen als JPG exportiert werden. Eine direkte Konvertierung wäre mit Imagick und einem funktionierenden HEIC/libheif-Decoder möglich, ist hier aber nicht implementiert. Imagick ist lokal nicht installiert; die STRATO-Unterstützung wurde nicht geprüft.

### PHP-Einstellungen und STRATO

Lokal geprüft (PHP 8.5.9 CLI): `upload_max_filesize=2M`, `post_max_size=8M`, `memory_limit=128M`; GD mit JPEG/PNG/WebP sowie EXIF und Fileinfo vorhanden. Diese lokalen Werte wurden nicht dauerhaft geändert. Die Verarbeitungstests liefen mit `memory_limit=512M`.

Auf STRATO müssen die **effektiven Werte der Web-PHP-Umgebung** separat geprüft und gegebenenfalls über die dort unterstützte PHP-Konfiguration angepasst werden:

- `upload_max_filesize`: mindestens `20M`.
- `post_max_size`: mindestens `24M`, damit zusätzlich zum Foto die Formulardaten Platz haben.
- `memory_limit`: `512M` als Ausgangswert für übliche Handyfotos. Sehr hochauflösende Fotos (z. B. 48 MP) können mit der konservativen Prüfung mehr benötigen oder werden verständlich abgewiesen.
- GD mit JPEG/PNG/WebP, EXIF und Fileinfo müssen aktiv sein; Schreibrechte für Galerieordner und Galerie-Daten müssen bestehen bleiben.

Keine STRATO-Einstellung wurde ausgelesen oder geändert. Upload-Limits lassen sich nicht nachträglich im Upload-Skript erhöhen. Auch vorgeschaltete Hosting-Limits und Laufzeitlimits sind bei einem abschließenden Test auf STRATO zu berücksichtigen.

### Mit FileZilla übertragen

In die entsprechenden Ordner des bestehenden Website-Stammverzeichnisses hochladen:

- `admin/upload.php`
- `admin/index.php`
- `admin/lib/image-upload.php` (neu)
- `admin/lib/gallery-data.php`, falls die vorhandene `gallery_dir()`-Pfadkorrektur noch nicht auf dem Server liegt; `upload.php` benötigt diese Funktion.

`README.md` ist nur Dokumentation und muss nicht hochgeladen werden. Keine lokalen Galerie-Daten oder Bildbestände über die live gepflegten Dateien kopieren. Nach Übertragung ein großes JPEG, ein gedrehtes Handyfoto und eine ungültige Datei testen und Galerie sowie Detailansicht prüfen. Es wurde nichts automatisch veröffentlicht.
