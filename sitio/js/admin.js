/**
 * admin.js — Panel de administración FRANZA (rediseño 2026)
 * Listado: filtros, búsqueda, ordenar arrastrando, destacar y eliminar.
 * Editor visual: campos en el lugar, pasos, galería (subir, portada, ordenar, borrar),
 * vista previa y guardado. Sin dependencias.
 */
(function () {
  'use strict';
  function $(s, c) { return (c || document).querySelector(s); }
  function $$(s, c) { return Array.prototype.slice.call((c || document).querySelectorAll(s)); }

  var toastEl = $('[data-toast]'), toastT = null;
  function toast(msj, error) {
    if (!toastEl) {
      toastEl = document.createElement('div'); toastEl.className = 'adm-toast'; toastEl.setAttribute('role', 'status');
      document.body.appendChild(toastEl);
    }
    toastEl.textContent = msj; toastEl.classList.toggle('is-error', !!error); toastEl.hidden = false;
    clearTimeout(toastT); toastT = setTimeout(function () { toastEl.hidden = true; }, 3200);
  }

  /* =========================================================
     LISTADO DE OBRAS
     ========================================================= */
  var lista = $('[data-obras]');
  if (lista) {
    var csrf = lista.dataset.csrf, grilla = $('[data-grilla]', lista);
    var cards = grilla ? $$('.adm-card', grilla) : [];
    var filtro = 'todas', busqueda = '', ayuda = $('[data-ayuda-orden]');
    var puedeOrdenar = cards.length && cards[0].hasAttribute('draggable');

    function aplicarFiltros() {
      var visibles = 0;
      cards.forEach(function (c) {
        var ok = (filtro === 'todas' || c.dataset.tipo === filtro) && (!busqueda || c.dataset.texto.indexOf(busqueda) !== -1);
        c.hidden = !ok; if (ok) visibles++;
        if (puedeOrdenar) c.setAttribute('draggable', filtro === 'todas' && !busqueda ? 'true' : 'false');
      });
      var sin = $('[data-sin-resultados]'); if (sin) sin.hidden = visibles > 0;
      if (ayuda) ayuda.classList.toggle('is-oculta', filtro !== 'todas' || !!busqueda);
    }
    $$('[data-filtro]', lista).forEach(function (b) {
      b.addEventListener('click', function () {
        filtro = b.dataset.filtro;
        $$('[data-filtro]', lista).forEach(function (x) { x.setAttribute('aria-pressed', String(x === b)); });
        aplicarFiltros();
      });
    });
    var buscar = $('[data-buscar]', lista);
    if (buscar) buscar.addEventListener('input', function () { busqueda = buscar.value.trim().toLowerCase(); aplicarFiltros(); });

    function enviar(datos) {
      var fd = new FormData(); fd.append('_csrf_token', csrf);
      Object.keys(datos).forEach(function (k) { [].concat(datos[k]).forEach(function (v) { fd.append(k, v); }); });
      return fetch('acciones/obras-ordenar.php', { method: 'POST', body: fd, credentials: 'same-origin' })
        .then(function (r) { return r.json().catch(function () { return { ok: false }; }); });
    }

    // Ordenar arrastrando
    var arrastrada = null, ordenInicial = '';
    function ordenActual() { return $$('.adm-card', grilla).map(function (c) { return c.dataset.id; }); }
    cards.forEach(function (c) {
      c.addEventListener('dragstart', function (e) {
        if (c.getAttribute('draggable') !== 'true') { e.preventDefault(); return; }
        arrastrada = c; ordenInicial = ordenActual().join(',');
        c.classList.add('is-arrastrando');
        e.dataTransfer.effectAllowed = 'move';
        try { e.dataTransfer.setData('text/plain', c.dataset.id); } catch (err) {}
      });
      c.addEventListener('dragover', function (e) {
        if (!arrastrada || arrastrada === c) return;
        e.preventDefault();
        var r = c.getBoundingClientRect();
        grilla.insertBefore(arrastrada, (e.clientX - r.left) < r.width / 2 ? c : c.nextSibling);
      });
      c.addEventListener('dragend', function () {
        if (!arrastrada) return;
        arrastrada.classList.remove('is-arrastrando'); arrastrada = null;
        var orden = ordenActual();
        if (orden.join(',') === ordenInicial) return;
        enviar({ 'ids[]': orden }).then(function (r) {
          toast(r.ok ? 'Orden guardado. Así se ven en el sitio.' : (r.error || 'No se pudo guardar el orden.'), !r.ok);
        }).catch(function () { toast('No se pudo guardar el orden. Revisá la conexión.', true); });
      });
    });

    // Destacar
    $$('[data-destacar]', lista).forEach(function (b) {
      b.addEventListener('click', function (e) {
        e.stopPropagation();
        var card = b.closest('.adm-card'), nuevo = b.getAttribute('aria-pressed') !== 'true';
        b.setAttribute('aria-pressed', String(nuevo));
        enviar({ destacar: card.dataset.id, valor: nuevo ? '1' : '0' }).then(function (r) {
          if (!r.ok) { b.setAttribute('aria-pressed', String(!nuevo)); toast(r.error || 'No se pudo guardar.', true); return; }
          toast(nuevo ? 'Destacada en la home.' : 'Ya no aparece entre las destacadas.');
        }).catch(function () { b.setAttribute('aria-pressed', String(!nuevo)); toast('No se pudo guardar. Revisá la conexión.', true); });
      });
    });

    // Eliminar con confirmación propia
    var modal = $('[data-modal-eliminar]'), origen = null;
    function cerrarModal() { modal.hidden = true; if (origen) origen.focus(); }
    $$('[data-eliminar]', lista).forEach(function (b) {
      b.addEventListener('click', function () {
        origen = b;
        $('[data-modal-nombre]', modal).textContent = '"' + b.dataset.titulo + '"';
        $('[data-modal-confirmar]', modal).href = b.dataset.url;
        modal.hidden = false; $('[data-modal-cancelar]', modal).focus();
      });
    });
    if (modal) {
      $('[data-modal-cancelar]', modal).addEventListener('click', cerrarModal);
      modal.addEventListener('click', function (e) { if (e.target === modal) cerrarModal(); });
      document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && !modal.hidden) cerrarModal(); });
    }
  }

  /* =========================================================
     EDITOR VISUAL
     ========================================================= */
  var form = $('[data-editor]');
  if (!form) return;

  var body = document.body, sucio = false, enviando = false;
  var estado = $('[data-estado]');
  function marcarSucio() {
    if (sucio) return; sucio = true;
    estado.textContent = 'Cambios sin guardar'; estado.classList.add('is-sucio');
  }
  window.addEventListener('beforeunload', function (e) { if (sucio && !enviando) { e.preventDefault(); e.returnValue = ''; } });
  form.addEventListener('input', marcarSucio);
  form.addEventListener('change', marcarSucio);

  // Textareas que crecen con el texto y inputs que se ajustan al ancho del contenido
  function autoalto(t) { t.style.height = 'auto'; t.style.height = t.scrollHeight + 'px'; }
  var soportaAncho = window.CSS && CSS.supports && CSS.supports('field-sizing', 'content');
  function autoancho(i) { if (!soportaAncho) i.size = Math.max(4, (i.value || i.placeholder || '').length + 1); }
  function prepararCampos(ctx) {
    $$('[data-autoalto]', ctx).forEach(function (t) { autoalto(t); t.addEventListener('input', function () { autoalto(t); }); });
    $$('[data-ancho-auto]', ctx).forEach(function (i) { autoancho(i); i.addEventListener('input', function () { autoancho(i); }); });
  }
  prepararCampos(form);
  window.addEventListener('resize', function () { $$('[data-autoalto]', form).forEach(autoalto); });
  if (document.fonts && document.fonts.ready) document.fonts.ready.then(function () { $$('[data-autoalto]', form).forEach(autoalto); });

  // Espejos: lo que se escribe se refleja en la ficha y en la tarjeta del listado
  var vacios = { titulo: 'Título de la obra', ubicacion: 'Ubicación', rubro: 'Rubro' };
  $$('[data-espejo]', form).forEach(function (campo) {
    var clave = campo.dataset.espejo;
    function reflejar() {
      var v = campo.value.trim();
      $$('[data-mostrar="' + clave + '"]', form).forEach(function (el) {
        el.textContent = v || vacios[clave] || '';
        if (clave === 'badge') el.hidden = !v;
      });
    }
    campo.addEventListener('input', reflejar); reflejar();
  });
  $$('input[name="tipo"]', form).forEach(function (r) {
    r.addEventListener('change', function () { $$('[data-mostrar-tipo]', form).forEach(function (el) { el.textContent = r.value === 'civil' ? 'Civil' : 'Industrial'; }); });
  });

  // Formato del texto: corrido o problema + pasos
  var bloqueTexto = $('[data-formato-texto]', form), bloquePasos = $('[data-formato-pasos]', form);
  $$('input[name="formato"]', form).forEach(function (r) {
    r.addEventListener('change', function () {
      var pasos = r.value === 'pasos';
      bloqueTexto.hidden = pasos; bloquePasos.hidden = !pasos;
      $$('[data-autoalto]', pasos ? bloquePasos : bloqueTexto).forEach(autoalto);
    });
  });

  // Pasos
  var listaPasos = $('[data-pasos]', form);
  function numerar() {
    $$('.ed-paso', listaPasos).forEach(function (li, i) {
      $('span', li).textContent = i + 1;
      $('textarea', li).setAttribute('aria-label', 'Paso ' + (i + 1));
    });
  }
  function conectarPaso(li) {
    $('[data-quitar-paso]', li).addEventListener('click', function () {
      if ($$('.ed-paso', listaPasos).length === 1) { $('textarea', li).value = ''; autoalto($('textarea', li)); }
      else { li.remove(); numerar(); }
      marcarSucio();
    });
  }
  $$('.ed-paso', listaPasos).forEach(conectarPaso);
  $('[data-agregar-paso]', form).addEventListener('click', function () {
    var modelo = $('.ed-paso', listaPasos), li = modelo.cloneNode(true), t = $('textarea', li);
    t.value = ''; listaPasos.appendChild(li); conectarPaso(li); prepararCampos(li); numerar(); t.focus(); marcarSucio();
  });

  // Vista previa
  var btnPrevia = $('[data-vista-previa]', form);
  btnPrevia.addEventListener('click', function () {
    var on = !body.classList.contains('is-preview');
    body.classList.toggle('is-preview', on);
    btnPrevia.setAttribute('aria-pressed', String(on));
    $('span', btnPrevia).textContent = on ? 'Volver a editar' : 'Vista previa';
  });

  /* ---------- Galería ---------- */
  var galeria = $('[data-galeria-editor]', form), seccionGal = galeria.closest('.ed-galeria');
  var tileAgregar = $('[data-agregar-fotos]', form), elegir = $('[data-archivos-elegir]', form);
  var portadaInput = $('[data-portada-input]', form), eliminarInputs = $('[data-eliminar-inputs]', form);
  var heroFoto = $('[data-hero-foto]', form), heroVacio = $('[data-hero-vacio]', form), cardFoto = $('[data-card-foto]', form);
  var pendientes = {}, contador = 0, siguienteEsPortada = false;

  function items() { return $$('.ed-foto', galeria); }
  function vigentes() { return items().filter(function (it) { return !it.classList.contains('is-borrada'); }); }

  function ponerPortada(it) {
    items().forEach(function (x) { x.classList.toggle('es-portada', x === it); });
    portadaInput.value = it ? it.dataset.token : '';
    var src = it ? $('img', it).src : '';
    [heroFoto, cardFoto].forEach(function (img) { if (!img) return; img.src = src; img.hidden = !src; });
    if (heroVacio) heroVacio.hidden = !!src;
  }
  function portadaActual() { return items().filter(function (x) { return x.classList.contains('es-portada'); })[0] || null; }

  function conectarFoto(it) {
    $('[data-hacer-portada]', it).addEventListener('click', function () { ponerPortada(it); marcarSucio(); });
    $('[data-borrar-foto]', it).addEventListener('click', function () {
      var eraPortada = it.classList.contains('es-portada');
      if (it.dataset.token.indexOf('n:') === 0) {
        URL.revokeObjectURL($('img', it).src); delete pendientes[it.dataset.token]; it.remove();
      } else {
        it.classList.add('is-borrada'); it.classList.remove('es-portada');
        var h = document.createElement('input'); h.type = 'hidden'; h.name = 'eliminar[]'; h.value = it.dataset.archivo; h.dataset.para = it.dataset.archivo;
        eliminarInputs.appendChild(h);
      }
      if (eraPortada) ponerPortada(vigentes()[0] || null);
      marcarSucio();
    });
    var deshacer = $('[data-deshacer-foto]', it);
    if (deshacer) deshacer.addEventListener('click', function () {
      it.classList.remove('is-borrada');
      var h = $('input[data-para="' + CSS.escape(it.dataset.archivo) + '"]', eliminarInputs); if (h) h.remove();
      if (!portadaActual()) ponerPortada(it);
    });
    // Ordenar arrastrando
    it.addEventListener('dragstart', function (e) {
      if (body.classList.contains('is-preview')) { e.preventDefault(); return; }
      arrastrandoFoto = it; it.classList.add('is-arrastrando'); e.dataTransfer.effectAllowed = 'move';
      try { e.dataTransfer.setData('text/plain', 'foto'); } catch (err) {}
    });
    it.addEventListener('dragover', function (e) {
      if (!arrastrandoFoto || arrastrandoFoto === it) return;
      e.preventDefault();
      var r = it.getBoundingClientRect();
      galeria.insertBefore(arrastrandoFoto, (e.clientX - r.left) < r.width / 2 ? it : it.nextSibling);
      galeria.appendChild(tileAgregar);
    });
    it.addEventListener('dragend', function () { if (arrastrandoFoto) { arrastrandoFoto.classList.remove('is-arrastrando'); arrastrandoFoto = null; marcarSucio(); } });
  }
  var arrastrandoFoto = null;
  items().forEach(conectarFoto);

  // Achica la foto en el navegador antes de subirla (más rápido y sin pasar el límite del servidor).
  function achicar(file) {
    return new Promise(function (ok) {
      if (!/^image\/(jpeg|png|webp)$/.test(file.type) || !window.createImageBitmap) { ok(file); return; }
      createImageBitmap(file).then(function (bmp) {
        var max = 2400, esc = Math.min(1, max / Math.max(bmp.width, bmp.height));
        if (esc === 1 && file.size < 2.5e6) { ok(file); return; }
        var c = document.createElement('canvas'); c.width = Math.round(bmp.width * esc); c.height = Math.round(bmp.height * esc);
        c.getContext('2d').drawImage(bmp, 0, 0, c.width, c.height);
        c.toBlob(function (blob) {
          if (!blob) { ok(file); return; }
          ok(new File([blob], file.name.replace(/\.[^.]+$/, '') + '.jpg', { type: 'image/jpeg' }));
        }, 'image/jpeg', 0.86);
      }).catch(function () { ok(file); });
    });
  }

  function agregarArchivos(lista) {
    var archivos = Array.prototype.slice.call(lista).filter(function (f) { return /^image\//.test(f.type); });
    if (!archivos.length) { toast('Elegí archivos de imagen (JPG, PNG o WebP).', true); return; }
    var hacerPortada = siguienteEsPortada; siguienteEsPortada = false;
    tileAgregar.classList.add('is-cargando');
    Promise.all(archivos.map(achicar)).then(function (finales) {
      finales.forEach(function (f, k) {
        var token = 'n:' + (contador++), url = URL.createObjectURL(f);
        pendientes[token] = f;
        var it = document.createElement('div');
        it.className = 'fz-galeria__item ed-foto es-nueva'; it.draggable = true; it.dataset.token = token;
        it.innerHTML = '<img alt="" draggable="false"><div class="ed-foto__acciones ed-control">' +
          '<button type="button" class="ed-foto__btn" data-hacer-portada aria-label="Usar como portada" title="Usar como portada">★</button>' +
          '<button type="button" class="ed-foto__btn" data-borrar-foto aria-label="Quitar foto" title="Quitar foto">×</button></div>' +
          '<span class="ed-foto__tag">Portada</span>';
        $('img', it).src = url;
        galeria.insertBefore(it, tileAgregar);
        conectarFoto(it);
        if ((hacerPortada && k === 0) || !portadaActual()) ponerPortada(it);
      });
      tileAgregar.classList.remove('is-cargando');
      marcarSucio();
      toast(finales.length === 1 ? 'Foto agregada. Se sube al guardar.' : finales.length + ' fotos agregadas. Se suben al guardar.');
    });
  }
  tileAgregar.addEventListener('click', function () { elegir.click(); });
  $('[data-subir-portada]', form).addEventListener('click', function () { siguienteEsPortada = true; elegir.click(); });
  elegir.addEventListener('change', function () { if (elegir.files.length) agregarArchivos(elegir.files); elegir.value = ''; });

  // Soltar fotos desde la compu
  function tieneArchivos(e) { return e.dataTransfer && Array.prototype.indexOf.call(e.dataTransfer.types || [], 'Files') !== -1; }
  seccionGal.addEventListener('dragover', function (e) { if (tieneArchivos(e)) { e.preventDefault(); seccionGal.classList.add('is-soltando'); } });
  seccionGal.addEventListener('dragleave', function (e) { if (!seccionGal.contains(e.relatedTarget)) seccionGal.classList.remove('is-soltando'); });
  seccionGal.addEventListener('drop', function (e) {
    if (!tieneArchivos(e)) return; e.preventDefault(); seccionGal.classList.remove('is-soltando'); agregarArchivos(e.dataTransfer.files);
  });
  ['dragover', 'drop'].forEach(function (ev) { window.addEventListener(ev, function (e) { if (tieneArchivos(e) && !seccionGal.contains(e.target)) e.preventDefault(); }); });

  /* ---------- Guardar ---------- */
  function avisar(campo, msj) {
    toast(msj, true);
    if (body.classList.contains('is-preview')) btnPrevia.click();
    campo.scrollIntoView({ behavior: 'smooth', block: 'center' }); setTimeout(function () { campo.focus(); }, 300);
  }
  form.addEventListener('submit', function (e) {
    var titulo = form.titulo, ubic = form.ubicacion, formato = $('input[name="formato"]:checked', form).value;
    if (titulo.value.trim().length < 3) { e.preventDefault(); avisar(titulo, 'Escribí el título de la obra.'); return; }
    if (!ubic.value.trim()) { e.preventDefault(); avisar(ubic, 'Escribí dónde fue la obra.'); return; }
    if (formato === 'texto' && !form.explicacion.value.trim()) { e.preventDefault(); avisar(form.explicacion, 'Contá qué se hizo en la obra.'); return; }
    if (formato === 'pasos' && !$$('textarea[name="pasos[]"]', form).some(function (t) { return t.value.trim(); })) {
      e.preventDefault(); avisar($('textarea[name="pasos[]"]', form), 'Escribí al menos un paso.'); return;
    }
    // Fotos nuevas en el orden en que quedaron
    var dt = new DataTransfer(), mapa = {}, orden = [];
    vigentes().forEach(function (it) {
      var t = it.dataset.token;
      if (t.indexOf('n:') === 0) { mapa[t] = 'n:' + dt.items.length; dt.items.add(pendientes[t]); orden.push(mapa[t]); }
      else orden.push(t);
    });
    $('[data-archivos-envio]', form).files = dt.files;
    $('[data-orden-input]', form).value = JSON.stringify(orden);
    if (mapa[portadaInput.value]) portadaInput.value = mapa[portadaInput.value];
    enviando = true;
    var b = $('[data-guardar]', form); b.classList.add('is-cargando'); b.textContent = 'Guardando…';
  });
})();
