<?php
/**
 * index.php — Router principal público
 * Rutas: inicio, obrasciviles, obrasindustriales, obra-ver,
 *        iniciar-sesion (standalone), registrarse (standalone), 404
 */

use App\Autenticacion\Autenticacion;

// Incluimos nuestro archivo de booteo.
require_once __DIR__ . '/bootstrap/init.php';
require_once __DIR__ . '/includes/helpers.php';

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

// Obra pedida por su dirección linda (/obra/restauracion-de-fachada) o por número (viejo ?id=72).
$obraActual = null;
if ($seccion === 'obra-ver') {
    $modeloObras = new \App\Modelos\Obras();
    $slugPedido = trim((string) ($_GET['slug'] ?? ''), '/');
    $obraActual = $slugPedido !== '' ? $modeloObras->porSlug($slugPedido) : $modeloObras->porId((int) ($_GET['id'] ?? 0));
}

// Si el servidor tiene direcciones lindas, las viejas (index.php?seccion=...) redirigen a las nuevas.
if (fz_urls_lindas() && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET') {
    $destino = null;
    $pidioVieja = str_contains($_SERVER['REQUEST_URI'] ?? '', 'seccion=') || !empty($_GET['id']);
    if ($pidioVieja && in_array($seccion, ['inicio', 'obrasciviles', 'obrasindustriales'], true)) {
        $destino = fz_url($seccion);
    } elseif ($pidioVieja && $obraActual && $obraActual->getSlug() !== '') {
        $destino = fz_url_obra($obraActual);
    }
    if ($destino !== null) {
        header('Location: ' . fz_url_absoluta($destino === './' ? '' : $destino), true, 301);
        exit;
    }
}

$seoPorSeccion = [
    'inicio' => [
        'titulo' => 'FRANZA Construcciones | Obras civiles e industriales en CABA y GBA',
        'descripcion' => 'Constructora con más de 15 años de oficio: fachadas, azoteas, ampliaciones, reformas y obras industriales. Mirá cada obra con su antes y después.',
    ],
    'obrasciviles' => [
        'titulo' => 'Obras civiles | FRANZA Construcciones',
        'descripcion' => 'Fachadas, azoteas, ampliaciones y reformas completas en CABA y Gran Buenos Aires. Cada obra con su ficha y sus fotos.',
    ],
    'obrasindustriales' => [
        'titulo' => 'Obras industriales | FRANZA Construcciones',
        'descripcion' => 'Pisos, demarcación y obras en plantas, depósitos y naves industriales. Conocé los trabajos de FRANZA para la industria.',
    ],
    'obra-ver' => [
        'titulo' => 'Obra | FRANZA Construcciones',
        'descripcion' => 'Conocé en detalle una obra de FRANZA Construcciones: qué se hizo, dónde y todas sus fotos.',
    ],
    '404' => [
        'titulo' => 'Página no encontrada | FRANZA Construcciones',
        'descripcion' => 'La página que buscás no existe. Volvé al inicio para ver las obras de FRANZA.',
    ],
];

$seoActual = $seoPorSeccion[$seccion] ?? $seoPorSeccion['inicio'];
$ogImagen = fz_url_absoluta('img/franza/og-franza.jpg');
$ogTipo = 'website';
$ogTamano = [1200, 630];
$ogAlt = 'FRANZA Construcciones — obras civiles e industriales';
$canonicalUrl = fz_url_absoluta($seccion === 'inicio' ? '' : fz_url($seccion));
$jsonLdExtra = null;

