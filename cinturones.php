<?php
/**
 * cinturones.php - Página de Cinturones de Taekwondo
 * MDKBoyacá - Muestra los cinturones con imágenes y descripciones desde la BD
 */

require_once __DIR__ . '/php/config.php';

// ============================================
// CONSULTA A LA BASE DE DATOS
// ============================================

// Obtener todos los cinturones de la tabla gestion_contenidos
$query = "SELECT subseccion, ruta_archivo, descripcion 
          FROM gestion_contenidos 
          WHERE seccion = 'cinturones'";
$result = mysqli_query($mysqli, $query);

// Crear array asociativo con los cinturones
$cinturones_db = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $cinturones_db[$row['subseccion']] = [
            'imagen' => $row['ruta_archivo'],
            'descripcion' => $row['descripcion']
        ];
    }
}

// ============================================
// CONFIGURACIÓN DE CINTURONES POR CATEGORÍA
// ============================================

$categorias = [
    'Principiantes' => [
        'descripcion' => 'Los primeros pasos en el camino del Taekwondo',
        'color' => '#4CAF50',
        'cinturones' => [
            ['id' => 'blanco', 'nombre' => 'Blanco', 'significado' => 'Pureza e inocencia'],
            ['id' => 'franja_amarilla', 'nombre' => 'Franja Amarilla', 'significado' => 'Transición hacia la luz'],
            ['id' => 'amarillo', 'nombre' => 'Amarillo', 'significado' => 'La tierra donde germina la semilla'],
            ['id' => 'franja_verde', 'nombre' => 'Franja Verde', 'significado' => 'Crecimiento inicial'],
            ['id' => 'verde', 'nombre' => 'Verde', 'significado' => 'La planta que crece hacia el cielo']
        ]
    ],
    'Avanzados' => [
        'descripcion' => 'El desarrollo técnico y mental del practicante',
        'color' => '#2196F3',
        'cinturones' => [
            
            ['id' => 'franja_azul', 'nombre' => 'Franja Azul', 'significado' => 'Preparación para alcanzar el cielo'],
            ['id' => 'azul', 'nombre' => 'Azul', 'significado' => 'El cielo hacia donde la planta madura'],
            ['id' => 'franja_roja', 'nombre' => 'Franja Rojo', 'significado' => 'Transición hacia el peligro'],
            ['id' => 'rojo', 'nombre' => 'Rojo', 'significado' => 'Peligro y advertencia de habilidad'],
            ['id' => 'franja_negra', 'nombre' => 'Franja Negra', 'significado' => 'Preparación para la maestría']
        ]
    ],
    'Superior' => [
        'descripcion' => 'El dominio y la maestría del arte marcial',
        'color' => '#212121',
        'cinturones' => [
            ['id' => 'negro', 'nombre' => 'Negro', 'significado' => 'Madurez y dominio del arte']
        ]
    ]
];

