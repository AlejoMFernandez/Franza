<?php
/**
 * franza.php — Contenido curado del sitio (rediseño 2026).
 * Acá se editan los textos y las fotos de las secciones especiales de la home
 * (hero, antes/después, paso a paso) y los datos extra de cada obra que la base
 * todavía no guarda (rubro, etiquetas, pasos de trabajo).
 * Los datos extra se buscan por la CARPETA de la obra.
 */
return [

    'contacto' => [
        'whatsapp'  => '5491136932502',
        'email'     => 'p.allegranza@franza.com.ar',
        'instagram' => 'https://www.instagram.com/franzaconstrucciones',
        'linkedin'  => 'https://www.linkedin.com/company/franza-construcciones/',
        'zona'      => 'CABA y Gran Buenos Aires',
    ],

    // Hero "Rayos X": la foto de después, y debajo la de antes (mismo encuadre).
    'hero' => [
        'antes'   => 'img/obracivilantess.jpg',
        'despues' => 'img/obrasciviles.webp',
        'obra_id' => 73,
        'rotulo'  => 'Reforma completa · Tres de Febrero',
    ],

    // Sección "Antes y después" de la home.
    'antes_despues' => [
        [
            'obra_id'  => 73,
            'nombre'   => 'Frente · Tres de Febrero',
            'tipo'     => 'Civil',
            'lugar'    => 'Tres de Febrero',
            'antes'    => 'img/obracivilantess.jpg',
            'despues'  => 'img/obrasciviles.webp',
            'etiquetas'=> ['Antes', 'Después'],
            'texto'    => 'Frente revocado y pintado, portón y rejas renovados.',
        ],
        [
            'obra_id'  => null,
            'nombre'   => 'Depósito · Planta industrial',
            'tipo'     => 'Industrial',
            'lugar'    => 'Planta industrial',
            'antes'    => 'img/obraindustrialantes.jpg',
            'despues'  => 'img/obrasindustriales.webp',
            'etiquetas'=> ['Antes', 'Después'],
            'texto'    => 'Recuperación de piso y demarcación de sendas peatonales y áreas de estiba.',
        ],
        [
            'obra_id'  => 72,
            'nombre'   => 'Fachada · Bmé. Mitre',
            'tipo'     => 'Civil',
            'lugar'    => 'Bmé. Mitre',
            'antes'    => 'img/obrasciviles/restauraci-on-de-fachada/3_1771019414_2.jpg',
            'despues'  => 'img/obrasciviles/restauraci-on-de-fachada/1_1771019414_0.jpg',
            'etiquetas'=> ['Durante', 'Terminada'],
            'texto'    => 'Hidrolavado a baja presión, sellado de grietas, restauración de ornamentos, reparación de hierros expuestos y 4 manos de pintura para muros.',
        ],
    ],

    // Sección "Paso a paso" (cinta métrica).
    'paso_a_paso' => [
        'obra_id' => 70,
        'titulo'  => 'Ampliación y quincho · Zona Norte',
        'etapas'  => [
            ['titulo' => 'Mampostería',  'foto' => 'img/obrasciviles/obra-particular/5_1771019309_4.jpg', 'texto' => 'Muros nuevos sobre la platea, con vigas de encadenado.'],
            ['titulo' => 'Estructura',   'foto' => 'img/obrasciviles/obra-particular/3_1771019309_2.jpg', 'texto' => 'Refuerzos para unir la cocina existente con el quincho.'],
            ['titulo' => 'Techo',        'foto' => 'img/obrasciviles/obra-particular/4_1771019309_3.jpg', 'texto' => 'Estructura del techo a dos aguas de tejas francesas.'],
            ['titulo' => 'Pisos',        'foto' => 'img/obrasciviles/obra-particular/2_1771019309_1.jpg', 'texto' => 'Pisos y aberturas nuevas hacia el jardín.'],
            ['titulo' => 'Parrilla',     'foto' => 'img/obrasciviles/obra-particular/1_1771019309_0.jpg', 'texto' => 'Parrilla completa y ventanas de paño fijo en el techo.'],
            ['titulo' => 'Entrega',      'foto' => 'img/obrasciviles/obra-particular/JorgelinaZonaNorte_1771019309.jpg', 'texto' => 'Cocina ampliada, baño nuevo y quincho integrado.'],
        ],
    ],

    // Obras destacadas de la home (por obra_id). La primera va grande.
    'destacadas' => [72, 70, 73, 69, 71],

    // Rubros de cada tipo, para el selector de la home.
    'rubros_tipo' => [
        'civil'      => 'Fachadas · azoteas · ampliaciones · reformas completas',
        'industrial' => 'Pisos · demarcación de plantas',
    ],

    // Datos extra por carpeta de obra.
    'obras' => [
        'reforma-de-azotea' => [
            'rubro' => 'Azoteas y terrazas', 'etiquetas' => 'Impermeabilización · juntas · pintura',
        ],
        'construccion-completa-de-hogar' => [
            'rubro' => 'Obra nueva', 'etiquetas' => 'Vivienda nueva · cocina, living y dormitorios',
            'badge' => 'Proyecto · Render 3D', 'render' => true,
        ],
        'reforma-complete-de-balcon' => [
            'rubro' => 'Azoteas y terrazas', 'etiquetas' => 'Filtraciones · sello hidrófugo · paredes',
        ],
        'obra-particular' => [
            'rubro' => 'Ampliaciones', 'etiquetas' => 'Platea · techo de tejas · parrilla · baño',
            'badge' => 'Paso a paso',
        ],
        'ampliaci-on-y-reforma-de-vivienda-unifamiliar' => [
            'rubro' => 'Ampliaciones', 'etiquetas' => 'Hormigón visto · gas · electricidad · Velux',
        ],
        'restauraci-on-de-fachada' => [
            'rubro' => 'Fachadas', 'etiquetas' => 'Hidrolavado · ornamentos · hierros · pintura',
            'badge' => 'Durante / terminada',
            'problema' => 'Un frente histórico con grietas tomadas por vegetación, ornamentos dañados y hierros de la estructura oxidados por el agua.',
            'pasos' => [
                'Hidrolavado a baja presión de todo el frente.',
                'Retiro de vegetación en las grietas y sellado.',
                'Restauración de los ornamentos de la fachada.',
                'Reparación de las partes afectadas por la corrosión de los hierros.',
                'Cuatro manos de pintura para muros de primera calidad, y pintura de los frentes de los locales en planta baja.',
            ],
        ],
        'reforma-completa-de-hogar' => [
            'rubro' => 'Reformas', 'etiquetas' => 'Frente · interiores · pintura',
            'badge' => 'Antes / después',
        ],
    ],
];
