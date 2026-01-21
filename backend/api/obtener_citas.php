<?php
// =====================================================
// OBTENER CITAS - CON MySQL
// =====================================================

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');

require_once '../config/database.php';
require_once '../utils/funciones.php';

// Validar método
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    respuestaJSON(['ok' => false, 'error' => 'Método no permitido'], 405);
}

try {
    $pdo = getConnection();
    
    // Query base
    $sql = "SELECT * FROM citas WHERE 1=1";
    $params = [];
    
    // Filtro por fecha
    if (isset($_GET['fecha']) && !empty($_GET['fecha'])) {
        $sql .= " AND fecha = :fecha";
        $params[':fecha'] = $_GET['fecha'];
    }
    
    // Filtro por prioridad
    if (isset($_GET['prioridad']) && !empty($_GET['prioridad'])) {
        $sql .= " AND prioridad = :prioridad";
        $params[':prioridad'] = $_GET['prioridad'];
    }
    
    // Filtro por estado
    if (isset($_GET['estado']) && !empty($_GET['estado'])) {
        $sql .= " AND estado = :estado";
        $params[':estado'] = $_GET['estado'];
    }
    
    // Ordenar por fecha y hora
    $sql .= " ORDER BY fecha ASC, hora ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $citas = $stmt->fetchAll();
    
    respuestaJSON([
        'ok' => true,
        'citas' => $citas,
        'total' => count($citas)
    ]);
    
} catch (PDOException $e) {
    error_log("Error en obtener_citas.php: " . $e->getMessage());
    respuestaJSON([
        'ok' => false,
        'error' => 'Error al obtener las citas'
    ], 500);
}