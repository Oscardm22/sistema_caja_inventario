// Funciones específicas para el módulo de usuarios

class UsuariosActions {
    static getBaseUrl() {
        const currentPath = window.location.pathname;
        
        if (currentPath.includes('/modules/usuarios/')) {
            const pathParts = currentPath.split('/modules/usuarios/');
            return window.location.origin + pathParts[0] + '/modules/usuarios/';
        }
        
        return window.location.origin + currentPath.substring(0, currentPath.lastIndexOf('/') + 1);
    }
    
    static crearModal(titulo, contenido, acciones) {
        const modalHtml = `
            <div id="dynamic-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
                <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                    <div class="mt-3 text-center">
                        ${contenido}
                        <div class="items-center px-4 py-3">
                            ${acciones}
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        const modalContainer = document.createElement('div');
        modalContainer.innerHTML = modalHtml;
        document.body.appendChild(modalContainer);
        
        const modal = document.getElementById('dynamic-modal');
        
        // Cerrar modal al hacer clic fuera
        modal.onclick = (e) => {
            if (e.target === modal) {
                this.cerrarModal(modalContainer);
            }
        };
        
        // Mostrar con animación
        setTimeout(() => {
            modal.style.transition = 'opacity 0.3s ease';
            modal.style.opacity = '1';
        }, 10);
        
        return { modalContainer, modal };
    }
    
    static cerrarModal(modalContainer) {
        const modal = document.getElementById('dynamic-modal');
        if (modal) {
            modal.style.opacity = '0';
            setTimeout(() => {
                if (modalContainer.parentNode) {
                    modalContainer.remove();
                }
            }, 300);
        }
    }
    
    static cambiarEstado(userId, nuevoEstado) {
        const actionText = nuevoEstado === 'activo' ? 'activar' : 'desactivar';
        
        const contenido = `
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full ${nuevoEstado === 'activo' ? 'bg-green-100' : 'bg-yellow-100'}">
                <i class="${nuevoEstado === 'activo' ? 'fas fa-user-check text-green-600' : 'fas fa-user-slash text-yellow-600'} text-xl"></i>
            </div>
            <h3 class="text-lg leading-6 font-medium text-gray-900 mt-4">
                ${nuevoEstado === 'activo' ? 'Activar Usuario' : 'Desactivar Usuario'}
            </h3>
            <div class="mt-2 px-7 py-3">
                <p class="text-sm text-gray-500">
                    ¿Estás seguro de ${actionText} este usuario?
                </p>
            </div>
        `;
        
        const acciones = `
            <button id="cancel-btn" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 mr-2 transition-colors">
                Cancelar
            </button>
            <button id="confirm-btn" class="px-4 py-2 ${nuevoEstado === 'activo' ? 'bg-green-600 hover:bg-green-700' : 'bg-yellow-600 hover:bg-yellow-700'} text-white rounded-md transition-colors">
                <i class="${nuevoEstado === 'activo' ? 'fas fa-user-check' : 'fas fa-user-slash'} mr-2"></i>
                ${nuevoEstado === 'activo' ? 'Activar' : 'Desactivar'}
            </button>
        `;
        
        const { modalContainer } = this.crearModal('Cambiar Estado', contenido, acciones);
        
        document.getElementById('cancel-btn').onclick = () => this.cerrarModal(modalContainer);
        
        document.getElementById('confirm-btn').onclick = () => {
            this.cerrarModal(modalContainer);
            this.ajaxCambiarEstado(userId, nuevoEstado);
        };
    }
    
    static confirmarEliminacion(userId, nombre) {
        const contenido = `
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
            </div>
            <h3 class="text-lg leading-6 font-medium text-gray-900 mt-4">Confirmar Eliminación</h3>
            <div class="mt-2 px-7 py-3">
                <p class="text-sm text-gray-500">
                    ¿Estás seguro de eliminar al usuario <span class="font-semibold">"${nombre}"</span>?
                </p>
                <p class="text-sm text-red-500 mt-2 font-medium">
                    <i class="fas fa-exclamation-circle mr-1"></i>
                    Esta acción no se puede deshacer.
                </p>
            </div>
        `;
        
        const acciones = `
            <button id="cancel-btn" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 mr-2 transition-colors">
                Cancelar
            </button>
            <button id="confirm-btn" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors">
                <i class="fas fa-trash mr-2"></i>Eliminar
            </button>
        `;
        
        const { modalContainer } = this.crearModal('Eliminar Usuario', contenido, acciones);
        
        document.getElementById('cancel-btn').onclick = () => this.cerrarModal(modalContainer);
        
        document.getElementById('confirm-btn').onclick = () => {
            this.cerrarModal(modalContainer);
            this.ajaxEliminarUsuario(userId);
        };
    }
    
    static ajaxCambiarEstado(userId, nuevoEstado) {
        const loadingNotification = NotificationSystem.show('Procesando solicitud...', 'info', 0);
        const baseUrl = this.getBaseUrl();
        
        fetch(baseUrl + 'acciones.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=cambiar_estado&id=${userId}&estado=${nuevoEstado}`
        })
        .then(response => response.json())
        .then(data => {
            NotificationSystem.close(loadingNotification);
            
            if (data.success) {
                NotificationSystem.show(data.message, 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                NotificationSystem.show(data.message, 'error', 6000);
            }
        })
        .catch(error => {
            NotificationSystem.close(loadingNotification);
            NotificationSystem.show('Error de conexión con el servidor', 'error', 6000);
            console.error('Error:', error);
        });
    }
    
    static ajaxEliminarUsuario(userId) {
        const loadingNotification = NotificationSystem.show('Eliminando usuario...', 'info', 0);
        const baseUrl = this.getBaseUrl();
        
        fetch(baseUrl + 'acciones.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=eliminar&id=${userId}`
        })
        .then(response => response.json())
        .then(data => {
            NotificationSystem.close(loadingNotification);
            
            if (data.success) {
                NotificationSystem.show(data.message, 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                NotificationSystem.show(data.message, 'error', 6000);
            }
        })
        .catch(error => {
            NotificationSystem.close(loadingNotification);
            NotificationSystem.show('Error de conexión con el servidor', 'error', 6000);
            console.error('Error:', error);
        });
    }
}

// Funciones globales para compatibilidad
function cambiarEstado(userId, nuevoEstado) {
    UsuariosActions.cambiarEstado(userId, nuevoEstado);
}

function confirmarEliminacion(userId, nombre) {
    UsuariosActions.confirmarEliminacion(userId, nombre);
}