<?php
/**
 * historial_cinturon_ajax.php
 * GET  ?id=X           → Devuelve tabla HTML del historial ordenada por fecha ASC
 * POST action=agregar  → Inserta una fila nueva
 * POST action=eliminar → Elimina una fila por idhistorial
 */
require __DIR__ . '/config.php';
require __DIR__ . '/auth.php';
require_login('admin');

// ── POST: agregar o eliminar ──────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');
    $action = trim($_POST['action'] ?? '');

    if ($action === 'agregar') {
        $id_persona  = (int) ($_POST['id_persona']   ?? 0);
        $id_cinturon = (int) ($_POST['id_cinturon']  ?? 0);
        $fecha       = trim($_POST['fecha_obtencion'] ?? '');
        $recibo      = trim($_POST['numero_recibo']   ?? '');
        $promocion   = trim($_POST['promocion']       ?? '');

        if ($id_persona <= 0 || $id_cinturon <= 0 || $fecha === '') {
            echo json_encode(['success' => false, 'message' => 'Faltan campos obligatorios.']);
            exit;
        }

        // Validar formato fecha
        $dt = DateTime::createFromFormat('Y-m-d', $fecha);
        if (!$dt) {
            echo json_encode(['success' => false, 'message' => 'Fecha inválida.']);
            exit;
        }

        $st = mysqli_prepare($mysqli,
            "INSERT INTO historial_cinturon (ID_PERSONA, ID_CINTURON, FECHA_OBTENCION, NUMERO_RECIBO, PROMOCION)
             VALUES (?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($st, 'iisss', $id_persona, $id_cinturon, $fecha, $recibo, $promocion);
        $ok = mysqli_stmt_execute($st);
        $new_id = mysqli_insert_id($mysqli);
        mysqli_stmt_close($st);

        echo json_encode(['success' => $ok, 'new_id' => $new_id]);
        exit;
    }

    if ($action === 'eliminar') {
        $idhistorial = (int) ($_POST['idhistorial'] ?? 0);
        if ($idhistorial <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID inválido.']);
            exit;
        }
        $st = mysqli_prepare($mysqli, "DELETE FROM historial_cinturon WHERE idhistorial = ?");
        mysqli_stmt_bind_param($st, 'i', $idhistorial);
        $ok = mysqli_stmt_execute($st);
        mysqli_stmt_close($st);
        echo json_encode(['success' => $ok]);
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'Acción no reconocida.']);
    exit;
}

// ── GET: renderizar tabla HTML ────────────────────────────────────────────────
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    echo '<p class="text-danger">ID inválido.</p>';
    exit;
}

// Traer todos los cinturones disponibles para el select de agregar
$cinturones_list = [];
$res_c = mysqli_query($mysqli, "SELECT ID_CINTURON, NOMBRE FROM cinturon ORDER BY ID_CINTURON ASC");
while ($r = mysqli_fetch_assoc($res_c)) {
    $cinturones_list[] = $r;
}

