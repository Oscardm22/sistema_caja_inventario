<?php
// modules/inventario/acciones.php
require_once '../../config/session.php';
require_once '../../config/database.php';
require_once 'models/InventarioModel.php';

// Verificar sesión - CORREGIR EL NOMBRE DE LA VARIABLE
if (!isset($_SESSION['user_id'])) { // Cambiar 'usuario_id' por 'user_id'
    http_response_code(401);
    echo json_encode([
        'success' => false, 
        'error' => 'No autenticado. Por favor, inicia sesión nuevamente.',
        'session_debug' => $_SESSION
    ]);
    exit();
}

// Verificar permisos (solo admin puede cambiar estados)
$usuario_rol = $_SESSION['user_role'] ?? 'cajero';
$es_admin = ($usuario_rol === 'admin');

header('Content-Type: application/json');

// Obtener acción
$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    $model = new InventarioModel();
    
    switch ($action) {
        case 'cambiar_estado':
            $id = $_POST['id'] ?? 0;
            $nuevo_estado = $_POST['estado'] ?? '';
            
            if (empty($id) || !in_array($nuevo_estado, ['activo', 'inactivo'])) {
                throw new Exception('Parámetros inválidos');
            }
            
            $resultado = $model->cambiarEstadoProducto($id, $nuevo_estado);
            
            if ($resultado) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Estado actualizado correctamente',
                    'nuevo_estado' => $nuevo_estado
                ]);
            } else {
                throw new Exception('No se pudo actualizar el estado');
            }
            break;
            
        case 'eliminar':
            $id = $_POST['id'] ?? 0;
            
            if (empty($id)) {
                throw new Exception('ID de producto no válido');
            }
            
            $resultado = $model->eliminarProducto($id);
            
            if ($resultado) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Producto eliminado correctamente'
                ]);
            } else {
                throw new Exception('No se pudo eliminar el producto');
            }
            break;
            
        case 'validar_codigo':
            $codigo = $_GET['codigo'] ?? '';
            $excluir_id = $_GET['excluir_id'] ?? null;
            
            if (empty($codigo)) {
                echo json_encode(['disponible' => false]);
                break;
            }
            
            $disponible = $model->codigoExiste($codigo, $excluir_id);
            echo json_encode(['disponible' => $disponible]);
            break;
            
        default:
            echo json_encode([
                'success' => false,
                'error' => 'Acción no válida: ' . $action
            ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>