/* Minimal theme — progressive enhancements (optional).
   The theme works fully without this file; it only adds subtle niceties. */
(function () {
  'use strict';

  // Add a subtle shadow / solidify header on scroll.
  var header = document.getElementById('appHeader');
  if (header) {
    var onScroll = function () {
      if (window.scrollY > 8) {
        header.style.boxShadow = '0 1px 0 rgba(0,0,0,.06)';
      } else {
        header.style.boxShadow = 'none';
      }
    };
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
  }

  // Lightweight fade-in-on-scroll for sections (respects reduced motion).
  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  if ('IntersectionObserver' in window && !reduce) {
    var targets = document.querySelectorAll('.mn-section, .mn-cat-banner, .mn-collection-grid .item');
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.style.opacity = '1';
          entry.target.style.transform = 'none';
          io.unobserve(entry.target);
        }
      });
    }, { threshold: 0.08 });
    targets.forEach(function (el) {
      el.style.opacity = '0';
      el.style.transform = 'translateY(16px)';
      el.style.transition = 'opacity .7s ease, transform .7s ease';
      io.observe(el);
    });
  }
})();
