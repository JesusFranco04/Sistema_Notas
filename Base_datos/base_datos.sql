CREATE DATABASE Sistema_Gestion;

USE Sistema_Gestion;

CREATE TABLE rol (
    id_rol INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    estado CHAR(1) NOT NULL CHECK (estado IN ('A', 'I')), -- A: Activo, I: Inactivo
    usuario_ingreso VARCHAR(50) NOT NULL,
    fecha_ingreso DATE NOT NULL
);

-- Insertar los perfiles
INSERT INTO rol (nombre, estado, usuario_ingreso, fecha_ingreso) 
VALUES ('Administrador', 'A', '0954352185', NOW());

INSERT INTO rol (nombre, estado, usuario_ingreso, fecha_ingreso) 
VALUES ('Profesor', 'A', '0954352185', NOW());

INSERT INTO rol (nombre, estado, usuario_ingreso, fecha_ingreso) 
VALUES ('Padre', 'A', '0954352185', NOW());


CREATE TABLE usuario (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    cedula VARCHAR(10) NOT NULL UNIQUE,
    contraseña VARCHAR(255) NOT NULL,
    id_rol INT NOT NULL,
    estado CHAR(1) NOT NULL,
    usuario_ingreso VARCHAR(50) NOT NULL,
    fecha_ingreso TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_rol) REFERENCES rol(id_rol)
);



CREATE TABLE nivel ( -- En esta tabla se guardara los datos por ejemplo: A, B, C, D,... 
	id_nivel INT AUTO_INCREMENT PRIMARY KEY,
	nombre VARCHAR(40) NOT NULL,
    estado CHAR(1) NOT NULL DEFAULT 'A', -- A: Activo, I: Inactivo
    usuario_ingreso VARCHAR(50) NOT NULL, -- Nombre de usuario que crea o modifica
    fecha_ingreso TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP -- Fecha y hora de registro
);

CREATE TABLE subnivel ( -- En esta tabla se guardara los datos de las especialidades que ofrece el plantel por ejemplo: 
	id_subnivel INT AUTO_INCREMENT PRIMARY KEY,
	nombre VARCHAR(40) NOT NULL, -- Aqui por ejemplo: Educación Basica General  y Bachillerato Técnico Industrial 
    abreviatura varchar(6) NOT NULL, -- Aqui por ejemplo: EBG y BTI
    estado CHAR(1) NOT NULL DEFAULT 'A', -- A: Activo, I: Inactivo
    usuario_ingreso VARCHAR(50) NOT NULL, -- Nombre de usuario que crea o modifica
    fecha_ingreso TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP -- Fecha y hora de registro
);

CREATE TABLE paralelo ( -- En esta tabla se guardara los datos de los paralelos por ejemplo: A, B, C, D,... 
	id_paralelo INT AUTO_INCREMENT PRIMARY KEY,
	nombre VARCHAR(2) NOT NULL,
    estado CHAR(1) NOT NULL DEFAULT 'A', -- A: Activo, I: Inactivo
    usuario_ingreso VARCHAR(50) NOT NULL, -- Nombre de usuario que crea o modifica
    fecha_ingreso TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP -- Fecha y hora de registro
);

CREATE TABLE especialidad ( -- En esta tabla se guardara los datos de las especialidades que ofrece el plantel por ejemplo: Mecánica Automotriz, Electrónica de Consumo,..
	id_especialidad INT AUTO_INCREMENT PRIMARY KEY,
	nombre VARCHAR(40) NOT NULL,
    estado CHAR(1) NOT NULL DEFAULT 'A', -- A: Activo, I: Inactivo
    usuario_ingreso VARCHAR(50) NOT NULL, -- Nombre de usuario que crea o modifica
    fecha_ingreso TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP -- Fecha y hora de registro
);

CREATE TABLE jornada ( -- En esta tabla se guardara los datos de las jornadas: Matutina, Vespertina y Nocturna
	id_jornada INT AUTO_INCREMENT PRIMARY KEY,
	nombre VARCHAR(40) NOT NULL,
    estado CHAR(1) NOT NULL DEFAULT 'A', -- A: Activo, I: Inactivo
    usuario_ingreso VARCHAR(50) NOT NULL, -- Nombre de usuario que crea o modifica
    fecha_ingreso TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP -- Fecha y hora de registro
);

CREATE TABLE materia ( -- En esta tabla se guardara los datos de las materias que hay por ejemplo: matematicas, lenguaje, ciencias naturales...
	id_materia INT AUTO_INCREMENT PRIMARY KEY,
	nombre VARCHAR(40) NOT NULL,
    estado CHAR(1) NOT NULL DEFAULT 'A', -- A: Activo, I: Inactivo
    usuario_ingreso VARCHAR(50) NOT NULL, -- Nombre de usuario que crea o modifica
    fecha_ingreso TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP -- Fecha y hora de registro
);

