<?php
// backend/utils/funciones.php
// Utilidades generales y almacenamiento en archivo JSON (sin base de datos).

const CITA_FILE = __DIR__ . '/../data/citas.json';

/**
 * Carga las citas desde el archivo JSON.
 */
function cargarCitas() {
    if (!file_exists(CITA_FILE)) {
        return [];
    }
    $contenido = file_get_contents(CITA_FILE);
    if ($contenido === false || trim($contenido) === '') {
        return [];
    }
    $data = json_decode($contenido, true);
    if (!is_array($data)) {
        return [];
    }
    return $data;
}

/**
 * Guarda el arreglo de citas en el archivo JSON.
 */
function guardarCitas(array $citas) {
    // Asegurar que el directorio exista
    $dir = dirname(CITA_FILE);
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
    file_put_contents(CITA_FILE, json_encode($citas, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
}

/**
 * Lee el cuerpo de la petición (JSON) y lo decodifica como array asociativo.
 */
function leerJSON() {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);

    if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
        respuestaJSON([
            'ok' => false,
            'error' => 'JSON inválido en el cuerpo de la petición'
        ], 400);
    }
    return $data;
}

/**
 * Envía una respuesta JSON con código de estado HTTP.
 */
function respuestaJSON($data, int $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

/**
 * Valida que un campo exista y no venga vacío.
 */
function requerirCampo(array $data, string $campo, string $mensaje = null) {
    if (!isset($data[$campo]) || $data[$campo] === '') {
        respuestaJSON([
            'ok' => false,
            'error' => $mensaje ?: "El campo '$campo' es obligatorio"
        ], 400);
    }
}
