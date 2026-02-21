// Funciones modales básicas
function abrirModalCrear() {
    document.getElementById('modal-titulo').textContent = 'Nueva Categoría';
    document.getElementById('form-categoria').reset();
    document.getElementById('categoria-id').value = '';
    document.getElementById('modal-categoria').classList.remove('hidden');
}

function abrirModalEditar(id) {
    fetch(`acciones_categorias.php?action=obtener&id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('modal-titulo').textContent = 'Editar Categoría';
                document.getElementById('categoria-id').value = data.categoria.id;
                document.getElementById('nombre').value = data.categoria.nombre;
                document.getElementById('descripcion').value = data.categoria.descripcion || '';
                document.getElementById('estado').value = data.categoria.estado;
                document.getElementById('modal-categoria').classList.remove('hidden');
            } else {
                mostrarNotificacion('error', data.error || 'Error al cargar categoría');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarNotificacion('error', 'Error de conexión');
        });
}

function abrirModalCambiarEstado(id, estadoActual, nombreCategoria, tieneProductos = false) {
    const nuevoEstado = estadoActual === 'activa' ? 'inactiva' : 'activa';
    
    // Guardar datos en el modal
    document.getElementById('estado-categoria-id').value = id;
    document.getElementById('estado-categoria-nuevo').value = nuevoEstado;
    document.getElementById('categoria-nombre-modal').textContent = nombreCategoria;
    
    // Configurar icono y mensajes según el cambio
    const icono = document.getElementById('estado-icono');
    const mensaje = document.getElementById('modal-estado-mensaje');
    const estadoActualBadge = document.getElementById('estado-actual-badge');
    const estadoNuevoBadge = document.getElementById('estado-nuevo-badge');
    
    if (nuevoEstado === 'inactiva') {
        // Activa → Inactiva
        icono.className = 'fas fa-pause-circle text-6xl text-yellow-500';
        mensaje.textContent = '¿Estás seguro de desactivar esta categoría?';
        estadoActualBadge.className = 'px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800';
        estadoActualBadge.textContent = 'Activa';
        estadoNuevoBadge.className = 'px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800';
        estadoNuevoBadge.textContent = 'Inactiva';
    } else {
        // Inactiva → Activa
        icono.className = 'fas fa-play-circle text-6xl text-green-500';
        mensaje.textContent = '¿Estás seguro de activar esta categoría?';
        estadoActualBadge.className = 'px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800';
        estadoActualBadge.textContent = 'Inactiva';
        estadoNuevoBadge.className = 'px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800';
        estadoNuevoBadge.textContent = 'Activa';
    }
    
    // Mostrar advertencia si tiene productos (solo cuando se va a desactivar)
    const advertenciaDiv = document.getElementById('productos-advertencia');
    if (tieneProductos && nuevoEstado === 'inactiva') {
        advertenciaDiv.classList.remove('hidden');
        document.getElementById('productos-mensaje').textContent = 
            `Esta categoría tiene ${tieneProductos} producto(s) asociado(s). Al desactivarla, los productos no se eliminarán pero quedarán en una categoría inactiva.`;
    } else {
        advertenciaDiv.classList.add('hidden');
    }
    
    // Mostrar modal
    document.getElementById('modal-cambiar-estado').classList.remove('hidden');
}

function cerrarModal() {
    document.getElementById('modal-categoria').classList.add('hidden');
    document.getElementById('form-categoria').reset();
    document.getElementById('categoria-id').value = '';
}

function cerrarModalEstado() {
    document.getElementById('modal-cambiar-estado').classList.add('hidden');
}

// Función para crear nueva categoría
function crearCategoria() {
    const formData = new FormData(document.getElementById('form-categoria'));
    formData.append('action', 'crear');
    
    const submitBtn = document.querySelector('#form-categoria button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Guardando...';
    
    fetch('acciones_categorias.php', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarNotificacion('success', data.message);
            cerrarModal();
            
            // Recargar la página después de un breve delay
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            mostrarNotificacion('error', data.error || 'Error al crear categoría');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-save mr-2"></i>Guardar';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarNotificacion('error', 'Error de conexión');
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-save mr-2"></i>Guardar';
    });
}

// Función para actualizar categoría existente
function actualizarCategoria() {
    const formData = new FormData(document.getElementById('form-categoria'));
    formData.append('action', 'editar');
    
    const submitBtn = document.querySelector('#form-categoria button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Actualizando...';
    
    fetch('acciones_categorias.php', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarNotificacion('success', data.message);
            cerrarModal();
            
            // Recargar la página después de un breve delay
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            mostrarNotificacion('error', data.error || 'Error al actualizar categoría');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-save mr-2"></i>Guardar';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarNotificacion('error', 'Error de conexión');
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fas fa-save mr-2"></i>Guardar';
    });
}

// Función para cambiar estado desde el modal
function cambiarEstadoCategoriaModal() {
    const id = document.getElementById('estado-categoria-id').value;
    const nuevoEstado = document.getElementById('estado-categoria-nuevo').value;
    
    const formData = new FormData();
    formData.append('action', 'cambiar_estado');
    formData.append('id', id);
    formData.append('estado', nuevoEstado);
    
    // Deshabilitar botón mientras se procesa
    const btnConfirmar = document.getElementById('btn-confirmar-cambio');
    btnConfirmar.disabled = true;
    btnConfirmar.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Procesando...';
    
    fetch('acciones_categorias.php', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarNotificacion('success', data.message);
            cerrarModalEstado();
            
            // Recargar la página después de un breve delay
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            mostrarNotificacion('error', data.error);
            btnConfirmar.disabled = false;
            btnConfirmar.innerHTML = '<i class="fas fa-check mr-2"></i>Confirmar Cambio';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarNotificacion('error', 'Error de conexión');
        btnConfirmar.disabled = false;
        btnConfirmar.innerHTML = '<i class="fas fa-check mr-2"></i>Confirmar Cambio';
    });
}

// Función para notificaciones
function mostrarNotificacion(tipo, mensaje) {
    // Limpiar notificaciones anteriores
    const notificacionesAnteriores = document.querySelectorAll('.notificacion-flotante');
    notificacionesAnteriores.forEach(notif => notif.remove());
    
    const notification = document.createElement('div');
    notification.className = `notificacion-flotante fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg text-white z-50 ${tipo === 'success' ? 'bg-green-500' : 'bg-red-500'}`;
    notification.textContent = mensaje;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 3000);
}

// Event Listeners
document.addEventListener('DOMContentLoaded', function() {
    // Listener para formulario de categoría (crear/editar)
    const formCategoria = document.getElementById('form-categoria');
    if (formCategoria) {
        formCategoria.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const id = document.getElementById('categoria-id').value;
            
            if (id) {
                // Es una edición
                actualizarCategoria();
            } else {
                // Es una creación
                crearCategoria();
            }
        });
    }
    
    // Listener para formulario de cambio de estado
    const formCambiarEstado = document.getElementById('form-cambiar-estado');
    if (formCambiarEstado) {
        formCambiarEstado.addEventListener('submit', function(e) {
            e.preventDefault();
            cambiarEstadoCategoriaModal();
        });
    }
});