-- Creación de tablas base para el sistema de captación

CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    rol_id INT NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (rol_id) REFERENCES roles(id)
);

CREATE TABLE zonas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL
);

CREATE TABLE centros (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    zona_id INT NOT NULL,
    FOREIGN KEY (zona_id) REFERENCES zonas(id)
);

CREATE TABLE atletas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    dni VARCHAR(20) NOT NULL UNIQUE,
    localidad VARCHAR(100),
    domicilio VARCHAR(255),
    email VARCHAR(100),
    telefono VARCHAR(30),
    sexo VARCHAR(10),
    altura DECIMAL(5,2),
    peso DECIMAL(5,2),
    envergadura DECIMAL(5,2),
    altura_sentado DECIMAL(5,2),
    evaluador_id INT NOT NULL,
    centro_id INT NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (evaluador_id) REFERENCES usuarios(id),
    FOREIGN KEY (centro_id) REFERENCES centros(id)
);

CREATE TABLE sesiones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fecha DATE NOT NULL,
    hora TIME NOT NULL,
    lugar VARCHAR(255) NOT NULL,
    evaluador_id INT NOT NULL,
    centro_id INT NOT NULL,
    FOREIGN KEY (evaluador_id) REFERENCES usuarios(id),
    FOREIGN KEY (centro_id) REFERENCES centros(id)
);

CREATE TABLE tests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT
);

CREATE TABLE resultados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    atleta_id INT NOT NULL,
    sesion_id INT NOT NULL,
    test_id INT NOT NULL,
    valor VARCHAR(100) NOT NULL,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (atleta_id) REFERENCES atletas(id),
    FOREIGN KEY (sesion_id) REFERENCES sesiones(id),
    FOREIGN KEY (test_id) REFERENCES tests(id)
);

-- Datos iniciales para roles y tests
INSERT INTO roles (nombre) VALUES ('Administrador'), ('Evaluador'), ('Atleta');

INSERT INTO tests (nombre, descripcion) VALUES
('Fuerza de agarre', 'Medición de fuerza de agarre'),
('Salto vertical', 'Medición de salto vertical'),
('Salto con contramovimiento', 'Medición de salto con contramovimiento'),
('Salto de longitud', 'Medición de salto de longitud'),
('Preferencia motriz visual', 'Test de preferencia motriz visual'),
('Preferencia motriz podal', 'Test de preferencia motriz podal'),
('Test de Wells', 'Test de flexibilidad'),
('Velocidad 20m', 'Test de velocidad en 20 metros');
