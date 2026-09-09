import { galleries } from "./data/images.js";

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

function createImageItem(filename, path, label, index) {
    const item = document.createElement("div");
    item.className = "gallery-item";

    const img = document.createElement("img");
    img.src = path + filename;
    img.alt = `${label} – Werkansicht ${index + 1}`;
    img.loading = "lazy";

    item.appendChild(img);
    return item;
}

function renderGallery() {
    const gallery = galleries[activeGallery];
    const visibleImages = gallery.images.slice(0, shownCount);
    const label = GALLERY_LABELS[activeGallery] || "Galerie";

    grid.replaceChildren();
    visibleImages.forEach((filename, index) => {
        grid.appendChild(createImageItem(filename, gallery.path, label, index));
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
