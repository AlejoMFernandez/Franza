<?php
/**
 * obra-ver.php — Ficha de una obra: hero con entrada animada, ficha técnica, qué se hizo y galería.
 */
use App\Modelos\Obras;

$obra = $obraActual ?? null;

if (!$obra): ?>
    <section class="fz-vacia">
        <div class="fz-wrap">
            <span class="fz-mono fz-oro">Obra no encontrada</span>
            <h1 class="fz-h1">Esta obra no existe o se dio de baja.</h1>
            <a class="fz-btn fz-btn--oro" href="<?= e(fz_url('obrasciviles')) ?>">Ver todas las obras</a>
        </div>
    </section>
<?php return; endif;

$extra = fz_extra($obra);
$esCivil = $obra->getTipo() === 'civil';
$seccionLista = $esCivil ? 'obrasciviles' : 'obrasindustriales';
$rutaBase = $obra->getRutaImagenes();
$imagenes = fz_medios($obra);
$cantVideos = count(array_filter($imagenes, [Obras::class, 'esVideo']));
$cantFotos = count($imagenes) - $cantVideos;
$portada = fz_portada($obra);
$sinFoto = $portada === FZ_SIN_PORTADA;

// Para separar el título en dos líneas con la ubicación resaltada.
$titulo = $obra->getTitulo();
?>

<section class="fz-ficha-hero fz-entrada <?= fz_titulo_tamano($titulo, $obra->getUbicacion()) ?><?= $sinFoto ? ' fz-ficha-hero--sin-foto' : '' ?>">
    <?php if ($sinFoto): ?>
        <div class="fz-ficha-hero__plano" aria-hidden="true"><img src="img/franza/isotipo.svg" alt=""></div>
    <?php else: ?>
        <img src="<?= e(fz_img($portada)) ?>" alt="<?= e($titulo) ?>" class="fz-ficha-hero__foto<?= $extra['render'] ? ' es-render' : '' ?>">
    <?php endif; ?>
    <div class="fz-ficha-hero__velo" aria-hidden="true"></div>
    <div class="fz-wrap fz-ficha-hero__contenido">
        <nav class="fz-ruta" aria-label="Ruta">
            <a href="<?= e(fz_url($seccionLista)) ?>">Obras</a><span>/</span>
            <a href="<?= e(fz_url($seccionLista)) ?>"><?= $esCivil ? 'Civiles' : 'Industriales' ?></a><span>/</span>
            <span aria-current="page"><?= e(fz_num($obra->getObraId())) ?></span>
        </nav>
        <div class="fz-ficha-hero__abajo">
            <h1 class="fz-ficha-hero__titulo" aria-label="<?= e($titulo . ' en ' . $obra->getUbicacion()) ?>"><?= fz_palabras($titulo) ?> <span><?= fz_palabras('en ' . $obra->getUbicacion(), count(preg_split('/\s+/', trim($titulo)))) ?></span></h1>
            <span class="fz-ficha-hero__num" aria-hidden="true"><?= str_pad((string) $obra->getObraId(), 3, '0', STR_PAD_LEFT) ?></span>
        </div>
        <?php if ($extra['badge']): ?><span class="fz-card__badge fz-ficha-hero__badge"><?= e($extra['badge']) ?></span><?php endif; ?>
    </div>
</section>

<section class="fz-ficha-datos fz-entrada-datos">
    <div class="fz-wrap fz-ficha-datos__grid">
        <div><span class="fz-mono">Ubicación</span><b><?= e($obra->getUbicacion()) ?></b></div>
        <div><span class="fz-mono">Tipo</span><b><?= $esCivil ? 'Civil' : 'Industrial' ?><?= $extra['rubro'] !== 'Otras obras' ? ' · ' . e($extra['rubro']) : '' ?></b></div>
        <?php if (!empty($extra['duracion'])): ?><div><span class="fz-mono">Duración</span><b><?= e($extra['duracion']) ?></b></div><?php endif; ?>
        <?php if (!empty($extra['anio'])): ?><div><span class="fz-mono">Año</span><b><?= e($extra['anio']) ?></b></div><?php endif; ?>
        <div class="fz-ficha-datos__cta"><a class="fz-btn fz-btn--oro" href="<?= e(fz_whatsapp('¡Hola! Vi la obra "' . $titulo . '" en la web y quiero consultar por algo parecido.')) ?>" target="_blank" rel="noopener">Quiero algo así</a></div>
    </div>
</section>

