<?php
/**
 * Autenticacion.php — Gestión de sesión de usuario
 * Login con verificación bcrypt, logout, y acceso al usuario actual.
 */
namespace App\Autenticacion;

use App\Modelos\Usuario;

class Autenticacion {
    /** @var ?Usuario El usuario que está autenticado. "null" si no hay ninguno. */
    private ?Usuario $usuario = null;

    public function intentar(string $email, string $password): bool {
        $usuario = $this->buscarUsuario($email);

        if(!$usuario) {
            return false; // No se encontró el usuario.
        }

        // Verificamos la contraseña.
        if(!password_verify($password, $usuario->getUsuarioPassword())) {
            return false; // Contraseña incorrecta.
        }

        // Si llegamos hasta aquí, el usuario y la contraseña son correctos.
        $this->autenticar($usuario);
        return true;
    }

    private function autenticar(Usuario $usuario): void {
        $_SESSION['usuario_id'] = $usuario->getUsuarioId();
        $this->usuario = $usuario;
    }

    public function cerrarSesion(): void
    {
        unset($_SESSION['usuario_id']);
        $this->usuario = null;
    }

    public function estaAutenticado(): bool
    {
        return isset($_SESSION['usuario_id']);
    }
    
    public function getUsuarioId(): ?string
    {
        return $_SESSION['usuario_id'] ?? null;
    }

    public function getUsuarioEmail(): ?string
    {
        return $_SESSION['email'] ?? null;
    }

    public function getUsuario(): ?Usuario
    {
        if(!$this->estaAutenticado()) return null;

        if(!$this->usuario) {
            $this->usuario = (new Usuario)->porId($this->getUsuarioId());
        }
        
        return $this->usuario;
    }

    public function buscarUsuario(string $email): ?Usuario
    {
        return (new Usuario)->porEmail($email);
    }
}

