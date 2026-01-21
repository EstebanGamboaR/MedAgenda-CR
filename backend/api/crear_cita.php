<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');

require_once '../config/database.php';
require_once '../utils/funciones.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respuestaJSON(['ok' => false, 'error' => 'Método no permitido'], 405);
}

$datos = leerJSON();

// Validaciones
requerirCampo($datos, 'nombre_paciente', 'Nombre obligatorio');
requerirCampo($datos, 'telefono', 'Teléfono obligatorio');
requerirCampo($datos, 'fecha', 'Fecha obligatoria');
requerirCampo($datos, 'hora', 'Hora obligatoria');

try {
    $pdo = getConnection();
    
    // Convertir array de síntomas a JSON string para guardar en BD
    $sintomasJSON = isset($datos['sintomas']) ? json_encode($datos['sintomas']) : '[]';
    
    $sql = "INSERT INTO citas (nombre_paciente, telefono, fecha, hora, motivo, sintomas, puntaje_triage, prioridad, estado) 
            VALUES (:nombre, :tel, :fecha, :hora, :motivo, :sintomas, :score, :prioridad, 'pendiente')";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':nombre' => $datos['nombre_paciente'],
        ':tel' => $datos['telefono'],
        ':fecha' => $datos['fecha'],
        ':hora' => $datos['hora'],
        ':motivo' => $datos['motivo'] ?? '',
        ':sintomas' => $sintomasJSON,
        ':score' => $datos['puntaje_triage'] ?? 0,
        ':prioridad' => $datos['prioridad'] ?? 'No urgente'
    ]);

    respuestaJSON([
        'ok' => true, 
        'mensaje' => 'Cita creada exitosamente', 
        'id' => $pdo->lastInsertId()
    ]);

} catch (PDOException $e) {
    respuestaJSON(['ok' => false, 'error' => 'Error BD: ' . $e->getMessage()], 500);
}
?>