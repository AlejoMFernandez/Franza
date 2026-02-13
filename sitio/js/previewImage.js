/**
 * previewImage.js — Vista previa y eliminación diferida de imágenes (admin)
 * 
 * Funciones:
 *   previewMainImage()        - Preview de imagen principal al seleccionar archivo
 *   previewGalleryImages()    - Preview de múltiples fotos de galería
 *   eliminarFotoGaleria()     - Marca foto de galería para eliminar (al guardar)
 *   eliminarImagenPrincipal() - Marca imagen principal para eliminar (al guardar)
 */

/**
 * Preview main image upload in the admin upload zone
 */
function previewMainImage(event) {
    var input = event.target;
    var preview = document.getElementById('preview-main-img');
    var placeholder = document.getElementById('main-upload-placeholder');

    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
            if (placeholder) placeholder.style.display = 'none';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

/**
 * Preview multiple gallery images before upload
 */
function previewGalleryImages(event) {
    var input = event.target;
    var grid = document.getElementById('gallery-preview-grid');
    var placeholder = document.getElementById('gallery-upload-placeholder');
    if (!grid) return;

    // Clear previous previews
    grid.innerHTML = '';

    if (input.files && input.files.length > 0) {
        // Hide placeholder and show count
        if (placeholder) {
            placeholder.innerHTML = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>' +
                '<p>' + input.files.length + ' foto' + (input.files.length !== 1 ? 's' : '') + ' seleccionada' + (input.files.length !== 1 ? 's' : '') + '</p>' +
                '<span>Hacé clic para cambiar la selección</span>';
            placeholder.classList.add('admin-upload-success');
        }

        Array.from(input.files).forEach(function(file, index) {
            var reader = new FileReader();
            reader.onload = function(e) {
                var item = document.createElement('div');
                item.classList.add('admin-gallery-preview-item');

                var img = document.createElement('img');
                img.src = e.target.result;
                img.alt = file.name;

                item.appendChild(img);
                grid.appendChild(item);
            };
            reader.readAsDataURL(file);
        });
    } else {
        // Restore placeholder
        if (placeholder) {
            placeholder.innerHTML = '<svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>' +
                '<p>Subir fotos adicionales para la galería</p>' +
                '<span>Podés seleccionar varias a la vez</span>';
            placeholder.classList.remove('admin-upload-success');
        }
    }
}

/**
 * Mark a gallery photo for deletion (deferred until form save)
 */
function eliminarFotoGaleria(obraId, archivo, thumbHash) {
    var thumb = document.getElementById('thumb-' + thumbHash);
    if (thumb) {
        thumb.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
        thumb.style.opacity = '0';
        thumb.style.transform = 'scale(0.8)';
        setTimeout(function() { thumb.remove(); }, 300);
    }

    // Add hidden input to mark for deletion on save
    var form = document.querySelector('.admin-form');
    if (form) {
        var input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'eliminar_galeria[]';
        input.value = archivo;
        form.appendChild(input);
    }
}

/**
 * Mark the main (portada) image for deletion (deferred until form save)
 */
function eliminarImagenPrincipal(obraId) {
    var preview = document.getElementById('preview-main-img');
    var placeholder = document.getElementById('main-upload-placeholder');
    var deleteBtn = document.getElementById('main-img-delete-btn');
    if (preview) { preview.style.display = 'none'; preview.src = ''; }
    if (placeholder) placeholder.style.display = '';
    if (deleteBtn) deleteBtn.style.display = 'none';

    // Add hidden input to mark for deletion on save
    var form = document.querySelector('.admin-form');
    if (form) {
        var input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'eliminar_portada';
        input.value = '1';
        form.appendChild(input);
    }
}