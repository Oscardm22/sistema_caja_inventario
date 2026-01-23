<?php
require_once '../../config/session.php';
require_once '../../config/database.php';

// Solo admin puede acceder
checkPermission(['admin']);

// Solo aceptar peticiones POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit();
}

header('Content-Type: application/json');

$database = Database::getInstance();
$conn = $database->getConnection();

// Obtener acción
$action = $_POST['action'] ?? '';
$userId = isset($_POST['id']) ? intval($_POST['id']) : 0;

if ($userId <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID de usuario inválido']);
    exit();
}

// Verificar que el usuario existe
$query = "SELECT rol FROM usuarios WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();
$stmt->close();

if (!$usuario) {
    echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
    exit();
}

switch ($action) {
    case 'cambiar_estado':
        $nuevoEstado = $_POST['estado'] ?? '';
        
        if (!in_array($nuevoEstado, ['activo', 'inactivo'])) {
            echo json_encode(['success' => false, 'message' => 'Estado inválido']);
            exit();
        }
        
        // Verificar si no es el último admin
        if ($usuario['rol'] === 'admin' && $nuevoEstado === 'inactivo') {
            $query = "SELECT COUNT(*) as total_admins FROM usuarios WHERE rol = 'admin' AND estado = 'activo' AND id != ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $admins = $result->fetch_assoc();
            $stmt->close();
            
            if ($admins['total_admins'] == 0) {
                echo json_encode(['success' => false, 'message' => 'No puedes desactivar al único administrador activo']);
                exit();
            }
        }
        
        // Cambiar estado
        $query = "UPDATE usuarios SET estado = ?, fecha_actualizacion = CURRENT_TIMESTAMP WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("si", $nuevoEstado, $userId);
        
        if ($stmt->execute()) {
            $estadoTexto = $nuevoEstado === 'activo' ? 'activado' : 'desactivado';
            echo json_encode(['success' => true, 'message' => 'Usuario ' . $estadoTexto . ' exitosamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al cambiar estado: ' . $conn->error]);
        }
        $stmt->close();
        break;
        
    case 'eliminar':
        // Verificar si no es el último admin
        if ($usuario['rol'] === 'admin') {
            $query = "SELECT COUNT(*) as total_admins FROM usuarios WHERE rol = 'admin' AND id != ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $admins = $result->fetch_assoc();
            $stmt->close();
            
            if ($admins['total_admins'] == 0) {
                echo json_encode(['success' => false, 'message' => 'No puedes eliminar al único administrador']);
                exit();
            }
        }
        
        // Verificar si tiene cajas abiertas
        $query = "SELECT COUNT(*) as cajas_abiertas FROM caja WHERE usuario_id = ? AND estado = 'abierta'";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $cajas = $result->fetch_assoc();
        $stmt->close();
        
        if ($cajas['cajas_abiertas'] > 0) {
            echo json_encode(['success' => false, 'message' => 'No puedes eliminar un usuario con caja abierta']);
            exit();
        }
        
        // Eliminar usuario (usar DELETE en lugar de estado inactivo)
        $query = "DELETE FROM usuarios WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $userId);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Usuario eliminado exitosamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al eliminar usuario: ' . $conn->error]);
        }
        $stmt->close();
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Acción no válida']);
        break;
}
?>