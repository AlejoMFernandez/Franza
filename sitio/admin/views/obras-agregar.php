<?php

use App\Autenticacion\Autenticacion;
use App\Modelos\Obras;

if(isset($_SESSION['errores'])) {
    $errores = $_SESSION['errores'];
    unset($_SESSION['errores']);
} else {
    $errores = [];
}

if(isset($_SESSION['datos_viejos'])) {
    $datosViejos = $_SESSION['datos_viejos'];
    unset($_SESSION['datos_viejos']);
} else {
    $datosViejos = [];
}
?>
<section class="admin-panel">

    <div class="admin-panel-header">
        <div>
            <h1 class="admin-panel-title">Agregar obra</h1>
            <p class="admin-panel-subtitle">Completá los datos de la nueva obra</p>
        </div>
        <a href="index.php?seccion=obras" class="admin-btn admin-btn-secondary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
            Volver
        </a>
    </div>

    <?php if(count($errores) > 0): ?>
        <div class="admin-errors">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <div>
                <?php foreach($errores as $error): ?>
                    <p><?= $error ?></p>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <form class="admin-form" action="acciones/obras-crear.php" method="post" enctype="multipart/form-data">
        <?= \App\Seguridad\Csrf::campo() ?>

        <div class="admin-form-grid">
            <!-- Left: Form fields -->
            <div class="admin-form-fields">
                <div class="admin-form-group">
                    <label for="obra-titulo" class="admin-label">Título de la obra</label>
                    <input type="text" id="obra-titulo" name="titulo" class="admin-input <?= isset($errores['titulo']) ? 'admin-input-error' : '' ?>" placeholder="Ej: Construcción completa de hogar" value="<?= htmlspecialchars($datosViejos['titulo'] ?? '') ?>">
                </div>

                <div class="admin-form-group">
                    <label for="obra-ubicacion" class="admin-label">Ubicación</label>
                    <input type="text" id="obra-ubicacion" name="ubicacion" class="admin-input <?= isset($errores['ubicacion']) ? 'admin-input-error' : '' ?>" placeholder="Ej: Villa del Parque, CABA" value="<?= htmlspecialchars($datosViejos['ubicacion'] ?? '') ?>">
                </div>

                <div class="admin-form-group">
                    <label for="obra-explicacion" class="admin-label">Descripción del proyecto</label>
                    <textarea id="obra-explicacion" name="explicacion" class="admin-textarea <?= isset($errores['explicacion']) ? 'admin-input-error' : '' ?>" placeholder="Describí qué se hizo en esta obra..." rows="5"><?= htmlspecialchars($datosViejos['explicacion'] ?? '') ?></textarea>
                </div>

                <div class="admin-form-group">
                    <label class="admin-label">Tipo de obra</label>
                    <div class="admin-tipo-pills">
                        <button type="button" class="admin-pill <?= (isset($datosViejos['tipo']) && $datosViejos['tipo'] === 'civil') ? 'active' : '' ?>" onclick="seleccionarTipo('civil', this)">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                            Civil
                        </button>
                        <button type="button" class="admin-pill <?= (isset($datosViejos['tipo']) && $datosViejos['tipo'] === 'industrial') ? 'active' : '' ?>" onclick="seleccionarTipo('industrial', this)">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="15" rx="2"/><path d="M16 7V3a1 1 0 00-1-1H9a1 1 0 00-1 1v4"/></svg>
                            Industrial
                        </button>
                        <input type="hidden" name="tipo" id="obra-tipo" value="<?= htmlspecialchars($datosViejos['tipo'] ?? '') ?>">
                    </div>
                </div>
            </div>

            <!-- Right: Image upload -->
            <div class="admin-form-images">
                <div class="admin-form-group">
                    <label class="admin-label">Imagen principal <span class="admin-label-opt">(opcional)</span></label>
                    <div class="admin-upload-zone" id="main-upload-zone" onclick="document.getElementById('archivo-obra').click();">
                        <input type="file" id="archivo-obra" name="imagen" style="display:none;" accept="image/*" onchange="previewMainImage(event)">
                        <div id="main-upload-placeholder" class="admin-upload-placeholder">
                            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>
                            <p>Hacé clic para subir la imagen de portada</p>
                            <span>JPG, PNG, WEBP — máx. 5MB</span>
                        </div>
                        <img id="preview-main-img" src="" alt="Previsualización" style="display:none;">
                    </div>
                </div>

                <div class="admin-form-group">
                    <label class="admin-label">Galería de fotos <span class="admin-label-opt">(opcional)</span></label>
                    <div class="admin-upload-zone admin-upload-gallery" id="gallery-upload-zone" onclick="document.getElementById('archivo-galeria').click();">
                        <input type="file" id="archivo-galeria" name="galeria[]" style="display:none;" accept="image/*" multiple onchange="previewGalleryImages(event)">
                        <div id="gallery-upload-placeholder" class="admin-upload-placeholder">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                            <p>Subir fotos adicionales para la galería</p>
                            <span>Podés seleccionar varias a la vez</span>
                        </div>
                    </div>
                    <div id="gallery-preview-grid" class="admin-gallery-preview"></div>
                </div>
            </div>
        </div>

        <div class="admin-form-actions">
            <a href="index.php?seccion=obras" class="admin-btn admin-btn-secondary">Cancelar</a>
            <button type="submit" class="admin-btn admin-btn-primary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>
                Publicar obra
            </button>
        </div>
    </form>

</section>