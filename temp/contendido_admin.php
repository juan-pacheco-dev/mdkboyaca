<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Administrador</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="icon" type="image/png" href="img/favicon-96x96.png" sizes="96x96">
</head>

<body style="margin: 10% 10%;">

    <!-- DASHBOARD SUMMARY -->
    <div class="dashboard-summary" style="display:flex; gap:20px; margin-bottom:30px; flex-wrap:wrap;">

        <!-- VISITAS CARD -->
        <div class="card-summary"
            style="flex:1; min-width:250px; background:#fff; padding:20px; border-radius:12px; box-shadow:0 4px 12px rgba(0,0,0,0.1); display:flex; align-items:center;">
            <div style="font-size:3rem; color:#1565c0; margin-right:20px;">
                👁️
            </div>
            <div>
                <h3 style="margin:0; font-size:1.2rem; color:#555;">Visitas Totales</h3>
                <p style="margin:0; font-size:2rem; font-weight:bold; color:#2c3e50;">
                    <?= number_format($total_visitas) ?>
                </p>
            </div>
        </div>

        <!-- COMMENTS CARD -->
        <a href="admin_comentarios.php" class="card-summary"
            style="flex:1; min-width:250px; background:#fff; padding:20px; border-radius:12px; box-shadow:0 4px 12px rgba(0,0,0,0.1); display:flex; align-items:center; text-decoration:none; transition:transform 0.2s;">
            <div style="font-size:3rem; color:#f39c12; margin-right:20px;">
                💬
            </div>
            <div>
                <h3 style="margin:0; font-size:1.2rem; color:#555;">Comentarios y Feedback</h3>
                <p style="margin:0; font-size:1rem; color:#888;">Gestionar opiniones</p>
            </div>
        </a>

    </div>

    <!-- 🎂 CUMPLEAÑOS HOY / PRÓXIMOS EN GRID -->
    <?php if (!empty($cumpleaneros) || !empty($proxCumpleanos)): ?>
        <div id="notificaciones-admin" class="cumples-grid">

            <!-- COLUMNA IZQUIERDA: CUMPLEAÑOS HOY -->
            <?php if (!empty($cumpleaneros)): ?>
                <div class="cumple-col">
                    <div class="notificacion-cumple">
                        🎉 <strong>¡Cumpleaños Hoy!</strong> 🎂
                        <ul style="margin:10px 0 0 15px;">
                            <?php foreach ($cumpleaneros as $c): ?>
                                <?php
                                $telAlumno = trim($c['CELULAR'] ?? '');
                                $telAcudiente = trim($c['ACUDIENTE_CELULAR'] ?? '');
                                // Si el estudiante tiene celular, usar ese; si no, el del acudiente
                                $numero = $telAlumno !== '' ? $telAlumno : $telAcudiente;
                                ?>
                                <li style="margin-bottom:6px;">
                                    <strong>
                                        <?= esc($c['PRIMER_NOMBRE'] . ' ' . $c['SEGUNDO_NOMBRE'] . ' ' . $c['PRIMER_APELLIDO'] . ' ' . $c['SEGUNDO_APELLIDO']) ?>
                                    </strong>

                                    <?php if ($numero): ?>
                                        <a href="https://wa.me/57<?= esc($numero) ?>?text=<?= $mensajeCumpleUrl ?>" target="_blank"
                                            style="margin-left:10px; padding:4px 8px; font-size:12px; background:#25D366; color:#fff; border-radius:4px; text-decoration:none; display:inline-block;">
                                            Enviar mensaje
                                        </a>
                                    <?php else: ?>
                                        <span style="margin-left:10px; font-size:12px; color:#888;">
                                            Sin número registrado
                                        </span>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>

            <!-- COLUMNA DERECHA: PRÓXIMOS 7 DÍAS -->
            <?php if (!empty($proxCumpleanos)): ?>
                <div class="cumple-col">
                    <div class="alert-info-cumple">
                        <div style="display:flex; justify-content:space-between; align-items:center; cursor:pointer;"
                            onclick="toggleCumpleProx()">
                            <h3 style="margin:0;">🎈 Cumpleaños próximos (7 días)</h3>
                            <span id="flechaCumpleProx" style="font-size: 20px;">▼</span>
                        </div>

                        <div id="listaCumpleProx">
                            <?php foreach ($proxCumpleanos as $c): ?>
                                <?php $dia = date('d', strtotime($c['FECHA_NACIMIENTO'])); ?>
                                <p style="margin:4px 0;">
                                    <strong><?= esc($c['PRIMER_NOMBRE'] . ' ' . $c['SEGUNDO_NOMBRE'] . ' ' . $c['PRIMER_APELLIDO'] . ' ' . $c['SEGUNDO_APELLIDO']) ?></strong>
                                    — Cumple el día <?= $dia ?>
                                </p>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

        </div>

        <script>
            function toggleCumpleProx() {
                const lista = document.getElementById("listaCumpleProx");
                const flecha = document.getElementById("flechaCumpleProx");

                if (!lista || !flecha) return;

                if (lista.style.display === "none" || lista.style.display === "") {
                    lista.style.display = "block";
                    flecha.textContent = "▲";
                } else {
                    lista.style.display = "none";
                    flecha.textContent = "▼";
                }
            }
        </script>
    <?php endif; ?>

    <section class="filtros-estudiantes">
        <h3>Filtrar Estudiantes:</h3>

        <!-- ✅ ESTUDIANTES QUE PAGAN HOY -->
        <?php if (!empty($estudiantes_pagan_hoy)): ?>
            <div class="alert alert-warning" style="margin-bottom:15px; padding:15px; border-radius:10px;">
                <div style="display:flex; justify-content:space-between; align-items:center; cursor:pointer;"
                    onclick="toggleHoy()">
                    <h3 style="margin-top:0;">📅 Estudiantes que pagan hoy</h3>
                    <span id="flechaHoy" style="font-size: 20px;">▼</span>
                </div>

                <div id="listaHoy" style="display:none; margin-top:10px;">
                    <?php foreach ($estudiantes_pagan_hoy as $e): ?>
                        <p style="margin:4px 0;">
                            <strong><?= esc($e['PRIMER_NOMBRE'] . ' ' . $e['SEGUNDO_NOMBRE'] . ' ' . $e['PRIMER_APELLIDO'] . ' ' . $e['SEGUNDO_APELLIDO']) ?></strong>
                            (Día <?= esc($e['DIA_PAGO']) ?>)
                        </p>
                    <?php endforeach; ?>
                </div>
            </div>

            <script>
                function toggleHoy() {
                    const lista = document.getElementById("listaHoy");
                    const flecha = document.getElementById("flechaHoy");

                    if (lista.style.display === "none") {
                        lista.style.display = "block";
                        flecha.textContent = "▲";
                    } else {
                        lista.style.display = "none";
                        flecha.textContent = "▼";
                    }
                }
            </script>
        <?php endif; ?>



        <!-- ✅ ESTUDIANTES QUE PAGAN PRÓXIMAMENTE (7 días) -->
        <?php if (!empty($proximos_pagan)): ?>
            <div class="alert alert-info" style="margin-bottom:15px; padding:15px; border-radius:10px;">

                <div style="display:flex; justify-content:space-between; align-items:center; cursor:pointer;"
                    onclick="toggleDias()">
                    <h3 style="margin-top:0; margin-bottom:0;">⏳ Próximos pagos (7 días)</h3>
                    <span id="flechaDias" style="font-size: 20px;">▼</span>
                </div>

                <div id="listaDias" style="display:none; margin-top:10px;">
                    <?php foreach ($proximos_pagan as $e): ?>
                        <?php
                        // Mismo criterio que en cumpleaños: primero alumno, si no acudiente
                        $telAlumno = trim($e['CELULAR_ALUMNO'] ?? '');
                        $telAcudiente = trim($e['CELULAR_ACUDIENTE'] ?? '');
                        $numero = $telAlumno !== '' ? $telAlumno : $telAcudiente;
                        ?>
                        <p style="margin:4px 0;">
                            <strong><?= esc($e['PRIMER_NOMBRE'] . ' ' . $e['SEGUNDO_NOMBRE'] . ' ' . $e['PRIMER_APELLIDO'] . ' ' . $e['SEGUNDO_APELLIDO']) ?></strong>
                            — paga el día <?= esc($e['DIA_PAGO']) ?>

                            <?php if ($numero): ?>
                                <a href="https://wa.me/57<?= esc($numero) ?>?text=<?= $mensajePagoUrl ?>" target="_blank"
                                    style="margin-left:10px; padding:4px 8px; font-size:12px; background:#25D366; color:#fff; border-radius:4px; text-decoration:none; display:inline-block;">
                                    Recordar pago 💬
                                </a>

                            <?php else: ?>
                                <span style="margin-left:10px; font-size:12px; color:#888;">
                                    Sin número registrado
                                </span>
                            <?php endif; ?>
                        </p>
                    <?php endforeach; ?>
                </div>
            </div>

            <script>
                function toggleDias() {
                    const lista = document.getElementById("listaDias");
                    const flecha = document.getElementById("flechaDias");

                    if (!lista || !flecha) return;

                    if (lista.style.display === "none" || lista.style.display === "") {
                        lista.style.display = "block";
                        flecha.textContent = "▲";
                    } else {
                        lista.style.display = "none";
                        flecha.textContent = "▼";
                    }
                }
            </script>
        <?php endif; ?>



        <!-- ✅ MAPA MESES -->
        <?php
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

        // Obtener nombre del mes filtro o actual
        $mesFiltroObj = DateTime::createFromFormat('Y-m', $mes_filtro);
        $mesFiltroNombre = $mesFiltroObj ? ($meses_map[$mesFiltroObj->format('F')] . ' ' . $mesFiltroObj->format('Y')) : $mes_filtro;
        ?>

        <!-- ======================= 🔎 NUEVOS FILTROS DE PAGO (SEPARADOS) ======================= -->
        <div style="display:flex; gap:20px; flex-wrap:wrap; margin-bottom:20px;">

            <!-- CAJA 1: CAMBIAR MES -->
            <div
                style="flex:1; background:#f1f8ff; padding:15px; border-radius:10px; border:1px solid #cce5ff; min-width:250px;">
                <h3 style="margin-top:0; color:#004085; font-size:1.1rem;">📅 Mes de Pago</h3>
                <form method="GET" action="admin.php" style="display:flex; gap:10px; align-items:center;">
                    <!-- Preserve other params -->
                    <input type="hidden" name="filtro-estado" value="<?= esc($estado_global) ?>">
                    <?php if (isset($_GET['filtro-nivel'])): ?><input type="hidden" name="filtro-nivel"
                            value="<?= esc($_GET['filtro-nivel']) ?>"><?php endif; ?>
                    <?php if (isset($_GET['filtro-cinturon'])): ?><input type="hidden" name="filtro-cinturon"
                            value="<?= esc($_GET['filtro-cinturon']) ?>"><?php endif; ?>

                    <input type="month" id="mes_filtro" name="mes_filtro" value="<?= esc($mes_filtro) ?>"
                        style="padding:6px; border-radius:4px; border:1px solid #ccc; flex:1;">

                    <button type="submit"
                        style="background:#0056b3; color:white; border:none; padding:8px 12px; border-radius:4px; cursor:pointer;">
                        Ver Mes
                    </button>
                </form>
            </div>

            <!-- CAJA 2: FILTRAR LISTA ESTUDIANTES (Controla Abajo) -->
            <div
                style="flex:1; background:#fff3cd; padding:15px; border-radius:10px; border:1px solid #ffeeba; min-width:250px;">
                <h3 style="margin-top:0; color:#856404; font-size:1.1rem;">👥 Filtrar Lista Estudiantes</h3>
                <form method="GET" action="admin.php" style="display:flex; gap:10px; align-items:center;">
                    <!-- Preserve other params -->
                    <input type="hidden" name="mes_filtro" value="<?= esc($mes_filtro) ?>">
                    <?php if (isset($_GET['filtro-nivel'])): ?><input type="hidden" name="filtro-nivel"
                            value="<?= esc($_GET['filtro-nivel']) ?>"><?php endif; ?>
                    <?php if (isset($_GET['filtro-cinturon'])): ?><input type="hidden" name="filtro-cinturon"
                            value="<?= esc($_GET['filtro-cinturon']) ?>"><?php endif; ?>

                    <select name="filtro-estado" id="filtro-estado-top"
                        style="padding:7px; border-radius:4px; border:1px solid #ccc; flex:1;">
                        <option value="1" <?= $estado_global === '1' ? 'selected' : '' ?>>Activos ✅</option>
                        <option value="2" <?= $estado_global === '2' ? 'selected' : '' ?>>Inactivos 🚫</option>
                        <option value="" <?= $estado_global === '' || $estado_global === 'todos' ? 'selected' : '' ?>>Todos
                            🌎</option>
                    </select>

                    <button type="submit"
                        style="background:#d39e00; color:white; border:none; padding:8px 12px; border-radius:4px; cursor:pointer;">
                        Filtrar Abajo
                    </button>
                </form>
            </div>

        </div>

        <div style="display:flex; gap:20px; flex-wrap:wrap;">

            <!-- 🔴 COLUMNA DEUDORES -->
            <div
                style="flex:1; min-width:300px; background:#fff5f5; border-left:6px solid #ef5350; padding:15px; border-radius:8px;">
                <div style="display:flex; justify-content:space-between; align-items:center; cursor:pointer;"
                    onclick="toggleDeudores()">
                    <h3 style="margin:0; color:#c62828;">
                        ❌ Pendientes (<?= count($deudores_mensuales) ?>)
                    </h3>
                    <span id="flechaDeudores" style="font-size: 20px;">▼</span>
                </div>
                <small style="color:#ba000d;">Mes: <?= esc($mesFiltroNombre) ?></small>

                <div id="listaDeudores" style="display:none; margin-top:10px; max-height:300px; overflow-y:auto;">
                    <?php if (empty($deudores_mensuales)): ?>
                        <p style="color:#666; font-style:italic;">¡Nadie debe este mes! 🎉</p>
                    <?php else: ?>
                        <?php foreach ($deudores_mensuales as $d):
                            $nombre = trim($d['PRIMER_NOMBRE'] . ' ' . $d['PRIMER_APELLIDO']);
                            // Estado para mostrar visualmente si es inactivo
                            $es_inactivo = (int) $d['ID_ESTADO'] === 2;
                            $tag_inactivo = $es_inactivo ? '<span style="font-size:10px; background:#ccc; padding:2px 4px; border-radius:3px;">Inactivo</span>' : '';
                            ?>
                            <div
                                style="padding:8px; border-bottom:1px solid #fecaca; display:flex; justify-content:space-between; align-items:center;">
                                <span><?= esc($nombre) ?>         <?= $tag_inactivo ?></span>
                                <form method="POST" action="admin.php" style="margin:0;">
                                    <input type="hidden" name="accion_pago" value="pagar">
                                    <input type="hidden" name="id_persona" value="<?= $d['ID_PERSONA'] ?>">
                                    <input type="hidden" name="mes_pago" value="<?= $mes_filtro ?>">
                                    <!-- Si quieres soportar esta logica extra en admin.php -->
                                    <button type="submit" title="Marcar como pagado"
                                        style="background:none; border:none; cursor:pointer; font-size:1.2rem;">✅</button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- 🟢 COLUMNA PAGADOS -->
            <div
                style="flex:1; min-width:300px; background:#f0fff4; border-left:6px solid #48bb78; padding:15px; border-radius:8px;">
                <div style="display:flex; justify-content:space-between; align-items:center; cursor:pointer;"
                    onclick="togglePagados()">
                    <h3 style="margin:0; color:#276749;">
                        ✅ Pagados (<?= count($pagados_mensuales) ?>)
                    </h3>
                    <span id="flechaPagados" style="font-size: 20px;">▼</span>
                </div>
                <small style="color:#22543d;">Mes: <?= esc($mesFiltroNombre) ?></small>

                <div id="listaPagados" style="display:none; margin-top:10px; max-height:300px; overflow-y:auto;">
                    <?php if (empty($pagados_mensuales)): ?>
                        <p style="color:#666; font-style:italic;">Nadie ha pagado aún. 🍃</p>
                    <?php else: ?>
                        <?php foreach ($pagados_mensuales as $p):
                            $nombre = trim($p['PRIMER_NOMBRE'] . ' ' . $p['PRIMER_APELLIDO']);
                            $fechaPago = $p['FECHA_PAGO'] ? date('d/m H:i', strtotime($p['FECHA_PAGO'])) : '-';
                            ?>
                            <div
                                style="padding:8px; border-bottom:1px solid #c6f6d5; display:flex; justify-content:space-between; align-items:center;">
                                <div>
                                    <span><?= esc($nombre) ?></span>
                                    <br><small style="color:#555;">Pagó: <?= $fechaPago ?></small>
                                </div>
                                <form method="POST" action="admin.php" style="margin:0;">
                                    <input type="hidden" name="accion_pago" value="pendiente">
                                    <input type="hidden" name="id_persona" value="<?= $p['ID_PERSONA'] ?>">
                                    <button type="submit" title="Revertir a pendiente"
                                        style="background:none; border:none; cursor:pointer; font-size:1.2rem;">↩️</button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

        </div>

        <script>
            function toggleDeudores() {
                const lista = document.getElementById("listaDeudores");
                const flecha = document.getElementById("flechaDeudores");
                if (lista.style.display === "none") {
                    lista.style.display = "block";
                    flecha.textContent = "▲";
                } else {
                    lista.style.display = "none";
                    flecha.textContent = "▼";
                }
            }
            function togglePagados() {
                const lista = document.getElementById("listaPagados");
                const flecha = document.getElementById("flechaPagados");
                if (lista.style.display === "none") {
                    lista.style.display = "block";
                    flecha.textContent = "▲";
                } else {
                    lista.style.display = "none";
                    flecha.textContent = "▼";
                }
            }
        </script>

        <br>
        <hr><br>


        <!-- ✅ FORM FILTROS -->
        <form class="filtros-estudiantes" method="get">

            <!-- Preservar filtros de mes -->
            <input type="hidden" name="mes_filtro" value="<?= esc($mes_filtro) ?>">

            <div class="filtro-grupo"><label for="filtro-nivel">Nivel:</label><select id="filtro-nivel"
                    name="filtro-nivel">
                    <option value="">Todos</option>
                    <option value="Principiante" <?= $nivel_f === 'Principiante' ? 'selected' : '' ?>>Principiantes
                    </option>
                    <option value="Avanzado" <?= $nivel_f === 'Avanzado' ? 'selected' : '' ?>>Avanzados</option>
                    <option value="Superior" <?= $nivel_f === 'Superior' ? 'selected' : '' ?>>Superiores</option>
                </select></div>

            <div class="filtro-grupo">
                <label for="filtro-estado">Ver Estudiantes:</label>
                <select id="filtro-estado" name="filtro-estado">
                    <option value="1" <?= $estado_global === '1' ? 'selected' : '' ?>>Activos</option>
                    <option value="2" <?= $estado_global === '2' ? 'selected' : '' ?>>Inactivos</option>
                    <option value="" <?= $estado_global === '' || $estado_global === 'todos' ? 'selected' : '' ?>>Todos
                    </option>
                </select>
            </div>


            <div class="filtro-grupo"><label for="filtro-cinturon">Cinturón:</label><select id="filtro-cinturon"
                    name="filtro-cinturon">
                    <option value="">Todos</option><?php foreach ($cinturones as $c): ?>
                        <option value="<?= $c['ID_CINTURON'] ?>" <?= (string) $cint_f === (string) $c['ID_CINTURON'] ? 'selected' : '' ?>><?= esc($c['NOMBRE']) ?></option><?php endforeach; ?>
                </select></div>
            <div class="filtro-grupo"><label for="filtro-edad-min">Edad:</label><input type="number"
                    id="filtro-edad-min" name="filtro-edad-min" value="<?= esc($edad_min) ?>" placeholder="Min"><span> -
                </span><input type="number" id="filtro-edad-max" name="filtro-edad-max" value="<?= esc($edad_max) ?>"
                    placeholder="Max"></div>
            <div class="filtro-grupo"><label for="filtro-dia">Día:</label><select id="filtro-dia" name="filtro-dia">
                    <option value="">Todos</option>
                    <?php foreach (['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'] as $d): ?>
                        <option <?= $dia_f === $d ? 'selected' : '' ?>><?= $d ?></option><?php endforeach; ?>
                </select></div>
            <div class="filtro-grupo"><label for="filtro-dan">Dan:</label><select id="filtro-dan" name="filtro-dan">
                    <option value="">Todos</option>
                    <option value="0" <?= $dan_f === '0' ? 'selected' : '' ?>>Poom</option>
                    <?php for ($i = 1; $i <= 9; $i++): ?>
                        <option value="<?= $i ?>" <?= (string) $dan_f === (string) $i ? 'selected' : '' ?>><?= $i ?> Dan
                        </option>
                    <?php endfor; ?>
                </select></div>
            <div class="filtro-botones">
                <button type="submit" class="button-filter">Aplicar</button>
                <button type="button" class="button-filter reset" onclick="window.location='admin.php'">Limpiar</button>


            </div>


        </form>

        <button id="abrir-modal-agregar-estudiante-btn" class="add-student" onclick="abrirModalNuevo()">Agregar
            Estudiante</button>

        <input type="text" id="search-box" placeholder="Buscar..."
            style="width: 100%; padding: 8px; margin: 20px 0; box-sizing: border-box;">


    </section>


    <div id="search-suggestions" style="
        width:100%;
        background:white;
        border:1px solid #ccc;
        border-radius:8px;
        max-height:250px;
        overflow-y:auto;
        display:none;
        position:absolute;
        z-index:9999;
     ">
    </div>


    <section id="lista-estudiantes-container">
        <?php if (!$estudiantes): ?>
            <?php if (isset($_GET['filtro-estado']) && $_GET['filtro-estado'] === '2'): ?>
                <div style="padding:40px; text-align:center; color:#666; background:#f9f9f9; border-radius:8px;">
                    <h3>🚫 No hay estudiantes inactivos</h3>
                    <p>Todos los estudiantes registrados están activos o no cumplen otros criterios.</p>
                </div>
            <?php else: ?>
                <p>No hay estudiantes que coincidan con los filtros.</p>
            <?php endif; ?>
        <?php else:
            foreach ($estudiantes as $e):
                $idp = (int) $e['ID_PERSONA'];
                $activo = ((int) $e['ID_ESTADO'] === 1);

                // 1 = pendiente, 2 = pagado
                $estado_pago = $pago_estado[$idp] ?? 1;   // si no hay registro, asumimos pendiente
                $pagado = ($estado_pago === 2);

                $nombre = trim(
                    $e['PRIMER_NOMBRE'] . ' ' .
                    $e['SEGUNDO_NOMBRE'] . ' ' .
                    $e['PRIMER_APELLIDO'] . ' ' .
                    $e['SEGUNDO_APELLIDO']

                );
                $doc_txt = trim($e['TIPO_DOCUMENTO'] . ' ' . $e['DOCUMENTO']);
                $edad = (int) $e['EDAD'];
                $ev_txt = $evento_ultimo[$idp] ?? '-';
                // NUEVO: meses pagados y pendientes para este estudiante
                $mesesPagados = $meses_pagados_por_est[$idp] ?? [];
                $mesesPendientes = $meses_pendientes_por_est[$idp] ?? [];

                ?>
                <div class="info-card student-card">

                    <div class="student-details">
                        <p><strong><?= esc($nombre) ?></strong></p>

                        <p>Cinturón: <?= esc($e['CINTURON_NOMBRE']) ?> | Nivel: <?= esc($e['NIVEL'] ?: '-') ?></p>
                        <p>Documento: <?= esc($doc_txt) ?></p>
                        <p>Usuario: <?= esc($e['USUARIO']) ?> | Contraseña: <?= esc($e['DOCUMENTO']) ?></p>
                        <p>Fecha Nac: <?= esc($e['FECHA_NACIMIENTO']) ?> | Edad: <?= esc($edad) ?> años</p>
                        <p>Inicio: <?= esc($e['FECHA_INICIO']) ?> | Lugar Nac: <?= esc($e['LUGAR_NACIMIENTO']) ?></p>
                        <p>Dirección: <?= esc($e['DIRECCION']) ?></p>
                        <p>Celular: <?= esc($e['CELULAR']) ?> | Email: <?= esc($e['CORREO']) ?></p>
                        <p>Estudia en: <?= esc($e['LUGAR_ESTUDIA']) ?> | EPS: <?= esc($e['EPS']) ?></p>
                        <p>Días: <?= esc($e['DIAS_ENTRENAMIENTO']) ?></p>
                        <p>Intensidad: <?= esc($e['INTENSIDAD_HORARIA']) ?>h | Precio:
                            $<?= number_format($e['PRECIO_MENSUAL'] ?? 0, 0) ?></p>
                        <hr>
                        <p>Intensidad: <?= esc($e['INTENSIDAD_HORARIA']) ?>h | Precio:
                            $<?= number_format($e['PRECIO_MENSUAL'] ?? 0, 0) ?></p>

                        <!-- === MESES PAGADOS / PENDIENTES === -->
                        <p>
                            <strong>Meses pagados:</strong>
                            <?php if (!empty($mesesPagados)): ?>
                                <?= esc(implode(', ', $mesesPagados)) ?>
                            <?php else: ?>
                                Ningún mes registrado como pagado
                            <?php endif; ?>
                        </p>
                        <p>
                            <strong>Meses pendientes:</strong>
                            <?php if (!empty($mesesPendientes)): ?>
                                <?= esc(implode(', ', $mesesPendientes)) ?>
                            <?php else: ?>
                                Sin meses pendientes
                            <?php endif; ?>
                        </p>
                        <!-- =============================== -->

                        <hr>
                        <p><strong>Acudiente:</strong> <?= esc($e['ACUDIENTE_NOMBRE_COMPLETO'] ?? 'No registrado') ?></p>


                        <hr>
                        <p><strong>Acudiente:</strong> <?= esc($e['ACUDIENTE_NOMBRE_COMPLETO'] ?? 'No registrado') ?></p>

                        <p><strong>Acudiente:</strong> <?= esc($e['ACUDIENTE_NOMBRE_COMPLETO'] ?? 'No registrado') ?></p>
                        <p>Cel: <?= esc($e['ACUDIENTE_CELULAR'] ?? '-') ?> | Email: <?= esc($e['ACUDIENTE_EMAIL'] ?? '-') ?></p>
                        <p>Empresa: <?= esc($e['ACUDIENTE_EMPRESA'] ?? '-') ?> | Cargo: <?= esc($e['ACUDIENTE_CARGO'] ?? '-') ?>
                        </p>
                        <?php if ($e['TIPO_PAGO'] === 'Anual'): ?>


                            <?php

                            //PLAN ANUAL
                            $fechaPlan = $e['FECHA_INICIO_PLAN'] ?? null;
                            $vigenciaAnual = null;

                            if ($fechaPlan) {
                                $vigenciaAnual = date('Y-m-d', strtotime($fechaPlan . ' +1 year'));
                            }

                            $hoy = date('Y-m-d');
                            ?>

                            <?php if ($vigenciaAnual && $vigenciaAnual >= $hoy): ?>
                                <p class="plan-tag plan-ok">
                                    ✅ Plan anual vigente<br>
                                    <span style="font-size:14px;">
                                        Hasta: <?= date('d/m/Y', strtotime($vigenciaAnual)) ?>
                                    </span>
                                </p>
                            <?php else: ?>
                                <p class="plan-tag plan-ok">
                                    ⚠️ Plan anual vencido<br>
                                    <span style="font-size:14px;">
                                        Desde ahora pagar mensual
                                    </span>
                                </p>
                            <?php endif; ?>

                        <?php endif; ?>

                        <hr>

                        <p>Últ. Evento: <?= esc($ev_txt) ?></p>
                        <p>Medalla: <?= esc($evento_medalla[$idp] ?? '-') ?></p>

                        <div class="botones-accion">
                            <button class="action-btn small-btn <?= $activo ? 'active' : 'inactive' ?>"
                                onclick="toggleStatus(this, '<?= $idp ?>', 'activo')"><?= $activo ? 'Activo' : 'Inactivo' ?></button>
                            <button class="action-btn small-btn <?= $pagado ? 'paid' : 'pending' ?>"
                                onclick="togglePago(<?= $idp ?>)"><?= $pagado ? 'Pago' : 'Pendiente' ?></button>
                            <button onclick="abrirModalEditar(<?= $idp ?>)">Editar</button>
                            <button onclick="eliminarEstudiante(<?= $idp ?>)">Eliminar</button>
                            <button type="button" class="boton-historial"
                                onclick="verHistorialCinturones(<?= $e['ID_PERSONA'] ?>)">Historial Cinturones</button>
                            <?php if ($e['TIPO_PAGO'] === 'Anual'): ?>
                                <form method="post" action="registrar_pago_anual.php" style="display:inline;">
                                    <input type="hidden" name="id_persona" value="<?= $e['ID_PERSONA'] ?>">
                                    <button type="submit" class="btn btn-primary">Registrar Pago Anual ✅</button>
                                </form>
                            <?php endif; ?>

                        </div>
                    </div>

                    <?php if (!empty($e['FOTO'])): ?>
                        <div class="student-photo">
                            <img src="<?= esc($e['FOTO']) ?>" alt="Foto de <?= esc($nombre) ?>">
                        </div>
                    <?php endif; ?>
                </div>



            <?php endforeach; endif; ?>
    </section>


    <button id="load-more" style="margin:20px auto; display:block; padding:10px 20px; 
        background:#007bff; color:white; border:none; border-radius:6px; 
        cursor:pointer; font-size:16px;">
        Cargar más
    </button>


    <!--AQUI ESTA EL BUSCADOR-->

    <script>
        const input = document.getElementById("search-box");
        const suggestionsBox = document.getElementById("search-suggestions");
        const cards = [...document.querySelectorAll(".student-card")];
        const loadMoreBtn = document.getElementById("load-more");

        const ITEMS_PER_PAGE = 10;
        let currentIndex = 0;
        let currentMatches = [...cards]; // siempre arranca mostrando todo

        // Normalizar para quitar tildes (ramírez = ramirez)
        const normalize = str => str
            .toLowerCase()
            .normalize("NFD")
            .replace(/[\u0300-\u036f]/g, "");

        // Crear lista para sugerencias
        const data = cards.map(card => ({
            element: card,
            text: normalize(card.textContent.trim())
        }));

        // ------------------------------
        // INICIALIZACIÓN
        // ------------------------------
        hideAllCards();
        showNextCards();


        // ------------------------------
        // BUSCADOR
        // ------------------------------
        input.addEventListener("input", function () {
            const value = normalize(this.value.trim());
            const terms = value.split(/\s+/);

            if (value === "") {
                suggestionsBox.style.display = "none";
                currentMatches = [...cards];
                resetPagination();
                return;
            }

            // Filtrar
            currentMatches = data.filter(d =>
                terms.every(t => d.text.includes(t))
            ).map(d => d.element);

            // Render sugerencias
            renderSuggestions(data.filter(d =>
                terms.every(t => d.text.includes(t))
            ), terms);

            // Reiniciar paginación con resultados filtrados
            resetPagination();
        });


        // ------------------------------
        // RENDER SUGERENCIAS
        // ------------------------------
        function renderSuggestions(matches, terms) {
            suggestionsBox.innerHTML = "";

            if (matches.length === 0) {
                suggestionsBox.style.display = "none";
                return;
            }

            suggestionsBox.style.display = "block";

            matches.slice(0, 10).forEach(match => {
                let original = match.element.querySelector("p strong")?.innerText || match.element.innerText;
                let highlighted = highlightTerms(original, terms);

                const item = document.createElement("div");
                item.style.padding = "10px";
                item.style.cursor = "pointer";
                item.style.borderBottom = "1px solid #eee";
                item.innerHTML = `🔍 ${highlighted}`;

                item.addEventListener("click", () => {
                    input.value = original;
                    suggestionsBox.style.display = "none";

                    // Filtro exacto al seleccionar sugerencia
                    currentMatches = [match.element];
                    resetPagination();
                });

                suggestionsBox.appendChild(item);
            });
        }

        // ------------------------------
        // RESALTAR TÉRMINOS
        // ------------------------------
        function highlightTerms(text, terms) {
            let normalizedText = normalize(text);

            terms.forEach(term => {
                let start = normalizedText.indexOf(term);
                if (start !== -1) {
                    let end = start + term.length;
                    let originalPart = text.substring(start, end);

                    text = text.substring(0, start)
                        + `<strong style="color:#007bff">${originalPart}</strong>`
                        + text.substring(end);

                    normalizedText = normalize(text);
                }
            });

            return text;
        }


        // ------------------------------
        // PAGINACIÓN (CARGAR MÁS)
        // ------------------------------
        function resetPagination() {
            hideAllCards();
            currentIndex = 0;
            showNextCards();
        }

        function hideAllCards() {
            cards.forEach(c => c.style.display = "none");
        }

        function showNextCards() {
            const end = currentIndex + ITEMS_PER_PAGE;
            const slice = currentMatches.slice(currentIndex, end);

            slice.forEach(c => c.style.display = "");

            currentIndex = end;

            // Mostrar u ocultar el botón
            if (currentIndex >= currentMatches.length) {
                loadMoreBtn.style.display = "none";
            } else {
                loadMoreBtn.style.display = "block";
            }
        }


        // Evento del botón "Cargar más"
        loadMoreBtn.addEventListener("click", showNextCards);

    </script>





</body>

</html>