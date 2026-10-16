<?php
/**
 * inicio.php — Home: hero rayos X, civil/industrial, antes y después,
 * obras destacadas y paso a paso.
 */
use App\Modelos\Obras;

$data = fz_data();
$modelo = new Obras();
$cantCiviles = count($modelo->porTipo('civil'));
$cantIndustriales = count($modelo->porTipo('industrial'));

// Destacadas: las marcadas con la estrella en el panel; si no hay ninguna, las del archivo de contenidos.
$destacadas = $modelo->destacadas();
foreach ($destacadas ? [] : $data['destacadas'] as $id) {
    $o = $modelo->porId((int) $id);
    if ($o) { $destacadas[] = $o; }
}
if (!$destacadas) {
    $destacadas = $modelo->todas();
}
$destacadas = array_slice($destacadas, 0, 5);

$hero = $data['hero'];
$paso = $data['paso_a_paso'];
?>

<!-- 1 · Hero rayos X -->
<section class="fz-hero" data-rayosx>
    <img class="fz-hero__foto" src="<?= e(fz_img($hero['despues'])) ?>" alt="Frente terminado por FRANZA en <?= e($hero['rotulo']) ?>" fetchpriority="high">
    <div class="fz-hero__antes" aria-hidden="true">
        <img class="fz-hero__foto" src="<?= e(fz_img($hero['antes'])) ?>" alt="">
    </div>
    <div class="fz-hero__lente" aria-hidden="true"><span>Antes</span></div>
    <div class="fz-hero__velo" aria-hidden="true"></div>
    <div class="fz-wrap fz-hero__contenido">
        <span class="fz-eyebrow">Obras civiles e industriales · CABA y GBA</span>
        <h1 class="fz-hero__titulo">Mirá debajo <span>de la obra.</span></h1>
        <p class="fz-hero__texto">
            <span class="solo-puntero">Pasá el cursor por la foto: donde mires, aparece cómo estaba antes de que llegara FRANZA.</span>
            <span class="solo-tactil">Tocá la foto: donde toques, aparece cómo estaba antes de que llegara FRANZA.</span>
        </p>
        <div class="fz-hero__acciones">
            <button type="button" class="fz-btn fz-btn--oro" data-rayosx-toggle aria-pressed="false">Ver todo el antes</button>
            <a href="<?= e(fz_aqui()) ?>#obras" class="fz-btn fz-btn--linea">Ver las obras</a>
        </div>
    </div>
    <a class="fz-hero__rotulo" href="<?= e(fz_url_obra((int) $hero['obra_id'])) ?>">
        <span><?= e(fz_num((int) $hero['obra_id'])) ?></span><?= e($hero['rotulo']) ?>
    </a>
</section>

<!-- 2 · Civil o industrial -->
<section class="fz-selector" id="obras">
    <div class="fz-wrap">
        <div class="fz-seccion-head">
            <h2 class="fz-h2">¿Qué tipo de obra buscás?</h2>
            <span class="fz-mono fz-seccion-head__nota">Elegí una para ver los trabajos</span>
        </div>
        <div class="fz-selector__paneles">
            <a class="fz-selector__panel" href="<?= e(fz_url('obrasciviles')) ?>">
                <img src="<?= e(fz_img('img/obrasciviles/restauraci-on-de-fachada/1_1771019414_0.jpg')) ?>" alt="Fachada restaurada por FRANZA" loading="lazy">
                <span class="fz-selector__info">
                    <span class="fz-mono fz-oro">01 · Civil · <?= str_pad((string) $cantCiviles, 2, '0', STR_PAD_LEFT) ?> obras</span>
                    <span class="fz-selector__titulo">Obras civiles</span>
                    <span class="fz-selector__rubros"><?= e($data['rubros_tipo']['civil']) ?></span>
                </span>
                <span class="fz-selector__flecha"><?= fz_flecha(26) ?></span>
            </a>
            <a class="fz-selector__panel" href="<?= e(fz_url('obrasindustriales')) ?>">
                <img src="<?= e(fz_img('img/obrasindustriales.webp')) ?>" alt="Planta industrial con sendas demarcadas por FRANZA" loading="lazy">
                <span class="fz-selector__info">
                    <span class="fz-mono fz-oro">02 · Industrial<?= $cantIndustriales ? ' · ' . str_pad((string) $cantIndustriales, 2, '0', STR_PAD_LEFT) . ' obras' : '' ?></span>
                    <span class="fz-selector__titulo">Obras industriales</span>
                    <span class="fz-selector__rubros"><?= e($data['rubros_tipo']['industrial']) ?></span>
                </span>
                <span class="fz-selector__flecha"><?= fz_flecha(26) ?></span>
            </a>
        </div>
    </div>