CREATE TABLE periodo_academico ( -- En esta tabla se guardara los datos de los periodos que tenga la institucion por ejemplo: quimestres, trimestres, entre otros
	id_periodo INT AUTO_INCREMENT PRIMARY KEY,
	nombre VARCHAR(40) NOT NULL,
    estado CHAR(1) NOT NULL DEFAULT 'A', -- A: Activo, I: Inactivo
    usuario_ingreso VARCHAR(50) NOT NULL, -- Nombre de usuario que crea o modifica
    fecha_ingreso TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP -- Fecha y hora de registro
);

CREATE TABLE historial_academico ( -- En esta tabla se guardara los datos de los años lectivos por ejemplo: 2023-2024 , 2024-2025
	id_his_academico INT AUTO_INCREMENT PRIMARY KEY,
	año VARCHAR(40) NOT NULL,
    estado CHAR(1) NOT NULL DEFAULT 'A', -- A: Activo, I: Inactivo
    usuario_ingreso VARCHAR(50) NOT NULL, -- Nombre de usuario que crea o modifica
    fecha_ingreso TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP -- Fecha y hora de registro
);

CREATE TABLE administrador (
    id_administrador INT AUTO_INCREMENT PRIMARY KEY,
    nombres VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    cedula VARCHAR(10) NOT NULL UNIQUE,
    telefono VARCHAR(10) NOT NULL,
    correo_electronico VARCHAR(100) NOT NULL,
    direccion VARCHAR(255) NOT NULL,
    fecha_nacimiento DATE NOT NULL,
    genero ENUM('femenino', 'masculino', 'otros') NOT NULL,
    discapacidad ENUM('si', 'no') NOT NULL,
    id_usuario INT NOT NULL,
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario)
);


CREATE TABLE profesor (
    id_profesor INT AUTO_INCREMENT PRIMARY KEY,
    nombres VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    cedula VARCHAR(10) NOT NULL UNIQUE,
    telefono VARCHAR(10) NOT NULL,
    correo_electronico VARCHAR(100) NOT NULL,
    direccion VARCHAR(255) NOT NULL,
    fecha_nacimiento DATE NOT NULL,
    genero ENUM('femenino', 'masculino', 'otros') NOT NULL,
    discapacidad ENUM('si', 'no') NOT NULL,
    id_usuario INT NOT NULL,
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario)
);



CREATE TABLE padre (
    id_padre INT AUTO_INCREMENT PRIMARY KEY,
    nombres VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    cedula VARCHAR(10) NOT NULL UNIQUE,
    parentesco VARCHAR(50) NOT NULL,
    telefono VARCHAR(10) NOT NULL,
    correo_electronico VARCHAR(100) NOT NULL,
    direccion VARCHAR(255) NOT NULL,
    fecha_nacimiento DATE NOT NULL,
    genero ENUM('femenino', 'masculino', 'otros') NOT NULL,
    discapacidad ENUM('si', 'no') NOT NULL,
    id_usuario INT NOT NULL,
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario)
);


CREATE TABLE estudiante (
    id_estudiante INT AUTO_INCREMENT PRIMARY KEY,
    nombres VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    cedula VARCHAR(10) NOT NULL UNIQUE,
    telefono VARCHAR(10) NULL,
    correo_electronico VARCHAR(100) NULL,
    direccion VARCHAR(255) NOT NULL,
    fecha_nacimiento DATE NOT NULL,
    genero ENUM('femenino', 'masculino', 'otros') NOT NULL,
    discapacidad ENUM('si', 'no') NOT NULL,
    estado_calificacion CHAR(1) NOT NULL DEFAULT 'P', -- P: Pendiente, A: Aprobado, R: Reprobado
    estado CHAR(1) NOT NULL DEFAULT 'A', -- A: Activo, I: Inactivo
    id_nivel INT NOT NULL, -- Nivel en el que está matriculado el estudiante
    id_paralelo INT NOT NULL, -- Paralelo en el que está matriculado el estudiante
    id_jornada INT NOT NULL, -- Jornada en la que está matriculado el estudiante
    id_historial_academico INT NOT NULL, -- Año académico en el que está matriculado el estudiante
    usuario_ingreso VARCHAR(50) NOT NULL, -- Nombre de usuario que crea o modifica
    fecha_ingreso TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, -- Fecha y hora de registro
    FOREIGN KEY (id_nivel) REFERENCES nivel(id_nivel),
    FOREIGN KEY (id_paralelo) REFERENCES paralelo(id_paralelo),
    FOREIGN KEY (id_jornada) REFERENCES jornada(id_jornada),
    FOREIGN KEY (id_historial_academico) REFERENCES historial_academico(id_his_academico)
);



