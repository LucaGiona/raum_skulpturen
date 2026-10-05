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
