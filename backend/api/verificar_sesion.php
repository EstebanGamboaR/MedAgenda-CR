<?php
session_start();
header('Content-Type: application/json');
if (isset($_SESSION['uid'])) {
    echo json_encode(['ok' => true, 'usuario' => ['rol' => $_SESSION['rol']]]);
} else {
    echo json_encode(['ok' => false]);
}
?>