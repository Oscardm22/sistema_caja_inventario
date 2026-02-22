// Sistema de notificaciones

function mostrarNotificacion(tipo, mensaje, duracion = 5000) {
    // Crear contenedor si no existe
    let container = document.getElementById('notification-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'notification-container';
        container.className = 'fixed top-4 right-4 z-50 space-y-2';
        document.body.appendChild(container);
    }
    
    // Configurar colores según tipo
    const config = {
        success: {
            bg: 'bg-green-500',
            icon: 'fa-check-circle',
            title: 'Éxito'
        },
        error: {
            bg: 'bg-red-500',
            icon: 'fa-exclamation-circle',
            title: 'Error'
        },
        warning: {
            bg: 'bg-yellow-500',
            icon: 'fa-exclamation-triangle',
            title: 'Advertencia'
        },
        info: {
            bg: 'bg-blue-500',
            icon: 'fa-info-circle',
            title: 'Información'
        }
    };
    
    const conf = config[tipo] || config.info;
    
    // Crear notificación
    const notification = document.createElement('div');
    notification.className = `${conf.bg} text-white rounded-lg shadow-lg overflow-hidden transform transition-all duration-300 translate-x-0 opacity-100`;
    notification.style.minWidth = '300px';
    notification.style.maxWidth = '400px';
    
    notification.innerHTML = `
        <div class="p-4">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <i class="fas ${conf.icon} text-xl"></i>
                </div>
                <div class="ml-3 flex-1">
                    <p class="text-sm font-semibold">${conf.title}</p>
                    <p class="text-sm opacity-90 mt-1">${mensaje}</p>
                </div>
                <button onclick="this.closest('.fixed').remove()" class="ml-4 text-white hover:text-gray-200 transition-colors">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <!-- Barra de progreso -->
            <div class="absolute bottom-0 left-0 h-1 bg-white bg-opacity-50 progress-bar" style="width: 100%; transition: width ${duracion}ms linear;"></div>
        </div>
    `;
    
    // Agregar al contenedor
    container.appendChild(notification);
    
    // Animar barra de progreso
    setTimeout(() => {
        const progressBar = notification.querySelector('.progress-bar');
        if (progressBar) {
            progressBar.style.width = '0%';
        }
    }, 10);
    
    // Auto-eliminar después de la duración
    setTimeout(() => {
        if (notification && notification.parentNode) {
            notification.style.opacity = '0';
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.remove();
                }
            }, 300);
        }
    }, duracion);
}

function actualizarTasaBCV() {
    const btn = document.getElementById('btn-actualizar-tasa');
    const icon = btn.querySelector('i');
    
    // Deshabilitar botón y mostrar animación
    btn.disabled = true;
    btn.classList.add('opacity-50', 'cursor-not-allowed');
    icon.classList.add('fa-spin');
    
    fetch('api_tasa.php?action=actualizar')
        .then(response => response.json())
        .then(data => {
            // Re-habilitar botón
            btn.disabled = false;
            btn.classList.remove('opacity-50', 'cursor-not-allowed');
            icon.classList.remove('fa-spin');
            
            if (data.success) {
                // Actualizar el display de tasa
                const tasaDisplay = document.getElementById('tasa-bcv-display');
                if (tasaDisplay) {
                    tasaDisplay.textContent = 'Bs. ' + data.tasa_formatted;
                }
                
                // Mostrar notificación de éxito
                mostrarNotificacion('success', ' Tasa BCV actualizada correctamente a Bs. ' + data.tasa_formatted);
            } else {
                // Mostrar notificación de error
                mostrarNotificacion('error', ' ' + (data.message || 'Error al actualizar la tasa BCV'));
            }
        })
        .catch(error => {
            // Re-habilitar botón
            btn.disabled = false;
            btn.classList.remove('opacity-50', 'cursor-not-allowed');
            icon.classList.remove('fa-spin');
            
            // Mostrar notificación de error
            mostrarNotificacion('error', '❌ Error de conexión al actualizar la tasa');
            console.error('Error:', error);
        });
}

// Función para mantener los filtros al cambiar de página
document.addEventListener('DOMContentLoaded', function() {
    // Si hay un formulario de filtros, asegurar que resetee a página 1
    const filterForm = document.querySelector('form[method="GET"]');
    if (filterForm) {
        filterForm.addEventListener('submit', function() {
            // Crear o actualizar un campo hidden para página=1
            let paginaInput = document.querySelector('input[name="pagina"]');
            if (!paginaInput) {
                paginaInput = document.createElement('input');
                paginaInput.type = 'hidden';
                paginaInput.name = 'pagina';
                filterForm.appendChild(paginaInput);
            }
            paginaInput.value = '1';
        });
    }
});