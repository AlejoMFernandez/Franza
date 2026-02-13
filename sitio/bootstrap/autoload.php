<?php
/**
 * autoload.php — Autoloader de clases PSR-4
 * Mapea el namespace App\ a sitio/classes/
 * Ej: App\Modelos\Obras → sitio/classes/Modelos/Obras.php
 */
spl_autoload_register(function(string $className) {
    if(str_starts_with($className, 'App\\')) {
        $className = substr($className, 4);
    }
    $className = str_replace('\\', DIRECTORY_SEPARATOR, $className);
    $classPath = __DIR__ . '/../classes/' . $className . '.php';
    if(file_exists($classPath)) {
        require_once $classPath;
    }
});