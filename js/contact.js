const form = document.getElementById("contact-form");
const messageBox = document.getElementById("contact-form-message");

let translations = {};

fetch("/js/data/translations.json")
    .then(res => res.json())
    .then(data => { translations = data; })
    .catch(() => {});

function t(key, fallback) {
    const lang = document.documentElement.lang === "en" ? "en" : "de";
    return translations[lang]?.[key] ?? fallback;
}

function showMessage(text, isError) {
    messageBox.textContent = text;
    messageBox.hidden = false;
    messageBox.className = "contact-form-message " + (isError ? "contact-error" : "contact-success");
    messageBox.setAttribute("role", isError ? "alert" : "status");
    messageBox.scrollIntoView({ behavior: "smooth", block: "nearest" });
}

form.addEventListener("submit", async event => {
    event.preventDefault();

    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    const submitButton = form.querySelector(".contact-submit");
    submitButton.disabled = true;

    try {
        const response = await fetch("/contact.php", {
            method: "POST",
            headers: { "X-Requested-With": "fetch" },
            body: new FormData(form)
        });

        const data = await response.json();

        if (data.success) {
            const firstname = form.elements.firstname.value.trim();
            const template = t("contactAlert", "Danke, {firstname}! Deine Nachricht wurde verschickt.");
            showMessage(template.replace("{firstname}", firstname), false);
            form.reset();
        } else {
            const errorText = Object.values(data.errors || {}).join(" ")
                || t("contactErrorGeneric", "Beim Senden ist ein Fehler aufgetreten. Bitte versuche es später erneut.");
            showMessage(errorText, true);
        }
    } catch (err) {
        showMessage(
            t("contactErrorGeneric", "Beim Senden ist ein Fehler aufgetreten. Bitte versuche es später erneut."),
            true
        );
    } finally {
        submitButton.disabled = false;
    }
});
