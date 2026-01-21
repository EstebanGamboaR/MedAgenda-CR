<?php
// =====================================================
// ESTADÍSTICAS - CON MySQL
// =====================================================

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');

require_once '../config/database.php';
require_once '../utils/funciones.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    respuestaJSON(['ok' => false, 'error' => 'Método no permitido'], 405);
}

try {
    $pdo = getConnection();
    
    // Total de citas activas (no canceladas)
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM citas WHERE estado != 'cancelada'");
    $totalCitas = $stmt->fetch()['total'];
    
    // Emergencias (puntaje >= 8)
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM citas WHERE puntaje_triage >= 8 AND estado != 'cancelada'");
    $emergencias = $stmt->fetch()['total'];
    
    // Muy urgentes (puntaje 4-7)
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM citas WHERE puntaje_triage BETWEEN 4 AND 7 AND estado != 'cancelada'");
    $muyUrgentes = $stmt->fetch()['total'];
    
    // En lista de espera
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM citas WHERE estado = 'en_espera'");
    $enEspera = $stmt->fetch()['total'];
    
    // Pendientes
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM citas WHERE estado = 'pendiente'");
    $pendientes = $stmt->fetch()['total'];
    
    // Confirmadas
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM citas WHERE estado = 'confirmada'");
    $confirmadas = $stmt->fetch()['total'];
    
    // Hoy
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM citas WHERE fecha = CURDATE() AND estado != 'cancelada'");
    $hoy = $stmt->fetch()['total'];
    
    respuestaJSON([
        'ok' => true,
        'estadisticas' => [
            'total_citas' => (int)$totalCitas,
            'emergencias' => (int)$emergencias,
            'muy_urgentes' => (int)$muyUrgentes,
            'en_espera' => (int)$enEspera,
            'pendientes' => (int)$pendientes,
            'confirmadas' => (int)$confirmadas,
            'hoy' => (int)$hoy
        ]
    ]);
    
} catch (PDOException $e) {
    error_log("Error en estadisticas.php: " . $e->getMessage());
    respuestaJSON([
        'ok' => false,
        'error' => 'Error al obtener estadísticas'
    ], 500);
}