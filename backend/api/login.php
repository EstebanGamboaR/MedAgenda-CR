<?php
// =====================================================
// LOGIN - AUTENTICACIÓN
// =====================================================

session_start();

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json; charset=utf-8');

require_once '../config/database.php';
require_once '../utils/funciones.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respuestaJSON(['ok' => false, 'error' => 'Método no permitido'], 405);
}

$datos = leerJSON();

requerirCampo($datos, 'email', 'El email es obligatorio');
requerirCampo($datos, 'password', 'La contraseña es obligatoria');

try {
    $pdo = getConnection();
    
    // Buscar usuario
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ? AND activo = 1");
    $stmt->execute([$datos['email']]);
    $usuario = $stmt->fetch();
    
    if (!$usuario) {
        respuestaJSON([
            'ok' => false,
            'error' => 'Credenciales incorrectas'
        ], 401);
    }
    
    // Verificar contraseña
    if (!password_verify($datos['password'], $usuario['password'])) {
        respuestaJSON([
            'ok' => false,
            'error' => 'Credenciales incorrectas'
        ], 401);
    }
    
    // Crear sesión
    $_SESSION['usuario_id'] = $usuario['id'];
    $_SESSION['usuario_email'] = $usuario['email'];
    $_SESSION['usuario_nombre'] = $usuario['nombre'];
    $_SESSION['usuario_rol'] = $usuario['rol'];
    
    respuestaJSON([
        'ok' => true,
        'mensaje' => 'Inicio de sesión exitoso',
        'usuario' => [
            'id' => $usuario['id'],
            'email' => $usuario['email'],
            'nombre' => $usuario['nombre'],
            'rol' => $usuario['rol']
        ]
    ]);
    
} catch (PDOException $e) {
    error_log("Error en login.php: " . $e->getMessage());
    respuestaJSON([
        'ok' => false,
        'error' => 'Error en el servidor'
    ], 500);
}