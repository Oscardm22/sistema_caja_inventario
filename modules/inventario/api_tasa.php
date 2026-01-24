<?php
// modules/inventario/api_tasa.php
require_once '../../config/session.php';
require_once '../../config/database.php';
require_once 'cache_tasa.php';

// Verificar si es una solicitud AJAX
$is_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

// Permitir acceso directo solo para debugging
if (!$is_ajax && !isset($_GET['action']) && php_sapi_name() !== 'cli') {
    header('HTTP/1.0 403 Forbidden');
    exit('Acceso directo no permitido');
}

// Inicializar cache
$tasaCache = new TasaBCVCache();

// Determinar acción
$action = $_GET['action'] ?? 'get';

header('Content-Type: application/json');

try {
    switch ($action) {
        case 'get':
            $tasa = $tasaCache->getTasa();
            echo json_encode([
                'success' => true,
                'tasa' => $tasa,
                'tasa_formatted' => number_format($tasa, 2, ',', '.'),
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            break;
            
        case 'actualizar':
            $tasa = $tasaCache->forceUpdate();
            echo json_encode([
                'success' => true,
                'tasa' => $tasa,
                'tasa_formatted' => number_format($tasa, 2, ',', '.'),
                'message' => 'Tasa actualizada correctamente',
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            break;
            
        case 'historial':
            $days = min($_GET['days'] ?? 30, 365); // Máximo 1 año
            $historial = $tasaCache->getHistory($days);
            echo json_encode([
                'success' => true,
                'historial' => $historial,
                'count' => count($historial)
            ]);
            break;
            
        default:
            echo json_encode([
                'success' => false,
                'error' => 'Acción no válida'
            ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>