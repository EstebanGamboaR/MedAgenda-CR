<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');
require_once '../config/database.php';
require_once '../utils/funciones.php';

$datos = leerJSON();
requerirCampo($datos, 'id', 'ID obligatorio');

try {
    $pdo = getConnection();
    $stmt = $pdo->prepare("DELETE FROM citas WHERE id = ?");
    $stmt->execute([$datos['id']]);
    respuestaJSON(['ok' => true, 'mensaje' => 'Cita eliminada']);
} catch (PDOException $e) {
    respuestaJSON(['ok' => false, 'error' => $e->getMessage()], 500);
}
?>