<?php
// modules/caja/acciones.php
require_once '../../config/session.php';
require_once '../../config/database.php';
require_once 'controllers/CajaController.php';

// Verificar permisos
checkPermission(['admin', 'cajero']);

$controller = new CajaController();
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'buscar_clientes':
        $controller->buscarClientesAction();
        break;
        
    case 'crear_cliente':
        $controller->crearClienteAction();
        break;
        
    case 'buscar_productos':
        $controller->buscarProductosAction();
        break;
        
    case 'obtener_producto':
        $controller->obtenerProductoAction();
        break;
        
    case 'procesar_pago':
        $controller->procesarPagoAction();
        break;
        
    case 'guardar_pendiente':
        $controller->guardarPendienteAction();
        break;
        
    case 'actualizar_tasa':
        $controller->actualizarTasaAction();
        break;
        
    default:
        http_response_code(404);
        echo json_encode(['error' => 'Acción no encontrada']);
        break;
}