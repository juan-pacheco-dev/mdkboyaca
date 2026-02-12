<?php
require __DIR__ . '/config.php';
header('Content-Type: application/json; charset=UTF-8');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    echo json_encode(['error' => 'ID inválido']);
    exit;
}

$sql = "SELECT ID_PRODUCTO, NOMBRE, DESCRIPCION, PRECIO, STOCK
        FROM productos
        WHERE ID_PRODUCTO = ?
        LIMIT 1";

$stmt = mysqli_prepare($mysqli, $sql);
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);

if (!$row) {
    echo json_encode(['error' => 'Producto no encontrado']);
    exit;
}

echo json_encode($row);
?>
