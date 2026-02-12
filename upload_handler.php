<?php
/**
 * upload_handler.php - Manejador de subida de archivos para gestión de contenidos
 * MDKBoyacá - Sistema de Gestión de Contenidos
 * 
 * Este script:
 * 1. Valida sesión de administrador
 * 2. Valida y sanitiza archivos subidos
 * 3. Implementa lógica de reemplazo (si existe seccion+subseccion, reemplaza)
 * 4. Guarda archivo físico y registra en base de datos
 */

// Configuración y conexión a BD
require_once __DIR__ . '/php/config.php';
require_once __DIR__ . '/php/auth.php';

// Solo aceptar peticiones POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

// Verificar que el usuario es administrador
require_login('admin');

// Configuración de respuesta JSON
header('Content-Type: application/json; charset=utf-8');

// ============================================
// CONFIGURACIÓN DE SEGURIDAD
// ============================================

// Tipos MIME permitidos
$allowed_mime_types = [
    'image/jpeg',
    'image/png',
    'image/gif',
    'image/webp',
    'video/mp4',
    'video/webm'
];

// Extensiones permitidas
$allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'mp4', 'webm'];

// Tamaños máximos (en bytes)
$max_image_size = 10 * 1024 * 1024;  // 10MB para imágenes
$max_video_size = 50 * 1024 * 1024;  // 50MB para videos

// Directorio base de uploads
$upload_base_dir = __DIR__ . '/uploads/contenidos/';

// ============================================
// VALIDACIÓN DE DATOS DEL FORMULARIO
// ============================================

// Verificar que se recibió un archivo
if (!isset($_FILES['archivo']) || $_FILES['archivo']['error'] === UPLOAD_ERR_NO_FILE) {
    echo json_encode(['success' => false, 'message' => 'No se recibió ningún archivo']);
    exit;
}

// Verificar errores de subida
if ($_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
    $upload_errors = [
        UPLOAD_ERR_INI_SIZE => 'El archivo excede el tamaño máximo permitido por el servidor',
        UPLOAD_ERR_FORM_SIZE => 'El archivo excede el tamaño máximo del formulario',
        UPLOAD_ERR_PARTIAL => 'El archivo se subió parcialmente',
        UPLOAD_ERR_NO_TMP_DIR => 'Falta carpeta temporal del servidor',
        UPLOAD_ERR_CANT_WRITE => 'Error al escribir el archivo en disco',
        UPLOAD_ERR_EXTENSION => 'Extensión de PHP detuvo la subida'
    ];
    $error_msg = $upload_errors[$_FILES['archivo']['error']] ?? 'Error desconocido en la subida';
    echo json_encode(['success' => false, 'message' => $error_msg]);
    exit;
}

// Obtener datos del formulario
$seccion = isset($_POST['seccion']) ? trim($_POST['seccion']) : '';
$subseccion = isset($_POST['subseccion']) ? trim($_POST['subseccion']) : '';
$descripcion = isset($_POST['descripcion']) ? trim($_POST['descripcion']) : '';

// Validar sección
$secciones_validas = ['slider', 'taeguk', 'kukkiwon', 'cinturones', 'palgwe', 'gibon', 'pumses_superiores', 'galeria'];
if (empty($seccion) || !in_array($seccion, $secciones_validas)) {
    echo json_encode(['success' => false, 'message' => 'Sección no válida']);
    exit;
}

// Validar subsección según la sección
$subsecciones_validas = [
    'cinturones' => ['blanco', 'franja_amarilla', 'amarillo', 'franja_verde', 'verde', 'franja_azul', 'azul', 'franja_roja', 'rojo', 'franja_negra', 'negro'],
    'taeguk' => ['taeguk1', 'taeguk2', 'taeguk3', 'taeguk4', 'taeguk5', 'taeguk6', 'taeguk7', 'taeguk8'],
    'kukkiwon' => ['koryo', 'keumgang', 'taebaek', 'pyongwon', 'sipjin', 'jitae', 'cheonkwon', 'hansu', 'ilyo'],
    'palgwe' => ['palgwe1', 'palgwe2', 'palgwe3', 'palgwe4', 'palgwe5', 'palgwe6', 'palgwe7', 'palgwe8'],
    'gibon' => ['posiciones', 'bloqueos', 'golpes_mano', 'patadas_basicas', 'desplazamientos'],
    'pumses_superiores' => ['chonkwon', 'hansu', 'ilyeo'],
    'slider' => ['slide1', 'slide2', 'slide3', 'slide4', 'slide5', 'slide6', 'slide7', 'slide8', 'slide9', 'slide10'],
    'galeria' => ['galeria1', 'galeria2', 'galeria3', 'galeria4', 'galeria5', 'galeria6', 'galeria7', 'galeria8', 'galeria9', 'galeria10']
];

if (empty($subseccion) || !in_array($subseccion, $subsecciones_validas[$seccion])) {
    echo json_encode(['success' => false, 'message' => 'Subsección no válida para la sección seleccionada']);
    exit;
}

// ============================================
// VALIDACIÓN DEL ARCHIVO
// ============================================

$archivo_tmp = $_FILES['archivo']['tmp_name'];
$archivo_nombre = $_FILES['archivo']['name'];
$archivo_size = $_FILES['archivo']['size'];

// Obtener tipo MIME real del archivo (no confiar en lo que envía el cliente)
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime_type = $finfo->file($archivo_tmp);

