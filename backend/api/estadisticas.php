<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');
require_once '../config/database.php';
require_once '../utils/funciones.php';

try {
    $pdo = getConnection();
    
    $stats = [
        'total' => $pdo->query("SELECT COUNT(*) FROM citas")->fetchColumn(),
        'emergencias' => $pdo->query("SELECT COUNT(*) FROM citas WHERE prioridad = 'emergencia'")->fetchColumn(),
        'hoy' => $pdo->query("SELECT COUNT(*) FROM citas WHERE fecha = CURDATE()")->fetchColumn()
    ];
    
    respuestaJSON(['ok' => true, 'estadisticas' => $stats]);
} catch (PDOException $e) {
    respuestaJSON(['ok' => false, 'error' => $e->getMessage()], 500);
}
?>