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
}
?>