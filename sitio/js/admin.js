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
  /* ---------- Estados de carga generales ---------- */
  function ponerCargando(btn, texto) {
    if (!btn || btn.classList.contains('is-cargando')) return;
    btn.dataset.textoOriginal = btn.textContent.trim();
    btn.classList.add('is-cargando'); btn.setAttribute('aria-busy', 'true');
    btn.innerHTML = '<span class="adm-giro" aria-hidden="true"></span>' + texto;
  }
  $$('form[data-cargando]').forEach(function (f) {
    f.addEventListener('submit', function () { ponerCargando(f.querySelector('[type="submit"]'), f.dataset.cargando); });
  });
  // Si el navegador vuelve atrás con la página en memoria, los botones vuelven a la normalidad.
  window.addEventListener('pageshow', function (e) {
    if (!e.persisted) return;
    $$('.is-cargando[data-texto-original]').forEach(function (b) { b.classList.remove('is-cargando'); b.removeAttribute('aria-busy'); b.textContent = b.dataset.textoOriginal; });
  });
  // Miniaturas del panel: fondo animado hasta que cargan.
  $$('.adm-card__foto img, .ed-foto img').forEach(function (im) {
    var c = im.parentNode;
    if (im.complete) return;
    c.classList.add('is-cargando-img');
    ['load', 'error'].forEach(function (ev) { im.addEventListener(ev, function () { c.classList.remove('is-cargando-img'); }); });
  });

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
      var sin = $('[data-sin-resultados]');
      if (sin) {
        sin.hidden = visibles > 0;
        var nombreTipo = filtro === 'civil' ? 'civiles' : filtro === 'industrial' ? 'industriales' : '';
        if (busqueda) {
          $('[data-sin-titulo]', sin).textContent = 'Ninguna obra' + (nombreTipo ? ' ' + nombreTipo.replace(/es$/, '').replace(/l$/, 'l') : '') + ' coincide con "' + buscar.value.trim() + '"';
          $('[data-sin-texto]', sin).textContent = 'Revisá cómo está escrito, buscá por el barrio o la ciudad, o mirá todas las obras.';
        } else {
          $('[data-sin-titulo]', sin).textContent = 'Todavía no hay obras ' + nombreTipo;
          $('[data-sin-texto]', sin).textContent = 'Cuando cargues una, va a aparecer acá y en la sección de obras ' + nombreTipo + ' del sitio.';
        }
        $('[data-cargar-tipo]', sin).href = 'index.php?seccion=obras-agregar' + (filtro !== 'todas' ? '&tipo=' + filtro : '');
      }
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
    var limpiar = $('[data-limpiar-filtros]', lista);
    if (limpiar) limpiar.addEventListener('click', function () {
      filtro = 'todas'; busqueda = ''; if (buscar) buscar.value = '';
      $$('[data-filtro]', lista).forEach(function (x) { x.setAttribute('aria-pressed', String(x.dataset.filtro === 'todas')); });
      aplicarFiltros(); if (buscar) buscar.focus();
    });

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
        b.setAttribute('aria-pressed', String(nuevo)); b.classList.add('is-guardando');
        enviar({ destacar: card.dataset.id, valor: nuevo ? '1' : '0' }).finally(function () { b.classList.remove('is-guardando'); }).then(function (r) {
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
      $('[data-modal-confirmar]', modal).addEventListener('click', function () { ponerCargando(this, 'Eliminando…'); $('[data-modal-cancelar]', modal).disabled = true; });
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
  // Título: el tamaño se ajusta solo según el largo (igual que en el sitio) y se avisa cuál quedó.
  var heroEd = $('[data-hero-editor]', form), avisoTam = $('[data-tamano-aviso]', form);
  var TAMANOS = [[40, 'xl', 'Grande'], [60, 'l', 'Grande'], [85, 'm', 'Mediano'], [115, 's', 'Chico'], [1e9, 'xs', 'Compacto']];
  function ajustarTitulo() {
    if (!heroEd) return;
    var largo = (form.titulo.value.trim() + ' en ' + form.ubicacion.value.trim()).length, t = TAMANOS.filter(function (x) { return largo <= x[0]; })[0];
    TAMANOS.forEach(function (x) { heroEd.classList.toggle('fz-titulo--' + x[1], x === t); });
    avisoTam.innerHTML = 'Tamaño del título: <b>' + t[2] + '</b> · se ajusta solo al largo para que entre completo';
    autoalto(form.titulo);
  }
  ['titulo', 'ubicacion'].forEach(function (n) { form[n].addEventListener('input', ajustarTitulo); });
  ajustarTitulo();

  // Dirección de la obra: se arma sola con el título mientras el admin no la toque.
  var slugInput = $('[data-slug]', form);
  function aSlug(t) {
    return t.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '').slice(0, 80).replace(/-+$/, '');
  }
  if (slugInput) {
    form.titulo.addEventListener('input', function () { if (slugInput.dataset.slugAuto === '1') slugInput.value = aSlug(form.titulo.value); });
    slugInput.addEventListener('input', function () {
      slugInput.dataset.slugAuto = slugInput.value ? '0' : '1';
      var pos = slugInput.selectionStart, limpio = slugInput.value.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/[^a-z0-9-]+/g, '-').replace(/-{2,}/g, '-');
      if (limpio !== slugInput.value) { slugInput.value = limpio; slugInput.setSelectionRange(pos, pos); }
    });
    if (slugInput.dataset.slugAuto === '1' && form.titulo.value) slugInput.value = aSlug(form.titulo.value);
  }

  // Textos largos: en el editor pasan a letra de lectura, igual que en el sitio.
  $$('textarea[name="explicacion"], textarea[name="problema"]', form).forEach(function (t) {
    function revisar() { var antes = t.classList.contains('es-largo'); t.classList.toggle('es-largo', t.value.trim().length > 280); if (antes !== t.classList.contains('es-largo')) autoalto(t); }
    revisar(); t.addEventListener('input', revisar);
  });
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
  var contador = 0, siguienteEsPortada = false;

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
    var btnPortada = $('[data-hacer-portada]', it);
    if (btnPortada) btnPortada.addEventListener('click', function () { ponerPortada(it); marcarSucio(); });
    $('[data-borrar-foto]', it).addEventListener('click', function () {
      var eraPortada = it.classList.contains('es-portada');
      if (it.classList.contains('es-nueva')) {
        if (it._xhr) it._xhr.abort();
        it._cancelado = true;
        cola = cola.filter(function (x) { return x !== it; });
        borradorQuitar(it.dataset.token); it.remove(); actualizarContador();
      } else {
        it.classList.add('is-borrada'); it.classList.remove('es-portada');
        var h = document.createElement('input'); h.type = 'hidden'; h.name = 'eliminar[]'; h.value = it.dataset.archivo; h.dataset.para = it.dataset.archivo;
        eliminarInputs.appendChild(h);
      }
      if (eraPortada) ponerPortada(fotosListas()[0] || null);
      actualizarContador();
      marcarSucio();
    });
    var deshacer = $('[data-deshacer-foto]', it);
    if (deshacer) deshacer.addEventListener('click', function () {
      it.classList.remove('is-borrada');
      var h = $('input[data-para="' + CSS.escape(it.dataset.archivo) + '"]', eliminarInputs); if (h) h.remove();
      if (!portadaActual()) ponerPortada(it);
      actualizarContador();
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

  /* ---------- Fotos: se achican en el navegador y se suben de a una apenas se agregan ---------- */
  var MAX_FOTOS = parseInt(form.dataset.maxFotos, 10) || 60;
  var MAX_VIDEOS = parseInt(form.dataset.maxVideos, 10) || 3;
  var MAX_VIDEO_MB = parseInt(form.dataset.maxVideoMb, 10) || 150;
  var MAX_VIDEO_SEG = parseInt(form.dataset.maxVideoSeg, 10) || 90;
  var URL_VIDEO = form.dataset.videoUrl;
  var URL_SUBIR = form.dataset.subirUrl;
  var CSRF = form.querySelector('input[name="_csrf_token"]').value;
  var contadorEl = $('[data-contador-fotos]', form);
  var cola = [], subiendo = 0, SIMULTANEAS = 3, alTerminar = null;
  var btnGuardar = $('[data-guardar]', form); btnGuardar.dataset.texto = btnGuardar.textContent.trim();

  function esVideoTile(it) { return it.classList.contains('es-video'); }
  function fotosListas() { return listas().filter(function (it) { return !esVideoTile(it); }); }
  function cuantos(video) { return vigentes().filter(function (it) { return esVideoTile(it) === video; }).length; }
  function listas() { return vigentes().filter(function (it) { return !it.classList.contains('is-subiendo') && !it.classList.contains('is-error'); }); }
  function enProceso() { return items().filter(function (it) { return it.classList.contains('is-subiendo'); }).length; }
  function conError() { return items().filter(function (it) { return it.classList.contains('is-error'); }); }

  function actualizarContador() {
    var fotos = cuantos(false), videos = cuantos(true), faltan = enProceso();
    var txt = '<b>' + fotos + '</b> de ' + MAX_FOTOS + ' fotos · <b>' + videos + '</b> de ' + MAX_VIDEOS + ' videos';
    if (faltan) txt += ' · subiendo ' + faltan + '…';
    contadorEl.innerHTML = txt;
    var lleno = fotos >= MAX_FOTOS && videos >= MAX_VIDEOS;
    contadorEl.classList.toggle('is-lleno', fotos >= MAX_FOTOS);
    contadorEl.classList.toggle('is-cerca', fotos >= MAX_FOTOS * 0.85 && fotos < MAX_FOTOS);
    tileAgregar.disabled = lleno;
    $('[data-agregar-texto]', tileAgregar).textContent = lleno ? 'Llegaste al máximo' : (fotos >= MAX_FOTOS ? 'Agregar videos' : 'Agregar fotos o videos');
  }

  // Achica: lado mayor 2000 px y JPEG 82 %. Una foto de celular de 5 MB queda en ~400 KB.
  function achicar(file) {
    return new Promise(function (ok) {
      if (!/^image\/(jpeg|png|webp)$/.test(file.type) || !window.createImageBitmap) { ok(file); return; }
      createImageBitmap(file).then(function (bmp) {
        var max = 2000, esc = Math.min(1, max / Math.max(bmp.width, bmp.height));
        if (esc === 1 && file.type === 'image/jpeg' && file.size < 700 * 1024) { ok(file); return; }
        var c = document.createElement('canvas'); c.width = Math.round(bmp.width * esc); c.height = Math.round(bmp.height * esc);
        var ctx = c.getContext('2d'); ctx.fillStyle = '#fff'; ctx.fillRect(0, 0, c.width, c.height);
        ctx.drawImage(bmp, 0, 0, c.width, c.height);
        c.toBlob(function (blob) {
          if (!blob) { ok(file); return; }
          ok(new File([blob], file.name.replace(/\.[^.]+$/, '') + '.jpg', { type: 'image/jpeg' }));
        }, 'image/jpeg', 0.82);
      }).catch(function () { ok(file); });
    });
  }

  function crearTile(src, video) {
    var it = document.createElement('div');
    it.className = 'fz-galeria__item ed-foto es-nueva' + (video ? ' es-video' : ''); it.draggable = true;
    it.innerHTML = '<img alt="" draggable="false">' +
      (video ? '<span class="ed-foto__video" aria-hidden="true"><svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg></span>' : '') +
      '<div class="ed-foto__acciones ed-control">' +
      (video ? '' : '<button type="button" class="ed-foto__btn" data-hacer-portada aria-label="Usar como portada" title="Usar como portada">★</button>') +
      '<button type="button" class="ed-foto__btn" data-borrar-foto aria-label="Quitar" title="Quitar">×</button></div>' +
      '<span class="ed-foto__tag">Portada</span>' +
      '<div class="ed-foto__subida"><span class="ed-foto__barra"><i></i></span><span class="ed-foto__estado">En espera</span></div>' +
      '<div class="ed-foto__error"><span data-error-texto>No se pudo subir</span><button type="button" class="adm-btn adm-btn--linea adm-btn--chico" data-reintentar>Reintentar</button></div>';
    var im0 = $('img', it); im0.onerror = function () { im0.hidden = true; };
    if (video) im0.onload = function () { it.classList.toggle('es-vertical', im0.naturalHeight > im0.naturalWidth * 1.05); };
    if (src) im0.src = src; else im0.hidden = true;
    galeria.insertBefore(it, tileAgregar);
    conectarFoto(it);
    $('[data-reintentar]', it).addEventListener('click', function () { it.classList.remove('is-error'); encolar(it); });
    return it;
  }

  function encolar(it) {
    it.classList.add('is-subiendo'); $('.ed-foto__estado', it).textContent = 'En espera';
    cola.push(it); actualizarContador(); siguiente();
  }

  function siguiente() {
    while (subiendo < SIMULTANEAS && cola.length) subir(cola.shift());
    if (!subiendo && !cola.length && alTerminar) { var f = alTerminar; alTerminar = null; f(); }
  }

  function subir(it) {
    subiendo++;
    if (esVideoTile(it)) { subirVideo(it); return; }
    var barra = $('.ed-foto__barra i', it), est = $('.ed-foto__estado', it);
    est.textContent = 'Achicando…';
    achicar(it._archivo).then(function (f) {
      if (!it.isConnected) { subiendo--; siguiente(); return; }
      var fd = new FormData(); fd.append('_csrf_token', CSRF); fd.append('foto', f, f.name);
      var x = new XMLHttpRequest(); it._xhr = x;
      x.open('POST', URL_SUBIR);
      x.upload.onprogress = function (e) {
        if (!e.lengthComputable) return;
        var p = Math.round(e.loaded / e.total * 100); barra.style.width = p + '%'; est.textContent = 'Subiendo ' + p + '%';
      };
      x.onload = function () {
        var r = null; try { r = JSON.parse(x.responseText); } catch (err) {}
        if (r && r.ok) {
          it.dataset.token = r.token; it._archivo = null;
          it.classList.remove('is-subiendo');
          borradorAgregar(r.token, r.url);
          if (it.classList.contains('es-portada')) portadaInput.value = r.token;
        } else fallo(it, (r && r.error) || 'No se pudo subir');
        fin();
      };
      x.onerror = function () { fallo(it, 'Sin conexión'); fin(); };
      x.onabort = function () { fin(); };
      function fin() { it._xhr = null; subiendo--; actualizarContador(); siguiente(); }
      x.send(fd);
    });
  }

  function fallo(it, msj) {
    it.classList.remove('is-subiendo'); it.classList.add('is-error');
    $('[data-error-texto]', it).textContent = msj;
  }

  /* ---------- Videos: se suben en partes de 1,5 MB (nunca chocan con el límite del hosting) ---------- */
  var PARTE = 1.5 * 1024 * 1024;
  function idSubida() { var a = new Uint8Array(12); crypto.getRandomValues(a); return Array.prototype.map.call(a, function (b) { return ('0' + b.toString(16)).slice(-2); }).join(''); }

  // Lee duración y saca un cuadro del video para usar de vista previa.
  function analizarVideo(file) {
    return new Promise(function (ok) {
      var v = document.createElement('video'), url = URL.createObjectURL(file), listo = false;
      v.muted = true; v.playsInline = true; v.preload = 'metadata'; v.src = url;
      function terminar(r) { if (listo) return; listo = true; URL.revokeObjectURL(url); ok(r); }
      setTimeout(function () { terminar(null); }, 15000);
      v.onerror = function () { terminar(null); };
      v.onloadedmetadata = function () {
        if (!isFinite(v.duration) || !v.videoWidth) { terminar(null); return; }
        v.currentTime = Math.min(1, v.duration / 2);
      };
      v.onseeked = function () {
        var max = 1280, esc = Math.min(1, max / Math.max(v.videoWidth, v.videoHeight));
        var c = document.createElement('canvas'); c.width = Math.round(v.videoWidth * esc); c.height = Math.round(v.videoHeight * esc);
        c.getContext('2d').drawImage(v, 0, 0, c.width, c.height);
        c.toBlob(function (blob) { terminar({ duracion: v.duration, poster: blob }); }, 'image/jpeg', 0.8);
      };
    });
  }

  function enviarParte(datos) {
    return new Promise(function (ok) {
      var fd = new FormData(); fd.append('_csrf_token', CSRF);
      Object.keys(datos).forEach(function (k) { if (datos[k] !== undefined) fd.append(k, datos[k]); });
      var x = new XMLHttpRequest(); x.open('POST', URL_VIDEO);
      x.onload = function () { var r = null; try { r = JSON.parse(x.responseText); } catch (e) {} ok(r || { ok: false, error: 'Respuesta inválida del servidor' }); };
      x.onerror = function () { ok({ ok: false, red: true, error: 'Sin conexión' }); };
      x.send(fd);
    });
  }

  function subirVideo(it) {
    var f = it._archivo, id = it._idSubida || (it._idSubida = idSubida()), offset = it._offset || 0, intentos = 0;
    var barra = $('.ed-foto__barra i', it), est = $('.ed-foto__estado', it);
    function fin() { subiendo--; actualizarContador(); siguiente(); }
    function paso() {
      if (it._cancelado) { fin(); return; }
      var p = Math.round(offset / f.size * 100); barra.style.width = p + '%'; est.textContent = 'Subiendo video ' + p + '%';
      if (offset >= f.size) {
        est.textContent = 'Terminando…';
        enviarParte({ id: id, final: '1', nombre: f.name, poster: it._poster ? new File([it._poster], 'poster.jpg', { type: 'image/jpeg' }) : undefined }).then(function (r) {
          if (r.ok) {
            it.dataset.token = r.token; it._archivo = null; it.classList.remove('is-subiendo');
            borradorAgregar(r.token, r.url, true);
          } else fallo(it, r.error || 'No se pudo subir el video');
          fin();
        });
        return;
      }
      var trozo = f.slice(offset, offset + PARTE);
      enviarParte({ id: id, offset: String(offset), parte: new File([trozo], 'parte.bin') }).then(function (r) {
        if (r.ok) { offset += trozo.size; it._offset = offset; intentos = 0; paso(); return; }
        if (typeof r.retomar === 'number') { offset = r.retomar; it._offset = offset; paso(); return; }
        if (r.red && intentos++ < 3) { setTimeout(paso, 1500 * intentos); return; }
        fallo(it, r.error || 'No se pudo subir el video'); fin();
      });
    }
    paso();
  }

  function esVideoArchivo(f) { return /^video\//.test(f.type) || /\.(mp4|mov|m4v|webm)$/i.test(f.name); }

  function agregarArchivos(lista) {
    lista = Array.prototype.slice.call(lista);
    var archivos = lista.filter(function (f) { return /^image\//.test(f.type); });
    var videos = lista.filter(esVideoArchivo);
    if (!archivos.length && !videos.length) { toast('Elegí fotos (JPG, PNG o WebP) o videos (MP4, MOV o WebM).', true); return; }
    var hacerPortada = siguienteEsPortada; siguienteEsPortada = false;

    if (archivos.length) {
      var lugar = MAX_FOTOS - cuantos(false);
      if (lugar <= 0) toast('Esta obra ya tiene ' + MAX_FOTOS + ' fotos, que es el máximo. Quitá alguna para agregar otra.', true);
      else {
        if (archivos.length > lugar) {
          toast('Se agregan ' + lugar + ' de ' + archivos.length + ' fotos: cada obra admite hasta ' + MAX_FOTOS + '.', true);
          archivos = archivos.slice(0, lugar);
        }
        archivos.forEach(function (f, k) {
          var it = crearTile(URL.createObjectURL(f));
          it._archivo = f; it.dataset.token = 'p:' + (contador++);
          if ((hacerPortada && k === 0) || !portadaActual()) ponerPortada(it);
          encolar(it);
        });
      }
    }

    if (videos.length) {
      var lugarV = MAX_VIDEOS - cuantos(true);
      if (lugarV <= 0) { toast('Cada obra admite hasta ' + MAX_VIDEOS + ' videos. Quitá uno para agregar otro.', true); videos = []; }
      else if (videos.length > lugarV) { toast('Se agregan ' + lugarV + ' de ' + videos.length + ' videos: cada obra admite hasta ' + MAX_VIDEOS + '.', true); videos = videos.slice(0, lugarV); }
      videos.forEach(function (f) {
        if (f.size > MAX_VIDEO_MB * 1048576) { toast('"' + f.name + '" pesa ' + Math.round(f.size / 1048576) + ' MB. El máximo es ' + MAX_VIDEO_MB + ' MB.', true); return; }
        var it = crearTile('', true);
        it._archivo = f; it.dataset.token = 'p:' + (contador++);
        it.classList.add('is-subiendo'); $('.ed-foto__estado', it).textContent = 'Revisando video…';
        actualizarContador();
        analizarVideo(f).then(function (info) {
          if (!it.isConnected) return;
          if (!info) {
            it.remove(); actualizarContador();
            toast('"' + f.name + '" no se puede reproducir en el navegador. Exportalo como MP4 (H.264) y probá de nuevo.', true);
            return;
          }
          if (info.duracion > MAX_VIDEO_SEG + 0.5) {
            it.remove(); actualizarContador();
            toast('"' + f.name + '" dura ' + Math.round(info.duracion) + ' segundos. El máximo es ' + MAX_VIDEO_SEG + '. Recortalo y probá de nuevo.', true);
            return;
          }
          it._poster = info.poster;
          if (info.poster) { var im = $('img', it); im.src = URL.createObjectURL(info.poster); im.hidden = false; }
          var dur = Math.round(info.duracion);
          it.dataset.duracion = Math.floor(dur / 60) + ':' + ('0' + dur % 60).slice(-2);
          encolar(it);
        });
      });
    }
    marcarSucio();
  }

  /* ---------- Borrador: fotos subidas que todavía no se guardaron en la obra ---------- */
  var CLAVE_BORRADOR = 'fz-fotos-' + form.dataset.obra;
  function leerBorrador() { try { return JSON.parse(localStorage.getItem(CLAVE_BORRADOR) || '[]'); } catch (e) { return []; } }
  function escribirBorrador(l) { try { if (l.length) localStorage.setItem(CLAVE_BORRADOR, JSON.stringify(l)); else localStorage.removeItem(CLAVE_BORRADOR); } catch (e) {} }
  function borradorAgregar(token, url, video) { var l = leerBorrador(); l.push({ token: token, url: url, video: !!video }); escribirBorrador(l); }
  function borradorQuitar(token) { escribirBorrador(leerBorrador().filter(function (x) { return x.token !== token; })); }

  (function revisarBorrador() {
    var l = leerBorrador(); if (!l.length) return;
    var barraB = $('[data-borrador]', form);
    // Las que ya se guardaron se movieron de la carpeta temporal: solo quedan las pendientes.
    Promise.all(l.map(function (x) {
      return fetch(x.url, { method: 'HEAD', cache: 'no-store' }).then(function (r) { return r.ok ? x : null; }).catch(function () { return null; });
    })).then(function (res) {
      var vivas = res.filter(Boolean); escribirBorrador(vivas);
      if (!vivas.length) return;
      var nv = vivas.filter(function (x) { return x.video; }).length, nf = vivas.length - nv, partes = [];
      if (nf) partes.push(nf + (nf === 1 ? ' foto' : ' fotos'));
      if (nv) partes.push(nv + (nv === 1 ? ' video' : ' videos'));
      $('[data-borrador-texto]', barraB).textContent = 'Hay ' + partes.join(' y ') + ' que subiste y no llegaste a guardar.';
      barraB.hidden = false;
      $('[data-borrador-recuperar]', barraB).onclick = function () {
        vivas.forEach(function (x) {
          var it = crearTile(x.video ? x.url.replace(/\.[^.]+$/, '-poster.jpg') : x.url, x.video); it.dataset.token = x.token;
          if (!x.video && !portadaActual()) ponerPortada(it);
        });
        barraB.hidden = true; actualizarContador(); marcarSucio();
        toast('Recuperado. Tocá "Guardar" para sumarlo a la obra.');
      };
      $('[data-borrador-descartar]', barraB).onclick = function () { escribirBorrador([]); barraB.hidden = true; };
    });
  })();
  actualizarContador();

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
    // Si quedan fotos subiendo, se espera a que terminen y se guarda solo.
    var faltan = enProceso();
    if (faltan) {
      e.preventDefault();
      var bt = $('[data-guardar]', form); bt.classList.add('is-cargando'); bt.textContent = 'Subiendo fotos…';
      toast('Esperando que terminen de subir ' + faltan + (faltan === 1 ? ' foto' : ' fotos') + '. Se guarda solo.');
      alTerminar = function () { bt.classList.remove('is-cargando'); bt.textContent = bt.dataset.texto || 'Guardar'; form.requestSubmit(); };
      return;
    }
    var errores = conError();
    if (errores.length) {
      e.preventDefault();
      avisar(errores[0], errores.length === 1 ? 'Una foto no se pudo subir: reintentala o quitala.' : errores.length + ' fotos no se pudieron subir: reintentalas o quitalas.');
      return;
    }
    // Orden final: "f:" fotos que ya estaban, "t:" fotos recién subidas.
    $('[data-orden-input]', form).value = JSON.stringify(listas().map(function (it) { return it.dataset.token; }));
    enviando = true;
    var b = $('[data-guardar]', form); b.classList.add('is-cargando'); b.textContent = 'Guardando…';
  });
})();
