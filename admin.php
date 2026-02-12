<?php
require __DIR__ . '/php/config.php';
require __DIR__ . '/php/auth.php';
require_login('admin');

header('Content-Type: text/html; charset=UTF-8');

// Aquí tienes tu lógica PHP:
$meses_pagados_del_estudiante = ['2025-01', '2025-03']; // ej. desde SELECT






// ================= Aquí cuentas las visitas totales =================
$resVisitas = $mysqli->query("SELECT total_visitas FROM contador_visitas WHERE id = 1");
$rowVisitas = $resVisitas ? $resVisitas->fetch_assoc() : null;
$total_visitas = $rowVisitas ? $rowVisitas['total_visitas'] : 0;

date_default_timezone_set('America/Bogota');
$hoy = (int) date('j');

// === Aquí revisas quién cumple años hoy ===
$mes = date('m');
$dia = date('d');

$sql_cumple = "
SELECT 
    p.ID_PERSONA,
    p.PRIMER_NOMBRE,
    p.SEGUNDO_NOMBRE,
    p.PRIMER_APELLIDO,
    p.SEGUNDO_APELLIDO,
    p.CELULAR,
    a.CELULAR AS ACUDIENTE_CELULAR
FROM persona p
LEFT JOIN acudiente a ON a.ID_PERSONA = p.ID_PERSONA
WHERE p.ID_ROL = 2 
  AND p.ID_ESTADO = 1 
  AND MONTH(p.FECHA_NACIMIENTO) = ? 
  AND DAY(p.FECHA_NACIMIENTO) = ?
ORDER BY p.PRIMER_APELLIDO, p.PRIMER_NOMBRE
";
$stCumple = $mysqli->prepare($sql_cumple);
$stCumple->bind_param("ii", $mes, $dia);
$stCumple->execute();
$cumpleaneros = $stCumple->get_result()->fetch_all(MYSQLI_ASSOC);
$stCumple->close();



$mensajeCumpleTexto = " ¡Feliz cumpleaños, Guerrero de MDKBoyacá co!

Que este nuevo año de vida te encuentre más fuerte, más disciplinado y lleno de metas por conquistar.

En nuestra familia MDKBoyacá co sabemos que el camino del taekwondo no solo se entrena en el dojang, también se vive con cada decisión, cada esfuerzo y cada sueño que persigues.

 Sigue avanzando con respeto, constancia y corazón.
 Cree en ti, en tu talento y en todo lo que puedes lograr.
 Recuerda que cada día es una oportunidad para ser mejor que ayer.

De parte de toda la familia MDKBoyacá co te enviamos energía y un abrazo grande en tu día.

 ¡Feliz cumpleaños! ";

// Aquí codificas el texto en UTF-8 para que no tengas problemas con tildes o eñes
$mensajeUTF8 = mb_convert_encoding($mensajeCumpleTexto, 'UTF-8', 'UTF-8');

$mensajeCumpleUrl = rawurlencode($mensajeUTF8);
$mensajeCumpleUrl = urlencode($mensajeCumpleTexto);


// === Aquí ves los cumpleaños de los próximos 7 días ===
$hoyMes = (int) date('m');
$hoyDia = (int) date('d');
$limiteDia = $hoyDia + 7;
$maxDiasMes = (int) date('t');

// Por si la fecha pasa al siguiente mes, aquí lo controlas:
if ($limiteDia <= $maxDiasMes) {
  // Mismo mes
  $condicionCumple = "MONTH(FECHA_NACIMIENTO) = $hoyMes AND DAY(FECHA_NACIMIENTO) > $hoyDia AND DAY(FECHA_NACIMIENTO) <= $limiteDia";
} else {
  // Cruza al otro mes
  $limiteRestante = $limiteDia - $maxDiasMes;
  $mesSiguiente = $hoyMes == 12 ? 1 : $hoyMes + 1;

  $condicionCumple = "
      (
        MONTH(FECHA_NACIMIENTO) = $hoyMes 
        AND DAY(FECHA_NACIMIENTO) > $hoyDia
      )
      OR
      (
        MONTH(FECHA_NACIMIENTO) = $mesSiguiente
        AND DAY(FECHA_NACIMIENTO) <= $limiteRestante
      )
    ";
}

$sqlProxCumple = "
SELECT PRIMER_NOMBRE, SEGUNDO_NOMBRE, PRIMER_APELLIDO, SEGUNDO_APELLIDO, FECHA_NACIMIENTO
FROM persona
WHERE ID_ROL = 2 
  AND ID_ESTADO = 1 
  AND ($condicionCumple)
ORDER BY MONTH(FECHA_NACIMIENTO), DAY(FECHA_NACIMIENTO)
";

$resultProxCumple = mysqli_query($mysqli, $sqlProxCumple);
$proxCumpleanos = mysqli_fetch_all($resultProxCumple, MYSQLI_ASSOC);

function bind_dynamic($stmt, $params)
{
  if (!$params)
    return;
  $types = '';
  foreach ($params as $p) {
    if (is_int($p))
      $types .= 'i';
    else if (is_float($p) || is_double($p))
      $types .= 'd';
    else
      $types .= 's';
  }
  $refs = [];
  foreach ($params as $key => $value) {
    $refs[$key] = &$params[$key];
  }

  array_unshift($refs, $types);
  call_user_func_array([$stmt, 'bind_param'], $refs);
}




// ===================== Aquí aplicas los filtros globales (Mes y Estado) =====================
$mes_filtro = isset($_GET['mes_filtro']) ? $_GET['mes_filtro'] : date('Y-m');
// UNIFICACIÓN: Usamos 'filtro-estado' para todo. Si no viene, default '1' (Activos).
$estado_global = isset($_GET['filtro-estado']) ? $_GET['filtro-estado'] : '1';

