<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Cinturones Negros - MDKBoyacá Taekwondo</title>
  <link rel="stylesheet" href="style.css">

  <!-- Librerías externas -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css">
  <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="icon" type="image/png" href="img/favicon-96x96.png" sizes="96x96">
</head>

<body>
  <!-- NAVBAR -->
  <nav class="navbar">
    <a href="index.php" class="logo">
      <img src="img/logo-mdk.jpg" alt="Logo MDK Boyacá" class="logo-img" />
    </a>
    <div>
      <a href="admin_eventos.php" class="volver-atras"
        style="color: #fff; text-decoration: none; font-weight: 600; transition: 0.3s;">Gestión de Eventos</a>
      <a href="inicio_admin.php" class="volver-atras"
        style="color: #fff; text-decoration: none; font-weight: 600; transition: 0.3s;">Volver</a>

    </div>
  </nav>

  <!-- HERO -->
  <section class="hero-cinturones">
    <div class="overlay"></div>
    <div class="hero-content">
      <h1>Cinturones Negros</h1>
      <p>Galería de Maestros y Practicantes de Taekwondo</p>
    </div>
  </section>

  <!-- SECCIONES DE DANES -->
  <main class="contenedor-cinturones">

    <!-- 9no Dan -->
    <section class="dan-section" data-aos="fade-up">
      <h2>9no Dan - Gran Maestro</h2>
      <div class="dan-grid" id="dan-9"></div>
    </section>


    <!-- 8vo Dan -->
    <section class="dan-section" data-aos="fade-up">
      <h2>8er Dan - Maestro Director</h2>
      <div class="dan-grid" id="dan-8"></div>
    </section>


    <!-- 7mo Dan -->
    <section class="dan-section" data-aos="fade-up">
      <h2>7mo Dan - Maestro</h2>
      <div class="dan-grid" id="dan-7"></div>
    </section>

    <!-- 6to Dan -->
    <section class="dan-section" data-aos="fade-up">
      <h2>6to Dan - Maestro</h2>
      <div class="dan-grid" id="dan-6"></div>
    </section>

    <!-- 5to Dan -->
    <section class="dan-section" data-aos="fade-up">
      <h2>5to Dan - Maestro</h2>
      <div class="dan-grid" id="dan-5"> </div>
    </section>

    <!-- 4to Dan -->
    <section class="dan-section" data-aos="fade-up">
      <h2>4to Dan - Profesor</h2>
      <div class="dan-grid" id="dan-4"> </div>
    </section>

    <!-- 3er Dan -->
    <section class="dan-section" data-aos="fade-up">
      <h2>3er Dan - Profesor</h2>
      <div class="dan-grid" id="dan-3"> </div>
    </section>

    <!-- 2do Dan -->
    <section class="dan-section" data-aos="fade-up">
      <h2>2do Dan - Instructor</h2>
      <div class="dan-grid" id="dan-2"> </div>
    </section>

    <!-- 1er Dan -->
    <section class="dan-section" data-aos="fade-up">
      <h2>1er Dan - Instructor</h2>
      <div class="dan-grid" id="dan-1"></div>
    </section>


    <script>
      fetch("php/negros.php")
        .then(response => {
          if (!response.ok) {
            throw new Error("Error HTTP: " + response.status);
          }
          return response.text(); // Leemos como texto primero para depurar
        })
        .then(texto => {
          try {
            const data = JSON.parse(texto); // Intentamos parsear

            if (data.error) {
              console.error("Error desde PHP:", data.error);
              return;
            }

            if (data.length === 0) {
              console.warn("No se encontraron cinturones negros en la BD.");
            }

            data.forEach(persona => {
              let dan = parseInt(persona.DAN); // Asegurar que es número
              let contenedor = document.getElementById("dan-" + dan);

              if (contenedor) {
                // Usamos una ruta relativa correcta para la imagen por defecto
                let fotoSrc = (persona.FOTO && persona.FOTO.trim() !== '') ? persona.FOTO : 'img/default.jpg';

                let tarjeta = `
              <div class="dan-card">
                <img src="${fotoSrc}" alt="${persona.PRIMER_NOMBRE}" onerror="this.src='img/default.jpg'">
                <h3>${persona.PRIMER_NOMBRE} ${persona.PRIMER_APELLIDO}</h3>
              </div>
            `;
                contenedor.innerHTML += tarjeta;
              } else {
                console.warn("No existe el contenedor para Dan:", dan);
              }
            });

          } catch (e) {
            console.error("Error al leer JSON. Respuesta recibida:", texto);
          }
        })
        .catch(err => console.error("Fallo en la petición:", err));
    </script>



  </main>

  <!-- FOOTER -->
  <footer>
    <div class="footer-content">
      <div class="social-media">
        <p><i class="fab fa-instagram"></i>
          <a href="https://www.instagram.com/mdkboyaca/" target="_blank" class="instagram-link">@mdkboyacá</a>
        </p>
      </div>
      <div class="contact-info">
        <p><i class="fab fa-whatsapp"></i>
          <a href="https://wa.me/573124514555" target="_blank">+57 312 4514555</a>
        </p>
      </div>
      <div class="contact-info">
        <p><i class="fas fa-map-marker-alt"></i> Calle 21 No 11 - 66, Tunja</p>
      </div>
    </div>
    <p>MDKBoyacá</p>
    <p>Página Web by: Programador Juan Ramírez</p>
  </footer>

  <script>
    AOS.init();
  </script>
</body>

</html>