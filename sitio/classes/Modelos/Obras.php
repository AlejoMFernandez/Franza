<?php
/**
 * Obras.php — Modelo de obras de construcción
 * CRUD completo, búsqueda por tipo, obras aleatorias para destacados.
 * Gestión de galería de imágenes basada en filesystem.
 */
namespace App\Modelos;

use App\BaseDeDatos\DBConexion;
use PDO;

class Obras
{
    private int $obra_id = 0;
    private string $carpeta = "";
    private string $titulo = "";
    private string $ubicacion = "";
    private string $explicacion = "";
    private string $tipo = "";
    private string $imagen = "";

    // Carga los datos del objeto con la $data.
    public function cargarDataDeArray(array $data): void
    {
        $this->obra_id              = $data['obra_id'];
        $this->carpeta              = $data['carpeta'];
        $this->titulo               = $data['titulo'];
        $this->ubicacion            = $data['ubicacion'];
        $this->explicacion          = $data['explicacion'];
        $this->tipo                 = $data['tipo'];
        $this->imagen               = $data['imagen'];
    }

    public function todas(): array
    {
        // Traemos INFO de la base de datos
        $db = DBConexion::getConexion();

        $consulta = "SELECT * FROM obras";
        $stmt = $db->prepare($consulta);
        $stmt->execute();

        // Le pedimos a PDOStatement que nos retorne los registros como instancias de la clase obra.
        $stmt->setFetchMode(PDO::FETCH_CLASS, self::class);

        return $stmt->fetchAll();
    }

    public function porId(int $id): ?self
    {
        $db = DBConexion::getConexion();
        
        // Versión usando el holder posicional.
        $consulta = "SELECT * FROM obras
                    WHERE obra_id = ?";
        $stmt = $db->prepare($consulta);
        $stmt->execute([$id]);
        
        $stmt->setFetchMode(PDO::FETCH_CLASS, self::class);
        $obra = $stmt->fetch();

        if(!$obra) {
            return null;
        }

        return $obra;
    }

    public function porTipo(string $tipo): array
    {
        $db = DBConexion::getConexion();
        
        // Versión usando el holder posicional.
        $consulta = "SELECT * FROM obras
                    WHERE tipo = ?";
        $stmt = $db->prepare($consulta);
        $stmt->execute([$tipo]);
        
        $stmt->setFetchMode(PDO::FETCH_CLASS, self::class);
        return $stmt->fetchAll();
    }

    //

    /*
    | ABM
    */

    // 

    public function crear(array $data): int
    {
        $db = DBConexion::getConexion();
        $consulta = "INSERT INTO obras (usuario_fk, carpeta, titulo, ubicacion, explicacion, tipo, imagen)
                    VALUES (:usuario_fk, :carpeta, :titulo, :ubicacion, :explicacion, :tipo, :imagen)";
        $stmt = $db->prepare($consulta);
        $stmt->execute([
            'usuario_fk'        => $data['usuario_fk'],
            'carpeta'           => $data['carpeta'],
            'titulo'            => $data['titulo'],
            'ubicacion'         => $data['ubicacion'],
            'explicacion'       => $data['explicacion'],
            'tipo'              => $data['tipo'],
            'imagen'            => $data['imagen'],
        ]);
        return $db->lastInsertId();
    }

    public function editar(int $id, array $data): void
    {
        $db = DBConexion::getConexion();
        $consulta = "UPDATE obras
                    SET carpeta   = :carpeta,
                        titulo    = :titulo,
                        ubicacion = :ubicacion,
                        explicacion = :explicacion,
                        tipo      = :tipo,
                        imagen    = :imagen
                    WHERE obra_id = :id";
        $stmt = $db->prepare($consulta);
        $stmt->execute([
            'carpeta'     => $data['carpeta'],
            'titulo'      => $data['titulo'],
            'ubicacion'   => $data['ubicacion'],
            'explicacion' => $data['explicacion'],
            'tipo'        => $data['tipo'],
            'imagen'      => $data['imagen'],
            'id'          => $id,
        ]);
    }

