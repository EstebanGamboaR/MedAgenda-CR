<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');
require_once '../config/database.php';
require_once '../utils/funciones.php';

try {
    $pdo = getConnection();
    $stmt = $pdo->query("SELECT * FROM citas ORDER BY fecha ASC, hora ASC");
    $citas = $stmt->fetchAll();
    respuestaJSON(['ok' => true, 'citas' => $citas]);
} catch (PDOException $e) {
    respuestaJSON(['ok' => false, 'error' => $e->getMessage()], 500);
}
?>