<?php
require_once '../../config/session.php';
require_once '../../config/database.php';

// Solo admin puede acceder
checkPermission(['admin']);

// Solo aceptar peticiones POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error_message'] = 'Método no permitido';
    header('Location: index.php');
    exit();
}

// Verificar que se proporcionó un ID
if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    $_SESSION['error_message'] = 'ID de usuario inválido';
    header('Location: index.php');
    exit();
}

$database = Database::getInstance();
$conn = $database->getConnection();
$userId = intval($_POST['id']);

// Verificar que el usuario existe
$query = "SELECT rol FROM usuarios WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();
$stmt->close();

if (!$usuario) {
    $_SESSION['error_message'] = 'Usuario no encontrado';
    header('Location: index.php');
    exit();
}

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
        $_SESSION['error_message'] = 'No puedes eliminar al único administrador';
        header('Location: index.php');
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
    $_SESSION['error_message'] = 'No puedes eliminar un usuario con caja abierta';
    header('Location: index.php');
    exit();
}

// Eliminar usuario
$query = "DELETE FROM usuarios WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);

if ($stmt->execute()) {
    $_SESSION['success_message'] = 'Usuario eliminado exitosamente';
} else {
    $_SESSION['error_message'] = 'Error al eliminar usuario: ' . $conn->error;
}

$stmt->close();
header('Location: index.php');
exit();
?>