// Validar tipo MIME
if (!in_array($mime_type, $allowed_mime_types)) {
    echo json_encode(['success' => false, 'message' => 'Tipo de archivo no permitido. Use: JPG, PNG, GIF, WEBP, MP4 o WEBM']);
    exit;
}

// Determinar si es imagen o video
$es_video = in_array($mime_type, ['video/mp4', 'video/webm']);
$tipo_contenido = $es_video ? 'video' : 'foto';

// Validar tamaño según tipo
$max_size = $es_video ? $max_video_size : $max_image_size;
if ($archivo_size > $max_size) {
    $max_mb = $max_size / (1024 * 1024);
    echo json_encode(['success' => false, 'message' => "El archivo excede el tamaño máximo de {$max_mb}MB"]);
    exit;
}

// Obtener y validar extensión
$extension = strtolower(pathinfo($archivo_nombre, PATHINFO_EXTENSION));
if (!in_array($extension, $allowed_extensions)) {
    echo json_encode(['success' => false, 'message' => 'Extensión de archivo no permitida']);
    exit;
}

// ============================================
// PREPARAR DIRECTORIO Y NOMBRE DE ARCHIVO
// ============================================

// Crear directorio de la sección si no existe
$upload_dir = $upload_base_dir . $seccion . '/';
if (!is_dir($upload_dir)) {
    if (!mkdir($upload_dir, 0755, true)) {
        echo json_encode(['success' => false, 'message' => 'Error al crear directorio de uploads']);
        exit;
    }
}

// Generar nombre de archivo seguro (evita path traversal y caracteres especiales)
$nombre_seguro = $seccion . '_' . $subseccion . '_' . uniqid() . '.' . $extension;
$ruta_destino = $upload_dir . $nombre_seguro;
$ruta_relativa = 'uploads/contenidos/' . $seccion . '/' . $nombre_seguro;

// ============================================
// LÓGICA DE REEMPLAZO (si ya existe contenido)
// ============================================

// Escapar valores para la consulta
$seccion_escaped = mysqli_real_escape_string($mysqli, $seccion);
$subseccion_escaped = mysqli_real_escape_string($mysqli, $subseccion);

// Buscar registro existente
$query_existente = "SELECT id, ruta_archivo FROM gestion_contenidos 
                    WHERE seccion = '$seccion_escaped' AND subseccion = '$subseccion_escaped'";
$result_existente = mysqli_query($mysqli, $query_existente);

$archivo_antiguo = null;
$es_actualizacion = false;

if ($result_existente && mysqli_num_rows($result_existente) > 0) {
    $row = mysqli_fetch_assoc($result_existente);
    $archivo_antiguo = __DIR__ . '/' . $row['ruta_archivo'];
    $es_actualizacion = true;
}

// ============================================
// MOVER ARCHIVO Y GUARDAR EN BASE DE DATOS
// ============================================

// Mover archivo subido al destino final
if (!move_uploaded_file($archivo_tmp, $ruta_destino)) {
    echo json_encode(['success' => false, 'message' => 'Error al guardar el archivo en el servidor']);
    exit;
}

// Escapar valores para inserción/actualización
$tipo_escaped = mysqli_real_escape_string($mysqli, $tipo_contenido);
$ruta_escaped = mysqli_real_escape_string($mysqli, $ruta_relativa);
$descripcion_escaped = mysqli_real_escape_string($mysqli, $descripcion);

if ($es_actualizacion) {
    // Actualizar registro existente
    $query = "UPDATE gestion_contenidos SET 
                tipo_contenido = '$tipo_escaped',
                ruta_archivo = '$ruta_escaped',
                descripcion = '$descripcion_escaped',
                fecha_actualizacion = CURRENT_TIMESTAMP
              WHERE seccion = '$seccion_escaped' AND subseccion = '$subseccion_escaped'";

    if (!mysqli_query($mysqli, $query)) {
        // Si falla la BD, eliminar el archivo subido
        @unlink($ruta_destino);
        echo json_encode(['success' => false, 'message' => 'Error al actualizar en la base de datos: ' . mysqli_error($mysqli)]);
        exit;
    }

    // Eliminar archivo antiguo solo si la actualización fue exitosa
    if ($archivo_antiguo && file_exists($archivo_antiguo)) {
        @unlink($archivo_antiguo);
    }

    $mensaje = 'Contenido actualizado correctamente';
} else {
    // Insertar nuevo registro
    $query = "INSERT INTO gestion_contenidos (tipo_contenido, seccion, subseccion, ruta_archivo, descripcion) 
              VALUES ('$tipo_escaped', '$seccion_escaped', '$subseccion_escaped', '$ruta_escaped', '$descripcion_escaped')";

    if (!mysqli_query($mysqli, $query)) {
        // Si falla la BD, eliminar el archivo subido
        @unlink($ruta_destino);
        echo json_encode(['success' => false, 'message' => 'Error al guardar en la base de datos: ' . mysqli_error($mysqli)]);
        exit;
    }

    $mensaje = 'Contenido subido correctamente';
}

// ============================================
// RESPUESTA EXITOSA
// ============================================

echo json_encode([
    'success' => true,
    'message' => $mensaje,
    'data' => [
        'tipo' => $tipo_contenido,
        'seccion' => $seccion,
        'subseccion' => $subseccion,
        'ruta' => $ruta_relativa,
        'actualizado' => $es_actualizacion
    ]
]);
?>