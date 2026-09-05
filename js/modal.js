const openLink = document.getElementById("impressum-link");
const modal = document.getElementById("impressum-modal");
const closeTriggers = modal.querySelectorAll("[data-modal-close]");

function openModal() {
    modal.hidden = false;
    document.body.style.overflow = "hidden";
}

function closeModal() {
    modal.hidden = true;
    document.body.style.overflow = "";
}

openLink.addEventListener("click", openModal);
closeTriggers.forEach(el => el.addEventListener("click", closeModal));

document.addEventListener("keydown", event => {
    if (event.key === "Escape" && !modal.hidden) {
        closeModal();
    }
});