// La ficha de obra arma su propio título, descripción e imagen para buscadores y redes.
if ($seccion === 'obra-ver') {
    $obraSeo = $obraActual;
    if ($obraSeo) {
        $seoActual['titulo'] = $obraSeo->getTitulo() . ' en ' . $obraSeo->getUbicacion() . ' | FRANZA Construcciones';
        $desc = trim(preg_replace('/\s+/', ' ', $obraSeo->getExplicacion()));
        $seoActual['descripcion'] = mb_strlen($desc) > 155 ? rtrim(mb_substr($desc, 0, 152)) . '…' : $desc;
        // Imagen para compartir: JPG 1200×630 con foto, logo y título (o la portada, si el servidor no puede armarla).
        $portadaSeo = fz_sin_portada($obraSeo) ? '' : fz_portada($obraSeo);
        $ogRel = \App\Imagenes\OgImagen::para($obraSeo, $portadaSeo);
        if ($ogRel) {
            $ogImagen = fz_url_absoluta($ogRel);
            $ogTamano = [1200, 630];
        } elseif ($portadaSeo !== '') {
            $ogImagen = fz_url_absoluta(fz_img($portadaSeo, 1600));
            $ogTamano = null;
        }
        $ogAlt = $obraSeo->getTitulo() . ' en ' . $obraSeo->getUbicacion() . ' — obra de FRANZA Construcciones';
        $ogTipo = 'article';
        $canonicalUrl = fz_url_absoluta(fz_url_obra($obraSeo));
        $seccionLista = $obraSeo->getTipo() === 'civil' ? 'obrasciviles' : 'obrasindustriales';
        $jsonLdExtra = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Inicio', 'item' => fz_url_absoluta('')],
                ['@type' => 'ListItem', 'position' => 2, 'name' => $obraSeo->getTipo() === 'civil' ? 'Obras civiles' : 'Obras industriales', 'item' => fz_url_absoluta(fz_url($seccionLista))],
                ['@type' => 'ListItem', 'position' => 3, 'name' => $obraSeo->getTitulo(), 'item' => $canonicalUrl],
            ],
        ];
    } else {
        $seccion = '404';
        $seoActual = $seoPorSeccion['404'];
    }
}

$contactoSeo = fz_data()['contacto'];
$jsonLd = [
    '@context' => 'https://schema.org',
    '@type' => 'GeneralContractor',
    'name' => 'FRANZA Construcciones',
    'url' => fz_url_absoluta(''),
    'logo' => fz_url_absoluta('img/franza/icon-512.png'),
    'image' => fz_url_absoluta('img/franza/og-franza.jpg'),
    'description' => $seoPorSeccion['inicio']['descripcion'],
    'email' => $contactoSeo['email'],
    'telephone' => '+' . $contactoSeo['whatsapp'],
    'areaServed' => ['Ciudad Autónoma de Buenos Aires', 'Gran Buenos Aires'],
    'address' => ['@type' => 'PostalAddress', 'addressRegion' => 'Buenos Aires', 'addressCountry' => 'AR'],
    'sameAs' => [$contactoSeo['instagram'], $contactoSeo['linkedin']],
];

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


