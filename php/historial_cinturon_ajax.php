<?php
require __DIR__ . '/config.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    echo '<p class="text-danger">ID inválido.</p>';
    exit;
}

// Procedural prepare statement
$st = mysqli_prepare($mysqli, "
    SELECT c.NOMBRE AS CINTURON, h.FECHA_OBTENCION
    FROM historial_cinturon h
    JOIN cinturon c ON h.ID_CINTURON = c.ID_CINTURON
    WHERE h.ID_PERSONA = ?
    ORDER BY h.FECHA_OBTENCION DESC
");
mysqli_stmt_bind_param($st, 'i', $id);
mysqli_stmt_execute($st);
$res = mysqli_stmt_get_result($st);

if (mysqli_num_rows($res) === 0) {
    echo '<p class="text-muted text-center">No hay historial de cinturones registrado.</p>';
    exit;
}

// Tabla HTML de resultados
echo '<table class="table table-striped align-middle">';
echo '<thead class="table-dark"><tr><th>Cinturón</th><th>Fecha de obtención</th></tr></thead><tbody>';

while ($row = mysqli_fetch_assoc($res)) {
    $cint = htmlspecialchars($row['CINTURON']);
    $fecha = htmlspecialchars($row['FECHA_OBTENCION']);
    echo "<tr><td>$cint</td><td>$fecha</td></tr>";
}

echo '</tbody></table>';
mysqli_stmt_close($st);
?>
