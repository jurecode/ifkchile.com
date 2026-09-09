<?php
/**
 * Contenido del sitio: respaldo, valores, método de trabajo, proyectos y marcas.
 * Sólo lo cargan las páginas del sitio (la fachada no lo necesita).
 */
declare(strict_types=1);

/* Los cuatro números que resumen a la empresa. */
$RESPALDO = [
    ['valor' => '10',   'label' => 'años de experiencia',   'detalle' => 'Operando desde el sur de Chile.'],
    ['valor' => '4',    'label' => 'áreas especializadas',  'detalle' => 'Frío, clima, aire y reefer.'],
    ['valor' => '4',    'label' => 'regiones con cobertura','detalle' => 'X · XI · XII · XIV.'],
    ['valor' => '24/7', 'label' => 'servicio de emergencia','detalle' => 'Respuesta ante fallas críticas.'],
];

$VALORES = [
    ['t' => 'Profesionalismo', 'd' => 'Equipo técnico especializado y procedimientos claros en cada faena.'],
    ['t' => 'Experiencia',     'd' => 'Una década resolviendo proyectos de frío y clima en el sur de Chile.'],
    ['t' => 'Confianza',       'd' => 'Garantía por fabricante o por contrato en todos nuestros trabajos.'],
    ['t' => 'Innovación',      'd' => 'Tecnología y marcas líderes para sistemas eficientes y seguros.'],
];

/* Cómo trabaja IFK, de la primera llamada a la mantención. */
$METODO = [
    ['n' => '01', 't' => 'Visita y diagnóstico', 'd' => 'Vamos a terreno, medimos y entendemos qué necesita el recinto.'],
    ['n' => '02', 't' => 'Proyecto y cotización','d' => 'Propuesta técnica con equipos, plazos y valores claros.'],
    ['n' => '03', 't' => 'Montaje y puesta en marcha', 'd' => 'Instalación con personal propio y entrega funcionando.'],
    ['n' => '04', 't' => 'Mantención y respaldo', 'd' => 'Planes preventivos, repuestos originales y emergencias.'],
];

/* Proyectos realizados. Se muestran públicamente con permiso del cliente. */
$PROYECTOS = [
    [
        'titulo'  => 'Cámaras de congelado',
        'cliente' => 'Hotel Bellavista',
        'detalle' => 'Instalación y puesta en marcha de cámaras de congelado.',
        'lugar'   => 'Puerto Varas',
        'area'    => 'Refrigeración',
        'foto'    => 'img/proyectos/proyecto-1.jpg',
    ],
    [
        'titulo'  => 'Climatización de clínica',
        'cliente' => 'Clínica Puerto Varas',
        'detalle' => 'Instalación de equipos de aire acondicionado.',
        'lugar'   => 'Puerto Varas',
        'area'    => 'Climatización',
        'foto'    => 'img/proyectos/proyecto-2.jpg',
    ],
    [
        'titulo'  => 'Proyecto integral',
        'cliente' => 'Restaurant La Forja',
        'detalle' => 'Cámara de congelado, aire acondicionado y extracción de aire.',
        'lugar'   => 'Puerto Varas',
        'area'    => 'Refrigeración · Climatización · Ventilación',
        'foto'    => 'img/proyectos/proyecto-3.jpg',
    ],
];

/* Marcas con las que trabaja IFK, por rubro. */
$MARCAS = [
    'Refrigeración'           => ['Hispania', 'Danfoss', 'Bitzer', 'Dorin'],
    'Climatización'           => ['Midea', 'Hisense', 'LG', 'Samsung', 'Trane'],
    'Ventilación y extracción'=> ['Sodeca', 'Soler & Palau'],
    'Arriendo Reefer'         => ['Carrier', 'Thermo King'],
];

/* A quién atendemos. */
$CLIENTES = [
    'Salmoneras e industria acuícola', 'Plantas de proceso', 'Supermercados y comercio',
    'Restaurantes y hoteles', 'Clínicas y centros de salud', 'Oficinas y edificios',
    'Centros comerciales', 'Constructoras', 'Instituciones públicas', 'Viviendas particulares',
];
