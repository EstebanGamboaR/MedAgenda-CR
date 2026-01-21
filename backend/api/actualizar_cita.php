<?php
// =====================================================
// ACTUALIZAR CITA - CON MySQL
// =====================================================

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: PUT, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once '../config/database.php';
require_once '../utils/funciones.php';

// Permitir PUT y POST
if (!in_array($_SERVER['REQUEST_METHOD'], ['PUT', 'POST'])) {
    respuestaJSON(['ok' => false, 'error' => 'Método no permitido'], 405);
}

// Leer datos
$datos = leerJSON();

// Validar ID
requerirCampo($datos, 'id', 'El ID de la cita es obligatorio');

try {
    $pdo = getConnection();
    
    // Verificar que la cita existe
    $stmt = $pdo->prepare("SELECT id FROM citas WHERE id = ?");
    $stmt->execute([$datos['id']]);
    if (!$stmt->fetch()) {
        respuestaJSON(['ok' => false, 'error' => 'Cita no encontrada'], 404);
    }
    
    // Construir UPDATE dinámicamente
    $camposActualizar = [];
    $params = [':id' => $datos['id']];
    
    $camposPermitidos = [
        'nombre_paciente', 'telefono', 'email', 'fecha', 'hora',
        'motivo', 'sintomas', 'puntaje_triage', 'prioridad', 'estado',
        'notas_admin', 'recordatorio_enviado'
    ];
    
    foreach ($camposPermitidos as $campo) {
        if (isset($datos[$campo])) {
            $camposActualizar[] = "$campo = :$campo";
            $params[":$campo"] = $datos[$campo];
        }
    }
    
    if (empty($camposActualizar)) {
        respuestaJSON(['ok' => false, 'error' => 'No hay campos para actualizar'], 400);
    }
    
    $sql = "UPDATE citas SET " . implode(', ', $camposActualizar) . " WHERE id = :id";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    respuestaJSON([
        'ok' => true,
        'mensaje' => 'Cita actualizada exitosamente'
    ]);
    
} catch (PDOException $e) {
    error_log("Error en actualizar_cita.php: " . $e->getMessage());
    respuestaJSON([
        'ok' => false,
        'error' => 'Error al actualizar la cita'
    ], 500);
}