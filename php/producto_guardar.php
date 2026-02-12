<?php
require __DIR__ . '/config.php';

if (ob_get_level()) ob_end_clean();
header('Content-Type: application/json; charset=utf-8');
error_reporting(0);

$response = ['success' => false, 'message' => 'Error desconocido'];

try {
    if (!$mysqli) throw new Exception("Sin conexión BD");

    $accion = $_POST['accion'] ?? 'guardar';
    $id = isset($_POST['id']) && $_POST['id'] !== '' ? (int)$_POST['id'] : 0;

    // ------------------ ELIMINAR ------------------
    if ($accion === 'eliminar') {
        if ($id <= 0) throw new Exception("ID inválido");

        // Obtener URL(s) asociadas y borrar archivos
        $resImgs = mysqli_query($mysqli, "SELECT URL FROM producto_imagen WHERE ID_PRODUCTO = $id");
        while ($r = mysqli_fetch_assoc($resImgs)) {
            if (!empty($r['URL'])) {
                $rutaF = __DIR__ . '/../' . $r['URL'];
                if (file_exists($rutaF)) @unlink($rutaF);
            }
        }

        // Borrar filas de imagenes y producto
        mysqli_query($mysqli, "DELETE FROM producto_imagen WHERE ID_PRODUCTO = $id");
        $stmt = mysqli_prepare($mysqli, "DELETE FROM productos WHERE ID_PRODUCTO = ?");
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);

        $response = ['success' => true, 'message' => 'Producto eliminado correctamente'];
        echo json_encode($response);
        exit;
    }

    // ------------------ GUARDAR / EDITAR ------------------
    $nombre = trim($_POST['nombre'] ?? '');
    $desc   = trim($_POST['descripcion'] ?? '');
    $precio = (float)($_POST['precio'] ?? 0);
    $stock  = (int)($_POST['stock'] ?? 0);
    $cat    = (int)($_POST['categoria'] ?? 1);

    if ($nombre === '') throw new Exception("El nombre es obligatorio");

    // Traer la imagen actual (si el modal la envía)
    $imagenActual = trim($_POST['imagen_actual'] ?? '');

    // Si se subió archivo nuevo, procesarlo
    $rutaNueva = ''; // ruta relativa para insertar en producto_imagen
    if (!empty($_FILES['imagen']['name']) && $_FILES['imagen']['error'] === 0) {
        $ext = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
        $permitidos = ['jpg','jpeg','png','gif','webp'];
        if (!in_array($ext, $permitidos)) throw new Exception("Formato de imagen no válido");

        $dir = __DIR__ . '/../uploads';
        if (!is_dir($dir)) mkdir($dir, 0777, true);

        $nuevoNombre = uniqid('prod_') . '.' . $ext;
        $destino = $dir . '/' . $nuevoNombre;

        if (!move_uploaded_file($_FILES['imagen']['tmp_name'], $destino)) {
            throw new Exception("Error al mover el archivo subido");
        }

        $rutaNueva = 'uploads/' . $nuevoNombre;
    }

    if ($id > 0) {
        // EDITAR datos de producto
        $sql = "UPDATE productos SET NOMBRE=?, DESCRIPCION=?, PRECIO=?, STOCK=?, ID_CATEGORIA=? WHERE ID_PRODUCTO=?";
        $stmt = mysqli_prepare($mysqli, $sql);
        mysqli_stmt_bind_param($stmt, 'ssdiii', $nombre, $desc, $precio, $stock, $cat, $id);
        mysqli_stmt_execute($stmt);

        // Si se subió nueva imagen: insertar en producto_imagen y borrar anterior archivo físico (si existe)
        if ($rutaNueva !== '') {
            // obtener url anterior (última) para borrar archivo físico
            $resPrev = mysqli_query($mysqli, "SELECT URL FROM producto_imagen WHERE ID_PRODUCTO = $id ORDER BY ID_IMAGEN DESC LIMIT 1");
            if ($rowPrev = mysqli_fetch_assoc($resPrev)) {
                if (!empty($rowPrev['URL'])) {
                    $rutaAnt = __DIR__ . '/../' . $rowPrev['URL'];
                    if (file_exists($rutaAnt)) @unlink($rutaAnt);
                }
            }

            // Insertar nueva fila de imagen
            $stmt2 = mysqli_prepare($mysqli, "INSERT INTO producto_imagen (ID_PRODUCTO, URL) VALUES (?, ?)");
            mysqli_stmt_bind_param($stmt2, 'is', $id, $rutaNueva);
            mysqli_stmt_execute($stmt2);
            mysqli_stmt_close($stmt2);
        }

    } else {
        // INSERTAR nuevo producto
        $sql = "INSERT INTO productos (NOMBRE, DESCRIPCION, PRECIO, STOCK, ID_CATEGORIA) VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($mysqli, $sql);
        mysqli_stmt_bind_param($stmt, 'ssdii', $nombre, $desc, $precio, $stock, $cat);
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Error en inserción producto: " . mysqli_stmt_error($stmt));
        }
        $id_creado = mysqli_insert_id($mysqli);
        mysqli_stmt_close($stmt);

        // Si se subió imagen, insertarla en producto_imagen
        if ($rutaNueva !== '') {
            $stmt2 = mysqli_prepare($mysqli, "INSERT INTO producto_imagen (ID_PRODUCTO, URL) VALUES (?, ?)");
            mysqli_stmt_bind_param($stmt2, 'is', $id_creado, $rutaNueva);
            mysqli_stmt_execute($stmt2);
            mysqli_stmt_close($stmt2);
        }
    }

    $response = ['success' => true, 'message' => 'Guardado correctamente'];

} catch (Exception $e) {
    $response = ['success' => false, 'message' => $e->getMessage()];
}

echo json_encode($response);
exit;
?>