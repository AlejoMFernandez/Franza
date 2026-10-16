<?php
/**
 * admin/views/obras.php — Listado de obras del panel: ordenar arrastrando,
 * destacar en la home, editar, ver y eliminar.
 */
use App\Modelos\Obras;
use App\Seguridad\Csrf;
use App\BaseDeDatos\Migracion;

$obras = (new Obras)->todas();
$total = count($obras);
$civiles = count(array_filter($obras, fn($o) => $o->getTipo() === 'civil'));
$industriales = $total - $civiles;
$token = Csrf::obtener();
$puedeOrdenar = Migracion::actualizada();
?>
<main class="adm-wrap adm-obras" data-obras data-csrf="<?= e($token) ?>">
    <div class="adm-head">
        <div>
            <h1 class="adm-h1">Obras</h1>
            <p class="adm-sub"><?= $total ?> obras · <?= $civiles ?> civiles · <?= $industriales ?> industriales</p>
        </div>
        <a href="index.php?seccion=obras-agregar" class="adm-btn adm-btn--oro">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
            Nueva obra
        </a>
    </div>

    <?php if (!$obras): ?>
        <div class="adm-vacio">
            <span class="adm-vacio__icono" aria-hidden="true"><img src="../img/franza/isotipo.svg" alt="" width="64" height="88"></span>
            <h2>Todavía no hay obras</h2>
            <p>Cargá la primera: la armás viendo cómo va a quedar en el sitio.</p>
            <ol class="adm-vacio__pasos">
                <li><b>1</b> Subí las fotos y elegí la portada</li>
                <li><b>2</b> Escribí el título, el lugar y qué se hizo</li>
                <li><b>3</b> Tocá "Publicar obra"</li>
            </ol>
            <a href="index.php?seccion=obras-agregar" class="adm-btn adm-btn--oro">Cargar la primera obra</a>
        </div>
    <?php else: ?>
        <div class="adm-barra">
            <div class="adm-chips" role="group" aria-label="Filtrar por tipo">
                <button type="button" class="adm-chip" aria-pressed="true" data-filtro="todas">Todas <span><?= $total ?></span></button>
                <button type="button" class="adm-chip" aria-pressed="false" data-filtro="civil">Civiles <span><?= $civiles ?></span></button>
                <button type="button" class="adm-chip" aria-pressed="false" data-filtro="industrial">Industriales <span><?= $industriales ?></span></button>
            </div>
            <label class="adm-buscar">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
                <input type="search" placeholder="Buscar por título o lugar" aria-label="Buscar obra" data-buscar>
            </label>
        </div>
        <?php if ($puedeOrdenar): ?>
            <p class="adm-ayuda" data-ayuda-orden>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><circle cx="9" cy="6" r="1.6"/><circle cx="15" cy="6" r="1.6"/><circle cx="9" cy="12" r="1.6"/><circle cx="15" cy="12" r="1.6"/><circle cx="9" cy="18" r="1.6"/><circle cx="15" cy="18" r="1.6"/></svg>
                Arrastrá las tarjetas para cambiar el orden en el que aparecen en el sitio. La estrella marca las obras destacadas de la home.
            </p>
        <?php endif; ?>

        <ol class="adm-grilla" data-grilla>
            <?php foreach ($obras as $o):
                $extra = fz_extra($o);
                $fotos = count(fz_galeria($o));
                $sinPortada = fz_sin_portada($o);
                $portada = '../' . fz_img(fz_portada($o), 800);
            ?>
                <li class="adm-card" data-id="<?= $o->getObraId() ?>" data-tipo="<?= e($o->getTipo()) ?>" data-texto="<?= e(mb_strtolower($o->getTitulo() . ' ' . $o->getUbicacion())) ?>" <?= $puedeOrdenar ? 'draggable="true"' : '' ?>>
                    <div class="adm-card__foto">
                        <img src="<?= e($portada) ?>" alt="" loading="lazy" draggable="false">
                        <?php if ($sinPortada): ?>
                            <span class="adm-card__sinfoto">Sin fotos todavía</span>
                        <?php endif; ?>
                        <span class="adm-card__num"><?= e(fz_num($o->getObraId())) ?></span>
                        <?php if ($puedeOrdenar): ?>
                            <button type="button" class="adm-estrella" data-destacar aria-pressed="<?= $o->esDestacada() ? 'true' : 'false' ?>" aria-label="Destacar en la home" title="Destacar en la home">
                                <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3l2.7 5.6 6.1.9-4.4 4.3 1 6.1L12 17l-5.4 2.9 1-6.1L3.2 9.5l6.1-.9z"/></svg>
                            </button>
                            <span class="adm-card__asa" aria-hidden="true"><svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><circle cx="9" cy="6" r="1.6"/><circle cx="15" cy="6" r="1.6"/><circle cx="9" cy="12" r="1.6"/><circle cx="15" cy="12" r="1.6"/><circle cx="9" cy="18" r="1.6"/><circle cx="15" cy="18" r="1.6"/></svg></span>
                        <?php endif; ?>
                    </div>
                    <div class="adm-card__cuerpo">
                        <span class="adm-card__meta"><?= $o->getTipo() === 'civil' ? 'Civil' : 'Industrial' ?> · <?= e($extra['rubro']) ?> · <?= $fotos ?> fotos</span>
                        <strong class="adm-card__titulo"><?= e($o->getTitulo()) ?></strong>
                        <span class="adm-card__lugar"><?= e($o->getUbicacion()) ?></span>
                    </div>
                    <div class="adm-card__acciones">
                        <a class="adm-btn adm-btn--negro adm-btn--chico" href="index.php?seccion=obras-editar&id=<?= $o->getObraId() ?>">Editar</a>
                        <a class="adm-btn adm-btn--linea-osc adm-btn--chico" href="../<?= e(fz_url_obra($o->getObraId())) ?>" target="_blank" rel="noopener">Ver en el sitio</a>
                        <button type="button" class="adm-icono" data-eliminar data-titulo="<?= e($o->getTitulo()) ?>" data-url="acciones/obras-eliminar.php?id=<?= $o->getObraId() ?>&amp;_csrf_token=<?= e($token) ?>" aria-label="Eliminar <?= e($o->getTitulo()) ?>">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 7h16M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3"/></svg>
                        </button>
                    </div>
                </li>
            <?php endforeach; ?>
        </ol>
        <div class="adm-vacio adm-vacio--filtro" data-sin-resultados hidden>
            <span class="adm-vacio__icono" aria-hidden="true"><svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3M8.5 8.5l5 5M13.5 8.5l-5 5"/></svg></span>
            <h2 data-sin-titulo>Ninguna obra coincide</h2>
            <p data-sin-texto>Probá con otra palabra o mirá todas las obras.</p>
            <div class="adm-vacio__acciones">
                <button type="button" class="adm-btn adm-btn--linea-osc" data-limpiar-filtros>Ver todas las obras</button>
                <a href="index.php?seccion=obras-agregar" class="adm-btn adm-btn--oro" data-cargar-tipo>Cargar una obra nueva</a>
            </div>
        </div>
    <?php endif; ?>
</main>

<div class="adm-modal" data-modal-eliminar hidden role="dialog" aria-modal="true" aria-labelledby="adm-modal-titulo">
    <div class="adm-modal__caja">
        <h2 id="adm-modal-titulo">¿Eliminar esta obra?</h2>
        <p>Se borra <b data-modal-nombre></b> con todas sus fotos. No se puede deshacer.</p>
        <div class="adm-modal__acciones">
            <button type="button" class="adm-btn adm-btn--linea-osc" data-modal-cancelar>Cancelar</button>
            <a href="#" class="adm-btn adm-btn--rojo" data-modal-confirmar>Sí, eliminar</a>
        </div>
    </div>
</div>
<div class="adm-toast" data-toast role="status" hidden></div>
