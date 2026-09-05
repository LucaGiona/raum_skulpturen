const form = document.getElementById("contact-form");

form.addEventListener("submit", event => {
    event.preventDefault();

    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    const firstname = form.elements.firstname.value.trim();

    alert(`Danke, ${firstname}! Deine Nachricht wurde erfasst. Der Versand ans Team wird bald freigeschaltet.`);

    form.reset();
});
