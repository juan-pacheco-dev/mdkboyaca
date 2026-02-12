<?php
require __DIR__.'/config.php';
require __DIR__.'/auth.php';
require_login('admin');

$id = isset($_POST['id_evento']) && $_POST['id_evento'] !== '' ? (int)$_POST['id_evento'] : 0;
$nombre = trim($_POST['nombre'] ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');
$fecha = trim($_POST['fecha'] ?? '');
$lugar = trim($_POST['lugar'] ?? '');

if ($nombre === '' || $fecha === ''){
  echo json_encode(['success'=>false,'message'=>'Completa nombre y fecha']); exit;
}

// validar formato fecha YYYY-MM-DD
$d = DateTime::createFromFormat('Y-m-d', $fecha);
if (!$d || $d->format('Y-m-d') !== $fecha){
  echo json_encode(['success'=>false,'message'=>'Formato de fecha inválido']); exit;
}

if ($id){
  $st = mysqli_prepare($mysqli, "UPDATE evento SET NOMBRE=?, DESCRIPCION=?, FECHA=?, LUGAR=? WHERE ID_EVENTO=?");
  mysqli_stmt_bind_param($st, 'ssssi', $nombre, $descripcion, $fecha, $lugar, $id);
  $ok = mysqli_stmt_execute($st);
  mysqli_stmt_close($st);
  echo json_encode(['success'=>$ok]);
} else {
  $st = mysqli_prepare($mysqli, "INSERT INTO evento (NOMBRE,DESCRIPCION,FECHA,LUGAR) VALUES (?,?,?,?)");
  mysqli_stmt_bind_param($st, 'ssss', $nombre, $descripcion, $fecha, $lugar);
  $ok = mysqli_stmt_execute($st);
  $id_new = mysqli_insert_id($mysqli);
  mysqli_stmt_close($st);
  echo json_encode(['success'=>$ok, 'id'=>$id_new]);
}
?>
