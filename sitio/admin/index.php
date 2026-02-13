<?php
/**
 * admin/index.php — Router del panel de administración
 * Rutas: inicio (login), obras, obras-agregar, obras-editar,
 *        obras-eliminar, registrarse, 404
 * Las rutas con requiereAutenticacion redirigen al login si no hay sesión.
 */
use App\Autenticacion\Autenticacion;

require_once __DIR__ . '/../bootstrap/init.php';

$listaRutas = [
    'inicio'              => [
        'titulo' => 'Ingreo al panel de administración',
    ],
    'obras'          => [
        'titulo' => 'Panel de obras',
        'requiereAutenticacion' => true,
    ],
    'obras-agregar'     => [
        'titulo' => 'Agregar obra',
        'requiereAutenticacion' => true,
    ],
    'obras-editar'    => [
        'titulo' => 'Editar obra',
        'requiereAutenticacion' => true,
    ],
    'obras-eliminar'       => [
        'titulo' => 'Eliminar obra',
        'requiereAutenticacion' => true,
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
    $seccion = '404';
}

$rutaOpciones = $listaRutas[$seccion];

// Creamos un objeto de autenticación.
$autenticacion = new Autenticacion;

// De requerirlo, preguntamos si el usuario está autenticado.
$requiereAutenticacion = $rutaOpciones['requiereAutenticacion'] ?? false;
if(
    $requiereAutenticacion && 
    (
        !$autenticacion->estaAutenticado() || 
        !$autenticacion->getUsuario()->esAdmin()
    )
) {
    // Lo pateamos al login.
    $_SESSION['mensajeFeedback'] = "Es necesario ser administrador.";
    $_SESSION['estadoFeedback'] = 'error';
    header("Location: ../index.php");
    exit;
} else {
    // si esta logeado lo mandamos a ver las obras.
    if($seccion === 'inicio' && $autenticacion->estaAutenticado()) {
        $_SESSION['mensajeFeedback'] = "¡Login realizado con éxito!";
        $_SESSION['estadoFeedback'] = 'crear';
        header("Location: index.php?seccion=obras");
        exit;
    }
}

if(isset($_SESSION['mensajeFeedback'])) {
    $mensajeFeedback = $_SESSION['mensajeFeedback'];
    $estado = $_SESSION['estadoFeedback'] ?? 'exito'; // por defecto exito
    unset($_SESSION['mensajeFeedback']);
}

// Si es login o registro del admin, cargar página standalone
if($seccion === 'inicio' || $seccion === 'registrarse') {
    require __DIR__ . '/views/' . $seccion . '.php';
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../favicon.ico" sizes="any">
    <link rel="icon" href="../favicon.png">
    <title>FRANZA: <?= $rutaOpciones['titulo'];?></title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="../styles/css/style.css">
</head>
<body>
    <div class="layout">
        <header>
            <div class="container">
                <a href="../index.php?seccion=inicio" class="logo"><img src="../favicon.png" alt="FRANZA Logo"><h1>FRANZA</h1></a>
                <nav class="main-nav">
                    <input type="checkbox" id="menu-toggle">
                    <label for="menu-toggle" class="menu-icon">
                        <span></span>
                        <span></span>
                        <span></span>
                    </label>
                    <ul class="menu">
                        <li><a href="../index.php?seccion=inicio">Inicio</a></li>
                        <li><a href="../index.php?seccion=obrasciviles">Obras Civiles</a></li>
                        <li><a href="../index.php?seccion=obrasindustriales">Obras Industriales</a></li>
                        <li><button class="presupuesto-btn" onclick="openWhatsApp()">Solicitar presupuesto</button></li>
                        <?php if(!$autenticacion->estaAutenticado()): ?>
                        <li><a href="../index.php?seccion=iniciar-sesion">Acceder</a></li>
                        <?php else: ?>
                        <li class="user-menu">
                            <button type="button" class="user-btn">
                                <span class="user-avatar"><?= strtoupper(substr($autenticacion->getUsuario()->getUsuarioEmail(), 0, 1)) ?></span>
                                <?= htmlspecialchars($autenticacion->getUsuario()->getUsuarioEmail()) ?> ▾
                            </button>
                            <div class="user-dropdown">
                                <div class="dropdown-links">
                                    <a href="index.php?seccion=obras" class="dropdown-link active">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
                                        Panel Admin
                                    </a>
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
                            <img src="../img/instagram.png" alt="Instagram">
                        </a>
                        <a class="icon linkedin" target="_blank" href="https://www.linkedin.com/company/franza-construcciones/">
                            <img src="../img/linkedin.png" alt="LinkedIn">
                        </a>
                    </div>
                </div>
                <div class="footer-col">
                    <h4>Navegación</h4>
                    <ul>
                        <li><a href="../index.php?seccion=inicio">Inicio</a></li>
                        <li><a href="../index.php?seccion=obrasciviles">Obras Civiles</a></li>
                        <li><a href="../index.php?seccion=obrasindustriales">Obras Industriales</a></li>
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
                        info@franzaconstrucciones.com
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <div class="footer-bottom-inner">
                    <span>© <?= date('Y'); ?> <strong style="color: var(--primary-lighter);">FRANZA</strong>. Todos los derechos reservados.</span>
                </div>
            </div>
        </footer>  
    </div>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="../js/whatsapp.js"></script>
    <script src="../js/userButton.js"></script>
    <script src="../js/mensaje.js"></script>
    <script src="../js/previewImage.js"></script>
    <script src="../js/popup.js"></script>
</body>
</html>