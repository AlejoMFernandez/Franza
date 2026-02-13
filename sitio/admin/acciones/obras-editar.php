<?php
/**
 * obras-editar.php — Acción: editar obra existente
 * Procesa formulario de edición, regenera slug, mueve carpeta si
 * cambió título/tipo, sube nueva imagen/galería, y procesa
 * eliminaciones diferidas (portada y galería marcadas para borrar).
 */

use App\Autenticacion\Autenticacion;
use App\Modelos\Obras;
use App\Seguridad\Csrf;

require_once __DIR__ . '/../../bootstrap/init.php';

// Validar token CSRF
Csrf::verificarOAbortar('../index.php?seccion=obras');

$autenticacion = new Autenticacion;
if(!$autenticacion->estaAutenticado()) {
    $_SESSION['mensajeFeedback'] = "Para ingresar a esta sección es necesario iniciar sesión.";
    $_SESSION['estadoFeedback'] = "error";
    header("Location: ../index.php");
    exit;
}

// 1. Capturamos los datos del form.
$obra_id     = (int)($_GET['id'] ?? 0);
$titulo      = trim($_POST['titulo'] ?? '');
$ubicacion   = trim($_POST['ubicacion'] ?? '');
$explicacion = trim($_POST['explicacion'] ?? '');
$tipo        = trim($_POST['tipo'] ?? ''); // civil | industrial
$imagen      = $_FILES['imagen'] ?? null;

// Auto-generar carpeta como slug del título
function generarSlug(string $texto): string {
    $slug = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $texto);
    $slug = strtolower($slug);
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
    $slug = trim($slug, '-');
    return $slug ?: 'obra-' . time();
}

// Traemos la obra que estamos editando.
$obra = (new Obras)->porId($obra_id);

if(!$obra) {
    $_SESSION['mensajeFeedback'] = "La obra indicada no existe.";
    $_SESSION['estadoFeedback'] = "error";
    header('Location: ../index.php?seccion=obras');
    exit;
}

// 2. Validación
$errores = [];
if($titulo === '') $errores['titulo'] = "El título es obligatorio.";
if($ubicacion === '') $errores['ubicacion'] = "La ubicación es obligatoria.";
if($explicacion === '') $errores['explicacion'] = "La explicación es obligatoria.";
if($tipo !== 'civil' && $tipo !== 'industrial') $errores['tipo'] = "Tipo inválido.";

if(count($errores) > 0) {
    $_SESSION['errores'] = $errores;
    $_SESSION['datos_viejos'] = $_POST;
    header("Location: ../index.php?seccion=obras-editar&id=" . $obra_id);
    exit;
}

// 3. Subir la imagen si corresponde (opcional)
$nombreImagen = $obra->getImagen();

// 3b. Generar slug y mover carpeta si cambió el título o tipo
$carpeta = generarSlug($titulo);
$tipoAnterior = $obra->getTipo();
$carpetaAnterior = $obra->getCarpeta();
if ($tipoAnterior !== $tipo || $carpetaAnterior !== $carpeta) {
    $tipoCarpetaOld = ($tipoAnterior === 'civil') ? 'obrasciviles' : 'obrasindustriales';
    $tipoCarpetaNew = ($tipo === 'civil') ? 'obrasciviles' : 'obrasindustriales';
    $rutaOld = __DIR__ . "/../../img/{$tipoCarpetaOld}/{$carpetaAnterior}/";
    $rutaNew = __DIR__ . "/../../img/{$tipoCarpetaNew}/{$carpeta}/";

    if (is_dir($rutaOld) && $rutaOld !== $rutaNew) {
        if (!is_dir(dirname($rutaNew))) {
            mkdir(dirname($rutaNew), 0777, true);
        }
        rename($rutaOld, $rutaNew);
    }
}

