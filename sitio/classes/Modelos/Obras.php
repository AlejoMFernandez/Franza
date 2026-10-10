<?php
/**
 * Obras.php — Modelo de obras de construcción
 * CRUD, búsqueda por tipo, orden manual, destacadas y galería en filesystem.
 * Los campos "extra" (rubro, etiquetas, pasos, etc.) existen desde la
 * actualización de base de datos del rediseño 2026 (ver Migracion.php).
 * Si todavía no se corrió, el modelo funciona igual con los campos viejos.
 */
namespace App\Modelos;

use App\BaseDeDatos\DBConexion;
use App\BaseDeDatos\Migracion;
use PDO;

class Obras
{
    private int $obra_id = 0;
    private ?int $usuario_fk = null;
    private string $carpeta = "";
    private string $titulo = "";
    private string $ubicacion = "";
    private string $explicacion = "";
    private string $tipo = "";
    private string $imagen = "";

    // Campos del rediseño 2026
    private ?string $rubro = null;
    private ?string $etiquetas = null;
    private ?string $badge = null;
    private ?string $problema = null;
    private ?string $pasos = null;
    private ?string $duracion = null;
    private ?string $anio = null;
    private $destacada = 0;
    private $orden = 0;
    private ?string $fotos_orden = null;

    /** Orden de los listados: manual primero, después por id. */
    private static function orderBy(): string
    {
        return Migracion::actualizada() ? ' ORDER BY orden ASC, obra_id ASC' : ' ORDER BY obra_id ASC';
    }

    public function cargarDataDeArray(array $data): void
    {
        foreach ($data as $k => $v) {
            if (property_exists($this, $k)) {
                $this->$k = $v;
            }
        }
    }

