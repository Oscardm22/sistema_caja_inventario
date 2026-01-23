<?php
class UsuariosModel {
    private $conn;
    
    public function __construct($connection) {
        $this->conn = $connection;
    }
    
    public function getUsuarios($filtros = []) {
        $query = "SELECT id, nombre, username, rol, estado, ultimo_login, fecha_creacion 
                  FROM usuarios 
                  WHERE 1=1";
        $params = [];
        $types = '';
        
        // Aplicar filtros
        if (!empty($filtros['search'])) {
            $query .= " AND (nombre LIKE ? OR username LIKE ?)";
            $search_param = "%{$filtros['search']}%";
            $params[] = $search_param;
            $params[] = $search_param;
            $types .= 'ss';
        }
        
        if (!empty($filtros['role'])) {
            $query .= " AND rol = ?";
            $params[] = $filtros['role'];
            $types .= 's';
        }
        
        if (!empty($filtros['status'])) {
            $query .= " AND estado = ?";
            $params[] = $filtros['status'];
            $types .= 's';
        }
        
        $query .= " ORDER BY fecha_creacion DESC";
        
        // Usar el Database helper si está disponible
        $database = Database::getInstance();
        $stmt = $database->executeQuery($query, $params, $types);
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    public function getEstadisticas() {
        $query = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN estado = 'activo' THEN 1 ELSE 0 END) as activos,
                    SUM(CASE WHEN estado = 'inactivo' THEN 1 ELSE 0 END) as inactivos,
                    SUM(CASE WHEN rol = 'admin' THEN 1 ELSE 0 END) as admins,
                    SUM(CASE WHEN rol = 'cajero' THEN 1 ELSE 0 END) as cajeros
                  FROM usuarios";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        return $result;
    }
    
    public function cambiarEstado($id, $nuevoEstado) {
        $query = "UPDATE usuarios SET estado = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('si', $nuevoEstado, $id);
        $success = $stmt->execute();
        $stmt->close();
        
        return $success;
    }
    
    public function eliminarUsuario($id) {
        $query = "DELETE FROM usuarios WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $id);
        $success = $stmt->execute();
        $stmt->close();
        
        return $success;
    }

    public function getUsuarioById($id) {
        $query = "SELECT id, nombre, username, rol, estado, 
                        pregunta_seguridad_1, respuesta_seguridad_1,
                        pregunta_seguridad_2, respuesta_seguridad_2
                FROM usuarios WHERE id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $usuario = $result->fetch_assoc();
        $stmt->close();
        
        return $usuario;
    }

    public function usernameExiste($username, $excludeId = null) {
        if ($excludeId) {
            $query = "SELECT id FROM usuarios WHERE username = ? AND id != ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("si", $username, $excludeId);
        } else {
            $query = "SELECT id FROM usuarios WHERE username = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("s", $username);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        $existe = $result->num_rows > 0;
        $stmt->close();
        
        return $existe;
    }

    public function countAdminsExcluding($excludeId) {
        $query = "SELECT COUNT(*) as total FROM usuarios WHERE rol = 'admin' AND id != ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $excludeId);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        return $result['total'];
    }

    public function actualizarUsuario($id, $data) {
        $password_changed = !empty($data['password']);
        
        if ($password_changed) {
            $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);
            $query = "UPDATE usuarios SET 
                    nombre = ?, username = ?, password = ?, rol = ?, estado = ?,
                    pregunta_seguridad_1 = ?, respuesta_seguridad_1 = ?,
                    pregunta_seguridad_2 = ?, respuesta_seguridad_2 = ?,
                    fecha_actualizacion = CURRENT_TIMESTAMP
                    WHERE id = ?";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("sssssssssi", 
                $data['nombre'], $data['username'], $hashed_password, $data['rol'], $data['estado'],
                $data['pregunta1'], $data['respuesta1'], $data['pregunta2'], $data['respuesta2'],
                $id
            );
        } else {
            $query = "UPDATE usuarios SET 
                    nombre = ?, username = ?, rol = ?, estado = ?,
                    pregunta_seguridad_1 = ?, respuesta_seguridad_1 = ?,
                    pregunta_seguridad_2 = ?, respuesta_seguridad_2 = ?,
                    fecha_actualizacion = CURRENT_TIMESTAMP
                    WHERE id = ?";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("ssssssssi", 
                $data['nombre'], $data['username'], $data['rol'], $data['estado'],
                $data['pregunta1'], $data['respuesta1'], $data['pregunta2'], $data['respuesta2'],
                $id
            );
        }
        
        $success = $stmt->execute();
        $stmt->close();
        
        return $success;
    }
    
    public function crearUsuario($data) {
        $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);
        
        $query = "INSERT INTO usuarios (nombre, username, password, rol, estado, 
                pregunta_seguridad_1, respuesta_seguridad_1, 
                pregunta_seguridad_2, respuesta_seguridad_2) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("sssssssss", 
            $data['nombre'], $data['username'], $hashed_password, $data['rol'], $data['estado'],
            $data['pregunta1'], $data['respuesta1'], $data['pregunta2'], $data['respuesta2']
        );
        
        $success = $stmt->execute();
        $stmt->close();
        
        return $success;
    }
}
?>