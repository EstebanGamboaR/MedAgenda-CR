<?php
// Configuración de conexión para XAMPP
define('DB_HOST', 'localhost');
define('DB_NAME', 'medagenda');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    header('Content-Type: application/json');
    die(json_encode(['ok' => false, 'error' => 'Error de conexión a BD: ' . $e->getMessage()]));
}

function getConnection() {
    global $pdo;
    return $pdo;
}
?>