// Imagen por defecto si no hay imagen en la BD
$imagen_default = 'img/default.png';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cinturones de Taekwondo - MDKBoyacá</title>

    <!-- CSS -->
    <link rel="stylesheet" href="style.css">

    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- AOS Animations -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css">

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="img/favicon-96x96.png" sizes="96x96">

    <style>
        /* =========================================
           ESTILOS ESPECÍFICOS PARA CINTURONES
           ========================================= */

        .hero-cinturones {
            background: linear-gradient(135deg, rgba(13, 71, 161, 0.9), rgba(21, 101, 192, 0.8)),
                url('img/fondo_tkd.jpg') center/cover;
            min-height: 40vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: white;
            padding: 100px 20px 60px;
        }

        .hero-cinturones h1 {
            font-size: 2.5rem;
            margin-bottom: 15px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .hero-cinturones p {
            font-size: 1.2rem;
            opacity: 0.9;
        }

        /* Contenedor principal */
        .cinturones-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        /* Categoría */
        .categoria-seccion {
            margin-bottom: 60px;
        }

        .categoria-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .categoria-titulo {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 10px;
            display: inline-flex;
            align-items: center;
            gap: 12px;
        }

        .categoria-titulo i {
            font-size: 1.5rem;
        }

        .categoria-descripcion {
            color: #666;
            font-size: 1.1rem;
        }

        /* Grid de cinturones */
        .cinturones-grid {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 30px;
        }

        /* Card de cinturón */
        .cinturon-card {
            background: #fff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            width: 100%;
            max-width: 350px;
            /* Evita que se estire demasiado si está solo */
        }

        .cinturon-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.2);
        }

        .cinturon-imagen {
            width: 100%;
            height: 300px;
            /* Ajustado a petición del usuario */
            object-fit: cover;
            background: #f5f5f5;
        }

        .cinturon-info {
            padding: 20px;
            text-align: center;
        }

        .cinturon-nombre {
            font-size: 1.3rem;
            font-weight: bold;
            color: #333;
            margin-bottom: 8px;
        }

        .cinturon-significado {
            color: #666;
            font-size: 0.95rem;
            line-height: 1.5;
            font-style: italic;
        }

        .cinturon-descripcion-custom {
            margin-top: 12px;
            padding-top: 12px;
            border-top: 1px solid #eee;
            color: #1565c0;
            font-size: 0.9rem;
            font-weight: 500;
        }

        /* Indicador de color del cinturón */
        .cinturon-color-bar {
            height: 6px;
            width: 100%;
        }

        .color-blanco {
            background: linear-gradient(90deg, #f5f5f5, #fff);
            border-top: 1px solid #ddd;
        }

        .color-franja_amarilla {
            background: linear-gradient(90deg, #f5f5f5, #FDD835, #f5f5f5);
        }

        .color-amarillo {
            background: #FDD835;
        }

        .color-franja_verde {
            background: linear-gradient(90deg, #FDD835, #4CAF50, #FDD835);
        }

        .color-verde {
            background: #4CAF50;
        }

        .color-franja_azul {
            background: linear-gradient(90deg, #4CAF50, #2196F3, #4CAF50);
        }

        .color-azul {
            background: #2196F3;
        }

        .color-franja_roja {
            background: linear-gradient(90deg, #2196F3, #F44336, #2196F3);
        }

        .color-rojo {
            background: #F44336;
        }

        .color-franja_negra {
            background: linear-gradient(90deg, #F44336, #212121, #F44336);
        }

        .color-negro {
            background: #212121;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero-cinturones {
                min-height: 30vh;
                padding: 80px 15px 40px;
            }

            .hero-cinturones h1 {
                font-size: 1.8rem;
            }

            .hero-cinturones p {
                font-size: 1rem;
            }

            .categoria-titulo {
                font-size: 1.5rem;
            }

            .cinturones-grid {
                grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
                gap: 20px;
            }

            .cinturon-imagen {
                height: 160px;
            }

            .cinturon-info {
                padding: 15px;
            }

            .cinturon-nombre {
                font-size: 1.1rem;
            }
        }

        @media (max-width: 480px) {
            .cinturones-grid {
                grid-template-columns: 1fr;
            }

            .categoria-titulo {
                font-size: 1.3rem;
                flex-direction: column;
            }
        }
    </style>
</head>

<body>
    <!-- NAVBAR -->
    <nav class="navbar">
        <a href="index.php" class="logo">
            <img src="img/logo-mdk.jpg" alt="Logo MDK Boyacá" class="logo-img" />
        </a>
        <div>
            <a href="aprendizaje.html" class="volver-atras"
                style="color: #fff; text-decoration: none; font-weight: 600; transition: 0.3s;">Volver</a>
        </div>
    </nav>

    <!-- HERO -->
    <section class="hero-cinturones">
        <div class="hero-content">
            <h1><i class="fa-solid fa-ranking-star"></i> Cinturones de Taekwondo</h1>
            <p>El camino del practicante representado en colores</p>
        </div>
    </section>

    <!-- CONTENIDO PRINCIPAL -->
    <div class="cinturones-container">

        <?php foreach ($categorias as $categoria_nombre => $categoria): ?>
            <section class="categoria-seccion" data-aos="fade-up">

                <div class="categoria-header">
                    <h2 class="categoria-titulo" style="color: <?php echo $categoria['color']; ?>">
                        <?php if ($categoria_nombre === 'Principiantes'): ?>
                            <i class="fa-solid fa-seedling"></i>
                        <?php elseif ($categoria_nombre === 'Avanzados'): ?>
                            <i class="fa-solid fa-bolt"></i>
                        <?php else: ?>
                            <i class="fa-solid fa-crown"></i>
                        <?php endif; ?>
                        <?php echo $categoria_nombre; ?>
                    </h2>
                    <p class="categoria-descripcion"><?php echo $categoria['descripcion']; ?></p>
                </div>

                <div class="cinturones-grid">
                    <?php foreach ($categoria['cinturones'] as $cinturon): ?>
                        <?php
                        // Obtener datos de la BD o usar defaults
                        $id = $cinturon['id'];
                        $tiene_imagen = isset($cinturones_db[$id]) && !empty($cinturones_db[$id]['imagen']);
                        $imagen = $tiene_imagen ? $cinturones_db[$id]['imagen'] : $imagen_default;
                        $descripcion_custom = isset($cinturones_db[$id]) ? $cinturones_db[$id]['descripcion'] : '';
                        ?>
                        <div class="cinturon-card" data-aos="zoom-in" data-aos-delay="100">
                            <div class="cinturon-color-bar color-<?php echo $id; ?>"></div>
                            <img src="<?php echo htmlspecialchars($imagen); ?>"
                                alt="Cinturón <?php echo htmlspecialchars($cinturon['nombre']); ?>" class="cinturon-imagen"
                                onerror="this.src='<?php echo $imagen_default; ?>'">
                            <div class="cinturon-info">
                                <h3 class="cinturon-nombre"><?php echo htmlspecialchars($cinturon['nombre']); ?></h3>
                                <p class="cinturon-significado">"<?php echo htmlspecialchars($cinturon['significado']); ?>"</p>
                                <?php if (!empty($descripcion_custom)): ?>
                                    <p class="cinturon-descripcion-custom">
                                        <i class="fa-solid fa-quote-left"></i>
                                        <?php echo htmlspecialchars($descripcion_custom); ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

            </section>
        <?php endforeach; ?>

    </div>

    <!-- FOOTER -->
    <footer>
        <div class="footer-content">
            <div class="social-media">
                <p>
                    <i class="fab fa-instagram"></i>
                    <a href="https://www.instagram.com/mdkboyaca/" target="_blank" class="instagram-link">@mdkboyacá</a>
                </p>
            </div>
            <div class="contact-info">
                <p>
                    <i class="fab fa-whatsapp"></i>
                    <a href="https://wa.me/573124514555" target="_blank">+57 312 4514555</a>
                </p>
            </div>
            <div class="contact-info">
                <p>
                    <i class="fas fa-map-marker-alt"></i> Calle 21 No 11 - 66, Tunja
                </p>
            </div>
        </div>
        <p>MDKBoyacá</p>
        <p>Página Web by: Programador Juan Ramírez</p>
    </footer>

    <!-- WhatsApp Float -->
    <a href="https://wa.me/573124514555" class="whatsapp-float" target="_blank">
        <i class="fab fa-whatsapp"></i>
    </a>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 800,
            once: true
        });
    </script>
</body>

</html>