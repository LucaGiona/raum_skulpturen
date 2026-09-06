const STORAGE_KEY = "site-theme";
const DEFAULT_THEME = "dark";

const toggleBtn = document.getElementById("theme-toggle");
const sunIcon = toggleBtn.querySelector('[data-theme-icon="sun"]');
const moonIcon = toggleBtn.querySelector('[data-theme-icon="moon"]');

function applyTheme(theme) {
    document.documentElement.dataset.theme = theme;
    const isDark = theme === "dark";
    sunIcon.hidden = !isDark;
    moonIcon.hidden = isDark;
    toggleBtn.setAttribute("aria-label", isDark ? "Helles Farbschema aktivieren" : "Dunkles Farbschema aktivieren");
    localStorage.setItem(STORAGE_KEY, theme);
}

function init() {
    const saved = localStorage.getItem(STORAGE_KEY) || DEFAULT_THEME;
    applyTheme(saved);

    toggleBtn.addEventListener("click", () => {
        const next = document.documentElement.dataset.theme === "dark" ? "light" : "dark";
        applyTheme(next);
    });
}

init();
