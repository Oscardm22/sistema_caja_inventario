<?php
// modules/inventario/acciones_categorias.php
require_once '../../config/session.php';
require_once '../../config/database.php';
require_once 'models/InventarioModel.php';

// Solo admin puede gestionar categorías
checkPermission(['admin']);

// Verificar si es una solicitud AJAX (versión más flexible)
$is_ajax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') ||
           (!empty($_SERVER['HTTP_ACCEPT']) && 
           strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);

// Si no es AJAX, mostrar error pero permitir pruebas directas en desarrollo
if (!$is_ajax && !defined('DEBUG_MODE')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Acceso no permitido']);
    exit();
}

header('Content-Type: application/json');

// Obtener acción
$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    $model = new InventarioModel();
    
    switch ($action) {
        case 'crear':
            $nombre = trim($_POST['nombre'] ?? '');
            $descripcion = trim($_POST['descripcion'] ?? '');
            $estado = $_POST['estado'] ?? 'activa';
            
            if (empty($nombre)) {
                throw new Exception('El nombre de la categoría es requerido');
            }
            
            // Validar que no exista una categoría con el mismo nombre
            if ($model->categoriaExiste($nombre)) {
                throw new Exception('Ya existe una categoría con ese nombre');
            }
            
            $id = $model->crearCategoria($nombre, $descripcion, $estado);
            
            if ($id) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Categoría creada correctamente',
                    'id' => $id
                ]);
            } else {
                throw new Exception('Error al crear la categoría');
            }
            break;
            
        case 'editar':
            $id = (int)($_POST['id'] ?? 0);
            $nombre = trim($_POST['nombre'] ?? '');
            $descripcion = trim($_POST['descripcion'] ?? '');
            $estado = $_POST['estado'] ?? 'activa';
            
            if ($id <= 0 || empty($nombre)) {
                throw new Exception('Datos inválidos');
            }
            
            // CORRECCIÓN IMPORTANTE: La lógica estaba invertida
            // Validar que no exista otra categoría con el mismo nombre
            if ($model->categoriaExiste($nombre, $id)) {
                throw new Exception('Ya existe otra categoría con ese nombre');
            }
            
            $resultado = $model->actualizarCategoria($id, $nombre, $descripcion, $estado);
            
            if ($resultado) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Categoría actualizada correctamente'
                ]);
            } else {
                throw new Exception('Error al actualizar la categoría');
            }
            break;
            
        case 'obtener':
            $id = (int)($_GET['id'] ?? 0);
            
            if ($id <= 0) {
                throw new Exception('ID inválido');
            }
            
            $categoria = $model->getCategoriaById($id);
            
            if ($categoria) {
                echo json_encode([
                    'success' => true,
                    'categoria' => $categoria
                ]);
            } else {
                throw new Exception('Categoría no encontrada');
            }
            break;
            
        case 'cambiar_estado':
            $id = (int)($_POST['id'] ?? 0);
            $nuevo_estado = $_POST['estado'] ?? '';
            
            if ($id <= 0 || !in_array($nuevo_estado, ['activa', 'inactiva'])) {
                throw new Exception('Parámetros inválidos');
            }
            
            $resultado = $model->cambiarEstadoCategoria($id, $nuevo_estado);
            
            if ($resultado) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Estado actualizado correctamente',
                    'nuevo_estado' => $nuevo_estado
                ]);
            } else {
                throw new Exception('Error al cambiar el estado');
            }
            break;
            
        case 'eliminar':
            $id = (int)($_POST['id'] ?? 0);
            
            if ($id <= 0) {
                throw new Exception('ID inválido');
            }
            
            // Verificar que no tenga productos asociados
            $productos_count = $model->contarProductosPorCategoria($id);
            
            if ($productos_count > 0) {
                throw new Exception('No se puede eliminar la categoría porque tiene productos asociados');
            }
            
            $resultado = $model->eliminarCategoria($id);
            
            if ($resultado) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Categoría eliminada correctamente'
                ]);
            } else {
                throw new Exception('Error al eliminar la categoría');
            }
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