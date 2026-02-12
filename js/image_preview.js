// js/image_preview.js - Previsualización de imágenes en formularios

document.addEventListener('change', (e) => {
  if (e.target.matches('input[type=file][data-preview]')) {
    const previewSelector = e.target.getAttribute('data-preview');
    const img = document.querySelector(previewSelector);
    const [file] = e.target.files || [];
    
    if (img && file) {
      img.src = URL.createObjectURL(file);
      img.style.display = 'block';
    }
  }
});
