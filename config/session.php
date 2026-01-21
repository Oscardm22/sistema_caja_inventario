<?php

// Iniciar sesión solo si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Obtener la URL base dinámicamente
function base_url($path = '') {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'];
    $script_name = dirname($_SERVER['SCRIPT_NAME']);
    
    // Si estamos en la raíz, no agregar el directorio
    $base = ($script_name === '/' || $script_name === '\\') ? '' : $script_name;
    
    return $protocol . $host . $base . '/' . ltrim($path, '/');
}

// Verificar si el usuario está logueado
function checkAuth() {
    if (!isset($_SESSION['user_id'])) {
        // Usar ruta absoluta
        $login_url = base_url('auth/login.php');
        header('Location: ' . $login_url);
        exit();
    }
}

// Verificar permisos según rol
function checkPermission($requiredRoles = []) {
    if (!isset($_SESSION['user_role'])) {
        $login_url = base_url('auth/login.php');
        header('Location: ' . $login_url);
        exit();
    }
    
    // Si se especifica un array de roles permitidos
    if (!empty($requiredRoles) && !in_array($_SESSION['user_role'], $requiredRoles)) {
        header('Location: ' . base_url('index.php'));
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