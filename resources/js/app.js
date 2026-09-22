import './bootstrap';
import '../css/palaz-dynamic.css';

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

  const homeSections = document.querySelectorAll('.hero-copy, .hero-art, .intro-section > .container > *, .section-head, .category-card, .service-card, .product-card, .project-grid > *');
  homeSections.forEach((el, index) => {
    el.classList.add('reveal-item');
    el.style.transitionDelay = Math.min(index * 35, 280) + 'ms';
  });

  const reveal = () => {
    document.querySelectorAll('.reveal-item').forEach(el => {
      if (el.getBoundingClientRect().top < window.innerHeight * .9) el.classList.add('is-visible');
    });
  };
  reveal();
  window.addEventListener('scroll', reveal, {passive:true});

  const art = document.querySelector('.hero-art');
  if (art && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    window.addEventListener('pointermove', (event) => {
      const rect = art.getBoundingClientRect();
      if (rect.top > window.innerHeight || rect.bottom < 0) return;
      const x = ((event.clientX - rect.left) / rect.width - .5) * 8;
      const y = ((event.clientY - rect.top) / rect.height - .5) * 6;
      art.style.transform = 'translate3d(' + x + 'px,' + y + 'px,0)';
    }, {passive:true});
    window.addEventListener('pointerleave', () => { art.style.transform = ''; });
  }
});