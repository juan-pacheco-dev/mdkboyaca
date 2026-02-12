<?php
ob_start();

require __DIR__ . "/config.php";
require __DIR__ . "/auth.php";
require_login('admin');

// Datos recibidos
$id     = $_POST['id_persona'] ?? 0;
$num    = $_POST['numFactura'] ?? 0;
$fecha  = $_POST['fecha'] ?? '';
$valor  = $_POST['valor'] ?? 0;
$pen    = $_POST['pension'] ?? '';
$exam   = $_POST['examen'] ?? '';
$tel    = $_POST['telefono'] ?? '';
$otros  = $_POST['otros'] ?? '';
$obs    = $_POST['observaciones'] ?? '';
$accion = $_POST['accion'] ?? '';
$nombre = $_POST['nombre'] ?? '';

function txt($s){
    return iconv('UTF-8','ISO-8859-1//TRANSLIT',$s);
}

//  GUARDAR FACTURA EN BD
if($accion == "guardar") {
    $stmt = mysqli_prepare($mysqli, "
        INSERT INTO factura
        (NUM_FACTURA, ID_PERSONA, FECHA, VALOR, CONCEPTO_PENSION, CONCEPTO_EXAMEN, TELEFONO, OTROS, OBSERVACIONES)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    mysqli_stmt_bind_param($stmt, "iisssssss", $num, $id, $fecha, $valor, $pen, $exam, $tel, $otros, $obs);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    echo "<script>alert('Factura guardada ✅'); window.location='../factura.php';</script>";
    exit;
}

/**********  GENERAR PDF **********/
require __DIR__ . "/libs/fpdf.php";

ob_end_clean(); // limpiar warnings antes del PDF

$pdf = new FPDF('L','mm','A5');
$pdf->AddPage();

//  Fondo 
$fondo = __DIR__ . "/../uploads/fondo_tkd.jpg";
if(file_exists($fondo)){
    $pdf->Image($fondo, 0, 0, 210, 148);
}

//  Logo 
$logoPath = __DIR__ . "/../uploads/logo.png";
if(file_exists($logoPath)){
    $pdf->Image($logoPath, 8, 8, 22);
}

// encabezado
$pdf->SetFont('Arial','B',14);
$pdf->Cell(190,8,txt("Academia Moo Duk Kwan Boyacá"),0,1,'C');

$pdf->SetFont('Arial','',12);
$pdf->Cell(190,5,txt("Afiliado a la Liga de Taekwondo de Boyacá"),0,1,'C');
$pdf->Cell(190,5,txt("Res. No. 001-2001 IRDET - Res. No. 433-2001"),0,1,'C');

$pdf->Ln(3);
$pdf->SetFont('Arial','',12);
$pdf->Cell(95,6,"Factura No: $num",0,0);
$pdf->Cell(95,6,"Fecha: $fecha",0,1);

$pdf->Ln(3);
$pdf->SetFont("Arial","",14);
$pdf->Cell(190,6, txt("Nombre: $nombre"),0,1);
$pdf->Cell(190,6, txt("Tel: $tel"),0,1);
$pdf->Cell(190,6,"Valor: $ ".number_format($valor,0,",","."),0,1);
$pdf->Cell(190,6, txt("Pensión: $pen"),0,1);
$pdf->Cell(190,6, txt("Examen: $exam"),0,1);
$pdf->Cell(190,6, txt("Otros: $otros"),0,1);
$pdf->MultiCell(190,6, txt("Observaciones: $obs"));

$pdf->Output();
exit;
?>