if($imagen && !empty($imagen['tmp_name'])) {
    // Validar tipo de archivo
    $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $ext = strtolower(pathinfo($imagen['name'], PATHINFO_EXTENSION));
    
    if(!in_array($ext, $extensionesPermitidas)) {
        $_SESSION['mensajeFeedback'] = "Solo se permiten imágenes (jpg, jpeg, png, gif, webp).";
        $_SESSION['estadoFeedback'] = "error";
        $_SESSION['datos_viejos'] = $_POST;
        header("Location: ../index.php?seccion=obras-editar&id=" . $obra_id);
        exit;
    }

    // Validar tamaño de archivo (máximo 10MB)
    if($imagen['size'] > 10485760) {
        $_SESSION['mensajeFeedback'] = "La imagen no debe superar los 10MB.";
        $_SESSION['estadoFeedback'] = "error";
        $_SESSION['datos_viejos'] = $_POST;
        header("Location: ../index.php?seccion=obras-editar&id=" . $obra_id);
        exit;
    }

    $base = pathinfo($imagen['name'], PATHINFO_FILENAME);
    $seguro = preg_replace('/[^a-zA-Z0-9_-]/', '_', $base);
    $nombreImagen = $seguro . '_' . time() . '.' . $ext;

    $tipoCarpeta = ($tipo === 'civil') ? 'obrasciviles' : 'obrasindustriales';
    $rutaImagenes = __DIR__ . "/../../img/{$tipoCarpeta}/{$carpeta}/";
    if (!is_dir($rutaImagenes)) {
        mkdir($rutaImagenes, 0777, true);
    }

    if(!move_uploaded_file($imagen['tmp_name'], $rutaImagenes . $nombreImagen)) {
        $_SESSION['mensajeFeedback'] = "Ocurrió un error al subir la imagen.";
        $_SESSION['estadoFeedback'] = "error";
        $_SESSION['datos_viejos'] = $_POST;
        header("Location: ../index.php?seccion=obras-editar&id=" . $obra_id);
        exit;
    }
}

// 4. Editar la obra.
try {
    // 4a. Procesar eliminación de imagen principal (si se marcó)
    if (!empty($_POST['eliminar_portada'])) {
        $tipoCarpeta = ($tipo === 'civil') ? 'obrasciviles' : 'obrasindustriales';
        $rutaPortada = __DIR__ . "/../../img/{$tipoCarpeta}/{$carpeta}/" . $nombreImagen;
        if (!empty($nombreImagen) && file_exists($rutaPortada)) {
            unlink($rutaPortada);
        }
        $nombreImagen = '';
    }

    // 4b. Procesar eliminación de fotos de galería (si se marcaron)
    if (!empty($_POST['eliminar_galeria']) && is_array($_POST['eliminar_galeria'])) {
        $tipoCarpeta = ($tipo === 'civil') ? 'obrasciviles' : 'obrasindustriales';
        foreach ($_POST['eliminar_galeria'] as $archivoEliminar) {
            // Validar nombre (sin traversal)
            if (strpos($archivoEliminar, '/') !== false || strpos($archivoEliminar, '\\') !== false || strpos($archivoEliminar, '..') !== false) {
                continue;
            }
            $rutaArchivo = __DIR__ . "/../../img/{$tipoCarpeta}/{$carpeta}/" . $archivoEliminar;
            if (file_exists($rutaArchivo)) {
                unlink($rutaArchivo);
            }
        }
    }

    (new Obras)->editar($obra_id, [
        'carpeta'     => $carpeta,
        'titulo'      => $titulo,
        'ubicacion'   => $ubicacion,
        'explicacion' => $explicacion,
        'tipo'        => $tipo,
        'imagen'      => $nombreImagen,
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

    $_SESSION['mensajeFeedback'] = "La obra '<b>" . htmlspecialchars($titulo, ENT_QUOTES) . "</b>' se editó con éxito.";
    $_SESSION['estadoFeedback'] = "editar";
    header('Location: ../index.php?seccion=obras');
    exit;
} catch (\Throwable $th) {
    $_SESSION['mensajeFeedback'] = "ERROR - La obra '<b>" . htmlspecialchars($titulo, ENT_QUOTES) . "</b>' no pudo ser editada.<br><small>" . $th->getMessage() . "</small>";
    $_SESSION['datos_viejos'] = $_POST;
    $_SESSION['estadoFeedback'] = "error";
    header('Location: ../index.php?seccion=obras-editar&id=' . $obra_id);
    exit;
}
