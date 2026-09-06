const form = document.getElementById("contact-form");

let translations = {};

fetch("/js/data/translations.json")
    .then(res => res.json())
    .then(data => { translations = data; })
    .catch(() => {});

form.addEventListener("submit", event => {
    event.preventDefault();

    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    const firstname = form.elements.firstname.value.trim();
    const lang = document.documentElement.lang === "en" ? "en" : "de";
    const template = translations[lang]?.contactAlert
        ?? "Danke, {firstname}! Deine Nachricht wurde erfasst. Der Versand ans Team wird bald freigeschaltet.";

    alert(template.replace("{firstname}", firstname));

    form.reset();
});
