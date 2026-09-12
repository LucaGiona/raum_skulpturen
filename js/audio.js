const toggleBtn = document.getElementById("audio-toggle");
const pauseIcon = toggleBtn.querySelector('[data-icon="pause"]');
const playIcon = toggleBtn.querySelector('[data-icon="play"]');
const player = document.getElementById("site-audio");

let isOn = false;

function applyState() {
    pauseIcon.toggleAttribute("hidden", !isOn);
    playIcon.toggleAttribute("hidden", isOn);
    toggleBtn.setAttribute("aria-pressed", String(isOn));
    const english = document.documentElement.lang === "en";
    toggleBtn.setAttribute("aria-label", english
        ? (isOn ? "Pause music" : "Play music")
        : (isOn ? "Musik pausieren" : "Musik abspielen"));
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

applyState();

document.addEventListener("site-language-change", applyState);
