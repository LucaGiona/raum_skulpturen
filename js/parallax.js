const prefersReducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

function initParallax(bgSelector, sectionSelector, defaultAmplitude) {
    const bg = document.querySelector(bgSelector);
    const section = document.querySelector(sectionSelector);

    if (!bg || !section || prefersReducedMotion) return;

    let ticking = false;
    let amplitude = defaultAmplitude;

    const readAmplitude = () => {
        const value = getComputedStyle(section).getPropertyValue("--parallax-amplitude");
        amplitude = parseFloat(value) || amplitude;
    };

    const update = () => {
        ticking = false;

        const rect = section.getBoundingClientRect();
        const viewportHeight = window.innerHeight;

        const progress = (viewportHeight - rect.top) / (viewportHeight + rect.height);
        const clamped = Math.min(1, Math.max(0, progress));

        const offset = (clamped - 0.5) * amplitude;
        bg.style.transform = `translateY(${offset.toFixed(1)}px)`;
    };

    const onScroll = () => {
        if (!ticking) {
            ticking = true;
            requestAnimationFrame(update);
        }
    };

    const onResize = () => {
        readAmplitude();
        onScroll();
    };

    window.addEventListener("scroll", onScroll, { passive: true });
    window.addEventListener("resize", onResize);
    readAmplitude();
    update();
}

initParallax(".contact-bg", ".contact", 60);
initParallax(".guestbook-bg-img", ".guestbook", 50);
