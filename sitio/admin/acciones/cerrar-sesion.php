<?php
/**
 * cerrar-sesion.php (admin) — Cierra sesión y redirige al inicio público
 */
use App\Autenticacion\Autenticacion;
use App\Seguridad\Csrf;

require_once __DIR__ . '/../../bootstrap/init.php';

// Validar token CSRF
Csrf::verificarOAbortar('../index.php?seccion=inicio');

$autenticacion = new Autenticacion;
$autenticacion->cerrarSesion();

$_SESSION['mensajeFeedback'] = "Sesión cerrada con éxito.";
$_SESSION['estadoFeedback'] = "exito";
header("Location: ../../index.php?seccion=inicio");
exit;