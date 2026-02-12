<?php
require __DIR__ . '/php/config.php';

// Consulta productos con su imagen principal en PHP procedural
$sql = "
  SELECT 
    p.id_producto, 
    p.nombre, 
    p.descripcion, 
    p.precio, 
    p.stock,
    (
      SELECT pi.URL
      FROM producto_imagen pi
      WHERE pi.id_producto = p.id_producto
      ORDER BY pi.id_imagen ASC 
      LIMIT 1
    ) AS imagen
  FROM productos p
  ORDER BY p.id_producto DESC
";

$res = mysqli_query($mysqli, $sql);
$productos = $res ? mysqli_fetch_all($res, MYSQLI_ASSOC) : [];
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tienda - MDK Boyacá</title>
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="tienda.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="icon" type="image/png" href="img/favicon-96x96.png" sizes="96x96">
</head>

<body>

  <!-- NAVBAR TIENDA -->
  <nav class="navbar_tienda">
    <!-- Logo -->
    <a href="index.php" class="logo">
      <img src="img/logo-mdk.jpg" alt="Logo MDK Boyacá" class="logo-img" />
    </a>

    <!-- Menú colapsable -->
    <ul class="nav-links">
      <li><a href="index.php">Volver</a></li>
    </ul>

    <!-- Botón hamburguesa -->
    <div class="hamburger">
      <span></span>
      <span></span>
      <span></span>
    </div>
  </nav>

  <!-- HERO -->
  <section class="hero-cinturones">
    <div class="overlay"></div>
    <div class="hero-content">
      <h1>Tienda MDK Boyacá</h1>
      <p>Uniformes, accesorios y mucho más</p>
    </div>
  </section>

  <!-- SECCIÓN DE PRODUCTOS -->
  <section class="tienda">
    <h2 class="tienda-titulo text-center mb-4">Nuestros Productos</h2>
    <div class="container">
      <div class="row justify-content-center">
        <?php if (count($productos) > 0): ?>
          <?php foreach ($productos as $p): ?>
            <div class="col-md-4 col-sm-6 mb-4">
              <div class="card producto-card shadow-sm border-0">
                <div class="ratio ratio-1x1">
                  <img src="<?= htmlspecialchars($p['imagen'] ?: 'img/producto-default.jpg') ?>" class="card-img-top"
                    alt="<?= htmlspecialchars($p['nombre']) ?>" style="object-fit: cover;">
                </div>
                <div class="card-body text-center">
                  <h5 class="card-title"><?= htmlspecialchars($p['nombre']) ?></h5>
                  <p class="card-text text-muted"><?= nl2br(htmlspecialchars($p['descripcion'])) ?></p>
                  <p class="precio fw-bold text-primary fs-5">
                    $<?= number_format($p['precio'], 0, ',', '.') ?>
                  </p>
                  <?php if ((int) $p['stock'] > 0): ?>
                    <button class="btn btn-success w-100"
                      onclick="window.open('https://wa.me/573124514555?text=Hola! Estoy interesado en <?= urlencode($p['nombre']) ?> ($<?= number_format($p['precio'], 0, ',', '.') ?>)', '_blank')">
                      <i class="fab fa-whatsapp"></i> Comprar
                    </button>
                  <?php else: ?>
                    <button class="btn btn-secondary w-100" disabled>Agotado</button>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="col-12 text-center">
            <p class="text-muted fs-5">No hay productos disponibles en este momento.</p>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <!-- FOOTER -->
  <footer class="text-center mt-5">
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
        <p><i class="fas fa-map-marker-alt"></i> Calle 21 No 11 - 66, Tunja</p>
      </div>
    </div>
    <p>MDK Boyacá</p>
    <p>Página Web by: Programador Juan Ramírez</p>
  </footer>

  <!-- Script hamburguesa -->
  <script>
    const hamburger = document.querySelector('.navbar_tienda .hamburger');
    const navLinks = document.querySelector('.navbar_tienda .nav-links');

    if (hamburger && navLinks) {
      hamburger.addEventListener('click', () => {
        hamburger.classList.toggle('active');
        navLinks.classList.toggle('open');
      });
    }
  </script>

</body>

</html>