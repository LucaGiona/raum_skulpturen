<?php
declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/lib/gallery-data.php';

require_login();

$galleries = load_galleries();
$activeCategory = $_GET['category'] ?? array_key_first($galleries);
if (!isset($galleries[$activeCategory])) {
    $activeCategory = array_key_first($galleries);
}

$token = csrf_token();
$errorMessage = $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin — Galerie — Skulptur + Raum</title>
    <link rel="stylesheet" href="/admin/admin.css">
</head>
<body class="admin-body">
    <header class="admin-header">
        <h1>Galerie verwalten</h1>
        <form method="post" action="/admin/logout.php">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($token) ?>">
            <button type="submit" class="admin-logout">Abmelden</button>
        </form>
    </header>

    <?php if ($errorMessage !== ''): ?>
        <p class="admin-error" role="alert"><?= htmlspecialchars($errorMessage) ?></p>
    <?php endif; ?>

    <?php if (isset($_GET['success'])): ?>
        <p class="admin-success" role="status">Bild wurde hochgeladen.</p>
    <?php endif; ?>

    <?php if (isset($_GET['deleted'])): ?>
        <p class="admin-success" role="status">Bild wurde gelöscht.</p>
    <?php endif; ?>

    <nav class="admin-tabs">
        <?php foreach ($galleries as $key => $gallery): ?>
            <a
                href="/admin/index.php?category=<?= urlencode($key) ?>"
                class="admin-tab <?= $key === $activeCategory ? 'is-active' : '' ?>"
            ><?= htmlspecialchars($key) ?> (<?= count($gallery['images']) ?>)</a>
        <?php endforeach; ?>
    </nav>

    <section class="admin-upload">
        <h2>Neues Bild hochladen</h2>
        <form method="post" action="/admin/upload.php" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($token) ?>">

            <div class="admin-field">
                <label for="category">Kategorie</label>
                <select id="category" name="category">
                    <?php foreach ($galleries as $key => $gallery): ?>
                        <option value="<?= htmlspecialchars($key) ?>" <?= $key === $activeCategory ? 'selected' : '' ?>>
                            <?= htmlspecialchars($key) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="admin-field">
                <label for="image">Bilddatei</label>
                <small id="image-hint">JPG, PNG oder WebP, maximal 2&nbsp;MB. Empfohlen: 600–1600 Pixel an der längsten Seite.</small>
                <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp" aria-describedby="image-hint" required>
            </div>

            <button type="submit">Hochladen</button>
        </form>
    </section>

    <section class="admin-gallery">
        <h2><?= htmlspecialchars($activeCategory) ?> — <?= count($galleries[$activeCategory]['images']) ?> Bilder</h2>

        <div class="admin-grid">
            <?php
            $imageList = $galleries[$activeCategory]['images'];
            $lastIndex = count($imageList) - 1;
            ?>
            <?php foreach ($imageList as $index => $filename): ?>
                <div class="admin-grid-item">
                    <img src="<?= htmlspecialchars($galleries[$activeCategory]['path'] . $filename) ?>" alt="<?= htmlspecialchars($filename) ?>" loading="lazy">
                    <p class="admin-filename"><?= htmlspecialchars($filename) ?></p>

                    <div class="admin-reorder">
                        <form method="post" action="/admin/reorder.php">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($token) ?>">
                            <input type="hidden" name="category" value="<?= htmlspecialchars($activeCategory) ?>">
                            <input type="hidden" name="filename" value="<?= htmlspecialchars($filename) ?>">
                            <input type="hidden" name="direction" value="up">
                            <button type="submit" class="admin-move" <?= $index === 0 ? 'disabled' : '' ?> title="Nach oben">↑</button>
                        </form>
                        <form method="post" action="/admin/reorder.php">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($token) ?>">
                            <input type="hidden" name="category" value="<?= htmlspecialchars($activeCategory) ?>">
                            <input type="hidden" name="filename" value="<?= htmlspecialchars($filename) ?>">
                            <input type="hidden" name="direction" value="down">
                            <button type="submit" class="admin-move" <?= $index === $lastIndex ? 'disabled' : '' ?> title="Nach unten">↓</button>
                        </form>
                    </div>

                    <form method="post" action="/admin/delete.php" onsubmit="return confirm('Dieses Bild wirklich löschen?');">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($token) ?>">
                        <input type="hidden" name="category" value="<?= htmlspecialchars($activeCategory) ?>">
                        <input type="hidden" name="filename" value="<?= htmlspecialchars($filename) ?>">
                        <button type="submit" class="admin-delete">Löschen</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
</body>
</html>
