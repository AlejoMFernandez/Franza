<?php
/**
 * index.php — Router principal público
 * Rutas: inicio, obrasciviles, obrasindustriales, obra-ver,
 *        iniciar-sesion (standalone), registrarse (standalone), 404
 */

use App\Autenticacion\Autenticacion;

// Incluimos nuestro archivo de booteo.
require_once __DIR__ . '/bootstrap/init.php';

$listaRutas = [
    'inicio'              => [
        'titulo' => 'Inicio',
    ],
    'paneladmin'              => [
        'titulo' => 'Panel Admin',
    ],
    'obrasciviles'          => [
        'titulo' => 'Obras Civiles',
    ],
    'obrasindustriales'     => [
        'titulo' => 'Obras Industriales',
    ],
    'obra-ver' => [
        'titulo' => 'Detalle de Obra',
    ],
    'iniciar-sesion'    => [
        'titulo' => 'Iniciar Sesión',
    ],
    'registrarse'       => [
        'titulo' => 'Registrarse',
    ],
    '404'               => [
        'titulo' => 'Página no encontrada',
    ],
];

$seccion = $_GET['seccion'] ?? 'inicio';

if(!isset($listaRutas[$seccion])) {
    // No existe la ruta, así que mostramos una página de error.
    $seccion = '404';
}

$rutaOpciones = $listaRutas[$seccion];

$seoPorSeccion = [
    'inicio' => [
        'titulo' => 'FRANZA | Obras civiles e industriales',
        'descripcion' => 'FRANZA es una empresa constructora especializada en obras civiles e industriales, con más de 15 años de experiencia en proyectos de calidad.',
    ],
    'obrasciviles' => [
        'titulo' => 'Obras Civiles | FRANZA',
        'descripcion' => 'Descubrí las obras civiles de FRANZA: proyectos ejecutados con calidad, compromiso y excelencia técnica.',
    ],
    'obrasindustriales' => [
        'titulo' => 'Obras Industriales | FRANZA',
        'descripcion' => 'Conocé las obras industriales de FRANZA y nuestra experiencia en soluciones constructivas para la industria.',
    ],
    'obra-ver' => [
        'titulo' => 'Detalle de Obra | FRANZA',
        'descripcion' => 'Explorá el detalle de una obra de FRANZA, incluyendo información del proyecto e imágenes destacadas.',
    ],
    '404' => [
        'titulo' => 'Página no encontrada | FRANZA',
        'descripcion' => 'La página solicitada no está disponible. Volvé al inicio para seguir explorando FRANZA.',
    ],
];

$seoActual = $seoPorSeccion[$seccion] ?? $seoPorSeccion['inicio'];

$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (($_SERVER['SERVER_PORT'] ?? null) == 443)
    || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

$scheme = $isHttps ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'franza.com.ar';
$baseUrl = $scheme . '://' . $host;

$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
$scriptDir = ($scriptDir === '/' || $scriptDir === '.') ? '' : rtrim($scriptDir, '/');

$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$canonicalUrl = $baseUrl . $requestPath;
$ogImageUrl = $baseUrl . $scriptDir . '/img/mainphoto.png';

$autenticacion = new Autenticacion();

// Flasheamos el mensaje de feedback
if(isset($_SESSION['mensajeFeedback'])) {
    $mensajeFeedback = $_SESSION['mensajeFeedback'];
    $estado = $_SESSION['estadoFeedback'] ?? 'exito'; // por defecto exito
    unset($_SESSION['mensajeFeedback'], $_SESSION['estadoFeedback']);
}

