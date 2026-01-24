<?php
// modules/inventario/cache_tasa.php

class TasaBCVCache {
    private $db;
    private $cache_time = 3600; // 1 hora en segundos
    private $api_url = 'https://api.dolarvzla.com/public/exchange-rate';
    
    public function __construct() {
        $database = Database::getInstance();
        $this->db = $database->getConnection();
    }
    
    /**
     * Obtiene la tasa BCV actual (desde cache o API)
     */
    public function getTasa() {
        // Primero intentar obtener del cache
        $cached_rate = $this->getFromCache();
        
        if ($cached_rate && !$this->isCacheExpired($cached_rate)) {
            return (float) $cached_rate['tasa_usd'];
        }
        
        // Si el cache está expirado o no existe, obtener de la API
        return $this->fetchFromAPI();
    }
    
    /**
     * Obtiene la tasa desde el cache de la base de datos
     */
    private function getFromCache() {
        $sql = "SELECT * FROM tasa_bcv_cache 
                ORDER BY fecha_consulta DESC 
                LIMIT 1";
        
        try {
            $result = $this->db->query($sql);
            return $result->fetch_assoc();
        } catch (Exception $e) {
            error_log("Error al obtener tasa desde cache: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Verifica si el cache está expirado
     */
    private function isCacheExpired($cache_data) {
        if (!$cache_data) {
            return true;
        }
        
        $cache_time = strtotime($cache_data['fecha_consulta']);
        $current_time = time();
        $elapsed_time = $current_time - $cache_time;
        
        return $elapsed_time > $this->cache_time;
    }
    
    /**
     * Obtiene la tasa desde la API externa
     */
    private function fetchFromAPI() {
        try {
            // Usar cURL para llamar a la API
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->api_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            
            if (curl_errno($ch)) {
                error_log("cURL Error: " . curl_error($ch));
                curl_close($ch);
                return $this->getFallbackRate();
            }
            
            curl_close($ch);
            
            if ($http_code !== 200) {
                error_log("API responded with code: " . $http_code);
                return $this->getFallbackRate();
            }
            
            $data = json_decode($response, true);
            
            if (!$data || !isset($data['current']['usd'])) {
                error_log("Invalid API response format");
                return $this->getFallbackRate();
            }
            
            $tasa_usd = (float) $data['current']['usd'];
            $tasa_eur = isset($data['current']['eur']) ? (float) $data['current']['eur'] : null;
            $fecha_actualizacion = isset($data['current']['date']) ? $data['current']['date'] : date('Y-m-d');
            
            // Guardar en cache
            $this->saveToCache($tasa_usd, $tasa_eur, $fecha_actualizacion);
            
            return $tasa_usd;
            
        } catch (Exception $e) {
            error_log("Error fetching from API: " . $e->getMessage());
            return $this->getFallbackRate();
        }
    }
    
    /**
     * Guarda la tasa en el cache
     */
    private function saveToCache($tasa_usd, $tasa_eur, $fecha_actualizacion) {
        // Si tasa_eur es null, usar un valor por defecto
        if ($tasa_eur === null) {
            $tasa_eur = 0.0;
        }
        
        $sql = "INSERT INTO tasa_bcv_cache 
                (tasa_usd, tasa_eur, fecha_consulta, fecha_actualizacion, fuente) 
                VALUES (?, ?, NOW(), ?, ?)";
        
        try {
            $stmt = $this->db->prepare($sql);
            
            // Manejar correctamente los parámetros
            $fecha_full = $fecha_actualizacion . ' 00:00:00';
            $fuente = 'api.dolarvzla.com';
            
            $stmt->bind_param("ddss", $tasa_usd, $tasa_eur, $fecha_full, $fuente);
            $stmt->execute();
            $stmt->close();
            
            // Limpiar cache viejo (más de 7 días)
            $this->cleanOldCache();
            
        } catch (Exception $e) {
            error_log("Error saving to cache: " . $e->getMessage());
        }
    }
    
    /**
     * Limpia cache antiguo
     */
    private function cleanOldCache() {
        $sql = "DELETE FROM tasa_bcv_cache 
                WHERE fecha_consulta < DATE_SUB(NOW(), INTERVAL 7 DAY)";
        
        try {
            $this->db->query($sql);
        } catch (Exception $e) {
            error_log("Error cleaning old cache: " . $e->getMessage());
        }
    }
    
    /**
     * Tasa de respaldo si la API falla
     */
    private function getFallbackRate() {
        $last_rate = $this->getFromCache();
        if ($last_rate) {
            return (float) $last_rate['tasa_usd'];
        }
        
        // Si no hay nada en cache, usar tasa por defecto
        return 355.55;
    }
    
    /**
     * Fuerza la actualización de la tasa
     */
    public function forceUpdate() {
        return $this->fetchFromAPI();
    }
    
    /**
     * Obtiene el historial de tasas (últimos 30 días)
     */
    public function getHistory($days = 30) {
        $sql = "SELECT * FROM tasa_bcv_cache 
                WHERE fecha_consulta >= DATE_SUB(NOW(), INTERVAL ? DAY)
                ORDER BY fecha_consulta DESC";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("i", $days);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $historial = [];
            while ($row = $result->fetch_assoc()) {
                $historial[] = $row;
            }
            
            $stmt->close();
            return $historial;
            
        } catch (Exception $e) {
            error_log("Error getting history: " . $e->getMessage());
            return [];
        }
    }
}
?>