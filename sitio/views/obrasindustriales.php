<?php
use App\Modelos\Obras;

$obrasIndustriales = (new Obras)->porTipo('industrial');
?>

<section class="main-seccion">
    <div class="section-header">
        <h1 class="h1-view">Obras Industriales</h1>
        <p>Soluciones constructivas de gran escala con calidad garantizada</p>
    </div>
    <div id="contenedorObrasIndustriales" class="container">
        <?php if(empty($obrasIndustriales)): ?>
            <div class="obras-empty">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>
                <h3>No hay obras industriales todavía</h3>
                <p>Estamos trabajando en nuevos proyectos. ¡Pronto podrás verlos acá!</p>
            </div>
        <?php endif; ?>
        <?php foreach($obrasIndustriales as $obra): 
            $carpeta = htmlspecialchars($obra->getCarpeta());
            $imagen = htmlspecialchars($obra->getImagen());
            $titulo = htmlspecialchars($obra->getTitulo());
            $ubicacion = htmlspecialchars($obra->getUbicacion());
            $imgRelPath = "img/obrasindustriales/{$carpeta}/{$imagen}";
            $imgAbsPath = __DIR__ . "/../img/obrasindustriales/{$carpeta}/{$imagen}";
            $tieneImagen = !empty($imagen) && file_exists($imgAbsPath);
        ?>
            <a class="obra-card-listing" href="index.php?seccion=obra-ver&id=<?= (int)$obra->getObraId() ?>">
                <div class="obra-card-listing-img">
                    <?php if($tieneImagen): ?>
                        <img src="<?= $imgRelPath ?>" alt="<?= $titulo ?>" loading="lazy" width="800" height="800">
                    <?php else: ?>
                        <div class="obra-card-no-img">Sin imagen</div>
                    <?php endif; ?>
                </div>
                <div class="obra-card-listing-overlay"></div>
                <div class="obra-card-listing-info">
                    <h3><?= $titulo ?></h3>
                    <p class="obra-card-listing-ubi">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                        <?= $ubicacion ?>
                    </p>
                </div>
                <div class="obra-card-listing-arrow">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="7" y1="17" x2="17" y2="7"/><polyline points="7 7 17 7 17 17"/></svg>
                </div>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- CTA Section -->
    <section class="obras-cta">
        <div class="container">
            <div class="obras-cta-content">
                <h3>¿Tenés un proyecto industrial?</h3>
                <p>Nuestro equipo de profesionales está listo para asesorarte. Soluciones a medida con los mejores estándares de calidad.</p>
                <a href="javascript:void(0)" onclick="openWhatsApp()" class="obras-cta-btn">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                    Solicitar presupuesto
                </a>
            </div>
        </div>
    </section>
</section>