<section class="fz-ficha-texto">
    <div class="fz-wrap fz-ficha-texto__grid">
        <?php if (!empty($extra['pasos'])): ?>
            <div class="fz-ficha-texto__col">
                <span class="fz-mono fz-gris">01 · El problema</span>
                <p class="fz-ficha-texto__grande<?= mb_strlen((string) ($extra['problema'] ?? '')) > FZ_TEXTO_CORTO ? ' es-largo' : '' ?>"><?= nl2br(e($extra['problema'] ?? '')) ?></p>
            </div>
            <div class="fz-ficha-texto__col fz-ficha-texto__col--der">
                <span class="fz-mono fz-gris">02 · Lo que hicimos</span>
                <ol class="fz-pasos">
                    <?php foreach ($extra['pasos'] as $i => $p): ?>
                        <li><span><?= $i + 1 ?></span><?= e($p) ?></li>
                    <?php endforeach; ?>
                </ol>
            </div>
        <?php else: $txt = fz_texto_obra($obra->getExplicacion()); ?>
            <?php if (!$txt['largo']): ?>
                <div class="fz-ficha-texto__col">
                    <span class="fz-mono fz-gris">Sobre la obra</span>
                </div>
                <div class="fz-ficha-texto__col fz-ficha-texto__col--der">
                    <p class="fz-ficha-texto__grande"><?= nl2br(e($txt['destacado'])) ?></p>
                </div>
            <?php else: ?>
                <div class="fz-ficha-texto__col">
                    <span class="fz-mono fz-gris">Sobre la obra</span>
                    <?php if ($txt['destacado']): ?><p class="fz-ficha-texto__grande fz-ficha-texto__lead"><?= e($txt['destacado']) ?></p><?php endif; ?>
                </div>
                <div class="fz-ficha-texto__col fz-ficha-texto__col--der fz-ficha-texto__col--lectura">
                    <div class="fz-lectura<?= $txt['caracteres'] > 700 && $txt['caracteres'] <= 1600 ? ' fz-lectura--columnas' : '' ?>" data-lectura>
                        <?php foreach ($txt['parrafos'] as $p): ?><p><?= e($p) ?></p><?php endforeach; ?>
                    </div>
                    <button type="button" class="fz-lectura__mas" data-lectura-mas hidden aria-expanded="false">Seguir leyendo <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="M6 9l6 6 6-6"/></svg></button>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>

<?php if ($imagenes): ?>
<section class="fz-galeria" data-galeria>
    <div class="fz-wrap">
        <div class="fz-seccion-head">
            <h2 class="fz-h2"><?= $cantVideos ? 'Fotos y videos' : 'Todas las fotos' ?></h2>
            <span class="fz-mono fz-gris"><?= $cantFotos ? $cantFotos . ($cantFotos === 1 ? ' foto' : ' fotos') : '' ?><?= $cantFotos && $cantVideos ? ' · ' : '' ?><?= $cantVideos ? $cantVideos . ($cantVideos === 1 ? ' video' : ' videos') : '' ?> · Tocá para ampliar</span>
        </div>
        <div class="fz-galeria__grid">
            <?php foreach ($imagenes as $i => $img): $ruta = $rutaBase . $img; $esVideo = Obras::esVideo($img); ?>
                <?php if ($esVideo):
                    $poster = fz_poster($ruta);
                    [$vw, $vh] = $poster ? (@getimagesize(__DIR__ . '/../' . $poster) ?: [0, 0]) : [0, 0];
                    $vertical = $vw && $vh > $vw * 1.05;
                ?>
                    <button type="button" class="fz-galeria__item fz-galeria__item--video<?= $vertical ? ' es-vertical' : '' ?>" data-galeria-i="<?= $i ?>" data-tipo="video" data-full="<?= e($ruta) ?>" data-poster="<?= e($poster) ?>" data-ancho="<?= (int) $vw ?>" data-alto="<?= (int) $vh ?>" aria-label="Ver video <?= $i + 1 ?> de <?= count($imagenes) ?>">
                        <?php if ($poster): ?>
                            <?php if ($vertical): ?><img class="fz-galeria__fondo" src="<?= e($poster) ?>" alt="" aria-hidden="true" loading="lazy"><?php endif; ?>
                            <img class="fz-galeria__frente" src="<?= e($poster) ?>" alt="<?= e($titulo) ?>, video" loading="lazy">
                        <?php else: ?>
                            <video src="<?= e($ruta) ?>#t=1" muted playsinline preload="metadata" aria-hidden="true"></video>
                        <?php endif; ?>
                        <span class="fz-galeria__play" aria-hidden="true"><svg width="26" height="26" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg></span>
                    </button>
                <?php else: ?>
                    <button type="button" class="fz-galeria__item" data-galeria-i="<?= $i ?>" data-full="<?= e(fz_img($ruta, 1600)) ?>" aria-label="Ampliar foto <?= $i + 1 ?> de <?= count($imagenes) ?>">
                        <img src="<?= e(fz_img($ruta, 800)) ?>" alt="<?= e($titulo) ?>, foto <?= $i + 1 ?>" loading="lazy">
                    </button>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="fz-lightbox" data-lightbox hidden role="dialog" aria-modal="true" aria-label="Foto ampliada">
        <button type="button" class="fz-lightbox__cerrar" data-lb-cerrar aria-label="Cerrar"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg></button>
        <button type="button" class="fz-lightbox__nav" data-lb-prev aria-label="Anterior"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="M19 12H5M11 6l-6 6 6 6"/></svg></button>
        <span class="fz-cargando fz-lightbox__cargando" data-lb-cargando hidden><span></span>Cargando…</span>
        <figure><img src="" alt="" data-lb-img><video data-lb-video controls playsinline preload="none" hidden></video><figcaption class="fz-mono" data-lb-cap></figcaption></figure>
        <button type="button" class="fz-lightbox__nav" data-lb-next aria-label="Siguiente"><?= fz_flecha(22) ?></button>
    </div>
</section>
<?php endif; ?>
