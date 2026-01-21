<?php
// server.php

header('Content-Type: text/plain; charset=utf-8');

echo "MedAgenda-CR backend PHP\n";
echo "Endpoints disponibles (métodos y rutas relativas):\n\n";
echo "POST   backend/api/crear_cita.php\n";
echo "GET    backend/api/obtener_citas.php\n";
echo "PUT    backend/api/actualizar_cita.php\n";
echo "DELETE backend/api/eliminar_cita.php\n";
echo "GET    backend/api/estadisticas.php\n\n";
