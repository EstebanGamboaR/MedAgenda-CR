<?php
require_once 'backend/config/database.php';

try {
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM usuarios");
    $result = $stmt->fetch();
    echo "✅ Conexión exitosa! Total usuarios: " . $result['total'];
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>