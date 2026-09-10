const lightbox = document.getElementById("lightbox");
const lightboxImage = document.getElementById("lightbox-image");
const closeTriggers = lightbox.querySelectorAll("[data-lightbox-close]");
const prevBtn = lightbox.querySelector(".lightbox-prev");
const nextBtn = lightbox.querySelector(".lightbox-next");

let currentImages = [];
let currentIndex = 0;
let touchStartX = null;

function renderCurrent() {
    const { path, filename, alt } = currentImages[currentIndex];
    lightboxImage.src = path + filename;
    lightboxImage.alt = alt;

    const hasMultiple = currentImages.length > 1;
    prevBtn.hidden = !hasMultiple;
    nextBtn.hidden = !hasMultiple;
}

function close() {
    lightbox.hidden = true;
    lightboxImage.src = "";
    document.body.style.overflow = "";
}

function showPrev() {
    currentIndex = (currentIndex - 1 + currentImages.length) % currentImages.length;
    renderCurrent();
}

function showNext() {
    currentIndex = (currentIndex + 1) % currentImages.length;
    renderCurrent();
}

export function openLightbox(images, index) {
    currentImages = images;
    currentIndex = index;
    renderCurrent();
    lightbox.hidden = false;
    document.body.style.overflow = "hidden";
}

closeTriggers.forEach(el => el.addEventListener("click", close));
prevBtn.addEventListener("click", showPrev);
nextBtn.addEventListener("click", showNext);

document.addEventListener("keydown", event => {
    if (lightbox.hidden) return;
    if (event.key === "Escape") close();
    if (event.key === "ArrowLeft") showPrev();
    if (event.key === "ArrowRight") showNext();
});

lightbox.addEventListener("touchstart", event => {
    touchStartX = event.changedTouches[0].clientX;
}, { passive: true });

lightbox.addEventListener("touchend", event => {
    if (touchStartX === null) return;
    const deltaX = event.changedTouches[0].clientX - touchStartX;
    if (Math.abs(deltaX) > 40) {
        deltaX > 0 ? showPrev() : showNext();
    }
    touchStartX = null;
}, { passive: true });
