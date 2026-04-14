<?php
/**
 * historial_cinturon_ajax.php
 * GET  ?id=X           → Devuelve tabla HTML del historial (sin <script>)
 * POST action=agregar  → Inserta una fila nueva
 * POST action=eliminar → Elimina una fila por ID_HISTORIAL
 */
require __DIR__ . '/config.php';
require __DIR__ . '/auth.php';
require_login('admin');

// ── POST ─────────────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');
    $action = trim($_POST['action'] ?? '');

    if ($action === 'agregar') {
        $id_persona  = (int) ($_POST['id_persona']    ?? 0);
        $id_cinturon = (int) ($_POST['id_cinturon']   ?? 0);
        $fecha       = trim($_POST['fecha_obtencion'] ?? '');
        $recibo      = trim($_POST['numero_recibo']   ?? '');
        $promocion   = trim($_POST['promocion']       ?? '');

        if ($id_persona <= 0 || $id_cinturon <= 0 || $fecha === '') {
            echo json_encode(['success' => false, 'message' => 'Faltan campos obligatorios.']);
            exit;
        }
        $dt = DateTime::createFromFormat('Y-m-d', $fecha);
        if (!$dt) {
            echo json_encode(['success' => false, 'message' => 'Fecha inválida.']);
            exit;
        }
        $st = mysqli_prepare($mysqli,
            "INSERT INTO historial_cinturon (ID_PERSONA, ID_CINTURON, FECHA_OBTENCION, NUMERO_RECIBO, PROMOCION)
             VALUES (?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($st, 'iisss', $id_persona, $id_cinturon, $fecha, $recibo, $promocion);
        $ok     = mysqli_stmt_execute($st);
        $new_id = mysqli_insert_id($mysqli);
        mysqli_stmt_close($st);
        echo json_encode(['success' => $ok, 'new_id' => $new_id]);
        exit;
    }

    if ($action === 'eliminar') {
        $id_historial = (int) ($_POST['id_historial'] ?? 0);
        if ($id_historial <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID inválido.']);
            exit;
        }
        $st = mysqli_prepare($mysqli, "DELETE FROM historial_cinturon WHERE ID_HISTORIAL = ?");
        mysqli_stmt_bind_param($st, 'i', $id_historial);
        $ok = mysqli_stmt_execute($st);
        mysqli_stmt_close($st);
        echo json_encode(['success' => $ok]);
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'Acción no reconocida.']);
    exit;
}

// ── GET: devuelve solo HTML (sin <script>) ────────────────────────────────────
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    echo '<p class="text-danger">ID inválido.</p>';
    exit;
}

// Cinturones disponibles
$cinturones_list = [];
$res_c = mysqli_query($mysqli, "SELECT ID_CINTURON, NOMBRE FROM cinturon ORDER BY ID_CINTURON ASC");
while ($r = mysqli_fetch_assoc($res_c)) {
    $cinturones_list[] = $r;
}

// Historial
$st = mysqli_prepare($mysqli, "
    SELECT h.ID_HISTORIAL, c.NOMBRE AS CINTURON, h.FECHA_OBTENCION,
           COALESCE(h.NUMERO_RECIBO, '') AS NUMERO_RECIBO,
           COALESCE(h.PROMOCION, '')     AS PROMOCION
    FROM historial_cinturon h
    JOIN cinturon c ON h.ID_CINTURON = c.ID_CINTURON
    WHERE h.ID_PERSONA = ?
    ORDER BY h.FECHA_OBTENCION ASC
");
mysqli_stmt_bind_param($st, 'i', $id);
mysqli_stmt_execute($st);
$res  = mysqli_stmt_get_result($st);
$rows = [];
while ($row = mysqli_fetch_assoc($res)) {
    $rows[] = $row;
}
mysqli_stmt_close($st);

// Formulario agregar
echo '<div class="mb-3 p-3 border rounded bg-white">';
echo '<h6 class="fw-bold mb-2">➕ Agregar grado</h6>';
echo '<div class="row g-2 align-items-end">';

echo '<div class="col-sm-3"><label class="form-label form-label-sm mb-1">Fecha <span class="text-danger">*</span></label>';
echo '<input type="date" id="hist-nueva-fecha" class="form-control form-control-sm"></div>';

echo '<div class="col-sm-3"><label class="form-label form-label-sm mb-1">Grado <span class="text-danger">*</span></label>';
echo '<select id="hist-nuevo-cinturon" class="form-select form-select-sm">';
foreach ($cinturones_list as $c) {
    echo '<option value="' . (int)$c['ID_CINTURON'] . '">' . htmlspecialchars($c['NOMBRE']) . '</option>';
}
echo '</select></div>';

echo '<div class="col-sm-2"><label class="form-label form-label-sm mb-1">Recibo No.</label>';
echo '<input type="text" id="hist-nuevo-recibo" class="form-control form-control-sm" placeholder="Opcional"></div>';

echo '<div class="col-sm-2"><label class="form-label form-label-sm mb-1">Promoc.</label>';
echo '<input type="text" id="hist-nueva-promocion" class="form-control form-control-sm" placeholder="Opcional"></div>';

// data-persona pasa el id al JS global sin inline onclick
echo '<div class="col-sm-2"><button class="btn btn-success btn-sm w-100" data-persona="' . $id . '" onclick="histAgregar(this.dataset.persona)">Agregar</button></div>';
echo '</div></div>';

// Tabla
if (count($rows) === 0) {
    echo '<p class="text-muted text-center mt-2">No hay historial de grados registrado.</p>';
} else {
    echo '<div class="table-responsive">';
    echo '<table class="table table-striped table-sm align-middle" id="tabla-historial-cinturones">';
    echo '<thead class="table-dark"><tr>';
    echo '<th>Fecha</th><th>Grado</th><th>Recibo No.</th><th>Promoc.</th><th></th>';
    echo '</tr></thead><tbody>';
    foreach ($rows as $row) {
        $hid  = (int) $row['ID_HISTORIAL'];
        $cint = htmlspecialchars($row['CINTURON']);
        $fecha = htmlspecialchars($row['FECHA_OBTENCION']);
        $rec  = htmlspecialchars($row['NUMERO_RECIBO']);
        $prom = htmlspecialchars($row['PROMOCION']);
        $fechaDisplay = $fecha;
        $dt = DateTime::createFromFormat('Y-m-d', $fecha);
        if ($dt) $fechaDisplay = $dt->format('d/m/Y');
        echo "<tr id=\"hist-row-{$hid}\">";
        echo "<td>{$fechaDisplay}</td>";
        echo "<td>{$cint}</td>";
        echo "<td>" . ($rec  ?: '<span class="text-muted">-</span>') . "</td>";
        echo "<td>" . ($prom ?: '<span class="text-muted">-</span>') . "</td>";
        echo "<td><button class=\"btn btn-danger btn-sm\" data-hid=\"{$hid}\" onclick=\"histEliminar(this.dataset.hid)\" title=\"Eliminar\">✕</button></td>";
        echo "</tr>";
    }
    echo '</tbody></table></div>';
}
// ── SIN <script> aquí: las funciones histAgregar/histEliminar
//    están definidas globalmente en temp/modal_admin.php