</section>

<!-- 3 · Antes y después -->
<section class="fz-ad" id="antes-y-despues">
    <div class="fz-wrap fz-ad__grid">
        <div class="fz-ad__lado">
            <span class="fz-mono fz-gris">Antes / Después</span>
            <h2 class="fz-h2">El antes no se esconde.</h2>
            <p class="fz-ad__texto">Fotos tomadas desde el mismo lugar, el primer día y el último. Arrastrá la línea para comparar.</p>
            <div class="fz-ad__tabs" role="tablist" aria-label="Obras para comparar">
                <?php foreach ($data['antes_despues'] as $i => $par): ?>
                    <button type="button" role="tab" id="fz-ad-tab-<?= $i ?>" aria-controls="fz-ad-panel-<?= $i ?>" aria-selected="<?= $i === 0 ? 'true' : 'false' ?>" tabindex="<?= $i === 0 ? '0' : '-1' ?>" class="fz-ad__tab">
                        <span class="fz-mono"><?= $par['obra_id'] ? e(fz_num((int) $par['obra_id'])) : 'N° —' ?></span>
                        <span class="fz-ad__tab-nombre"><?= e($par['nombre']) ?></span>
                        <span class="fz-mono fz-ad__tab-tipo"><?= e($par['tipo']) ?></span>
                    </button>
                <?php endforeach; ?>
            </div>
            <?php foreach ($data['antes_despues'] as $i => $par): ?>
                <div class="fz-ad__ficha" data-ad-ficha="<?= $i ?>" <?= $i ? 'hidden' : '' ?>>
                    <span class="fz-mono"><span class="fz-gris">Ficha técnica</span><span class="fz-oro"><?= e($par['lugar']) ?></span></span>
                    <p><?= e($par['texto']) ?></p>
                    <?php if ($par['obra_id']): ?><a href="<?= e(fz_url_obra((int) $par['obra_id'])) ?>">Ver la obra completa <?= fz_flecha(16) ?></a><?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="fz-ad__visor">
            <?php foreach ($data['antes_despues'] as $i => $par): ?>
                <div class="fz-comparar" role="tabpanel" id="fz-ad-panel-<?= $i ?>" aria-labelledby="fz-ad-tab-<?= $i ?>" data-comparar <?= $i ? 'hidden' : '' ?>>
                    <img src="<?= e(fz_img($par['despues'])) ?>" alt="<?= e($par['nombre']) ?>: <?= e(mb_strtolower($par['etiquetas'][1])) ?>" loading="lazy">
                    <div class="fz-comparar__antes"><img src="<?= e(fz_img($par['antes'])) ?>" alt="<?= e($par['nombre']) ?>: <?= e(mb_strtolower($par['etiquetas'][0])) ?>" loading="lazy"></div>
                    <span class="fz-comparar__tag fz-comparar__tag--a"><?= e($par['etiquetas'][0]) ?></span>
                    <span class="fz-comparar__tag fz-comparar__tag--b"><?= e($par['etiquetas'][1]) ?></span>
                    <div class="fz-comparar__linea" role="slider" tabindex="0" aria-label="Comparar <?= e(mb_strtolower($par['etiquetas'][0])) ?> y <?= e(mb_strtolower($par['etiquetas'][1])) ?>" aria-valuemin="0" aria-valuemax="100" aria-valuenow="50">
                        <span class="fz-comparar__manija"><svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true"><path d="M9 6l-6 6 6 6M15 6l6 6-6 6"/></svg></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- 4 · Obras destacadas -->
