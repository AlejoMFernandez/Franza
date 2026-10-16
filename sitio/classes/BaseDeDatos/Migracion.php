<?php
/**
 * Migracion.php — Actualización de la base de datos para el rediseño 2026.
 * - Agrega los campos nuevos de obras (rubro, etiquetas, pasos, destacada, orden, etc.).
 * - Pasa la tabla a utf8mb4 y la explicación a TEXT (antes cortaba en 500 caracteres).
 * - Repara títulos y textos con tildes rotas ("Ampliaci??n") usando los textos originales.
 * - Carga los datos extra iniciales de las obras existentes (si están vacíos).
 * Se puede correr varias veces: solo hace lo que falta.
 */
namespace App\BaseDeDatos;

use PDO;

class Migracion
{
    public const CAMPOS_EXTRA = ['rubro', 'etiquetas', 'badge', 'problema', 'pasos', 'duracion', 'anio', 'destacada', 'orden', 'fotos_orden'];

    private const COLUMNAS = [
        'rubro'       => 'VARCHAR(80) NULL',
        'etiquetas'   => 'VARCHAR(255) NULL',
        'badge'       => 'VARCHAR(60) NULL',
        'problema'    => 'TEXT NULL',
        'pasos'       => 'TEXT NULL',
        'duracion'    => 'VARCHAR(60) NULL',
        'anio'        => 'VARCHAR(10) NULL',
        'destacada'   => 'TINYINT(1) NOT NULL DEFAULT 0',
        'orden'       => 'INT NOT NULL DEFAULT 0',
        'fotos_orden' => 'TEXT NULL',
        'slug'        => 'VARCHAR(160) NULL',
    ];

    /** Textos originales de las obras cargadas, para reparar tildes rotas. */
    private const ORIGINALES = [
        67 => ['titulo' => 'Reforma de azotea', 'ubicacion' => 'San Miguel'],
        68 => ['titulo' => 'Construcción completa de hogar', 'ubicacion' => 'Barrio Cerrado ?', 'explicacion' => 'Explicación extensa de que se hizo'],
        69 => ['titulo' => 'Reforma completa de balcón', 'alias' => 'Reforma complete de balcón', 'ubicacion' => 'Villa del Parque'],
        70 => ['titulo' => 'Obra particular', 'ubicacion' => 'Zona Norte GBA'],
        71 => ['titulo' => 'Ampliación y reforma de vivienda unifamiliar', 'ubicacion' => 'San Isidro - GBA'],
        72 => ['titulo' => 'Restauración de fachada', 'ubicacion' => 'Mitre'],
        73 => ['titulo' => 'Reforma completa de hogar', 'ubicacion' => 'Tres de Febrero'],
    ];
    private const TEXTO_TERRAZA = 'La zona de la terraza presentaba problemas de filtraciones hacia la PB por fisuras en su revestimiento. Se reemplazaron los dañados, previo a la reparación de la carpeta para generar el sello hidrófugo. Se quitaron y volvieron a generar las juntas de dilatación con Poliuretánico de primera marca. Se pintó toda la superficie.';
    private const EXPLICACIONES = [
        67 => 'La azotea presentaba problemas de filtraciones hacia la PB por fisuras en su revestimiento. Se reemplazaron los dañados, previo a la reparación de la carpeta para generar el sello hidrófugo. Se quitaron y volvieron a generar las juntas de dilatación con Poliuretánico de primera marca. Se pintó toda la superficie.',
        70 => 'Mediante un proyecto se realizó la ampliación de una cocina existente en conexión con un nuevo quincho. Se demolieron paredes y se generaron nuevos refuerzos. Mediante vigas de encadenados se montó la nueva platea para soportar la carga del nuevo espacio. Se realizó una parrilla completa, techo a dos aguas de tejas francesas, nuevas ventanas en techo de paño fijo, un baño y la ampliación de la cocina existente.',
        71 => 'Mediante un proyecto, se ejecutó esta obra que comprendía el anexo de un nuevo espacio a uno existente y la reforma interna de los ambientes. Se realizaron tareas de hormigón visto - Pintura - Electricidad nueva - Gas - Aberturas - Techados - Aberturas Velux',
        72 => 'Comenzamos con hidrolavado baja presión, retiro de vegetación en griteas y sellado de las mismas. Se restauraron los ornamentos de la facha y se repararon las partes afectadas por la corrosión debido a la exposición del hierro de estructuras al agua. Se pintó con 4 manos de pintura para muros de primera calidad y se pintaron los frentes de los locales comerciales en la PB',
    ];

