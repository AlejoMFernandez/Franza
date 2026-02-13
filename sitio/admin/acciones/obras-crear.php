<?php
/**
 * obras-crear.php — Acción: crear nueva obra
 * Recibe datos del formulario, valida, genera slug para carpeta,
 * sube imagen principal (opcional) y fotos de galería,
 * y crea el registro en la base de datos.
 * Límite por archivo: 10MB. Extensiones: jpg, jpeg, png, gif, webp.
 */

use App\Autenticacion\Autenticacion;
use App\Modelos\Obras;
use App\BaseDeDatos\DBConexion;
use App\Seguridad\Csrf;

require_once __DIR__ . '/../../bootstrap/init.php';

// Validar token CSRF
Csrf::verificarOAbortar('../index.php?seccion=obras-agregar');

$autenticacion = new Autenticacion;
if(!$autenticacion->estaAutenticado()) {
    $_SESSION['mensajeFeedback'] = "Para ingresar a esta sección es necesario iniciar sesión.";
    $_SESSION['estadoFeedback'] = "error";
    header("Location: ../index.php");
    exit;
}

$usuarioId = $autenticacion->getUsuarioId();
if(empty($usuarioId)) {
    $_SESSION['mensajeFeedback'] = "No se pudo identificar el usuario. Inicia sesión nuevamente.";
    $_SESSION['estadoFeedback'] = "error";
    header('Location: ../index.php?seccion=obras-agregar');
    exit;
}

// Obtención de datos del formulario
$ubicacion   = $_POST['ubicacion'] ?? '';
$titulo      = $_POST['titulo'] ?? '';
$explicacion = $_POST['explicacion'] ?? '';
$tipo        = $_POST['tipo'] ?? '';
$imagen      = $_FILES['imagen'] ?? null;

// Auto-generar carpeta como slug del título
function generarSlug(string $texto): string {
    $slug = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $texto);
    $slug = strtolower($slug);
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
    $slug = trim($slug, '-');
    return $slug ?: 'obra-' . time();
}
$carpeta = generarSlug($titulo);

$errores = [];

// Validaciones
if(empty($ubicacion)) {
    $errores['ubicacion'] = "La ubicación debe tener un valor.";
}

if(empty($titulo)) {
    $errores['titulo'] = "El título debe tener un valor.";
} else if(strlen($titulo) <= 2) {
    $errores['titulo'] = "El título debe tener al menos 2 caracteres.";
}

if(empty($explicacion)) {
    $errores['explicacion'] = "La explicación debe tener un valor.";
}

if(empty($tipo)) {
    $errores['tipo'] = "El tipo debe tener un valor.";
} else if($tipo !== 'civil' && $tipo !== 'industrial') {
    $errores['tipo'] = "El tipo debe ser 'civil' o 'industrial'.";
}

if(count($errores) > 0) {
    $_SESSION['errores'] = $errores;
    $_SESSION['datos_viejos'] = $_POST;
    header("Location: ../index.php?seccion=obras-agregar");
    exit;
}

