<?php
// modules/inventario/includes/tasa_bcv_widget.php
require_once '../cache_tasa.php';

$tasaCache = new TasaBCVCache();
$tasa_bcv = $tasaCache->getTasa();

// Obtener fecha de última actualización
$sql = "SELECT fecha_consulta FROM tasa_bcv_cache 
        ORDER BY fecha_consulta DESC LIMIT 1";
$database = Database::getInstance();
$db = $database->getConnection();
$result = $db->query($sql);
$last_update = $result ? $result->fetch_assoc()['fecha_consulta'] : date('Y-m-d H:i:s');
?>
<div class="tasa-bcv-widget bg-gradient-to-r from-yellow-50 to-yellow-100 border border-yellow-200 rounded-lg p-4 mb-6">
    <div class="flex justify-between items-center">
        <div>
            <h3 class="font-bold text-yellow-800">Tasa BCV Actual</h3>
            <p class="text-2xl font-bold text-yellow-900">
                Bs. <span id="tasa-bcv-display"><?php echo number_format($tasa_bcv, 2, ',', '.'); ?></span>
            </p>
            <p class="text-sm text-yellow-700">
                Última actualización: <span id="tasa-fecha"><?php echo date('d/m/Y H:i', strtotime($last_update)); ?></span>
                <br>
                <small class="text-yellow-600">(Se actualiza automáticamente cada hora)</small>
            </p>
        </div>
        <div class="text-right">
            <button onclick="actualizarTasaBCV()" 
                    class="px-3 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition-colors disabled:opacity-50"
                    id="btn-actualizar-tasa"
                    title="Actualizar tasa manualmente">
                <i class="fas fa-sync-alt mr-2"></i>Actualizar
            </button>
        </div>
    </div>
    <div class="mt-2 text-xs hidden" id="tasa-mensaje"></div>
</div>

<script>
// Variable para controlar si ya se está actualizando
let actualizandoTasa = false;

function actualizarTasaBCV() {
    if (actualizandoTasa) {
        return;
    }
    
    const btn = document.getElementById('btn-actualizar-tasa');
    const mensaje = document.getElementById('tasa-mensaje');
    
    // Deshabilitar botón y mostrar carga
    actualizandoTasa = true;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Actualizando...';
    
    fetch('api_tasa.php?action=actualizar')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Actualizar display
                document.getElementById('tasa-bcv-display').textContent = data.tasa_formatted;
                document.getElementById('tasa-fecha').textContent = new Date().toLocaleString('es-VE');
                
                // Mostrar mensaje de éxito
                mensaje.textContent = '✅ ' + data.message;
                mensaje.className = 'mt-2 text-xs text-green-600';
                mensaje.classList.remove('hidden');
                
                // Si estás en una página de productos, actualizar precios en Bs
                if (typeof actualizarPreciosBs === 'function') {
                    actualizarPreciosBs(data.tasa);
                }
            } else {
                mensaje.textContent = '❌ Error: ' + data.error;
                mensaje.className = 'mt-2 text-xs text-red-600';
                mensaje.classList.remove('hidden');
            }
        })
        .catch(error => {
            mensaje.textContent = '❌ Error de conexión: ' + error.message;
            mensaje.className = 'mt-2 text-xs text-red-600';
            mensaje.classList.remove('hidden');
        })
        .finally(() => {
            // Restaurar botón después de 3 segundos
            setTimeout(() => {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-sync-alt mr-2"></i>Actualizar';
                actualizandoTasa = false;
                
                // Ocultar mensaje después de 5 segundos
                setTimeout(() => {
                    mensaje.classList.add('hidden');
                }, 5000);
            }, 3000);
        });
}

// Solo actualizar automáticamente si han pasado más de 30 minutos desde la última actualización
const ultimaActualizacion = document.getElementById('tasa-fecha').textContent;
function necesitaActualizacion() {
    // Implementar lógica para verificar si necesita actualización
    // Por ahora, actualizar cada 30 minutos
    return true;
}

if (necesitaActualizacion()) {
    // Actualizar automáticamente cada 30 minutos
    setInterval(() => {
        if (!actualizandoTasa) {
            actualizarTasaBCV();
        }
    }, 30 * 60 * 1000); // 30 minutos
}
</script>