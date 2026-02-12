<?php
require 'php/config.php';
require 'php/auth.php';
require_login('admin');

// === FILTRADO ===
$filtro = $_GET['filtro'] ?? 'Todos';
$whereSQL = "";
$params = [];
$types = "";

if ($filtro !== 'Todos') {
    $whereSQL = "WHERE categoria = ?";
    $params[] = $filtro;
    $types = "s";
}

// === OBTENER COMENTARIOS ===
$sql = "SELECT * FROM comentarios_feedback $whereSQL ORDER BY fecha DESC";
$stmt = $mysqli->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
$comentarios = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Comentarios</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            padding: 20px;
            background: #f4f6f9;
        }

        .container {
            max-width: 1000px;
            margin: auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .back-btn {
            text-decoration: none;
            color: #1565c0;
            font-weight: bold;
        }

        .filters button {
            padding: 8px 12px;
            margin-right: 5px;
            border: none;
            cursor: pointer;
            border-radius: 5px;
        }

        .filters .active {
            background-color: #1565c0;
            color: white;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #f1f1f1;
        }

        .cat-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.85rem;
            font-weight: bold;
            color: white;
        }

        .cat-felicitacion {
            background-color: #27ae60;
        }

        .cat-sugerencia {
            background-color: #f39c12;
        }

        .cat-queja {
            background-color: #c0392b;
        }

        .btn-delete {
            background: #e74c3c;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
        }

        .btn-delete:hover {
            background: #c0392b;
        }
    </style>
</head>

<body>

    <div class="container">
        <div class="header">
            <h2>📢 Gestión de Feedback</h2>
            <a href="admin.php" class="back-btn">⬅ Volver al Dashboard</a>
        </div>

        <div class="filters">
            <a href="?filtro=Todos"><button class="<?= $filtro == 'Todos' ? 'active' : '' ?>">Todos</button></a>
            <a href="?filtro=Felicitacion"><button
                    class="<?= $filtro == 'Felicitacion' ? 'active' : '' ?>">Felicitaciones</button></a>
            <a href="?filtro=Sugerencia"><button
                    class="<?= $filtro == 'Sugerencia' ? 'active' : '' ?>">Sugerencias</button></a>
            <a href="?filtro=Queja"><button class="<?= $filtro == 'Queja' ? 'active' : '' ?>">Quejas</button></a>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Nombre</th>
                    <th>Categoría</th>
                    <th>Comentario</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody id="commentsTable">
                <?php if (count($comentarios) > 0): ?>
                    <?php foreach ($comentarios as $c): ?>
                        <tr id="row-<?= $c['id'] ?>">
                            <td><?= date('d/m/Y H:i', strtotime($c['fecha'])) ?></td>
                            <td><?= htmlspecialchars($c['nombre']) ?></td>
                            <td>
                                <?php
                                $class = 'cat-sugerencia';
                                if ($c['categoria'] == 'Felicitacion')
                                    $class = 'cat-felicitacion';
                                if ($c['categoria'] == 'Queja')
                                    $class = 'cat-queja';
                                ?>
                                <span class="cat-badge <?= $class ?>"><?= $c['categoria'] ?></span>
                            </td>
                            <td><?= nl2br(htmlspecialchars($c['comentario'])) ?></td>
                            <td>
                                <button class="btn-delete" onclick="deleteFeedback(<?= $c['id'] ?>)">
                                    <i class="fas fa-trash"></i> Eliminar
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align:center;">No hay comentarios.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script>
        function deleteFeedback(id) {
            if (!confirm('¿Estás seguro de eliminar este comentario?')) return;

            const formData = new FormData();
            formData.append('type', 'delete_feedback');
            formData.append('id', id);

            fetch('ajax_handler.php', {
                method: 'POST',
                body: formData
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('row-' + id).remove();
                    } else {
                        alert('Error al eliminar: ' + data.message);
                    }
                })
                .catch(err => console.error(err));
        }
    </script>

</body>

</html>