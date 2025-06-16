-- Create database
CREATE DATABASE IF NOT EXISTS sistema_captacion;
USE sistema_captacion;

-- Roles table
CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    descripcion TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Users table (for authentication)
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    rol_id INT NOT NULL,
    activo BOOLEAN DEFAULT TRUE,
    ultimo_acceso DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (rol_id) REFERENCES roles(id)
);

-- Zones table
CREATE TABLE zonas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    activa BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Centers table
CREATE TABLE centros (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    direccion TEXT,
    zona_id INT,
    telefono VARCHAR(20),
    email VARCHAR(100),
    activo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (zona_id) REFERENCES zonas(id)
);

-- Evaluators table
CREATE TABLE evaluadores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT UNIQUE,
    nombre VARCHAR(50) NOT NULL,
    apellido VARCHAR(50) NOT NULL,
    dni VARCHAR(20) UNIQUE NOT NULL,
    domicilio TEXT,
    telefono VARCHAR(20),
    email VARCHAR(100) UNIQUE,
    centro_id INT,
    activo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (centro_id) REFERENCES centros(id)
);

-- Athletes table
CREATE TABLE atletas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT UNIQUE,
    nombre VARCHAR(50) NOT NULL,
    apellido VARCHAR(50) NOT NULL,
    dni VARCHAR(20) UNIQUE NOT NULL,
    fecha_nacimiento DATE,
    sexo ENUM('M', 'F', 'Otro'),
    domicilio TEXT,
    localidad VARCHAR(100),
    telefono VARCHAR(20),
    email VARCHAR(100) UNIQUE,
    altura DECIMAL(5,2) COMMENT 'en cm',
    peso DECIMAL(5,2) COMMENT 'en kg',
    envergadura DECIMAL(5,2) COMMENT 'en cm',
    altura_sentado DECIMAL(5,2) COMMENT 'en cm',
    evaluador_id INT,
    activo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (evaluador_id) REFERENCES evaluadores(id)
);

-- Tests table
CREATE TABLE pruebas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    unidad_medida VARCHAR(20),
    activo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Test sessions
CREATE TABLE sesiones_evaluacion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    fecha DATE NOT NULL,
    hora_inicio TIME,
    hora_fin TIME,
    centro_id INT,
    evaluador_id INT,
    observaciones TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (centro_id) REFERENCES centros(id),
    FOREIGN KEY (evaluador_id) REFERENCES evaluadores(id)
);

-- Test results
CREATE TABLE resultados_pruebas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sesion_id INT,
    atleta_id INT,
    prueba_id INT,
    valor_izquierdo DECIMAL(10,2),
    valor_derecho DECIMAL(10,2),
    valor_promedio DECIMAL(10,2),
    observaciones TEXT,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sesion_id) REFERENCES sesiones_evaluacion(id) ON DELETE CASCADE,
    FOREIGN KEY (atleta_id) REFERENCES atletas(id),
    FOREIGN KEY (prueba_id) REFERENCES pruebas(id)
);

-- Insert default roles
INSERT INTO roles (nombre, descripcion) VALUES 
('Administrador', 'Acceso total al sistema'),
('Evaluador', 'Puede realizar evaluaciones y gestionar atletas'),
('Atleta', 'Puede ver sus propios resultados');

-- Insert default tests
INSERT INTO pruebas (nombre, descripcion, unidad_medida) VALUES
('Fuerza de agarre', 'Test de prensión manual', 'kg'),
('Salto vertical', 'Altura máxima de salto', 'cm'),
('Salto con contramovimiento', 'Salto con impulso previo', 'cm'),
('Salto de longitud', 'Distancia de salto horizontal', 'cm'),
('Test de Wells', 'Flexibilidad de isquiotibiales', 'cm'),
('Velocidad 20m', 'Tiempo en recorrer 20m', 'segundos'),
('Preferencia motriz', 'Lateralidad (1=derecho, 2=izquierdo, 3=ambidiestro)', 'índice');
