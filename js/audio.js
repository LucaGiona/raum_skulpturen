const toggleBtn = document.getElementById("audio-toggle");
const iconOn = toggleBtn.querySelector('[data-icon="on"]');
const iconOff = toggleBtn.querySelector('[data-icon="off"]');
const player = document.getElementById("site-audio");

let isOn = false;

function applyState() {
    iconOn.hidden = !isOn;
    iconOff.hidden = isOn;
    toggleBtn.setAttribute("aria-pressed", String(isOn));
    toggleBtn.setAttribute("aria-label", isOn ? "Ton ausschalten" : "Ton einschalten");
}

function startPlayback() {
    return player.play().then(() => {
        isOn = true;
        applyState();
    });
}

function stopPlayback() {
    player.pause();
    isOn = false;
    applyState();
}

toggleBtn.addEventListener("click", () => {
    if (isOn) {
        stopPlayback();
    } else {
        startPlayback().catch(() => {
            isOn = false;
            applyState();
        });
    }
});

// Browsers block autoplay with sound. Try immediately; if blocked,
// start on the very first user interaction with the page instead.
const retryEvents = ["pointerdown", "keydown", "touchstart"];

function retryOnFirstInteraction() {
    startPlayback().catch(() => {});
    retryEvents.forEach(type => document.removeEventListener(type, retryOnFirstInteraction));
}

startPlayback().catch(() => {
    retryEvents.forEach(type => document.addEventListener(type, retryOnFirstInteraction, { once: true }));
});

applyState();
