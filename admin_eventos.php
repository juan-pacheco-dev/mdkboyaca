<?php
require __DIR__ . '/php/config.php';
require __DIR__ . '/php/auth.php';
require_login('admin');

// Procedural connection alias
$conexion = $mysqli;

// Obtener eventos ordenados ascendente por fecha
$sql = "SELECT ID_EVENTO, NOMBRE, DESCRIPCION, FECHA, LUGAR FROM evento ORDER BY FECHA ASC";
$result = mysqli_query($conexion, $sql);
$eventos = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $eventos[] = $row;
    }
}

// Función para escapar caracteres HTML
function esc($s) {
    return htmlspecialchars((string)($s ?? ''), ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Gestión de Eventos</title>
<link rel="stylesheet" href="style.css">
<link rel="stylesheet" href="eventos.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<?php include './temp/header_eventos.php'; ?>

<div class="evento-wrapper">
  <div class="evento-header">
    <h2>📅 Gestión de Eventos</h2>
  </div>

  <!-- LISTA DE EVENTOS -->
  <div class="card evento-list">
    <h3><i class="fa fa-calendar"></i> Eventos Programados</h3>

    <?php if (empty($eventos)): ?>
      <p>No hay eventos registrados.</p>
    <?php else: ?>
      <?php foreach($eventos as $ev): ?>
      <div class="evento-item">
        <div class="evento-info">
          <div class="evento-nombre"><?= esc($ev['NOMBRE']) ?></div>
          <div class="evento-sub">
            <i class="fa fa-map-marker-alt"></i> <?= esc($ev['LUGAR']) ?> 
            &nbsp;•&nbsp; 
            <i class="fa fa-clock"></i> <?= esc($ev['FECHA']) ?>
          </div>
          <div class="evento-desc"><?= esc($ev['DESCRIPCION']) ?></div>
        </div>
        <div class="evento-actions">
          <button class="btn-outline small" onclick="editarEvento(<?= (int)$ev['ID_EVENTO'] ?>)">
            <i class="fa fa-edit"></i>
          </button>
          <button class="btn-danger small" onclick="eliminarEvento(<?= (int)$ev['ID_EVENTO'] ?>)">
            <i class="fa fa-trash"></i>
          </button>
        </div>
      </div>
      <?php endforeach; ?>
    <?php endif; ?>
    <button id="btn-agregar" class="btn-primary"><i class="fa fa-plus"></i> Nuevo Evento</button>
  </div>

  <!-- FORMULARIO -->
  <div class="card evento-form">
    <h3>📝 Crear / Editar Evento</h3>
    <form id="form-evento">
      <input type="hidden" id="id_evento" name="id_evento">
      <label>Nombre del evento</label>
      <input type="text" id="nombre" name="nombre" required>
      <label>Fecha</label>
      <input type="date" id="fecha" name="fecha" required>
      <label>Lugar</label>
      <input type="text" id="lugar" name="lugar">
      <label>Descripción</label>
      <textarea id="descripcion" name="descripcion"></textarea>
      <button type="submit" class="btn-primary full"><i class="fa fa-save"></i> Guardar</button>
      <button type="button" id="btn-reset" class="btn-secondary full">Limpiar</button>
      
    </form>

    <br>
    <br>
    <a href="admin.php" class="volver">Volver</a>
  </div>
</div>

<?php include './temp/footer.php'; ?>

<script>
// Reset form
document.getElementById('btn-agregar').onclick = ()=> {
  document.getElementById('id_evento').value='';
  document.getElementById('form-evento').reset();
  window.scrollTo({top:document.body.scrollHeight,behavior:'smooth'});
};

document.getElementById('btn-reset').onclick = ()=>document.getElementById('btn-agregar').click();

function editarEvento(id){
  fetch('php/get_evento.php?id='+id)
  .then(r=>r.json()).then(d=>{
    if(!d.success) return alert("Error");
    let e=d.evento;
    id_evento.value=e.ID_EVENTO;
    nombre.value=e.NOMBRE;
    fecha.value=e.FECHA;
    lugar.value=e.LUGAR;
    descripcion.value=e.DESCRIPCION;
    window.scrollTo({top:document.body.scrollHeight,behavior:'smooth'});
  });
}

function eliminarEvento(id){
  if(!confirm("¿Eliminar evento?")) return;
  fetch('php/eventos_eliminar.php',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:'id_evento='+id})
  .then(r=>r.json()).then(j=>{ if(j.success) location.reload(); });
}

document.getElementById("form-evento").onsubmit = e=>{
  e.preventDefault();
  fetch("php/eventos_guardar.php",{method:"POST",body:new FormData(e.target)})
  .then(r=>r.json()).then(j=>{
    if(j.success){
      location.reload();
    } else {
      alert("Error guardando");
    }
  });
};
</script>

</body>
</html>
