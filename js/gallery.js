import { galleries as initialGalleries } from "./data/images.js";
import { openLightbox } from "./lightbox.js";

let galleries = initialGalleries;
let refreshPending = false;
const galleryDataUrl = new URL("./data/images.json", import.meta.url);

async function refreshGallery() {
    if (refreshPending || document.visibilityState === "hidden") return;
    refreshPending = true;
    try {
        const response = await fetch(galleryDataUrl, { cache: "no-store" });
        if (!response.ok) throw new Error(`HTTP ${response.status}`);
        const next = await response.json();
        for (const category of ["works", "sculptures"]) {
            const gallery = next?.[category];
            if (!gallery || typeof gallery.path !== "string" ||
                !Array.isArray(gallery.images) ||
                !gallery.images.every(filename => typeof filename === "string")) {
                throw new Error("Ungültige Galerie-Daten");
            }
        }
        if (["works", "sculptures"].some(category =>
            JSON.stringify(next[category]) !== JSON.stringify(galleries[category]))) {
            galleries = next;
            renderGallery();
        }
    } catch (error) {
        // Bei Netzwerkfehlern die bereits geladene Galerie erhalten.
        console.warn("Galerie konnte nicht aktualisiert werden:", error);
    } finally {
        refreshPending = false;
    }
}

const BATCH_SIZE = 6;
let activeGallery = "works";
let shownCount = BATCH_SIZE;

const grid = document.getElementById("gallery-grid");
const toggleBtn = document.getElementById("gallery-toggle");
const categoryButtons = document.querySelectorAll("[data-gallery]");
const moreLabel = toggleBtn.querySelector('[data-action-label="more"]');
const lessLabel = toggleBtn.querySelector('[data-action-label="less"]');
const gallerySection = document.getElementById("gallery");
const visibleCount = document.getElementById("gallery-visible-count");
const totalCount = document.getElementById("gallery-total-count");

function scrollToGalleryStart() {
    gallerySection.scrollIntoView({ behavior: "smooth", block: "start" });
}

const GALLERY_LABELS = {
    works: "Kunst am Bau",
    sculptures: "Skulpturen",
};

function createImageItem(filename, path, label, index, allImages) {
    const item = document.createElement("div");
    item.className = "gallery-item";
    item.setAttribute("role", "button");
    item.tabIndex = 0;
    item.setAttribute("aria-label", `${label} – Werkansicht ${index + 1} vergrößern`);

    const img = document.createElement("img");
    img.src = path + filename;
    img.alt = `${label} – Werkansicht ${index + 1}`;
    img.loading = "lazy";

    item.appendChild(img);

    function openImage() {
        const images = allImages.map((imageFilename, imageIndex) => ({
            path,
            filename: imageFilename,
            alt: `${label} – Werkansicht ${imageIndex + 1}`,
        }));
        openLightbox(images, index);
    }

    item.addEventListener("click", openImage);
    item.addEventListener("keydown", event => {
        if (event.key === "Enter" || event.key === " ") {
            event.preventDefault();
            openImage();
        }
    });

    return item;
}

function renderGallery() {
    const gallery = galleries[activeGallery];
    const visibleImages = gallery.images.slice(0, shownCount);
    const label = GALLERY_LABELS[activeGallery] || "Galerie";

    grid.replaceChildren();
    visibleImages.forEach((filename, index) => {
        grid.appendChild(createImageItem(filename, gallery.path, label, index, gallery.images));
    });

    const isFullyExpanded = shownCount >= gallery.images.length;
    visibleCount.textContent = visibleImages.length;
    totalCount.textContent = gallery.images.length;
    toggleBtn.hidden = gallery.images.length <= BATCH_SIZE;
    moreLabel.hidden = isFullyExpanded;
    lessLabel.hidden = !isFullyExpanded;
}

function selectGallery(nextGallery) {
    activeGallery = nextGallery;
    shownCount = BATCH_SIZE;

    categoryButtons.forEach(button => {
        const isActive = button.dataset.gallery === activeGallery;
        button.classList.toggle("is-active", isActive);
        button.setAttribute("aria-pressed", String(isActive));
    });

    renderGallery();
    scrollToGalleryStart();
}

function toggleGallerySize() {
    const imageCount = galleries[activeGallery].images.length;

    if (shownCount >= imageCount) {
        shownCount = BATCH_SIZE;
        renderGallery();
        scrollToGalleryStart();
        return;
    }

    shownCount = Math.min(shownCount + BATCH_SIZE, imageCount);
    renderGallery();
}

categoryButtons.forEach(button => {
    button.addEventListener("click", () => selectGallery(button.dataset.gallery));
});

toggleBtn.addEventListener("click", toggleGallerySize);

renderGallery();
refreshGallery();
document.addEventListener("visibilitychange", refreshGallery);
window.addEventListener("focus", refreshGallery);
window.addEventListener("pageshow", event => {
    if (event.persisted) refreshGallery();
});
