<?php

use App\Modelos\Obras;
use App\Seguridad\Csrf;

$obras = (new Obras)->todas();
$totalObras = count($obras);

?>
<section class="admin-panel">

    <div class="admin-panel-header">
        <div>
            <h1 class="admin-panel-title">Mis Obras</h1>
            <p class="admin-panel-subtitle"><?= $totalObras ?> obra<?= $totalObras !== 1 ? 's' : '' ?> en total</p>
        </div>
        <div class="admin-header-actions">
            <?php if($totalObras > 0): ?>
            <div class="admin-view-toggle">
                <button type="button" class="admin-view-btn active" data-view="grid" onclick="cambiarVista('grid', this)" title="Vista grilla">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
                </button>
                <button type="button" class="admin-view-btn" data-view="table" onclick="cambiarVista('table', this)" title="Vista tabla">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
                </button>
            </div>
            <?php endif; ?>
            <a href="index.php?seccion=obras-agregar" class="admin-btn admin-btn-primary">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Agregar obra
            </a>
        </div>
    </div>

    <?php if($totalObras === 0): ?>
        <div class="admin-empty">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
            <h3>No hay obras todavía</h3>
            <p>Empezá agregando tu primera obra para que aparezca en el sitio.</p>
            <a href="index.php?seccion=obras-agregar" class="admin-btn admin-btn-primary">Agregar primera obra</a>
        </div>
    <?php else: ?>

        <?php
        // Pre-compute data for both views
        $obrasData = [];
        foreach($obras as $obra) {
            $tipoObra = htmlspecialchars($obra->getTipo());
            $carpetaObra = htmlspecialchars($obra->getCarpeta());
            $imagenObra = htmlspecialchars($obra->getImagen());
            $tituloObra = htmlspecialchars($obra->getTitulo());
            $ubicacionObra = htmlspecialchars($obra->getUbicacion());
            $imgRelPath = "../img/obras{$tipoObra}es/{$carpetaObra}/{$imagenObra}";
            $imgAbsPath = __DIR__ . "/../../img/obras{$tipoObra}es/{$carpetaObra}/{$imagenObra}";
            $tieneImagen = !empty($imagenObra) && file_exists($imgAbsPath);
            $numFotos = count($obra->getGaleriaImagenes());
            $obrasData[] = [
                'id' => (int)$obra->getObraId(),
                'tipo' => $tipoObra,
                'titulo' => $tituloObra,
                'tituloJson' => htmlspecialchars(json_encode($obra->getTitulo()), ENT_QUOTES),
                'ubicacion' => $ubicacionObra,
                'imgRelPath' => $imgRelPath,
                'tieneImagen' => $tieneImagen,
                'numFotos' => $numFotos,
            ];
        }
        ?>

        <!-- ==================== -->
        <!-- VISTA GRILLA         -->
        <!-- ==================== -->
        <div id="vista-grid" class="admin-vista active">
            <div class="admin-obras-grid">
                <?php foreach($obrasData as $od): ?>
                    <div class="admin-obra-card">
                        <div class="admin-obra-img">
                            <?php if($od['tieneImagen']): ?>
                                <img src="<?= $od['imgRelPath'] ?>" alt="<?= $od['titulo'] ?>">
                            <?php else: ?>
                                <div class="admin-obra-no-img">
                                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>
                                </div>
                            <?php endif; ?>
                            <span class="admin-obra-badge admin-badge-<?= $od['tipo'] ?>"><?= ucfirst($od['tipo']) ?></span>
                        </div>
                        <div class="admin-obra-body">
                            <h3 class="admin-obra-title"><?= $od['titulo'] ?></h3>
                            <p class="admin-obra-location">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                                <?= $od['ubicacion'] ?>
                            </p>
                            <span class="admin-obra-photos">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>
                                <?= $od['numFotos'] ?> foto<?= $od['numFotos'] !== 1 ? 's' : '' ?>
                            </span>
                        </div>
                        <div class="admin-obra-actions">
                            <a href="index.php?seccion=obras-editar&id=<?= $od['id'] ?>" class="admin-btn-icon admin-btn-edit" title="Editar obra">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                <span>Editar</span>
                            </a>
                            <button type="button"
                                onclick="confirmarEliminacion(<?= $od['id'] ?>, <?= $od['tituloJson'] ?>)"
                                class="admin-btn-icon admin-btn-delete" title="Eliminar obra">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                                <span>Eliminar</span>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- ==================== -->
        <!-- VISTA TABLA          -->
        <!-- ==================== -->
        <div id="vista-table" class="admin-vista">
            <!-- Bulk actions bar (hidden until selection) -->
            <div id="bulk-bar" class="admin-bulk-bar" style="display:none;">
                <div class="admin-bulk-info">
                    <span id="bulk-count">0</span> obra(s) seleccionada(s)
                </div>
                <div class="admin-bulk-actions">
                    <button type="button" class="admin-btn admin-btn-secondary admin-btn-sm" onclick="deseleccionarTodas()">
                        Deseleccionar
                    </button>
                    <button type="button" class="admin-btn admin-btn-danger admin-btn-sm" onclick="confirmarEliminacionMasiva()">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg>
                        Eliminar selección
                    </button>
                </div>
            </div>

            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th class="admin-th-check">
                                <label class="admin-checkbox">
                                    <input type="checkbox" id="check-all" onchange="toggleSelectAll(this)">
                                    <span class="admin-checkmark"></span>
                                </label>
                            </th>
                            <th class="admin-th-img">Imagen</th>
                            <th>Título</th>
                            <th>Ubicación</th>
                            <th>Tipo</th>
                            <th>Fotos</th>
                            <th class="admin-th-actions">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($obrasData as $od): ?>
                        <tr class="admin-table-row" data-id="<?= $od['id'] ?>">
                            <td class="admin-td-check">
                                <label class="admin-checkbox">
                                    <input type="checkbox" class="obra-check" value="<?= $od['id'] ?>" data-titulo="<?= $od['titulo'] ?>" onchange="actualizarSeleccion()">
                                    <span class="admin-checkmark"></span>
                                </label>
                            </td>
                            <td class="admin-td-img">
                                <?php if($od['tieneImagen']): ?>
                                    <img src="<?= $od['imgRelPath'] ?>" alt="<?= $od['titulo'] ?>">
                                <?php else: ?>
                                    <div class="admin-table-no-img">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="admin-td-title"><?= $od['titulo'] ?></td>
                            <td class="admin-td-location"><?= $od['ubicacion'] ?></td>
                            <td>
                                <span class="admin-table-badge admin-badge-<?= $od['tipo'] ?>"><?= ucfirst($od['tipo']) ?></span>
                            </td>
                            <td class="admin-td-photos"><?= $od['numFotos'] ?></td>
                            <td class="admin-td-actions">
                                <div class="admin-td-actions-wrap">
                                    <a href="index.php?seccion=obras-editar&id=<?= $od['id'] ?>" class="admin-table-action admin-btn-edit" title="Editar">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                    </a>
                                    <button type="button" class="admin-table-action admin-btn-delete"
                                        onclick="confirmarEliminacion(<?= $od['id'] ?>, <?= $od['tituloJson'] ?>)" title="Eliminar">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    <?php endif; ?>

</section>

<!-- POPUP ELIMINAR -->
<div id="popup-eliminar" class="admin-popup-overlay" style="display: none;">
    <div class="admin-popup">
        <div class="admin-popup-icon">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
        </div>
        <h3 id="popup-titulo">Eliminar obra</h3>
        <p id="popup-mensaje"></p>
        <div class="admin-popup-btns">
            <button onclick="cerrarPopup()" class="admin-btn admin-btn-secondary">Cancelar</button>
            <button id="popup-btn-confirmar" onclick="eliminarObra()" class="admin-btn admin-btn-danger">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg>
                Eliminar
            </button>
        </div>
    </div>
</div>
<script>const csrfToken = '<?= htmlspecialchars(Csrf::obtener(), ENT_QUOTES) ?>';</script>