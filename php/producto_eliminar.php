<?php
require __DIR__ . '/config.php';
header('Content-Type: application/json');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID inválido']);
    exit;
}

// Procedural: Eliminar imágenes asociadas
$imgs = mysqli_prepare($mysqli, "SELECT URL FROM producto_imagen WHERE ID_PRODUCTO=?");
mysqli_stmt_bind_param($imgs, 'i', $id);
mysqli_stmt_execute($imgs);
$res = mysqli_stmt_get_result($imgs);
while ($img = mysqli_fetch_assoc($res)) {
    $path = __DIR__ . '/../' . $img['URL'];
    if (file_exists($path)) unlink($path);
}
mysqli_stmt_close($imgs);

mysqli_query($mysqli, "DELETE FROM producto_imagen WHERE ID_PRODUCTO=$id");

// Procedural: Eliminar producto
$st = mysqli_prepare($mysqli, "DELETE FROM productos WHERE ID_PRODUCTO=?");
mysqli_stmt_bind_param($st, 'i', $id);
$ok = mysqli_stmt_execute($st);
mysqli_stmt_close($st);

echo json_encode([
    'success' => $ok,
    'message' => $ok ? 'Producto eliminado correctamente.' : 'Error al eliminar producto.'
]);
?>
