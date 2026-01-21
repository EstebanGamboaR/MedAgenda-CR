<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');
require_once '../config/database.php';
require_once '../utils/funciones.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { // Usamos POST para simplicidad en XAMPP
    respuestaJSON(['ok' => false, 'error' => 'Método no permitido'], 405);
}

$datos = leerJSON();
requerirCampo($datos, 'id', 'ID obligatorio');

try {
    $pdo = getConnection();
    // Ejemplo: Actualizar solo estado
    if (isset($datos['estado'])) {
        $stmt = $pdo->prepare("UPDATE citas SET estado = ? WHERE id = ?");
        $stmt->execute([$datos['estado'], $datos['id']]);
        respuestaJSON(['ok' => true, 'mensaje' => 'Estado actualizado']);
    } else {
        respuestaJSON(['ok' => false, 'error' => 'Nada que actualizar'], 400);
    }
} catch (PDOException $e) {
    respuestaJSON(['ok' => false, 'error' => $e->getMessage()], 500);
}
?>