// Si es login o registro, cargar página standalone
if($seccion === 'iniciar-sesion' || $seccion === 'registrarse') {
    require __DIR__ . '/views/' . $seccion . '.php';
    exit;
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta name="cf-2fa-verify" value="W1x36AnsZ6pVFj3vdSj1" />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="favicon.ico" sizes="any">
    <link rel="icon" href="favicon.png">
    <title><?= htmlspecialchars($seoActual['titulo'], ENT_QUOTES, 'UTF-8'); ?></title>
    <meta name="description" content="<?= htmlspecialchars($seoActual['descripcion'], ENT_QUOTES, 'UTF-8'); ?>">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="<?= htmlspecialchars($canonicalUrl, ENT_QUOTES, 'UTF-8'); ?>">

    <meta property="og:type" content="website">
    <meta property="og:site_name" content="FRANZA">
    <meta property="og:locale" content="es_AR">
    <meta property="og:title" content="<?= htmlspecialchars($seoActual['titulo'], ENT_QUOTES, 'UTF-8'); ?>">
    <meta property="og:description" content="<?= htmlspecialchars($seoActual['descripcion'], ENT_QUOTES, 'UTF-8'); ?>">
    <meta property="og:url" content="<?= htmlspecialchars($canonicalUrl, ENT_QUOTES, 'UTF-8'); ?>">
    <meta property="og:image" content="<?= htmlspecialchars($ogImageUrl, ENT_QUOTES, 'UTF-8'); ?>">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= htmlspecialchars($seoActual['titulo'], ENT_QUOTES, 'UTF-8'); ?>">
    <meta name="twitter:description" content="<?= htmlspecialchars($seoActual['descripcion'], ENT_QUOTES, 'UTF-8'); ?>">
    <meta name="twitter:image" content="<?= htmlspecialchars($ogImageUrl, ENT_QUOTES, 'UTF-8'); ?>">

    <?php if ($seccion === 'inicio'): ?>
    <link rel="preload" as="image" href="img/mainphoto.webp" type="image/webp">
    <?php endif; ?>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="styles/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
 </head>
<body>
    <div class="layout">
        <header>
            <div class="container">
                <a href="index.php?seccion=inicio" class="logo"><img src="favicon.png" alt="FRANZA Logo"><h1>FRANZA</h1></a>
                <nav class="main-nav">
                    <input type="checkbox" id="menu-toggle">
                    <label for="menu-toggle" class="menu-icon">
                        <span></span>
                        <span></span>
                        <span></span>
                    </label>
                    <ul class="menu">
                        <li><a href="index.php?seccion=inicio" <?=$seccion==='inicio'?'class="activo"':''?>>Inicio</a></li>
                        <li><a href="index.php?seccion=obrasciviles" <?=$seccion==='obrasciviles'?'class="activo"':''?>>Obras Civiles</a></li>
                        <li><a href="index.php?seccion=obrasindustriales" <?=$seccion==='obrasindustriales'?'class="activo"':''?>>Obras Industriales</a></li>
                        <li><button class="presupuesto-btn" onclick="openWhatsApp()">CONTACTANOS</button></li>
                        <?php if(!$autenticacion->estaAutenticado()): ?>
                            <li><a href="index.php?seccion=iniciar-sesion" <?=$seccion==='iniciar-sesion' || $seccion==='registrarse'?'class="activo"':''?>>Acceder</a></li>
                        <?php else: ?>
                            <li class="user-menu">
                                <button type="button" class="user-btn">
                                    <span class="user-avatar"><?= strtoupper(substr($autenticacion->getUsuario()->getUsuarioEmail(), 0, 1)) ?></span>
                                    <?= htmlspecialchars($autenticacion->getUsuario()->getUsuarioEmail()) ?> ▾
                                </button>
                                <div class="user-dropdown">
                                    <div class="dropdown-links">
                                        <?php if($autenticacion->getUsuario()->getUsuarioRolId() === \App\Modelos\Usuario::ROL_ADMIN): ?>
                                        <a href="admin/index.php" class="dropdown-link">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
                                            Panel Admin
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                    <div class="dropdown-footer">
                                        <form action="acciones/cerrar-sesion.php" method="post">
                                            <?= \App\Seguridad\Csrf::campo() ?>
                                            <button type="submit" class="dropdown-link btn-logout">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                                                Cerrar sesión
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        </header>
        <main class="main-content">
            <?php
                // Mensajes de feedback
                if(isset($mensajeFeedback)):
                ?>
                <div class="msj msj-<?= htmlspecialchars($estado, ENT_QUOTES) ?>"><?= $mensajeFeedback;?></div>
                <?php
                endif;
            ?>
            <?php
            require __DIR__ . '/views/' . $seccion . '.php';
            ?>
        </main>
        <footer>
            <div class="footer-main">
                <div class="footer-brand">
                    <h3>FRANZA</h3>
                    <p>Empresa constructora con más de 15 años de experiencia en obras civiles e industriales. Calidad, compromiso y profesionalismo en cada proyecto.</p>
                    <div class="footer-social">
                        <a class="icon instagram" target="_blank" href="https://www.instagram.com/franzaconstrucciones?igsh=Zzl5emNueml3bXI5&utm_source=qr">
                            <img src="img/instagram.png" alt="Instagram">
                        </a>
                        <a class="icon linkedin" target="_blank" href="https://www.linkedin.com/company/franza-construcciones/">
                            <img src="img/linkedin.png" alt="LinkedIn">
                        </a>
                    </div>
                </div>
                <div class="footer-col">
                    <h4>Navegación</h4>
                    <ul>
                        <li><a href="index.php?seccion=inicio">Inicio</a></li>
                        <li><a href="index.php?seccion=obrasciviles">Obras Civiles</a></li>
                        <li><a href="index.php?seccion=obrasindustriales">Obras Industriales</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h4>Contacto</h4>
                    <div class="footer-contact-item">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                        Buenos Aires, Argentina
                    </div>
                    <div class="footer-contact-item">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                        p.allegranza@franza.com.ar
                    </div>
                    <div class="footer-contact-item">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>
                        <a href="https://www.instagram.com/franzaconstrucciones" target="_blank" rel="noopener noreferrer" style="color: var(--text-muted); text-decoration: none;">@franzaconstrucciones</a>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <div class="footer-bottom-inner">
                    <span>© <?= date('Y'); ?> <strong style="color: var(--primary-lighter);">FRANZA</strong>. Todos los derechos reservados.</span>
                    <span>Desarrollado por <a href="https://alejomfernandez.com.ar" target="_blank" rel="noopener noreferrer" style="color: var(--primary-lighter); text-decoration: underline;">Alejo Martin Fernandez</a></span>
                </div>
            </div>
        </footer>  
    </div>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <!-- Botón flotante WhatsApp -->
    <a class="whatsapp-fab" href="https://wa.me/5491136932502?text=%C2%A1Hola%21%20Me%20gustar%C3%ADa%20hacer%20una%20consulta." target="_blank" rel="noopener noreferrer" aria-label="Contactar por WhatsApp">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="28" height="28" fill="white">
            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
        </svg>
    </a>
    <script src="js/whatsapp.js"></script>
    <script src="js/carrousel.js"></script>
    <script src="js/userButton.js"></script>
    <script src="js/mensaje.js"></script>
</body>
</html>