<?php
// index.php — Página principal + calendario unificado

require __DIR__ . '/php/config.php';
require __DIR__ . '/php/auth.php';

// ================= Aquí tienes el contador de visitas =================
if (!isset($_COOKIE['mdk_visitor_id'])) {
  // Si no tiene cookie, es visita única (por mes/periodo)
  $mysqli->query("UPDATE contador_visitas SET total_visitas = total_visitas + 1 WHERE id = 1");

  // Marcar cookie por 30 días
  setcookie('mdk_visitor_id', 'visited', time() + (86400 * 30), "/");
}

// ================= Aquí cargas los eventos del sistema =================
$eventosSistema = [];
$q1 = $mysqli->query("SELECT ID_EVENTO, NOMBRE, DESCRIPCION, FECHA, LUGAR FROM evento ORDER BY FECHA ASC");
if ($q1)
  $eventosSistema = $q1->fetch_all(MYSQLI_ASSOC);

// ================= Aquí cargas las inscripciones a los torneos =================
$inscripciones = [];
$q2 = $mysqli->query("
    SELECT 
        it.ID_INSCRIPCION,
        t.NOMBRE AS TORNEO,
        t.FECHA AS FECHA_TORNEO,
        t.LUGAR AS LUGAR_TORNEO,
        p.PRIMER_NOMBRE,
        p.PRIMER_APELLIDO,
        it.CATEGORIA,
        it.MODALIDAD
    FROM inscripciones_torneos it
    JOIN torneos t ON it.ID_TORNEO = t.ID_TORNEO
    LEFT JOIN persona p ON it.ID_PERSONA = p.ID_PERSONA
    ORDER BY t.FECHA ASC
");
if ($q2)
  $inscripciones = $q2->fetch_all(MYSQLI_ASSOC);

// ================= Aquí armas el arreglo para el LocalStorage =================
$inscripcionesForLS = [];

// Eventos del sistema (sin persona)
foreach ($eventosSistema as $e) {
  $inscripcionesForLS[] = [
    'torneo' => $e['NOMBRE'],
    'nombre' => '',
    'categoria' => 'Evento',
    'modalidad' => 'General',
    'fecha' => substr($e['FECHA'], 0, 10),
    'lugar' => $e['LUGAR'] ?? '',
    'fuente' => 'evento'
  ];
}

// Inscripciones reales (con persona)
foreach ($inscripciones as $e) {
  $inscripcionesForLS[] = [
    'torneo' => $e['TORNEO'],
    'nombre' => trim(($e['PRIMER_NOMBRE'] ?? '') . ' ' . ($e['PRIMER_APELLIDO'] ?? '')),
    'categoria' => $e['CATEGORIA'],
    'modalidad' => $e['MODALIDAD'],
    'fecha' => substr($e['FECHA_TORNEO'], 0, 10),
    'lugar' => $e['LUGAR_TORNEO'] ?? '',
    'fuente' => 'inscripcion'
  ];
}

// Asegurar que sea array siempre
if (!is_array($inscripcionesForLS))
  $inscripcionesForLS = [];

$inscripciones_json = json_encode($inscripcionesForLS, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Academia de Taekwondo - Inicio</title>
  <link rel="stylesheet" href="style.css">

  <!-- Librerías externas -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper/swiper-bundle.min.css" />
  <script src="https://cdn.jsdelivr.net/npm/swiper/swiper-bundle.min.js"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css">
  <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script src="js/script.js" defer></script>
  <link rel="icon" type="image/png" href="img/favicon-96x96.png" sizes="96x96">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link rel="stylesheet" href="feedback.css"> <!-- New Feedback Styles -->
</head>

<body>

  <!-- HERO PRINCIPAL -->
  <section class="hero" id="inicio">
    <nav class="navbar">

      <!-- Logo -->
      <a href="index.php" class="logo" data-aos="fade-up">
        <img src="img/logo-mdk.jpg" alt="MDK Boyacá" class="logo-img">
      </a>

      <!-- Lista de enlaces (menú) -->
      <ul class="nav-links" data-aos="fade-up">
        <li><a href="#inicio">Inicio</a></li>
        <li><a href="#valores">Pilares</a></li>
        <li><a href="#calendario">Calendario</a></li>
        <li><a href="#contacto">Contacto</a></li>
        <li><a href="tienda.php">Tienda</a></li>
      </ul>

      <!-- Botón Login -->
      <a href="login.php" class="login-button" data-aos="fade-up">Ingresa</a>

      <!-- Botón hamburguesa (último para que no tape nada) -->
      <div class="hamburger">
        <span></span>
        <span></span>
        <span></span>
      </div>

    </nav>

    <div class="overlay"></div>
    <div class="hero-content" data-aos="fade-up">
      <br>
    </div>
  </section>

  <section class="seccion-escuela" data-aos="fade-up">
    <div class="escuela-contenedor">
      <!-- Imagen izquierda -->
      <div class="escuela-imagen">
        <img src="img/MdkBoyaca_dorado.PNG" alt="Escuela de Taekwondo">
      </div>

      <!-- Texto derecha -->
      <div class="escuela-texto">
        <h2 class="escuela-titulo">Transformando Vidas</h2>
        <p>
          La esencia del Taekwondo es forjar hombres y mujeres de actitud positiva, agresivos y a la vez con disciplina
          y respeto. Hombres cuyo campo de batalla sea la vida misma y salgan por una medalla de ORO🏅 cada amanecer.
        </p>
      </div>
    </div>
  </section>

  <!-- ESTUDIANTE ATLETA -->
  <section class="seccion-ninos" data-aos="fade-up">
    <div class="escuela-contenedor">
      <!-- Texto -->
      <div class="escuela-texto">
        <h2 class="escuela-titulo">Niñas, Niños y el TaeKwonDo</h2>
        <p>Trabajamos activamente para que sus hijos mejoren en su formación personal, académica y física.</p>
        <p>No sabemos si su hijo será un gran médico, científico, empresario o un campeón mundial.</p>
        <p>Lo que sí sabemos es que podrá lograrlo, porque tendrá la formación necesaria para ser el mejor
          <strong>Estudiante Atleta</strong>.
        </p>
      </div>

      <!-- Slider propio -->
      <div class="escuela-imagen">
        <div id="slider-atleta" class="slider-imagenes" data-aos="fade-up">
          <div class="slider-container">
            <div class="slide">
              <img src="estudiante-atleta/_DSC2713-1.jpg" alt="Atleta 1">
            </div>
            <div class="slide">
              <img src="estudiante-atleta/_DSC2723-3.jpg" alt="Atleta 2">
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!--  CALENDARIO -->
  <section id="calendario" class="calendario-actividades">
    <h2 class="titulo">📅 Calendario de Actividades</h2>

    <div class="calendario-card">
      <div class="calendario-controles">
        <button id="mesAnterior"><i class="fas fa-chevron-left"></i></button>
        <p id="mesActualTexto" class="mes-ano-actual" style="font-weight:bold;font-size:18px;margin:0 10px;"></p>
        <button id="mesSiguiente"><i class="fas fa-chevron-right"></i></button>
      </div>

      <input type="hidden" id="mesActual" value="<?= date('Y-m') ?>">

      <div class="eventos-del-dia">
        <p class="subsubtitulo">Eventos del mes:</p>
        <ul id="listaEventos">
          <li class="evento">Cargando eventos...</li>
        </ul>
      </div>
    </div>
  </section>

  <script>
    (function () {
      try {
        var data = <?= $inscripciones_json ?>;
        localStorage.setItem('inscripcionesTorneos', JSON.stringify(data));
      } catch (e) { console.error(e); }
    })();
  </script>

  <!--  Script del calendario -->
  <script src="js/calendario_script.js"></script>

  <!--  Mini motor de calendario -->
  <script>
    (function () {
      const btnPrev = document.getElementById('mesAnterior');
      const btnNext = document.getElementById('mesSiguiente');
      const mesInput = document.getElementById('mesActual');
      const mesTexto = document.getElementById('mesActualTexto');
      const lista = document.getElementById('listaEventos');

      const meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];

      function renderMes(val) {
        try {
          const [y, m] = val.split('-');
          mesTexto.textContent = meses[parseInt(m) - 1] + " " + y;
        } catch { mesTexto.textContent = val; }
      }

      function cargarEventos(val) {
        let arr = JSON.parse(localStorage.getItem('inscripcionesTorneos') || "[]");
        let ev = arr.filter(e => e.fecha.startsWith(val));

        if (ev.length === 0) {
          lista.innerHTML = `<li class='evento'>No hay eventos este mes.</li>`;
          return;
        }

        lista.innerHTML = ev.map(e => `
      <li class='evento torneo'>
        🏆 ${e.torneo} — ${e.nombre}
        <br>📍 ${e.lugar}
        <br>📅 ${e.fecha}
      </li>
    `).join('');
      }

      function moverMes(n) {
        let [y, m] = mesInput.value.split('-').map(Number);
        let d = new Date(y, m - 1 + n, 1);
        let nv = `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}`;
        mesInput.value = nv;
        renderMes(nv);
        cargarEventos(nv);
      }

      renderMes(mesInput.value);
      cargarEventos(mesInput.value);

      btnPrev.onclick = () => moverMes(-1);
      btnNext.onclick = () => moverMes(1);
    })();
  </script>

  <!--  Librería de íconos -->
  <script src="https://kit.fontawesome.com/64d58efce2.js" crossorigin="anonymous"></script>

  <!-- GALERÍA -->
  <?php
  // ================= AQUÍ CARGAS LAS IMÁGENES DE TU GALERÍA =================
  $galeria_imagenes = [];
  $query_galeria = $mysqli->query("SELECT ruta_archivo, descripcion FROM gestion_contenidos WHERE seccion = 'galeria' ORDER BY subseccion ASC");
  if ($query_galeria && $query_galeria->num_rows > 0) {
    while ($img = $query_galeria->fetch_assoc()) {
      $galeria_imagenes[] = $img;
    }
  }

  // Imágenes estáticas predefinidas
  $imagenes_estaticas = [
    ['ruta_archivo' => 'galery/_DSC4343-6.jpg', 'descripcion' => ''],
    ['ruta_archivo' => 'galery/_DSC4354-7.jpg', 'descripcion' => ''],
    ['ruta_archivo' => 'galery/_DSC4364-11.jpg', 'descripcion' => ''],
    ['ruta_archivo' => 'galery/_DSC4533-7.jpg', 'descripcion' => ''],
    ['ruta_archivo' => 'galery/_DSC4656-11.jpg', 'descripcion' => ''],
    ['ruta_archivo' => 'galery/_DSC5235-351.jpg', 'descripcion' => ''],
    ['ruta_archivo' => 'galery/_DSC7871-21.jpg', 'descripcion' => ''],
    ['ruta_archivo' => 'galery/_DSC7874-22.jpg', 'descripcion' => ''],
    ['ruta_archivo' => 'galery/_DSC7887-27.jpg', 'descripcion' => ''],
    ['ruta_archivo' => 'galery/_DSC7907-35.jpg', 'descripcion' => '']
  ];

  // Combinar imágenes estáticas con las de la BD
  // NOTA: Se muestran primero las estáticas y luego las nuevas (BD).
  // Para "reemplazar" las estáticas, simplemente comente o elimine líneas en el array $imagenes_estaticas.
  $lista_galeria = array_merge($imagenes_estaticas, $galeria_imagenes);
  ?>
  <section id="slider-galeria" class="galeria-seccion" data-aos="fade-up" data-aos-duration="2500">
    <h2 class="galeria-titulo" data-aos="fade-up">Galería</h2>

    <div class="galeria-contenedor">
      <!-- Imagen fija izquierda -->
      <div class="galeria-lateral">
        <img src="img/IMG_6109.png" alt="Imagen izquierda">
      </div>

      <!-- SLIDER CENTRAL -->
      <div class="slider-galeria">
        <div class="slider-container">
          <?php foreach ($lista_galeria as $index => $img): ?>
            <div class="slide<?php echo $index === 0 ? ' active' : ''; ?>">
              <img src="<?php echo htmlspecialchars($img['ruta_archivo']); ?>"
                alt="<?php echo htmlspecialchars($img['descripcion'] ?: 'Galería ' . ($index + 1)); ?>">
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Imagen fija derecha -->
      <div class="galeria-lateral">
        <img src="img/IMG_6110.png" alt="Imagen derecha">
      </div>
    </div>
  </section>

  <!-- VALORES -->
  <section id="valores" class="valores" data-aos="zoom-in">
    <h2 class="titulo-pilares">MDKBoyacá-Taekwondo</h2>
    <p class="texto-pilares">Pilares del Arte Marcial</p>
    <div class="valores-grid" data-aos="zoom-in" data-aos-duration="3000">

      <a href="historia.html" class="valor-card">
        <img src="img/historia.png" alt="Historia">
        <h3>Historia</h3>
      </a>

      <a href="filosofia.html" class="valor-card">
        <img src="img/filosofia.png" alt="Filosofía">
        <h3>Filosofía</h3>
      </a>

      <a href="marcialidad.html" class="valor-card">
        <img src="img/marcialidad.png" alt="Marcialidad">
        <h3>Marcialidad</h3>
      </a>

      <a href="tradicion.html" class="valor-card">
        <img src="img/tradicion.png" alt="Tradición">
        <h3>Tradición</h3>
      </a>

      <a href="tecnica.html" class="valor-card">
        <img src="img/tecnica.png" alt="Técnica">
        <h3>Técnica</h3>
      </a>

    </div>
  </section>

  </div>
  </section>

  <!-- UBICACION -->
  <section class="ubicacion" id="contacto" data-aos="fade-up" data-aos-duration="2000">
    <div class="ubicacion-contenedor">
      <div class="ubicacion-texto">
        <h2 class="titulo">Encuéntranos aquí</h2>
        <p class="texto-ubicacion">Estamos en el corazón de Boyacá. Ven y entrena con nosotros en un ambiente sano,
          seguro y lleno de disciplina.</p>
      </div>
      <div class="ubicacion-mapa">
        <iframe
          src="https://www.google.com/maps/embed?pb=!4v1759106598931!6m8!1m7!1s9vgoXT2pA1KiOzZ8HFO5rA!2m2!1d5.5346!2d-73.3632!3f20.4!4f-3.1!5f0.78"
          width="100%" height="300" allowfullscreen loading="lazy"></iframe>
      </div>
    </div>
  </section>

  <section class="feedback-section" data-aos="fade-up">
    <div class="feedback-container">
      <h2 class="titulo">Tu Opinión es Importante</h2>
      <p class="feedback-subtitle">Ayúdanos a mejorar contándonos tu experiencia.</p>

      <form id="feedbackForm" class="feedback-form">
        <div class="form-grid">
          <div class="form-group">
            <label for="fb_nombre">Nombre (Opcional)</label>
            <input type="text" id="fb_nombre" name="nombre" placeholder="Tu nombre">
          </div>

          <div class="form-group">
            <label for="fb_categoria">Tipo de Comentario</label>
            <select id="fb_categoria" name="categoria" required>
              <option value="Felicitacion">Felicitación 🌟</option>
              <option value="Sugerencia">Sugerencia 💡</option>
              <option value="Queja">Queja ⚠️</option>
            </select>
          </div>
        </div>

        <div class="form-group comentario-area">
          <label for="fb_comentario">Comentario</label>
          <textarea id="fb_comentario" name="comentario" required
            placeholder="Comparte tu experiencia con nosotros..."></textarea>
        </div>

        <div class="btn-container">
          <button type="submit" class="btn-cta">ENVIAR COMENTARIO</button>
        </div>

        <div id="feedbackResponse"></div>
      </form>
    </div>
  </section>

  <script>
    document.getElementById('feedbackForm').addEventListener('submit', function (e) {
      e.preventDefault();
      const formData = new FormData(this);
      const respDiv = document.getElementById('feedbackResponse');

      respDiv.innerHTML = '<span style="color:blue;">Enviando...</span>';

      fetch('feedback_handler.php', {
        method: 'POST',
        body: formData
      })
        .then(response => response.json())
        .then(data => {
          if (data.status === 'success') {
            respDiv.innerHTML = '<span style="color:green; font-weight:bold;">' + data.message + '</span>';
            document.getElementById('feedbackForm').reset();
          } else {
            respDiv.innerHTML = '<span style="color:red; font-weight:bold;">Error: ' + data.message + '</span>';
          }
        })
        .catch(error => {
          console.error('Error:', error);
          respDiv.innerHTML = '<span style="color:red;">Error de conexión. Inténtalo de nuevo.</span>';
        });
    });
  </script>

  <!-- INVITACION -->
  <section class="seccion-invitacion" data-aos="zoom-in">

    <div class="invitacion-lateral">
      <img src="img/IMG_6109.png" alt="Imagen izquierda">
      <h3>Primero guerreras, después princesas</h3>
    </div>

    <div class="invitacion-contenido">
      <h2 class="invitacion">Sonríe, Disfruta, Vive y Entrena <br>
        <p class="taekwondo-invitacion">TaeKwonDo</p>
      </h2>
      <a href="https://api.whatsapp.com/send?phone=573124514555&text=Hola!%20Quiero%20mi%20clase%20de%20cortesia."
        target="_blank" class="boton-invitacion">
        ¡Clase de cortesía!
      </a>
    </div>

    <div class="invitacion-lateral">
      <img src="img/IMG_6110.png" alt="Imagen derecha">
      <h3>Si deseas ser un león, entrena con los leones</h3>
    </div>

  </section>

  <!-- BOTON WHATSAPP -->
  <a href="https://api.whatsapp.com/send?phone=573124514555&text=Hola!%20Quiero%20más%20información."
    class="whatsapp-float" target="_blank">
    <i class="fab fa-whatsapp"></i>
  </a>

  <!-- BOTON INSTAGRAM -->
  <a href="https://www.instagram.com/mdkboyaca/" class="instagram-float" target="_blank">
    <i class="fab fa-instagram"></i>
  </a>

  <!-- BOTON FACEBOOK -->
  <a href="https://www.facebook.com/MDKBoyaca" class="facebook-float" target="_blank">
    <i class="fab fa-facebook-f"></i>
  </a>

  <?php include("./temp/footer.php") ?>

  <!-- BOTÓN HORARIOS -->
  <button id="btn-horarios" class="btn-horarios">Ver Horarios</button>

  <!-- MODAL HORARIOS -->
  <div id="modal-horarios" class="modal-horarios">
    <div class="modal-content-horarios">
      <span class="close-horarios">&times;</span>
      <h2>Horarios de Entrenamiento</h2>
      <ul>
        <li><strong class="texto-horarios">Niños de 3 a 5 años:</strong> <br>Miércoles 5 p.m. - 6 p.m. y Sábado 9 a.m.
        </li>
        <li><strong class="texto-horarios">Niños de 6 a 8 años:</strong> <br>Lunes, Miércoles, Viernes 6 p.m. - 7 p.m. y
          Sábados 10 a.m.</li>
        <li><strong class="texto-horarios">Niños de 9 a 14 años:</strong> <br>Lunes, Miércoles, Viernes 7 p.m. - 8 p.m.
          y Sábados 11 a.m. o 12 p.m.</li>
        <li><strong class="texto-horarios">Jóvenes, Adultos y Cinturones Negros:</strong> <br>Lunes, Miércoles, Viernes
          8 p.m. - 9 p.m.</li>
      </ul>
    </div>
  </div>

  <script>
    // JS para abrir y cerrar el modal
    const btnHorarios = document.getElementById("btn-horarios");
    const modalHorarios = document.getElementById("modal-horarios");
    const closeHorarios = document.querySelector(".close-horarios");

    btnHorarios.addEventListener("click", () => {
      modalHorarios.style.display = "flex";
    });

    closeHorarios.addEventListener("click", () => {
      modalHorarios.style.display = "none";
    });

    window.addEventListener("click", (e) => {
      if (e.target === modalHorarios) {
        modalHorarios.style.display = "none";
      }
    });
  </script>

  <!-- RUULETA DE FRASES TIPO TEMU -->
  <div id="ruleta-overlay" style="display:none;">
    <div id="ruleta-modal">
      <button id="cerrar-ruleta">×</button>
      <canvas id="ruleta" width="400" height="400"></canvas>
      <p id="resultado-ruleta" style="display:none;"></p>
    </div>
  </div>

  <script src="js/ruleta.js"></script>

  <script>
    AOS.init();
    const swiper = new Swiper(".mySwiper", {
      loop: true,
      pagination: { el: ".swiper-pagination", clickable: true },
      navigation: { nextEl: ".swiper-button-next", prevEl: ".swiper-button-prev" }
    });
  </script>

  <script>
    // ---- Inicializar TODOS los sliders .slider-container independientemente ----
    document.addEventListener("DOMContentLoaded", () => {
      // buscar todos los contenedores de slider en la página
      const containers = document.querySelectorAll(".slider-container");

      containers.forEach((container) => {
        const slides = Array.from(container.querySelectorAll(".slide"));
        if (slides.length === 0) return;

        let idx = 0;
        let intervalo = null;
        const delay = 3500; // tiempo entre slides (ms)

        function show(i) {
          slides.forEach((s, j) => s.classList.toggle("active", j === i));
        }

        function next() {
          idx = (idx + 1) % slides.length;
          show(idx);
        }

        function start() {
          // garantizar que no existan intervalos duplicados
          stop();
          show(idx); // mostrar inmediatamente
          intervalo = setInterval(next, delay);
        }

        function stop() {
          if (intervalo) {
            clearInterval(intervalo);
            intervalo = null;
          }
        }

        // iniciar slider individual
        start();

        // pause / resume on hover (solo para este contenedor)
        container.addEventListener("mouseenter", () => {
          stop();
        });
        container.addEventListener("mouseleave", () => {
          start();
        });

        // opcional: permitir que si el usuario toca (mobile) pare el slider brevemente
        container.addEventListener("touchstart", () => stop(), { passive: true });
        container.addEventListener("touchend", () => start(), { passive: true });
      });
    });
  </script>

  <script>
    const hamburger = document.querySelector('.hamburger');
    const navLinks = document.querySelector('.nav-links');

    hamburger.addEventListener('click', () => {
      hamburger.classList.toggle('active');
      navLinks.classList.toggle('open');
    });
  </script>

</body>

</html>