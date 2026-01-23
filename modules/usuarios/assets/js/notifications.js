// Sistema de notificaciones reutilizable
class NotificationSystem {
    static show(message, type = 'info', duration = 5000) {
        const config = {
            'success': {
                icon: 'fas fa-check-circle',
                bgColor: 'bg-green-50',
                textColor: 'text-green-800',
                borderColor: 'border-green-200',
                iconColor: 'text-green-400'
            },
            'error': {
                icon: 'fas fa-exclamation-circle',
                bgColor: 'bg-red-50',
                textColor: 'text-red-800',
                borderColor: 'border-red-200',
                iconColor: 'text-red-400'
            },
            'warning': {
                icon: 'fas fa-exclamation-triangle',
                bgColor: 'bg-yellow-50',
                textColor: 'text-yellow-800',
                borderColor: 'border-yellow-200',
                iconColor: 'text-yellow-400'
            },
            'info': {
                icon: 'fas fa-info-circle',
                bgColor: 'bg-blue-50',
                textColor: 'text-blue-800',
                borderColor: 'border-blue-200',
                iconColor: 'text-blue-400'
            }
        };
        
        const style = config[type] || config.info;
        
        const notification = document.createElement('div');
        notification.className = `notification ${style.bgColor} ${style.borderColor} border rounded-lg shadow-lg p-4 mb-2`;
        
        notification.innerHTML = `
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <i class="${style.icon} ${style.iconColor} text-lg"></i>
                </div>
                <div class="ml-3 w-0 flex-1">
                    <p class="text-sm font-medium ${style.textColor}">${message}</p>
                </div>
                <div class="ml-4 flex-shrink-0 flex">
                    <button type="button" class="inline-flex ${style.textColor} hover:opacity-75 focus:outline-none close-btn">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        `;
        
        let container = document.getElementById('notifications-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'notifications-container';
            container.className = 'notifications';
            document.body.appendChild(container);
        }
        container.appendChild(notification);
        
        const closeBtn = notification.querySelector('.close-btn');
        closeBtn.onclick = () => this.close(notification);
        
        if (duration > 0) {
            setTimeout(() => this.close(notification), duration);
        }
        
        return notification;
    }
    
    static close(notification) {
        if (notification.parentNode) {
            notification.classList.add('hide');
            setTimeout(() => notification.remove(), 300);
        }
    }
}

// Función global para compatibilidad
function showNotification(message, type, duration) {
    return NotificationSystem.show(message, type, duration);
}