function openWhatsApp() {
    const phoneNumber = "5491136932502"; // Número en formato internacional (código de país + número)
    const message = encodeURIComponent("¡Hola! Me gustaría hacer una consulta."); // Mensaje opcional
    const whatsappUrl = `https://wa.me/${phoneNumber}?text=${message}`;
    window.open(whatsappUrl, '_blank');
}