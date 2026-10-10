<?php
/**
 * obra-ver.php — Ficha de una obra: hero con entrada animada, ficha técnica, qué se hizo y galería.
 */
use App\Modelos\Obras;

$modelo = new Obras();
$obra = $modelo->porId((int) ($_GET['id'] ?? 0));

if (!$obra): ?>
    <section class="fz-vacia">
        <div class="fz-wrap">
            <span class="fz-mono fz-oro">Obra no encontrada</span>
            <h1 class="fz-h1">Esta obra no existe o se dio de baja.</h1>
            <a class="fz-btn fz-btn--oro" href="index.php?seccion=obrasciviles">Ver todas las obras</a>
        </div>
    </section>
<?php return; endif;

$extra = fz_extra($obra);
$esCivil = $obra->getTipo() === 'civil';
$seccionLista = $esCivil ? 'obrasciviles' : 'obrasindustriales';
$rutaBase = $obra->getRutaImagenes();
$imagenes = fz_galeria($obra);
$portada = fz_portada($obra);

// Para separar el título en dos líneas con la ubicación resaltada.
$titulo = $obra->getTitulo();
?>

<section class="fz-ficha-hero fz-entrada">
    <img src="<?= e(fz_img($portada)) ?>" alt="<?= e($titulo) ?>" class="fz-ficha-hero__foto<?= $extra['render'] ? ' es-render' : '' ?>">
    <div class="fz-ficha-hero__velo" aria-hidden="true"></div>
    <div class="fz-wrap fz-ficha-hero__contenido">
        <nav class="fz-ruta" aria-label="Ruta">
            <a href="index.php?seccion=<?= $seccionLista ?>">Obras</a><span>/</span>
            <a href="index.php?seccion=<?= $seccionLista ?>"><?= $esCivil ? 'Civiles' : 'Industriales' ?></a><span>/</span>
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
                <p class="fz-ficha-texto__grande"><?= e($extra['problema'] ?? '') ?></p>
            </div>
            <div class="fz-ficha-texto__col fz-ficha-texto__col--der">
                <span class="fz-mono fz-gris">02 · Lo que hicimos</span>
                <ol class="fz-pasos">
                    <?php foreach ($extra['pasos'] as $i => $p): ?>
                        <li><span><?= $i + 1 ?></span><?= e($p) ?></li>
                    <?php endforeach; ?>
                </ol>
            </div>
        <?php else: ?>
            <div class="fz-ficha-texto__col">
                <span class="fz-mono fz-gris">Sobre la obra</span>
            </div>
            <div class="fz-ficha-texto__col fz-ficha-texto__col--der">
                <p class="fz-ficha-texto__grande"><?= nl2br(e($obra->getExplicacion())) ?></p>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php if ($imagenes): ?>
<section class="fz-galeria" data-galeria>
    <div class="fz-wrap">
        <div class="fz-seccion-head">
            <h2 class="fz-h2">Todas las fotos</h2>
            <span class="fz-mono fz-gris"><?= count($imagenes) ?> fotos · Tocá para ampliar</span>
        </div>
        <div class="fz-galeria__grid">
            <?php foreach ($imagenes as $i => $img): $ruta = $rutaBase . $img; ?>
                <button type="button" class="fz-galeria__item" data-galeria-i="<?= $i ?>" data-full="<?= e(fz_img($ruta, 1600)) ?>" aria-label="Ampliar foto <?= $i + 1 ?> de <?= count($imagenes) ?>">
                    <img src="<?= e(fz_img($ruta, 800)) ?>" alt="<?= e($titulo) ?>, foto <?= $i + 1 ?>" loading="lazy">
                </button>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="fz-lightbox" data-lightbox hidden role="dialog" aria-modal="true" aria-label="Foto ampliada">
        <button type="button" class="fz-lightbox__cerrar" data-lb-cerrar aria-label="Cerrar"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg></button>
        <button type="button" class="fz-lightbox__nav" data-lb-prev aria-label="Foto anterior"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="M19 12H5M11 6l-6 6 6 6"/></svg></button>
        <figure><img src="" alt="" data-lb-img><figcaption class="fz-mono" data-lb-cap></figcaption></figure>
        <button type="button" class="fz-lightbox__nav" data-lb-next aria-label="Foto siguiente"><?= fz_flecha(22) ?></button>
    </div>
</section>
<?php endif; ?>
