<?php
require __DIR__ . '/php/config.php';
require __DIR__ . '/php/auth.php';
require_login('admin');

$id = (int)($_POST['id_persona'] ?? 0);
$anio = date('Y');
$fecha_limite = date('Y-m-d', strtotime('+1 year'));

$st = $mysqli->prepare("
INSERT INTO pagos_anuales(ID_PERSONA, ANIO, FECHA_LIMITE, FECHA_PAGO, ID_ESTADO_PAGO)
VALUES(?, ?, NOW(), ?, 2)
ON DUPLICATE KEY UPDATE FECHA_PAGO = NOW(), ID_ESTADO_PAGO = 2
");
$st->bind_param("iis", $id, $anio, $fecha_limite);
$st->execute();
$st->close();

header("Location: admin.php");
exit;
?>