    private static ?bool $actualizada = null;
    private static ?bool $tieneSlug = null;

    /** ¿Ya existe el campo slug (direcciones lindas de las obras)? */
    public static function tieneSlug(): bool
    {
        if (self::$tieneSlug === null) {
            self::$tieneSlug = self::tieneColumna('slug');
        }
        return self::$tieneSlug;
    }

    /** ¿Está todo actualizado (campos del rediseño y direcciones lindas)? */
    public static function completa(): bool
    {
        return self::actualizada() && self::tieneSlug();
    }

    /** ¿La tabla obras ya tiene los campos nuevos? */
    public static function actualizada(): bool
    {
        if (self::$actualizada === null) {
            self::$actualizada = self::tieneColumna('fotos_orden');
        }
        return self::$actualizada;
    }

    private static function tieneColumna(string $columna): bool
    {
        try {
            DBConexion::getConexion()->query("SELECT $columna FROM obras LIMIT 0");
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Ejecuta todo lo que falte. Devuelve la lista de cambios hechos.
     * @param array $extrasIniciales datos extra por carpeta (de data/franza.php)
     */
    public static function ejecutar(array $extrasIniciales = []): array
    {
        $db = DBConexion::getConexion();
        $esMysql = $db->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql';
        $log = [];

        if ($esMysql) {
            try {
                $db->exec("ALTER TABLE obras CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                $db->exec("ALTER TABLE obras MODIFY explicacion TEXT NOT NULL");
                $log[] = 'Tabla de obras pasada a utf8mb4 y explicación sin límite de 500 caracteres.';
            } catch (\Throwable $e) {
                $log[] = 'No se pudo convertir la tabla a utf8mb4: ' . $e->getMessage();
            }
        }

        foreach (self::COLUMNAS as $col => $def) {
            if (!self::tieneColumna($col)) {
                $db->exec("ALTER TABLE obras ADD COLUMN $col $def");
                $log[] = "Campo nuevo: $col.";
            }
        }
        self::$actualizada = true;
        self::$tieneSlug = true;

        // Reparar tildes rotas y cargar datos extra iniciales.
        $filas = $db->query("SELECT obra_id, carpeta, titulo, ubicacion, explicacion, rubro, orden FROM obras ORDER BY obra_id")->fetchAll(PDO::FETCH_ASSOC);
        $upd = $db->prepare("UPDATE obras SET titulo = :titulo, ubicacion = :ubicacion, explicacion = :explicacion WHERE obra_id = :id");
        $i = 0;
        foreach ($filas as $f) {
            $i++;
            $id = (int) $f['obra_id'];
            $nuevo = ['titulo' => $f['titulo'], 'ubicacion' => $f['ubicacion'], 'explicacion' => $f['explicacion']];
            $orig = self::ORIGINALES[$id] ?? [];
            $origExpl = self::EXPLICACIONES[$id] ?? (in_array($id, [69, 73], true) ? self::TEXTO_TERRAZA : ($orig['explicacion'] ?? null));
            foreach (['titulo', 'ubicacion'] as $campo) {
                $coincide = isset($orig[$campo]) && (self::mismoTexto($f[$campo], $orig[$campo])
                    || ($campo === 'titulo' && isset($orig['alias']) && self::mismoTexto($f[$campo], $orig['alias'])));
                if ($coincide) {
                    $nuevo[$campo] = $orig[$campo];
                }
            }
            if ($origExpl !== null) {
                $soloAscii = fn(string $t) => substr(preg_replace('/[^a-z ]/', '', strtolower($t)), 0, 120);
                $roto = strpos($f['explicacion'], '?') !== false || mb_strlen($f['explicacion']) >= 495;
                if ($roto && ($soloAscii($f['explicacion']) === $soloAscii($origExpl) || self::mismoTexto($f['explicacion'], $origExpl)
                    || str_starts_with($soloAscii($f['explicacion']), substr($soloAscii($origExpl), 0, 60)))) {
                    // Repara tildes y quita el texto que quedó cortado en 500 caracteres.
                    $nuevo['explicacion'] = $origExpl;
                }
            }
            if ($nuevo !== ['titulo' => $f['titulo'], 'ubicacion' => $f['ubicacion'], 'explicacion' => $f['explicacion']]) {
                $upd->execute($nuevo + ['id' => $id]);
                $log[] = "Obra $id: textos reparados (" . $nuevo['titulo'] . ').';
            }

            $extra = $extrasIniciales[$f['carpeta']] ?? null;
            if ($extra && empty($f['rubro'])) {
                $stmt = $db->prepare("UPDATE obras SET rubro = ?, etiquetas = ?, badge = ?, problema = ?, pasos = ? WHERE obra_id = ?");
                $stmt->execute([
                    $extra['rubro'] ?? null,
                    $extra['etiquetas'] ?? null,
                    $extra['badge'] ?? null,
                    $extra['problema'] ?? null,
                    !empty($extra['pasos']) ? implode("\n", $extra['pasos']) : null,
                    $id,
                ]);
                $log[] = "Obra $id: rubro y etiquetas cargados.";
            }
            if ((int) $f['orden'] === 0) {
                $db->prepare("UPDATE obras SET orden = ? WHERE obra_id = ?")->execute([$i, $id]);
            }
        }
        // Direcciones lindas: cada obra recibe su slug a partir del título (si no tiene).
        $modelo = new \App\Modelos\Obras();
        $sinSlug = $db->query("SELECT obra_id, titulo FROM obras WHERE slug IS NULL OR slug = '' ORDER BY obra_id")->fetchAll(PDO::FETCH_ASSOC);
        $updSlug = $db->prepare("UPDATE obras SET slug = ? WHERE obra_id = ?");
        foreach ($sinSlug as $f) {
            $slug = $modelo->slugUnico(\App\Modelos\Obras::slugDesde($f['titulo']), (int) $f['obra_id']);
            $updSlug->execute([$slug, (int) $f['obra_id']]);
            $log[] = 'Obra ' . $f['obra_id'] . ': dirección /obra/' . $slug;
        }
        return $log;
    }

    /**
     * Compara ignorando tildes y los "?" que deja una codificación rota.
     */
    private static function mismoTexto(string $actual, string $original): bool
    {
        $norm = function (string $s): string {
            $s = mb_strtolower($s, 'UTF-8');
            $s = strtr($s, ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ñ' => 'n', 'ü' => 'u']);
            return $s;
        };
        $a = $norm($actual);
        $o = $norm($original);
        if ($a === $o) {
            return true;
        }
        // "Ampliaci??n" / "Ampliaci?n" / "AmpliaciÃ³n" contra "Ampliacion"
        $patron = '';
        foreach (preg_split('//u', $original, -1, PREG_SPLIT_NO_EMPTY) as $ch) {
            $patron .= (strlen($ch) > 1) ? '(?:\?{1,2}|.{1,2}|' . preg_quote($norm($ch), '/') . ')' : preg_quote(mb_strtolower($ch), '/');
        }
        return (bool) preg_match('/^' . $patron . '$/iu', mb_strtolower($actual, 'UTF-8'));
    }
}