if ($seccion === '404') { http_response_code(404); }
$esInicio = ($seccion === 'inicio');
$contacto = fz_data()['contacto'];
$version = '6';
?>
<!DOCTYPE html>
<html lang="es-AR">
<head>
    <meta charset="UTF-8">
    <base href="<?= e(fz_base()) ?>">
    <script>document.documentElement.classList.add('js');</script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="cf-2fa-verify" content="W1x36AnsZ6pVFj3vdSj1">
    <meta name="facebook-domain-verification" content="5jkjdlc5h7cov244vpyeljxal0sx6o">
    <title><?= e($seoActual['titulo']); ?></title>
    <meta name="description" content="<?= e($seoActual['descripcion']); ?>">
    <meta name="robots" content="<?= $seccion === '404' ? 'noindex, follow' : 'index, follow, max-image-preview:large' ?>">
    <link rel="canonical" href="<?= e($canonicalUrl); ?>">
    <meta name="theme-color" content="#141311">

    <link rel="icon" href="favicon.ico" sizes="32x32">
    <link rel="icon" href="favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="img/franza/apple-touch-icon.png">
    <link rel="manifest" href="site.webmanifest">

    <meta property="og:type" content="<?= $ogTipo ?>">
    <meta property="og:site_name" content="FRANZA Construcciones">
    <meta property="og:locale" content="es_AR">
    <meta property="og:title" content="<?= e($seoActual['titulo']); ?>">
    <meta property="og:description" content="<?= e($seoActual['descripcion']); ?>">
    <meta property="og:url" content="<?= e($canonicalUrl); ?>">
    <meta property="og:image" content="<?= e($ogImagen); ?>">
    <?php if (str_starts_with($ogImagen, 'https://')): ?><meta property="og:image:secure_url" content="<?= e($ogImagen); ?>"><?php endif; ?>
    <meta property="og:image:type" content="<?= str_ends_with($ogImagen, '.webp') ? 'image/webp' : 'image/jpeg' ?>">
    <?php if ($ogTamano): ?><meta property="og:image:width" content="<?= $ogTamano[0] ?>">
    <meta property="og:image:height" content="<?= $ogTamano[1] ?>"><?php endif; ?>
    <meta property="og:image:alt" content="<?= e($ogAlt); ?>">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= e($seoActual['titulo']); ?>">
    <meta name="twitter:description" content="<?= e($seoActual['descripcion']); ?>">
    <meta name="twitter:image" content="<?= e($ogImagen); ?>">
    <meta name="twitter:image:alt" content="<?= e($ogAlt); ?>">
    <script type="application/ld+json"><?= json_encode($jsonLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) ?></script>
    <?php if ($jsonLdExtra): ?><script type="application/ld+json"><?= json_encode($jsonLdExtra, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) ?></script><?php endif; ?>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,500..800&family=Geist:wght@400..700&family=Geist+Mono:wght@400..600&family=Montserrat:wght@300;800&display=swap">
    <?php if ($esInicio): ?>
    <link rel="preload" as="image" href="<?= e(fz_img(fz_data()['hero']['despues'])) ?>" type="image/webp">
    <?php endif; ?>
    <link rel="stylesheet" href="styles/css/franza.css?v=<?= $version ?>">
</head>
<body class="fz seccion-<?= e($seccion) ?><?= $esInicio ? ' es-inicio' : '' ?>">
<div class="fz-progreso" aria-hidden="true"></div>

<?php if ($esInicio): require __DIR__ . '/views/partials/intro.php'; endif; ?>

<header class="fz-header<?= $esInicio ? ' fz-header--sobre-hero' : '' ?>" id="fz-header">
    <div class="fz-header__inner">
        <a href="<?= e(fz_url('inicio')) ?>" class="fz-header__logo" aria-label="FRANZA Construcciones, ir al inicio">
            <img src="img/franza/logo-claro.png" alt="FRANZA Construcciones" width="200" height="79">
        </a>
        <button type="button" class="fz-menu-btn" id="fz-menu-btn" aria-expanded="false" aria-controls="fz-nav" aria-label="Abrir menú">
            <span></span><span></span>
        </button>
        <nav class="fz-nav" id="fz-nav" aria-label="Principal">
            <a href="<?= e(fz_url('obrasciviles')) ?>" <?= $seccion === 'obrasciviles' ? 'aria-current="page"' : '' ?>>Obras civiles</a>
            <a href="<?= e(fz_url('obrasindustriales')) ?>" <?= $seccion === 'obrasindustriales' ? 'aria-current="page"' : '' ?>>Obras industriales</a>
            <a href="<?= e(fz_url('inicio') . '#paso-a-paso') ?>">Cómo trabajamos</a>
            <a href="<?= e(fz_aqui()) ?>#contacto">Contacto</a>
            <?php if ($autenticacion->estaAutenticado()): $usuario = $autenticacion->getUsuario(); ?>
                <div class="fz-user">
                    <button type="button" class="fz-user__btn" aria-expanded="false">
                        <span class="fz-user__avatar"><?= e(strtoupper(substr($usuario->getUsuarioEmail(), 0, 1))) ?></span>
                        <span class="fz-user__mail"><?= e($usuario->getUsuarioEmail()) ?></span>
                    </button>
                    <div class="fz-user__menu">
                        <?php if ($usuario->getUsuarioRolId() === \App\Modelos\Usuario::ROL_ADMIN): ?>
                            <a href="admin/index.php">Panel de administración</a>
                        <?php endif; ?>
                        <form action="acciones/cerrar-sesion.php" method="post">
                            <?= \App\Seguridad\Csrf::campo() ?>
                            <button type="submit">Cerrar sesión</button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
            <a href="<?= e(fz_whatsapp('¡Hola! Quiero pedir un presupuesto.')) ?>" class="fz-btn fz-btn--oro fz-nav__cta" target="_blank" rel="noopener">Pedir presupuesto</a>
        </nav>
    </div>
