const trigger = document.querySelector(".bio");
const button = document.getElementById("back-to-top");

const EARLY_OFFSET = 400;

function updateVisibility() {
    const triggerTop = trigger.getBoundingClientRect().top + window.scrollY;
    button.hidden = window.scrollY < triggerTop - EARLY_OFFSET;
}

window.addEventListener("scroll", updateVisibility, { passive: true });
window.addEventListener("resize", updateVisibility);
updateVisibility();

button.addEventListener("click", () => {
    window.scrollTo({ top: 0, behavior: "smooth" });
});
