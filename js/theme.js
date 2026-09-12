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
    const english = document.documentElement.lang === "en";
    toggleBtn.setAttribute("aria-label", english
        ? (isDark ? "Activate light theme" : "Activate dark theme")
        : (isDark ? "Helles Farbschema aktivieren" : "Dunkles Farbschema aktivieren"));
}

function init() {
    let saved = DEFAULT_THEME;
    try { saved = localStorage.getItem(STORAGE_KEY) || saved; } catch { /* Ohne Browser-Speicher nutzbar. */ }
    applyTheme(saved);

    toggleBtn.addEventListener("click", () => {
        const next = document.documentElement.dataset.theme === "dark" ? "light" : "dark";
        applyTheme(next);
        try { localStorage.setItem(STORAGE_KEY, next); } catch { /* Speicherung optional. */ }
    });
}

document.addEventListener("site-language-change", () => applyTheme(document.documentElement.dataset.theme || DEFAULT_THEME));
init();
