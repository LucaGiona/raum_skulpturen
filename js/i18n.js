const STORAGE_KEY = "site-lang";
const DEFAULT_LANG = "de";

const toggleBtn = document.getElementById("lang-toggle");

let translations = {};

function applyLanguage(lang) {
    document.querySelectorAll("[data-i18n]").forEach(el => {
        const value = translations[lang]?.[el.dataset.i18n];
        if (value) {
            el.textContent = value;
        }
    });

    document.documentElement.lang = lang;
    toggleBtn.textContent = lang === "de" ? "EN" : "DE";
    toggleBtn.dataset.lang = lang;
    toggleBtn.setAttribute("aria-label", lang === "de" ? "Switch to English" : "Auf Deutsch umschalten");
    document.dispatchEvent(new Event("site-language-change"));
}

async function init() {
    const res = await fetch("/js/data/translations.json");
    translations = await res.json();

    let saved = DEFAULT_LANG;
    try { saved = localStorage.getItem(STORAGE_KEY) || saved; } catch { /* Ohne Browser-Speicher nutzbar. */ }
    applyLanguage(saved);

    toggleBtn.addEventListener("click", () => {
        const next = toggleBtn.dataset.lang === "de" ? "en" : "de";
        applyLanguage(next);
        try { localStorage.setItem(STORAGE_KEY, next); } catch { /* Speicherung optional. */ }
    });
}

init();