    public function eliminar(int $id): void
    {
        $db = DBConexion::getConexion();

        // Primero eliminar registros relacionados en obras_carrousel
        $stmtCarrousel = $db->prepare("DELETE FROM obras_carrousel WHERE obra_id = ?");
        $stmtCarrousel->execute([$id]);

        // Luego eliminar la obra
        $consulta = "DELETE FROM obras WHERE obra_id = ?";
        $stmt = $db->prepare($consulta);
        $stmt->execute([$id]);
    }

    /*
    | GETS & SETS
    */

    // obra ID
    public function getObraId(): int
    {
        return $this->obra_id;
    }
    public function setObraId(int $obra_id)
    {
        $this->obra_id = $obra_id;
    }
    // Carpeta
    public function getCarpeta(): string
    {
        return $this->carpeta;
    }
    public function setCarpeta(string $carpeta)
    {
        $this->carpeta = $carpeta;
    }
    // Título
    public function getTitulo(): string
    {
        return $this->titulo;
    }
    public function setTitulo(string $titulo)
    {
        $this->titulo = $titulo;
    }
    // Ubicacion
    public function getUbicacion(): string
    {
        return $this->ubicacion;
    }
    public function setUbicacion(string $ubicacion)
    {
        $this->ubicacion = $ubicacion;
    }
    // Explicacion
    public function getExplicacion(): string
    {
        return $this->explicacion;
    }
    public function setExplicacion(string $explicacion)
    {
        $this->explicacion = $explicacion;
    }
    // Tipo
    public function getTipo(): string
    {
        return $this->tipo;
    }
    public function setTipo(string $tipo)
    {
        $this->tipo = $tipo;
    }
    // Imagen
    public function getImagen(): string
    {
        return $this->imagen;
    }
    public function setImagen(string $imagen)
    {
        $this->imagen = $imagen;
    }

    /*
    | Aditionals functions
    */

    /**
     * Devuelve las imágenes de la carpeta de la obra en el filesystem.
     * @return string[] Lista de nombres de archivo de imagen encontrados.
     */
    public function getImagenesCarpeta(): array
    {
        $tipo = $this->getTipo();
        $carpeta = $this->getCarpeta();
        $tipoCarpeta = ($tipo === 'civil') ? 'obrasciviles' : 'obrasindustriales';
        $rutaCarpeta = __DIR__ . '/../../img/' . $tipoCarpeta . '/' . $carpeta;

        if (!is_dir($rutaCarpeta)) {
            return [];
        }

        $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $imagenes = [];

        $archivos = scandir($rutaCarpeta);
        foreach ($archivos as $archivo) {
            if ($archivo === '.' || $archivo === '..') continue;
            $ext = strtolower(pathinfo($archivo, PATHINFO_EXTENSION));
            if (in_array($ext, $extensionesPermitidas)) {
                $imagenes[] = $archivo;
            }
        }

        return $imagenes;
    }

    /**
     * Devuelve las imágenes de galería (excluyendo la imagen de portada).
     * @return string[] Lista de nombres de archivo de imagen de galería.
     */
    public function getGaleriaImagenes(): array
    {
        $todas = $this->getImagenesCarpeta();
        $portada = $this->getImagen();
        if (empty($portada)) {
            return $todas;
        }
        return array_values(array_filter($todas, fn($img) => $img !== $portada));
    }

    /**
     * Devuelve la ruta base de imágenes relativa al sitio.
     */
    public function getRutaImagenes(): string
    {
        $tipo = $this->getTipo();
        $tipoCarpeta = ($tipo === 'civil') ? 'obrasciviles' : 'obrasindustriales';
        return 'img/' . $tipoCarpeta . '/' . $this->getCarpeta() . '/';
    }

    // Devuelve obras random al azar para banners.
    public static function GetRandomObra(int $limite = 5): array
    {
        $db = DBConexion::getConexion();
        $stmt = $db->prepare("SELECT * FROM obras ORDER BY RAND() LIMIT ?");
        $stmt->bindValue(1, $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
