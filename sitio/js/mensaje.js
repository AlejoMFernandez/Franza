/**
 * mensaje.js — Auto-dismiss de notificaciones (.msj)
 * La notificación aparece con animación CSS y se oculta a los 5 segundos.
 */
document.addEventListener('DOMContentLoaded', function() {
    var msj = document.querySelector('.msj');
    if(msj) {
        // Remove any old animate.css classes
        msj.classList.remove('animate__animated', 'animate__bounceInRight', 'animate__bounceInDown');
        // CSS handles the entrance animation via @keyframes msjSlideIn.
        // After 5s, trigger the exit animation.
        setTimeout(function() {
            msj.style.animation = 'msjSlideOut 0.4s cubic-bezier(0.4, 0, 0.2, 1) forwards';
            msj.addEventListener('animationend', function() {
                msj.remove();
            }, { once: true });
        }, 5000);
    }
});