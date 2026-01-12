<?php
// Mostrar errores solo en desarrollo
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

define('DB_HOST', '127.0.0.1:3307');
define('DB_NAME', 'sistema_caja_inventario');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DEBUG_MODE', false);

class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        try {
            $this->connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            
            if ($this->connection->connect_error) {
                throw new Exception("Error de conexión: " . $this->connection->connect_error);
            }
            
            $this->connection->set_charset("utf8mb4");
            
        } catch (Exception $e) {
            if (DEBUG_MODE) {
                die("Error de conexión a la base de datos: " . $e->getMessage());
            } else {
                die("Error del sistema. Por favor, contacte al administrador.");
            }
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    // Método para ejecutar consultas preparadas
    public function executeQuery($sql, $params = [], $types = '') {
        try {
            $stmt = $this->connection->prepare($sql);
            if (!$stmt) {
                throw new Exception("Error en la preparación de la consulta: " . $this->connection->error);
            }
            
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            
            if (!$stmt->execute()) {
                throw new Exception("Error al ejecutar la consulta: " . $stmt->error);
            }
            
            return $stmt;
        } catch (Exception $e) {
            if (DEBUG_MODE) {
                die("Error en la consulta: " . $e->getMessage());
            } else {
                die("Error del sistema. Por favor, contacte al administrador.");
            }
        }
    }
}

function getDB() {
    return Database::getInstance()->getConnection();
}

function getDatabase() {
    return Database::getInstance();
}
?>