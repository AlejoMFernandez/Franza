<?php
/**
 * obras-eliminar.php — Acción: eliminar una obra individual
 * Valida CSRF y autenticación, elimina carpeta de imágenes y registro en DB.
 */

use App\Autenticacion\Autenticacion;
use App\Modelos\Obras;
use App\Seguridad\Csrf;

require_once __DIR__ . '/../../bootstrap/init.php';

// Validar token CSRF (viene por GET en la URL)
Csrf::verificarOAbortar('../index.php?seccion=obras');

$autenticacion = new Autenticacion;
// Corregimos la forma de verificar el rol del usuario.
// Asumimos que el rol de admin tiene el id 1 y el método se llama getRol_fk().
if(!$autenticacion->estaAutenticado() || $autenticacion->getUsuario()->noEsAdmin()) {
    $_SESSION['mensajeFeedback'] = "No tienes permisos para realizar esta acción.";
    $_SESSION['estadoFeedback'] = "error";
    header("Location: ../index.php?seccion=obras");
    exit;
}

$id = $_GET['id'];
$obra = (new Obras)->porId($id);

// Función auxiliar para eliminar un directorio y su contenido.
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

try {
    if (!$obra) {
        throw new Exception("No se encontró la obra que intentas eliminar.");
    }

    $tipo = $obra->getTipo();
    $tituloObra = $obra->getTitulo();
    $imagenObra = $obra->getImagen();
    $carpetaObra = $obra->getCarpeta();

    // 2. Eliminar la carpeta de la obra y su contenido.
    if ($carpetaObra) {
        $tipoCarpeta = ($tipo === 'civil') ? 'obrasciviles' : 'obrasindustriales';
        $rutaCarpeta = __DIR__ . '/../../img/' . $tipoCarpeta . '/' . $carpetaObra;
        eliminarDirectorio($rutaCarpeta);
    }

    // 3. Eliminar la obra de la base de datos.
    (new Obras)->eliminar($id);

    $_SESSION['mensajeFeedback'] = "La obra '<b>" . $tituloObra . "</b>' se eliminó correctamente.";
    $_SESSION['estadoFeedback'] = "eliminar";

} catch (\Throwable $th) {
    $_SESSION['mensajeFeedback'] = "ERROR - La obra no pudo ser eliminada.";
    $_SESSION['mensajeFeedback'] .= "<br><br>Detalle técnico: " . $th->getMessage();
    error_log($th->getMessage());
    $_SESSION['estadoFeedback'] = "error";
}

header('Location: ../index.php?seccion=obras');
exit;