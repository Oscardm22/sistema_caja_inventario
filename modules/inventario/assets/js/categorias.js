// modules/inventario/assets/js/categorias.js

document.addEventListener('DOMContentLoaded', function() {
    // Configurar formulario de categoría
    const formCategoria = document.getElementById('form-categoria');
    
    if (formCategoria) {
        formCategoria.addEventListener('submit', function(e) {
            e.preventDefault();
            guardarCategoria();
        });
    }
});

function guardarCategoria() {
    const formData = new FormData(document.getElementById('form-categoria'));
    const categoriaId = document.getElementById('categoria-id').value;
    const action = categoriaId ? 'editar' : 'crear';
    
    // Agregar action al formData
    formData.append('action', action);
    
    // Añadir header X-Requested-With para identificar como AJAX
    fetch('acciones_categorias.php', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Error en la respuesta del servidor: ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            mostrarNotificacion('success', data.message);
            cerrarModal();
            
            // Recargar la página después de un breve delay
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            mostrarNotificacion('error', data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarNotificacion('error', 'Error de conexión: ' + error.message);
    });
}

function cambiarEstadoCategoria(id, estadoActual) {
    if (!confirm('¿Estás seguro de cambiar el estado de esta categoría?')) {
        return;
    }
    
    const nuevoEstado = estadoActual === 'activa' ? 'inactiva' : 'activa';
    const formData = new FormData();
    formData.append('action', 'cambiar_estado');
    formData.append('id', id);
    formData.append('estado', nuevoEstado);
    
    fetch('acciones_categorias.php', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Error en la respuesta del servidor: ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            mostrarNotificacion('success', data.message);
            
            // Actualizar la fila sin recargar toda la página
            const fila = document.getElementById(`categoria-${id}`);
            if (fila) {
                // Actualizar estado visual
                const estadoBadge = fila.querySelector('.bg-green-100, .bg-red-100');
                if (estadoBadge) {
                    estadoBadge.className = `px-2 py-1 text-xs rounded-full ${nuevoEstado === 'activa' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}`;
                    estadoBadge.textContent = nuevoEstado === 'activa' ? 'Activa' : 'Inactiva';
                }
                
                // Actualizar ícono del botón
                const icono = fila.querySelector('.fa-play, .fa-pause');
                if (icono) {
                    icono.className = nuevoEstado === 'activa' ? 'fas fa-pause' : 'fas fa-play';
                }
                
                // Actualizar color del botón
                const boton = fila.querySelector('.text-yellow-600, .text-green-600');
                if (boton) {
                    boton.className = nuevoEstado === 'activa' ? 'text-yellow-600 hover:text-yellow-900' : 'text-green-600 hover:text-green-900';
                }
            }
        } else {
            mostrarNotificacion('error', data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarNotificacion('error', 'Error de conexión');
    });
}

function eliminarCategoria(id) {
    if (!confirm('¿Estás seguro de eliminar esta categoría?\n\nEsta acción no se puede deshacer.')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'eliminar');
    formData.append('id', id);
    
    fetch('acciones_categorias.php', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Error en la respuesta del servidor: ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            mostrarNotificacion('success', data.message);
            
            // Eliminar la fila de la tabla
            const fila = document.getElementById(`categoria-${id}`);
            if (fila) {
                fila.remove();
                
                // Actualizar contadores
                actualizarContadores();
            }
        } else {
            mostrarNotificacion('error', data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarNotificacion('error', 'Error de conexión');
    });
}

function actualizarContadores() {
    // Actualizar contador de total de categorías
    const filas = document.querySelectorAll('tbody tr');
    const totalCategorias = filas.length;
    
    const totalElements = document.querySelectorAll('.text-2xl.font-bold');
    if (totalElements.length > 0) {
        totalElements[0].textContent = totalCategorias;
    }
    
    // Actualizar contador de categorías activas
    const activas = document.querySelectorAll('.bg-green-100.text-green-800').length;
    if (totalElements.length > 1) {
        totalElements[1].textContent = activas;
    }
    
    // Si no hay categorías, mostrar mensaje de "no hay categorías"
    if (totalCategorias === 0) {
        const tablaContainer = document.querySelector('.bg-white.rounded-lg.shadow');
        if (tablaContainer) {
            const emptyHTML = `
                <div class="p-8 text-center text-gray-500">
                    <i class="fas fa-tags text-4xl mb-4 text-gray-300"></i>
                    <p class="text-lg">No hay categorías creadas</p>
                    <p class="text-sm">Crea tu primera categoría para organizar los productos</p>
                    <button onclick="abrirModalCrear()" 
                            class="mt-4 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        <i class="fas fa-plus mr-2"></i>Crear Categoría
                    </button>
                </div>
            `;
            tablaContainer.innerHTML = emptyHTML;
        }
    }
}