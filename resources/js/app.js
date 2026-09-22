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

  const slides = document.querySelectorAll('.hero-slide');
  const dots = document.querySelectorAll('.hero-slide-dots span');
  const counter = document.querySelector('.hero-number');
  const progress = document.querySelector('.hero-slide-progress span');
  const slideDuration = 5000;

  if (slides.length > 1 && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    let current = 0;

    const updateHeroMeta = () => {
      const number = String(current + 1).padStart(2, '0');
      if (counter) counter.textContent = number + ' — ' + String(slides.length).padStart(2, '0');
      if (progress) {
        progress.style.animation = 'none';
        void progress.offsetWidth;
        progress.style.animation = 'palazHeroProgress ' + slideDuration + 'ms linear forwards';
      }
    };

    updateHeroMeta();

    setInterval(() => {
      slides[current].classList.remove('is-active');
      if (dots[current]) dots[current].classList.remove('active');
      current = (current + 1) % slides.length;
      slides[current].classList.add('is-active');
      if (dots[current]) dots[current].classList.add('active');
      updateHeroMeta();
    }, slideDuration);
  }

  const mobileMenu = document.querySelector('.mobile-menu');
  const mainNav = document.querySelector('.main-nav');
  if (mobileMenu && mainNav) {
    mobileMenu.addEventListener('click', () => {
      const open = mainNav.classList.toggle('is-open');
      mobileMenu.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
  }


  // Shop catalog sorting / lightweight interaction
  const shopSort = document.querySelector('#shopSort');
  const shopProducts = document.querySelector('#shopProducts');

  if (shopSort && shopProducts) {
    shopSort.addEventListener('change', () => {
      const cards = Array.from(shopProducts.querySelectorAll('.shop-product'));
      if (shopSort.value === 'name') {
        cards.sort((a, b) => (a.dataset.productName || '').localeCompare(b.dataset.productName || '', 'fa'));
      } else {
        cards.sort((a, b) => Number(a.dataset.productIndex || 0) - Number(b.dataset.productIndex || 0));
      }

      cards.forEach((card, index) => {
        card.classList.remove('is-sorted');
        card.style.order = index;
        void card.offsetWidth;
        card.classList.add('is-sorted');
      });
    });
  }
});
