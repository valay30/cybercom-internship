
const minRange = document.getElementById('min-range-input');
const maxRange = document.getElementById('max-range-input');
const minDisp = document.getElementById('min-price-disp');
const maxDisp = document.getElementById('max-price-disp');
const hiddenMin = document.getElementById('hidden_min_price');
const hiddenMax = document.getElementById('hidden_max_price');
const track = document.querySelector('.slider-track');
const rangeMax = 15000;

function updateTrack() {
    const minPercent = (minRange.value / rangeMax) * 100;
    const maxPercent = (maxRange.value / rangeMax) * 100;
    track.style.background = `linear-gradient(to right, #ddd ${minPercent}%, #3498db ${minPercent}%, #3498db ${maxPercent}%, #ddd ${maxPercent}%)`;
}

minRange.addEventListener('input', () => {
    if (parseInt(minRange.value) > parseInt(maxRange.value) - 500) {
        minRange.value = parseInt(maxRange.value) - 500;
    }
    minDisp.textContent = parseInt(minRange.value).toLocaleString();
    hiddenMin.value = minRange.value;
    updateTrack();
});

maxRange.addEventListener('input', () => {
    if (parseInt(maxRange.value) < parseInt(minRange.value) + 500) {
        maxRange.value = parseInt(minRange.value) + 500;
    }
    maxDisp.textContent = parseInt(maxRange.value).toLocaleString() + (maxRange.value == 15000 ? '+' : '');
    hiddenMax.value = maxRange.value;
    updateTrack();
});

// Initialize
updateTrack();
