<?php
// modules/inventario/includes/tasa_bcv_widget.php
?>
<div class="tasa-bcv-widget bg-gradient-to-r from-yellow-50 to-yellow-100 border border-yellow-200 rounded-lg p-4 mb-6">
    <div class="flex justify-between items-center">
        <div>
            <h3 class="font-bold text-yellow-800">Tasa BCV Actual</h3>
            <p class="text-2xl font-bold text-yellow-900">
                Bs. <span id="tasa-bcv-display"><?php echo number_format($tasa_bcv, 2, ',', '.'); ?></span>
            </p>
            <p class="text-sm text-yellow-700">
                Última actualización: <span id="tasa-fecha"><?php echo date('d/m/Y H:i'); ?></span>
            </p>
        </div>
        <div class="text-right">
            <button onclick="actualizarTasaBCV()" 
                    class="px-3 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition-colors">
                <i class="fas fa-sync-alt mr-2"></i>Actualizar
            </button>
        </div>
    </div>
</div>