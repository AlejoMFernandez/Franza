<?php

use App\Modelos\Obras;
use App\Seguridad\Csrf;

require_once __DIR__ . '/../../bootstrap/init.php';

$obra = (new Obras)->porId((int)($_GET['id'] ?? 0));

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
            <h1 class="admin-panel-title">Editar obra</h1>
            <p class="admin-panel-subtitle"><?= $obra ? htmlspecialchars($obra->getTitulo()) : 'Obra no encontrada' ?></p>
        </div>
        <a href="index.php?seccion=obras" class="admin-btn admin-btn-secondary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
            Volver
        </a>
    </div>

    <?php if(!$obra): ?>
        <div class="admin-empty">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <h3>Obra no encontrada</h3>
            <p>La obra que intentás editar no existe.</p>
            <a href="index.php?seccion=obras" class="admin-btn admin-btn-primary">Volver a obras</a>
        </div>
    <?php else: ?>

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

        <?php
            $galeriaActual = $obra->getGaleriaImagenes();
            $rutaBase = $obra->getRutaImagenes();
            $imgPortada = $obra->getImagen();
        ?>

        <form class="admin-form" action="acciones/obras-editar.php?id=<?= $obra->getObraId() ?>" method="post" enctype="multipart/form-data">
            <?= Csrf::campo() ?>

            <div class="admin-form-grid">
                <!-- Left: Form fields -->
                <div class="admin-form-fields">
                    <div class="admin-form-group">
                        <label for="titulo" class="admin-label">Título de la obra</label>
                        <input type="text" id="titulo" name="titulo" class="admin-input <?= isset($errores['titulo']) ? 'admin-input-error' : '' ?>" value="<?= htmlspecialchars($datosViejos['titulo'] ?? $obra->getTitulo()) ?>">
                    </div>

                    <div class="admin-form-group">
                        <label for="ubicacion" class="admin-label">Ubicación</label>
                        <input type="text" id="ubicacion" name="ubicacion" class="admin-input <?= isset($errores['ubicacion']) ? 'admin-input-error' : '' ?>" value="<?= htmlspecialchars($datosViejos['ubicacion'] ?? $obra->getUbicacion()) ?>">
                    </div>

                    <div class="admin-form-group">
                        <label for="explicacion" class="admin-label">Descripción del proyecto</label>
                        <textarea id="explicacion" name="explicacion" class="admin-textarea <?= isset($errores['explicacion']) ? 'admin-input-error' : '' ?>" rows="5"><?= htmlspecialchars($datosViejos['explicacion'] ?? $obra->getExplicacion()) ?></textarea>
                    </div>

                    <div class="admin-form-group">
                        <label class="admin-label">Tipo de obra</label>
                        <div class="admin-tipo-pills">
                            <?php $tipoSel = $datosViejos['tipo'] ?? $obra->getTipo(); ?>
                            <button type="button" class="admin-pill <?= $tipoSel === 'civil' ? 'active' : '' ?>" onclick="seleccionarTipo('civil', this)">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                                Civil
                            </button>
                            <button type="button" class="admin-pill <?= $tipoSel === 'industrial' ? 'active' : '' ?>" onclick="seleccionarTipo('industrial', this)">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="15" rx="2"/><path d="M16 7V3a1 1 0 00-1-1H9a1 1 0 00-1 1v4"/></svg>
                                Industrial
                            </button>
                            <input type="hidden" name="tipo" id="obra-tipo" value="<?= htmlspecialchars($tipoSel) ?>">
                        </div>
                    </div>
                </div>

                <!-- Right: Image upload -->
                <div class="admin-form-images">
                    <div class="admin-form-group">
                        <?php
                            $imgAbsPath = __DIR__ . '/../../' . $rutaBase . $imgPortada;
                            $tienePortada = !empty($imgPortada) && file_exists($imgAbsPath);
                        ?>
                        <label class="admin-label">Imagen principal</label>
                        <div class="admin-upload-zone" id="main-upload-zone" onclick="document.getElementById('imagen').click();">
                            <input type="file" id="imagen" name="imagen" style="display:none;" accept="image/*" onchange="previewMainImage(event)">
                            <div id="main-upload-placeholder" class="admin-upload-placeholder" <?= $tienePortada ? 'style="display:none;"' : '' ?>>
                                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>
                                <p>Hacé clic para cambiar la imagen de portada</p>
                                <span>JPG, PNG, WEBP — máx. 5MB</span>
                            </div>
                            <img id="preview-main-img" src="<?= $tienePortada ? '../' . htmlspecialchars($rutaBase . $imgPortada) : '' ?>" alt="Previsualización" <?= $tienePortada ? '' : 'style="display:none;"' ?>>
                            <?php if($tienePortada): ?>
                            <button type="button" id="main-img-delete-btn" class="admin-gallery-delete admin-main-img-delete" onclick="event.stopPropagation(); eliminarImagenPrincipal(<?= $obra->getObraId() ?>)" title="Eliminar imagen principal">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="admin-form-group">
                        <label class="admin-label">Galería de fotos <span class="admin-label-opt">(<?= count($galeriaActual) ?> foto<?= count($galeriaActual) !== 1 ? 's' : '' ?>)</span></label>

                        <!-- Existing gallery photos -->
                        <?php if(count($galeriaActual) > 0): ?>
                        <div class="admin-gallery-existing">
                            <?php foreach($galeriaActual as $gImg): ?>
                            <div class="admin-gallery-thumb" id="thumb-<?= md5($gImg) ?>">
                                <img src="../<?= htmlspecialchars($rutaBase . $gImg) ?>" alt="<?= htmlspecialchars($gImg) ?>">
                                <button type="button" class="admin-gallery-delete" onclick="eliminarFotoGaleria(<?= $obra->getObraId() ?>, '<?= htmlspecialchars($gImg, ENT_QUOTES) ?>', '<?= md5($gImg) ?>')" title="Eliminar foto">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                </button>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>

                        <!-- Add more photos -->
                        <div class="admin-upload-zone admin-upload-gallery" id="gallery-upload-zone" onclick="document.getElementById('archivo-galeria').click();">
                            <input type="file" id="archivo-galeria" name="galeria[]" style="display:none;" accept="image/*" multiple onchange="previewGalleryImages(event)">
                            <div id="gallery-upload-placeholder" class="admin-upload-placeholder">
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                                <p>Agregar más fotos a la galería</p>
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
                    Guardar cambios
                </button>
            </div>
        </form>

    <?php endif; ?>

</section>
<script>const csrfToken = '<?= htmlspecialchars(Csrf::obtener(), ENT_QUOTES) ?>';</script>