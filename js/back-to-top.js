const button = document.getElementById("back-to-top");

const SHOW_AFTER = 400;

function updateVisibility() {
    button.hidden = window.scrollY < SHOW_AFTER;
}

window.addEventListener("scroll", updateVisibility, { passive: true });
window.addEventListener("resize", updateVisibility);
updateVisibility();

button.addEventListener("click", () => {
    window.scrollTo({ top: 0, behavior: "smooth" });
});
