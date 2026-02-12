<?php
// student.php — Panel de estudiante con clases/IDs del perfil original y datos desde BD

require __DIR__ . '/php/config.php';
require __DIR__ . '/php/auth.php';

// Requiere sesión iniciada (cualquier rol)
if (!isset($_SESSION['user'])) {
  header('Location: login.php');
  exit;
}

$user_id = (int) ($_SESSION['user']['id'] ?? 0);

// ================================
// TRAER DATOS DEL ESTUDIANTE
// ================================
$stmt = $mysqli->prepare("
  SELECT
    p.ID_PERSONA, p.PRIMER_NOMBRE, p.SEGUNDO_NOMBRE, p.PRIMER_APELLIDO, p.SEGUNDO_APELLIDO,
    p.TIPO_DOCUMENTO, p.DOCUMENTO, p.FECHA_NACIMIENTO, p.EDAD, p.USUARIO,
    p.FOTO, p.ID_CINTURON, p.DAN, p.NIVEL, p.DIAS_ENTRENAMIENTO, p.ID_ESTADO,
    c.NOMBRE AS CINTURON_NOMBRE
  FROM persona p
  LEFT JOIN cinturon c ON c.ID_CINTURON = p.ID_CINTURON
  WHERE p.ID_PERSONA = ?
  LIMIT 1
");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$est = $stmt->get_result()->fetch_assoc();

if (!$est) {
  header('Location: login.php');
  exit;
}

// ================================
// VERIFICAR ESTADO ACTIVO/INACTIVO
// ================================
// ID_ESTADO: 1 = Activo, 2 = Inactivo
if ((int) $est['ID_ESTADO'] === 2) {
  // Usuario inactivo - mostrar modal y detener ejecución
  include __DIR__ . '/modal_inactivo.html';
  exit;
}

// ================================
// CALCULAR EDAD
// ================================
function calc_age($ymd)
{
  if (!$ymd)
    return null;
  $dob = DateTime::createFromFormat('Y-m-d', $ymd);
  if (!$dob)
    return null;
  $today = new DateTime('today');
  return (int) $dob->diff($today)->y;
}
$edad_calc = calc_age($est['FECHA_NACIMIENTO']);
if ($edad_calc !== null) {
  $est['EDAD'] = $edad_calc;
}

// ================================
// RESOLVER TEXTO DE DAN
// ================================
function dan_text($dan)
{
  if ($dan === null || $dan === '')
    return '-';
  if ((int) $dan === 0)
    return 'Poom';
  return (int) $dan . ' Dan';
}

// ================================
// NOMBRE COMPLETO + DATOS
// ================================
$nombre_completo = trim(
  $est['PRIMER_NOMBRE'] . ' ' .
  ($est['SEGUNDO_NOMBRE'] ?: '') . ' ' .
  $est['PRIMER_APELLIDO'] . ' ' .
  ($est['SEGUNDO_APELLIDO'] ?: '')
);

$doc_texto = trim($est['TIPO_DOCUMENTO'] . ' ' . $est['DOCUMENTO']);
$dias_texto = $est['DIAS_ENTRENAMIENTO'] ? $est['DIAS_ENTRENAMIENTO'] : '-';

$foto_url = $est['FOTO'] ? $est['FOTO'] : 'img/avatar_placeholder.png';

// ================================
// BADGE DE CINTURÓN
// ================================
function belt_class($nombre)
{
  $n = strtolower($nombre ?: '');
  if (strpos($n, 'blanco') !== false)
    return 'belt--blanco';
  if (strpos($n, 'amarilla') !== false || strpos($n, 'amarillo') !== false)
    return 'belt--amarillo';
  if (strpos($n, 'verde') !== false)
    return 'belt--verde';
  if (strpos($n, 'azul') !== false)
    return 'belt--azul';
  if (strpos($n, 'roja') !== false || strpos($n, 'rojo') !== false)
    return 'belt--rojo';
  if (strpos($n, 'negra') !== false || strpos($n, 'negro') !== false)
    return 'belt--negro';
  return 'belt--base';
}
$belt_cls = belt_class($est['CINTURON_NOMBRE']);

// ================================
// TRAER INFORMACIÓN DEL ACUDIENTE
// ================================
$stmtA = $mysqli->prepare("
    SELECT 
        NOMBRE_COMPLETO,
        EMPRESA,
        CARGO,
        EMAIL,
        CELULAR
    FROM acudiente
    WHERE ID_PERSONA = ?
    LIMIT 1
");
$stmtA->bind_param("i", $user_id);
$stmtA->execute();
$acud = $stmtA->get_result()->fetch_assoc();

// Variables seguras()
$acudiente_nombre = $acud['NOMBRE_COMPLETO'] ?? '-';
$acudiente_empresa = $acud['EMPRESA'] ?? '-';
$acudiente_cargo = $acud['CARGO'] ?? '-';
$acudiente_email = $acud['EMAIL'] ?? '-';
$acudiente_celular = $acud['CELULAR'] ?? '-';




// LOGICA PARA GUARDAR DATOS DEL ACUDIENTE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'actualizar_acudiente') {

  // 1. Recoger los datos del formulario
  $nuevo_nombre = trim($_POST['nom_acudiente']);
  $nueva_empresa = trim($_POST['empresa_acudiente']);
  $nuevo_cargo = trim($_POST['cargo_acudiente']);
  $nuevo_email = trim($_POST['email_acudiente']);
  $nuevo_cel = trim($_POST['cel_acudiente']);

  // 2. Verificar si ya existe un acudiente para este estudiante
  // (Asumo que $user_id ya está definido arriba por la sesión)
  $check = $mysqli->prepare("SELECT ID_ACUDIENTE FROM acudiente WHERE ID_PERSONA = ?");
  $check->bind_param("i", $user_id);
  $check->execute();
  $resultado = $check->get_result();

  if ($resultado->num_rows > 0) {
    // ACTUALIZAR (UPDATE) si ya existe
    $sql = "UPDATE acudiente SET NOMBRE_COMPLETO=?, EMPRESA=?, CARGO=?, EMAIL=?, CELULAR=? WHERE ID_PERSONA=?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("sssssi", $nuevo_nombre, $nueva_empresa, $nuevo_cargo, $nuevo_email, $nuevo_cel, $user_id);
  } else {
    // CREAR (INSERT) si no tenía acudiente
    $sql = "INSERT INTO acudiente (NOMBRE_COMPLETO, EMPRESA, CARGO, EMAIL, CELULAR, ID_PERSONA) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("sssssi", $nuevo_nombre, $nueva_empresa, $nuevo_cargo, $nuevo_email, $nuevo_cel, $user_id);
  }

  if ($stmt->execute()) {
    // Recargar la página para ver los cambios limpios
    header("Location: student.php?msg=guardado");
    exit;
  } else {
    echo "<p style='color:red;'>Error al guardar: " . $mysqli->error . "</p>";
  }
}

?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Perfil del Estudiante - MDK Boyacá</title>
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="student.css">
  <link rel="stylesheet" href="student_belt.css">
  <link rel="icon" href="img/favicon-96x96.png" sizes="96x96" type="image/png">

</head>

<body>

  <section>

    <style>
      /* Fijar navbar SOLO en esta página */
      section .navbar {
        position: fixed !important;
        top: 0;
        left: 0;
        width: 100%;
        z-index: 9999;
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(6px);
        padding: 10px 20px !important;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
      }

      /* Evitar que el contenido quede debajo */
      section {
        padding-top: 110px !important;
      }
    </style>

    <nav class="navbar">

      <!-- Logo -->
      <a href="index.php" class="logo">
        <img src="img/logo-mdk.jpg" alt="Logo MDK Boyacá" class="logo-img">
      </a>

      <!-- BOTÓN DINÁMICO PARA ESTUDIANTE -->
      <?php if (!isset($_SESSION)) {
        session_start();
      } ?>

      <?php if (isset($_SESSION['user'])): ?>

        <div class="admin-info">

          <span id="student-username-display">
            <?= htmlspecialchars($_SESSION['user']['usuario']) ?>
          </span>

          <a href="aprendizaje.html" class="login-button">
            MDKBoyacá-Educación
          </a>

          <a href="logout.php" id="student-logout-button" class="login-button" style="background:#e74c3c;">
            Salir
          </a>

        </div>

      <?php endif; ?>

    </nav>

  </section>


  <!-- Contenedor principal del perfil -->
  <main class="profile-container">
    <section class="profile-card">

      <!-- Columna de foto -->
      <div class="profile-photo">
        <img id="s-foto" src="<?= htmlspecialchars($foto_url) ?>" alt="Foto del estudiante">
      </div>

      <!-- Columna de información -->
      <div class="profile-info">
        <h2 class="heading">Bienvenido, <?= htmlspecialchars($est['PRIMER_NOMBRE'] . ' ' . $est['PRIMER_APELLIDO']) ?>
        </h2>

        <ul class="profile-list">
          <li><strong>Nombre completo:</strong> <span id="s-nombre"><?= htmlspecialchars($nombre_completo) ?></span>
          </li>
          <li><strong>Documento:</strong> <span id="s-documento"><?= htmlspecialchars($doc_texto) ?></span></li>
          <li><strong>Edad:</strong> <span id="s-edad"><?= htmlspecialchars((string) $est['EDAD']) ?></span> años</li>
          <li>
            <strong>Cinturón:</strong>
            <?php
            // 1. Obtener imágenes de cinturones
            $cinturones_map = [];
            $qCintas = $mysqli->query("SELECT subseccion, ruta_archivo FROM gestion_contenidos WHERE seccion='cinturones'");
            while ($rowC = $qCintas->fetch_assoc()) {
              $cinturones_map[strtolower($rowC['subseccion'])] = $rowC['ruta_archivo'];
            }

            // 2. Mapear nombre del estudiante a la clave (subseccion)
            // Normalizar nombre para buscar coincidencia
            $cinta_nombre = strtolower($est['CINTURON_NOMBRE'] ?? '');
            $img_cinturon = 'img/default.png'; // Fallback
            
            // Mapeo manual de nombres a claves de BD (ajustar si es necesario)
            $keys_map = [
              'blanco' => 'blanco',
              'franja amarilla' => 'franja_amarilla',
              'amarillo' => 'amarillo',
              'franja verde' => 'franja_verde',
              'verde' => 'verde',
              'franja azul' => 'franja_azul',
              'azul' => 'azul',
              'franja roja' => 'franja_roja',
              'rojo' => 'rojo',
              'franja negra' => 'franja_negra',
              'negro' => 'negro'
            ];

            // Intentar buscar la clave
            foreach ($keys_map as $name_key => $db_key) {
              if (strpos($cinta_nombre, $name_key) !== false) {
                if (isset($cinturones_map[$db_key])) {
                  $img_cinturon = $cinturones_map[$db_key];
                }
                break;
              }
            }
            ?>

            <div class="belt-display-container">
              <img src="<?= htmlspecialchars($img_cinturon) ?>"
                alt="Cinturón <?= htmlspecialchars($est['CINTURON_NOMBRE']) ?>" class="belt-img">
              <span id="s-cinturon" class="belt-badge <?= htmlspecialchars($belt_cls) ?>">
                <?= htmlspecialchars($est['CINTURON_NOMBRE'] ?: '-') ?>
              </span>
              <small>(<?= htmlspecialchars(dan_text($est['DAN'])) ?>)</small>
            </div>
          </li>
          <li><strong>Nivel:</strong> <span id="s-nivel"><?= htmlspecialchars($est['NIVEL'] ?: '-') ?></span></li>
          <li><strong>Días de entrenamiento:</strong> <span
              id="s-dias-entrenamiento"><?= htmlspecialchars($dias_texto) ?></span></li>

          <!-- ================================
               ACUDIENTE 
          ================================= -->
          <li>
            <strong>Acudiente:</strong>
            <span id="s-nombre-acudiente"><?= htmlspecialchars($acudiente_nombre) ?></span>
          </li>

          <li><strong>Empresa:</strong> <span><?= htmlspecialchars($acudiente_empresa) ?></span></li>
          <li><strong>Cargo:</strong> <span><?= htmlspecialchars($acudiente_cargo) ?></span></li>
          <li><strong>Email:</strong> <span><?= htmlspecialchars($acudiente_email) ?></span></li>
          <li><strong>Celular:</strong> <span><?= htmlspecialchars($acudiente_celular) ?></span></li>

        </ul>

        <!-- Botón para mostrar el formulario -->
        <button onclick="document.getElementById('form-acudiente').style.display='block'" class="btn-edit-toggle">
          <i class="boton-editar"></i> Editar Datos Acudiente
        </button>

        <!-- El Formulario -->
        <div id="form-acudiente" style="display:none;">
          <h3>Actualizar Acudiente</h3>

          <form method="POST" action="student.php">
            <input type="hidden" name="accion" value="actualizar_acudiente">

            <!-- CADA CAMPO EN SU PROPIA FILA -->
            <div class="form-group">
              <label>Nombre Completo:</label>
              <input type="text" name="nom_acudiente" value="<?= htmlspecialchars($acudiente_nombre) ?>" required>
            </div>

            <div class="form-group">
              <label>Empresa:</label>
              <input type="text" name="empresa_acudiente" value="<?= htmlspecialchars($acudiente_empresa) ?>">
            </div>

            <div class="form-group">
              <label>Cargo:</label>
              <input type="text" name="cargo_acudiente" value="<?= htmlspecialchars($acudiente_cargo) ?>">
            </div>

            <div class="form-group">
              <label>Email:</label>
              <input type="email" name="email_acudiente" value="<?= htmlspecialchars($acudiente_email) ?>">
            </div>

            <div class="form-group">
              <label>Celular:</label>
              <input type="text" name="cel_acudiente" value="<?= htmlspecialchars($acudiente_celular) ?>">
            </div>

            <div class="form-buttons">
              <button type="submit" class="boton">Guardar</button>
              <button type="button" onclick="document.getElementById('form-acudiente').style.display='none'"
                class="boton">Cancelar</button>
            </div>
          </form>
        </div>

      </div>
    </section>

    <!-- Sección extra opcional (eventos personales) -->
    <section class="profile-events">
      <h3 class="subtitulo">Eventos personales recientes</h3>
      <?php
      $stmt = $mysqli->prepare("SELECT DESCRIPCION, FECHA FROM eventos_personales WHERE ID_PERSONA=? ORDER BY FECHA DESC, ID_EVENTO_PERSONAL DESC LIMIT 6");
      $stmt->bind_param('i', $user_id);
      $stmt->execute();
      $evs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
      if (!$evs): ?>
        <p>No hay eventos registrados.</p>
      <?php else: ?>
        <ul>
          <?php foreach ($evs as $ev): ?>
            <li>
              <div><?= htmlspecialchars($ev['FECHA']) ?></div>
              <div><?= htmlspecialchars($ev['DESCRIPCION']) ?></div>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </section>
  </main>

  <?php include './temp/footer.php'; ?>


  <script>
    document.getElementById('current-year-student').textContent = new Date().getFullYear();
  </script>
</body>

</html>