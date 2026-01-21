<?php
// =====================================================
// ELIMINAR CITA - CON MySQL
// =====================================================

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: DELETE, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once '../config/database.php';
require_once '../utils/funciones.php';

// Permitir DELETE y POST
if (!in_array($_SERVER['REQUEST_METHOD'], ['DELETE', 'POST'])) {
    respuestaJSON(['ok' => false, 'error' => 'Método no permitido'], 405);
}

// Leer datos
$datos = leerJSON();

// Validar ID
requerirCampo($datos, 'id', 'El ID de la cita es obligatorio');

try {
    $pdo = getConnection();
    
    // Verificar que existe
    $stmt = $pdo->prepare("SELECT id FROM citas WHERE id = ?");
    $stmt->execute([$datos['id']]);
    if (!$stmt->fetch()) {
        respuestaJSON(['ok' => false, 'error' => 'Cita no encontrada'], 404);
    }
    
    // Eliminar
    $stmt = $pdo->prepare("DELETE FROM citas WHERE id = ?");
    $stmt->execute([$datos['id']]);
    
    respuestaJSON([
        'ok' => true,
        'mensaje' => 'Cita eliminada exitosamente'
    ]);
    
} catch (PDOException $e) {
    error_log("Error en eliminar_cita.php: " . $e->getMessage());
    respuestaJSON([
        'ok' => false,
        'error' => 'Error al eliminar la cita'
    ], 500);
}