<?php
// =====================================================
// VERIFICAR SESIÓN
// =====================================================

session_start();

header('Content-Type: application/json; charset=utf-8');

if (isset($_SESSION['usuario_id'])) {
    echo json_encode([
        'ok' => true,
        'usuario' => [
            'id' => $_SESSION['usuario_id'],
            'email' => $_SESSION['usuario_email'],
            'nombre' => $_SESSION['usuario_nombre'],
            'rol' => $_SESSION['usuario_rol']
        ]
    ]);
} else {
    echo json_encode([
        'ok' => false,
        'error' => 'No hay sesión activa'
    ]);
}