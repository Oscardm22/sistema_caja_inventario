<?php
// modules/inventario/funciones_precios.php

/**
 * Calcula el precio en bolívares basado en la tasa BCV
 */
function calcularPrecioBS($precio_usd, $tasa_bcv = null, $margen = 30) {
    if ($tasa_bcv === null) {
        require_once __DIR__ . '/cache_tasa.php';
        $tasaCache = new TasaBCVCache();
        $tasa_bcv = $tasaCache->getTasa();
    }
    
    $precio_base = $precio_usd * $tasa_bcv;
    $ganancia = $precio_base * ($margen / 100);
    return round($precio_base + $ganancia, 2);
}

/**
 * Formatea un precio en bolívares
 */
function formatoPrecioBS($precio) {
    return 'Bs. ' . number_format($precio, 2, ',', '.');
}

/**
 * Formatea un precio en dólares
 */
function formatoPrecioUSD($precio) {
    return '$' . number_format($precio, 2, '.', ',');
}

/**
 * Calcula el margen de ganancia
 */
function calcularMargen($precio_costo, $precio_venta) {
    if ($precio_costo <= 0) return 0;
    $ganancia = $precio_venta - $precio_costo;
    return round(($ganancia / $precio_costo) * 100, 2);
}
?>