// Traer historial ordenado por fecha ASC (más antiguo arriba, igual que el carnet físico)
$st = mysqli_prepare($mysqli, "
    SELECT h.idhistorial, c.NOMBRE AS CINTURON, h.FECHA_OBTENCION,
           COALESCE(h.NUMERO_RECIBO, '') AS NUMERO_RECIBO,
           COALESCE(h.PROMOCION, '')     AS PROMOCION
    FROM historial_cinturon h
    JOIN cinturon c ON h.ID_CINTURON = c.ID_CINTURON
    WHERE h.ID_PERSONA = ?
    ORDER BY h.FECHA_OBTENCION ASC
");
mysqli_stmt_bind_param($st, 'i', $id);
mysqli_stmt_execute($st);
$res = mysqli_stmt_get_result($st);
$rows = [];
while ($row = mysqli_fetch_assoc($res)) {
    $rows[] = $row;
}
mysqli_stmt_close($st);

// ── Formulario para agregar nueva fila ───────────────────────────────────────
echo '<div class="mb-3 p-3 border rounded bg-white">';
echo '<h6 class="fw-bold mb-2">➕ Agregar grado</h6>';
echo '<div class="row g-2 align-items-end">';

// Fecha
echo '<div class="col-sm-3"><label class="form-label form-label-sm mb-1">Fecha <span class="text-danger">*</span></label>';
echo '<input type="date" id="hist-nueva-fecha" class="form-control form-control-sm"></div>';

// Cinturón
echo '<div class="col-sm-3"><label class="form-label form-label-sm mb-1">Grado <span class="text-danger">*</span></label>';
echo '<select id="hist-nuevo-cinturon" class="form-select form-select-sm">';
foreach ($cinturones_list as $c) {
    echo '<option value="' . (int)$c['ID_CINTURON'] . '">' . htmlspecialchars($c['NOMBRE']) . '</option>';
}
echo '</select></div>';

// Recibo
echo '<div class="col-sm-2"><label class="form-label form-label-sm mb-1">Recibo No.</label>';
echo '<input type="text" id="hist-nuevo-recibo" class="form-control form-control-sm" placeholder="Opcional"></div>';

// Promoción
echo '<div class="col-sm-2"><label class="form-label form-label-sm mb-1">Promoc.</label>';
echo '<input type="text" id="hist-nueva-promocion" class="form-control form-control-sm" placeholder="Opcional"></div>';

// Botón
echo '<div class="col-sm-2"><button class="btn btn-success btn-sm w-100" onclick="histAgregar(' . $id . ')">Agregar</button></div>';
echo '</div></div>';

// ── Tabla de historial ────────────────────────────────────────────────────────
if (count($rows) === 0) {
    echo '<p class="text-muted text-center mt-2">No hay historial de grados registrado.</p>';
} else {
    echo '<div class="table-responsive">';
    echo '<table class="table table-striped table-sm align-middle" id="tabla-historial-cinturones">';
    echo '<thead class="table-dark"><tr>';
    echo '<th>Fecha</th><th>Grado</th><th>Recibo No.</th><th>Promoc.</th><th></th>';
    echo '</tr></thead><tbody>';

    foreach ($rows as $row) {
        $hid   = (int) $row['idhistorial'];
        $cint  = htmlspecialchars($row['CINTURON']);
        $fecha = htmlspecialchars($row['FECHA_OBTENCION']);
        $rec   = htmlspecialchars($row['NUMERO_RECIBO']);
        $prom  = htmlspecialchars($row['PROMOCION']);

        // Formatear fecha a formato legible dd/mm/YYYY
        $fechaDisplay = $fecha;
        $dt = DateTime::createFromFormat('Y-m-d', $fecha);
        if ($dt) $fechaDisplay = $dt->format('d/m/Y');

        echo "<tr id=\"hist-row-{$hid}\">";
        echo "<td>{$fechaDisplay}</td>";
        echo "<td>{$cint}</td>";
        echo "<td>" . ($rec ?: '<span class="text-muted">-</span>') . "</td>";
        echo "<td>" . ($prom ?: '<span class="text-muted">-</span>') . "</td>";
        echo "<td><button class=\"btn btn-danger btn-sm\" onclick=\"histEliminar({$hid})\" title=\"Eliminar\">✕</button></td>";
        echo "</tr>";
    }
    echo '</tbody></table></div>';
}
?>

<script>
function histAgregar(idPersona) {
  const fecha    = document.getElementById('hist-nueva-fecha').value;
  const cinturon = document.getElementById('hist-nuevo-cinturon').value;
  const recibo   = document.getElementById('hist-nuevo-recibo').value;
  const promoc   = document.getElementById('hist-nueva-promocion').value;

  if (!fecha || !cinturon) {
    alert('La fecha y el grado son obligatorios.');
    return;
  }

  const fd = new FormData();
  fd.append('action',          'agregar');
  fd.append('id_persona',      idPersona);
  fd.append('id_cinturon',     cinturon);
  fd.append('fecha_obtencion', fecha);
  fd.append('numero_recibo',   recibo);
  fd.append('promocion',       promoc);

  fetch('php/historial_cinturon_ajax.php', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        // Recargar el historial dentro del modal
        verHistorialCinturones(idPersona);
      } else {
        alert('Error: ' + (data.message || 'No se pudo agregar.'));
      }
    })
    .catch(() => alert('Error de red al agregar grado.'));
}

function histEliminar(idHistorial) {
  if (!confirm('¿Eliminar este registro de grado?')) return;

  const fd = new FormData();
  fd.append('action',      'eliminar');
  fd.append('idhistorial', idHistorial);

  fetch('php/historial_cinturon_ajax.php', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        const row = document.getElementById('hist-row-' + idHistorial);
        if (row) row.remove();
        // Si la tabla quedó vacía, mostrar mensaje
        const tbody = document.querySelector('#tabla-historial-cinturones tbody');
        if (tbody && tbody.rows.length === 0) {
          document.querySelector('.table-responsive').outerHTML =
            '<p class="text-muted text-center mt-2">No hay historial de grados registrado.</p>';
        }
      } else {
        alert('Error al eliminar el registro.');
      }
    })
    .catch(() => alert('Error de red al eliminar.'));
}
</script>
