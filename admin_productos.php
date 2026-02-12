<?php
require __DIR__ . '/php/config.php';
require __DIR__ . '/php/auth.php';
require_login('admin');

$conexion = $mysqli;

function esc($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

/* ======================================================
   PROCESAMIENTO POST (AGREGAR O EDITAR)
====================================================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $accion = $_POST['accion'] ?? '';

    /* -----------------------------------------
       AGREGAR O EDITAR
    ----------------------------------------- */
    if ($accion === 'guardar') {

        $id = isset($_POST['id_producto']) ? (int)$_POST['id_producto'] : 0;

        $nombre       = trim($_POST['nombre'] ?? '');
        $descripcion  = trim($_POST['descripcion'] ?? 'Sin descripción');
        $precio       = (float)($_POST['precio'] ?? 0);
        $stock        = (int)($_POST['stock'] ?? 0);

        if ($nombre === '') {
            $nombre = 'Producto sin nombre ' . date('His');
        }

        /* -----------------------------------------
           SI ES NUEVO → INSERTAR
        ----------------------------------------- */
        if ($id === 0) {

            $query = "INSERT INTO productos (NOMBRE, DESCRIPCION, PRECIO, STOCK)
                      VALUES (?, ?, ?, ?)";
            $stmt = mysqli_prepare($conexion, $query);
            mysqli_stmt_bind_param($stmt, 'ssdi', $nombre, $descripcion, $precio, $stock);
            mysqli_stmt_execute($stmt);

            $id = mysqli_insert_id($conexion);
            mysqli_stmt_close($stmt);

        } else {

            /* -----------------------------------------
               SI EXISTE → ACTUALIZAR
            ----------------------------------------- */
            $query = "UPDATE productos
                      SET NOMBRE=?, DESCRIPCION=?, PRECIO=?, STOCK=?
                      WHERE ID_PRODUCTO=?";
            $stmt = mysqli_prepare($conexion, $query);
            mysqli_stmt_bind_param($stmt, 'ssdii', $nombre, $descripcion, $precio, $stock, $id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }

        /* -----------------------------------------
           PROCESAR IMAGEN SI SE SUBIÓ UNA NUEVA
        ----------------------------------------- */
        if (!empty($_FILES['imagen']['name'])) {

            $ext = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg','jpeg','png','gif','webp'])) {

                if (!is_dir('uploads')) {
                    mkdir('uploads', 0777, true);
                }

                $ruta = 'uploads/' . uniqid('prod_') . '.' . $ext;

                if (move_uploaded_file($_FILES['imagen']['tmp_name'], $ruta)) {

                    mysqli_query($conexion,
                        "INSERT INTO producto_imagen (ID_PRODUCTO, URL)
                         VALUES ($id, '$ruta')"
                    );
                }
            }
        }

        header("Location: admin_productos.php");
        exit;
    }

    /* -----------------------------------------
       ELIMINAR
    ----------------------------------------- */
    if ($accion === 'eliminar') {
        $id = (int)$_POST['id_producto'];
        mysqli_query($conexion, "DELETE FROM producto_imagen WHERE ID_PRODUCTO=$id");
        mysqli_query($conexion, "DELETE FROM productos WHERE ID_PRODUCTO=$id");
        header("Location: admin_productos.php");
        exit;
    }
}

/* ======================================================
   CONSULTA LISTA
====================================================== */
$sql = "SELECT p.ID_PRODUCTO, p.NOMBRE, p.DESCRIPCION, p.PRECIO, p.STOCK,
        (SELECT URL FROM producto_imagen WHERE ID_PRODUCTO=p.ID_PRODUCTO ORDER BY ID_IMAGEN DESC LIMIT 1) AS IMAGEN
        FROM productos p
        ORDER BY p.ID_PRODUCTO DESC";

$result = mysqli_query($conexion, $sql);

$productos = [];
while ($row = mysqli_fetch_assoc($result)) {
    $productos[] = $row;
}

/* ======================================================
   SI PIDEN EDITAR DESDE GET
====================================================== */
$editando = false;
$prod_edit = null;

