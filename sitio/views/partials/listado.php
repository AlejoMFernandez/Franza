<?php
/**
 * listado.php — Listado de obras por tipo, con pestañas civil/industrial y filtro por rubro.
 * Espera $tipoListado = 'civil' | 'industrial'.
 */
use App\Modelos\Obras;

$modelo = new Obras();
$obras = $modelo->porTipo($tipoListado);
$cantCiviles = count($tipoListado === 'civil' ? $obras : $modelo->porTipo('civil'));
$cantIndustriales = count($tipoListado === 'industrial' ? $obras : $modelo->porTipo('industrial'));
$esCivil = ($tipoListado === 'civil');

$rubros = [];
foreach ($obras as $o) {
    $r = fz_extra($o)['rubro'];
    $rubros[$r] = true;
}
$rubros = array_keys($rubros);
$pad = fn(int $n) => str_pad((string) $n, 2, '0', STR_PAD_LEFT);
?>
<section class="fz-listado-head">
    <div class="fz-wrap">
        <div class="fz-listado-head__top">
            <div>
                <nav class="fz-ruta" aria-label="Ruta"><a href="<?= e(fz_url('inicio')) ?>">Inicio</a><span>/</span><span aria-current="page">Obras</span></nav>
                <h1 class="fz-h1"><?= $esCivil ? 'Obras civiles' : 'Obras industriales' ?></h1>
            </div>
            <p class="fz-listado-head__texto">
                <?= $esCivil
                    ? 'Casas, fachadas, azoteas y ampliaciones en CABA y Gran Buenos Aires. Cada obra con su ficha, sus fotos y, cuando las tenemos, las de antes.'
                    : 'Pisos, demarcación y obras en plantas, depósitos y naves industriales.' ?>
            </p>
        </div>
        <nav class="fz-tipos" aria-label="Tipo de obra">
            <a href="<?= e(fz_url('obrasciviles')) ?>" <?= $esCivil ? 'aria-current="page"' : '' ?>>Obras civiles <span class="fz-mono"><?= $pad($cantCiviles) ?></span></a>
            <a href="<?= e(fz_url('obrasindustriales')) ?>" <?= !$esCivil ? 'aria-current="page"' : '' ?>>Obras industriales <span class="fz-mono"><?= $pad($cantIndustriales) ?></span></a>
        </nav>
    </div>
</section>

<section class="fz-listado" data-listado>
    <div class="fz-wrap">
        <?php if (count($rubros) > 1): ?>
        <div class="fz-filtros">
            <div class="fz-filtros__chips" role="group" aria-label="Filtrar por rubro">
                <button type="button" class="fz-chip" aria-pressed="true" data-rubro="todas">Todos los rubros</button>
                <?php foreach ($rubros as $r): ?>
                    <button type="button" class="fz-chip" aria-pressed="false" data-rubro="<?= e($r) ?>"><?= e($r) ?></button>
                <?php endforeach; ?>
            </div>
            <span class="fz-mono fz-gris" data-listado-cuenta aria-live="polite"><?= count($obras) ?> <?= count($obras) === 1 ? 'obra' : 'obras' ?></span>
        </div>
        <?php endif; ?>

        <div class="fz-grilla">
            <?php foreach ($obras as $o): $extra = fz_extra($o); ?>
                <a class="fz-card" href="<?= e(fz_url_obra($o->getObraId())) ?>" data-rubro="<?= e($extra['rubro']) ?>">
                    <span class="fz-card__foto">
                        <img src="<?= e(fz_img(fz_portada($o), 800)) ?>" alt="<?= e($o->getTitulo()) ?>" loading="lazy"<?= $extra['render'] ? ' class="es-render"' : '' ?>>
                        <span class="fz-card__num"><?= e(fz_num($o->getObraId())) ?></span>
                        <?php if ($extra['badge']): ?><span class="fz-card__badge"><?= e($extra['badge']) ?></span><?php endif; ?>
                    </span>
                    <span class="fz-card__meta"><span><?= e($o->getUbicacion()) ?></span><span><?= e($extra['rubro']) ?></span></span>
                    <span class="fz-card__titulo"><?= e($o->getTitulo()) ?> <?= fz_flecha(22) ?></span>
                    <?php if ($extra['etiquetas']): ?><span class="fz-card__tags"><?= e($extra['etiquetas']) ?></span><?php endif; ?>
                </a>
            <?php endforeach; ?>

            <?php if (!$obras): ?>
                <?= fz_vacio(
                    $esCivil ? 'Obras civiles' : 'Plantas · depósitos · naves',
                    $esCivil ? 'Estamos subiendo nuestras obras civiles.' : 'Estamos subiendo nuestras obras industriales.',
                    'Mientras tanto, mirá el resto de los trabajos o contanos qué necesitás: te respondemos por WhatsApp.',
                    [
                        ['texto' => 'Pedir presupuesto', 'href' => fz_whatsapp($esCivil ? '¡Hola! Quiero consultar por una obra.' : '¡Hola! Quiero consultar por una obra industrial.'), 'estilo' => 'oro', 'externo' => true],
                        ['texto' => $esCivil ? 'Ver obras industriales' : 'Ver obras civiles', 'href' => fz_url($esCivil ? 'obrasindustriales' : 'obrasciviles'), 'estilo' => 'linea-osc'],
                    ],
                    'fz-vacio-bloque--grilla'
                ) ?>
            <?php elseif (!$esCivil && count($obras) < 3): ?>
                <div class="fz-invita<?= count($obras) === 0 ? ' fz-invita--sola' : '' ?>">
                    <span class="fz-mono fz-oro-claro">Plantas · depósitos · naves</span>
                    <span class="fz-invita__titulo"><?= count($obras) === 0 ? 'Estamos cargando nuestras obras industriales.' : '¿Tu planta necesita una obra?' ?> Contanos qué hay que hacer.</span>
                    <a href="<?= e(fz_whatsapp('¡Hola! Quiero consultar por una obra industrial.')) ?>" class="fz-btn fz-btn--oro" target="_blank" rel="noopener">Pedir presupuesto</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
