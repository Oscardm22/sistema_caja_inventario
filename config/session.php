<?php
// Iniciar sesión solo si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar si el usuario está logueado
function checkAuth() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: ../auth/login.php');
        exit();
    }
}

// Verificar permisos según rol
function checkPermission($requiredRoles = []) {
    if (!isset($_SESSION['user_role'])) {
        header('Location: ../auth/login.php');
        exit();
    }
    
    // Si se especifica un array de roles permitidos
    if (!empty($requiredRoles) && !in_array($_SESSION['user_role'], $requiredRoles)) {
        header('Location: ../index.php');
        exit();
    }
}

// Función para verificar rol específico
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function isCajero() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'cajero';
}

function isAlmacen() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'almacen';
}

// Obtener información del usuario actual
function getCurrentUser() {
    if (isset($_SESSION['user_id'])) {
        return [
            'id' => $_SESSION['user_id'],
            'name' => $_SESSION['user_name'] ?? '',
            'email' => $_SESSION['user_email'] ?? '',
            'role' => $_SESSION['user_role'] ?? '',
        ];
    }
    return null;
}
?>