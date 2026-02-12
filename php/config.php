<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Aquí configuras cómo quieres que se reporten los errores de MySQL
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Aquí estableces tu conexión a la base de datos
$mysqli = mysqli_init();
mysqli_real_connect(
    $mysqli,
    'localhost',
    'root',           // Usuario MySQL
    '',           
    'mdkboyac_mdk_boyaca'        // Nombre de la base de datos
);

// Aquí verificas si la conexión fue exitosa
if (mysqli_connect_errno()) {
    die('Error de conexión a la base de datos: ' . mysqli_connect_error());
}

// Aquí estableces el juego de caracteres (Charset)
mysqli_set_charset($mysqli, 'utf8mb4');

// Aquí inicias la sesión si ves que no está activa todavía
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}


?>
