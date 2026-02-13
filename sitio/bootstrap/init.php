<?php
/**
 * init.php — Bootstrap del proyecto
 * Inicializa sesión con configuración segura, regenera ID periódicamente
 * e incluye el autoloader de clases.
 */

// Configuración de seguridad de sesión (ANTES de session_start)
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Cambiar a 1 si usas HTTPS

session_start(); // Iniciamos la sesión.

// Regenerar ID de sesión periódicamente
if (!isset($_SESSION['session_created'])) {
    $_SESSION['session_created'] = time();
} else if (time() - $_SESSION['session_created'] > 1800) {
    // Regenerar cada 30 minutos
    session_regenerate_id(true);
    $_SESSION['session_created'] = time();
}

// Incluimos el autoload.
require_once __DIR__ . '/autoload.php';