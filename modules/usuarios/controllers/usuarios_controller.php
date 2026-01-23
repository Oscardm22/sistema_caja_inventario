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
}
?>