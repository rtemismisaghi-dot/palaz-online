import './bootstrap';


// PALAZ micro-interactions
window.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.swatches').forEach(group => {
    const visual = document.querySelector('[data-product-image]');
    group.querySelectorAll('.swatch').forEach(swatch => {
      swatch.addEventListener('click', () => {
        group.querySelectorAll('.swatch').forEach(x => x.classList.remove('selected'));
        swatch.classList.add('selected');
        if (visual) visual.style.backgroundPosition = ({'tone-a':'center','tone-b':'65% center','tone-c':'35% center','tone-d':'center 65%'})[swatch.dataset.visual] || 'center';
      });
    });
  });
});
