<?php
require_once 'models/usuarios_model.php';

class UsuariosController {
    private $model;
    
    public function __construct() {
        $database = Database::getInstance();
        $conn = $database->getConnection();
        $this->model = new UsuariosModel($conn);
    }
    
    public function indexAction() {
        // Obtener filtros del GET
        $filtros = [
            'search' => $_GET['search'] ?? '',
            'role' => $_GET['role'] ?? '',
            'status' => $_GET['status'] ?? ''
        ];
        
        // Obtener datos del modelo
        $usuarios = $this->model->getUsuarios($filtros);
        $estadisticas = $this->model->getEstadisticas();
        
        return [
            'usuarios' => $usuarios,
            'estadisticas' => $estadisticas,
            'filtros' => $filtros
        ];
    }

    public function editarAction() {
        // Verificar que se proporcionó un ID
        if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
            header('Location: index.php');
            exit();
        }
        
        $userId = intval($_GET['id']);
        
        // Cargar datos del usuario
        $usuario = $this->model->getUsuarioById($userId);
        
        // Verificar si el usuario existe
        if (!$usuario) {
            $_SESSION['error_message'] = 'Usuario no encontrado';
            header('Location: index.php');
            exit();
        }
        
        // Cargar preguntas de seguridad
        require_once '../../auth/security_questions.php';
        
        $formData = [
            'nombre' => $usuario['nombre'],
            'username' => $usuario['username'],
            'rol' => $usuario['rol'],
            'estado' => $usuario['estado'],
            'pregunta1' => $usuario['pregunta_seguridad_1'],
            'respuesta1' => $usuario['respuesta_seguridad_1'],
            'pregunta2' => $usuario['pregunta_seguridad_2'],
            'respuesta2' => $usuario['respuesta_seguridad_2']
        ];
        
        $errors = [];
        
        // Procesar formulario si se envió
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $result = $this->procesarEdicion($userId, $usuario);
            
            if (isset($result['success']) && $result['success']) {
                $_SESSION['success_message'] = 'Usuario actualizado exitosamente';
                header('Location: index.php');
                exit();
            }
            
            $errors = $result['errors'] ?? [];
            $formData = $result['formData'] ?? $formData;
        }
        
        return [
            'usuario' => $usuario,
            'errors' => $errors,
            'security_questions' => $security_questions,
            'formData' => $formData
        ];
    }

    private function procesarEdicion($userId, $usuarioActual) {
        $formData = [
            'nombre' => trim($_POST['nombre'] ?? ''),
            'username' => trim($_POST['username'] ?? ''),
            'password' => $_POST['password'] ?? '',
            'confirm_password' => $_POST['confirm_password'] ?? '',
            'rol' => $_POST['rol'] ?? 'cajero',
            'estado' => $_POST['estado'] ?? 'activo',
            'pregunta1' => trim($_POST['pregunta_seguridad_1'] ?? ''),
            'respuesta1' => trim($_POST['respuesta_seguridad_1'] ?? ''),
            'pregunta2' => trim($_POST['pregunta_seguridad_2'] ?? ''),
            'respuesta2' => trim($_POST['respuesta_seguridad_2'] ?? '')
        ];
        
        $errors = [];
        
        // Validaciones
        $errors = array_merge($errors, $this->validarDatosUsuario($formData, $userId));
        
        // Verificar si no es el último admin
        if ($usuarioActual['rol'] === 'admin' && $formData['rol'] !== 'admin') {
            $adminsCount = $this->model->countAdminsExcluding($userId);
            if ($adminsCount == 0) {
                $errors['rol'] = 'No puedes cambiar el rol del único administrador';
            }
        }
        
        // Si no hay errores, actualizar el usuario
        if (empty($errors)) {
            $success = $this->model->actualizarUsuario($userId, $formData);
            
            if ($success) {
                return ['success' => true];
            } else {
                $errors['general'] = 'Error al actualizar el usuario';
            }
        }
        
        return [
            'errors' => $errors,
            'formData' => $formData
        ];
    }

    private function validarDatosUsuario($data, $userId) {
        $errors = [];
        
        if (empty($data['nombre'])) {
            $errors['nombre'] = 'El nombre es obligatorio';
        } elseif (strlen($data['nombre']) < 3) {
            $errors['nombre'] = 'El nombre debe tener al menos 3 caracteres';
        }
        
        if (empty($data['username'])) {
            $errors['username'] = 'El nombre de usuario es obligatorio';
        } elseif (strlen($data['username']) < 3) {
            $errors['username'] = 'El usuario debe tener al menos 3 caracteres';
        } else {
            if ($this->model->usernameExiste($data['username'], $userId)) {
                $errors['username'] = 'Este nombre de usuario ya está registrado';
            }
        }
        
        if (!empty($data['password'])) {
            if (strlen($data['password']) < 6) {
                $errors['password'] = 'La contraseña debe tener al menos 6 caracteres';
            }
            
            if ($data['password'] !== $data['confirm_password']) {
                $errors['confirm_password'] = 'Las contraseñas no coinciden';
            }
        }
        
        if (empty($data['pregunta1']) || empty($data['respuesta1'])) {
            $errors['pregunta1'] = 'La primera pregunta de seguridad es obligatoria';
        }
        
        if (empty($data['pregunta2']) || empty($data['respuesta2'])) {
            $errors['pregunta2'] = 'La segunda pregunta de seguridad es obligatoria';
        }
        
        return $errors;
    }
}
?>