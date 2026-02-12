// =========================
// CALENDARIO MDK (Modo Visual)
// =========================

// Aquí tienes tus elementos del DOM
const txtMes = document.getElementById("mesActualTexto");
const inputMes = document.getElementById("mesActual");
const listaEventos = document.getElementById("listaEventos");

// Si no existen los elementos, no corre (previene errores)
if (!txtMes || !inputMes) {
    console.warn("⛔ Elementos del calendario no encontrados");
}

// Con esta función formateas el mes para mostrarlo bonito
function nombreMes(fechaStr) {
    const meses = [
        "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio",
        "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"
    ];
    const [y, m] = fechaStr.split("-");
    return meses[Number(m) - 1] + " " + y;
}

// Esta función carga el mes inicial apenas entras
function actualizarMes() {
    const mes = inputMes.value;
    txtMes.textContent = nombreMes(mes);
    cargarEventos(mes);
}

// Aquí obtienes los eventos del mes desde el localStorage
function cargarEventos(mes) {
    const data = JSON.parse(localStorage.getItem("inscripcionesTorneos") || "[]");

    const eventosMes = data.filter(ev => ev.fecha.startsWith(mes));

    listaEventos.innerHTML = eventosMes.length
        ? eventosMes.map(e => `
        <li class="evento torneo">
            🏆 ${e.torneo}
            ${e.nombre ? ' — ' + e.nombre : ''}
            <br>
            <strong>${e.categoria}</strong> • ${e.modalidad}
            ${e.lugar ? ' • 📍 ' + e.lugar : ''}
            <br>
            📅 ${e.fecha}
        </li>
    `).join("")
        : `<li class="evento">No hay eventos programados este mes.</li>`;
}

// ✅ 🔥 YA NO CONTROLAMOS FLECHAS AQUÍ 🔥

// Solo actualizar cuando cargue
actualizarMes();
