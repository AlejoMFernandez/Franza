/**
 * userButton.js — Toggle del dropdown de usuario en el header
 */
document.addEventListener('DOMContentLoaded', function() {
    const userMenu = document.querySelector('.user-menu');
    const userBtn = document.querySelector('.user-btn');
    if(userBtn && userMenu) {
        userBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            userMenu.classList.toggle('open');
        });
        document.addEventListener('click', function() {
            userMenu.classList.remove('open');
        });
    }
});