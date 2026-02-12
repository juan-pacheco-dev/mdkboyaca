<?php
// Verificación de seguridad para la función esc()
if (!function_exists('esc')) {
    function esc($str) {
        return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Admin</title>
    <!-- Asegúrate de que la ruta al CSS sea correcta desde donde se incluye -->
    <link rel="stylesheet" href="../style.css">
</head>
<body>

<section>

<style>
section .navbar {
    position: fixed !important;
    top: 0;
    left: 0;
    width: 100%;
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(6px);
    padding: 10px 20px !important;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    z-index: 999;
}

.hamburger span {
  background-color: #000; /* negro */
}
</style>

<nav class="navbar">

<!-- Logo -->
  <a href="index.php" class="logo" data-aos="fade-up">
    <img src="img/logo-mdk.jpg" alt="Logo MDK Boyacá" class="logo-img">
  </a>
  <?php if (session_status() === PHP_SESSION_NONE) { session_start(); } ?>

  <!-- Menu colapsable (usa la MISMA clase que index: nav-links) -->
  <ul class="nav-links" data-aos="fade-up">
    <?php if (isset($_SESSION['user'])): ?>
      <li>
        <span id="admin-username-display" style="font-weight:bold; color:#007bff;">
          <?= esc($_SESSION['user']['usuario']) ?>
        </span>
      </li>

<!-- Para Productos -->
<li>
  <a href="admin_productos.php" class="button-filter gestion" onclick="if(typeof abrirModalProductos === 'function') { event.preventDefault(); abrirModalProductos(); }">
    Gestión de Productos
  </a>
</li>



      <li>
        <a href="inicio_admin.php" class="login-button" style="background:#dc3545;">
          Volver
        </a>
      </li>
    <?php endif; ?>
  </ul>

  <!-- Botón hamburguesa -->
  <div class="hamburger">
    <span></span>
    <span></span>
    <span></span>
  </div>

</nav>

<br><br><br>

</section>

<!-- MISMO SCRIPT QUE EN INDEX -->
<script>
const hamburger = document.querySelector('.hamburger');
const navLinks  = document.querySelector('.nav-links');

if (hamburger && navLinks) {
  hamburger.addEventListener('click', () => {
    hamburger.classList.toggle('active');
    navLinks.classList.toggle('open');
  });
}
</script>

</body>
</html>