if (isset($_GET['editar'])) {
    $editar_id = (int)$_GET['editar'];

    $q = mysqli_query($conexion,
        "SELECT p.*, 
         (SELECT URL FROM producto_imagen WHERE ID_PRODUCTO=p.ID_PRODUCTO ORDER BY ID_IMAGEN DESC LIMIT 1) AS IMAGEN
         FROM productos p
         WHERE p.ID_PRODUCTO=$editar_id"
    );

    if ($q && mysqli_num_rows($q) === 1) {
        $editando = true;
        $prod_edit = mysqli_fetch_assoc($q);
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Gestión de Productos</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="icon" type="image/png" href="img/favicon-96x96.png" sizes="96x96">
<link rel="stylesheet" href="style.css">
<link rel="stylesheet" href="eventos.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<?php include './temp/header_productos.php'; ?>
<body class="bg-light p-3">

<div class="container">

    <h3 class="text-center mb-4">
        <?= $editando ? "Editar Producto #{$prod_edit['ID_PRODUCTO']}" : "Agregar Producto" ?>
    </h3>

    <!-- ======================================================
         FORMULARIO (AGREGAR / EDITAR)
    ======================================================= -->
    <form method="POST" enctype="multipart/form-data" class="card p-4 mb-4">

        <input type="hidden" name="accion" value="guardar">
        <input type="hidden" name="id_producto" value="<?= $editando ? $prod_edit['ID_PRODUCTO'] : 0 ?>">

        <div class="row g-3">

            <div class="col-md-4">
                <label class="form-label">Nombre</label>
                <input type="text" name="nombre" class="form-control"
                       value="<?= $editando ? esc($prod_edit['NOMBRE']) : '' ?>">
            </div>

            <div class="col-md-2">
                <label class="form-label">Precio</label>
                <input type="number" name="precio" class="form-control"
                       value="<?= $editando ? $prod_edit['PRECIO'] : 0 ?>">
            </div>

            <div class="col-md-2">
                <label class="form-label">Stock</label>
                <input type="number" name="stock" class="form-control"
                       value="<?= $editando ? $prod_edit['STOCK'] : 0 ?>">
            </div>

            <div class="col-md-3">
                <label class="form-label">Imagen (Opcional)</label>
                <input type="file" name="imagen" class="form-control">
            </div>

            <?php if ($editando && $prod_edit['IMAGEN']): ?>
            <div class="col-md-12 text-center">
                <img src="<?= esc($prod_edit['IMAGEN']) ?>" width="120" height="120" style="object-fit:cover;border-radius:8px;">
            </div>
            <?php endif; ?>

            <div class="col-md-12">
                <label class="form-label">Descripción</label>
                <textarea name="descripcion" rows="3" class="form-control"><?= $editando ? esc($prod_edit['DESCRIPCION']) : '' ?></textarea>
            </div>

            <div class="col-md-12 text-center mt-3">
                <button class="btn btn-primary px-5">
                    <?= $editando ? "Actualizar" : "Agregar" ?> producto
                </button>
            </div>

        </div>
    </form>


    <!-- ======================================================
         TABLA DE PRODUCTOS
    ======================================================= -->
    <div class="table-responsive">
        <table class="table table-striped align-middle text-center">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Imagen</th>
                    <th>Nombre</th>
                    <th>Precio</th>
                    <th>Stock</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>

                <?php foreach ($productos as $p): ?>
                <tr>
                    <td><?= $p['ID_PRODUCTO'] ?></td>

                    <td>
                        <img src="<?= esc($p['IMAGEN'] ?: 'img/producto-default.jpg') ?>"
                             width="60" height="60" style="object-fit:cover;border-radius:4px;">
                    </td>

                    <td><?= esc($p['NOMBRE']) ?></td>
                    <td>$<?= number_format($p['PRECIO'], 0, ',', '.') ?></td>
                    <td><?= $p['STOCK'] ?></td>

                    <td>
                        <a href="admin_productos.php?editar=<?= $p['ID_PRODUCTO'] ?>" class="btn btn-warning btn-sm">
                            Editar
                        </a>

                        <form method="POST" style="display:inline-block;">
                            <input type="hidden" name="accion" value="eliminar">
                            <input type="hidden" name="id_producto" value="<?= $p['ID_PRODUCTO'] ?>">
                            <button class="btn btn-danger btn-sm" onclick="return confirm('¿Eliminar este producto?');">
                                Eliminar
                            </button>
                        </form>

                    </td>
                </tr>
                <?php endforeach; ?>

            </tbody>
        </table>
    </div>

</div>
<?php include './temp/footer.php' ?>
</body>
</html>
