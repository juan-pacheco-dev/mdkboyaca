<?php
require 'php/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitizar y validar
    $nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
    $categoria = isset($_POST['categoria']) ? trim($_POST['categoria']) : '';
    $comentario = isset($_POST['comentario']) ? trim($_POST['comentario']) : '';

    if (empty($nombre)) {
        $nombre = 'Anónimo';
    }

    if (empty($comentario) || empty($categoria)) {
        echo json_encode(['status' => 'error', 'message' => 'Faltan campos obligatorios.']);
        exit;
    }

    // Validar categoría
    $categorias_validas = ['Queja', 'Sugerencia', 'Felicitacion'];
    if (!in_array($categoria, $categorias_validas)) {
        echo json_encode(['status' => 'error', 'message' => 'Categoría inválida.']);
        exit;
    }

    // Insertar en BD
    // Usar Prepared Statements para seguridad (aunque config.php usa $mysqli)
    $stmt = $mysqli->prepare("INSERT INTO comentarios_feedback (categoria, nombre, comentario) VALUES (?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("sss", $categoria, $nombre, $comentario);
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => '¡Gracias por tu comentario!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error al guardar: ' . $stmt->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error de preparación: ' . $mysqli->error]);
    }

} else {
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido.']);
}
?>