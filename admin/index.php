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
    <link rel="stylesheet" href="/admin/admin.css?v=<?= filemtime(__DIR__ . '/admin.css') ?>">
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
        <form method="post" action="/admin/upload.php" enctype="multipart/form-data" id="upload-form">
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
                <small id="image-hint">JPG, PNG oder WebP, maximal 20&nbsp;MB. Fotos werden automatisch auf maximal 2560 Pixel an der längsten Seite verkleinert. HEIC/HEIF bitte vorher als JPG exportieren. Sehr große Pixelmaße können das Server-Speicherlimit überschreiten.</small>
                <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp" aria-describedby="image-hint" required>
            </div>

            <button type="submit" id="upload-button">Hochladen</button>
            <div id="upload-status" hidden>
                <p id="upload-message" role="status">Upload wird vorbereitet …</p>
                <div id="upload-progress" class="upload-progress" role="progressbar"
                     aria-label="Foto hochladen" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
                    <span id="upload-progress-fill" class="upload-progress-fill"></span>
                </div>
                <small>Bitte diese Seite bis zum Abschluss geöffnet lassen.</small>
            </div>
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
<script>
    const uploadForm = document.getElementById('upload-form');
    const uploadButton = document.getElementById('upload-button');
    const uploadStatus = document.getElementById('upload-status');
    const uploadMessage = document.getElementById('upload-message');
    const uploadProgress = document.getElementById('upload-progress');
    const uploadFill = document.getElementById('upload-progress-fill');
    const uploadFields = [document.getElementById('image'), document.getElementById('category')];
    let uploading = false;

    function unlockUpload() {
        uploading = false;
        uploadButton.disabled = false;
        uploadButton.hidden = false;
        uploadButton.textContent = 'Hochladen';
        uploadFields.forEach((field) => { field.disabled = false; });
        uploadForm.removeAttribute('aria-busy');
    }

    function uploadFailed(message) {
        unlockUpload();
        uploadStatus.dataset.state = 'error';
        uploadMessage.textContent = message;
        uploadProgress.hidden = true;
    }

    function processingUpload() {
        uploadFill.style.width = '100%';
        uploadProgress.setAttribute('aria-valuenow', '100');
        uploadStatus.dataset.state = 'processing';
        uploadMessage.textContent = 'Übertragung abgeschlossen – Foto wird jetzt verarbeitet …';
        uploadButton.textContent = 'Wird verarbeitet …';
    }

    uploadForm.addEventListener('submit', (event) => {
        event.preventDefault();
        if (uploading) return;

        // Formulardaten vor dem Sperren der Eingabefelder erfassen.
        const data = new FormData(uploadForm);
        uploading = true;
        uploadButton.disabled = true;
        uploadButton.hidden = true;
        uploadButton.textContent = 'Wird hochgeladen …';
        uploadFields.forEach((field) => { field.disabled = true; });
        uploadStatus.hidden = false;
        uploadStatus.dataset.state = 'uploading';
        uploadProgress.hidden = false;
        uploadProgress.setAttribute('aria-valuenow', '0');
        uploadFill.style.width = '0%';
        uploadMessage.textContent = 'Foto wird hochgeladen: 0 %';
        uploadForm.setAttribute('aria-busy', 'true');

        const request = new XMLHttpRequest();
        request.upload.addEventListener('progress', (progress) => {
            if (!progress.lengthComputable) {
                uploadProgress.removeAttribute('aria-valuenow');
                uploadMessage.textContent = 'Foto wird hochgeladen …';
                return;
            }
            const percent = Math.min(100, Math.floor(progress.loaded / progress.total * 100));
            uploadFill.style.width = percent + '%';
            uploadProgress.setAttribute('aria-valuenow', String(percent));
            uploadMessage.textContent = 'Foto wird hochgeladen: ' + percent + ' %';
        });
        request.upload.addEventListener('load', processingUpload);
        request.addEventListener('load', () => {
            // PHP leitet nach Erfolg bzw. Fehler auf die bestehende Admin-Seite um.
            const destination = new URL(request.responseURL || uploadForm.action, window.location.href);
            if (request.status >= 200 && request.status < 300
                && destination.origin === window.location.origin
                && (destination.searchParams.has('success') || destination.searchParams.has('error')
                    || destination.pathname.endsWith('/login.php'))) {
                window.location.assign(destination.href);
                return;
            }
            uploadFailed('Die Serverantwort konnte nicht bestätigt werden. Bitte die Seite neu laden und die Galerie prüfen, bevor du erneut hochlädst.');
        });
        request.addEventListener('error', () => {
            uploadFailed('Die Verbindung wurde unterbrochen. Bitte die Seite neu laden und prüfen, ob das Foto bereits in der Galerie ist.');
        });
        request.addEventListener('abort', () => {
            uploadFailed('Der Upload wurde abgebrochen. Bitte vor einem erneuten Upload die Galerie prüfen.');
        });
        try {
            request.open('POST', uploadForm.action);
            request.send(data);
        } catch (error) {
            uploadFailed('Der Upload konnte nicht gestartet werden. Bitte die Seite neu laden und erneut versuchen.');
        }
    });

    window.addEventListener('pageshow', () => {
        unlockUpload();
        uploadStatus.hidden = true;
    });
    </script>
</body>
</html>
