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
    localStorage.setItem(STORAGE_KEY, lang);
}

async function init() {
    const res = await fetch("/js/data/translations.json");
    translations = await res.json();

    const saved = localStorage.getItem(STORAGE_KEY) || DEFAULT_LANG;
    applyLanguage(saved);

    toggleBtn.addEventListener("click", () => {
        const next = toggleBtn.dataset.lang === "de" ? "en" : "de";
        applyLanguage(next);
    });
}

init();
