<?php
/**
 * Usuario.php — Modelo de usuarios
 * Búsqueda por ID/email, registro con hash bcrypt.
 * Roles: ROL_ADMIN (1), ROL_USUARIO (2).
 */

namespace App\Modelos;

use App\BaseDeDatos\DBConexion;
use PDO;

class Usuario
{
    private int $usuario_id = 0;
    private int $rol_fk = 0;
    private string $email = '';
    private string $password = '';

    // Definimos ROLES
    public const ROL_ADMIN = 1;
    public const ROL_USUARIO = 2;

    // Obtener USUARIO por ID
    public function porId(int $id): ?self
    {
        $db = DBConexion::getConexion();
        $consulta = "SELECT * FROM usuarios
                    WHERE usuario_id = ?"; // TOKEN
        $stmt = $db->prepare($consulta);
        $stmt->execute([$id]);
        $stmt->setFetchMode(PDO::FETCH_CLASS, self::class);
        $usuario = $stmt->fetch();

        if(!$usuario) {
            return null;
        }

        return $usuario;
    }

    // Obtener USUARIO por Email
    public function porEmail(string $email): ?self
    {
        $db = DBConexion::getConexion();
        $consulta = "SELECT * FROM usuarios
                    WHERE email = ?"; // TOKEN
        $stmt = $db->prepare($consulta);
        $stmt->execute([$email]);
        $stmt->setFetchMode(PDO::FETCH_CLASS, self::class);
        $usuario = $stmt->fetch();

        if(!$usuario) {
            return null;
        }

        return $usuario;
    }   

    // Alta de usuarios
    public function registrarUsuario(array $data): void
    {
        $db = DBConexion::getConexion();
        $consulta = "INSERT INTO usuarios (rol_fk, email, password) 
                     VALUES (:rol_fk, :email, :password)";
        $stmt = $db->prepare($consulta);
        $stmt->execute([
            'rol_fk' => Usuario::ROL_USUARIO, // o Usuario::ROL_ADMIN
            'email' => $data['email'],
            'password' => $data['password'],
        ]);
    }

    public function esAdmin(): bool
    {
        return $this->getUsuarioRolId() === self::ROL_ADMIN;
    }
    public function noEsAdmin(): bool
    {
        return $this->getUsuarioRolId() !== self::ROL_ADMIN;
    }

    /*
    | GETS & SETS
    */

    public function setUsuarioId(int $usuario_id): void
    {
        $this->usuario_id = $usuario_id;
    }
    public function getUsuarioId(): int
    {
        return $this->usuario_id;
    }
    public function setUsuarioRolId(int $rol_fk): void
    {
        $this->rol_fk = $rol_fk;
    }
    public function getUsuarioRolId(): int
    {
        return $this->rol_fk;
    }
    public function setUsuarioEmail(string $email): void
    {
        $this->email = $email;
    }
    public function getUsuarioEmail(): string
    {
        return $this->email;
    }
    public function setUsuarioPassword(string $password): void
    {
        $this->password = $password;
    }
    public function getUsuarioPassword(): string
    {
        return $this->password;
    }

}