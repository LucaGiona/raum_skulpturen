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

## Struktur

* `index.html`, `guestbook.html` – Seiten
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
