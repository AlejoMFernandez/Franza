<?php
/**
 * obras-eliminar-masivo.php — Acción: eliminar múltiples obras
 * Recibe IDs separados por coma vía GET, elimina carpetas e imágenes,
 * y borra registros de DB (incluye obras_carrousel).
 */

use App\Autenticacion\Autenticacion;
use App\Modelos\Obras;
use App\Seguridad\Csrf;

require_once __DIR__ . '/../../bootstrap/init.php';

// Validar token CSRF (viene por GET en la URL)
Csrf::verificarOAbortar('../index.php?seccion=obras');

$autenticacion = new Autenticacion;
if(!$autenticacion->estaAutenticado() || $autenticacion->getUsuario()->noEsAdmin()) {
    $_SESSION['mensajeFeedback'] = "No tienes permisos para realizar esta acción.";
    $_SESSION['estadoFeedback'] = "error";
    header("Location: ../index.php?seccion=obras");
    exit;
}

$idsRaw = $_GET['ids'] ?? '';
$ids = array_filter(array_map('intval', explode(',', $idsRaw)));

if (empty($ids)) {
    $_SESSION['mensajeFeedback'] = "No se seleccionaron obras para eliminar.";
    $_SESSION['estadoFeedback'] = "error";
    header("Location: ../index.php?seccion=obras");
    exit;
}

function eliminarDirectorio(string $dir): void {
    if (!is_dir($dir)) {
        return;
    }
    $files = array_diff(scandir($dir), ['.', '..']);
    foreach ($files as $file) {
        (is_dir("$dir/$file")) ? eliminarDirectorio("$dir/$file") : unlink("$dir/$file");
    }
    rmdir($dir);
}

$obrasModel = new Obras;
$eliminadas = 0;
$errores = 0;

foreach ($ids as $id) {
    try {
        $obra = $obrasModel->porId($id);
        if (!$obra) {
            $errores++;
            continue;
        }

        $tipo = $obra->getTipo();
        $carpetaObra = $obra->getCarpeta();

        // Eliminar carpeta de imágenes
        if ($carpetaObra) {
            $tipoCarpeta = ($tipo === 'civil') ? 'obrasciviles' : 'obrasindustriales';
            $rutaCarpeta = __DIR__ . '/../../img/' . $tipoCarpeta . '/' . $carpetaObra;
            eliminarDirectorio($rutaCarpeta);
        }

        // Eliminar de la base de datos
        $obrasModel->eliminar($id);
        $eliminadas++;

    } catch (\Throwable $th) {
        error_log("Error eliminando obra ID $id: " . $th->getMessage());
        $errores++;
    }
}

if ($errores === 0) {
    $_SESSION['mensajeFeedback'] = "Se eliminaron <b>$eliminadas obra" . ($eliminadas > 1 ? 's' : '') . "</b> correctamente.";
    $_SESSION['estadoFeedback'] = "eliminar";
} else if ($eliminadas > 0) {
    $_SESSION['mensajeFeedback'] = "Se eliminaron <b>$eliminadas obra" . ($eliminadas > 1 ? 's' : '') . "</b>. $errores no pudieron ser eliminadas.";
    $_SESSION['estadoFeedback'] = "error";
} else {
    $_SESSION['mensajeFeedback'] = "ERROR - No se pudo eliminar ninguna obra.";
    $_SESSION['estadoFeedback'] = "error";
}

header('Location: ../index.php?seccion=obras');
exit;
