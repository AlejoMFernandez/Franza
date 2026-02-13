<?php
/**
 * DBConexion.php — Conexión singleton a MySQL/MariaDB vía PDO
 * Configuración: localhost:3306, usuario root, DB franza.
 * Ajustar credenciales antes de subir a producción.
 */
namespace App\BaseDeDatos;

use PDO;

class DBConexion
{
    public const DB_HOST = '127.0.0.1';
    public const DB_PORT = '3306';
    public const DB_USER = 'root';
    public const DB_PASS = '';
    public const DB_SCHEMA = 'franza';

    private static ?PDO $db = null;

    static public function getConexion(): PDO
    {
        if(self::$db === null) {

            // DSN correcto para MySQL: host y port separados por punto y coma.
            $dsn = "mysql:host=" . self::DB_HOST . ";port=" . self::DB_PORT . ";dbname=" . self::DB_SCHEMA . ";charset=utf8mb4";

            try {
                self::$db = new PDO($dsn, self::DB_USER, self::DB_PASS, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::ATTR_TIMEOUT => 5,
                ]);
            } catch (\Throwable $th) {
                // No exponemos detalles sensibles al usuario final.
                error_log("DB error: " . $th->getMessage());
                exit('Error al conectar con la base de datos.');
            }
        }
        return self::$db;
    }
}