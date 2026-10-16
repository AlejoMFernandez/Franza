<?php
/** 404.php — Página no encontrada: explica qué pasó y ofrece seguir por las obras. */
use App\Modelos\Obras;

$sugeridas = array_slice((new Obras())->todas(), 0, 3);
?>
<section class="fz-404">
    <div class="fz-wrap fz-404__grid">
        <div class="fz-404__texto">
            <span class="fz-mono fz-oro">Error 404 · Página no encontrada</span>
            <h1 class="fz-h1">Esta página no está en los planos.</h1>
            <p>Puede que la dirección esté mal escrita o que la obra se haya dado de baja. Seguí por acá:</p>
            <div class="fz-404__acciones">
                <a class="fz-btn fz-btn--oro" href="<?= e(fz_url('obrasciviles')) ?>">Ver todas las obras</a>
                <a class="fz-btn fz-btn--linea" href="<?= e(fz_url('inicio')) ?>">Ir al inicio</a>
            </div>
        </div>
        <div class="fz-404__numero" aria-hidden="true">404</div>
    </div>
    <?php if ($sugeridas): ?>
    <div class="fz-wrap fz-404__sugeridas">
        <span class="fz-mono fz-gris">Algunas obras</span>
        <div class="fz-404__cards">
            <?php foreach ($sugeridas as $o): ?>
                <a class="fz-404__card" href="<?= e(fz_url_obra($o)) ?>">
                    <span class="fz-404__foto"><img src="<?= e(fz_img(fz_portada($o), 800)) ?>" alt="" loading="lazy"></span>
                    <span class="fz-mono fz-oro"><?= e(fz_num($o->getObraId())) ?> · <?= e($o->getUbicacion()) ?></span>
                    <span class="fz-404__titulo"><?= e($o->getTitulo()) ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</section>
