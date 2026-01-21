<?php
/**
 * MEDAGENDA-CR - Configuración de Base de Datos
 * Conexión PDO a MySQL con manejo de errores
 */

// Configuración de la base de datos
define('DB_HOST', 'db');  // Nombre del servicio en docker-compose
define('DB_NAME', 'medagenda');
define('DB_USER', 'medagenda_user');
define('DB_PASS', 'medagenda123');
define('DB_CHARSET', 'utf8mb4');

// Opciones de PDO
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

// Crear conexión
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    // En producción, registrar el error y mostrar mensaje genérico
    error_log("Error de conexión a BD: " . $e->getMessage());
    die(json_encode([
        'success' => false,
        'message' => 'Error de conexión a la base de datos'
    ]));
}

// Función helper para obtener la conexión
function getConnection() {
    global $pdo;
    return $pdo;
}
?>