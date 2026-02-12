<?php
require __DIR__ . '/config.php';

if (ob_get_level()) ob_end_clean();
header('Content-Type: application/json; charset=utf-8');
error_reporting(0);

$response = ['success' => false, 'message' => 'Error al obtener datos'];

try {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    if ($id <= 0) throw new Exception("ID de producto inválido");

    $query = "
      SELECT p.ID_PRODUCTO, p.NOMBRE, p.DESCRIPCION, p.PRECIO, p.STOCK, p.ID_CATEGORIA,
             (SELECT URL FROM producto_imagen WHERE ID_PRODUCTO = p.ID_PRODUCTO ORDER BY ID_IMAGEN DESC LIMIT 1) AS IMAGEN
      FROM productos p
      WHERE p.ID_PRODUCTO = ?
      LIMIT 1
    ";

    $stmt = mysqli_prepare($mysqli, $query);
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $producto = mysqli_fetch_assoc($res);

    if ($producto) {
        $response = ['success' => true, 'data' => $producto];
    } else {
        throw new Exception("Producto no encontrado");
    }

    mysqli_stmt_close($stmt);

} catch (Exception $e) {
    $response = ['success' => false, 'message' => $e->getMessage()];
}

echo json_encode($response);
?>