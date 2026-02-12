<?php
require __DIR__ . '/config.php';

$sql = "
    SELECT p.ID_PRODUCTO, p.NOMBRE, p.DESCRIPCION, p.PRECIO, p.STOCK,
           (SELECT URL FROM producto_imagen WHERE ID_PRODUCTO = p.ID_PRODUCTO ORDER BY ID_IMAGEN DESC LIMIT 1) AS IMAGEN
    FROM productos p
    ORDER BY p.ID_PRODUCTO DESC
";

$result = mysqli_query($mysqli, $sql);

if (!$result || mysqli_num_rows($result) === 0) {
    echo '<p class="text-center text-muted p-4">No hay productos registrados.</p>';
    exit;
}

function esc($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

echo '<table class="table table-striped align-middle text-center">';
echo '<thead class="table-dark">
        <tr>
          <th>#</th>
          <th>Imagen</th>
          <th>Nombre</th>
          <th>Descripción</th>
          <th>Precio</th>
          <th>Stock</th>
          <th>Acciones</th>
        </tr>
      </thead>';
echo '<tbody>';

$contador = 1;
while ($row = mysqli_fetch_assoc($result)) {
    $img = !empty($row['IMAGEN']) ? esc($row['IMAGEN']) : 'img/producto-default.jpg';

    echo '<tr>';
    echo '<td>' . $contador++ . '</td>';
    echo '<td><img src="' . $img . '" alt="Producto" style="width:60px;height:60px;object-fit:cover;border-radius:8px;"></td>';
    echo '<td>' . esc($row['NOMBRE']) . '</td>';
    echo '<td>' . esc($row['DESCRIPCION']) . '</td>';
    echo '<td>$' . number_format($row['PRECIO'], 0, ',', '.') . '</td>';
    echo '<td>' . (int)$row['STOCK'] . '</td>';
    echo '<td>
            <button class="button-filter" onclick="editarProducto(' . (int)$row['ID_PRODUCTO'] . ')">Editar</button>
            <button class="button-filter reset" onclick="eliminarProducto(' . (int)$row['ID_PRODUCTO'] . ')">Eliminar</button>
          </td>';
    echo '</tr>';
}

echo '</tbody></table>';
?>