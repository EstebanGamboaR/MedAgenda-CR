-- =====================================================
-- MEDAGENDA-CR - BASE DE DATOS (Versión XAMPP)
-- =====================================================

CREATE DATABASE IF NOT EXISTS medagenda;
USE medagenda;

-- Tabla de Usuarios (Administrativos)
CREATE TABLE usuarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100),
    rol ENUM('admin', 'recepcionista') DEFAULT 'recepcionista',
    activo BOOLEAN DEFAULT TRUE,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de Citas
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
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de Historial (Auditoría)
CREATE TABLE historial_cambios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    cita_id INT,
    accion VARCHAR(50),
    descripcion TEXT,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cita_id) REFERENCES citas(id) ON DELETE CASCADE
);

-- Insertar Usuarios por Defecto
-- Passwords: 'admin123' y 'recepcion123' (Hasheados con BCRYPT)
INSERT INTO usuarios (email, password, nombre, rol) VALUES 
('admin@medagenda.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrador', 'admin'),
('recepcion@medagenda.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Recepcionista', 'recepcionista');