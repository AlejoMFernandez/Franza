<?php
/**
 * admin/views/obra-editor.php — Editor visual de obras.
 * La página se ve igual que la ficha pública de la obra, pero cada texto y cada foto
 * se edita en el lugar. Sirve para crear (obras-agregar) y para editar (obras-editar).
 */
use App\Modelos\Obras;
use App\Seguridad\Csrf;
use App\BaseDeDatos\Migracion;

$modelo = new Obras();
$obraId = (int) ($_GET['id'] ?? 0);
$obra = ($seccion === 'obras-editar') ? $modelo->porId($obraId) : null;

if ($seccion === 'obras-editar' && !$obra) {
    $_SESSION['mensajeFeedback'] = 'Esa obra no existe o se eliminó.';
    $_SESSION['estadoFeedback'] = 'error';
    header('Location: index.php?seccion=obras');
    exit;
}

$extra = $obra ? fz_extra($obra) : ['rubro' => '', 'etiquetas' => '', 'badge' => '', 'problema' => '', 'pasos' => [], 'duracion' => '', 'anio' => ''];
if (($extra['rubro'] ?? '') === 'Otras obras') { $extra['rubro'] = ''; }
$tipo = $obra ? $obra->getTipo() : ($_GET['tipo'] ?? 'civil');
$tipo = $tipo === 'industrial' ? 'industrial' : 'civil';
$fotos = $obra ? $obra->getMediosCarpeta() : [];
$fotos = array_values(array_filter($fotos, fn($f) => !preg_match('/^screenshot/i', $f)));
$portada = $obra ? $obra->getImagen() : '';
$rutaBase = $obra ? '../' . $obra->getRutaImagenes() : '';
$formato = !empty($extra['pasos']) ? 'pasos' : 'texto';
$extrasOk = Migracion::actualizada();

// Rubros ya usados, para sugerir.
$rubros = [];
foreach ($modelo->todas() as $o) {
    $r = fz_extra($o)['rubro'];
    if ($r && $r !== 'Otras obras') { $rubros[$r] = true; }
}
$rubros = array_keys($rubros + ['Fachadas' => 1, 'Azoteas y terrazas' => 1, 'Ampliaciones' => 1, 'Reformas' => 1, 'Obra nueva' => 1, 'Pisos industriales' => 1]);

