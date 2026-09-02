/* IFK — interacciones del sitio */
(function () {
  'use strict';

  var nav    = document.getElementById('nav');
  var menu   = document.getElementById('menu');
  var burger = document.getElementById('burger');

  /* Menú móvil */
  if (burger && menu && nav) {
    burger.addEventListener('click', function () {
      var abierto = menu.classList.toggle('is-open');
      burger.classList.toggle('is-open', abierto);
      document.body.classList.toggle('nav-stuck', abierto || window.scrollY > 40);
      burger.setAttribute('aria-expanded', abierto ? 'true' : 'false');
      burger.setAttribute('aria-label', abierto ? 'Cerrar menú' : 'Abrir menú');
      document.body.style.overflow = abierto ? 'hidden' : '';
    });

    document.addEventListener('keydown', function (ev) {
      if (ev.key === 'Escape' && menu.classList.contains('is-open')) burger.click();
    });
  }

  /* Fondo de la barra al hacer scroll (sólo sobre el hero transparente) */
  if (!document.body.classList.contains('nav-solida')) {
    var marcar = function () {
      document.body.classList.toggle('nav-stuck', window.scrollY > 40);
    };
    marcar();
    window.addEventListener('scroll', marcar, { passive: true });
  }

  /* Aparición suave de secciones.
     Sólo se activa si la pestaña está visible: así el contenido nunca
     queda oculto si el observador no llega a dispararse. */
  if ('IntersectionObserver' in window &&
      document.visibilityState === 'visible' &&
      !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    var obs = new IntersectionObserver(function (entradas) {
      entradas.forEach(function (en) {
        if (en.isIntersecting) { en.target.classList.add('vis'); obs.unobserve(en.target); }
      });
    }, { rootMargin: '0px 0px -12% 0px', threshold: 0.08 });

    var animables = document.querySelectorAll(
      '.card, .proy, .stat, .valor, .marcas__g, .split__txt, .split__media, .sec__head');
    animables.forEach(function (el) { el.classList.add('anim'); obs.observe(el); });

    /* Red de seguridad: si algo impide que el observador dispare, se muestra igual */
    setTimeout(function () {
      animables.forEach(function (el) { el.classList.add('vis'); });
    }, 4000);
  }
})();
