<?php
require __DIR__.'/config.php';
require __DIR__.'/auth.php';
// accesible incluso para no-admin si tu calendario es visible -> no require_login aqui o usa require_login()
$mes = trim($_GET['mes'] ?? date('Y-m'));
if (!preg_match('/^\d{4}-\d{2}$/', $mes)) $mes = date('Y-m');

$st = mysqli_prepare($mysqli, "SELECT ID_EVENTO, NOMBRE, DESCRIPCION, FECHA, LUGAR FROM evento WHERE DATE_FORMAT(FECHA,'%Y-%m') = ? ORDER BY FECHA ASC");
mysqli_stmt_bind_param($st, 's', $mes);
mysqli_stmt_execute($st);
$res = mysqli_stmt_get_result($st);
$rows = mysqli_fetch_all($res, MYSQLI_ASSOC);
mysqli_stmt_close($st);

header('Content-Type: application/json; charset=utf-8');
echo json_encode(['success'=>true,'mes'=>$mes,'eventos'=>$rows]);
?>
