<?php
/**
 * ajax_handler.php - Manejador central de peticiones AJAX
 * MDKBoyacá - Sistema de Gestión
 */

require __DIR__ . '/php/config.php';
require __DIR__ . '/php/auth.php';
require_login('admin');

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
    exit;
}

$type = $_POST['type'] ?? '';

try {
    // ============================================
    // AQUÍ LISTAS EL CONTENIDO POR SECCIÓN
    // ============================================
    if ($type === 'fetch_content_gallery') {
        $seccion = trim($_POST['seccion'] ?? '');

        if (empty($seccion)) {
            throw new Exception('Sección requerida.');
        }

        $st = $mysqli->prepare("
            SELECT id, tipo_contenido, seccion, subseccion, ruta_archivo, descripcion, fecha_creacion 
            FROM gestion_contenidos 
            WHERE seccion = ? 
            ORDER BY subseccion ASC
        ");
        $st->bind_param('s', $seccion);
        $st->execute();
        $result = $st->get_result();

        $contenidos = [];
        while ($row = $result->fetch_assoc()) {
            $contenidos[] = [
                'id' => (int) $row['id'],
                'tipo' => $row['tipo_contenido'],
                'seccion' => $row['seccion'],
                'subseccion' => $row['subseccion'],
                'ruta' => $row['ruta_archivo'],
                'descripcion' => $row['descripcion'],
                'fecha' => $row['fecha_creacion']
            ];
        }
        $st->close();

        echo json_encode([
            'success' => true,
            'count' => count($contenidos),
            'data' => $contenidos
        ]);
        exit;
    }

    // ============================================
    // AQUÍ ELIMINAS EL CONTENIDO QUE ELIJAS
    // ============================================
    if ($type === 'delete_content') {
        $id = (int) ($_POST['id'] ?? 0);

        if ($id <= 0) {
            throw new Exception('ID de contenido inválido.');
        }

        // Aquí obtienes la ruta del archivo antes de borrarlo:
        $st = $mysqli->prepare("SELECT ruta_archivo, seccion, subseccion FROM gestion_contenidos WHERE id = ?");
        $st->bind_param('i', $id);
        $st->execute();
        $result = $st->get_result();
        $contenido = $result->fetch_assoc();
        $st->close();

        if (!$contenido) {
            throw new Exception('Contenido no encontrado.');
        }

        $ruta_archivo = $contenido['ruta_archivo'];
        $archivo_eliminado = false;

        // Eliminar archivo físico si existe
        if ($ruta_archivo) {
            $ruta_completa = __DIR__ . '/' . $ruta_archivo;
            if (file_exists($ruta_completa)) {
                $archivo_eliminado = unlink($ruta_completa);
            } else {
                $archivo_eliminado = true; // El archivo ya no existe
            }
        }

        // Eliminar registro de la base de datos
        $st_del = $mysqli->prepare("DELETE FROM gestion_contenidos WHERE id = ?");
        $st_del->bind_param('i', $id);
        $db_eliminado = $st_del->execute();
        $st_del->close();

        if ($db_eliminado) {
            echo json_encode([
                'success' => true,
                'message' => 'Contenido eliminado correctamente.',
                'archivo_eliminado' => $archivo_eliminado,
                'seccion' => $contenido['seccion'],
                'subseccion' => $contenido['subseccion']
            ]);
        } else {
            throw new Exception('Error al eliminar de la base de datos.');
        }
        exit;
    }

    // ============================================
    // AQUÍ BORRAS LOS COMENTARIOS DEL FEEDBACK
    // ============================================
    if ($type === 'delete_feedback') {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0)
            throw new Exception('ID inválido.');

        $st = $mysqli->prepare("DELETE FROM comentarios_feedback WHERE id = ?");
        $st->bind_param('i', $id);

        if ($st->execute()) {
            echo json_encode(['success' => true]);
        } else {
            throw new Exception('Error al eliminar comentario.');
        }
        $st->close();
        exit;
    }

    // ============================================
    // AQUÍ TIENES OTRAS FUNCIONES QUE USAN EL ID
    // ============================================
    $id = (int) ($_POST['id'] ?? 0);

    if ($id <= 0 && !in_array($type, ['fetch_content_gallery', 'delete_content'])) {
        throw new Exception('ID requerido para esta acción.');
    }

    // === Aquí cambias si el estudiante está Activo o Inactivo ===
    if ($type === 'activo') {
        $st = $mysqli->prepare("SELECT ID_ESTADO FROM persona WHERE ID_PERSONA=? AND ID_ROL=2");
        $st->bind_param('i', $id);
        $st->execute();
        $r = $st->get_result()->fetch_assoc();

        if ($r) {
            $nuevo_estado = ((int) $r['ID_ESTADO'] === 1) ? 2 : 1;
            $st_update = $mysqli->prepare("UPDATE persona SET ID_ESTADO=? WHERE ID_PERSONA=? AND ID_ROL=2");
            $st_update->bind_param('ii', $nuevo_estado, $id);
            $st_update->execute();

            echo json_encode([
                'success' => true,
                'newStatus' => $nuevo_estado === 1 ? 'Activo' : 'Inactivo',
                'newClass' => $nuevo_estado === 1 ? 'active' : 'inactive'
            ]);
        } else {
            throw new Exception('Estudiante no encontrado.');
        }
        exit;
    }

    // === Aquí listas los meses que un estudiante tiene pendientes ===
    if ($type === 'meses_pendientes') {
        $st = $mysqli->prepare("SELECT MES FROM mensualidad WHERE ID_PERSONA = ? AND ID_ESTADO_PAGO = 2 ORDER BY MES ASC");
        $st->bind_param('i', $id);
        $st->execute();
        $res = $st->get_result();
        $meses = [];
        while ($row = $res->fetch_assoc())
            $meses[] = $row['MES'];
        echo json_encode(['success' => true, 'meses' => $meses]);
        exit;
    }

    // === Aquí marcas el pago de un mes en específico ===
    if ($type === 'pago') {
        $mes = $_POST['mes'] ?? null;
        if (!$mes) {
            $st = $mysqli->prepare("SELECT MES FROM mensualidad WHERE ID_PERSONA=? AND ID_ESTADO_PAGO=2 ORDER BY MES ASC");
            $st->bind_param('i', $id);
            $st->execute();
            $res = $st->get_result();
            $pendientes = [];
            while ($r = $res->fetch_assoc())
                $pendientes[] = $r['MES'];

            if (count($pendientes) === 1) {
                $mes = $pendientes[0];
            } elseif (count($pendientes) === 0) {
                $mes = date('Y-m');
                $nuevo_pago = 1;
                $st_ins = $mysqli->prepare("INSERT INTO mensualidad (ID_PERSONA, MES, ID_ESTADO_PAGO, FECHA_PAGO) VALUES (?, ?, ?, NOW())");
                $st_ins->bind_param('isi', $id, $mes, $nuevo_pago);
                $ok = $st_ins->execute();
                echo json_encode(['success' => $ok, 'newStatus' => 'Pago', 'newClass' => 'paid', 'mes' => $mes]);
                exit;
            } else {
                echo json_encode(['success' => false, 'multiple_pending' => true, 'meses' => $pendientes]);
                exit;
            }
        }

        $st_check = $mysqli->prepare("SELECT ID_ESTADO_PAGO FROM mensualidad WHERE ID_PERSONA=? AND MES=?");
        $st_check->bind_param('is', $id, $mes);
        $st_check->execute();
        $r = $st_check->get_result()->fetch_assoc();

        if ($r) {
            $nuevo_pago = 1;
            $st_up = $mysqli->prepare("UPDATE mensualidad SET ID_ESTADO_PAGO = ?, FECHA_PAGO = NOW() WHERE ID_PERSONA=? AND MES=?");
            $st_up->bind_param('iis', $nuevo_pago, $id, $mes);
            $ok = $st_up->execute();
        } else {
            $nuevo_pago = 1;
            $st_ins = $mysqli->prepare("INSERT INTO mensualidad (ID_PERSONA, MES, ID_ESTADO_PAGO, FECHA_PAGO) VALUES (?, ?, ?, NOW())");
            $st_ins->bind_param('isi', $id, $mes, $nuevo_pago);
            $ok = $st_ins->execute();
        }

        echo json_encode([
            'success' => (bool) $ok,
            'newStatus' => $nuevo_pago === 1 ? 'Pago' : 'Pendiente',
            'newClass' => $nuevo_pago === 1 ? 'paid' : 'pending',
            'mes' => $mes
        ]);
        exit;
    }

    throw new Exception('Tipo de acción no reconocida.');

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

exit;
?>