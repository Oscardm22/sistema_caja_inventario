// modules/inventario/assets/js/productos.js

// Funciones para productos
function mostrarDetalleProducto(id) {
    // Aquí irá la lógica para mostrar detalles del producto
    alert('Detalle del producto ID: ' + id);
}

function cambiarEstadoProducto(id, estadoActual) {
    if (!confirm('¿Estás seguro de cambiar el estado de este producto?')) {
        return;
    }
    
    const nuevoEstado = estadoActual === 'activo' ? 'inactivo' : 'activo';
    
    fetch('acciones.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=cambiar_estado&id=${id}&estado=${nuevoEstado}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + (data.error || 'No se pudo cambiar el estado'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error de conexión');
    });
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    // Aquí puedes agregar inicializaciones adicionales
});