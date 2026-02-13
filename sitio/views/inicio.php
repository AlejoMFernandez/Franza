<?php
use App\Modelos\Obras;

// Destacados usando métodos de la clase Obras
$obrasRandom = Obras::GetRandomObra(6);

?>
<section class="main-section" style="margin-top: 0 !important;">
    <main>
        <section class="mainseccion">
            <div>
                <h1 class="h1-main">FRANZA</h1>
                <h2>CONSTRUCCIONES</h2>
            </div>
        </section>
        <section class="quenecesitas">
            <div class="quenecesitastop">
                <h4>¿Qué necesitas?</h4>
                <p>Hace el cambio que tanto necesita tu hogar</p>
            </div>
            <div class="quenecesitasbot">
                <div class="obrasciviles">
                    <a href="index.php?seccion=obrasciviles"><p>OBRAS CIVILES</p></a>
                </div>
                <div class="obrasindustriales">
                    <a href="index.php?seccion=obrasindustriales"><p>OBRAS INDUSTRIALES</p></a>
                </div>
            </div>
        </section>
        <section class="corte">
            <div class="containercorte">
                <h3>¡Líderes en calidad y compromiso!</h3>
                <p>Con más de 15 años de experiencia, en Franza desarrollamos soluciones constructivas a medida, combinando innovación, eficiencia y profesionalismo en cada proyecto.</p>
            </div>
        </section>
        <section class="obrasdestacadas">
            <div class="section-header-dest">
                <span class="section-badge">Obras Destacadas</span>
                <h2>Lo mejor de nuestro portfolio</h2>
            </div>
            <div class="obras-scroll">
                <?php if(empty($obrasRandom)): ?>
                    <div class="obras-empty">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>
                        <h3>No hay obras destacadas todavía</h3>
                        <p>Pronto vas a poder ver nuestros mejores proyectos acá.</p>
                    </div>
                <?php endif; ?>
                <?php foreach($obrasRandom as $obra):
                    $tipoCarpeta = ($obra['tipo'] === 'civil') ? 'obrasciviles' : 'obrasindustriales';
                    $imgFile = $obra['imagen'] ?? '';
                    $imgRelPath = "img/{$tipoCarpeta}/{$obra['carpeta']}/{$imgFile}";
                    $imgAbsPath = __DIR__ . "/../img/{$tipoCarpeta}/{$obra['carpeta']}/{$imgFile}";
                    $tieneImagen = !empty($imgFile) && file_exists($imgAbsPath);
                ?>
                    <a href="index.php?seccion=obra-ver&id=<?= (int)$obra['obra_id'] ?>" class="obra-card-listing obra-card-dest">
                        <div class="obra-card-listing-img">
                            <?php if($tieneImagen): ?>
                                <img src="<?= htmlspecialchars($imgRelPath) ?>" alt="<?= htmlspecialchars($obra['titulo']) ?>">
                            <?php else: ?>
                                <div class="obra-card-no-img">Sin imagen</div>
                            <?php endif; ?>
                        </div>
                        <div class="obra-card-listing-overlay"></div>
                        <div class="obra-card-listing-info">
                            <h3><?= htmlspecialchars($obra['titulo']) ?></h3>
                            <p class="obra-card-listing-ubi">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                                <?= htmlspecialchars($obra['ubicacion']) ?>
                            </p>
                        </div>
                        <div class="obra-card-listing-arrow">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="7" y1="17" x2="17" y2="7"/><polyline points="7 7 17 7 17 17"/></svg>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>
    </main>
</section>