$accion = 'acciones/obras-guardar.php' . ($obra ? '?id=' . $obra->getObraId() : '');
$numero = $obra ? str_pad((string) $obra->getObraId(), 3, '0', STR_PAD_LEFT) : 'NUEVA';
?>
<form class="ed" id="ed" action="<?= e($accion) ?>" method="post" enctype="multipart/form-data" data-editor novalidate
      data-obra="<?= $obra ? $obra->getObraId() : 'nueva' ?>" data-max-fotos="<?= Obras::MAX_FOTOS ?>" data-subir-url="acciones/fotos-subir.php"
      data-max-videos="<?= Obras::MAX_VIDEOS ?>" data-max-video-mb="<?= Obras::MAX_VIDEO_MB ?>" data-max-video-seg="<?= Obras::MAX_VIDEO_SEG ?>" data-video-url="acciones/video-subir.php">
    <?= Csrf::campo() ?>
    <input type="hidden" name="portada" value="<?= $portada ? 'f:' . e($portada) : '' ?>" data-portada-input>
    <input type="hidden" name="orden_fotos" value="" data-orden-input>
    <input type="file" name="fotos_nuevas[]" multiple hidden data-archivos-envio>
    <input type="file" accept="image/*,video/mp4,video/quicktime,video/webm,.mov,.m4v" multiple hidden data-archivos-elegir>
    <div data-eliminar-inputs></div>

    <!-- Barra del editor -->
    <div class="ed-barra">
        <a href="index.php?seccion=obras" class="ed-barra__volver"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="M19 12H5M11 6l-6 6 6 6"/></svg><span>Obras</span></a>
        <div class="ed-barra__titulo">
            <span class="fz-mono fz-oro-claro"><?= $obra ? 'Editando ' . e(fz_num($obra->getObraId())) : 'Nueva obra' ?></span>
            <span class="ed-barra__estado" data-estado>Sin cambios</span>
        </div>
        <div class="ed-barra__acciones">
            <button type="button" class="adm-btn adm-btn--linea adm-btn--chico" data-vista-previa aria-pressed="false">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                <span>Vista previa</span>
            </button>
            <?php if ($obra): ?>
                <a href="../<?= e(fz_url_obra($obra->getObraId())) ?>" target="_blank" rel="noopener" class="adm-btn adm-btn--linea adm-btn--chico ed-solo-desk">Ver en el sitio</a>
            <?php endif; ?>
            <button type="submit" class="adm-btn adm-btn--oro" data-guardar><?= $obra ? 'Guardar cambios' : 'Publicar obra' ?></button>
        </div>
    </div>

    <?php if (!$extrasOk): ?>
        <p class="ed-nota-mig">Hasta que actualices la base de datos (botón de arriba en el listado) solo se guardan título, lugar, tipo, texto y fotos.</p>
    <?php endif; ?>

    <!-- HERO -->
    <section class="fz-ficha-hero ed-hero <?= fz_titulo_tamano($obra ? $obra->getTitulo() : '', $obra ? $obra->getUbicacion() : '') ?>" data-hero-editor>
        <img src="<?= $portada ? e($rutaBase . $portada) : '' ?>" alt="" class="fz-ficha-hero__foto" data-hero-foto <?= $portada ? '' : 'hidden' ?>>
        <div class="ed-hero__vacio" data-hero-vacio <?= $portada ? 'hidden' : '' ?>>
            <span class="ed-hero__vacio-txt"><img src="../img/franza/isotipo.svg" alt="" width="54" height="74"><b>Todavía no hay portada</b><small>Tocá "Subir portada" o agregá fotos abajo y elegí una con la ★</small></span>
        </div>
        <div class="fz-ficha-hero__velo" aria-hidden="true"></div>
        <button type="button" class="adm-btn adm-btn--linea adm-btn--chico ed-hero__cambiar ed-control" data-subir-portada>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="1"/><circle cx="9" cy="10" r="2"/><path d="M21 16l-5-5-8 8"/></svg>
            <?= $portada ? 'Cambiar portada' : 'Subir portada' ?>
        </button>
        <div class="fz-wrap fz-ficha-hero__contenido">
            <div class="ed-tipo" role="radiogroup" aria-label="Tipo de obra">
                <span class="fz-mono">Obras /</span>
                <label class="ed-tipo__op"><input type="radio" name="tipo" value="civil" <?= $tipo === 'civil' ? 'checked' : '' ?>><span>Civiles</span></label>
                <label class="ed-tipo__op"><input type="radio" name="tipo" value="industrial" <?= $tipo === 'industrial' ? 'checked' : '' ?>><span>Industriales</span></label>
                <span class="fz-mono">/ N° <?= e($numero) ?></span>
            </div>
            <div class="fz-ficha-hero__abajo">
                <div class="ed-titulo">
                    <textarea name="titulo" rows="1" class="ed-campo fz-ficha-hero__titulo ed-titulo__input" placeholder="Título de la obra" required maxlength="100" data-autoalto data-espejo="titulo" aria-label="Título de la obra"><?= e($obra ? $obra->getTitulo() : '') ?></textarea>
                    <div class="ed-titulo__lugar fz-ficha-hero__titulo">
                        <span>en</span>
                        <input type="text" name="ubicacion" class="ed-campo" placeholder="Barrio o ciudad" required maxlength="100" value="<?= e($obra ? $obra->getUbicacion() : '') ?>" data-espejo="ubicacion" data-ancho-auto aria-label="Ubicación">
                    </div>
                </div>
                <span class="fz-ficha-hero__num" aria-hidden="true"><?= e($numero) ?></span>
            </div>
            <p class="ed-tamano ed-control" data-tamano-aviso aria-live="polite"></p>
            <label class="ed-badge ed-campo-envoltura">
                <span class="ed-ayuda">Etiqueta destacada (opcional)</span>
                <input type="text" name="badge" class="ed-campo fz-card__badge" maxlength="60" placeholder="Ej: Antes / después" value="<?= e($extra['badge']) ?>" data-espejo="badge" data-ancho-auto list="ed-badges">
                <datalist id="ed-badges"><option value="Antes / después"><option value="Paso a paso"><option value="Durante / terminada"><option value="Proyecto · Render 3D"></datalist>
            </label>
        </div>
    </section>

    <!-- FICHA -->
    <section class="fz-ficha-datos">
        <div class="fz-wrap fz-ficha-datos__grid">
            <div><span class="fz-mono">Ubicación</span><b data-mostrar="ubicacion"><?= e($obra ? $obra->getUbicacion() : '—') ?></b></div>
            <div>
                <span class="fz-mono">Tipo · rubro</span>
                <b class="ed-fila"><span data-mostrar-tipo><?= $tipo === 'civil' ? 'Civil' : 'Industrial' ?></span> ·
                    <input type="text" name="rubro" class="ed-campo" placeholder="Rubro" maxlength="80" value="<?= e($extra['rubro']) ?>" list="ed-rubros" data-espejo="rubro" data-ancho-auto aria-label="Rubro">
                </b>
                <datalist id="ed-rubros"><?php foreach ($rubros as $r): ?><option value="<?= e($r) ?>"><?php endforeach; ?></datalist>
            </div>
            <div><span class="fz-mono">Duración</span><b><input type="text" name="duracion" class="ed-campo" placeholder="Ej: 3 meses (opcional)" maxlength="60" value="<?= e($extra['duracion']) ?>" aria-label="Duración"></b></div>
            <div><span class="fz-mono">Año</span><b><input type="text" name="anio" class="ed-campo" placeholder="Ej: 2025 (opcional)" maxlength="10" inputmode="numeric" value="<?= e($extra['anio']) ?>" aria-label="Año"></b></div>
            <div class="fz-ficha-datos__cta ed-destacar">
                <label class="ed-switch">
                    <input type="checkbox" name="destacada" value="1" <?= ($obra && $obra->esDestacada()) ? 'checked' : '' ?>>
                    <span class="ed-switch__pista" aria-hidden="true"></span>
                    <span>Destacada en la home</span>
                </label>
            </div>
        </div>
    </section>

    <!-- TEXTO -->
    <section class="fz-ficha-texto">
        <div class="fz-wrap">
            <div class="ed-formato ed-control" role="radiogroup" aria-label="Cómo contar la obra">
                <span class="ed-ayuda">¿Cómo querés contar la obra?</span>
                <label><input type="radio" name="formato" value="texto" <?= $formato === 'texto' ? 'checked' : '' ?>><span>Texto corrido</span></label>
                <label><input type="radio" name="formato" value="pasos" <?= $formato === 'pasos' ? 'checked' : '' ?>><span>Problema + pasos</span></label>
            </div>
            <div class="fz-ficha-texto__grid" data-formato-texto <?= $formato === 'pasos' ? 'hidden' : '' ?>>
                <div class="fz-ficha-texto__col"><span class="fz-mono fz-gris">Sobre la obra</span></div>
                <div class="fz-ficha-texto__col fz-ficha-texto__col--der">
                    <textarea name="explicacion" class="ed-campo fz-ficha-texto__grande" rows="3" placeholder="Contá qué se hizo en la obra: el estado inicial, los trabajos y el resultado." data-autoalto aria-label="Texto de la obra"><?= e($obra ? $obra->getExplicacion() : '') ?></textarea>
                </div>
            </div>
            <div class="fz-ficha-texto__grid" data-formato-pasos <?= $formato === 'texto' ? 'hidden' : '' ?>>
                <div class="fz-ficha-texto__col">
                    <span class="fz-mono fz-gris">01 · El problema</span>
                    <textarea name="problema" class="ed-campo fz-ficha-texto__grande" rows="3" placeholder="¿Cómo estaba antes? Ej: un frente con grietas y hierros oxidados." data-autoalto aria-label="El problema"><?= e($extra['problema']) ?></textarea>
                </div>
                <div class="fz-ficha-texto__col fz-ficha-texto__col--der">
                    <span class="fz-mono fz-gris">02 · Lo que hicimos</span>
                    <ol class="fz-pasos" data-pasos>
                        <?php $pasos = $extra['pasos'] ?: ['']; foreach ($pasos as $i => $p): ?>
                            <li class="ed-paso"><span><?= $i + 1 ?></span><textarea name="pasos[]" class="ed-campo" rows="1" placeholder="Describí este paso" data-autoalto aria-label="Paso <?= $i + 1 ?>"><?= e($p) ?></textarea><button type="button" class="ed-quitar ed-control" data-quitar-paso aria-label="Quitar este paso">×</button></li>
                        <?php endforeach; ?>
                    </ol>
                    <button type="button" class="adm-btn adm-btn--linea-osc adm-btn--chico ed-control" data-agregar-paso>+ Agregar paso</button>
                </div>
            </div>
        </div>
    </section>

    <!-- GALERÍA -->
    <section class="fz-galeria ed-galeria">
        <div class="fz-wrap">
            <div class="fz-seccion-head">
                <h2 class="fz-h2">Todas las fotos</h2>
                <div class="ed-fotos-info ed-control">
                    <span class="ed-contador" data-contador-fotos></span>
                    <span class="fz-mono fz-gris">Arrastrá para ordenar · <b class="fz-oro-osc">★</b> = portada</span>
                </div>
            </div>
            <div class="ed-borrador ed-control" data-borrador hidden>
                <span data-borrador-texto></span>
                <button type="button" class="adm-btn adm-btn--oro adm-btn--chico" data-borrador-recuperar>Recuperar</button>
                <button type="button" class="adm-btn adm-btn--linea-osc adm-btn--chico" data-borrador-descartar>Descartar</button>
            </div>
            <div class="fz-galeria__grid ed-galeria__grid" data-galeria-editor>
                <?php foreach ($fotos as $f):
                    $esVideo = Obras::esVideo($f); $rutaF = $obra->getRutaImagenes() . $f;
                    $poster = $esVideo ? fz_poster($rutaF) : '';
                    [$pw, $ph] = $poster ? (@getimagesize(__DIR__ . '/../../' . $poster) ?: [0, 0]) : [0, 0];
                ?>
                    <div class="fz-galeria__item ed-foto<?= $esVideo ? ' es-video' : '' ?><?= $pw && $ph > $pw * 1.05 ? ' es-vertical' : '' ?><?= $f === $portada ? ' es-portada' : '' ?>" draggable="true" data-token="f:<?= e($f) ?>" data-archivo="<?= e($f) ?>">
                        <?php if ($esVideo): ?>
                            <?php if ($poster): ?><img src="<?= e('../' . $poster) ?>" alt="" draggable="false"><?php else: ?><video src="<?= e('../' . $rutaF) ?>#t=1" muted preload="metadata" playsinline></video><?php endif; ?>
                            <span class="ed-foto__video" aria-hidden="true"><svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg></span>
                        <?php else: ?>
                            <img src="<?= e('../' . fz_img($rutaF, 800)) ?>" alt="" draggable="false">
                        <?php endif; ?>
                        <div class="ed-foto__acciones ed-control">
                            <?php if (!$esVideo): ?><button type="button" class="ed-foto__btn" data-hacer-portada aria-label="Usar como portada" title="Usar como portada">★</button><?php endif; ?>
                            <button type="button" class="ed-foto__btn" data-borrar-foto aria-label="Eliminar <?= $esVideo ? 'video' : 'foto' ?>" title="Eliminar <?= $esVideo ? 'video' : 'foto' ?>">×</button>
                        </div>
                        <span class="ed-foto__tag">Portada</span>
                        <div class="ed-foto__borrada"><span>Se elimina al guardar</span><button type="button" class="adm-btn adm-btn--linea adm-btn--chico" data-deshacer-foto>Deshacer</button></div>
                    </div>
                <?php endforeach; ?>
                <button type="button" class="ed-agregar ed-control" data-agregar-fotos>
                    <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                    <span data-agregar-texto>Agregar fotos o videos</span>
                    <small>Tocá o arrastrá acá. Las fotos se achican solas. Videos: hasta <?= Obras::MAX_VIDEOS ?> por obra, <?= Obras::MAX_VIDEO_SEG ?> segundos y <?= Obras::MAX_VIDEO_MB ?> MB.</small>
                </button>
            </div>
        </div>
    </section>

    <!-- VISTA EN EL LISTADO -->
    <section class="ed-listado">
        <div class="fz-wrap ed-listado__grid">
            <div>
                <span class="fz-mono fz-gris">Así se ve en el listado de obras</span>
                <p class="ed-listado__texto">Las etiquetas son 3 o 4 trabajos cortos, separados por puntos. Ayudan a entender la obra de un vistazo.</p>
                <?php if (Migracion::tieneSlug()): ?>
                    <label class="ed-slug">
                        <span class="fz-mono fz-gris">Dirección de la obra</span>
                        <span class="ed-slug__campo"><span class="ed-slug__base"><?= e(preg_replace('#^https?://#', '', fz_url_absoluta('obra/'))) ?></span><input type="text" name="slug" value="<?= e($obra ? $obra->getSlug() : '') ?>" maxlength="80" pattern="[a-z0-9-]*" spellcheck="false" autocomplete="off" data-slug data-slug-auto="<?= $obra && $obra->getSlug() !== '' ? '0' : '1' ?>" aria-label="Dirección de la obra" placeholder="se-arma-sola-con-el-titulo"></span>
                        <small>Se arma sola con el título. Si la cambiás después de compartirla, el link viejo deja de funcionar.</small>
                    </label>
                <?php endif; ?>
            </div>
            <div class="fz-card ed-card">
                <span class="fz-card__foto">
                    <img src="<?= $portada ? e($rutaBase . $portada) : '' ?>" alt="" data-card-foto <?= $portada ? '' : 'hidden' ?>>
                    <span class="fz-card__num">N° <?= e($numero) ?></span>
                    <span class="fz-card__badge" data-mostrar="badge" <?= $extra['badge'] ? '' : 'hidden' ?>><?= e($extra['badge']) ?></span>
                </span>
                <span class="fz-card__meta"><span data-mostrar="ubicacion"><?= e($obra ? $obra->getUbicacion() : 'Ubicación') ?></span><span data-mostrar="rubro"><?= e($extra['rubro'] ?: 'Rubro') ?></span></span>
                <span class="fz-card__titulo"><span data-mostrar="titulo"><?= e($obra ? $obra->getTitulo() : 'Título de la obra') ?></span> <?= fz_flecha(22) ?></span>
                <input type="text" name="etiquetas" class="ed-campo fz-card__tags" maxlength="255" placeholder="Ej: Hidrolavado · ornamentos · pintura" value="<?= e($extra['etiquetas']) ?>" aria-label="Etiquetas del listado">
            </div>
        </div>
    </section>
</form>
