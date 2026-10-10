<?php
/**
 * sitemap.php — Mapa del sitio para Google, armado con las obras de la base.
 */
use App\Modelos\Obras;

require_once __DIR__ . '/bootstrap/init.php';
require_once __DIR__ . '/includes/helpers.php';

header('Content-Type: application/xml; charset=utf-8');

$urls = [
    ['loc' => fz_url_absoluta(''), 'prioridad' => '1.0'],
    ['loc' => fz_url_absoluta('index.php?seccion=obrasciviles'), 'prioridad' => '0.8'],
    ['loc' => fz_url_absoluta('index.php?seccion=obrasindustriales'), 'prioridad' => '0.8'],
];
foreach ((new Obras())->todas() as $obra) {
    $urls[] = ['loc' => fz_url_absoluta(fz_url_obra($obra->getObraId())), 'prioridad' => '0.6'];
}

echo '<?xml version="1.0" encoding="UTF-8"?>', "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">', "\n";
foreach ($urls as $u) {
    echo '  <url><loc>', htmlspecialchars($u['loc'], ENT_XML1), '</loc><priority>', $u['prioridad'], '</priority></url>', "\n";
}
echo '</urlset>', "\n";
