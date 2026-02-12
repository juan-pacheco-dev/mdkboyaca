<?php
require __DIR__ . '/config.php';
header('Content-Type: application/json');

$id = (int)($_POST['id'] ?? 0);
$nombre = trim($_POST['nombre'] ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');
$precio = (float)($_POST['precio'] ?? 0);
$stock = (int)($_POST['stock'] ?? 0);

if ($id <= 0 || $nombre === '') {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos.']);
    exit;
}

// === Actualizar datos del producto ===
$st = mysqli_prepare($mysqli, "
    UPDATE productos
    SET NOMBRE=?, DESCRIPCION=?, PRECIO=?, STOCK=?
    WHERE ID_PRODUCTO=?
");
mysqli_stmt_bind_param($st, 'ssdii', $nombre, $descripcion, $precio, $stock, $id);
$ok = mysqli_stmt_execute($st);
mysqli_stmt_close($st);

// === Subir imagen (opcional) ===
if (!empty($_FILES['imagen']['name'])) {
    $ext = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
    $permitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    if (in_array($ext, $permitidas) && is_uploaded_file($_FILES['imagen']['tmp_name'])) {
        $destDir = __DIR__ . '/../uploads';
        if (!is_dir($destDir)) mkdir($destDir, 0775, true);
        $newName = uniqid('producto_') . '.' . $ext;
        $destPath = $destDir . '/' . $newName;

        if (move_uploaded_file($_FILES['imagen']['tmp_name'], $destPath)) {
            $url = 'uploads/' . $newName;
            // Guardar la imagen asociada al producto
            $stImg = mysqli_prepare($mysqli, "
                INSERT INTO producto_imagen (ID_PRODUCTO, URL)
                VALUES (?, ?)
            ");
            mysqli_stmt_bind_param($stImg, 'is', $id, $url);
            mysqli_stmt_execute($stImg);
            mysqli_stmt_close($stImg);
        }
    }
}

echo json_encode([
    'success' => $ok,
    'message' => $ok ? 'Producto actualizado correctamente.' : 'Error al actualizar producto.'
]);
?>
