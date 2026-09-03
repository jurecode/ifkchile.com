/* Panel IFK — subida de imágenes arrastrando y soltando */
(function () {
  'use strict';
  document.body.classList.add('js');

  var TIPOS = ['image/jpeg', 'image/png', 'image/webp'];
  var MAX   = 12 * 1024 * 1024;

  /* Evita que el navegador abra el archivo si se suelta fuera de una zona */
  ['dragover', 'drop'].forEach(function (ev) {
    window.addEventListener(ev, function (e) { e.preventDefault(); }, false);
  });

  document.querySelectorAll('.img-card').forEach(function (card) {
    var form   = card.querySelector('form');
    var zona   = card.querySelector('.zona');
    var input  = card.querySelector('input[type=file]');
    var img    = card.querySelector('.zona__img');
    var barra  = card.querySelector('.zona__barra i');
    var estado = card.querySelector('.img-card__estado');
    var peso   = card.querySelector('.peso');
    if (!zona || !input) return;

    /* Clic y teclado abren el selector de archivos */
    zona.addEventListener('click', function () { input.click(); });
    zona.addEventListener('keydown', function (e) {
      if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); input.click(); }
    });

    input.addEventListener('change', function () {
      if (input.files && input.files[0]) subir(input.files[0]);
    });

    /* Arrastrar y soltar */
    var dentro = 0;
    zona.addEventListener('dragenter', function (e) {
      e.preventDefault(); dentro++; zona.classList.add('is-drag');
    });
    zona.addEventListener('dragover', function (e) { e.preventDefault(); });
    zona.addEventListener('dragleave', function () {
      dentro--; if (dentro <= 0) { dentro = 0; zona.classList.remove('is-drag'); }
    });
    zona.addEventListener('drop', function (e) {
      e.preventDefault(); e.stopPropagation();
      dentro = 0; zona.classList.remove('is-drag');
      var f = e.dataTransfer && e.dataTransfer.files ? e.dataTransfer.files[0] : null;
      if (f) subir(f);
    });

    function decir(texto, tipo) {
      estado.textContent = texto || '';
      estado.className = 'img-card__estado' + (tipo ? ' es-' + tipo : '');
    }

    function subir(archivo) {
      if (TIPOS.indexOf(archivo.type) === -1) {
        decir('Formato no permitido: usa JPG, PNG o WebP.', 'error');
        zona.classList.add('is-error'); setTimeout(function(){ zona.classList.remove('is-error'); }, 1200);
        return;
      }
      if (archivo.size > MAX) {
        decir('La imagen pesa ' + Math.round(archivo.size / 1048576) + ' MB; el máximo son 12 MB.', 'error');
        return;
      }

      /* Vista previa inmediata (se revierte si falla) */
      var anterior = img ? img.src : null;
      var previa   = URL.createObjectURL(archivo);
      if (img) img.src = previa;

      zona.classList.add('is-subiendo');
      barra.style.width = '0%';
      decir('Subiendo…', 'proceso');

      var datos = new FormData(form);
      datos.set('archivo', archivo);
      datos.set('ajax', '1');

      var xhr = new XMLHttpRequest();
      xhr.open('POST', form.getAttribute('action'), true);
      xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

      xhr.upload.addEventListener('progress', function (e) {
        if (e.lengthComputable) barra.style.width = Math.round((e.loaded / e.total) * 92) + '%';
      });

      xhr.onload = function () {
        barra.style.width = '100%';
        var r = null;
        try { r = JSON.parse(xhr.responseText); } catch (err) { r = null; }

        setTimeout(function () {
          zona.classList.remove('is-subiendo');
          barra.style.width = '0%';
        }, 350);

        if (!r) {
          revertir('El panel respondió algo inesperado. Recarga la página e inténtalo de nuevo.');
          return;
        }
        if (!r.ok) { revertir(r.msg || 'No se pudo actualizar la imagen.'); return; }

        if (img && r.src) img.src = r.src;         // versión ya recortada por el servidor
        if (peso && r.peso) peso.textContent = r.peso;
        URL.revokeObjectURL(previa);
        input.value = '';
        decir('Imagen actualizada', 'ok');
        zona.classList.add('is-ok');
        setTimeout(function () { zona.classList.remove('is-ok'); }, 1600);
        setTimeout(function () { if (estado.classList.contains('es-ok')) decir(''); }, 4000);
      };

      xhr.onerror = function () { revertir('Se cortó la conexión con el servidor.'); };
      xhr.send(datos);

      function revertir(mensaje) {
        if (img && anterior) img.src = anterior;
        URL.revokeObjectURL(previa);
        input.value = '';
        zona.classList.remove('is-subiendo');
        zona.classList.add('is-error');
        setTimeout(function () { zona.classList.remove('is-error'); }, 1400);
        decir(mensaje, 'error');
      }
    }
  });
})();
