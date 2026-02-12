// eventos_calendario.js
// Dependencia: none (usa fetch)

function mostrarMes(yearMonth) {
  // yearMonth = "YYYY-MM"
  fetch('php/get_eventos.php?mes=' + encodeURIComponent(yearMonth))
    .then(r => r.json())
    .then(j => {
      if (!j.success) { console.error('no eventos'); return; }
      const eventos = j.eventos || [];
      // Aquí es donde dibujas los eventos en tu interfaz (ej: en el contenedor #lista-eventos)
      const cont = document.getElementById('lista-eventos');
      if (!cont) return;
      cont.innerHTML = '';
      if (eventos.length === 0) { cont.innerHTML = '<p>No hay eventos este mes.</p>'; return; }
      eventos.forEach(ev => {
        const div = document.createElement('div');
        div.className = 'ev-item';
        div.innerHTML = `<strong>${ev.FECHA} — ${ev.NOMBRE}</strong><div>${ev.LUGAR || ''}</div><div>${ev.DESCRIPCION || ''}</div>`;
        cont.appendChild(div);
      });
    })
    .catch(err => console.error(err));
}

// Aquí manejas las flechas y el renderizado básico del mes
(function () {
  const currentInput = document.getElementById('selectedMonth'); // Aquí tienes el input oculto con YYYY-MM
  const span = document.getElementById('currentMonth');
  const prev = document.getElementById('prevMonth');
  const next = document.getElementById('nextMonth');
  if (!currentInput || !span || !prev || !next) return;

  function toDisplay(ym) {
    const parts = ym.split('-'); const y = parseInt(parts[0], 10); const m = parseInt(parts[1], 10);
    const dt = new Date(y, m - 1, 1);
    span.textContent = dt.toLocaleString('es-ES', { month: 'long', year: 'numeric' });
  }

  function load(ym) {
    currentInput.value = ym;
    toDisplay(ym);
    mostrarMes(ym);
  }

  prev.addEventListener('click', () => {
    const p = currentInput.value.split('-'); let y = parseInt(p[0], 10); let m = parseInt(p[1], 10);
    m--; if (m < 1) { m = 12; y--; }
    load(y + '-' + String(m).padStart(2, '0'));
  });

  next.addEventListener('click', () => {
    const p = currentInput.value.split('-'); let y = parseInt(p[0], 10); let m = parseInt(p[1], 10);
    m++; if (m > 12) { m = 1; y++; }
    load(y + '-' + String(m).padStart(2, '0'));
  });

  // Carga inicial al entrar
  load(currentInput.value || (new Date()).toISOString().slice(0, 7));
})();
