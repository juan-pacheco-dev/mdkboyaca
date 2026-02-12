// js/ui.js - Toggles y controles visuales básicos

document.addEventListener('click', (e) => {
  // Toggle genérico con data-toggle
  const toggle = e.target.closest('[data-toggle]');
  if (toggle) {
    const selector = toggle.getAttribute('data-toggle');
    const target = document.querySelector(selector);
    if (target) {
      target.hidden = !target.hidden;
    }
  }
});
