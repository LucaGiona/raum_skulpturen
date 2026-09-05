const toggleBtn = document.getElementById("audio-toggle");
const iconOn = toggleBtn.querySelector('[data-icon="on"]');
const iconOff = toggleBtn.querySelector('[data-icon="off"]');

let isOn = false;

function applyState() {
    iconOn.hidden = !isOn;
    iconOff.hidden = isOn;
    toggleBtn.setAttribute("aria-pressed", String(isOn));
    toggleBtn.setAttribute("aria-label", isOn ? "Ton ausschalten" : "Ton einschalten");
}

toggleBtn.addEventListener("click", () => {
    isOn = !isOn;
    applyState();
});

applyState();
