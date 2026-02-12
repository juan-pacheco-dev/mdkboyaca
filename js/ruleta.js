// ---- AQUÍ TIENES TU RULETA TIPO TEMU ----
const canvas = document.getElementById("ruleta");
const ctx = canvas.getContext("2d");
const overlay = document.getElementById("ruleta-overlay");
const resultadoP = document.getElementById("resultado-ruleta");
const cerrarBtn = document.getElementById("cerrar-ruleta");

// Aquí defines las frases que quieres mostrar en la ruleta
const frases = [
  "Cuando una familia se ama 🧑‍🧑‍🧒, que importa el mundo 🌎",
  "El Camino no se detiene, solo cambia de forma.",
  "Vibramos en sintonía con la pasión de vivir y sentir el Taekwondo.",
  "Disciplina, respeto y tradición nos unen, creando un legado ♾ que perdura más allá del tiempo",
  "Si de todas formas vas a soñar, sueña en grande",
  "Entre más flexible es mi mente, más flexible es mi sonrisa",
  "En la vida están los que gatean, los que caminan, los que corren, los que vuelan y los que enseñan a Volar",
  "Contra viento llegare y contra marea peleare, y como si no hubiera un final yo ganare"
];

const numSeg = frases.length;
let anguloActual = 0;

// Con esta función puedes dividir el texto en varias líneas dentro del canvas
function wrapText(context, text, x, y, maxWidth, lineHeight) {
  const words = text.split(" ");
  let line = "";
  for (let n = 0; n < words.length; n++) {
    const testLine = line + words[n] + " ";
    const metrics = context.measureText(testLine);
    const testWidth = metrics.width;

    if (testWidth > maxWidth && n > 0) {
      context.fillText(line, x, y);
      line = words[n] + " ";
      y += lineHeight;
    } else {
      line = testLine;
    }
  }
  context.fillText(line, x, y);
}

// Aquí es donde dibujas toda la ruleta
function dibujarRuleta() {
  const anguloSeg = (2 * Math.PI) / numSeg;
  ctx.clearRect(0, 0, canvas.width, canvas.height);

  ctx.beginPath();
  ctx.arc(200, 200, 200, 0, 2 * Math.PI);
  ctx.fillStyle = "rgba(0, 123, 255, 0.3)";
  ctx.fill();

  for (let i = 0; i < numSeg; i++) {
    ctx.beginPath();
    ctx.moveTo(200, 200);
    ctx.arc(200, 200, 200, i * anguloSeg, (i + 1) * anguloSeg);
    ctx.fillStyle = i % 2 === 0 ? "#0d47a1" : "#1565c0";
    ctx.fill();

    ctx.save();
    ctx.translate(200, 200);
    ctx.rotate(i * anguloSeg + anguloSeg / 2);
    ctx.textAlign = "right";
    ctx.fillStyle = "#fff";
    ctx.font = "16px Arial";

    // Aquí usas wrapText para que las frases queden bien acomodadas
    wrapText(ctx, frases[i], 180, 0, 100, 16);

    ctx.restore();
  }
}

// Esta función hace que la ruleta empiece a girar
function girarRuleta() {
  overlay.style.display = "flex";
  resultadoP.style.display = "none";

  const giro = Math.random() * 10 + 10;
  const duracion = 3000;
  const start = performance.now();

  function anim(t) {
    const elapsed = t - start;
    if (elapsed < duracion) {
      const progreso = elapsed / duracion;
      anguloActual = (giro * progreso) % (2 * Math.PI);
      ctx.save();
      ctx.translate(200, 200);
      ctx.rotate(anguloActual);
      ctx.translate(-200, -200);
      dibujarRuleta();
      ctx.restore();
      requestAnimationFrame(anim);
    } else {
      const anguloSeg = (2 * Math.PI) / numSeg;
      const index = Math.floor(((2 * Math.PI - anguloActual + anguloSeg / 2) % (2 * Math.PI)) / anguloSeg);

      // Aquí muestras la frase que salió ganadora encima del canvas
      resultadoP.style.display = "block";
      resultadoP.textContent = frases[index];
    }
  }
  requestAnimationFrame(anim);
}

// Este es para que funcione el botón de cerrar
cerrarBtn.addEventListener("click", () => {
  overlay.style.display = "none";
});

// Esto se ejecuta apenas carga la página
window.addEventListener("load", () => {
  setTimeout(girarRuleta, 1000);
});