// Procesamiento de imagen
$nombreImagen = '';
if(!empty($imagen['tmp_name'])) {
    // Validar tipo de archivo
    $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $ext = strtolower(pathinfo($imagen['name'], PATHINFO_EXTENSION));
    
    if(!in_array($ext, $extensionesPermitidas)) {
        $_SESSION['mensajeFeedback'] = "Solo se permiten imágenes (jpg, jpeg, png, gif, webp).";
        $_SESSION['estadoFeedback'] = "error";
        $_SESSION['datos_viejos'] = $_POST;
        header("Location: ../index.php?seccion=obras-agregar");
        exit;
    }

    // Validar tamaño de archivo (máximo 10MB)
    if($imagen['size'] > 10485760) {
        $_SESSION['mensajeFeedback'] = "La imagen no debe superar los 10MB.";
        $_SESSION['estadoFeedback'] = "error";
        $_SESSION['datos_viejos'] = $_POST;
        header("Location: ../index.php?seccion=obras-agregar");
        exit;
    }

    // Generar nombre seguro y único
    $base = pathinfo($imagen['name'], PATHINFO_FILENAME);
    $seguro = preg_replace('/[^a-zA-Z0-9_-]/', '_', $base);
    $nombreImagen = $seguro . '_' . time() . '.' . $ext;

    // Ajusta el nombre de la carpeta según el tipo
    $tipoCarpeta = ($tipo === 'civil') ? 'obrasciviles' : (($tipo === 'industrial') ? 'obrasindustriales' : 'obrasotros');
    $rutaImagenes = __DIR__ . "/../../img/{$tipoCarpeta}/{$carpeta}/";
    if (!is_dir($rutaImagenes)) {
        mkdir($rutaImagenes, 0777, true); // Crea la carpeta si no existe
    }

    if(!move_uploaded_file($imagen['tmp_name'], $rutaImagenes . $nombreImagen)) {
        $_SESSION['mensajeFeedback'] = "Ocurrió un error inesperado al subir la imagen. La obra '<b>" . htmlspecialchars($titulo, ENT_QUOTES) . "</b>' no pudo ser creada.";
        $_SESSION['estadoFeedback'] = "error";
        $_SESSION['datos_viejos'] = $_POST;
        header("Location: ../index.php?seccion=obras-agregar");
        exit;
    }
}

// Asegurar que la carpeta exista aunque no haya imagen
$tipoCarpetaDir = ($tipo === 'civil') ? 'obrasciviles' : 'obrasindustriales';
$rutaCarpetaDir = __DIR__ . "/../../img/{$tipoCarpetaDir}/{$carpeta}/";
if (!is_dir($rutaCarpetaDir)) {
    mkdir($rutaCarpetaDir, 0777, true);
}

try {
    $obra_id = (new Obras)->crear([
        'usuario_fk'  => $autenticacion->getUsuarioId(),
        'ubicacion'   => $ubicacion,
        'titulo'      => $titulo,
        'explicacion' => $explicacion,
        'tipo'        => $tipo,
        'imagen'      => $nombreImagen,
        'carpeta'     => $carpeta,
    ]);

    // Handle gallery photos upload
    if (!empty($_FILES['galeria']['tmp_name'][0])) {
        $tipoCarpeta = ($tipo === 'civil') ? 'obrasciviles' : 'obrasindustriales';
        $rutaGaleria = __DIR__ . "/../../img/{$tipoCarpeta}/{$carpeta}/";
        if (!is_dir($rutaGaleria)) {
            mkdir($rutaGaleria, 0777, true);
        }

        $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $galeriaFiles = $_FILES['galeria'];
        $galeriaCount = count($galeriaFiles['tmp_name']);

        for ($i = 0; $i < $galeriaCount; $i++) {
            if (empty($galeriaFiles['tmp_name'][$i])) continue;
            $ext = strtolower(pathinfo($galeriaFiles['name'][$i], PATHINFO_EXTENSION));
            if (!in_array($ext, $extensionesPermitidas)) continue;
            if ($galeriaFiles['size'][$i] > 10485760) continue;

            $base = pathinfo($galeriaFiles['name'][$i], PATHINFO_FILENAME);
            $seguro = preg_replace('/[^a-zA-Z0-9_-]/', '_', $base);
            $nombreGaleria = $seguro . '_' . time() . '_' . $i . '.' . $ext;

            move_uploaded_file($galeriaFiles['tmp_name'][$i], $rutaGaleria . $nombreGaleria);
        }
    }

    $_SESSION['mensajeFeedback'] = "La obra '<b>" . $titulo . "</b>' se creó con éxito.";
    $_SESSION['estadoFeedback'] = "crear";
    header('Location: ../index.php?seccion=obras');
    exit;
} catch (\Throwable $th) {
    error_log($th->getMessage());
    $_SESSION['mensajeFeedback'] = "ERROR - La obra '<b>" . $titulo . "</b>' no pudo ser creada. - " . $th->getMessage();
    $_SESSION['datos_viejos'] = $_POST;
    $_SESSION['estadoFeedback'] = "error";
    header('Location: ../index.php?seccion=obras-agregar');
    exit;
}