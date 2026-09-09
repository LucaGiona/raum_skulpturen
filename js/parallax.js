const bg = document.querySelector(".contact-bg");
const section = document.querySelector(".contact");

const prefersReducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

const stucco = document.querySelector(".stucco-feature");
const stuccoImage = stucco?.querySelector("img");

if (stuccoImage && !prefersReducedMotion) {
    let scheduled = false;

    const updateStucco = () => {
        scheduled = false;
        const rect = stucco.getBoundingClientRect();
        const viewportHeight = window.innerHeight;
        const progress = Math.min(1, Math.max(0,
            (viewportHeight - rect.top) / (viewportHeight + rect.height)));
        // Keep the slower-moving image inside its frame at either scroll extreme.
        const offset = (progress - 0.5) * rect.height * 0.14;
        stuccoImage.style.setProperty("--stucco-offset", `${offset.toFixed(1)}px`);
    };

    const scheduleStucco = () => {
        if (!scheduled) {
            scheduled = true;
            requestAnimationFrame(updateStucco);
        }
    };

    window.addEventListener("scroll", scheduleStucco, { passive: true });
    window.addEventListener("resize", scheduleStucco);
    updateStucco();
}

if (bg && section && !prefersReducedMotion) {
    let ticking = false;
    let amplitude = 60;

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
