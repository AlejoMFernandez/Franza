<?php
/**
 * iniciar-sesion.php — Acción: procesar login
 * Detecta si viene del admin o del público para redirigir correctamente.
 * Valida email/password con bcrypt, inicia sesión y redirige según rol.
 */

use App\Autenticacion\Autenticacion;
use App\Seguridad\Csrf;

require_once __DIR__ . '/../../bootstrap/init.php';

// Validar token CSRF
$referer = $_SERVER['HTTP_REFERER'] ?? '';
$esAdmin = strpos($referer, '/admin/') !== false;
$urlLogin = $esAdmin ? '../index.php?seccion=inicio' : '../../index.php?seccion=iniciar-sesion';
Csrf::verificarOAbortar($urlLogin);

$email      = trim($_POST['email'] ?? '');
$password   = $_POST['password'] ?? '';

// Validaciones básicas
if(empty($email) || empty($password)) {
    $_SESSION['mensajeFeedback'] = "Email y contraseña son obligatorios.";
    $_SESSION['estadoFeedback'] = "error";
    $_SESSION['login_email'] = $email;
    header('Location: ' . $urlLogin);
    exit;
}

if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['mensajeFeedback'] = "El email no es válido.";
    $_SESSION['estadoFeedback'] = "error";
    $_SESSION['login_email'] = $email;
    header('Location: ' . $urlLogin);
    exit;
}

$auth = new Autenticacion;

if(!$auth->intentar($email, $password)) {
    $_SESSION['mensajeFeedback'] = "Email o contraseña incorrectos.";
    $_SESSION['estadoFeedback'] = "error";
    $_SESSION['login_email'] = $email;
    header('Location: ' . $urlLogin);
    exit;
}

$_SESSION['mensajeFeedback'] = "Sesión iniciada con éxito.";
$_SESSION['estadoFeedback'] = "crear";

unset($_SESSION['login_email']);

// Redirigir según el rol
if($auth->getUsuario()->esAdmin()) {
    header('Location: ../index.php?seccion=obras');
} else {
    header('Location: ../../index.php?seccion=inicio');
}
exit;