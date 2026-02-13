<?php
use App\Modelos\Obras;

$obra_id = $_GET['id'] ?? null;
$obra = (new Obras)->porId((int)$obra_id);
?>

<section class="obra-detail">
    <?php if ($obra): ?>
        <?php
        $imagenes = $obra->getGaleriaImagenes();
        $rutaBase = $obra->getRutaImagenes();
        $portada = $rutaBase . $obra->getImagen();
        ?>

        <!-- Hero Banner -->
        <div class="obra-hero" style="background-image: url('<?= htmlspecialchars($portada) ?>');">
            <div class="obra-hero-overlay"></div>
            <div class="obra-hero-content">
                <span class="obra-badge"><?= htmlspecialchars($obra->getUbicacion()) ?></span>
                <h1 class="obra-hero-title"><?= htmlspecialchars($obra->getTitulo()) ?></h1>
            </div>
        </div>

        <!-- Project Info -->
        <div class="obra-info-section">
            <div class="container">
                <div class="obra-info-grid">
                    <div class="obra-info-main">
                        <h2 class="obra-info-heading">Sobre el proyecto</h2>
                        <p class="obra-info-desc"><?= htmlspecialchars($obra->getExplicacion()) ?></p>
                    </div>
                    <div class="obra-info-sidebar">
                        <a href="javascript:void(0)" onclick="openWhatsApp()" class="obra-cta-btn">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                            Solicitar presupuesto
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Image Gallery -->
        <?php if (count($imagenes) > 0): ?>
        <div class="obra-gallery-section">
            <div class="container">
                <h2 class="obra-gallery-title">Galería</h2>
                <div class="obra-gallery-grid">
                    <?php foreach ($imagenes as $i => $img): ?>
                    <div class="obra-gallery-item" onclick="openLightbox(<?= $i ?>)">
                        <img src="<?= htmlspecialchars($rutaBase . $img) ?>" alt="<?= htmlspecialchars($obra->getTitulo()) ?> - Imagen <?= $i + 1 ?>" loading="lazy">
                        <div class="obra-gallery-hover">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="11" y1="8" x2="11" y2="14"/><line x1="8" y1="11" x2="14" y2="11"/></svg>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Lightbox -->
        <div class="obra-lightbox" id="lightbox">
            <button class="lightbox-close" onclick="closeLightbox()">&times;</button>
            <button class="lightbox-nav lightbox-prev" onclick="lightboxPrev()">&#10094;</button>
            <button class="lightbox-nav lightbox-next" onclick="lightboxNext()">&#10095;</button>
            <div class="lightbox-content">
                <img id="lightbox-img" src="" alt="">
                <div class="lightbox-counter" id="lightbox-counter"></div>
            </div>
        </div>

        <script>
        (function() {
            const images = <?= json_encode(array_map(function($img) use ($rutaBase) { return $rutaBase . $img; }, $imagenes)) ?>;
            let currentIndex = 0;

            window.openLightbox = function(index) {
                currentIndex = index;
                updateLightbox();
                document.getElementById('lightbox').classList.add('active');
                document.body.style.overflow = 'hidden';
            };

            window.closeLightbox = function() {
                document.getElementById('lightbox').classList.remove('active');
                document.body.style.overflow = '';
            };

            window.lightboxPrev = function() {
                currentIndex = (currentIndex - 1 + images.length) % images.length;
                updateLightbox();
            };

            window.lightboxNext = function() {
                currentIndex = (currentIndex + 1) % images.length;
                updateLightbox();
            };

            function updateLightbox() {
                document.getElementById('lightbox-img').src = images[currentIndex];
                document.getElementById('lightbox-counter').textContent = (currentIndex + 1) + ' / ' + images.length;
            }

            document.getElementById('lightbox').addEventListener('click', function(e) {
                if (e.target === this) closeLightbox();
            });

            document.addEventListener('keydown', function(e) {
                if (!document.getElementById('lightbox').classList.contains('active')) return;
                if (e.key === 'Escape') closeLightbox();
                if (e.key === 'ArrowLeft') lightboxPrev();
                if (e.key === 'ArrowRight') lightboxNext();
            });
        })();
        </script>
        <?php endif; ?>

    <?php else: ?>
        <h1 class="h1-view">Obra no encontrada</h1>
        <p style="text-align:center; color: var(--text-muted);">No existe una obra con ese identificador.</p>
    <?php endif; ?>
</section>