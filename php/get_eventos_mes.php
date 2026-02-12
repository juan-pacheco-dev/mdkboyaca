<?php
require __DIR__ . '/config.php';

$mes = $_GET['mes'] ?? date('Y-m');

// Validar formato YYYY-MM
if (!preg_match('/^\d{4}-\d{2}$/', $mes)) {
    $mes = date('Y-m');
}

// Procedural prepare statement
$st = mysqli_prepare($mysqli, "
    SELECT NOMBRE, FECHA, LUGAR, DESCRIPCION
    FROM evento
    WHERE DATE_FORMAT(FECHA,'%Y-%m') = ?
    ORDER BY FECHA ASC
");
mysqli_stmt_bind_param($st, 's', $mes);
mysqli_stmt_execute($st);
$res = mysqli_stmt_get_result($st);
$eventos = mysqli_fetch_all($res, MYSQLI_ASSOC);
mysqli_stmt_close($st);

header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    'success' => true,
    'mes' => $mes,
    'eventos' => $eventos
]);
?>
