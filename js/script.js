// Aquí inicializas tu carrusel Swiper
var swiper = new Swiper(".mySwiper", {
  loop: true, // Esto hace que vuelva al inicio cuando llegas al final
  navigation: {
    nextEl: ".swiper-button-next", // Este es tu botón de siguiente
    prevEl: ".swiper-button-prev", // Y este el de anterior
  },
  pagination: {
    el: ".swiper-pagination", // Aquí activas la paginación
    clickable: true,          // Para que puedas hacer clic en los puntitos
  },
});
