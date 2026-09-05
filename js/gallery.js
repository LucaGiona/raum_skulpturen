import { images } from "./data/images.js";

const BATCH_SIZE = 8;
let shownCount = 0;

const grid = document.getElementById("gallery-grid");
const loadMoreBtn = document.getElementById("load-more");

function showNextBatch() {
    const next = images.slice(shownCount, shownCount + BATCH_SIZE);
    next.forEach(filename => {
        const item = document.createElement("div");
        item.className = "gallery-item";

        const img = document.createElement("img");
        img.src = "/assets/images/" + filename;
        img.alt = filename;
        img.loading = "lazy";

        item.appendChild(img);
        grid.appendChild(item);
    });

    shownCount += next.length;

    if (shownCount >= images.length) {
        loadMoreBtn.style.display = "none";
    }
}

loadMoreBtn.addEventListener("click", showNextBatch);

showNextBatch();