    public function todas(): array
    {
        $db = DBConexion::getConexion();
        $stmt = $db->prepare("SELECT * FROM obras" . self::orderBy());
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_CLASS, self::class);
        return $stmt->fetchAll();
    }

    public function porId(int $id): ?self
    {
        $db = DBConexion::getConexion();
        $stmt = $db->prepare("SELECT * FROM obras WHERE obra_id = ?");
        $stmt->execute([$id]);
        $stmt->setFetchMode(PDO::FETCH_CLASS, self::class);
        $obra = $stmt->fetch();
        return $obra ?: null;
    }

    public function porTipo(string $tipo): array
    {
        $db = DBConexion::getConexion();
        $stmt = $db->prepare("SELECT * FROM obras WHERE tipo = ?" . self::orderBy());
        $stmt->execute([$tipo]);
        $stmt->setFetchMode(PDO::FETCH_CLASS, self::class);
        return $stmt->fetchAll();
    }

    /** Obras marcadas como destacadas desde el panel. */
    public function destacadas(): array
    {
        if (!Migracion::actualizada()) {
            return [];
        }
        $db = DBConexion::getConexion();
        $stmt = $db->prepare("SELECT * FROM obras WHERE destacada = 1" . self::orderBy());
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_CLASS, self::class);
        return $stmt->fetchAll();
    }

    /** ¿Existe otra obra con esa carpeta? */
    public function carpetaEnUso(string $carpeta, int $excepto = 0): bool
    {
        $db = DBConexion::getConexion();
        $stmt = $db->prepare("SELECT COUNT(*) AS n FROM obras WHERE carpeta = ? AND obra_id <> ?");
        $stmt->execute([$carpeta, $excepto]);
        return (int) ($stmt->fetch(PDO::FETCH_ASSOC)['n'] ?? 0) > 0;
    }

    /*
    | ABM
    */

    public function crear(array $data): int
    {
        $db = DBConexion::getConexion();
        $campos = ['usuario_fk', 'carpeta', 'titulo', 'ubicacion', 'explicacion', 'tipo', 'imagen'];
        if (Migracion::actualizada()) {
            $campos = array_merge($campos, Migracion::CAMPOS_EXTRA);
            if (!isset($data['orden'])) {
                $max = $db->query("SELECT COALESCE(MAX(orden), 0) AS m FROM obras")->fetch(PDO::FETCH_ASSOC);
                $data['orden'] = (int) ($max['m'] ?? 0) + 1;
            }
        }
        $valores = [];
        foreach ($campos as $c) {
            $valores[$c] = $data[$c] ?? self::valorPorDefecto($c);
        }
        $consulta = "INSERT INTO obras (" . implode(', ', $campos) . ") VALUES (:" . implode(', :', $campos) . ")";
        $stmt = $db->prepare($consulta);
        $stmt->execute($valores);
        return (int) $db->lastInsertId();
    }

    public function editar(int $id, array $data): void
    {
        $db = DBConexion::getConexion();
        $campos = ['carpeta', 'titulo', 'ubicacion', 'explicacion', 'tipo', 'imagen'];
        if (Migracion::actualizada()) {
            $campos = array_merge($campos, array_values(array_diff(Migracion::CAMPOS_EXTRA, ['orden'])));
        }
        $sets = [];
        $valores = ['id' => $id];
        foreach ($campos as $c) {
            if (!array_key_exists($c, $data)) {
                continue;
            }
            $sets[] = "$c = :$c";
            $valores[$c] = $data[$c];
        }
        if (!$sets) {
            return;
        }
        $stmt = $db->prepare("UPDATE obras SET " . implode(', ', $sets) . " WHERE obra_id = :id");
        $stmt->execute($valores);
    }

    /** Guarda el orden manual: recibe los ids en el orden deseado. */
    public function ordenar(array $ids): void
    {
        if (!Migracion::actualizada()) {
            return;
        }
        $db = DBConexion::getConexion();
        $stmt = $db->prepare("UPDATE obras SET orden = ? WHERE obra_id = ?");
        foreach (array_values($ids) as $i => $id) {
            $stmt->execute([$i + 1, (int) $id]);
        }
    }

    public function marcarDestacada(int $id, bool $destacada): void
    {
        if (!Migracion::actualizada()) {
            return;
        }
        $db = DBConexion::getConexion();
        $stmt = $db->prepare("UPDATE obras SET destacada = ? WHERE obra_id = ?");
        $stmt->execute([$destacada ? 1 : 0, $id]);
    }

    public function eliminar(int $id): void
    {
        $db = DBConexion::getConexion();
        $stmtCarrousel = $db->prepare("DELETE FROM obras_carrousel WHERE obra_id = ?");
        $stmtCarrousel->execute([$id]);
        $stmt = $db->prepare("DELETE FROM obras WHERE obra_id = ?");
        $stmt->execute([$id]);
    }

    private static function valorPorDefecto(string $campo)
    {
        return in_array($campo, ['destacada', 'orden'], true) ? 0 : null;
    }

    /*
    | GETS & SETS
    */
    public function getObraId(): int { return (int) $this->obra_id; }
    public function setObraId(int $obra_id) { $this->obra_id = $obra_id; }
    public function getCarpeta(): string { return (string) $this->carpeta; }
    public function setCarpeta(string $carpeta) { $this->carpeta = $carpeta; }
    public function getTitulo(): string { return (string) $this->titulo; }
    public function setTitulo(string $titulo) { $this->titulo = $titulo; }
    public function getUbicacion(): string { return (string) $this->ubicacion; }
    public function setUbicacion(string $ubicacion) { $this->ubicacion = $ubicacion; }
    public function getExplicacion(): string { return (string) $this->explicacion; }
    public function setExplicacion(string $explicacion) { $this->explicacion = $explicacion; }
    public function getTipo(): string { return (string) $this->tipo; }
    public function setTipo(string $tipo) { $this->tipo = $tipo; }
    public function getImagen(): string { return (string) $this->imagen; }
    public function setImagen(string $imagen) { $this->imagen = $imagen; }

    public function getRubro(): string { return (string) $this->rubro; }
    public function getEtiquetas(): string { return (string) $this->etiquetas; }
    public function getBadge(): string { return (string) $this->badge; }
    public function getProblema(): string { return (string) $this->problema; }
    public function getDuracion(): string { return (string) $this->duracion; }
    public function getAnio(): string { return (string) $this->anio; }
    public function esDestacada(): bool { return (int) $this->destacada === 1; }
    public function getOrden(): int { return (int) $this->orden; }

    /** Pasos de "lo que hicimos" como lista (se guardan uno por línea). */
    public function getPasos(): array
    {
        $lineas = preg_split('/\r\n|\r|\n/', (string) $this->pasos);
        return array_values(array_filter(array_map('trim', $lineas), fn($l) => $l !== ''));
    }

    /** Orden manual de las fotos de la galería (nombres de archivo). */
    public function getFotosOrden(): array
    {
        $orden = json_decode((string) $this->fotos_orden, true);
        return is_array($orden) ? array_values(array_filter($orden, 'is_string')) : [];
    }

    /*
    | Imágenes
    */

    /** Carpeta de imágenes según el tipo. */
    public static function carpetaTipo(string $tipo): string
    {
        return ($tipo === 'civil') ? 'obrasciviles' : 'obrasindustriales';
    }

    /** Ruta absoluta de la carpeta de imágenes de la obra. */
    public function getRutaAbsoluta(): string
    {
        return __DIR__ . '/../../img/' . self::carpetaTipo($this->getTipo()) . '/' . $this->getCarpeta() . '/';
    }

    /**
     * Imágenes de la carpeta de la obra, respetando el orden manual si existe.
     * @return string[]
     */
    public function getImagenesCarpeta(): array
    {
        $rutaCarpeta = $this->getRutaAbsoluta();
        if ($this->getCarpeta() === '' || !is_dir($rutaCarpeta)) {
            return [];
        }
        $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $imagenes = [];
        foreach (scandir($rutaCarpeta) as $archivo) {
            if ($archivo === '.' || $archivo === '..') continue;
            $ext = strtolower(pathinfo($archivo, PATHINFO_EXTENSION));
            if (in_array($ext, $extensionesPermitidas, true)) {
                $imagenes[] = $archivo;
            }
        }
        $orden = $this->getFotosOrden();
        if ($orden) {
            $pos = array_flip($orden);
            usort($imagenes, function ($a, $b) use ($pos) {
                $pa = $pos[$a] ?? PHP_INT_MAX;
                $pb = $pos[$b] ?? PHP_INT_MAX;
                return $pa === $pb ? strcmp($a, $b) : $pa <=> $pb;
            });
        }
        return $imagenes;
    }

    /** Imágenes de galería (sin la portada). */
    public function getGaleriaImagenes(): array
    {
        $portada = $this->getImagen();
        return array_values(array_filter($this->getImagenesCarpeta(), fn($img) => $img !== $portada));
    }

    /** Ruta base de imágenes relativa al sitio. */
    public function getRutaImagenes(): string
    {
        return 'img/' . self::carpetaTipo($this->getTipo()) . '/' . $this->getCarpeta() . '/';
    }

    /** Obras al azar (compatibilidad con la home anterior). */
    public static function GetRandomObra(int $limite = 5): array
    {
        $db = DBConexion::getConexion();
        $stmt = $db->prepare("SELECT * FROM obras ORDER BY RAND() LIMIT ?");
        $stmt->bindValue(1, $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
