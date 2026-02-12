<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>
  <link rel="stylesheet" href="../style.css">
  <link rel="icon" type="image/png" href="img/favicon-96x96.png" sizes="96x96">

</head>

<body>
  <!-- MODAL -->
  <div id="modal-agregar-estudiante" class="modal" style="display:none;">
    <div class="modal-content">
      <span id="cerrar-modal-agregar" class="close-button" onclick="cerrarModal()">&times;</span>
      <h3 id="modal-titulo">Agregar Estudiante</h3>

      <form id="form-estudiante" class="form-grid" method="post" enctype="multipart/form-data" autocomplete="off">
        <input type="hidden" name="action" id="form-action" value="guardar_estudiante">
        <input type="hidden" name="id_persona" id="id-persona" value="">
        <input type="hidden" name="foto_actual" id="foto-actual" value="">
        <input type="hidden" name="eventos_json" id="eventos-json" value="[]">

        <!-- === DATOS PERSONALES === -->
        <div class="section-box">
          <h3>Datos Personales</h3>

          <div class="form-group"><label>Foto:</label><input type="file" id="foto" name="foto" accept="image/*"></div>
          <div class="form-group"><label>Primer Nombre:</label><input id="primer-nombre" name="primer-nombre" required>
          </div>
          <div class="form-group"><label>Segundo Nombre:</label><input id="segundo-nombre" name="segundo-nombre"></div>
          <div class="form-group"><label>Primer Apellido:</label><input id="primer-apellido" name="primer-apellido"
              required></div>
          <div class="form-group"><label>Segundo Apellido:</label><input id="segundo-apellido" name="segundo-apellido">
          </div>
          <div class="form-group">
            <label>Tipo Doc:</label>
            <select id="tipo-documento" name="tipo-documento" required>
              <option value="TI">TI</option>
              <option value="CC" selected>CC</option>
              <option value="CE">CE</option>
            </select>
          </div>
          <div class="form-group"><label># Documento:</label><input id="numero-documento" name="numero-documento"
              required></div>
          <div class="form-group"><label>Fecha Nacimiento:</label><input type="date" id="fecha-nacimiento"
              name="fecha-nacimiento" required></div>
          <div class="form-group"><label>Lugar Nacimiento:</label><input id="lugar-nacimiento" name="lugar-nacimiento">
          </div>
          <div class="form-group"><label>Dirección:</label><input id="direccion" name="direccion"></div>
          <div class="form-group"><label>Celular:</label><input id="celular-estudiante" name="celular-estudiante"></div>
          <div class="form-group"><label>Correo:</label><input type="email" id="correo-estudiante"
              name="correo-estudiante"></div>
          <div class="form-group"><label>Estudia en:</label><input id="lugar-estudia" name="lugar-estudia"></div>
          <div class="form-group"><label>EPS:</label><input id="eps" name="eps"></div>
        </div>

        <!-- === ENTRENAMIENTO === -->
        <div class="section-box">
          <h3>Entrenamiento</h3>

          <div class="form-group">
            <label>Fecha Inicio:</label>
            <input type="date" id="fecha-inicio" name="fecha-inicio">
          </div>

          <div class="form-group">
            <label>Día de pago </label>
            <input type="number" id="dia-pago" name="dia-pago" min="1" max="31"
              value="<?= isset($edit_data['DIA_PAGO']) ? esc($edit_data['DIA_PAGO']) : 1 ?>" required>
          </div>

          <div class="form-group">
            <label style="margin-bottom:6px; display:block;">Tipo de Pago</label>
            <div style="display:flex; flex-direction:column; gap:10px;">
              <label style="display:flex; flex-direction:column; align-items:flex-start; gap:2px;">
                <span>Mensual</span>
                <input type="radio" name="tipo_pago" value="Mensual" <?= (isset($edit_data) && $edit_data['TIPO_PAGO'] == 'Anual') ? '' : 'checked' ?>>
              </label>

              <label style="display:flex; flex-direction:column; align-items:flex-start; gap:2px;">
                <span>Anual</span>
                <input type="radio" name="tipo_pago" value="Anual" <?= (isset($edit_data) && $edit_data['TIPO_PAGO'] == 'Anual') ? 'checked' : '' ?>>
              </label>
            </div>
          </div>

          <div class="form-group" id="fechaPlanContainer"
            style="display:<?= (isset($edit_data) && $edit_data['TIPO_PAGO'] == 'Anual') ? 'block' : 'none' ?>;">
            <label>Inicio del Plan Anual</label>
            <input type="date" name="fecha-inicio-plan" value="<?= esc($edit_data['FECHA_INICIO_PLAN'] ?? '') ?>">
          </div>


          <div class="section-box">
            <h3>Meses de pago</h3>

            <div class="form-group">
              <label>Meses pagados</label>
              <select id="meses-pagados" multiple size="5" style="width:100%;">
                <!-- Opciones se llenan por JS o PHP -->
              </select>
            </div>

            <div class="form-group">
              <label>Meses no pagados</label>
              <select id="meses-no-pagados" multiple size="5" style="width:100%;">
                <!-- Opciones se llenan por JS o PHP -->
              </select>
            </div>

            <div class="form-group" style="display:flex; gap:10px; justify-content:center; margin-top:10px;">
              <button type="button" class="button-filter" id="btnMarcarPagado">Marcar como pagado →</button>
              <button type="button" class="button-filter reset" id="btnMarcarNoPagado">← Marcar como no pagado</button>
            </div>

            <!-- Aquí guardaremos los meses pagados para PHP -->
            <input type="hidden" name="meses_pagados" id="meses-pagados-hidden">
          </div>


          <div class="form-group">
            <label>Cinturón:</label>
            <select id="cinturon-actual" name="cinturon-actual" required>
              <?php foreach ($cinturones as $c): ?>
                <option value="<?= $c['ID_CINTURON'] ?>"><?= esc($c['NOMBRE']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-group">
            <label>Fecha de obtención del cinturón</label>
            <input type="date" id="fecha-cinturon" name="fecha-cinturon">
          </div>

          <div class="form-group" id="dan-container">
            <label>Dan:</label>
            <select id="dan" name="dan">
              <option value="">N/A</option>
              <option value="0">Poom</option>
              <?php for ($i = 1; $i <= 9; $i++): ?>
                <option value="<?= $i ?>"><?= $i ?> Dan</option>
              <?php endfor; ?>
            </select>
          </div>

          <div class="form-group">
            <label># Días:</label>
            <input type="number" id="dias-numero" value="1" min="1" max="6">
            <div id="dias-entrenamiento-container"></div>
          </div>

          <div class="form-group">
            <label>Intensidad Horaria:</label>
            <select id="intensidad-horaria" name="intensidad-horaria" onchange="actualizarPrecio(this)">
              <option value="0" data-precio="0">Seleccionar</option>
              <option value="1" data-precio="140000">1 Hora</option>
              <option value="2" data-precio="170000">2 Horas</option>
              <option value="3" data-precio="200000">3 Horas</option>
              <option value="4" data-precio="230000">4 Horas</option>
              <option value="5" data-precio="250000">5 Horas</option>
            </select>
            <input type="hidden" name="precio-mensual" id="precio-mensual">
          </div>
        </div>

        <!-- === ACUDIENTE === -->
        <div class="section-box">
          <h3>Acudiente</h3>

          <div class="form-group"><label>Nombre Acudiente:</label><input id="nombre-acudiente" name="nombre-acudiente">
          </div>
          <div class="form-group"><label>Empresa:</label><input id="empresa-acudiente" name="empresa-acudiente"></div>
          <div class="form-group"><label>Cargo:</label><input id="cargo-acudiente" name="cargo-acudiente"></div>
          <div class="form-group"><label>Email Acudiente:</label><input type="email" id="email-acudiente"
              name="email-acudiente"></div>
          <div class="form-group"><label>Celular Acudiente:</label><input id="celular-acudiente"
              name="celular-acudiente"></div>
        </div>

        <!-- === EVENTOS === -->
        <div class="section-box" style="grid-column:1/-1;">
          <h3>Eventos</h3>
          <div class="form-group">
            <div id="eventos-container"></div>
            <button type="button" id="agregar-evento-btn" class="button-filter" style="margin-top:10px;">Agregar
              Evento</button>
          </div>
        </div>

        <div class="form-actions">
          <button type="submit" class="save-btn">Guardar</button>
        </div>
      </form>
    </div>
  </div>

  <script>
    document.querySelectorAll("input[name='tipo_pago']").forEach(r => {
      r.addEventListener("change", () => {
        document.getElementById("fechaPlanContainer").style.display =
          (r.value === "Anual") ? "block" : "none";
      });
    });
  </script>

  <!-- Modal: Historial de Cinturones -->
  <div class="modal fade" id="modalHistorialCinturones" tabindex="-1" aria-labelledby="modalHistorialCinturonesLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg"> <!-- centrado vertical -->
      <div class="modal-content shadow-lg rounded-3 border-0"> <!-- sombra y bordes suaves -->
        <div class="modal-header bg-dark text-white">
          <h5 class="modal-title" id="modalHistorialCinturonesLabel">Registro De Grados</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body bg-light">
          <div id="historial-cinturones-contenido" class="p-3 text-center">
            <p class="text-muted">Cargando historial...</p>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- =====================================
     MODAL: LISTA DE PRODUCTOS
===================================== -->
  <div class="modal fade" id="modalProductos" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Gestión de Productos</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">

          <div class="text-end mb-3">
            <button class="btn btn-success" onclick="abrirModalNuevoProducto()">+ Agregar Producto</button>
          </div>

          <div id="productos-contenido" class="table-responsive text-center">
            <p class="text-muted">Cargando...</p>
          </div>
        </div>
      </div>
    </div>
  </div>


  <!-- =====================================
     MODAL: FORMULARIO (CREAR / EDITAR)
===================================== -->
  <div class="modal fade" id="modalProductoForm" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">

        <div class="modal-header">
          <h5 class="modal-title" id="modalProductoFormLabel">Producto</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          <form id="formProducto" enctype="multipart/form-data">

            <input type="hidden" name="id" id="producto-id">
            <input type="hidden" name="accion" value="guardar">

            <input type="hidden" name="imagen_actual" id="producto-imagen-actual">

            <div class="mb-3">
              <label class="form-label">Nombre</label>
              <input type="text" class="form-control" id="producto-nombre" name="nombre" required>
            </div>

            <div class="mb-3">
              <label class="form-label">Descripción</label>
              <textarea class="form-control" id="producto-descripcion" name="descripcion" rows="3"></textarea>
            </div>

            <div class="row">
              <div class="col-6 mb-3">
                <label class="form-label">Precio</label>
                <input type="number" class="form-control" id="producto-precio" name="precio" step="0.01" required>
              </div>
              <div class="col-6 mb-3">
                <label class="form-label">Stock</label>
                <input type="number" class="form-control" id="producto-stock" name="stock" required>
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label">Imagen (Opcional)</label>
              <input type="file" class="form-control" id="producto-imagen" name="imagen" accept="image/*">
            </div>

            <div class="text-center mt-4">
              <button type="submit" class="btn btn-primary w-100">Guardar Cambios</button>
            </div>

          </form>
        </div>

      </div>
    </div>
  </div>



  <!-- =====================================
     JAVASCRIPT
===================================== -->
  <script>

    function abrirModalProductos() {
      new bootstrap.Modal(document.getElementById('modalProductos')).show();
      cargarProductos();
    }

    function cargarProductos() {
      const cont = document.getElementById('productos-contenido');
      cont.innerHTML = '<div class="spinner-border text-primary mt-3"></div>';

      fetch('php/productos_ajax.php')
        .then(r => r.text())
        .then(html => cont.innerHTML = html)
        .catch(() => cont.innerHTML = '<p class="text-danger">Error cargando lista.</p>');
    }

    function abrirModalNuevoProducto() {
      document.getElementById('formProducto').reset();
      document.getElementById('producto-id').value = '';
      document.getElementById('producto-imagen-actual').value = '';

      document.getElementById('modalProductoFormLabel').textContent = 'Agregar Producto';

      new bootstrap.Modal(document.getElementById('modalProductoForm')).show();
    }

    function editarProducto(id) {

      const fd = new FormData();
      fd.append('id', id);

      fetch('php/producto_obtener.php', {
        method: 'POST',
        body: fd
      })
        .then(r => r.json())
        .then(data => {
          if (!data.success) return alert(data.message);

          const p = data.data;

          document.getElementById('producto-id').value = p.ID_PRODUCTO;
          document.getElementById('producto-nombre').value = p.NOMBRE;
          document.getElementById('producto-descripcion').value = p.DESCRIPCION;
          document.getElementById('producto-precio').value = p.PRECIO;
          document.getElementById('producto-stock').value = p.STOCK;

          // Guardar imagen actual
          document.getElementById('producto-imagen-actual').value = p.IMAGEN;

          document.getElementById('modalProductoFormLabel').textContent = 'Editar Producto';

          new bootstrap.Modal(document.getElementById('modalProductoForm')).show();
        })
        .catch(() => alert("Error al obtener producto"));
    }


    document.getElementById('formProducto').addEventListener('submit', function (e) {
      e.preventDefault();

      const fd = new FormData(this);

      fetch('php/producto_guardar.php', {
        method: 'POST',
        body: fd
      })
        .then(r => r.text())
        .then(text => {
          try {
            const data = JSON.parse(text);

            if (data.success) {
              alert(data.message);

              bootstrap.Modal.getInstance(
                document.getElementById('modalProductoForm')
              ).hide();

              cargarProductos();
            } else {
              alert("Error: " + data.message);
            }

          } catch (e) {
            console.error(text);
            alert("Error inesperado.");
          }
        });
    });


    function eliminarProducto(id) {
      if (!confirm("¿Eliminar producto?")) return;

      const fd = new FormData();
      fd.append('accion', 'eliminar');
      fd.append('id', id);

      fetch('php/producto_guardar.php', {
        method: 'POST',
        body: fd
      })
        .then(r => r.json())
        .then(data => {
          alert(data.message);
          if (data.success) cargarProductos();
        });
    }

  </script>



  <!-- Modal Inteligente: Seleccionar mes a pagar -->
  <div class="modal fade" id="modalPagarMes" tabindex="-1" aria-labelledby="modalPagarMesLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content" id="modalPagarMesContent">
        <div class="modal-header">
          <h5 class="modal-title" id="modalPagarMesLabel">Selecciona mes a pagar</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body" id="modalPagarMesBody">
          <p id="modalPagarMesInfo">Cargando...</p>
          <div id="modalPagarMesList" style="display:none;"></div>
        </div>
      </div>
    </div>
  </div>

  <script>
    function togglePago(id) {
      fetch(`php/toggle_pago.php?id=${id}`)
        .then(() => location.reload());
    }
    function pagarModal(idPersona) {
      const fd = new FormData();
      fd.append('id', idPersona);
      fd.append('type', 'meses_pendientes');

      fetch('php/ajax_handler.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
          if (!data.success) {
            pagarMesDirecto(idPersona);
            return;
          }
          const meses = data.meses || [];
          if (meses.length === 0) {
            pagarMesDirecto(idPersona);
            return;
          } else if (meses.length === 1) {
            pagarMes(idPersona, meses[0]);
            return;
          } else {
            const listDiv = document.getElementById('modalPagarMesList');
            listDiv.innerHTML = '';
            meses.forEach(m => {
              const [yr, mo] = m.split('-');
              const date = new Date(parseInt(yr), parseInt(mo) - 1, 1);
              const nombreMes = date.toLocaleString('es-ES', { month: 'long' });
              const btn = document.createElement('button');
              btn.className = 'button-filter';
              btn.style.display = 'block';
              btn.style.width = '100%';
              btn.style.marginBottom = '8px';
              btn.textContent = nombreMes.charAt(0).toUpperCase() + nombreMes.slice(1) + '/' + yr;
              btn.onclick = () => { pagarMes(idPersona, m); };
              listDiv.appendChild(btn);
            });
            document.getElementById('modalPagarMesInfo').style.display = 'none';
            listDiv.style.display = 'block';
            const modal = new bootstrap.Modal(document.getElementById('modalPagarMes'));
            modal.show();
          }
        })
        .catch(err => {
          console.error(err);
          pagarMesDirecto(idPersona);
        });
    }

    function pagarMes(idPersona, mes) {
      const fd = new FormData();
      fd.append('id', idPersona);
      fd.append('type', 'pago');
      fd.append('mes', mes);

      fetch('php/ajax_handler.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(resp => {
          if (resp.success) {
            location.reload();
          } else {
            alert('Error al registrar pago: ' + (resp.message || ''));
          }
        })
        .catch(() => alert('Error de red al marcar pago.'));
    }

    function pagarMesDirecto(idPersona) {
      const fd = new FormData();
      fd.append('id', idPersona);
      fd.append('type', 'pago');

      fetch('php/ajax_handler.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(resp => {
          if (resp.success) {
            location.reload();
          } else if (resp.multiple_pending) {
            const meses = resp.meses || [];
            const listDiv = document.getElementById('modalPagarMesList');
            listDiv.innerHTML = '';
            meses.forEach(m => {
              const [yr, mo] = m.split('-');
              const date = new Date(parseInt(yr), parseInt(mo) - 1, 1);
              const nombreMes = date.toLocaleString('es-ES', { month: 'long' });
              const btn = document.createElement('button');
              btn.className = 'button-filter';
              btn.style.display = 'block';
              btn.style.width = '100%';
              btn.style.marginBottom = '8px';
              btn.textContent = nombreMes.charAt(0).toUpperCase() + nombreMes.slice(1) + '/' + yr;
              btn.onclick = () => { pagarMes(idPersona, m); };
              listDiv.appendChild(btn);
            });
            document.getElementById('modalPagarMesInfo').style.display = 'none';
            listDiv.style.display = 'block';
            const modal = new bootstrap.Modal(document.getElementById('modalPagarMes'));
            modal.show();
          } else {
            alert('No se pudo procesar el pago: ' + (resp.message || ''));
          }
        })
        .catch(() => alert('Error de red al marcar pago.'));
    }
  </script>





  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>


  <script>
    // =============  =============
    function abrirModalNuevo() { document.getElementById('modal-titulo').textContent = 'Agregar Estudiante'; document.getElementById('form-action').value = 'guardar_estudiante'; document.getElementById('form-estudiante').reset(); limpiarEventos(); initDiasContainer(1); document.getElementById('modal-agregar-estudiante').style.display = 'flex'; }
    function abrirModalEditar(id) { window.location = 'admin.php?edit=' + id; }
    function cerrarModal() { window.location = 'admin.php'; }
    function eliminarEstudiante(id) { if (confirm('¿Eliminar estudiante?')) window.location = 'admin.php?delete=' + id; }
    function initDiasContainer(num) { const cont = document.getElementById('dias-entrenamiento-container'); cont.innerHTML = ''; const dias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado']; for (let i = 1; i <= num; i++) { const label = document.createElement('label'); label.textContent = 'Día ' + i + ':'; const sel = document.createElement('select'); sel.name = 'dias[]'; dias.forEach(d => { const o = document.createElement('option'); o.value = d; o.textContent = d; sel.appendChild(o); }); cont.appendChild(label); cont.appendChild(sel); } }
    document.getElementById('dias-numero').addEventListener('input', e => { const n = Math.max(1, Math.min(6, parseInt(e.target.value || '1', 10))); initDiasContainer(n); });
    const cintSel = document.getElementById('cinturon-actual'); const danSel = document.getElementById('dan');
    function actualizarDan() { const txt = (cintSel.options[cintSel.selectedIndex]?.text || '').toLowerCase(); danSel.parentElement.style.display = txt.includes('negro') ? 'block' : 'none'; }
    cintSel.addEventListener('change', actualizarDan);
    const eventosCont = document.getElementById('eventos-container'); const eventosJson = document.getElementById('eventos-json');
    function limpiarEventos() { eventosCont.innerHTML = ''; eventosJson.value = '[]'; }
    function syncEventos() {
      const nodes = eventosCont.querySelectorAll('.evento-item');
      const items = Array.from(nodes).map(div => ({
        nombre: (div.querySelector('.ev-nombre')?.value || '').trim(),
        fecha: div.querySelector('.ev-fecha')?.value || '',
        medalla: (div.querySelector('.ev-medalla')?.value || '').trim()
      }));
      eventosJson.value = JSON.stringify(items);
    }
    document.getElementById('agregar-evento-btn').addEventListener('click', () => { const wrap = document.createElement('div'); wrap.className = 'evento-item'; wrap.style.marginBottom = '10px'; wrap.innerHTML = `<div class="form-grid" style="grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:10px;"><div class="form-group"><label>Nombre</label><input class="ev-nombre" placeholder="Nombre del evento"></div><div class="form-group"><label>Fecha</label><input type="date" class="ev-fecha" value="<?= date('Y-m-d') ?>"></div><div class="form-group"><label>Medalla</label><input class="ev-medalla" placeholder="Oro/Plata/Bronce/-"></div><div class="form-group" style="display:flex;align-items:end;"><button type="button" class="button-filter reset ev-del">Quitar</button></div></div>`; eventosCont.appendChild(wrap); wrap.querySelector('.ev-del').addEventListener('click', () => { wrap.remove(); syncEventos(); }); wrap.querySelectorAll('input').forEach(i => i.addEventListener('input', syncEventos)); syncEventos(); });

    <?php if ($editing && $edit_data): ?>
        (function () {
          document.getElementById('modal-titulo').textContent = 'Editar Estudiante';
          document.getElementById('form-action').value = 'editar_estudiante';
          document.getElementById('id-persona').value = '<?= (int) $edit_data['ID_PERSONA'] ?>';
          document.getElementById('foto-actual').value = <?= json_encode((string) ($edit_data['FOTO'] ?? '')) ?>;
          document.getElementById('primer-nombre').value = <?= json_encode((string) $edit_data['PRIMER_NOMBRE']) ?>;
          document.getElementById('segundo-nombre').value = <?= json_encode((string) ($edit_data['SEGUNDO_NOMBRE'] ?? '')) ?>;
          document.getElementById('primer-apellido').value = <?= json_encode((string) $edit_data['PRIMER_APELLIDO']) ?>;
          document.getElementById('segundo-apellido').value = <?= json_encode((string) ($edit_data['SEGUNDO_APELLIDO'] ?? '')) ?>;
          document.getElementById('tipo-documento').value = <?= json_encode((string) $edit_data['TIPO_DOCUMENTO']) ?>;
          document.getElementById('numero-documento').value = <?= json_encode((string) $edit_data['DOCUMENTO']) ?>;
          document.getElementById('fecha-nacimiento').value = <?= json_encode((string) $edit_data['FECHA_NACIMIENTO']) ?>;
          document.getElementById('cinturon-actual').value = <?= json_encode((int) $edit_data['ID_CINTURON']) ?>;
          document.getElementById('dan').value = <?= json_encode($edit_data['DAN'] === null ? '' : (string) (int) $edit_data['DAN']) ?>;

          // --- Precargar nuevos campos ---
          document.getElementById('fecha-inicio').value = <?= json_encode((string) ($edit_data['FECHA_INICIO'] ?? '')) ?>;
          document.getElementById('lugar-nacimiento').value = <?= json_encode((string) ($edit_data['LUGAR_NACIMIENTO'] ?? '')) ?>;
          document.getElementById('direccion').value = <?= json_encode((string) ($edit_data['DIRECCION'] ?? '')) ?>;
          document.getElementById('celular-estudiante').value = <?= json_encode((string) ($edit_data['CELULAR'] ?? '')) ?>;
          document.getElementById('correo-estudiante').value = <?= json_encode((string) ($edit_data['CORREO'] ?? '')) ?>;
          document.getElementById('lugar-estudia').value = <?= json_encode((string) ($edit_data['LUGAR_ESTUDIA'] ?? '')) ?>;
          document.getElementById('eps').value = <?= json_encode((string) ($edit_data['EPS'] ?? '')) ?>;
          document.getElementById('intensidad-horaria').value = <?= json_encode((string) ($edit_data['INTENSIDAD_HORARIA'] ?? '')) ?>;
          document.getElementById('precio-mensual').value = <?= json_encode((string) ($edit_data['PRECIO_MENSUAL'] ?? '')) ?>;

          document.getElementById('dia-pago').value = <?= json_encode((string) ($edit_data['DIA_PAGO'] ?? '1')) ?>;


          document.getElementById('nombre-acudiente').value = <?= json_encode((string) ($edit_data['ACUDIENTE_NOMBRE_COMPLETO'] ?? '')) ?>;
          document.getElementById('empresa-acudiente').value = <?= json_encode((string) ($edit_data['ACUDIENTE_EMPRESA'] ?? '')) ?>;
          document.getElementById('cargo-acudiente').value = <?= json_encode((string) ($edit_data['ACUDIENTE_CARGO'] ?? '')) ?>;
          document.getElementById('email-acudiente').value = <?= json_encode((string) ($edit_data['ACUDIENTE_EMAIL'] ?? '')) ?>;
          document.getElementById('celular-acudiente').value = <?= json_encode((string) ($edit_data['ACUDIENTE_CELULAR'] ?? '')) ?>;

          const diasStr = <?= json_encode((string) ($edit_data['DIAS_ENTRENAMIENTO'] ?? '')) ?>;
          const arr = diasStr ? diasStr.split(',').map(s => s.trim()).filter(Boolean) : [];
          const n = Math.max(1, arr.length || 1);
          document.getElementById('dias-numero').value = n; initDiasContainer(n);
          if (arr.length) { const sels = document.querySelectorAll('#dias-entrenamiento-container select[name="dias[]"]'); arr.forEach((v, i) => { if (sels[i]) sels[i].value = v; }); }
          const evs = <?= json_encode($eventos_edit) ?>;
          limpiarEventos();
          evs.forEach(ev => { document.getElementById('agregar-evento-btn').click(); const last = eventosCont.querySelector('.evento-item:last-child'); if (last) { last.querySelector('.ev-nombre').value = ev.nombre || ''; last.querySelector('.ev-fecha').value = ev.fecha || '<?= date('Y-m-d') ?>'; last.querySelector('.ev-medalla').value = ev.medalla || '-'; } });
          syncEventos();
          document.getElementById('modal-agregar-estudiante').style.display = 'flex';
          actualizarDan();
        })();
    <?php else: ?>
      initDiasContainer(1); actualizarDan();
    <?php endif; ?>





    // === Cargar historial por AJAX ===
    function verHistorialCinturones(idPersona) {
      fetch('php/historial_cinturon_ajax.php?id=' + idPersona)
        .then(res => {
          if (!res.ok) throw new Error('Error ' + res.status);
          return res.text();
        })
        .then(html => {
          document.getElementById('historial-cinturones-contenido').innerHTML = html;
          const modal = new bootstrap.Modal(document.getElementById('modalHistorialCinturones'));
          modal.show();
        })
        .catch(err => {
          document.getElementById('historial-cinturones-contenido').innerHTML = `<p class="text-danger">No se pudo cargar el historial.<br>${err.message}</p>`;
        });
    }


    // ============= NUEVO SCRIPT AÑADIDO =============
    document.getElementById('search-box').addEventListener('keyup', function () {
      const filter = this.value.toLowerCase();
      const cards = document.querySelectorAll('.student-card');
      cards.forEach(card => {
        const text = card.textContent.toLowerCase();
        card.style.display = text.includes(filter) ? '' : 'none';
      });
    });
    function actualizarPrecio(selectElement) { const selectedOption = selectElement.options[selectElement.selectedIndex]; document.getElementById('precio-mensual').value = selectedOption.getAttribute('data-precio') || '0'; }
    function toggleStatus(button, id, type) {
      button.disabled = true; button.textContent = '...';
      const formData = new FormData();
      formData.append('id', id); formData.append('type', type); formData.append('mes', '<?= $mes_actual ?>');
      fetch('ajax_handler.php', { method: 'POST', body: formData })
        .then(response => response.json())
        .then(data => {
          if (data.success) { button.textContent = data.newStatus; button.className = button.className.replace(/active|inactive|paid|pending/g, '') + ' ' + data.newClass; }
          else { alert('Error: ' + data.message); location.reload(); }
        })
        .catch(error => { console.error('Error:', error); alert('Error de red.'); location.reload(); })
        .finally(() => { button.disabled = false; });
    }



  </script>


  <script>
    // ===== MESES PAGADOS / NO PAGADOS =====
    const anioActual = new Date().getFullYear();
    const MESES_LISTA = [
      `${anioActual}-01`,
      `${anioActual}-02`,
      `${anioActual}-03`,
      `${anioActual}-04`,
      `${anioActual}-05`,
      `${anioActual}-06`,
      `${anioActual}-07`,
      `${anioActual}-08`,
      `${anioActual}-09`,
      `${anioActual}-10`,
      `${anioActual}-11`,
      `${anioActual}-12`,
    ];

    function formatearMes(ym) {
      const [y, m] = ym.split("-");
      const fecha = new Date(parseInt(y), parseInt(m) - 1, 1);
      const nombreMes = fecha.toLocaleString("es-ES", { month: "long" });
      return nombreMes.charAt(0).toUpperCase() + nombreMes.slice(1) + " " + y;
    }

    function initMesesPago(mesesPagadosInicial = []) {
      const selPagados = document.getElementById("meses-pagados");
      const selNoPagados = document.getElementById("meses-no-pagados");

      selPagados.innerHTML = "";
      selNoPagados.innerHTML = "";

      MESES_LISTA.forEach((ym) => {
        const opt = document.createElement("option");
        opt.value = ym;
        opt.textContent = formatearMes(ym);

        if (mesesPagadosInicial.includes(ym)) {
          selPagados.appendChild(opt);
        } else {
          selNoPagados.appendChild(opt);
        }
      });

      syncMesesPagadosHidden();
    }

    function moverMeses(origenId, destinoId) {
      const origen = document.getElementById(origenId);
      const destino = document.getElementById(destinoId);
      const seleccionados = Array.from(origen.selectedOptions);
      seleccionados.forEach((opt) => destino.appendChild(opt));
      syncMesesPagadosHidden();
    }

    function syncMesesPagadosHidden() {
      const selPagados = document.getElementById("meses-pagados");
      const hidden = document.getElementById("meses-pagados-hidden");
      const valores = Array.from(selPagados.options).map((o) => o.value);
      hidden.value = JSON.stringify(valores);
    }

    // Botones
    document.getElementById("btnMarcarPagado").addEventListener("click", () => {
      moverMeses("meses-no-pagados", "meses-pagados");
    });

    document.getElementById("btnMarcarNoPagado").addEventListener("click", () => {
      moverMeses("meses-pagados", "meses-no-pagados");
    });

    // Inicializar al abrir el modal (nuevo)
    initMesesPago([]);


    let mesesIniciales = <?= json_encode($meses_pagados_del_estudiante ?? []); ?>;
    initMesesPago(mesesIniciales);



  </script>




</body>

</html>