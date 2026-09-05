const STORAGE_KEY = "site-theme";
const DEFAULT_THEME = "dark";

const toggleBtn = document.getElementById("theme-toggle");

function applyTheme(theme) {
    document.documentElement.dataset.theme = theme;
    toggleBtn.textContent = theme === "dark" ? "☀️" : "🌙";
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
