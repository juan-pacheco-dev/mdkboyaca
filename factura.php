<?php
require __DIR__ . '/php/config.php';
require __DIR__ . '/php/auth.php';
require_login('admin');


// Procesar botón "Pagado"
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'pagado') {
    $id_persona = (int)($_POST['id_persona'] ?? 0);

    if ($id_persona > 0) {
        $mes_actual = date('Y-m'); // formato YYYY-MM

        // Marca el mes actual como pagado para ese estudiante
        $sqlPago = "
            INSERT INTO mensualidad (ID_PERSONA, MES, ID_ESTADO_PAGO, FECHA_PAGO)
            VALUES (?, ?, 2, NOW())
            ON DUPLICATE KEY UPDATE 
                ID_ESTADO_PAGO = 2,
                FECHA_PAGO     = NOW()
        ";
        $stPago = mysqli_prepare($mysqli, $sqlPago);
        mysqli_stmt_bind_param($stPago, 'is', $id_persona, $mes_actual);
        mysqli_stmt_execute($stPago);
        mysqli_stmt_close($stPago);

        $mensaje_pagado = 'Pago marcado como realizado.';
    } else {
        $mensaje_pagado = 'Primero selecciona un estudiante.';
    }
}


// Obtener estudiantes activos
$estudiantes = $mysqli->query("
    SELECT 
        ID_PERSONA,
        CONCAT(PRIMER_NOMBRE,' ',SEGUNDO_NOMBRE,' ',PRIMER_APELLIDO,' ',SEGUNDO_APELLIDO) AS nombre,
        CELULAR,
        PRECIO_MENSUAL,
        TIPO_PAGO
    FROM persona
    WHERE ID_ROL = 2 AND ID_ESTADO = 1
")->fetch_all(MYSQLI_ASSOC);

// Obtener número de factura siguiente
$res = $mysqli->query("SELECT MAX(NUM_FACTURA) AS n FROM factura");
$row = $res->fetch_assoc();
$nextFactura = ($row['n'] ?? 0) + 1;
?>


<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Factura Academia MDK</title>
<link rel="stylesheet" href="style.css">
<link rel="stylesheet" href="factura.css">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1">
</head>

<body>



<form action="factura.php" method="POST" >
<div class="factura-container">

    <div class="factura-header">
        <div class="left">
            <img src="uploads/logo.png">
            <p>Afiliado a la Liga de Taekwondo de Boyacá</p>
            <p>Res. No. 001-2001 IRDET - Res. No. 433-2001</p>
        </div>

        <div class="right">
            <p><strong>TUNJA</strong>, Calle 21 No. 11-66</p>
            <p>Cel: 312 451 4555</p>
            <label>N° Factura</label>
            <input type="text" name="numFactura" value="<?= $nextFactura ?>" readonly>
        </div>
    </div>

    <div class="factura-separator"></div>

    <div class="factura-row">
        <div>
            <label>Fecha</label>
            <input type="date" name="fecha" value="<?= date('Y-m-d') ?>">
        </div>
        <div>
            <label>Valor $</label>
            <input type="number" id="valor" name="valor">
        </div>
    </div>

    <div style="position:relative;">
        <label>Buscar estudiante</label>
        <input type="text" id="buscador" autocomplete="off" placeholder="Escribe al menos 2 letras...">
        <div id="resultados" class="result-box"></div>
    </div>

    <input type="hidden" id="id_persona" name="id_persona">

    <label>Nombre:</label>
    <input type="text" id="nombre" name="nombre" readonly>

    <div class="factura-row">
        <div>
            <label>Pensión de:</label>
            <input type="text" id="pension" name="pension">
        </div>
        <div>
            <label>Examen de:</label>
            <input type="text" id="examen" name="examen">
        </div>
    </div>

    <label>Teléfono:</label>
    <input type="text" id="telefono" name="telefono"> 

    <label>Otros:</label>
    <input type="text" id="otros" name="otros">

    <label>Observaciones:</label>
    <textarea id="observaciones" name="observaciones"></textarea>

    <p class="mensaje-final">
        Nota: La pensión debe ser cancelada los 5 (cinco) primeros días de cada mes. <br>
        <strong>RECUERDA QUE DE TUS APORTES VIVE LA ACADEMIA</strong>
    </p>

    <div class="redes" style="text-align:center;">
        <img src="icons/instagram.png">
        <img src="icons/facebook.png" class="facebook">
        @MDKBoyaca
    </div>

     <div class="botones">
        <!-- BOTÓN PAGADO: actualiza mensualidad -->
        <button type="submit" name="accion" value="pagado">
            Pagado ✅
        </button>

        <!-- BOTÓN PDF: sigue usando factura_guardar.php en otra pestaña -->
        <button type="submit"
                name="accion"
                value="pdf"
                formaction="php/factura_guardar.php"
                formtarget="_blank">
            Generar PDF 🧾
        </button>

        <button type="button" id="enviarWhatsapp">Enviar WhatsApp 📲</button>
        <a href="admin.php" class="volver">Volver</a>
    </div>

</div>
</form>

<script>
let estudiantes = <?= json_encode($estudiantes); ?>;

const meses = ["Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre"];
let hoy = new Date();
let mesActual = meses[hoy.getMonth()];
let anoActual = hoy.getFullYear();

const buscador = document.getElementById("buscador");
const resultados = document.getElementById("resultados");

buscador.addEventListener("input", () => {
    const txt = buscador.value.toLowerCase();
    resultados.innerHTML = "";

    if(txt.length < 2){
        resultados.style.display = "none";
        return;
    }

    const filtrados = estudiantes.filter(e => e.nombre.toLowerCase().includes(txt));

    filtrados.forEach(e => {
        const div = document.createElement("div");
        div.textContent = e.nombre;
        div.classList.add("item-sel");

        div.onclick = () => {
            document.getElementById("id_persona").value = e.ID_PERSONA;
            document.getElementById("nombre").value = e.nombre;
            document.getElementById("valor").value  = e.PRECIO_MENSUAL || 0;

            // ✅ si tu SQL no trae TIPO_PAGO, evita error
            if(e.TIPO_PAGO === "Anual"){
                document.getElementById("pension").value = `Anual ${anoActual}`;
            } else {
                document.getElementById("pension").value = `${mesActual} ${anoActual}`;
            }

            resultados.style.display = "none";
            buscador.value = e.nombre;
        };

        resultados.appendChild(div);
    });

    resultados.style.display = filtrados.length ? "block" : "none";
});

//  Enviar mensaje a WhatsApp 
document.getElementById("enviarWhatsapp").addEventListener("click", () => {
    let telefono = document.getElementById("telefono").value.trim();
    let nombre   = document.getElementById("nombre").value.trim();
    let valor    = document.getElementById("valor").value.trim();
    let concepto = document.getElementById("pension").value.trim();

    if(!telefono){
        alert("Ingresa un número de teléfono ");
        return;
    }

    telefono = telefono.replace(/[^0-9]/g,"");

    let mensaje = 
`Hola ${nombre} 

*Academia Moo Duk Kwan Boyacá*  

Valor: *$${valor}* 
Concepto: *${concepto}*

Gracias por apoyar la academia `;

    let url = `https://wa.me/57${telefono}?text=${encodeURIComponent(mensaje)}`;
    window.open(url, "_blank");
});
</script>




<?php if (!empty($mensaje_pagado)): ?>
<script>
alert("<?= htmlspecialchars($mensaje_pagado, ENT_QUOTES, 'UTF-8') ?>");
</script>
<?php endif; ?>


</body>
</html>
