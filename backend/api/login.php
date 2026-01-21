<?php
session_start();
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');

require_once '../config/database.php';
require_once '../utils/funciones.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respuestaJSON(['ok' => false, 'error' => 'Método no permitido'], 405);
}

$datos = leerJSON();
requerirCampo($datos, 'email', 'Email obligatorio');
requerirCampo($datos, 'password', 'Contraseña obligatoria');

$pdo = getConnection();
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ? AND activo = 1");
$stmt->execute([$datos['email']]);
$usuario = $stmt->fetch();

if ($usuario && password_verify($datos['password'], $usuario['password'])) {
    $_SESSION['uid'] = $usuario['id'];
    $_SESSION['rol'] = $usuario['rol'];
    
    respuestaJSON([
        'ok' => true,
        'mensaje' => 'Bienvenido ' . $usuario['nombre'],
        'usuario' => ['nombre' => $usuario['nombre'], 'rol' => $usuario['rol']]
    ]);
} else {
    respuestaJSON(['ok' => false, 'error' => 'Credenciales inválidas'], 401);
}
?>