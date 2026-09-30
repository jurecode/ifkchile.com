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

  /* La cinta de marcas.
     Corre sola, despacio, y se detiene apenas alguien la toca: con el dedo,
     con el trackpad, con las flechas o pasando el mouse por encima. Después
     de unos segundos sin que la toquen, vuelve a andar.

     La fila de logotipos está dos veces en el HTML: cuando la cinta llega a
     la mitad, se devuelve esa mitad y el salto no se ve, así que nunca hay
     un final incómodo. */
  document.querySelectorAll('.cinta').forEach(function (cinta) {
    var pista = cinta.querySelector('.cinta__pista');
    var izq   = cinta.querySelector('.cinta__flecha--izq');
    var der   = cinta.querySelector('.cinta__flecha--der');
    if (!pista) return;

    var quieto = window.matchMedia('(prefers-reduced-motion: reduce)');
    var conMouse = window.matchMedia('(hover: hover)');
    var mitad = 0, pausaHasta = 0, anterior = 0, tocada = false;

    function medir() {
      mitad = pista.scrollWidth / 2;
      /* Si las marcas caben todas, las flechas y el movimiento sobran. */
      cinta.classList.toggle('cinta--completa', mitad <= pista.clientWidth + 4);
    }
    medir();
    window.addEventListener('resize', medir);
    /* Los logotipos se cargan de a poco: al terminar, la mitad es otra. */
    window.addEventListener('load', medir);

    function pausar(ms) { pausaHasta = Date.now() + ms; }
    function paso()     { return Math.max(200, pista.clientWidth * 0.8); }

    /* Mientras la manipulan, quieta. */
    ['pointerdown', 'touchstart', 'wheel'].forEach(function (ev) {
      pista.addEventListener(ev, function () { tocada = true; pausar(5000); }, { passive: true });
    });
    pista.addEventListener('touchend', function () { pausar(3000); }, { passive: true });
    if (conMouse.matches) {
      cinta.addEventListener('mouseenter', function () { pausar(3600000); });
      cinta.addEventListener('mouseleave', function () { pausar(400); });
    }
    cinta.addEventListener('focusin',  function () { pausar(3600000); });
    cinta.addEventListener('focusout', function () { pausar(1500); });

    if (izq) izq.addEventListener('click', function () {
      pausar(6000); pista.scrollBy({ left: -paso(), behavior: 'smooth' });
    });
    if (der) der.addEventListener('click', function () {
      pausar(6000); pista.scrollBy({ left: paso(), behavior: 'smooth' });
    });

    function marco(ahora) {
      var dt = anterior ? Math.min(64, ahora - anterior) : 16;
      anterior = ahora;

      if (!document.hidden && !quieto.matches && mitad > pista.clientWidth &&
          Date.now() >= pausaHasta) {
        pista.scrollLeft += dt * 0.028;          // unos 28 píxeles por segundo
      }
      /* La vuelta. Hacia atrás sólo después de que alguien la haya arrastrado:
         al cargar, la cinta parte en cero y sin esto saltaría de inmediato a
         la mitad. */
      if (mitad > 10) {
        if (pista.scrollLeft >= mitad) { pista.scrollLeft -= mitad; medir(); }
        else if (tocada && pista.scrollLeft <= 0) pista.scrollLeft += mitad;
      }
      requestAnimationFrame(marco);
    }
    requestAnimationFrame(marco);
  });

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
