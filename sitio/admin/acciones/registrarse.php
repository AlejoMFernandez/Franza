<?php
/**
 * registrarse.php — Acción: registrar nuevo usuario
 * Valida datos, hashea contraseña con bcrypt y crea usuario con rol Usuario.
 * Detecta contexto (admin/público) para redirigir correctamente.
 */
use App\Modelos\Usuario;
use App\Seguridad\Csrf;

require_once __DIR__ . '/../../bootstrap/init.php';

// Validar token CSRF
$referer = $_SERVER['HTTP_REFERER'] ?? '';
$urlRetorno = (strpos($referer, '/admin/') !== false) ? '../index.php?seccion=registrarse' : '../../index.php?seccion=registrarse';
Csrf::verificarOAbortar($urlRetorno);

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$password_confirm = $_POST['password_confirm'] ?? '';

if(empty($email) || empty($password) || empty($password_confirm)) {
    $_SESSION['mensajeFeedback'] = 'Todos los campos son obligatorios.';
    $_SESSION['estadoFeedback'] = 'error';
    $_SESSION['email_anterior'] = $email;
    
    // Detectar de dónde viene
    $referer = $_SERVER['HTTP_REFERER'] ?? '';
    if(strpos($referer, '/admin/') !== false) {
        header('Location: ../index.php?seccion=registrarse');
    } else {
        header('Location: ../../index.php?seccion=registrarse');
    }
    exit;
}

if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['mensajeFeedback'] = 'El email no es válido.';
    $_SESSION['estadoFeedback'] = 'error';
    $_SESSION['email_anterior'] = $email;
    
    // Detectar de dónde viene
    $referer = $_SERVER['HTTP_REFERER'] ?? '';
    if(strpos($referer, '/admin/') !== false) {
        header('Location: ../index.php?seccion=registrarse');
    } else {
        header('Location: ../../index.php?seccion=registrarse');
    }
    exit;
}

if(strlen($password) < 6) {
    $_SESSION['mensajeFeedback'] = 'La contraseña debe tener al menos 6 caracteres.';
    $_SESSION['estadoFeedback'] = 'error';
    $_SESSION['email_anterior'] = $email;
    
    // Detectar de dónde viene
    $referer = $_SERVER['HTTP_REFERER'] ?? '';
    if(strpos($referer, '/admin/') !== false) {
        header('Location: ../index.php?seccion=registrarse');
    } else {
        header('Location: ../../index.php?seccion=registrarse');
    }
    exit;
}

if($password !== $password_confirm) {
    $_SESSION['mensajeFeedback'] = 'Las contraseñas no coinciden.';
    $_SESSION['estadoFeedback'] = 'error';
    $_SESSION['email_anterior'] = $email;
    
    // Detectar de dónde viene
    $referer = $_SERVER['HTTP_REFERER'] ?? '';
    if(strpos($referer, '/admin/') !== false) {
        header('Location: ../index.php?seccion=registrarse');
    } else {
        header('Location: ../../index.php?seccion=registrarse');
    }
    exit;
}

try {
    (new Usuario)->registrarUsuario([
        // El método internamente asigna el rol de usuario por defecto.
        'email' => $email,
        'password' => password_hash($password, PASSWORD_DEFAULT),
    ]);

    $_SESSION['mensajeFeedback'] = '¡Cuenta creada con éxito! Ahora puedes iniciar sesión.';
    $_SESSION['estadoFeedback'] = 'crear';
    
    // Detectar de dónde viene
    $referer = $_SERVER['HTTP_REFERER'] ?? '';
    if(strpos($referer, '/admin/') !== false) {
        header('Location: ../index.php?seccion=inicio');
    } else {
        header('Location: ../../index.php?seccion=iniciar-sesion');
    }
    exit;

} catch (\Throwable $th) {
    $_SESSION['mensajeFeedback'] = 'Error: ' . $th->getMessage(); // Mostrar error real para depuración
    $_SESSION['estadoFeedback'] = 'error';
    
    // Detectar de dónde viene
    $referer = $_SERVER['HTTP_REFERER'] ?? '';
    if(strpos($referer, '/admin/') !== false) {
        header('Location: ../index.php?seccion=registrarse');
    } else {
        header('Location: ../../index.php?seccion=registrarse');
    }
    exit;
}