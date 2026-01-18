<?php
// auth/login_processing.php

$error = '';
$recovery_mode = isset($_GET['recovery']) && $_GET['recovery'] === 'admin';

// Procesar login normal
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Verificar si es modo recuperación
    $is_recovery = isset($_POST['recovery_mode']) && $_POST['recovery_mode'] === 'true';
    
    if ($is_recovery) {
        // MODO RECUPERACIÓN DE ADMINISTRADOR
        $nombre_completo = $_POST['nombre_completo'] ?? '';
        $nueva_password = $_POST['nueva_password'] ?? '';
        $confirmar_password = $_POST['confirmar_password'] ?? '';
        
        if (!empty($username) && !empty($nombre_completo) && !empty($nueva_password) && !empty($confirmar_password)) {
            if ($nueva_password === $confirmar_password) {
                if (strlen($nueva_password) >= 6) {
                    $db = getDB();
                    
                    // Verificar que el usuario sea administrador
                    $stmt = $db->prepare("SELECT id, nombre, username, rol, estado FROM usuarios WHERE username = ? AND rol = 'admin' AND estado = 'activo'");
                    $stmt->bind_param("s", $username);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    
                    if ($result->num_rows === 1) {
                        $user = $result->fetch_assoc();
                        
                        // Verificar nombre completo (case-insensitive, sin espacios extras)
                        $nombre_bd = trim(strtolower($user['nombre']));
                        $nombre_ingresado = trim(strtolower($nombre_completo));
                        
                        if ($nombre_bd === $nombre_ingresado) {
                            // Actualizar contraseña
                            $hashed_password = password_hash($nueva_password, PASSWORD_DEFAULT);
                            $updateStmt = $db->prepare("UPDATE usuarios SET password = ? WHERE id = ?");
                            $updateStmt->bind_param("si", $hashed_password, $user['id']);
                            
                            if ($updateStmt->execute()) {
                                // Redirigir al login con mensaje de éxito
                                header('Location: login.php?success=admin_recovery');
                                exit();
                            } else {
                                $error = 'Error al actualizar la contraseña. Intenta nuevamente.';
                            }
                            $updateStmt->close();
                        } else {
                            $error = 'El nombre completo no coincide. Verifica mayúsculas, acentos y espacios.';
                        }
                    } else {
                        $error = 'Usuario no encontrado o no tiene permisos de administrador.';
                    }
                    $stmt->close();
                } else {
                    $error = 'La contraseña debe tener al menos 6 caracteres.';
                }
            } else {
                $error = 'Las contraseñas no coinciden.';
            }
        } else {
            $error = 'Por favor completa todos los campos.';
        }
    } else {
        // MODO LOGIN NORMAL
        if (!empty($username) && !empty($password)) {
            $db = getDB();
            
            $stmt = $db->prepare("SELECT id, nombre, username, password, rol, estado FROM usuarios WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                
                // Verificar contraseña
                if (password_verify($password, $user['password'])) {
                    if ($user['estado'] === 'activo') {
                        // Iniciar sesión
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['user_name'] = $user['nombre'];
                        $_SESSION['username'] = $user['username'];
                        $_SESSION['user_role'] = $user['rol'];
                        
                        // Actualizar último login
                        $updateStmt = $db->prepare("UPDATE usuarios SET ultimo_login = NOW() WHERE id = ?");
                        $updateStmt->bind_param("i", $user['id']);
                        $updateStmt->execute();
                        
                        // Redirigir según rol
                        header('Location: ../index.php');
                        exit();
                    } else {
                        $error = 'Tu cuenta está desactivada. Contacta al administrador.';
                    }
                } else {
                    $error = 'Credenciales incorrectas.';
                }
            } else {
                $error = 'Credenciales incorrectas.';
            }
            $stmt->close();
        } else {
            $error = 'Por favor ingresa nombre de usuario y contraseña.';
        }
    }
}

// Mensaje de éxito después de recuperación
$success = $_GET['success'] ?? '';
?>