CREATE TABLE curso (
    id_curso INT AUTO_INCREMENT PRIMARY KEY,
    id_profesor INT NOT NULL,
    id_materia INT NOT NULL,
    id_nivel INT NOT NULL,
    id_paralelo INT NOT NULL,
    id_subnivel INT NOT NULL,
    id_especialidad INT NOT NULL,
    id_jornada INT NOT NULL,
    id_his_academico INT NOT NULL,
    estado CHAR(1) NOT NULL DEFAULT 'A', -- A: Activo, I: Inactivo
    usuario_ingreso VARCHAR(50) NOT NULL, -- Número de cédula del usuario que crea o modifica
    fecha_ingreso TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, -- Fecha y hora de registro
    FOREIGN KEY (id_profesor) REFERENCES profesor(id_profesor),
    FOREIGN KEY (id_materia) REFERENCES materia(id_materia),
    FOREIGN KEY (id_nivel) REFERENCES nivel(id_nivel),
    FOREIGN KEY (id_paralelo) REFERENCES paralelo(id_paralelo),
    FOREIGN KEY (id_subnivel) REFERENCES subnivel(id_subnivel),
    FOREIGN KEY (id_especialidad) REFERENCES especialidad(id_especialidad),
    FOREIGN KEY (id_jornada) REFERENCES jornada(id_jornada),
    FOREIGN KEY (id_his_academico) REFERENCES historial_academico(id_his_academico)
);

CREATE TABLE curso_materia (
    id_curso_materia INT AUTO_INCREMENT PRIMARY KEY,
    id_curso INT NOT NULL,
    id_materia INT NOT NULL,
    FOREIGN KEY (id_curso) REFERENCES curso(id_curso),
    FOREIGN KEY (id_materia) REFERENCES materia(id_materia)
);



CREATE TABLE asignacion_estudiante ( -- Asignación de Estudiantes a Cursos
    id_asig_estudiante INT AUTO_INCREMENT PRIMARY KEY,
    id_estudiante INT NOT NULL,
    id_curso INT NOT NULL,
    id_jornada INT NOT NULL,
    id_his_academico INT NOT NULL,
	estado CHAR(1) NOT NULL DEFAULT 'A', -- A: Activo, I: Inactivo
    usuario_ingreso VARCHAR(50) NOT NULL, -- Nombre de usuario que crea o modifica
    fecha_ingreso TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, -- Fecha y hora de registro
    FOREIGN KEY (id_estudiante) REFERENCES estudiante(id_estudiante),
    FOREIGN KEY (id_curso) REFERENCES curso(id_curso),
    FOREIGN KEY (id_jornada) REFERENCES jornada(id_jornada),
    FOREIGN KEY (id_his_academico) REFERENCES historial_academico(id_his_academico)
);

-- Tabla Tipos_Examen
CREATE TABLE Tipos_Examen (
    id_examen INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    habilitado BOOLEAN DEFAULT FALSE,
	id_periodo INT,
    estado CHAR(1) NOT NULL CHECK (estado IN ('A', 'I')), -- A: Activo, I: Inactivo
    usuario_ingreso VARCHAR(50) NOT NULL,
    fecha_ingreso TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	FOREIGN KEY (id_periodo) REFERENCES periodo_academico(id_periodo),
    INDEX idx_estado (estado)
);

-- Tabla Tipos_Evaluacion
CREATE TABLE Tipos_Evaluacion (
    id_tipo_evalu INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    estado CHAR(1) NOT NULL CHECK (estado IN ('A', 'I')), -- A: Activo, I: Inactivo
    usuario_ingreso VARCHAR(50) NOT NULL,
    fecha_ingreso TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_estado (estado)
);

-- Tabla Evaluaciones
CREATE TABLE Evaluacion (
    id_evaluacion INT AUTO_INCREMENT PRIMARY KEY,
    id_materia INT,
    id_examen INT,
    id_tipo_evalu INT,
    porcentaje INT CHECK (porcentaje > 0 AND porcentaje <= 100),
    estado CHAR(1) NOT NULL CHECK (estado IN ('A', 'I')), -- A: Activo, I: Inactivo
    usuario_ingreso VARCHAR(50) NOT NULL,
    fecha_ingreso TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_materia) REFERENCES Materia(id_materia),
    FOREIGN KEY (id_examen) REFERENCES Tipos_Examen(id_examen),
    FOREIGN KEY (id_tipo_evalu) REFERENCES Tipos_Evaluacion(id_tipo_evalu),
    INDEX idx_estado (estado)
);

-- Tabla Evalu_Estudiantes
CREATE TABLE Evalu_Estudiantes (
    id_curso INT,
    id_materia INT,
    id_jornada INT,
    id_his_academico INT,
    id_evaluacion INT,
    nota DECIMAL(5,2),
    nota_porcentaje DECIMAL(5,2),
    supletorio DECIMAL(5,2) DEFAULT NULL,
    resultado CHAR(1) DEFAULT NULL,
    estado CHAR(1) NOT NULL CHECK (estado IN ('A', 'I')), -- A: Activo, I: Inactivo
    usuario_ingreso VARCHAR(50) NOT NULL,
    fecha_ingreso TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_curso, id_materia, id_jornada, id_his_academico, id_evaluacion),
    FOREIGN KEY (id_curso) REFERENCES Curso(id_curso),
    FOREIGN KEY (id_materia) REFERENCES Materia(id_materia),
    FOREIGN KEY (id_jornada) REFERENCES Jornada(id_jornada),
    FOREIGN KEY (id_his_academico) REFERENCES historial_academico(id_his_academico),
    FOREIGN KEY (id_evaluacion) REFERENCES Evaluacion(id_evaluacion),
    INDEX idx_estado (estado)
);


