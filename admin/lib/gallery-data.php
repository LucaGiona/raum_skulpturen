<?php
declare(strict_types=1);

/**
 * Zentrale Verwaltung der Galerie-Daten.
 *
 * Datenquelle ist js/data/images.json. Nach jeder Änderung wird daraus
 * automatisch js/data/images.js neu geschrieben, damit die bestehende
 * Frontend-Logik (js/gallery.js) unverändert weiterfunktioniert.
 */

const GALLERY_JSON_PATH = __DIR__ . '/../../js/data/images.json';
const GALLERY_JS_PATH = __DIR__ . '/../../js/data/images.js';

/** @return array<string, array{path: string, images: string[]}> */
function load_galleries(): array
{
    if (!file_exists(GALLERY_JSON_PATH)) {
        throw new RuntimeException('js/data/images.json wurde nicht gefunden.');
    }

    $raw = file_get_contents(GALLERY_JSON_PATH);
    $data = json_decode($raw, true);

    if (!is_array($data)) {
        throw new RuntimeException('js/data/images.json ist beschädigt oder kein gültiges JSON.');
    }

    return $data;
}

/** @param array<string, array{path: string, images: string[]}> $galleries */
function save_galleries(array $galleries): void
{
    $json = json_encode($galleries, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    if ($json === false) {
        throw new RuntimeException('Galerie-Daten konnten nicht als JSON kodiert werden.');
    }

    file_put_contents(GALLERY_JSON_PATH, $json . "\n");

    $jsContent = "// Diese Datei wird automatisch aus js/data/images.json erzeugt.\n"
        . "// Bitte nicht von Hand bearbeiten - Aenderungen im admin/-Bereich vornehmen.\n"
        . "export const galleries = {$json};\n";

    file_put_contents(GALLERY_JS_PATH, $jsContent);
}

function add_image_to_gallery(string $category, string $filename): void
{
    $galleries = load_galleries();

    if (!isset($galleries[$category])) {
        throw new InvalidArgumentException("Unbekannte Kategorie: {$category}");
    }

    if (!in_array($filename, $galleries[$category]['images'], true)) {
        $galleries[$category]['images'][] = $filename;
    }

    save_galleries($galleries);
}

function remove_image_from_gallery(string $category, string $filename): void
{
    $galleries = load_galleries();

    if (!isset($galleries[$category])) {
        throw new InvalidArgumentException("Unbekannte Kategorie: {$category}");
    }

    $galleries[$category]['images'] = array_values(array_filter(
        $galleries[$category]['images'],
        static fn (string $existing) => $existing !== $filename
    ));

    save_galleries($galleries);
}

function move_image(string $category, string $filename, string $direction): void
{
    $galleries = load_galleries();

    if (!isset($galleries[$category])) {
        throw new InvalidArgumentException("Unbekannte Kategorie: {$category}");
    }

    $images = $galleries[$category]['images'];
    $index = array_search($filename, $images, true);

    if ($index === false) {
        return;
    }

    $targetIndex = $direction === 'up' ? $index - 1 : $index + 1;

    if ($targetIndex < 0 || $targetIndex >= count($images)) {
        return; // Bereits am Anfang/Ende, nichts zu tun.
    }

    [$images[$index], $images[$targetIndex]] = [$images[$targetIndex], $images[$index]];

    $galleries[$category]['images'] = $images;
    save_galleries($galleries);
}
