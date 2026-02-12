<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Admin - MDKBoyacá</title>
    <!-- Enlace a tu CSS -->
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="tienda.css">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>


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

<br>
<br>
<br>
<br>
<br>


<!-- CONTENEDOR PRINCIPAL (Centra la caja) -->
<div class="main-container">

    <div class="login-box">
        <div class="login-title">Bienvenido, al panel de administrador 👋</div>

        <p style="text-align:center; margin-bottom:30px; font-size:17px; color:#666; font-size: 20px;">
            Selecciona un módulo para continuar
        </p>

        <div class="dashboard-grid">

            <a href="admin.php" class="dashboard-card">
                <i class="fa-solid fa-users"></i>
                <span>Personas</span>
            </a>

            <a href="cinturones_negros.php" class="dashboard-card">
                <i class="fa-solid fa-trophy"></i>
                <span>Cinturones Negros</span>
            </a>

            <a href="admin_productos.php" class="dashboard-card">
                <i class="fa-solid fa-shop"></i>
                <span>Gestión Productos</span>
            </a>

            <a href="admin_eventos.php" class="dashboard-card">
                <i class="fa-solid fa-calendar-days"></i>
                <span>Gestión Eventos</span>
            </a>

            <a href="gestion_medios.php" class="dashboard-card">
                <i class="fa-solid fa-photo-film"></i>
                <span>Agregar Contenido</span>
            </a>

            <!-- Espacio vacío o módulo futuro si quieres mantener 3 columnas perfectas -->
            <!-- <div class="dashboard-card" style="opacity:0; pointer-events:none;"></div> -->

            <a href="logout.php" class="dashboard-card" style="border: 2px solid #ffcdd2;">
                <i class="fa-solid fa-right-from-bracket" style="color:#d32f2f;"></i>
                <span style="color:#d32f2f;">Salir</span>
            </a>

        </div>
    </div>

</div>
<!-- Fin main-container -->


<!-- FOOTER PERSONALIZADO (footer-inicio-admin) -->
<footer class="footer-inicio-admin">
    <div class="footer-content">

        <!-- Instagram -->
        <div class="info-item">
            <i class="fab fa-instagram"></i>
            <a href="https://www.instagram.com/mdkboyaca/" target="_blank">@mdkboyacá</a>
        </div>

        <!-- WhatsApp -->
        <div class="info-item">
            <i class="fab fa-whatsapp"></i>
            <a href="https://wa.me/573124514555" target="_blank">+57 312 4514555</a>
        </div>

        <!-- Dirección -->
        <div class="info-item">
            <i class="fas fa-map-marker-alt"></i>
            <span>Calle 21 No 11 - 66, Tunja</span>
        </div>

    </div>

    <div class="footer-credits">
        <p style="margin:0; font-weight:bold;">MDKBoyacá</p>
        <p style="margin:5px 0 0; font-size:13px;">Página Web by: Programador Juan Ramírez</p>
    </div>
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