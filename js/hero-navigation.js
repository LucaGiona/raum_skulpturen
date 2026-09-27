const intro = document.querySelector(".hero .intro");
const gallery = document.getElementById("gallery");
const reducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)");

if (intro && gallery) {
    let scrollFrame;

    function cancelScroll() {
        cancelAnimationFrame(scrollFrame);
    }

    function scrollToGallery() {
        cancelScroll();
        if (reducedMotion.matches) {
            gallery.scrollIntoView({ behavior: "instant", block: "start" });
            return;
        }

        const start = window.scrollY;
        const margin = parseFloat(getComputedStyle(gallery).scrollMarginTop) || 0;
        const maxScroll = Math.max(0, document.documentElement.scrollHeight - window.innerHeight);
        const target = Math.min(maxScroll, Math.max(0, start + gallery.getBoundingClientRect().top - margin));
        const startedAt = performance.now();
        const duration = 1400;

        function animate(now) {
            const progress = Math.min(1, (now - startedAt) / duration);
            const eased = (1 - Math.cos(Math.PI * progress)) / 2;
            window.scrollTo({ top: start + (target - start) * eased, behavior: "instant" });
            if (progress < 1) scrollFrame = requestAnimationFrame(animate);
        }

        scrollFrame = requestAnimationFrame(animate);
    }

    // Let visitors take over immediately when they scroll themselves.
    window.addEventListener("touchstart", cancelScroll, { passive: true });
    window.addEventListener("wheel", cancelScroll, { passive: true });
    window.addEventListener("keydown", event => {
        if (["ArrowUp", "ArrowDown", "PageUp", "PageDown", "Home", "End", " ", "Escape", "Tab"].includes(event.key)) cancelScroll();
    });
    window.addEventListener("resize", cancelScroll);
    reducedMotion.addEventListener("change", cancelScroll);

    intro.addEventListener("click", event => {
        const link = event.target.closest(".hero-gallery-link");
        if (!link || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
        event.preventDefault();
        scrollToGallery();
    });

}
