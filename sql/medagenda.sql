-- =====================================================
-- MEDAGENDA-CR - BASE DE DATOS
-- Sistema de Gestión de Citas Médicas
-- =====================================================

-- Crear base de datos
CREATE DATABASE IF NOT EXISTS medagenda;
USE medagenda;

-- =====================================================
-- TABLA: usuarios (Admin y Recepcionistas)
-- =====================================================
CREATE TABLE usuarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100),
    rol ENUM('admin', 'recepcionista') DEFAULT 'recepcionista',
    activo BOOLEAN DEFAULT TRUE,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: citas (Agendamiento de Citas Médicas)
-- =====================================================
CREATE TABLE citas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre_paciente VARCHAR(100) NOT NULL,
    telefono VARCHAR(20) NOT NULL,
    email VARCHAR(100),
    fecha DATE NOT NULL,
    hora TIME NOT NULL,
    motivo TEXT,
    sintomas JSON,
    puntaje_triage INT DEFAULT 0,
    prioridad VARCHAR(50) DEFAULT 'No urgente',
    estado ENUM('pendiente', 'confirmada', 'en_espera', 'atendida', 'cancelada') DEFAULT 'pendiente',
    recordatorio_enviado BOOLEAN DEFAULT FALSE,
    notas_admin TEXT,
    creado_por INT,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (creado_por) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_fecha_hora (fecha, hora),
    INDEX idx_estado (estado),
    INDEX idx_prioridad (puntaje_triage DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: historial_cambios (Auditoría)
-- =====================================================
CREATE TABLE historial_cambios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    cita_id INT NOT NULL,
    usuario_id INT,
    accion ENUM('creada', 'modificada', 'cancelada', 'confirmada', 'atendida'),
    descripcion TEXT,
    fecha_cambio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cita_id) REFERENCES citas(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_cita (cita_id),
    INDEX idx_fecha (fecha_cambio)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- INSERTAR DATOS DE PRUEBA
-- =====================================================

-- Usuario Admin (contraseña: admin123)
INSERT INTO usuarios (email, password, nombre, apellidos, rol) VALUES
('admin@medagenda.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrador', 'Sistema', 'admin');

-- Usuario Recepcionista (contraseña: recep123)
INSERT INTO usuarios (email, password, nombre, apellidos, rol) VALUES
('recepcionista@medagenda.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'María', 'González', 'recepcionista');

-- Citas de prueba
INSERT INTO citas (nombre_paciente, telefono, email, fecha, hora, motivo, sintomas, puntaje_triage, prioridad, estado, creado_por) VALUES
('Alvaro Diaz', '8888-8888', 'alvaro@email.com', '2025-12-10', '09:00:00', 'Consulta general', 
'{"dolor_pecho": false, "dificultad_respirar": false, "sangrado": false, "fiebre_alta": false, "dolor_intenso": false}',
0, 'No urgente', 'pendiente', 1),

('Jefferson Edward', '7777-7777', 'jeff@email.com', '2025-12-10', '10:30:00', 'Control prenatal',
'{"dolor_pecho": false, "dificultad_respirar": false, "sangrado": true, "fiebre_alta": false, "dolor_intenso": false}',
5, 'Urgente', 'confirmada', 1),

('Esteban Gamboa', '6666-6666', 'gamboa@email.com', '2025-12-11', '14:00:00', 'Dolor de cabeza persistente',
'{"dolor_pecho": false, "dificultad_respirar": false, "sangrado": false, "fiebre_alta": true, "dolor_intenso": true}',
6, 'Urgente', 'pendiente', 1);

-- =====================================================
-- VISTA: Estadísticas del Dashboard
-- =====================================================
CREATE VIEW estadisticas_dashboard AS
SELECT
    (SELECT COUNT(*) FROM citas WHERE estado = 'pendiente') AS pendientes,
    (SELECT COUNT(*) FROM citas WHERE estado = 'confirmada') AS confirmadas,
    (SELECT COUNT(*) FROM citas WHERE estado = 'en_espera') AS en_espera,
    (SELECT COUNT(*) FROM citas WHERE fecha = CURDATE()) AS hoy,
    (SELECT COUNT(*) FROM citas WHERE puntaje_triage >= 7) AS urgentes;

-- =====================================================
-- PROCEDIMIENTO: Obtener próximas citas
-- =====================================================
DELIMITER //
CREATE PROCEDURE obtener_proximas_citas(IN dias INT)
BEGIN
    SELECT * FROM citas
    WHERE fecha BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL dias DAY)
    ORDER BY fecha ASC, hora ASC;
END //
DELIMITER ;

-- =====================================================
-- TRIGGER: Auditoría automática
-- =====================================================
DELIMITER //
CREATE TRIGGER auditoria_citas_insert
AFTER INSERT ON citas
FOR EACH ROW
BEGIN
    INSERT INTO historial_cambios (cita_id, usuario_id, accion, descripcion)
    VALUES (NEW.id, NEW.creado_por, 'creada', CONCAT('Cita creada para ', NEW.nombre_paciente));
END //
DELIMITER ;

-- =====================================================
-- FIN DE SCRIPT
-- =====================================================