const slider = document.querySelector('.slider');
const container = document.querySelector('.slider-container');

slider.addEventListener('input', (e) => {
  container.style.setProperty('--position', `${e.target.value}%`);
});