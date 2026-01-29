<?php
// modules/inventario/limpiar_cache_tasa.php
require_once '../../config/database.php';

echo "<pre>=== LIMPIANDO CACHE DUPLICADO DE TASA BCV ===\n\n";

$database = Database::getInstance();
$db = $database->getConnection();

// 1. Ver estadísticas actuales
$sql_stats = "SELECT 
                COUNT(*) as total,
                COUNT(DISTINCT DATE(fecha_actualizacion)) as dias_unicos,
                MIN(fecha_actualizacion) as fecha_inicio,
                MAX(fecha_actualizacion) as fecha_fin
              FROM tasa_bcv_cache";
              
$result = $db->query($sql_stats);
$stats = $result->fetch_assoc();

echo "ESTADÍSTICAS ACTUALES:\n";
echo "Total registros: " . $stats['total'] . "\n";
echo "Días únicos: " . $stats['dias_unicos'] . "\n";
echo "Fecha inicio: " . $stats['fecha_inicio'] . "\n";
echo "Fecha fin: " . $stats['fecha_fin'] . "\n\n";

// 2. Crear tabla temporal con los registros a conservar
echo "CREANDO TABLA TEMPORAL...\n";
$sql_temp = "CREATE TEMPORARY TABLE tasa_keep AS
            SELECT 
                MAX(id) as id,
                DATE(fecha_actualizacion) as fecha,
                AVG(tasa_usd) as tasa_usd,
                MAX(fuente) as fuente,
                MAX(fecha_consulta) as fecha_consulta
            FROM tasa_bcv_cache 
            GROUP BY DATE(fecha_actualizacion)";
            
$db->query($sql_temp);
echo "Tabla temporal creada.\n\n";

// 3. Eliminar duplicados
echo "ELIMINANDO REGISTROS DUPLICADOS...\n";
$sql_delete = "DELETE t FROM tasa_bcv_cache t
               LEFT JOIN tasa_keep k ON t.id = k.id
               WHERE k.id IS NULL";
               
$result = $db->query($sql_delete);
$eliminados = $db->affected_rows;

echo "Registros eliminados: " . $eliminados . "\n\n";

// 4. Ver nuevas estadísticas
$result = $db->query($sql_stats);
$new_stats = $result->fetch_assoc();

echo "NUEVAS ESTADÍSTICAS:\n";
echo "Total registros: " . $new_stats['total'] . "\n";
echo "Días únicos: " . $new_stats['dias_unicos'] . "\n";
echo "Fecha inicio: " . $new_stats['fecha_inicio'] . "\n";
echo "Fecha fin: " . $new_stats['fecha_fin'] . "\n\n";

// 5. Mostrar resumen por día
echo "RESUMEN POR DÍA (últimos 7 días):\n";
$sql_dias = "SELECT 
                DATE(fecha_actualizacion) as fecha,
                COUNT(*) as registros,
                AVG(tasa_usd) as tasa_promedio
             FROM tasa_bcv_cache 
             WHERE fecha_actualizacion >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
             GROUP BY DATE(fecha_actualizacion)
             ORDER BY fecha DESC";
             
$result = $db->query($sql_dias);
while ($row = $result->fetch_assoc()) {
    echo $row['fecha'] . ": " . $row['registros'] . " registros, tasa: " . 
         number_format($row['tasa_promedio'], 2, ',', '.') . "\n";
}

echo "\n=== LIMPIEZA COMPLETADA ===</pre>";
?>