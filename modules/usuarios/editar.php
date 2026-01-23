<?php
require_once '../../config/session.php';
require_once '../../config/database.php';

// Solo admin puede acceder
checkPermission(['admin']);

// Verificar que se proporcionó un ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$pageTitle = "Editar Usuario";
require_once '../../includes/header.php';

$database = Database::getInstance();
$conn = $database->getConnection();
$userId = intval($_GET['id']);

// Cargar datos del usuario
$query = "SELECT id, nombre, username, rol, estado, 
                 pregunta_seguridad_1, respuesta_seguridad_1,
                 pregunta_seguridad_2, respuesta_seguridad_2
          FROM usuarios WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();
$stmt->close();

// Verificar si el usuario existe
if (!$usuario) {
    $_SESSION['error_message'] = 'Usuario no encontrado';
    header('Location: index.php');
    exit();
}

// Cargar preguntas de seguridad
require_once '../../auth/security_questions.php';

// Variables para el formulario
$nombre = $usuario['nombre'];
$username = $usuario['username'];
$rol = $usuario['rol'];
$estado = $usuario['estado'];
$pregunta1 = $usuario['pregunta_seguridad_1'];
$respuesta1 = $usuario['respuesta_seguridad_1'];
$pregunta2 = $usuario['pregunta_seguridad_2'];
$respuesta2 = $usuario['respuesta_seguridad_2'];
$password_changed = false;
$errors = [];

// Procesar formulario si se envió
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener datos del formulario
    $nombre = trim($_POST['nombre'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $rol = $_POST['rol'] ?? 'cajero';
    $estado = $_POST['estado'] ?? 'activo';
    $pregunta1 = trim($_POST['pregunta_seguridad_1'] ?? '');
    $respuesta1 = trim($_POST['respuesta_seguridad_1'] ?? '');
    $pregunta2 = trim($_POST['pregunta_seguridad_2'] ?? '');
    $respuesta2 = trim($_POST['respuesta_seguridad_2'] ?? '');
    $password_changed = !empty($password);
    
    // Validaciones
    if (empty($nombre)) {
        $errors['nombre'] = 'El nombre es obligatorio';
    } elseif (strlen($nombre) < 3) {
        $errors['nombre'] = 'El nombre debe tener al menos 3 caracteres';
    }
    
    if (empty($username)) {
        $errors['username'] = 'El nombre de usuario es obligatorio';
    } elseif (strlen($username) < 3) {
        $errors['username'] = 'El usuario debe tener al menos 3 caracteres';
    } else {
        // Verificar si el usuario ya existe (excluyendo el actual)
        $query = "SELECT id FROM usuarios WHERE username = ? AND id != ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("si", $username, $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $errors['username'] = 'Este nombre de usuario ya está registrado';
        }
        $stmt->close();
    }
    
    if ($password_changed) {
        if (strlen($password) < 6) {
            $errors['password'] = 'La contraseña debe tener al menos 6 caracteres';
        }
        
        if ($password !== $confirm_password) {
            $errors['confirm_password'] = 'Las contraseñas no coinciden';
        }
    }
    
    if (empty($pregunta1) || empty($respuesta1)) {
        $errors['pregunta1'] = 'La primera pregunta de seguridad es obligatoria';
    }
    
    if (empty($pregunta2) || empty($respuesta2)) {
        $errors['pregunta2'] = 'La segunda pregunta de seguridad es obligatoria';
    }
    
    // Verificar si no es el último admin
    if ($usuario['rol'] === 'admin' && $rol !== 'admin') {
        $query = "SELECT COUNT(*) as total_admins FROM usuarios WHERE rol = 'admin' AND id != ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $admins = $result->fetch_assoc();
        $stmt->close();
        
        if ($admins['total_admins'] == 0) {
            $errors['rol'] = 'No puedes cambiar el rol del único administrador';
        }
    }
    
    // Si no hay errores, actualizar el usuario
    if (empty($errors)) {
        if ($password_changed) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $query = "UPDATE usuarios SET 
                      nombre = ?, username = ?, password = ?, rol = ?, estado = ?,
                      pregunta_seguridad_1 = ?, respuesta_seguridad_1 = ?,
                      pregunta_seguridad_2 = ?, respuesta_seguridad_2 = ?,
                      fecha_actualizacion = CURRENT_TIMESTAMP
                      WHERE id = ?";
            
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sssssssssi", 
                $nombre, $username, $hashed_password, $rol, $estado,
                $pregunta1, $respuesta1, $pregunta2, $respuesta2,
                $userId
            );
        } else {
            $query = "UPDATE usuarios SET 
                      nombre = ?, username = ?, rol = ?, estado = ?,
                      pregunta_seguridad_1 = ?, respuesta_seguridad_1 = ?,
                      pregunta_seguridad_2 = ?, respuesta_seguridad_2 = ?,
                      fecha_actualizacion = CURRENT_TIMESTAMP
                      WHERE id = ?";
            
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ssssssssi", 
                $nombre, $username, $rol, $estado,
                $pregunta1, $respuesta1, $pregunta2, $respuesta2,
                $userId
            );
        }
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = 'Usuario actualizado exitosamente';
            header('Location: index.php');
            exit();
        } else {
            $errors['general'] = 'Error al actualizar el usuario: ' . $conn->error;
        }
        $stmt->close();
    }
}
?>

