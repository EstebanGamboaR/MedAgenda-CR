<?php
// Utilidades generales para respuestas JSON

function respuestaJSON($datos, $codigo = 200) {
    http_response_code($codigo);
    echo json_encode($datos);
    exit;
}

function leerJSON() {
    $json = file_get_contents('php://input');
    return json_decode($json, true);
}

function requerirCampo($datos, $campo, $mensaje) {
    if (!isset($datos[$campo]) || empty(trim($datos[$campo]))) {
        respuestaJSON(['ok' => false, 'error' => $mensaje], 400);
    }
}
?>