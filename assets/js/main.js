/**
 * JavaScript principal
 * /assets/js/main.js
 */

// Carrito de cotización
let cotizacionItems = JSON.parse(localStorage.getItem('cotizacion') || '[]');

// Actualizar contador del carrito
function updateCartCount() {
    const count = cotizacionItems.length;
    document.getElementById('cart-count').textContent = count;
}

// Agregar producto a cotización
function agregarCotizacion(productoId) {
    // Aquí deberías hacer una llamada AJAX para obtener los detalles del producto
    // Por ahora, simulamos
    if (!cotizacionItems.find(item => item.id === productoId)) {
        cotizacionItems.push({
            id: productoId,
            cantidad: 1
        });
        localStorage.setItem('cotizacion', JSON.stringify(cotizacionItems));
        updateCartCount();
        
        // Mostrar notificación
        alert('Producto agregado a la cotización');
    } else {
        alert('Este producto ya está en la cotización');
    }
}

// Ver cotización
function verCotizacion() {
    window.location.href = '/cotizacion';
}

// Inicializar
document.addEventListener('DOMContentLoaded', function() {
    updateCartCount();
    
    // Tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });
});