<!-- Contenedor principal -->
<div class="flex">
    <!-- Sidebar -->
    <?php require_once '../../includes/sidebar.php'; ?>
    
    <!-- Contenido principal -->
    <main class="ml-64 flex-1 p-6 min-h-screen">
        <!-- Encabezado -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Editar Usuario</h1>
            <a href="index.php" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>Volver al Listado
            </a>
        </div>
        
        <!-- Mensajes de error -->
        <?php if (!empty($errors['general'])): ?>
        <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-triangle text-red-400"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-red-700"><?php echo htmlspecialchars($errors['general']); ?></p>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Formulario -->
        <div class="bg-white p-6 rounded-lg shadow">
            <form method="POST" action="" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Información Básica -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-medium text-gray-900 border-b pb-2">
                            <i class="fas fa-user-circle mr-2"></i>Información Básica
                        </h3>
                        
                        <!-- Nombre -->
                        <div>
                            <label for="nombre" class="block text-sm font-medium text-gray-700 mb-1">
                                Nombre Completo <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="nombre" name="nombre" 
                                   value="<?php echo htmlspecialchars($nombre); ?>"
                                   class="w-full px-3 py-2 border <?php echo isset($errors['nombre']) ? 'border-red-500' : 'border-gray-300'; ?> rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                   placeholder="Ej: Juan Pérez">
                            <?php if (isset($errors['nombre'])): ?>
                            <p class="mt-1 text-sm text-red-600"><?php echo $errors['nombre']; ?></p>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Username -->
                        <div>
                            <label for="username" class="block text-sm font-medium text-gray-700 mb-1">
                                Nombre de Usuario <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="username" name="username" 
                                   value="<?php echo htmlspecialchars($username); ?>"
                                   class="w-full px-3 py-2 border <?php echo isset($errors['username']) ? 'border-red-500' : 'border-gray-300'; ?> rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                   placeholder="Ej: juan.perez">
                            <?php if (isset($errors['username'])): ?>
                            <p class="mt-1 text-sm text-red-600"><?php echo $errors['username']; ?></p>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Contraseña (opcional) -->
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                                Nueva Contraseña <small class="text-gray-500">(dejar vacío para no cambiar)</small>
                            </label>
                            <input type="password" id="password" name="password"
                                   class="w-full px-3 py-2 border <?php echo isset($errors['password']) ? 'border-red-500' : 'border-gray-300'; ?> rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                   placeholder="Dejar vacío para no cambiar">
                            <?php if (isset($errors['password'])): ?>
                            <p class="mt-1 text-sm text-red-600"><?php echo $errors['password']; ?></p>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Confirmar Contraseña -->
                        <div>
                            <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1">
                                Confirmar Nueva Contraseña
                            </label>
                            <input type="password" id="confirm_password" name="confirm_password"
                                   class="w-full px-3 py-2 border <?php echo isset($errors['confirm_password']) ? 'border-red-500' : 'border-gray-300'; ?> rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                   placeholder="Solo si cambias la contraseña">
                            <?php if (isset($errors['confirm_password'])): ?>
                            <p class="mt-1 text-sm text-red-600"><?php echo $errors['confirm_password']; ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Rol, Estado y Seguridad -->
                    <div class="space-y-4">
                        <!-- Rol y Estado -->
                        <div class="grid grid-cols-2 gap-4">
                            <!-- Rol -->
                            <div>
                                <label for="rol" class="block text-sm font-medium text-gray-700 mb-1">
                                    Rol <span class="text-red-500">*</span>
                                </label>
                                <select id="rol" name="rol"
                                        class="w-full px-3 py-2 border <?php echo isset($errors['rol']) ? 'border-red-500' : 'border-gray-300'; ?> rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="cajero" <?php echo $rol === 'cajero' ? 'selected' : ''; ?>>Cajero</option>
                                    <option value="admin" <?php echo $rol === 'admin' ? 'selected' : ''; ?>>Administrador</option>
                                </select>
                                <?php if (isset($errors['rol'])): ?>
                                <p class="mt-1 text-sm text-red-600"><?php echo $errors['rol']; ?></p>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Estado -->
                            <div>
                                <label for="estado" class="block text-sm font-medium text-gray-700 mb-1">
                                    Estado <span class="text-red-500">*</span>
                                </label>
                                <select id="estado" name="estado"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="activo" <?php echo $estado === 'activo' ? 'selected' : ''; ?>>Activo</option>
                                    <option value="inactivo" <?php echo $estado === 'inactivo' ? 'selected' : ''; ?>>Inactivo</option>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Preguntas de Seguridad -->
                        <div class="space-y-4 mt-6">
                            <h3 class="text-lg font-medium text-gray-900 border-b pb-2">
                                <i class="fas fa-shield-alt mr-2"></i>Preguntas de Seguridad
                            </h3>
                            
                            <!-- Pregunta 1 -->
                            <div>
                                <label for="pregunta_seguridad_1" class="block text-sm font-medium text-gray-700 mb-1">
                                    Pregunta de Seguridad 1 <span class="text-red-500">*</span>
                                </label>
                                <select id="pregunta_seguridad_1" name="pregunta_seguridad_1"
                                        class="w-full px-3 py-2 border <?php echo isset($errors['pregunta1']) ? 'border-red-500' : 'border-gray-300'; ?> rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">Selecciona una pregunta</option>
                                    <?php foreach ($security_questions as $question): ?>
                                    <option value="<?php echo htmlspecialchars($question); ?>" 
                                        <?php echo $pregunta1 === $question ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($question); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <input type="text" name="respuesta_seguridad_1" 
                                       value="<?php echo htmlspecialchars($respuesta1); ?>"
                                       class="w-full px-3 py-2 border <?php echo isset($errors['pregunta1']) ? 'border-red-500' : 'border-gray-300'; ?> rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 mt-2"
                                       placeholder="Respuesta">
                                <?php if (isset($errors['pregunta1'])): ?>
                                <p class="mt-1 text-sm text-red-600"><?php echo $errors['pregunta1']; ?></p>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Pregunta 2 -->
                            <div>
                                <label for="pregunta_seguridad_2" class="block text-sm font-medium text-gray-700 mb-1">
                                    Pregunta de Seguridad 2 <span class="text-red-500">*</span>
                                </label>
                                <select id="pregunta_seguridad_2" name="pregunta_seguridad_2"
                                        class="w-full px-3 py-2 border <?php echo isset($errors['pregunta2']) ? 'border-red-500' : 'border-gray-300'; ?> rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">Selecciona una pregunta</option>
                                    <?php foreach ($security_questions as $question): ?>
                                    <option value="<?php echo htmlspecialchars($question); ?>" 
                                        <?php echo $pregunta2 === $question ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($question); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <input type="text" name="respuesta_seguridad_2" 
                                       value="<?php echo htmlspecialchars($respuesta2); ?>"
                                       class="w-full px-3 py-2 border <?php echo isset($errors['pregunta2']) ? 'border-red-500' : 'border-gray-300'; ?> rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 mt-2"
                                       placeholder="Respuesta">
                                <?php if (isset($errors['pregunta2'])): ?>
                                <p class="mt-1 text-sm text-red-600"><?php echo $errors['pregunta2']; ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Botones -->
                <div class="flex justify-end space-x-4 pt-6 border-t">
                    <a href="index.php" class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition-colors">
                        Cancelar
                    </a>
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
                        <i class="fas fa-save mr-2"></i>Actualizar Usuario
                    </button>
                </div>
            </form>
        </div>
    </main>
</div>

<!-- JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-focus en el primer campo
    document.getElementById('nombre').focus();
    
    // Mostrar/ocultar contraseñas
    const togglePassword = (inputId) => {
        const input = document.getElementById(inputId);
        const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
        input.setAttribute('type', type);
    };
    
    // Agregar botones para mostrar contraseña
    const passwordFields = ['password', 'confirm_password'];
    passwordFields.forEach(fieldId => {
        const field = document.getElementById(fieldId);
        const parent = field.parentElement;
        parent.classList.add('relative');
        
        const toggleBtn = document.createElement('button');
        toggleBtn.type = 'button';
        toggleBtn.innerHTML = '<i class="fas fa-eye"></i>';
        toggleBtn.className = 'absolute right-3 top-9 text-gray-500 hover:text-gray-700';
        toggleBtn.onclick = () => togglePassword(fieldId);
        
        parent.appendChild(toggleBtn);
    });
});
</script>

<?php require_once '../../includes/footer.php'; ?>