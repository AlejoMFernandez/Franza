<?php
namespace App\Modelos;

use App\BaseDeDatos\DBConexion;
use PDO;

class Modelo
{
    /** @var string El nombre de la tabla del modelo. */
    protected string $table = '';

    /**
     * @var static[]
     */
    public function todos(): array
    {
        $db = DBConexion::getConexion();
        $consulta = "SELECT * FROM " . $this->table;
        $stmt = $db->prepare($consulta);
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_CLASS, static::class);
        return $stmt->fetchAll();
    }
}