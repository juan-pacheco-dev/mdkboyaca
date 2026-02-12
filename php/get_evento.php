<?php
require __DIR__.'/config.php';
require __DIR__.'/auth.php';
require_login('admin');

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    echo json_encode(['success'=>false,'message'=>'id faltante']);
    exit;
}

$st = mysqli_prepare($mysqli, "SELECT ID_EVENTO, NOMBRE, DESCRIPCION, FECHA, LUGAR FROM evento WHERE ID_EVENTO=? LIMIT 1");
mysqli_stmt_bind_param($st, 'i', $id);
mysqli_stmt_execute($st);
$res = mysqli_stmt_get_result($st);
$ev = mysqli_fetch_assoc($res);
mysqli_stmt_close($st);

if (!$ev) {
    echo json_encode(['success'=>false,'message'=>'No encontrado']);
    exit;
}

echo json_encode(['success'=>true,'evento'=>$ev]);
?>
