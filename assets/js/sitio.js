/* IFK — tres cosas: la barra al bajar, el menú del teléfono y la aparición
   de las secciones. Nada más; si el navegador no ejecuta esto, el sitio
   se sigue leyendo igual. */
(function () {
  'use strict';

  /* Con esta marca el CSS se atreve a esconder lo que va a aparecer después.
     Si este archivo no llegara a ejecutarse, el sitio se ve completo igual. */
  document.documentElement.classList.add('js');

  var nav = document.getElementById('nav');
  var burger = document.getElementById('burger');

  /* La barra se pinta cuando la portada deja de estar arriba. */
  function pintar() {
    if (!nav) return;
    nav.classList.toggle('solida', window.scrollY > 24 || nav.dataset.abierto === 'si');
  }
  pintar();
  window.addEventListener('scroll', pintar, { passive: true });

  /* Menú del teléfono. */
  if (burger && nav) {
    burger.addEventListener('click', function () {
      var abierto = nav.dataset.abierto === 'si';
      nav.dataset.abierto = abierto ? 'no' : 'si';
      burger.setAttribute('aria-expanded', String(!abierto));
      burger.setAttribute('aria-label', abierto ? 'Abrir el menú' : 'Cerrar el menú');
      pintar();
    });
    /* Al elegir una página, el menú se cierra solo. */
    nav.querySelectorAll('.nav__menu a').forEach(function (a) {
      a.addEventListener('click', function () {
        nav.dataset.abierto = 'no';
        burger.setAttribute('aria-expanded', 'false');
      });
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && nav.dataset.abierto === 'si') burger.click();
    });
  }

  /* Las secciones aparecen al llegar a ellas. */
  var piezas = document.querySelectorAll('.revelar');
  if (!('IntersectionObserver' in window)) {
    piezas.forEach(function (p) { p.classList.add('visible'); });
    return;
  }
  var vigia = new IntersectionObserver(function (entradas) {
    entradas.forEach(function (entrada) {
      if (!entrada.isIntersecting) return;
      entrada.target.classList.add('visible');
      vigia.unobserve(entrada.target);
    });
  }, { rootMargin: '0px 0px -12% 0px', threshold: 0.05 });
  piezas.forEach(function (p) { vigia.observe(p); });
})();
