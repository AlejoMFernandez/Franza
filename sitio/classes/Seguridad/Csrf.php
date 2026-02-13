<?php
/**
 * Csrf.php — Protección CSRF con token por sesión
 * Genera tokens, valida con hash_equals, aborta si es inválido.
 * Compatible con múltiples tabs (token único por sesión, no single-use).
 */
namespace App\Seguridad;

class Csrf
{
    private const TOKEN_NAME = '_csrf_token';

    /**
     * Genera un token CSRF y lo guarda en sesión.
     */
    public static function generar(): string
    {
        $token = bin2hex(random_bytes(32));
        $_SESSION[self::TOKEN_NAME] = $token;
        return $token;
    }

    /**
     * Devuelve el token actual de sesión (o genera uno nuevo).
     */
    public static function obtener(): string
    {
        if (empty($_SESSION[self::TOKEN_NAME])) {
            return self::generar();
        }
        return $_SESSION[self::TOKEN_NAME];
    }

    /**
     * Devuelve el campo hidden HTML para incluir en formularios.
     */
    public static function campo(): string
    {
        $token = self::obtener();
        return '<input type="hidden" name="' . self::TOKEN_NAME . '" value="' . htmlspecialchars($token, ENT_QUOTES) . '">';
    }

    /**
     * Valida que el token enviado coincida con el de sesión.
     * Usa token por sesión (no single-use) para compatibilidad con múltiples tabs.
     */
    public static function validar(?string $token = null): bool
    {
        if ($token === null) {
            $token = $_POST[self::TOKEN_NAME] ?? $_GET[self::TOKEN_NAME] ?? '';
        }

        $tokenSesion = $_SESSION[self::TOKEN_NAME] ?? '';

        if (empty($tokenSesion) || empty($token)) {
            return false;
        }

        return hash_equals($tokenSesion, $token);
    }

    /**
     * Aborta con error si el token CSRF no es válido.
     * Redirige a la URL indicada con mensaje de error.
     */
    public static function verificarOAbortar(string $urlRedireccion): void
    {
        if (!self::validar()) {
            $_SESSION['mensajeFeedback'] = 'Token de seguridad inválido. Intentá de nuevo.';
            $_SESSION['estadoFeedback'] = 'error';
            header('Location: ' . $urlRedireccion);
            exit;
        }
    }
}
