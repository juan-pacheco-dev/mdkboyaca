<?php
require __DIR__.'/config.php';
require __DIR__.'/auth.php';
require_login('admin');

$id = (int)($_POST['id_evento'] ?? 0);
if (!$id) { 
    echo json_encode(['success'=>false,'message'=>'id faltante']); 
    exit; 
}

// Procedural style prepare statement for deletion
$st = mysqli_prepare($mysqli, "DELETE FROM evento WHERE ID_EVENTO=?");
mysqli_stmt_bind_param($st, 'i', $id);
$ok = mysqli_stmt_execute($st);
mysqli_stmt_close($st);

echo json_encode(['success'=>$ok]);
?>
