<?php
/**
 * Contenido del sitio: áreas de negocio, marcas, proyectos y respaldo.
 * Editar aquí actualiza el home, el menú y las páginas internas.
 */

$AREAS = [
    'refrigeracion' => [
        'nombre'    => 'Refrigeración',
        'menu'      => 'Refrigeración',
        'bajada'    => 'Cámaras frigoríficas, túneles de congelado y servicio técnico industrial.',
        'intro'     => 'Diseñamos, construimos y mantenemos sistemas de frío para industria, comercio y acuicultura, con respaldo técnico permanente y repuestos originales.',
        'slot'      => 'refrigeracion',
        'icono'     => 'copo',
        'meta'      => ['Baja y media T°', 'Industrial'],
        'servicios' => [
            'Servicio técnico correctivo y preventivo',
            'Construcción de cámaras frigoríficas de baja y media temperatura',
            'Túneles de congelado',
            'Venta de repuestos y máquinas de hielo',
            'Puesta en marcha y programas de mantención',
        ],
        'para'      => ['Salmoneras e industria acuícola', 'Supermercados y comercio', 'Restaurantes y hoteles', 'Plantas de proceso'],
    ],
    'climatizacion' => [
        'nombre'    => 'Climatización',
        'menu'      => 'Climatización',
        'bajada'    => 'Aire acondicionado y bombas de calor para proyectos domiciliarios, comerciales e industriales.',
        'intro'     => 'Proyectos de climatización completos: cálculo, suministro, montaje y mantención de equipos Split, ducto, cassette, VRF y bombas de calor.',
        'slot'      => 'climatizacion',
        'icono'     => 'aire',
        'meta'      => ['Split · VRF', 'Bombas de calor'],
        'servicios' => [
            'Servicio técnico correctivo y preventivo',
            'Venta y montaje de equipos Split muro, Split ducto y cassette',
            'Sistemas VRF y bombas de calor',
            'Proyectos de climatización domiciliaria, comercial e industrial',
            'Planes de mantención programada',
        ],
        'para'      => ['Clínicas y centros de salud', 'Oficinas y edificios corporativos', 'Retail y centros comerciales', 'Viviendas particulares'],
    ],
    'ventilacion' => [
        'nombre'    => 'Ventilación y extracción',
        'menu'      => 'Ventilación',
        'bajada'    => 'Tratamiento de aire, extracción y renovación para espacios comerciales e industriales.',
        'intro'     => 'Soluciones de ventilación y extracción que aseguran calidad de aire, control de humedad y cumplimiento normativo en cocinas, plantas y recintos cerrados.',
        'slot'      => 'ventilacion',
        'icono'     => 'flujo',
        'meta'      => ['Tratamiento de aire', 'Comercial e industrial'],
        'servicios' => [
            'Proyectos de ventilación comercial e industrial',
            'Extracción de cocinas y áreas de proceso',
            'Redes de ductos y difusión de aire',
            'Renovación y tratamiento de aire',
            'Mantención de equipos de extracción',
        ],
        'para'      => ['Restaurantes y cocinas industriales', 'Plantas productivas', 'Bodegas y talleres', 'Instituciones públicas'],
    ],
    'reefer' => [
        'nombre'    => 'Arriendo Reefer',
        'menu'      => 'Arriendo Reefer',
        'bajada'    => 'Contenedores refrigerados de +30 °C a −29 °C, con arriendo y logística.',
        'intro'     => 'Arriendo de contenedores reefer con control de temperatura, apoyo logístico y soporte técnico durante toda la operación.',
        'slot'      => 'reefer',
        'icono'     => 'contenedor',
        'meta'      => ['+30 °C / −29 °C', 'Arriendo y logística'],
        'servicios' => [
            'Arriendo de contenedores refrigerados',
            'Rango de temperatura desde +30 °C hasta −29 °C',
            'Logística y traslado de contenedores',
            'Monitoreo y soporte técnico en terreno',
            'Soluciones temporales para peaks de producción',
        ],
        'para'      => ['Industria acuícola y pesquera', 'Agroindustria', 'Eventos y contingencias', 'Operadores logísticos'],
    ],
];

$MARCAS = [
    'Refrigeración' => ['Hispania', 'Danfoss', 'Bitzer', 'Dorin'],
    'Climatización' => ['Midea', 'Hisense', 'LG', 'Samsung', 'Trane'],
    'Ventilación'   => ['Sodeca', 'Soler & Palau'],
];

$PROYECTOS = [
    [
        'titulo'   => 'Cámaras de congelado',
        'cliente'  => 'Hotel Bellavista',
        'detalle'  => 'Instalación y puesta en marcha de cámaras de congelado.',
        'lugar'    => 'Puerto Varas',
        'area'     => 'Refrigeración',
        'slot'      => 'proyecto-1',
    ],
    [
        'titulo'   => 'Climatización clínica',
        'cliente'  => 'Clínica Puerto Varas',
        'detalle'  => 'Instalación de equipos de aire acondicionado.',
        'lugar'    => 'Puerto Varas',
        'area'     => 'Climatización',
        'slot'      => 'proyecto-2',
    ],
    [
        'titulo'   => 'Proyecto integral',
        'cliente'  => 'Restaurant La Forja',
        'detalle'  => 'Cámara de congelado, aire acondicionado y extracción de aire.',
        'lugar'    => 'Puerto Varas',
        'area'     => 'Refrigeración · Climatización · Ventilación',
        'slot'      => 'proyecto-3',
    ],
];

$RESPALDO = [
    ['valor' => '10',    'label' => 'años de experiencia',      'detalle' => 'Operando desde el sur de Chile.'],
    ['valor' => '4',     'label' => 'áreas especializadas',      'detalle' => 'Frío, clima, aire y reefer.'],
    ['valor' => '4',     'label' => 'regiones con cobertura',    'detalle' => 'X · XI · XII · XIV.'],
    ['valor' => '24/7',  'label' => 'servicio de emergencia',    'detalle' => 'Respuesta ante fallas críticas.'],
];

$VALORES = [
    ['t' => 'Profesionalismo', 'd' => 'Equipo técnico especializado y procedimientos claros en cada faena.'],
    ['t' => 'Experiencia',     'd' => 'Una década resolviendo proyectos de frío y clima en el sur de Chile.'],
    ['t' => 'Confianza',       'd' => 'Garantía por fabricante o por contrato en todos nuestros trabajos.'],
    ['t' => 'Innovación',      'd' => 'Tecnología y marcas líderes para sistemas eficientes y seguros.'],
];

/* Menú principal del sitio */
$MENU = [
    ['p' => 'home',       'label' => 'Inicio'],
    ['p' => 'area',       'label' => 'Refrigeración',   'a' => 'refrigeracion'],
    ['p' => 'area',       'label' => 'Climatización',   'a' => 'climatizacion'],
    ['p' => 'area',       'label' => 'Ventilación',     'a' => 'ventilacion'],
    ['p' => 'area',       'label' => 'Arriendo Reefer', 'a' => 'reefer'],
    ['p' => 'nosotros',   'label' => 'Nosotros'],
    ['p' => 'contacto',   'label' => 'Contacto'],
];