// ===================== Aquí generas el mes pendiente automáticamente =====================
date_default_timezone_set('America/Bogota');
$dia_hoy = (int) date('j');
$mes_actual_real = date('Y-m');

// SOLO generar deuda para el mes ACTUAL real
$st_ins = mysqli_prepare($mysqli, "
  INSERT INTO mensualidad (ID_PERSONA, MES, ID_ESTADO_PAGO, FECHA_PAGO)
  SELECT p.ID_PERSONA, ?, 1, NULL
  FROM persona p
  WHERE p.ID_ROL = 2
    AND p.ID_ESTADO = 1
    AND COALESCE(p.DIA_PAGO,1) <= ?
    AND (
    p.TIPO_PAGO = 'Mensual'
    OR (p.TIPO_PAGO = 'Anual' 
        AND DATE_ADD(p.FECHA_INICIO_PLAN, INTERVAL 1 YEAR) <= CURDATE()
    )
)
    AND NOT EXISTS (
      SELECT 1 FROM mensualidad m
      WHERE m.ID_PERSONA = p.ID_PERSONA
        AND m.MES = ?
    )
");
if ($st_ins) {
  bind_dynamic($st_ins, [$mes_actual_real, $dia_hoy, $mes_actual_real]);
  mysqli_stmt_execute($st_ins);
  mysqli_stmt_close($st_ins);
}

// ===================== Aquí consultas quién debe o quién ya pagó según el mes =====================
// Deudores (Estado Pago 1 = Pendiente) o Pagados (Estado Pago 2)
// Vamos a traer TODO del mes seleccionado y luego separar en listas
$sql_pagos_mes = "
  SELECT
    p.ID_PERSONA,
    p.PRIMER_NOMBRE,
    p.SEGUNDO_NOMBRE,
    p.PRIMER_APELLIDO,
    p.SEGUNDO_APELLIDO,
    p.ID_ESTADO,
    m.MES,
    m.ID_ESTADO_PAGO,
    m.FECHA_PAGO
  FROM persona p
  JOIN mensualidad m ON p.ID_PERSONA = m.ID_PERSONA
  WHERE p.ID_ROL = 2
    AND m.MES = ?
";

// Aplicar filtro de estado UNIFICADO (si no es 'todos' o vacio)
if ($estado_global !== '' && $estado_global !== 'todos') {
  $sql_pagos_mes .= " AND p.ID_ESTADO = " . (int) $estado_global;
}

$sql_pagos_mes .= " ORDER BY p.PRIMER_APELLIDO, p.PRIMER_NOMBRE";

$st = mysqli_prepare($mysqli, $sql_pagos_mes);
mysqli_stmt_bind_param($st, "s", $mes_filtro);
mysqli_stmt_execute($st);
$resultPagosMes = mysqli_stmt_get_result($st);
$todos_pagos_mes = mysqli_fetch_all($resultPagosMes, MYSQLI_ASSOC);
mysqli_stmt_close($st);

// Aquí separas los resultados en listas distintas:
$deudores_mensuales = [];
$pagados_mensuales = [];

foreach ($todos_pagos_mes as $pm) {
  if ((int) $pm['ID_ESTADO_PAGO'] === 1) { // Pendiente
    $deudores_mensuales[] = $pm;
  } elseif ((int) $pm['ID_ESTADO_PAGO'] === 2) { // Pagado
    $pagados_mensuales[] = $pm;
  }
}


// Aquí revisas los deudores anuales:
$anio_actual = (int) date('Y');
$sql_deudores_anual = "
  SELECT p.ID_PERSONA, p.PRIMER_NOMBRE, p.SEGUNDO_NOMBRE, p.PRIMER_APELLIDO, p.SEGUNDO_APELLIDO
  FROM persona p
  LEFT JOIN pagos_anuales pa ON pa.ID_PERSONA = p.ID_PERSONA AND pa.ANIO = ?
  WHERE p.ID_ROL = 2
    AND p.ID_ESTADO = 1
    AND COALESCE(p.TIPO_PAGO,'Mensual') = 'Anual'
    AND (pa.ID_PAGO_ANUAL IS NULL OR pa.ID_ESTADO_PAGO = 1)
  ORDER BY p.PRIMER_APELLIDO, p.PRIMER_NOMBRE
";
$st2 = mysqli_prepare($mysqli, $sql_deudores_anual);
if ($st2) {
  mysqli_stmt_bind_param($st2, 'i', $anio_actual);
  mysqli_stmt_execute($st2);
  $resultDeudoresAnual = mysqli_stmt_get_result($st2);
  $deudores_anuales_raw = mysqli_fetch_all($resultDeudoresAnual, MYSQLI_ASSOC);
  mysqli_stmt_close($st2);
} else {
  $deudores_anuales_raw = [];
}

$deudores_anuales = [];
foreach ($deudores_anuales_raw as $r) {
  $deudores_anuales[] = [
    'ID_PERSONA' => $r['ID_PERSONA'],
    'PRIMER_NOMBRE' => $r['PRIMER_NOMBRE'],
    'SEGUNDO_NOMBRE' => $r['SEGUNDO_NOMBRE'],
    'PRIMER_APELLIDO' => $r['PRIMER_APELLIDO'],
    'SEGUNDO_APELLIDO' => $r['SEGUNDO_APELLIDO'],
    'MES' => (string) $anio_actual,
    'TIPO_DEUDA' => 'Anual'
  ];
}

$deudores = array_merge($deudores_mensuales, $deudores_anuales);

/* =========== Estos son los estudiantes que deben pagar en los próximos 7 días ========== */
$hoy = (int) date('j');
$max_dia = (int) date('t');
$limite = $hoy + 7;

$condicion_rango = ($limite <= $max_dia)
  ? "p.DIA_PAGO > $hoy AND p.DIA_PAGO <= $limite"
  : "(p.DIA_PAGO > $hoy OR p.DIA_PAGO <= " . ($limite - $max_dia) . ")";

$sql_proximos = "
    SELECT 
        p.ID_PERSONA,
        p.PRIMER_NOMBRE,
        p.SEGUNDO_NOMBRE,
        p.PRIMER_APELLIDO,
        p.SEGUNDO_APELLIDO,
        p.DIA_PAGO,
        p.CELULAR AS CELULAR_ALUMNO,
        a.CELULAR AS CELULAR_ACUDIENTE
    FROM persona p
    LEFT JOIN acudiente a ON a.ID_PERSONA = p.ID_PERSONA
    WHERE p.ID_ROL = 2 
      AND p.ID_ESTADO = 1 
      AND $condicion_rango
    ORDER BY p.DIA_PAGO, p.PRIMER_APELLIDO, p.PRIMER_NOMBRE
";

$resultProximos = mysqli_query($mysqli, $sql_proximos);
$proximos_pagan = $resultProximos ? mysqli_fetch_all($resultProximos, MYSQLI_ASSOC) : [];

// Este es el texto que envías por WhatsApp para recordar el pago:
$mensajePagoTexto = "Hola, un saludo de MDKBoyacá. Te recordamos que tu pago de mensualidad está próximo. Gracias por tu puntualidad.";
$mensajePagoUrl = urlencode($mensajePagoTexto);


/* =========== Aquí ves quiénes pagan hoy mismo ========== */
$hoy = (int) date('j');

$sql_pagan_hoy = "
SELECT ID_PERSONA, PRIMER_NOMBRE, SEGUNDO_NOMBRE, PRIMER_APELLIDO, SEGUNDO_APELLIDO, DIA_PAGO
FROM persona
WHERE ID_ROL = 2 
  AND ID_ESTADO = 1 
  AND DIA_PAGO = ?
ORDER BY PRIMER_APELLIDO, PRIMER_NOMBRE
";

$st = mysqli_prepare($mysqli, $sql_pagan_hoy);
mysqli_stmt_bind_param($st, 'i', $hoy);
mysqli_stmt_execute($st);
$resultPaganHoy = mysqli_stmt_get_result($st);
$estudiantes_pagan_hoy = mysqli_fetch_all($resultPaganHoy, MYSQLI_ASSOC);
mysqli_stmt_close($st);

// ===================== Aquí gestionas el cambio de estado de pago si envías el formulario =====================
if (isset($_POST['accion_pago'], $_POST['id_persona']) && in_array($_POST['accion_pago'], ['pagar', 'pendiente'])) {
  $idp = (int) $_POST['id_persona'];
  $mes_actual = date('Y-m');

  if ($_POST['accion_pago'] === 'pagar') {
    $stp = mysqli_prepare($mysqli, "
      UPDATE mensualidad
      SET ID_ESTADO_PAGO = 2, FECHA_PAGO = NOW()
      WHERE ID_PERSONA = ? AND MES = ?
    ");
  } else {
    $stp = mysqli_prepare($mysqli, "
      UPDATE mensualidad
      SET ID_ESTADO_PAGO = 1, FECHA_PAGO = NULL
      WHERE ID_PERSONA = ? AND MES = ?
    ");
  }

  if ($stp) {
    mysqli_stmt_bind_param($stp, "is", $idp, $mes_actual);
    mysqli_stmt_execute($stp);
    mysqli_stmt_close($stp);
  }
  header("Location: admin.php");
  exit;
}

/* ===================== FUNCIONES ===================== */
function esc($s)
{
  return htmlspecialchars((string) ($s ?? ''), ENT_QUOTES, 'UTF-8');
}
function calc_age($ymd)
{
  if (!$ymd)
    return null;
  $d = DateTime::createFromFormat('Y-m-d', $ymd);
  if (!$d)
    return null;
  return (int) $d->diff(new DateTime('today'))->y;
}
function slugify($str)
{
  $str = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', trim($str));
  $str = strtolower($str);
  $str = preg_replace('/[^a-z0-9]+/', '-', $str);
  return trim($str, '-') ?: 'user';
}
function base_username($first, $last)
{
  $f = str_replace('-', '', slugify($first));
  $l = str_replace('-', '', slugify($last));
  return ($f ?: 'user') . ($l ?: 'mdk');
}
function username_unique($db, $base, $exclude_id = null)
{
  $u = $base;
  $i = 0;
  while (true) {
    if ($exclude_id) {
      $st = mysqli_prepare($db, "SELECT 1 FROM persona WHERE USUARIO=? AND ID_PERSONA<>? LIMIT 1");
      mysqli_stmt_bind_param($st, 'si', $u, $exclude_id);
    } else {
      $st = mysqli_prepare($db, "SELECT 1 FROM persona WHERE USUARIO=? LIMIT 1");
      mysqli_stmt_bind_param($st, 's', $u);
    }
    mysqli_stmt_execute($st);
    $res = mysqli_stmt_get_result($st);
    if (!mysqli_fetch_row($res))
      return $u;
    $i++;
    $u = $base . $i;
  }
}
function ensure_uploads_dir()
{
  $d = __DIR__ . '/uploads';
  if (!is_dir($d))
    @mkdir($d, 0775, true);
  return $d;
}
function calc_nivel_auto($c)
{
  $c = strtolower($c);

  // Principiante
  if (
    strpos($c, 'blanco') !== false
    || strpos($c, 'amarill') !== false
    || strpos($c, 'verde') !== false
  )
    return 'Principiante';

  // Avanzado
  if (
    strpos($c, 'azul') !== false
    || strpos($c, 'roj') !== false
    || strpos($c, 'franja negra') !== false
    || strpos($c, 'franja') !== false
  )
    return 'Avanzado';

  // Superior
  if (strpos($c, 'negro') !== false || strpos($c, 'negra') !== false)
    return 'Superior';

  return 'Principiante';
}


/* ===================== Aquí tienes la opción de eliminar estudiantes ===================== */
if (isset($_GET['delete'])) {
  $id = (int) $_GET['delete'];

  // Borrar dependencias que NO tienen ON DELETE CASCADE
  $tables_to_delete = [
    "asistencia" => "ID_PERSONA",
    "estudiante_evento" => "ID_PERSONA",
    "historial_cinturon" => "id_persona",
    "factura" => "ID_PERSONA",
    // pedidos -> pedido_items tiene ON DELETE CASCADE; al borrar pedidos se borran sus items
    "pedidos" => "ID_PERSONA",
  ];

  foreach ($tables_to_delete as $tbl => $col) {
    $sql = "DELETE FROM `$tbl` WHERE `$col` = ?";
    $st = mysqli_prepare($mysqli, $sql);
    if ($st) {
      mysqli_stmt_bind_param($st, 'i', $id);
      mysqli_stmt_execute($st);
      mysqli_stmt_close($st);
    }
  }

  // Tablas que ya borrabas (eventos_personales tiene ON DELETE CASCADE pero no pasa nada borrarlo)
  $st = mysqli_prepare($mysqli, "DELETE FROM eventos_personales WHERE ID_PERSONA=?");
  if ($st) {
    mysqli_stmt_bind_param($st, 'i', $id);
    mysqli_stmt_execute($st);
    mysqli_stmt_close($st);
  }

  $st = mysqli_prepare($mysqli, "DELETE FROM acudiente WHERE ID_PERSONA=?");
  if ($st) {
    mysqli_stmt_bind_param($st, 'i', $id);
    mysqli_stmt_execute($st);
    mysqli_stmt_close($st);
  }

  // Finalmente borrar persona (nota: mantengo la condición ID_ROL=2 como tenías)
  $st = mysqli_prepare($mysqli, "DELETE FROM persona WHERE ID_PERSONA=? AND ID_ROL=2");
  if ($st) {
    mysqli_stmt_bind_param($st, 'i', $id);
    mysqli_stmt_execute($st);
    mysqli_stmt_close($st);
  }

  header('Location: admin.php');
  exit;
}

/* ===================== Aquí cargas los datos por si quieres editar a alguien ===================== */
$editing = false;
$edit_data = null;
$eventos_edit = [];
if (isset($_GET['edit'])) {
  $editing = true;
  $edit_id = (int) $_GET['edit'];
  $st = mysqli_prepare($mysqli, "SELECT p.*, a.NOMBRE_COMPLETO as ACUDIENTE_NOMBRE_COMPLETO, a.EMPRESA as ACUDIENTE_EMPRESA, a.EMAIL as ACUDIENTE_EMAIL, a.CELULAR as ACUDIENTE_CELULAR, a.CARGO as ACUDIENTE_CARGO FROM persona p LEFT JOIN acudiente a ON p.ID_PERSONA = a.ID_PERSONA WHERE p.ID_PERSONA=? AND p.ID_ROL=2");
  mysqli_stmt_bind_param($st, 'i', $edit_id);
  mysqli_stmt_execute($st);
  $res = mysqli_stmt_get_result($st);
  $edit_data = mysqli_fetch_assoc($res);
  mysqli_stmt_close($st);
  if ($edit_data) {
    $st2 = mysqli_prepare($mysqli, "SELECT DESCRIPCION,FECHA FROM eventos_personales WHERE ID_PERSONA=? ORDER BY FECHA DESC");
    mysqli_stmt_bind_param($st2, 'i', $edit_id);
    mysqli_stmt_execute($st2);
    $res2 = mysqli_stmt_get_result($st2);
    while ($r = mysqli_fetch_assoc($res2)) {
      $nom = '';
      $med = '-';
      if (strpos($r['DESCRIPCION'], 'Evento:') !== false) {
        foreach (explode(';', $r['DESCRIPCION']) as $p) {
          $p = trim($p);
          if (stripos($p, 'Evento:') === 0)
            $nom = trim(substr($p, 7));
          if (stripos($p, 'Medalla:') === 0)
            $med = trim(substr($p, 8));
        }
      }
      $eventos_edit[] = ['nombre' => $nom, 'fecha' => $r['FECHA'], 'medalla' => $med];
    }
    mysqli_stmt_close($st2);
  }

  // Meses pagados del estudiante para el modal
  $meses_pagados_del_estudiante = [];
  $st_m = mysqli_prepare($mysqli, "SELECT MES FROM mensualidad WHERE ID_PERSONA = ? AND ID_ESTADO_PAGO = 2 ORDER BY MES");
  mysqli_stmt_bind_param($st_m, 'i', $edit_id);
  mysqli_stmt_execute($st_m);
  $res_m = mysqli_stmt_get_result($st_m);
  while ($row_m = mysqli_fetch_assoc($res_m)) {
    $meses_pagados_del_estudiante[] = $row_m['MES']; // 'YYYY-MM'
  }
  mysqli_stmt_close($st_m);

}

/* ===================== Aquí guardas o actualizas la información del estudiante ===================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && in_array($_POST['action'], ['guardar_estudiante', 'editar_estudiante'], true)) {
  $is_edit = $_POST['action'] === 'editar_estudiante';
  $id_edit = $is_edit ? (int) ($_POST['id_persona'] ?? 0) : 0;

  // --- Datos básicos form ---
  $p_nombre = trim($_POST['primer-nombre'] ?? '');
  $s_nombre = trim($_POST['segundo-nombre'] ?? '');
  $p_apellido = trim($_POST['primer-apellido'] ?? '');
  $s_apellido = trim($_POST['segundo-apellido'] ?? '');
  $tipo_doc = trim($_POST['tipo-documento'] ?? 'CC');
  $documento = trim($_POST['numero-documento'] ?? '');
  $fecha_nac = trim($_POST['fecha-nacimiento'] ?? '');
  $id_cinturon = (int) ($_POST['cinturon-actual'] ?? 1);
  $dan_val = ($_POST['dan'] === '' ? null : (int) $_POST['dan']);
  $dias_arr = $_POST['dias'] ?? [];
  if (!is_array($dias_arr))
    $dias_arr = [$dias_arr];
  $dias_str = implode(',', array_map('trim', $dias_arr));
  $fecha_inicio = trim($_POST['fecha-inicio'] ?? '');
  $lugar_nacimiento = trim($_POST['lugar-nacimiento'] ?? '');
  $direccion = trim($_POST['direccion'] ?? '');
  $celular_estudiante = trim($_POST['celular-estudiante'] ?? '');
  $correo_estudiante = trim($_POST['correo-estudiante'] ?? '');
  $lugar_estudia = trim($_POST['lugar-estudia'] ?? '');
  $eps = trim($_POST['eps'] ?? '');
  $intensidad_horaria = (int) ($_POST['intensidad-horaria'] ?? 0);
  $precio_mensual = (float) ($_POST['precio-mensual'] ?? 0.0);

  // --- Día de pago ---
  $dia_pago = (int) ($_POST['dia-pago'] ?? 1);
  $tipo_pago = $_POST['tipo_pago'] ?? 'Mensual';
  $fecha_inicio_plan = trim($_POST['fecha-inicio-plan'] ?? null);

  // Acudiente:
  $nombre_acudiente = trim($_POST['nombre-acudiente'] ?? '');
  $empresa_acudiente = trim($_POST['empresa-acudiente'] ?? '');
  $email_acudiente = trim($_POST['email-acudiente'] ?? '');
  $celular_acudiente = trim($_POST['celular-acudiente'] ?? '');
  $cargo_acudiente = trim($_POST['cargo-acudiente'] ?? '');

  if ($p_nombre === '' || $p_apellido === '' || $documento === '' || $fecha_nac === '') {
    $_SESSION['form_error'] = 'Completa los campos requeridos.';
    header('Location: admin.php' . ($is_edit ? ('?edit=' . $id_edit) : ''));
    exit;
  }

  $edad = calc_age($fecha_nac) ?? 0;
  $usuario = username_unique($mysqli, base_username($p_nombre, $p_apellido), $is_edit ? $id_edit : null);
  $hash = password_hash($documento, PASSWORD_DEFAULT);

  // --- Foto upload ---
  $stc = mysqli_prepare($mysqli, "SELECT NOMBRE FROM cinturon WHERE ID_CINTURON=?");
  mysqli_stmt_bind_param($stc, 'i', $id_cinturon);
  mysqli_stmt_execute($stc);
  $res_cint = mysqli_stmt_get_result($stc);
  $cint_row = mysqli_fetch_assoc($res_cint);
  mysqli_stmt_close($stc);
  $nivel = calc_nivel_auto($cint_row['NOMBRE'] ?? '');

  $fotoPath = $is_edit ? ($_POST['foto_actual'] ?? null) : null;
  if (!empty($_FILES['foto']['name'])) {
    ensure_uploads_dir();
    $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
    if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']) && is_uploaded_file($_FILES['foto']['tmp_name'])) {
      $destRel = 'uploads/' . uniqid('foto_') . '.' . $ext;
      if (move_uploaded_file($_FILES['foto']['tmp_name'], __DIR__ . '/' . $destRel))
        $fotoPath = $destRel;
    }
  }

  // === Capturar el cinturón actual antes de actualizar (para historial) ===
  $old_cinturon = 0;
  if ($is_edit && $id_edit) {
    $st_old = mysqli_prepare($mysqli, "SELECT ID_CINTURON FROM persona WHERE ID_PERSONA=? LIMIT 1");
    mysqli_stmt_bind_param($st_old, 'i', $id_edit);
    mysqli_stmt_execute($st_old);
    $res_old = mysqli_stmt_get_result($st_old);
    $r_old = mysqli_fetch_assoc($res_old);
    mysqli_stmt_close($st_old);
    $old_cinturon = $r_old ? (int) $r_old['ID_CINTURON'] : 0;
  }

  // ==== UPDATE or INSERT persona usando bind dinámico para evitar mismatches ====
  if ($is_edit) {
    $sql = "UPDATE persona SET PRIMER_NOMBRE=?, SEGUNDO_NOMBRE=?, PRIMER_APELLIDO=?, SEGUNDO_APELLIDO=?, TIPO_DOCUMENTO=?, DOCUMENTO=?, FECHA_NACIMIENTO=?, EDAD=?, USUARIO=?, FOTO=?, ID_CINTURON=?, DAN=?, NIVEL=?, DIAS_ENTRENAMIENTO=?, FECHA_INICIO=?, LUGAR_NACIMIENTO=?, DIRECCION=?, CELULAR=?, CORREO=?, LUGAR_ESTUDIA=?, EPS=?, INTENSIDAD_HORARIA=?, PRECIO_MENSUAL=?, TIPO_PAGO=?, FECHA_INICIO_PLAN=?, DIA_PAGO=? WHERE ID_PERSONA=? AND ID_ROL=2";
    $st = mysqli_prepare($mysqli, $sql);
    $params = [
      $p_nombre,
      $s_nombre,
      $p_apellido,
      $s_apellido,
      $tipo_doc,
      $documento,
      $fecha_nac,
      (int) $edad,
      $usuario,
      $fotoPath,
      (int) $id_cinturon,
      $dan_val === null ? null : (int) $dan_val,
      $nivel,
      $dias_str,
      $fecha_inicio,
      $lugar_nacimiento,
      $direccion,
      $celular_estudiante,
      $correo_estudiante,
      $lugar_estudia,
      $eps,
      (int) $intensidad_horaria,
      (float) $precio_mensual,
      $tipo_pago,
      $fecha_inicio_plan,
      (int) $dia_pago,
      (int) $id_edit
    ];
    bind_dynamic($st, $params);
    mysqli_stmt_execute($st);

    // actualizar/insertar acudiente
    $st_acu_check = mysqli_prepare($mysqli, "SELECT ID_ACUDIENTE FROM acudiente WHERE ID_PERSONA = ?");
    mysqli_stmt_bind_param($st_acu_check, 'i', $id_edit);
    mysqli_stmt_execute($st_acu_check);
    $res_acu_check = mysqli_stmt_get_result($st_acu_check);
    $exists_acu = mysqli_fetch_assoc($res_acu_check);
    mysqli_stmt_close($st_acu_check);

    if ($exists_acu) {
      $st_acu = mysqli_prepare($mysqli, "UPDATE acudiente SET NOMBRE_COMPLETO=?, EMPRESA=?, EMAIL=?, CELULAR=?, CARGO=? WHERE ID_PERSONA=?");
      mysqli_stmt_bind_param($st_acu, 'sssssi', $nombre_acudiente, $empresa_acudiente, $email_acudiente, $celular_acudiente, $cargo_acudiente, $id_edit);
      mysqli_stmt_execute($st_acu);
      mysqli_stmt_close($st_acu);
    } else if ($nombre_acudiente) {
      $st_acu = mysqli_prepare($mysqli, "INSERT INTO acudiente (ID_PERSONA, NOMBRE_COMPLETO, EMPRESA, EMAIL, CELULAR, CARGO) VALUES (?, ?, ?, ?, ?, ?)");
      mysqli_stmt_bind_param($st_acu, 'isssss', $id_edit, $nombre_acudiente, $empresa_acudiente, $email_acudiente, $celular_acudiente, $cargo_acudiente);
      mysqli_stmt_execute($st_acu);
      mysqli_stmt_close($st_acu);
    }

    $id_persona_eventos = $id_edit;
  } else {
    $sql = "INSERT INTO persona(PRIMER_NOMBRE, SEGUNDO_NOMBRE, PRIMER_APELLIDO, SEGUNDO_APELLIDO, TIPO_DOCUMENTO, DOCUMENTO, FECHA_NACIMIENTO, EDAD, USUARIO, CONTRASENA, FOTO, ID_CINTURON, DAN, NIVEL, DIAS_ENTRENAMIENTO, ID_ROL, ID_ESTADO, FECHA_INICIO, LUGAR_NACIMIENTO, DIRECCION, CELULAR, CORREO, LUGAR_ESTUDIA, EPS, INTENSIDAD_HORARIA, PRECIO_MENSUAL, TIPO_PAGO, FECHA_INICIO_PLAN, DIA_PAGO) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
    $st = mysqli_prepare($mysqli, $sql);
    $id_rol = 2;
    $id_estado = 1;
    $params = [
      $p_nombre,
      $s_nombre,
      $p_apellido,
      $s_apellido,
      $tipo_doc,
      $documento,
      $fecha_nac,
      (int) $edad,
      $usuario,
      $hash,
      $fotoPath,
      (int) $id_cinturon,
      $dan_val === null ? null : (int) $dan_val,
      $nivel,
      $dias_str,
      $id_rol,
      $id_estado,
      $fecha_inicio,
      $lugar_nacimiento,
      $direccion,
      $celular_estudiante,
      $correo_estudiante,
      $lugar_estudia,
      $eps,
      (int) $intensidad_horaria,
      (float) $precio_mensual,
      $tipo_pago,
      $fecha_inicio_plan,
      (int) $dia_pago
    ];
    bind_dynamic($st, $params);
    mysqli_stmt_execute($st);
    $new_id = mysqli_insert_id($mysqli);
    if ($nombre_acudiente) {
      $st_acu = mysqli_prepare($mysqli, "INSERT INTO acudiente (ID_PERSONA, NOMBRE_COMPLETO, EMPRESA, EMAIL, CELULAR, CARGO) VALUES (?, ?, ?, ?, ?, ?)");
      mysqli_stmt_bind_param($st_acu, 'isssss', $new_id, $nombre_acudiente, $empresa_acudiente, $email_acudiente, $celular_acudiente, $cargo_acudiente);
      mysqli_stmt_execute($st_acu);
      mysqli_stmt_close($st_acu);
    }
    $id_persona_eventos = $new_id;
  }

  /* ==== eventos_personales ==== */
  if ($id_persona_eventos > 0) {
    $st = mysqli_prepare($mysqli, "DELETE FROM eventos_personales WHERE ID_PERSONA=?");
    mysqli_stmt_bind_param($st, 'i', $id_persona_eventos);
    mysqli_stmt_execute($st);
    mysqli_stmt_close($st);
    $eventos = json_decode($_POST['eventos_json'] ?? '[]', true) ?: [];
    foreach ($eventos as $ev) {
      $desc = 'Evento: ' . trim($ev['nombre'] ?? '') . '; Medalla: ' . trim($ev['medalla'] ?? '-');
      $fec = $ev['fecha'] ?? date('Y-m-d');
      $hora = '00:00:00';
      $st = mysqli_prepare($mysqli, "INSERT INTO eventos_personales (ID_PERSONA, DESCRIPCION, FECHA, HORA) VALUES (?, ?, ?, ?)");
      mysqli_stmt_bind_param($st, 'isss', $id_persona_eventos, $desc, $fec, $hora);
      mysqli_stmt_execute($st);
      mysqli_stmt_close($st);
    }
  }

  /* ==== historial_cinturon ==== */
  if ($id_persona_eventos > 0) {
    $fecha_cinturon = trim($_POST['fecha-cinturon'] ?? '');
    if ($fecha_cinturon === '')
      $fecha_cinturon = date('Y-m-d');

    if ($is_edit) {
      if ($old_cinturon !== (int) $id_cinturon) {
        $st_hist = mysqli_prepare($mysqli, "INSERT INTO historial_cinturon (ID_PERSONA, ID_CINTURON, FECHA_OBTENCION) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($st_hist, 'iis', $id_persona_eventos, $id_cinturon, $fecha_cinturon);
        mysqli_stmt_execute($st_hist);
        mysqli_stmt_close($st_hist);
      }
    } else {
      $st_hist = mysqli_prepare($mysqli, "INSERT INTO historial_cinturon (ID_PERSONA, ID_CINTURON, FECHA_OBTENCION) VALUES (?, ?, ?)");
      mysqli_stmt_bind_param($st_hist, 'iis', $id_persona_eventos, $id_cinturon, $fecha_cinturon);
      mysqli_stmt_execute($st_hist);
      mysqli_stmt_close($st_hist);
    }
  }

  // ===== Aquí guardas los meses que el estudiante marcó como pagados =====
  $meses_pagados_json = $_POST['meses_pagados'] ?? '[]';
  $meses_pagados = json_decode($meses_pagados_json, true); // array "YYYY-MM"

  if ($id_persona_eventos > 0 && is_array($meses_pagados)) {
    // Borrar todos los registros de mensualidad de ese alumno (opcional)
    $st_del = mysqli_prepare($mysqli, "DELETE FROM mensualidad WHERE ID_PERSONA = ?");
    mysqli_stmt_bind_param($st_del, 'i', $id_persona_eventos);
    mysqli_stmt_execute($st_del);
    mysqli_stmt_close($st_del);

    // Insertar los meses marcados como pagados
    foreach ($meses_pagados as $mes) {
      $st_ins_m = mysqli_prepare($mysqli, "
                INSERT INTO mensualidad (ID_PERSONA, MES, ID_ESTADO_PAGO, FECHA_PAGO)
                VALUES (?, ?, 2, NOW())
                ON DUPLICATE KEY UPDATE 
                    ID_ESTADO_PAGO = 2,
                    FECHA_PAGO = NOW()
            ");
      mysqli_stmt_bind_param($st_ins_m, 'is', $id_persona_eventos, $mes);
      mysqli_stmt_execute($st_ins_m);
      mysqli_stmt_close($st_ins_m);
    }
  }

  header('Location: admin.php');
  exit;
}


/* ===================== Aquí tienes la lista completa de estudiantes con sus filtros ===================== */
$nivel_f = $_GET['filtro-nivel'] ?? '';
$cint_f = $_GET['filtro-cinturon'] ?? '';
$edad_min = $_GET['filtro-edad-min'] ?? '';
$edad_max = $_GET['filtro-edad-max'] ?? '';
$dia_f = $_GET['filtro-dia'] ?? '';
$dan_f = $_GET['filtro-dan'] ?? '';
$est_estado_f = $estado_global; // Usar la variable unificada definida arriba

$where = ["p.ID_ROL=2"];



$types = '';
$params = [];

if ($est_estado_f !== '') {
  $where[] = "p.ID_ESTADO=?";
  $types .= 'i';
  $params[] = (int) $est_estado_f;
}

if ($nivel_f !== '') {
  $where[] = "p.NIVEL=?";
  $types .= 's';
  $params[] = $nivel_f;
}
if ($cint_f !== '') {
  $where[] = "p.ID_CINTURON=?";
  $types .= 'i';
  $params[] = (int) $cint_f;
}
if ($edad_min !== '') {
  $where[] = "p.EDAD>=?";
  $types .= 'i';
  $params[] = (int) $edad_min;
}
if ($edad_max !== '') {
  $where[] = "p.EDAD<=?";
  $types .= 'i';
  $params[] = (int) $edad_max;
}
if ($dia_f !== '') {
  $where[] = "p.DIAS_ENTRENAMIENTO LIKE ?";
  $types .= 's';
  $params[] = '%' . $dia_f . '%';
}
if ($dan_f !== '') {
  $where[] = "p.DAN=?";
  $types .= 'i';
  $params[] = (int) $dan_f;
}

$sql_list = "
    SELECT p.*, c.NOMBRE AS CINTURON_NOMBRE, a.NOMBRE_COMPLETO as ACUDIENTE_NOMBRE_COMPLETO,
    a.EMPRESA as ACUDIENTE_EMPRESA, a.EMAIL as ACUDIENTE_EMAIL, a.CELULAR as ACUDIENTE_CELULAR, a.CARGO as ACUDIENTE_CARGO
    FROM persona p 
    LEFT JOIN cinturon c ON c.ID_CINTURON=p.ID_CINTURON
    LEFT JOIN acudiente a ON a.ID_PERSONA=p.ID_PERSONA
    WHERE " . implode(' AND ', $where) . " ORDER BY p.PRIMER_APELLIDO, p.PRIMER_NOMBRE";

$st = mysqli_prepare($mysqli, $sql_list);
if ($st && $params) {
  // Se usa bind_dynamic, aquí adaptado para mysqli
  $bind_names[] = $types;
  for ($i = 0; $i < count($params); $i++) {
    $bind_name = 'bind' . $i;
    $$bind_name = $params[$i];
    $bind_names[] = &$$bind_name;
  }
  call_user_func_array(array($st, 'bind_param'), $bind_names);
}
if ($st)
  mysqli_stmt_execute($st);
$resultEstudiantes = $st ? mysqli_stmt_get_result($st) : null;
$estudiantes = $resultEstudiantes ? mysqli_fetch_all($resultEstudiantes, MYSQLI_ASSOC) : [];

$cinturones_res = mysqli_query($mysqli, "SELECT ID_CINTURON,NOMBRE FROM cinturon ORDER BY ID_CINTURON");
$cinturones = mysqli_fetch_all($cinturones_res, MYSQLI_ASSOC);

$mes_actual = date('Y-m');
$pago_estado = [];

if ($estudiantes) {
  $ids = array_column($estudiantes, 'ID_PERSONA');
  if ($ids) {
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $sql_pm = "SELECT ID_PERSONA,ID_ESTADO_PAGO FROM mensualidad WHERE MES=? AND ID_PERSONA IN ($placeholders)";
    $st = mysqli_prepare($mysqli, $sql_pm);

    $bindParams = array_merge([$mes_actual], $ids);
    $types_pm = str_repeat('i', count($ids));
    $bindTypes = 's' . $types_pm;
    $bind_names_pm[] = $bindTypes;
    for ($i = 0; $i < count($bindParams); $i++) {
      $bind_name = 'bind' . $i;
      $$bind_name = $bindParams[$i];
      $bind_names_pm[] = &$$bind_name;
    }
    call_user_func_array(array($st, 'bind_param'), $bind_names_pm);

    mysqli_stmt_execute($st);
    $res = mysqli_stmt_get_result($st);
    while ($r = mysqli_fetch_assoc($res)) {
      $pago_estado[(int) $r['ID_PERSONA']] = (int) $r['ID_ESTADO_PAGO'];
    }
    mysqli_stmt_close($st);
  }
}

/* ===================== 8) ÚLTIMO EVENTO ===================== */
$evento_ultimo = [];
$evento_medalla = [];
foreach ($estudiantes as $e) {
  $idp = (int) $e['ID_PERSONA'];
  $st = mysqli_prepare($mysqli, "SELECT DESCRIPCION, FECHA FROM eventos_personales WHERE ID_PERSONA=? ORDER BY FECHA DESC LIMIT 1");
  mysqli_stmt_bind_param($st, 'i', $idp);
  mysqli_stmt_execute($st);
  $res_evt = mysqli_stmt_get_result($st);
  if ($row = mysqli_fetch_assoc($res_evt)) {
    $nombre = '';
    $medalla = '-';
    foreach (explode(';', $row['DESCRIPCION']) as $p) {
      $p = trim($p);
      if (stripos($p, 'Evento:') === 0)
        $nombre = trim(substr($p, 7));
      if (stripos($p, 'Medalla:') === 0)
        $medalla = trim(substr($p, 8));
    }
    $evento_ultimo[$idp] = $nombre !== '' ? ($nombre . ' (' . $row['FECHA'] . ')') : '-';
    $evento_medalla[$idp] = $medalla;
  }
  mysqli_stmt_close($st);
}


// ===================== 9. MESES PAGADOS / PENDIENTES POR ESTUDIANTE =====================

// Mapa de meses (ya lo usas arriba para deudores)
$meses_map = [
  "January" => "Enero",
  "February" => "Febrero",
  "March" => "Marzo",
  "April" => "Abril",
  "May" => "Mayo",
  "June" => "Junio",
  "July" => "Julio",
  "August" => "Agosto",
  "September" => "Septiembre",
  "October" => "Octubre",
  "November" => "Noviembre",
  "December" => "Diciembre"
];

$meses_pagados_por_est = [];
$meses_pendientes_por_est = [];

if (!empty($estudiantes)) {
  $ids = array_column($estudiantes, 'ID_PERSONA');
  $ids = array_map('intval', $ids);

  if ($ids) {
    $placeholders = implode(',', array_fill(0, count($ids), '?'));

    // Ajusta el nombre de tabla/campos a tu BD real
    // Supongo tabla mensualidad(MES 'YYYY-MM', ID_ESTADO_PAGO 1=pendiente 2=pagado)
    $sql_meses = "
            SELECT ID_PERSONA, MES, ID_ESTADO_PAGO
            FROM mensualidad
            WHERE ID_PERSONA IN ($placeholders)
            ORDER BY MES
        ";
    $st_m = mysqli_prepare($mysqli, $sql_meses);

    $types_m = str_repeat('i', count($ids));
    $bind = [];
    $bind[] = $types_m;
    foreach ($ids as $k => $v) {
      $bind[$k + 1] = &$ids[$k];
    }
    call_user_func_array([$st_m, 'bind_param'], $bind);

    mysqli_stmt_execute($st_m);
    $res_m = mysqli_stmt_get_result($st_m);

    while ($row = mysqli_fetch_assoc($res_m)) {
      $idp = (int) $row['ID_PERSONA'];
      $mesRaw = $row['MES'];             // ej: '2025-11'
      $estado = (int) $row['ID_ESTADO_PAGO']; // 1 pendiente, 2 pagado

      // Formatear 'YYYY-MM' a 'Noviembre 2025'
      $mes_fmt = $mesRaw;
      if (preg_match('/^(\d{4})-(\d{2})$/', $mesRaw, $mm)) {
        $yr = (int) $mm[1];
        $mo = (int) $mm[2];
        $mesIng = date('F', mktime(0, 0, 0, $mo, 1, $yr));
        $mesEsp = $meses_map[$mesIng] ?? $mesIng;
        $mes_fmt = $mesEsp . ' ' . $yr;
      }

      if ($estado === 2) {
        $meses_pagados_por_est[$idp][] = $mes_fmt;
      } else {
        $meses_pendientes_por_est[$idp][] = $mes_fmt;
      }
    }

    mysqli_stmt_close($st_m);
  }
}
/* ===================== 10 RENDER (includes) ===================== */

?>





<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Gestión de Estudiantes</title>
  <link rel="stylesheet" href="style.css">
  <link rel="icon" href="img/favicon-96x96.png" sizes="96x96" type="image/png">

</head>

<body>

  <?php
  $estudiantespaganhoy = $estudiantes_pagan_hoy;
  $__deudores = $deudores;
  $__proximos_pagan = $proximos_pagan;

  include './temp/header_admin.php';
  include './temp/contendido_admin.php';
  include './temp/modal_admin.php';
  include './temp/footer.php';
  ?>

</body>

</html>