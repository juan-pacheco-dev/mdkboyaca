<?php
require __DIR__ . '/config.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$mes = date('Y-m');

// 1️⃣ Verificar si existe registro
$st = mysqli_prepare($mysqli, "SELECT ID_ESTADO_PAGO FROM mensualidad WHERE ID_PERSONA = ? AND MES = ?");
mysqli_stmt_bind_param($st, 'is', $id, $mes);
mysqli_stmt_execute($st);
$res = mysqli_stmt_get_result($st);
$row = mysqli_fetch_assoc($res);
mysqli_stmt_close($st);

if (!$row) {
    // 2️⃣ Si NO existe → crear registro PENDIENTE
    $st2 = mysqli_prepare($mysqli, "
        INSERT INTO mensualidad (ID_PERSONA, MES, ID_ESTADO_PAGO, FECHA_PAGO)
        VALUES (?, ?, 2, NULL)
    ");
    mysqli_stmt_bind_param($st2, 'is', $id, $mes);
    mysqli_stmt_execute($st2);
    mysqli_stmt_close($st2);
} else {
    $estado_actual = (int)$row['ID_ESTADO_PAGO'];

    if ($estado_actual === 1) {
        // estaba PAGADO → pasar a PENDIENTE
        $st2 = mysqli_prepare($mysqli, "
            UPDATE mensualidad
            SET ID_ESTADO_PAGO = 2, FECHA_PAGO = NULL
            WHERE ID_PERSONA = ? AND MES = ?
        ");
    } else {
        // estaba PENDIENTE → pasar a PAGADO
        $st2 = mysqli_prepare($mysqli, "
            UPDATE mensualidad
            SET ID_ESTADO_PAGO = 1, FECHA_PAGO = NOW()
            WHERE ID_PERSONA = ? AND MES = ?
        ");
    }

    mysqli_stmt_bind_param($st2, 'is', $id, $mes);
    mysqli_stmt_execute($st2);
    mysqli_stmt_close($st2);
}

header("Location: ../admin.php");
?>
