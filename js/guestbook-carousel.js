import { galleries } from "./data/images.js";

const gallery = galleries.guestbook;
const realCount = gallery.images.length;

// Track layout: [clone-of-last, real 0..N-1, clone-of-first]
// "ext" (extended index) addresses this track directly; ext = realIndex + 1.
let currentIndex = 0;
let slideStep = 0;
let isDragging = false;
let dragStartX = 0;
let dragDeltaX = 0;
let baseOffset = 0;

const viewport = document.getElementById("guestbook-carousel-viewport");
const track = document.getElementById("guestbook-carousel-track");
const indexLabel = document.getElementById("guestbook-carousel-index");
const totalLabel = document.getElementById("guestbook-carousel-total");
const prevBtn = document.querySelector(".guestbook-carousel-prev");
const nextBtn = document.querySelector(".guestbook-carousel-next");

function createSlide(realIndex, ext) {
    const slide = document.createElement("div");
    slide.className = "guestbook-carousel-slide";
    slide.dataset.ext = String(ext);

    const img = document.createElement("img");
    img.src = gallery.path + gallery.images[realIndex];
    img.alt = `Gästebuch – Eintrag ${realIndex + 1}`;
    img.loading = realIndex === 0 ? "eager" : "lazy";
    img.draggable = false;

    slide.appendChild(img);
    slide.addEventListener("click", () => {
        if (Math.abs(dragDeltaX) > 5) return;
        const activeExt = currentIndex + 1;
        if (ext === activeExt - 1) showPrev();
        else if (ext === activeExt + 1) showNext();
    });

    return slide;
}

function buildSlides() {
    track.replaceChildren();
    const order = [realCount - 1, ...gallery.images.map((_, i) => i), 0];
    order.forEach((realIndex, ext) => track.appendChild(createSlide(realIndex, ext)));
}

// Slides overlap via negative margin (peek images sit partly behind the
// active one), so spacing between slides isn't uniform in a simple
// width+gap sense. offsetLeft/offsetWidth are layout-box values - unaffected
// by the scale()/margin visuals - so measuring the real rendered position of
// each slide (rather than assuming a fixed step) keeps centering exact.
function measureStep() {
    const a = track.children[0];
    const b = track.children[1];
    if (!a || !b) return 0;
    return b.offsetLeft - a.offsetLeft;
}

function offsetForExt(ext) {
    const targetSlide = track.children[ext];
    if (!targetSlide) return 0;
    const slideCenter = targetSlide.offsetLeft + targetSlide.offsetWidth / 2;
    const viewportCenter = viewport.offsetLeft + viewport.offsetWidth / 2;
    return viewportCenter - slideCenter;
}

function updateActive(ext) {
    [...track.children].forEach((slide, i) => {
        slide.classList.toggle("is-active", i === ext);
    });
}

function animateTo(ext, onDone) {
    slideStep = measureStep();
    baseOffset = offsetForExt(ext);
    track.style.transform = `translateX(${baseOffset}px)`;
    if (onDone) {
        track.addEventListener("transitionend", onDone, { once: true });
    }
}

function jumpTo(ext) {
    slideStep = measureStep();
    baseOffset = offsetForExt(ext);
    track.style.transition = "none";
    track.style.transform = `translateX(${baseOffset}px)`;
    track.getBoundingClientRect();
    track.style.transition = "";
}

function showPrev() {
    if (currentIndex === 0) {
        currentIndex = realCount - 1;
        indexLabel.textContent = currentIndex + 1;
        updateActive(0);
        animateTo(0, () => {
            updateActive(realCount);
            jumpTo(realCount);
        });
    } else {
        currentIndex -= 1;
        indexLabel.textContent = currentIndex + 1;
        updateActive(currentIndex + 1);
        animateTo(currentIndex + 1);
    }
}

function showNext() {
    if (currentIndex === realCount - 1) {
        currentIndex = 0;
        indexLabel.textContent = 1;
        updateActive(realCount + 1);
        animateTo(realCount + 1, () => {
            updateActive(1);
            jumpTo(1);
        });
    } else {
        currentIndex += 1;
        indexLabel.textContent = currentIndex + 1;
        updateActive(currentIndex + 1);
        animateTo(currentIndex + 1);
    }
}

prevBtn.addEventListener("click", showPrev);
nextBtn.addEventListener("click", showNext);

document.addEventListener("keydown", event => {
    if (event.key === "ArrowLeft") showPrev();
    if (event.key === "ArrowRight") showNext();
});

function onPointerDown(event) {
    isDragging = true;
    dragStartX = event.clientX;
    dragDeltaX = 0;
    slideStep = measureStep();
    baseOffset = offsetForExt(currentIndex + 1);
    track.classList.add("is-dragging");
    track.setPointerCapture(event.pointerId);
}

function onPointerMove(event) {
    if (!isDragging) return;
    dragDeltaX = event.clientX - dragStartX;
    track.style.transform = `translateX(${baseOffset + dragDeltaX}px)`;
}

function onPointerUp() {
    if (!isDragging) return;
    isDragging = false;
    track.classList.remove("is-dragging");

    const threshold = slideStep * 0.18;
    if (dragDeltaX > threshold) {
        showPrev();
    } else if (dragDeltaX < -threshold) {
        showNext();
    } else {
        animateTo(currentIndex + 1);
    }
}

track.addEventListener("pointerdown", onPointerDown);
track.addEventListener("pointermove", onPointerMove);
track.addEventListener("pointerup", onPointerUp);
track.addEventListener("pointercancel", onPointerUp);

window.addEventListener("resize", () => {
    if (!isDragging) jumpTo(currentIndex + 1);
});

totalLabel.textContent = realCount;
indexLabel.textContent = currentIndex + 1;
buildSlides();
updateActive(currentIndex + 1);
jumpTo(currentIndex + 1);
