<?php
/**
 * kukkiwon.php - Página dinámica de videos Kukkiwon
 */

require __DIR__ . '/php/config.php';

$seccion = 'kukkiwon';
$titulo_pagina = 'Kukkiwon';
$titulo_seccion = 'Kukkiwon';

// Cargar videos desde BD
$videos = [];
$query = $mysqli->prepare("SELECT ruta_archivo, descripcion, subseccion FROM gestion_contenidos WHERE seccion = ? AND tipo_contenido = 'video' ORDER BY subseccion ASC");
$query->bind_param('s', $seccion);
$query->execute();
$result = $query->get_result();
while ($row = $result->fetch_assoc()) {
    $videos[] = $row;
}
$query->close();

// Fallback YouTube
$youtube_fallback = [
    'https://www.youtube.com/embed/uxpMI0VMqzs',
    'https://www.youtube.com/embed/xIkiOF79vX0',
    'https://www.youtube.com/embed/eKCYgEEFYM0'
];

$usar_bd = count($videos) > 0;
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= htmlspecialchars($titulo_pagina) ?> - MDKBoyacá-Taekwondo</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper/swiper-bundle.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/swiper/swiper-bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="js/script.js" defer></script>
    <link rel="icon" type="image/png" href="img/favicon-96x96.png" sizes="96x96">
    <style>
        .swiper-slide video {
            width: 100%;
            height: 100%;
            max-height: 450px;
            object-fit: contain;
            border-radius: 16px;
            background: #000;
        }

        .swiper-slide iframe {
            width: 100%;
            height: 100%;
            min-height: 350px;
            border: none;
            border-radius: 16px;
        }

        .video-placeholder {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 350px;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            border-radius: 16px;
            color: #fff;
            text-align: center;
            padding: 40px;
        }

        .video-placeholder i {
            font-size: 4rem;
            margin-bottom: 20px;
            color: #42a5f5;
        }

        .video-label {
            text-align: center;
            margin-top: 10px;
            font-weight: 600;
            color: #0d47a1;
        }
    </style>
</head>

<body>
    <nav class="navbar">
        <a href="index.php" class="logo"><img src="img/logo-mdk.jpg" alt="Logo MDK Boyacá" class="logo-img" /></a>
        <div><a href="aprendizaje.html" class="volver-atras"
                style="color: #fff; text-decoration: none; font-weight: 600;">Volver</a></div>
    </nav>

    <section class="hero-aprendizaje">
        <div class="overlay"></div>
        <div class="hero-content">
            <h1>Escuela MDKBoyacá-Taekwondo</h1>
            <p>Explora nuestros entrenamientos y contenidos en video</p>
        </div>
    </section>

    <section class="carrusel" data-aos="fade-up">
        <h2 class="taeguk-titulo" data-aos="fade-up"><?= htmlspecialchars($titulo_seccion) ?></h2>
        <?php if ($usar_bd): ?>
            <div class="swiper mySwiper" data-aos="fade-up">
                <div class="swiper-wrapper">
                    <?php foreach ($videos as $video): ?>
                        <div class="swiper-slide">
                            <video controls preload="metadata">
                                <source src="<?= htmlspecialchars($video['ruta_archivo']) ?>" type="video/mp4">
                            </video>
                            <?php if (!empty($video['descripcion'])): ?>
                                <p class="video-label"><?= htmlspecialchars($video['descripcion']) ?></p><?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="swiper-button-next"></div>
                <div class="swiper-button-prev"></div>
                <div class="swiper-pagination"></div>
            </div>
        <?php elseif (!empty($youtube_fallback)): ?>
            <div class="swiper mySwiper" data-aos="fade-up">
                <div class="swiper-wrapper">
                    <?php foreach ($youtube_fallback as $url): ?>
                        <div class="swiper-slide"><iframe src="<?= htmlspecialchars($url) ?>" allowfullscreen></iframe></div>
                    <?php endforeach; ?>
                </div>
                <div class="swiper-button-next"></div>
                <div class="swiper-button-prev"></div>
                <div class="swiper-pagination"></div>
            </div>
        <?php else: ?>
            <div class="video-placeholder"><i class="fas fa-video"></i>
                <h3>Próximamente</h3>
                <p>Videos disponibles pronto.</p>
            </div>
        <?php endif; ?>
    </section>

    <?php include './temp/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
    <script>AOS.init();</script>
</body>

</html>