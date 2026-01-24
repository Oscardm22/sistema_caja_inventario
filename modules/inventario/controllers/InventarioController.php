<?php
// modules/inventario/controllers/InventarioController.php

class InventarioController {
    private $model;
    private $tasaCache;
    
    public function __construct() {
        require_once __DIR__ . '/../models/InventarioModel.php';
        require_once __DIR__ . '/../cache_tasa.php';
        
        $this->model = new InventarioModel();
        $this->tasaCache = new TasaBCVCache();
    }
    
    public function indexAction() {
        // Obtener filtros
        $filtros = [
            'search' => $_GET['search'] ?? '',
            'categoria' => $_GET['categoria'] ?? '',
            'estado' => $_GET['estado'] ?? 'activo'
        ];
        
        // Obtener datos
        $productos = $this->model->getProductos($filtros);
        $categorias = $this->model->getCategorias();
        $stats = $this->model->getEstadisticas();
        
        // Obtener tasa BCV actual
        $tasa_bcv = $this->tasaCache->getTasa();
        
        // Calcular precios en BS para cada producto
        foreach ($productos as &$producto) {
            $producto['precio_bs'] = $this->calcularPrecioBS(
                $producto['precio_$'], 
                $tasa_bcv
            );
        }
        
        return [
            'productos' => $productos,
            'categorias' => $categorias,
            'estadisticas' => $stats,
            'filtros' => $filtros,
            'tasa_bcv' => $tasa_bcv
        ];
    }
    
    /**
     * Calcula el precio en bolívares
     */
    private function calcularPrecioBS($precio_usd, $tasa_bcv, $margen = 30) {
        $precio_base = $precio_usd * $tasa_bcv;
        $ganancia = $precio_base * ($margen / 100);
        return round($precio_base + $ganancia, 2);
    }
}
?>