</header>

<main class="fz-main" id="contenido">
    <?php if (isset($mensajeFeedback)): ?>
        <div class="msj msj-<?= e($estado) ?>" role="status"><?= $mensajeFeedback; ?></div>
    <?php endif; ?>
    <?php require __DIR__ . '/views/' . $seccion . '.php'; ?>
</main>

<section class="fz-cta" id="contacto">
    <div class="fz-wrap fz-cta__inner">
        <h2 class="fz-cta__titulo">¿Tenés una obra en mente?</h2>
        <div class="fz-cta__acciones">
            <a href="<?= e(fz_whatsapp('¡Hola! Tengo una obra en mente y quiero pedir un presupuesto.')) ?>" class="fz-btn fz-btn--negro" target="_blank" rel="noopener">Escribinos por WhatsApp <?= fz_flecha(20) ?></a>
            <span class="fz-mono"><?= e($contacto['email']) ?></span>
        </div>
    </div>
</section>

<footer class="fz-footer">
    <div class="fz-wrap fz-footer__grid">
        <img src="img/franza/logo-claro.png" alt="FRANZA Construcciones" class="fz-footer__logo" width="200" height="79" loading="lazy">
        <div class="fz-footer__col">
            <b>Obras</b>
            <a href="<?= e(fz_url('obrasciviles')) ?>">Obras civiles</a>
            <a href="<?= e(fz_url('obrasindustriales')) ?>">Obras industriales</a>
            <a href="<?= e(fz_url('inicio') . '#paso-a-paso') ?>">Cómo trabajamos</a>
        </div>
        <div class="fz-footer__col">
            <b>Contacto</b>
            <a href="<?= e(fz_whatsapp()) ?>" target="_blank" rel="noopener">WhatsApp <?= e(fz_whatsapp_visible()) ?></a>
            <a href="mailto:<?= e($contacto['email']) ?>"><?= e($contacto['email']) ?></a>
        </div>
        <div class="fz-footer__col">
            <b>Redes</b>
            <div class="fz-redes">
                <a href="<?= e($contacto['instagram']) ?>" target="_blank" rel="noopener" aria-label="Instagram de FRANZA">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4.2"/><circle cx="17.4" cy="6.6" r="1.1" fill="currentColor" stroke="none"/></svg>
                </a>
                <a href="<?= e($contacto['linkedin']) ?>" target="_blank" rel="noopener" aria-label="LinkedIn de FRANZA">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M4.98 3.5a2.5 2.5 0 1 1 0 5 2.5 2.5 0 0 1 0-5zM3 9.75h4v11H3zM9.5 9.75h3.8v1.5h.05c.53-1 1.84-2.05 3.78-2.05 4.04 0 4.79 2.66 4.79 6.12v5.43h-4v-4.82c0-1.15-.02-2.63-1.6-2.63-1.6 0-1.85 1.25-1.85 2.55v4.9h-4z"/></svg>
                </a>
            </div>
        </div>
        <div class="fz-footer__base">
            <span>© <?= date('Y'); ?> FRANZA Construcciones<?php if (!$autenticacion->estaAutenticado()): ?> · <a href="index.php?seccion=iniciar-sesion">Acceder</a><?php endif; ?></span>
            <span>Sitio desarrollado por <a href="https://alejomfernandez.com.ar" target="_blank" rel="noopener">Alejo Martín Fernández</a></span>
        </div>
    </div>
</footer>

<a class="fz-wa" href="<?= e(fz_whatsapp()) ?>" target="_blank" rel="noopener" aria-label="Escribinos por WhatsApp">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="28" height="28" fill="currentColor" aria-hidden="true"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
</a>

<script src="js/whatsapp.js"></script>
<script src="js/mensaje.js"></script>
<script src="js/franza.js?v=<?= $version ?>"></script>
</body>
</html>
