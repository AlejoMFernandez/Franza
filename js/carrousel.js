function toggleMenu() {
  console.log("Menu toggled");
  const nav = document.querySelector('.navbot');
  nav.classList.toggle('active');
}

document.addEventListener("DOMContentLoaded", () => {
    const carousel = document.querySelector(".carousel")
    const items = document.querySelectorAll(".carousel-item")
    const prevBtn = document.querySelector(".carousel-button.prev")
    const nextBtn = document.querySelector(".carousel-button.next")
  
    let activeIndex = 1 // Comenzamos con el segundo elemento activo (índice 1)
  
    // Función para actualizar las clases de los elementos
    function updateCarousel() {
      items.forEach((item, index) => {
        // Eliminar todas las clases
        item.classList.remove("active", "prev", "next")
  
        // Asignar clases según la posición relativa al elemento activo
        if (index === activeIndex) {
          item.classList.add("active")
        } else if (index === getPrevIndex()) {
          item.classList.add("prev")
        } else if (index === getNextIndex()) {
          item.classList.add("next")
        }
  
        // Calcular la posición z para crear el efecto de profundidad
        const distance = Math.abs(index - activeIndex)
        const zIndex = items.length - distance
        item.style.zIndex = zIndex
      })
    }
  
    // Obtener el índice del elemento anterior
    function getPrevIndex() {
      return activeIndex === 0 ? items.length - 1 : activeIndex - 1
    }
  
    // Obtener el índice del elemento siguiente
    function getNextIndex() {
      return activeIndex === items.length - 1 ? 0 : activeIndex + 1
    }
  
    // Evento para el botón anterior
    prevBtn.addEventListener("click", () => {
      activeIndex = getPrevIndex()
      updateCarousel()
    })
  
    // Evento para el botón siguiente
    nextBtn.addEventListener("click", () => {
      activeIndex = getNextIndex()
      updateCarousel()
    })
  
    // Inicializar el carrusel
    updateCarousel()
  
    // Añadir funcionalidad de deslizamiento táctil
    let touchStartX = 0
    let touchEndX = 0
  
    carousel.addEventListener("touchstart", (e) => {
      touchStartX = e.changedTouches[0].screenX
    })
  
    carousel.addEventListener("touchend", (e) => {
      touchEndX = e.changedTouches[0].screenX
      handleSwipe()
    })
  
    function handleSwipe() {
      const swipeThreshold = 50
      if (touchEndX < touchStartX - swipeThreshold) {
        // Deslizamiento a la izquierda
        activeIndex = getNextIndex()
        updateCarousel()
      } else if (touchEndX > touchStartX + swipeThreshold) {
        // Deslizamiento a la derecha
        activeIndex = getPrevIndex()
        updateCarousel()
      }
    }
  
    // Añadir funcionalidad de teclado
    document.addEventListener("keydown", (e) => {
      if (e.key === "ArrowLeft") {
        activeIndex = getPrevIndex()
        updateCarousel()
      } else if (e.key === "ArrowRight") {
        activeIndex = getNextIndex()
        updateCarousel()
      }
    })
  
    // Añadir funcionalidad de clic en las imágenes
    items.forEach((item, index) => {
      item.addEventListener("click", () => {
        if (index !== activeIndex) {
          activeIndex = index
          updateCarousel()
        }
      })
    })
  
    // Iniciar autoplay (opcional)
    let autoplayInterval
  
    function startAutoplay() {
      autoplayInterval = setInterval(() => {
        activeIndex = getNextIndex()
        updateCarousel()
      }, 5000) // Cambiar cada 5 segundos
    }
  
    function stopAutoplay() {
      clearInterval(autoplayInterval)
    }
  
    // Comentar la siguiente línea si no deseas el autoplay
    // startAutoplay();
  
    // Detener autoplay al interactuar con el carrusel
    carousel.addEventListener("mouseenter", stopAutoplay)
    prevBtn.addEventListener("mouseenter", stopAutoplay)
    nextBtn.addEventListener("mouseenter", stopAutoplay)
  
    // Reiniciar autoplay al dejar de interactuar
    // Comentar las siguientes líneas si no deseas el autoplay
    /*
      carousel.addEventListener('mouseleave', startAutoplay);
      prevBtn.addEventListener('mouseleave', startAutoplay);
      nextBtn.addEventListener('mouseleave', startAutoplay);
      */
  })
  