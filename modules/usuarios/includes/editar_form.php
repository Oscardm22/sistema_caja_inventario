<?php
// Extraer datos
$nombre = $formData['nombre'] ?? '';
$username = $formData['username'] ?? '';
$rol = $formData['rol'] ?? 'cajero';
$estado = $formData['estado'] ?? 'activo';
$pregunta1 = $formData['pregunta1'] ?? '';
$respuesta1 = $formData['respuesta1'] ?? '';
$pregunta2 = $formData['pregunta2'] ?? '';
$respuesta2 = $formData['respuesta2'] ?? '';
?>

<!-- Formulario -->
<div class="bg-white p-6 rounded-lg shadow">
    <form method="POST" action="" class="space-y-6" id="editar-usuario-form">
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