<?php if ($destacadas): ?>
<section class="fz-destacadas">
    <div class="fz-wrap">
        <div class="fz-seccion-head">
            <h2 class="fz-h2">Obras destacadas</h2>
            <a class="fz-link-flecha" href="<?= e(fz_url('obrasciviles')) ?>">Ver todas las obras <?= fz_flecha(18) ?></a>
        </div>
        <div class="fz-destacadas__grid">
            <?php foreach ($destacadas as $i => $o): $extra = fz_extra($o); ?>
                <a class="fz-dest<?= $i === 0 ? ' fz-dest--grande' : '' ?>" href="<?= e(fz_url_obra($o->getObraId())) ?>">
                    <img src="<?= e(fz_img(fz_portada($o), $i === 0 ? 1600 : 800)) ?>" alt="<?= e($o->getTitulo()) ?>" loading="lazy">
                    <span class="fz-dest__info">
                        <span class="fz-mono fz-oro"><?= e(fz_num($o->getObraId())) ?> · <?= $o->getTipo() === 'civil' ? 'Civil' : 'Industrial' ?> · <?= e($o->getUbicacion()) ?></span>
                        <span class="fz-dest__titulo"><?= e($o->getTitulo()) ?></span>
                    </span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- 5 · Paso a paso -->
<section class="fz-paso" id="paso-a-paso" data-cinta>
    <?php foreach ($paso['etapas'] as $i => $et): ?>
        <img class="fz-paso__foto" src="<?= e(fz_img($et['foto'])) ?>" alt="Etapa <?= $i + 1 ?>: <?= e($et['titulo']) ?>" loading="lazy" data-cinta-foto style="opacity:<?= $i === 0 ? 1 : 0 ?>">
    <?php endforeach; ?>
    <div class="fz-paso__velo" aria-hidden="true"></div>
    <div class="fz-wrap fz-paso__contenido">
        <div class="fz-paso__top">
            <div>
                <span class="fz-mono fz-oro"><?= e(fz_num((int) $paso['obra_id'])) ?> · <?= e($paso['titulo']) ?></span>
                <h2 class="fz-h2">Así trabajamos, <span class="fz-oro-claro">paso a paso.</span></h2>
            </div>
            <span class="fz-paso__num" data-cinta-num>01</span>
        </div>
        <div class="fz-paso__abajo">
            <div class="fz-paso__texto" aria-live="polite">
                <strong data-cinta-titulo><?= e($paso['etapas'][0]['titulo']) ?></strong>
                <span data-cinta-texto><?= e($paso['etapas'][0]['texto']) ?></span>
            </div>
            <a class="fz-link-flecha fz-link-flecha--claro" href="<?= e(fz_url_obra((int) $paso['obra_id'])) ?>">Ver la obra <?= fz_flecha(18) ?></a>
        </div>
        <div class="fz-cinta" data-cinta-pista role="slider" tabindex="0" aria-label="Avance de la obra" aria-valuemin="1" aria-valuemax="<?= count($paso['etapas']) ?>" aria-valuenow="1">
            <div class="fz-cinta__cinta">
                <div class="fz-cinta__marcas" aria-hidden="true"></div>
                <?php $n = count($paso['etapas']); foreach ($paso['etapas'] as $i => $et): ?>
                    <span class="fz-cinta__etiqueta" style="left:<?= round($i / max(1, $n - 1) * 100, 3) ?>%" data-cinta-etiqueta><i></i><?= str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) ?> <?= e($et['titulo']) ?></span>
                <?php endforeach; ?>
            </div>
            <div class="fz-cinta__caja" data-cinta-caja aria-hidden="true"><i></i><b></b></div>
        </div>
        <script type="application/json" data-cinta-etapas><?= json_encode(array_map(fn($et) => ['titulo' => $et['titulo'], 'texto' => $et['texto']], $paso['etapas']), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?></script>
    </div>
</section>
