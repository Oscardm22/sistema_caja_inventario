<?php
// auth/login_processing.php - MODIFICADO PARA FLUJO DE DOS PASOS

$error = '';
$recovery_step = isset($_GET['recovery_step']) ? (int)$_GET['recovery_step'] : 0;

// Procesar login normal
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'] ?? '';
    
    // Verificar qué paso estamos procesando
    $recovery_mode = $_POST['recovery_mode'] ?? '';
    
    if ($recovery_mode === 'step1') {
        // PASO 1: Verificar usuario y redirigir al paso 2
        if (!empty($username)) {
            $db = getDB();
            
            // Verificar que el usuario sea administrador
            $stmt = $db->prepare("SELECT id, pregunta_seguridad_1, pregunta_seguridad_2 FROM usuarios WHERE username = ? AND rol = 'admin' AND estado = 'activo'");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                
                // Verificar si tiene preguntas configuradas
                if (!empty($user['pregunta_seguridad_1']) && !empty($user['pregunta_seguridad_2'])) {
                    // Redirigir al paso 2
                    header('Location: login.php?recovery_step=2&username=' . urlencode($username));
                    exit();
                } else {
                    $error = 'Este usuario no tiene configuradas preguntas de seguridad.';
                }
            } else {
                $error = 'Usuario no encontrado o no tiene permisos de administrador.';
            }
            $stmt->close();
        } else {
            $error = 'Por favor ingresa tu usuario.';
        }
    }
    elseif ($recovery_mode === 'step2') {
        // PASO 2: Verificar respuestas y cambiar contraseña
        $respuesta_1 = trim($_POST['respuesta_1'] ?? '');
        $respuesta_2 = trim($_POST['respuesta_2'] ?? '');
        $nueva_password = $_POST['nueva_password'] ?? '';
        $confirmar_password = $_POST['confirmar_password'] ?? '';
        $username = $_POST['username'] ?? '';
        
        if (!empty($username) && !empty($respuesta_1) && !empty($respuesta_2) && !empty($nueva_password) && !empty($confirmar_password)) {
            if ($nueva_password === $confirmar_password) {
                if (strlen($nueva_password) >= 6) {
                    $db = getDB();
                    
                    // Obtener respuestas guardadas
                    $stmt = $db->prepare("SELECT id, respuesta_seguridad_1, respuesta_seguridad_2 FROM usuarios WHERE username = ? AND rol = 'admin' AND estado = 'activo'");
                    $stmt->bind_param("s", $username);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    
                    if ($result->num_rows === 1) {
                        $user = $result->fetch_assoc();
                        
                        // Verificar respuestas (case-insensitive, sin espacios extras)
                        $respuesta_1_clean = trim(strtolower($respuesta_1));
                        $respuesta_2_clean = trim(strtolower($respuesta_2));
                        $respuesta_db_1 = trim(strtolower($user['respuesta_seguridad_1']));
                        $respuesta_db_2 = trim(strtolower($user['respuesta_seguridad_2']));
                        
                        if ($respuesta_1_clean === $respuesta_db_1 && $respuesta_2_clean === $respuesta_db_2) {
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
                            $error = 'Una o ambas respuestas de seguridad son incorrectas.';
                        }
                    } else {
                        $error = 'Usuario no encontrado.';
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
    }
    else {
        // MODO LOGIN NORMAL
        $password = $_POST['password'] ?? '';
        
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