// assets/js/dashboard.js

document.addEventListener('DOMContentLoaded', function() {
    console.log('Dashboard cargado con datos reales');
    
    // Actualizar hora cada segundo
    function updateClock() {
        const now = new Date();
        
        // Formatear fecha en español
        const fecha = now.toLocaleDateString('es-PE', {
            day: '2-digit',
            month: '2-digit', 
            year: 'numeric'
        });
        
        // Formatear hora en 24h
        const hora = now.toLocaleTimeString('es-PE', {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: false
        });
        
        const dateStr = `${fecha} ${hora}`;
        const clockElement = document.getElementById('live-clock');
        
        if (clockElement) {
            clockElement.textContent = dateStr;
        }
    }
    
    // Actualizar inmediatamente y cada segundo
    updateClock();
    setInterval(updateClock, 1000);
    
    // Manejo de cache
    if (window.performance && window.performance.navigation.type === 2) {
        window.location.reload();
    }
});