/**
 * popup.js — Admin: eliminación individual/masiva, toggle vista, selección tabla
 *
 * Funciones:
 *   confirmarEliminacion()       - Popup para eliminar una obra
 *   confirmarEliminacionMasiva() - Popup para eliminar varias obras seleccionadas
 *   abrirPopup() / cerrarPopup() - Control del popup modal
 *   cambiarVista()               - Alterna entre vista grilla y tabla
 *   toggleSelectAll()            - Seleccionar/deseleccionar todas las obras
 *   actualizarSeleccion()        - Actualiza bulk bar y checkboxes
 *   seleccionarTipo()            - Alterna pills de tipo (civil/industrial)
 */

let obraIdEliminar = null;
let eliminacionMasiva = false;
let idsParaEliminar = [];

function confirmarEliminacion(id, titulo) {
    eliminacionMasiva = false;
    obraIdEliminar = id;
    var mensajeEl = document.getElementById('popup-mensaje');
    var tituloEl = document.getElementById('popup-titulo');
    var btnEl = document.getElementById('popup-btn-confirmar');
    if (tituloEl) tituloEl.textContent = 'Eliminar obra';
    if (mensajeEl) {
        mensajeEl.innerHTML = '¿Estás seguro de que querés eliminar <b>"' + titulo + '"</b>? Esta acción no se puede deshacer.';
    }
    if (btnEl) {
        btnEl.onclick = eliminarObra;
        btnEl.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg> Eliminar';
    }
    abrirPopup();
}

function confirmarEliminacionMasiva() {
    var checks = document.querySelectorAll('.obra-check:checked');
    if (checks.length === 0) return;

    eliminacionMasiva = true;
    idsParaEliminar = [];
    checks.forEach(function(c) { idsParaEliminar.push(c.value); });

    var tituloEl = document.getElementById('popup-titulo');
    var mensajeEl = document.getElementById('popup-mensaje');
    var btnEl = document.getElementById('popup-btn-confirmar');

    if (tituloEl) tituloEl.textContent = 'Eliminar ' + idsParaEliminar.length + ' obra' + (idsParaEliminar.length > 1 ? 's' : '');
    if (mensajeEl) {
        if (idsParaEliminar.length === 1) {
            var titulo = checks[0].getAttribute('data-titulo');
            mensajeEl.innerHTML = '¿Estás seguro de que querés eliminar <b>"' + titulo + '"</b>? Esta acción no se puede deshacer.';
        } else {
            mensajeEl.innerHTML = '¿Estás seguro de que querés eliminar <b>' + idsParaEliminar.length + ' obras</b>? Se eliminarán todas las imágenes asociadas. Esta acción no se puede deshacer.';
        }
    }
    if (btnEl) {
        btnEl.onclick = eliminarObrasMasivo;
        btnEl.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg> Eliminar ' + idsParaEliminar.length;
    }
    abrirPopup();
}

function abrirPopup() {
    var popup = document.getElementById('popup-eliminar');
    if (!popup) return;
    popup.style.display = 'flex';
    popup.offsetHeight;
    popup.classList.add('active');
    document.body.style.overflow = 'hidden';
}

function cerrarPopup() {
    var popup = document.getElementById('popup-eliminar');
    if (!popup) return;
    popup.classList.remove('active');
    document.body.style.overflow = '';
    setTimeout(function() {
        popup.style.display = 'none';
        obraIdEliminar = null;
        eliminacionMasiva = false;
    }, 200);
}

function eliminarObra() {
    if (obraIdEliminar && typeof csrfToken !== 'undefined') {
        window.location.href = 'acciones/obras-eliminar.php?id=' + obraIdEliminar + '&_csrf_token=' + encodeURIComponent(csrfToken);
    }
}

function eliminarObrasMasivo() {
    if (idsParaEliminar.length === 0 || typeof csrfToken === 'undefined') return;
    var ids = idsParaEliminar.join(',');
    window.location.href = 'acciones/obras-eliminar-masivo.php?ids=' + encodeURIComponent(ids) + '&_csrf_token=' + encodeURIComponent(csrfToken);
}

// Close popup on overlay click
document.addEventListener('click', function(e) {
    if (e.target && e.target.id === 'popup-eliminar') {
        cerrarPopup();
    }
});

// Close popup on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        var popup = document.getElementById('popup-eliminar');
        if (popup && popup.style.display !== 'none') {
            cerrarPopup();
        }
    }
});

/* ===================== */
/* View toggle           */
/* ===================== */
function cambiarVista(vista, btn) {
    // Toggle button active
    document.querySelectorAll('.admin-view-btn').forEach(function(b) { b.classList.remove('active'); });
    btn.classList.add('active');

    // Toggle views
    document.querySelectorAll('.admin-vista').forEach(function(v) { v.classList.remove('active'); });
    var target = document.getElementById('vista-' + vista);
    if (target) target.classList.add('active');

    // Save preference
    try { localStorage.setItem('adminVistaObras', vista); } catch(e) {}
}

// Restore preferred view on load
document.addEventListener('DOMContentLoaded', function() {
    try {
        var saved = localStorage.getItem('adminVistaObras');
        if (saved === 'table') {
            var btn = document.querySelector('.admin-view-btn[data-view="table"]');
            if (btn) cambiarVista('table', btn);
        }
    } catch(e) {}
});

/* ===================== */
/* Table selection       */
/* ===================== */
function toggleSelectAll(masterCheck) {
    var checks = document.querySelectorAll('.obra-check');
    checks.forEach(function(c) { c.checked = masterCheck.checked; });
    actualizarSeleccion();
}

function actualizarSeleccion() {
    var checks = document.querySelectorAll('.obra-check');
    var checked = document.querySelectorAll('.obra-check:checked');
    var bulkBar = document.getElementById('bulk-bar');
    var bulkCount = document.getElementById('bulk-count');
    var masterCheck = document.getElementById('check-all');

    // Update bulk bar
    if (checked.length > 0) {
        if (bulkBar) bulkBar.style.display = 'flex';
        if (bulkCount) bulkCount.textContent = checked.length;
    } else {
        if (bulkBar) bulkBar.style.display = 'none';
    }

    // Update master checkbox state
    if (masterCheck) {
        masterCheck.checked = checks.length > 0 && checked.length === checks.length;
        masterCheck.indeterminate = checked.length > 0 && checked.length < checks.length;
    }

    // Highlight selected rows
    document.querySelectorAll('.admin-table-row').forEach(function(row) {
        var cb = row.querySelector('.obra-check');
        if (cb && cb.checked) {
            row.classList.add('selected');
        } else {
            row.classList.remove('selected');
        }
    });
}

function deseleccionarTodas() {
    var masterCheck = document.getElementById('check-all');
    if (masterCheck) masterCheck.checked = false;
    document.querySelectorAll('.obra-check').forEach(function(c) { c.checked = false; });
    actualizarSeleccion();
}

function seleccionarTipo(tipo, btn) {
    document.getElementById('obra-tipo').value = tipo;
    document.querySelectorAll('.admin-pill').forEach(function(b) { b.classList.remove('active'); });
    btn.classList.add('active');
}

