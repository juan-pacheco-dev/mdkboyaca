<?php
// torneos.php — Listado de torneos e inscripción desde BD

require __DIR__ . '/php/config.php';
require __DIR__ . '/php/auth.php';
require_login(); // cualquier usuario autenticado

$mensaje = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'inscribir') {
    $stmt = $mysqli->prepare("
        INSERT INTO inscripciones_torneos (
            ID_PERSONA, ID_TORNEO, CATEGORIA, MODALIDAD, FECHA_INSCRIPCION
        ) VALUES (?, ?, ?, ?, CURDATE())
    ");
    $stmt->bind_param(
        'iiss',
        $_SESSION['user']['id'],
        $_POST['torneo'],
        $_POST['categoria'],
        $_POST['modalidad']
    );
    $stmt->execute();
    $mensaje = 'Inscripción realizada con éxito.';
}

// Obtener torneos activos
$torneos = $mysqli->query("
    SELECT ID_TORNEO, NOMBRE, DESCRIPCION, FECHA, LUGAR 
    FROM torneos 
    WHERE ESTADO = 'Activo' 
    ORDER BY FECHA ASC
")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Torneos - MDK Boyacá</title>
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
  <header class="site-header">
    <div class="container">
      <div class="brand">
        <a href="index.php" class="logo" aria-label="MDK Boyacá">
          <img src="img/logo-mdk.jpg" alt="Logo MDK Boyacá">
        </a>
        <strong class="brand-name">Torneos</strong>
      </div>
      <nav class="site-nav">
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar sesión</a>
      </nav>
    </div>
  </header>

  <main class="torneos">
    <div class="container">
      <h1>Próximos Torneos</h1>

      <?php if ($mensaje): ?>
        <div class="alert alert-success"><?=htmlspecialchars($mensaje)?></div>
      <?php endif; ?>

      <?php if (!$torneos): ?>
        <p>No hay torneos activos en este momento.</p>
      <?php else: ?>
        <ul class="torneos-list">
          <?php foreach ($torneos as $t): ?>
            <li class="torneo-item">
              <h2><?=htmlspecialchars($t['NOMBRE'])?></h2>
              <?php if ($t['DESCRIPCION']): ?>
                <p><?=htmlspecialchars($t['DESCRIPCION'])?></p>
              <?php endif; ?>
              <p><strong>Fecha:</strong> <?=htmlspecialchars($t['FECHA'])?> 
                 <?php if ($t['LUGAR']): ?><span>•</span> <strong>Lugar:</strong> <?=htmlspecialchars($t['LUGAR'])?><?php endif;?>
              </p>
              <form method="post" class="inscripcion-form">
                <input type="hidden" name="action" value="inscribir">
                <input type="hidden" name="torneo" value="<?=$t['ID_TORNEO']?>">
                <label>Categoría:
                  <input name="categoria" type="text" placeholder="Infantil/Juvenil/Adulto" required>
                </label>
                <label>Modalidad:
                  <input name="modalidad" type="text" placeholder="Poomsae/Combate" required>
                </label>
                <button type="submit" class="btn btn-primary">Inscribirme</button>
              </form>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>
  </main>

  <footer class="site-footer">
    <div class="container">
      <div class="footer-meta">© <?=date('Y')?> MDK Boyacá · Todos los derechos reservados</div>
      <nav class="footer-nav">
        <a href="index.php">Inicio</a>
        <a href="tienda.php">Tienda</a>
      </nav>
    </div>
  </footer>
</body>
</html>
