// modules/inventario/assets/js/tasa_bcv.js

class TasaBCVManager {
    constructor() {
        this.tasaElement = document.getElementById('tasa-bcv');
        this.interval = null;
        this.updateInterval = 3600000; // 1 hora en milisegundos
    }
    
    init() {
        // Actualizar automáticamente cada hora
        this.startAutoUpdate();
        
        // Actualizar al hacer focus en la ventana
        window.addEventListener('focus', () => {
            this.checkAndUpdate();
        });
    }
    
    startAutoUpdate() {
        if (this.interval) {
            clearInterval(this.interval);
        }
        
        this.interval = setInterval(() => {
            this.updateTasa();
        }, this.updateInterval);
    }
    
    async checkAndUpdate() {
        // Verificar si la tasa necesita actualización
        const response = await fetch('api_tasa.php?action=get');
        const data = await response.json();
        
        if (data.success) {
            const lastUpdate = new Date(data.timestamp);
            const now = new Date();
            const hoursDiff = (now - lastUpdate) / (1000 * 60 * 60);
            
            if (hoursDiff > 1) {
                this.updateTasa();
            }
        }
    }
    
    async updateTasa() {
        try {
            const response = await fetch('api_tasa.php?action=actualizar');
            const data = await response.json();
            
            if (data.success && this.tasaElement) {
                this.tasaElement.textContent = 'Bs. ' + data.tasa_formatted;
                this.showNotification('Tasa BCV actualizada', 'success');
            }
        } catch (error) {
            console.error('Error updating tasa BCV:', error);
            this.showNotification('Error al actualizar tasa', 'error');
        }
    }
    
    showNotification(message, type = 'info') {
        // Usar tu sistema de notificaciones existente
        if (typeof mostrarNotificacion === 'function') {
            mostrarNotificacion(type, message);
        } else {
            // Notificación simple si no existe el sistema
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 px-4 py-2 rounded-lg shadow-lg ${
                type === 'success' ? 'bg-green-500' : 'bg-red-500'
            } text-white`;
            notification.textContent = message;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 3000);
        }
    }
    
    // Obtener tasa actual para cálculos
    async getCurrentTasa() {
        try {
            const response = await fetch('api_tasa.php?action=get');
            const data = await response.json();
            return data.success ? data.tasa : null;
        } catch (error) {
            console.error('Error getting tasa:', error);
            return null;
        }
    }
    
    // Calcular precio en BS
    calcularPrecioBS(precioUSD, margen = 30) {
        const tasa = parseFloat(this.tasaElement?.textContent?.replace(/[^\d.,]/g, '')?.replace(',', '.')) || 355.55;
        const precioBase = precioUSD * tasa;
        const ganancia = precioBase * (margen / 100);
        return precioBase + ganancia;
    }
}

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    window.tasaBCVManager = new TasaBCVManager();
    window.tasaBCVManager.init();
});