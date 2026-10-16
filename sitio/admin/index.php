<?php
/**
 * admin/index.php — Router del panel de administración (rediseño 2026)
 * Rutas: inicio (login), obras (listado), obras-agregar / obras-editar (editor visual),
 *        registrarse, 404. Las rutas con requiereAutenticacion piden un admin logueado.
 */
use App\Autenticacion\Autenticacion;
use App\BaseDeDatos\Migracion;

require_once __DIR__ . '/../bootstrap/init.php';
require_once __DIR__ . '/../includes/helpers.php';

$listaRutas = [
    'inicio'        => ['titulo' => 'Ingreso al panel'],
    'obras'         => ['titulo' => 'Obras', 'requiereAutenticacion' => true],
    'obras-agregar' => ['titulo' => 'Nueva obra', 'requiereAutenticacion' => true, 'vista' => 'obra-editor'],
    'obras-editar'  => ['titulo' => 'Editar obra', 'requiereAutenticacion' => true, 'vista' => 'obra-editor'],
    'registrarse'   => ['titulo' => 'Registrarse'],
    '404'           => ['titulo' => 'Página no encontrada'],
];

$seccion = $_GET['seccion'] ?? 'inicio';
if (!isset($listaRutas[$seccion])) {
    $seccion = '404';
}
$rutaOpciones = $listaRutas[$seccion];

$autenticacion = new Autenticacion;
$esAdmin = $autenticacion->estaAutenticado() && $autenticacion->getUsuario()->esAdmin();

if (($rutaOpciones['requiereAutenticacion'] ?? false) && !$esAdmin) {
    $_SESSION['mensajeFeedback'] = 'Ingresá con un usuario administrador para ver el panel.';
    $_SESSION['estadoFeedback'] = 'error';
    header('Location: index.php?seccion=inicio');
    exit;
}
if ($seccion === 'inicio' && $esAdmin) {
    header('Location: index.php?seccion=obras');
    exit;
}

if (isset($_SESSION['mensajeFeedback'])) {
    $mensajeFeedback = $_SESSION['mensajeFeedback'];
    $estado = $_SESSION['estadoFeedback'] ?? 'exito';
    unset($_SESSION['mensajeFeedback'], $_SESSION['estadoFeedback']);
}

// Login y registro: páginas propias, sin el layout del panel.
if ($seccion === 'inicio' || $seccion === 'registrarse') {
    require __DIR__ . '/views/' . $seccion . '.php';
    exit;
}

$vista = $rutaOpciones['vista'] ?? $seccion;
$esEditor = ($vista === 'obra-editor');
$usuario = $autenticacion->getUsuario();
$version = '7';
?>
<!DOCTYPE html>
<html lang="es-AR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#141311">
    <link rel="icon" href="../favicon.ico" sizes="32x32">
    <link rel="icon" href="../favicon.svg" type="image/svg+xml">
    <title><?= e($rutaOpciones['titulo']) ?> · Panel FRANZA</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,500..800&family=Geist:wght@400..700&family=Geist+Mono:wght@400..600&display=swap">
    <link rel="stylesheet" href="../styles/css/franza.css?v=<?= $version ?>">
    <link rel="stylesheet" href="../styles/css/admin.css?v=<?= $version ?>">
</head>
<body class="fz fz-admin<?= $esEditor ? ' fz-admin--editor' : '' ?>">

<?php if (!$esEditor): ?>
<header class="adm-top">
    <div class="adm-top__inner">
        <a href="index.php?seccion=obras" class="adm-top__logo"><img src="../img/franza/logo-claro.png" alt="FRANZA" width="150" height="59"><span>Panel</span></a>
        <nav class="adm-top__nav" aria-label="Panel">
            <a href="index.php?seccion=obras" <?= $seccion === 'obras' ? 'aria-current="page"' : '' ?>>Obras</a>
            <a href="../index.php?seccion=inicio" target="_blank" rel="noopener">Ver el sitio</a>
        </nav>
        <div class="adm-top__user">
            <span class="adm-top__mail"><?= e($usuario->getUsuarioEmail()) ?></span>
            <form action="acciones/cerrar-sesion.php" method="post" data-cargando="Saliendo…">
                <?= \App\Seguridad\Csrf::campo() ?>
                <button type="submit" class="adm-btn adm-btn--linea adm-btn--chico">Cerrar sesión</button>
            </form>
        </div>
    </div>
</header>
<?php endif; ?>

<?php if (!Migracion::completa()): ?>
    <div class="adm-aviso">
        <div class="adm-aviso__inner">
            <p><b>Falta un paso:</b> actualizá la base de datos para usar el panel nuevo (rubros, pasos, destacadas, orden y arreglo de tildes). Se hace una sola vez y no borra nada.</p>
            <form action="acciones/migrar.php" method="post" data-cargando="Actualizando…">
                <?= \App\Seguridad\Csrf::campo() ?>
                <button type="submit" class="adm-btn adm-btn--oro">Actualizar ahora</button>
            </form>
        </div>
    </div>
<?php endif; ?>

<?php if (isset($mensajeFeedback)): ?>
    <div class="msj msj-<?= e($estado) ?>" role="status"><?= $mensajeFeedback ?></div>
<?php endif; ?>

<?php require __DIR__ . '/views/' . $vista . '.php'; ?>

<script src="../js/mensaje.js"></script>
<script src="../js/admin.js?v=<?= $version ?>"></script>
</body>
</html>
