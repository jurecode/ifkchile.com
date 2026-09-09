<?php
/**
 * IFK — Inversiones Friomak SpA
 * Datos de la empresa y de sus cuatro áreas. Fuente única: si cambia un
 * teléfono, un correo o el texto de un servicio, se cambia aquí y cambia
 * en todo el sitio.
 */
declare(strict_types=1);

$SITE = [
    'marca'        => 'IFK',
    'razon_social' => 'Inversiones Friomak SpA',
    'nombre_largo' => 'IFK · Inversiones Friomak',
    'claim'        => 'Refrigeración, climatización, ventilación y arriendo reefer',
    'anios'        => 10,
    'dominio'      => 'https://ifkchile.com',
    'email'        => 'contacto@ifkchile.com',   // pendiente de confirmar con el cliente
    'telefono'     => '+56 9 8546 4643',
    'whatsapp'     => '56985464643',             // sólo dígitos, formato internacional
    'whatsapp_msg' => '',                        // el cliente pidió sin mensaje automático
    'direccion'    => 'Camino a Pargua Km 9, Lote 23, Puerto Montt',
    'horario'      => 'Lunes a viernes, 09:00 a 18:00 hrs',
    'emergencia'   => 'Servicio de emergencia',
    'cobertura'    => 'Regiones X · XI · XII · XIV',
    /* Cuando existan, se descomentan y aparecen solas en la fachada y el pie. */
    'redes'        => [
        // 'instagram' => 'https://instagram.com/…',
        // 'linkedin'  => 'https://linkedin.com/company/…',
        // 'facebook'  => 'https://facebook.com/…',
    ],
];

/* Las cuatro áreas del negocio. El orden es el del menú y el del sitio. */
$AREAS = [
    'refrigeracion' => [
        'nombre'    => 'Refrigeración',
        'menu'      => 'Refrigeración',
        'nota'      => 'Baja y media temperatura',
        'bajada'    => 'Cámaras frigoríficas, túneles de congelado y servicio técnico industrial.',
        'intro'     => 'Diseñamos, construimos y mantenemos sistemas de frío para industria, '
                     . 'comercio y acuicultura, con respaldo técnico permanente y repuestos originales.',
        'foto'      => 'img/refrigeracion.jpg',
        'meta'      => ['Baja y media T°', 'Industrial'],
        'servicios' => [
            'Servicio técnico correctivo y preventivo',
            'Construcción de cámaras frigoríficas de baja y media temperatura',
            'Túneles de congelado',
            'Venta de repuestos y máquinas de hielo',
            'Puesta en marcha y planes de mantención',
        ],
        'para'      => ['Salmoneras e industria acuícola', 'Supermercados y comercio',
                        'Restaurantes y hoteles', 'Plantas de proceso'],
        'marcas'    => ['Hispania', 'Danfoss', 'Bitzer', 'Dorin'],
    ],
    'climatizacion' => [
        'nombre'    => 'Climatización',
        'menu'      => 'Climatización',
        'nota'      => 'Split · Ducto · VRF',
        'bajada'    => 'Aire acondicionado y bombas de calor: domiciliario, comercial e industrial.',
        'intro'     => 'Proyectos de climatización completos: cálculo, suministro, montaje y mantención '
                     . 'de equipos Split, ducto, cassette, VRF y bombas de calor.',
        'foto'      => 'img/climatizacion.jpg',
        'meta'      => ['Split · VRF', 'Bombas de calor'],
        'servicios' => [
            'Servicio técnico correctivo y preventivo',
            'Venta y montaje de equipos Split muro, Split ducto y cassette',
            'Sistemas VRF y bombas de calor',
            'Proyectos domiciliarios, comerciales e industriales',
            'Planes de mantención programada',
        ],
        'para'      => ['Clínicas y centros de salud', 'Oficinas y edificios corporativos',
                        'Retail y centros comerciales', 'Viviendas particulares'],
        'marcas'    => ['Midea', 'Hisense', 'LG', 'Samsung', 'Trane'],
    ],
    'ventilacion' => [
        'nombre'    => 'Ventilación y extracción',
        'menu'      => 'Ventilación',
        'nota'      => 'Tratamiento de aire',
        'bajada'    => 'Tratamiento de aire, extracción y renovación en recintos comerciales e industriales.',
        'intro'     => 'Soluciones de ventilación y extracción que aseguran calidad de aire, control de '
                     . 'humedad y cumplimiento normativo en cocinas, plantas y recintos cerrados.',
        'foto'      => 'img/ventilacion.jpg',
        'meta'      => ['Tratamiento de aire', 'Comercial e industrial'],
        'servicios' => [
            'Proyectos de ventilación comercial e industrial',
            'Extracción de cocinas y áreas de proceso',
            'Redes de ductos y difusión de aire',
            'Renovación y tratamiento de aire',
            'Mantención de equipos de extracción',
        ],
        'para'      => ['Restaurantes y cocinas industriales', 'Plantas productivas',
                        'Bodegas y talleres', 'Instituciones públicas'],
        'marcas'    => ['Sodeca', 'Soler & Palau'],
    ],
    'reefer' => [
        'nombre'    => 'Arriendo Reefer',
        'menu'      => 'Arriendo Reefer',
        'nota'      => 'Arriendo y logística',
        'bajada'    => 'Contenedores refrigerados de +30 °C a −29 °C, con arriendo y logística.',
        'intro'     => 'Arriendo de contenedores reefer con control de temperatura, apoyo logístico y '
                     . 'soporte técnico durante toda la operación.',
        'foto'      => 'img/reefer.jpg',
        'meta'      => ['+30 °C / −29 °C', 'Arriendo y logística'],
        'servicios' => [
            'Arriendo de contenedores refrigerados',
            'Rango de temperatura desde +30 °C hasta −29 °C',
            'Logística y traslado de contenedores',
            'Monitoreo y soporte técnico en terreno',
            'Soluciones temporales para peaks de producción',
        ],
        'para'      => ['Industria acuícola y pesquera', 'Agroindustria',
                        'Eventos y contingencias', 'Operadores logísticos'],
        'marcas'    => ['Carrier', 'Thermo King'],
    ],
];
