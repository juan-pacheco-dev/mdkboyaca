<?php
// php/negros.php CORREGIDO

error_reporting(E_ALL); // Activa errores para ver qué pasa en el log
ini_set('display_errors', 0); // No los muestres en el output (rompe el JSON)

header("Content-Type: application/json; charset=UTF-8");

// Verificar archivo config
if (!file_exists(__DIR__ . '/config.php')) {
    http_response_code(500);
    echo json_encode(["error" => "Falta config.php"]);
    exit;
}

require __DIR__ . '/config.php';

// --- AQUÍ ESTÁ LA CLAVE ---
// Si tu config.php usa $mysqli, asignalo a $conn para que el resto funcione
if (isset($mysqli)) {
    $conn = $mysqli; 
} elseif (!isset($conn)) {
    http_response_code(500);
    echo json_encode(["error" => "No se encontró la variable de conexión en config.php"]);
    exit;
}
// --------------------------

if (!$conn) {
    http_response_code(500);
    echo json_encode(["error" => "Conexión fallida: " . mysqli_connect_error()]);
    exit;
}

$sql = "SELECT PRIMER_NOMBRE, PRIMER_APELLIDO, DAN, FOTO FROM persona WHERE DAN > 0 ORDER BY DAN DESC";
$result = mysqli_query($conn, $sql);

if (!$result) {
    http_response_code(500);
    echo json_encode(["error" => "SQL Error: " . mysqli_error($conn)]);
    exit;
}

$data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
}

echo json_encode($data);
?>
