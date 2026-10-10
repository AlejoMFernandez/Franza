/**
 * franza.js — Interacciones del sitio público (rediseño 2026)
 * Intro, header, menú mobile, hero rayos X, antes/después, cinta métrica,
 * filtros del listado y lightbox de la galería. Sin dependencias.
 */
(function () {
  'use strict';
  var html = document.documentElement;
  var reducir = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var tactil = window.matchMedia && window.matchMedia('(hover: none), (pointer: coarse)').matches;
  function $(sel, ctx) { return (ctx || document).querySelector(sel); }
  function $$(sel, ctx) { return Array.prototype.slice.call((ctx || document).querySelectorAll(sel)); }
  function clamp(v, a, b) { return Math.max(a, Math.min(b, v)); }

  /* ---------- Intro ---------- */
  (function intro() {
    var el = $('#fz-intro');
    if (!el || !html.classList.contains('fz-con-intro')) return;
    var pct = $('#fz-intro-pct'), barra = $('#fz-intro-barra'), avance = $('.fz-intro__avance', el);
    var t0 = performance.now(), dur = 1600, fin = false;
    function paso(now) {
      var t = Math.min(1, (now - t0) / dur), e = 1 - Math.pow(1 - t, 3), p = Math.round(e * 100);
      if (pct) pct.textContent = String(p).padStart(3, '0');
      if (barra) barra.style.width = p + '%';
      if (p >= 100 && avance) avance.classList.add('is-lleno');
      if (t < 1 && !fin) requestAnimationFrame(paso);
    }
    requestAnimationFrame(paso);
    function terminar() {
      if (fin) return; fin = true;
      html.classList.add('fz-intro-fin');
      try { sessionStorage.setItem('fz-intro', '1'); } catch (e) {}
    }
    setTimeout(terminar, 2950);
    var saltar = $('#fz-intro-saltar');
    if (saltar) saltar.addEventListener('click', function () {
      html.classList.remove('fz-con-intro'); html.classList.add('fz-sin-intro'); terminar();
    });
  })();

  /* ---------- Header: sólido al bajar, menú mobile, menú de usuario ---------- */
  var header = $('#fz-header');
  if (header && header.classList.contains('fz-header--sobre-hero')) {
    var actualizar = function () { header.classList.toggle('is-solido', window.scrollY > 40); };
    window.addEventListener('scroll', actualizar, { passive: true }); actualizar();
  }
  var menuBtn = $('#fz-menu-btn'), nav = $('#fz-nav');
  if (menuBtn && nav) {
    var cerrarMenu = function () { nav.classList.remove('is-abierto'); menuBtn.setAttribute('aria-expanded', 'false'); menuBtn.setAttribute('aria-label', 'Abrir menú'); document.body.classList.remove('menu-abierto'); };
    menuBtn.addEventListener('click', function () {
      var abrir = !nav.classList.contains('is-abierto');
      nav.classList.toggle('is-abierto', abrir);
      menuBtn.setAttribute('aria-expanded', String(abrir));
      menuBtn.setAttribute('aria-label', abrir ? 'Cerrar menú' : 'Abrir menú');
      document.body.classList.toggle('menu-abierto', abrir);
    });
    $$('a', nav).forEach(function (a) { a.addEventListener('click', cerrarMenu); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') cerrarMenu(); });
  }
  $$('.fz-user').forEach(function (u) {
    var b = $('.fz-user__btn', u);
    b.addEventListener('click', function (e) { e.stopPropagation(); var a = !u.classList.contains('is-abierto'); u.classList.toggle('is-abierto', a); b.setAttribute('aria-expanded', String(a)); });
    document.addEventListener('click', function () { u.classList.remove('is-abierto'); b.setAttribute('aria-expanded', 'false'); });
  });

  /* ---------- Hero rayos X ---------- */
  $$('[data-rayosx]').forEach(function (hero) {
    var toggle = $('[data-rayosx-toggle]', hero), timer = null, auto = null, tocado = false;
    function poner(x, y) { hero.style.setProperty('--x', x + 'px'); hero.style.setProperty('--y', y + 'px'); }
    function mover(e) {
      if (hero.classList.contains('is-completo')) return;
      var r = hero.getBoundingClientRect();
      poner(e.clientX - r.left, e.clientY - r.top);
      hero.classList.add('is-moviendo');
      clearTimeout(timer); timer = setTimeout(function () { hero.classList.remove('is-moviendo'); }, 120);
    }
    if (!tactil) {
      hero.addEventListener('pointermove', mover);
    } else {
      hero.addEventListener('pointerdown', function (e) {
        if (e.target.closest('a, button')) return;
        tocado = true; if (auto) cancelAnimationFrame(auto); mover(e);
      });
      // En celulares la lente pasea sola hasta que tocan la foto.
      if (!reducir) {
        var t0 = performance.now();
        var pasear = function (now) {
          if (tocado || hero.classList.contains('is-completo')) return;
          var r = hero.getBoundingClientRect(), t = (now - t0) / 1000;
          poner(r.width * (0.55 + 0.22 * Math.sin(t * 0.7)), r.height * (0.34 + 0.12 * Math.sin(t * 1.1)));
          hero.classList.add('is-moviendo');
          auto = requestAnimationFrame(pasear);
        };
        auto = requestAnimationFrame(pasear);
      }
    }
    if (toggle) toggle.addEventListener('click', function () {
      var completo = !hero.classList.contains('is-completo');
      hero.classList.remove('is-moviendo');
      hero.classList.toggle('is-completo', completo);
      toggle.setAttribute('aria-pressed', String(completo));
      toggle.textContent = completo ? 'Volver al después' : 'Ver todo el antes';
    });
  });

  /* ---------- Antes / después ---------- */
  function comparador(box) {
    var linea = $('.fz-comparar__linea', box), pos = 50, arrastrando = false;
    function poner(p) { pos = clamp(p, 0, 100); box.style.setProperty('--pos', pos + '%'); linea.setAttribute('aria-valuenow', String(Math.round(pos))); }
    function desdeEvento(e) { var r = box.getBoundingClientRect(); poner((e.clientX - r.left) / r.width * 100); }
    box.addEventListener('pointerdown', function (e) {
      if (e.button !== undefined && e.button !== 0) return;
      arrastrando = true; box.classList.add('is-arrastrando'); desdeEvento(e);
      if (box.setPointerCapture) { try { box.setPointerCapture(e.pointerId); } catch (err) {} }
    });
    box.addEventListener('pointermove', function (e) { if (arrastrando) desdeEvento(e); });
    ['pointerup', 'pointercancel', 'lostpointercapture'].forEach(function (ev) {
      box.addEventListener(ev, function () { arrastrando = false; box.classList.remove('is-arrastrando'); });
    });
    linea.addEventListener('keydown', function (e) {
      var d = { ArrowLeft: -5, ArrowDown: -5, ArrowRight: 5, ArrowUp: 5, Home: -100, End: 100 }[e.key];
      if (d === undefined) return; e.preventDefault(); poner(pos + d);
    });
    // Pequeño vaivén para mostrar que se puede arrastrar.
    box.mostrar = function () {
      if (reducir || box.dataset.mostrado) return; box.dataset.mostrado = '1';
      var t0 = performance.now();
      (function anim(now) {
        if (arrastrando) return;
        var t = (now - t0) / 1000; if (t > 1.6) { poner(50); return; }
        poner(50 + Math.sin(t * Math.PI * 1.25) * 16 * (1 - t / 1.6)); requestAnimationFrame(anim);
      })(t0);
    };
    poner(50);
  }
  var comparadores = $$('[data-comparar]');
  comparadores.forEach(comparador);
  if ('IntersectionObserver' in window) {
    var ioC = new IntersectionObserver(function (ents) {
      ents.forEach(function (en) { if (en.isIntersecting && en.target.mostrar) { en.target.mostrar(); ioC.unobserve(en.target); } });
    }, { threshold: 0.5 });
    comparadores.forEach(function (c) { ioC.observe(c); });
  }
  var tabs = $$('.fz-ad__tab');
  function elegirTab(i, foco) {
    tabs.forEach(function (t, k) { var on = k === i; t.setAttribute('aria-selected', String(on)); t.tabIndex = on ? 0 : -1; });
    comparadores.forEach(function (c, k) { c.hidden = k !== i; if (k === i && c.mostrar) { delete c.dataset.mostrado; c.mostrar(); } });
    $$('[data-ad-ficha]').forEach(function (f, k) { f.hidden = k !== i; });
    if (foco) tabs[i].focus();
  }
  tabs.forEach(function (t, i) {
    t.addEventListener('click', function () { elegirTab(i); });
    t.addEventListener('keydown', function (e) {
      var d = { ArrowDown: 1, ArrowRight: 1, ArrowUp: -1, ArrowLeft: -1 }[e.key];
      if (!d) return; e.preventDefault(); elegirTab((i + d + tabs.length) % tabs.length, true);
    });
  });

  /* ---------- Paso a paso: cinta métrica ---------- */
  $$('[data-cinta]').forEach(function (sec) {
    var fotos = $$('[data-cinta-foto]', sec), etiquetas = $$('[data-cinta-etiqueta]', sec);
    var pista = $('[data-cinta-pista]', sec), caja = $('[data-cinta-caja]', sec);
    var num = $('[data-cinta-num]', sec), tit = $('[data-cinta-titulo]', sec), txt = $('[data-cinta-texto]', sec);
    var etapas = []; try { etapas = JSON.parse($('[data-cinta-etapas]', sec).textContent); } catch (e) {}
    var n = fotos.length, pos = 0, idx = -1, arrastrando = false, tocado = false;
    function poner(p) {
      pos = clamp(p, 0, 100);
      var f = pos / 100 * (n - 1), i = Math.round(f);
      caja.style.left = pos + '%';
      fotos.forEach(function (img, k) { img.style.opacity = Math.max(0, 1 - Math.abs(f - k)).toFixed(3); });
      if (i !== idx) {
        idx = i;
        num.textContent = String(i + 1).padStart(2, '0');
        if (etapas[i]) { tit.textContent = etapas[i].titulo; txt.textContent = etapas[i].texto; }
        etiquetas.forEach(function (et, k) { et.classList.toggle('is-activa', k === i); });
        pista.setAttribute('aria-valuenow', String(i + 1));
      }
    }
    function desdeEvento(e) { var r = pista.getBoundingClientRect(); poner((e.clientX - r.left) / r.width * 100); }
    pista.addEventListener('pointerdown', function (e) {
      tocado = true; arrastrando = true; pista.classList.add('is-arrastrando'); desdeEvento(e);
      if (pista.setPointerCapture) { try { pista.setPointerCapture(e.pointerId); } catch (err) {} }
    });
    pista.addEventListener('pointermove', function (e) { if (arrastrando) desdeEvento(e); });
    ['pointerup', 'pointercancel', 'lostpointercapture'].forEach(function (ev) {
      pista.addEventListener(ev, function () { arrastrando = false; pista.classList.remove('is-arrastrando'); });
    });
    pista.addEventListener('keydown', function (e) {
      var paso = 100 / (n - 1), d = { ArrowLeft: -paso, ArrowDown: -paso, ArrowRight: paso, ArrowUp: paso }[e.key];
      if (e.key === 'Home') d = -100; if (e.key === 'End') d = 100;
      if (d === undefined) return; e.preventDefault(); tocado = true; poner(Math.round((pos + d) / paso) * paso);
    });
    poner(0);
    // Cuando la sección entra en pantalla, la cinta recorre la obra una vez sola.
    if (!reducir && 'IntersectionObserver' in window) {
      var io = new IntersectionObserver(function (ents) {
        if (!ents[0].isIntersecting) return; io.disconnect();
        var t0 = performance.now(), dur = 7000;
        (function anim(now) {
          if (tocado) return; var t = Math.min(1, (now - t0) / dur);
          poner(t * 100); if (t < 1) requestAnimationFrame(anim);
        })(t0);
      }, { threshold: 0.55 });
      io.observe(sec);
    }
  });

  /* ---------- Listado: filtro por rubro ---------- */
  $$('[data-listado]').forEach(function (lista) {
    var chips = $$('.fz-chip', lista), cards = $$('.fz-card', lista), cuenta = $('[data-listado-cuenta]', lista);
    chips.forEach(function (chip) {
      chip.addEventListener('click', function () {
        var r = chip.dataset.rubro, n = 0;
        chips.forEach(function (c) { c.setAttribute('aria-pressed', String(c === chip)); });
        cards.forEach(function (card) { var ok = r === 'todas' || card.dataset.rubro === r; card.hidden = !ok; if (ok) n++; });
        if (cuenta) cuenta.textContent = n + (n === 1 ? ' obra' : ' obras');
      });
    });
  });

  /* ---------- Galería: lightbox ---------- */
  $$('[data-galeria]').forEach(function (gal) {
    var items = $$('[data-galeria-i]', gal), lb = $('[data-lightbox]', gal);
    if (!lb || !items.length) return;
    var img = $('[data-lb-img]', lb), cap = $('[data-lb-cap]', lb), i = 0, origen = null;
    function mostrar(k) {
      i = (k + items.length) % items.length;
      img.src = items[i].dataset.full; img.alt = $('img', items[i]).alt;
      cap.textContent = (i + 1) + ' / ' + items.length;
    }
    function abrir(k) { origen = document.activeElement; mostrar(k); lb.hidden = false; document.body.style.overflow = 'hidden'; $('[data-lb-cerrar]', lb).focus(); }
    function cerrar() { lb.hidden = true; document.body.style.overflow = ''; if (origen) origen.focus(); }
    items.forEach(function (it, k) { it.addEventListener('click', function () { abrir(k); }); });
    $('[data-lb-cerrar]', lb).addEventListener('click', cerrar);
    $('[data-lb-prev]', lb).addEventListener('click', function () { mostrar(i - 1); });
    $('[data-lb-next]', lb).addEventListener('click', function () { mostrar(i + 1); });
    lb.addEventListener('click', function (e) { if (e.target === lb) cerrar(); });
    document.addEventListener('keydown', function (e) {
      if (lb.hidden) return;
      if (e.key === 'Escape') cerrar();
      if (e.key === 'ArrowLeft') mostrar(i - 1);
      if (e.key === 'ArrowRight') mostrar(i + 1);
    });
    var x0 = null;
    lb.addEventListener('touchstart', function (e) { x0 = e.touches[0].clientX; }, { passive: true });
    lb.addEventListener('touchend', function (e) { if (x0 === null) return; var dx = e.changedTouches[0].clientX - x0; if (Math.abs(dx) > 50) mostrar(i + (dx < 0 ? 1 : -1)); x0 = null; });
  });
})();
