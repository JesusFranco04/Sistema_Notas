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
    cedula VARCHAR(10) NOT NULL,
    contraseña VARCHAR(255) NOT NULL, -- Debe ser lo suficientemente larga para almacenar el hash encriptado
    id_rol INT NOT NULL,
    estado CHAR(1) NOT NULL DEFAULT 'A', -- A: Activo, I: Inactivo
    usuario_ingreso VARCHAR(50) NOT NULL, -- Nombre de usuario que inicia sesión
    fecha_ingreso TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, -- Fecha y hora que inicia sesión ese usuario
    UNIQUE KEY (cedula),
    FOREIGN KEY (id_rol) REFERENCES rol(id_rol),
    CHECK (CHAR_LENGTH(cedula) = 10)
);
ALTER TABLE usuario ADD INDEX (usuario_ingreso);


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
	nombre VARCHAR(40) NOT NULL,
    estado CHAR(1) NOT NULL DEFAULT 'A', -- A: Activo, I: Inactivo
    usuario_ingreso VARCHAR(50) NOT NULL, -- Nombre de usuario que crea o modifica
    fecha_ingreso TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP -- Fecha y hora de registro
);

CREATE TABLE administrador ( -- En esta tabla se guardara los datos de los administradores 
    id_administrador INT AUTO_INCREMENT PRIMARY KEY,
    nombres VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    cedula VARCHAR(10) NULL,
    telefono VARCHAR(10)NOT NULL,
    correo_electronico VARCHAR(100) NOT NULL,
    direccion VARCHAR(255) NOT NULL,
    fecha_nacimiento DATE NOT NULL,
    genero ENUM('femenino', 'masculino', 'otros') NOT NULL,
    discapacidad ENUM('si', 'no') NOT NULL,
    id_rol INT NULL,
    estado CHAR(1) DEFAULT 'A',
    usuario_ingreso VARCHAR(50) NULL, -- Nombre de usuario que crea o modifica
    fecha_ingreso TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP, -- Fecha y hora de registro
    UNIQUE KEY (cedula),
    FOREIGN KEY (id_rol) REFERENCES rol(id_rol),
    FOREIGN KEY (usuario_ingreso) REFERENCES usuario(usuario_ingreso)
);

CREATE TABLE profesor ( -- En esta tabla se guardara los datos de los profesores
    id_profesor INT AUTO_INCREMENT PRIMARY KEY,
    nombres VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    cedula VARCHAR(10) NULL,
    telefono VARCHAR(10) NOT NULL,
    correo_electronico VARCHAR(100) NOT NULL,
    direccion VARCHAR(255) NOT NULL,
    fecha_nacimiento DATE NOT NULL,
    genero ENUM('Femenino', 'Masculino', 'Otros') NOT NULL,
    discapacidad ENUM('si', 'no') NOT NULL,
    id_rol INT NULL,
    estado CHAR(1) DEFAULT 'A',
    usuario_ingreso VARCHAR(50) NULL, -- Nombre de usuario que crea o modifica
    fecha_ingreso TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP, -- Fecha y hora de registro
    UNIQUE KEY (cedula),
    FOREIGN KEY (id_rol) REFERENCES rol(id_rol),
    FOREIGN KEY (usuario_ingreso) REFERENCES usuario(usuario_ingreso)
);

CREATE TABLE padre ( -- En esta tabla se guardara los datos de padre de familia
    id_padre INT AUTO_INCREMENT PRIMARY KEY,
    nombres VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    cedula VARCHAR(10) NULL,
    parentesco VARCHAR(50) NOT NULL, -- Tipo de parentezco del padre con el estudiante (madre, padre, tía, tío, abuelo, etc.)
    telefono VARCHAR(10) NOT NULL,
    correo_electronico VARCHAR(100),
    direccion VARCHAR(255) NOT NULL NOT NULL,
    fecha_nacimiento DATE NOT NULL,
    genero ENUM('Femenino', 'Masculino', 'Otros') NOT NULL,
    discapacidad ENUM('si', 'no') NOT NULL,
    id_rol INT NULL,
    estado CHAR(1) DEFAULT 'A',
    usuario_ingreso VARCHAR(50) NULL, -- Nombre de usuario que crea o modifica
    fecha_ingreso TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP, -- Fecha y hora de registro
    UNIQUE KEY (cedula),
    FOREIGN KEY (id_rol) REFERENCES rol(id_rol),
    FOREIGN KEY (usuario_ingreso) REFERENCES usuario(usuario_ingreso)
);

CREATE TABLE estudiante ( -- En esta tabla se guardara los datos de los estudiantes
    id_estudiante INT AUTO_INCREMENT PRIMARY KEY,
    nombres VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    cedula VARCHAR(10) NOT NULL,
    telefono VARCHAR(10),
    correo_electronico VARCHAR(100),
    direccion VARCHAR(255),
    fecha_nacimiento DATE NOT NULL,
    genero ENUM('Femenino', 'Masculino', 'Otros') NOT NULL,
    discapacidad ENUM('si', 'no') NOT NULL,
    id_padre INT NOT NULL,
    estado CHAR(1) NOT NULL DEFAULT 'A', -- A: Activo, I: Inactivo
    usuario_ingreso VARCHAR(50) NOT NULL, -- Nombre de usuario que crea o modifica
    fecha_ingreso TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, -- Fecha y hora de registro
    UNIQUE KEY (cedula),
    FOREIGN KEY (id_padre) REFERENCES padre(id_padre),
    INDEX (id_padre), -- Índice para mejorar la velocidad de las consultas relacionadas con padres
    INDEX (estado) -- Índice para mejorar la velocidad de las consultas según el estado del estudiante
);

CREATE TABLE curso ( -- En esta tabla se guardara los datos de la siguiente manera: Primer Bachillerato G BTI Electricidad
    id_curso INT AUTO_INCREMENT PRIMARY KEY,
    id_nivel INT NOT NULL,
	id_paralelo INT NOT NULL,
    id_subnivel INT NOT NULL,
    id_especialidad INT NOT NULL,
	estado CHAR(1) NOT NULL DEFAULT 'A', -- A: Activo, I: Inactivo
    usuario_ingreso VARCHAR(50) NOT NULL, -- Nombre de usuario que crea o modifica
    fecha_ingreso TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, -- Fecha y hora de registro
    FOREIGN KEY (id_nivel) REFERENCES nivel(id_nivel),
	FOREIGN KEY (id_paralelo) REFERENCES paralelo(id_paralelo),
    FOREIGN KEY (id_subnivel) REFERENCES subnivel(id_subnivel),
    FOREIGN KEY (id_especialidad) REFERENCES especialidad(id_especialidad)
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


/* Triggers para administrador - AFTER INSERT*/
DELIMITER //

CREATE TRIGGER after_insert_administrador
AFTER INSERT ON administrador
FOR EACH ROW
BEGIN
    -- Insertar en la tabla de usuario
    INSERT INTO usuario (cedula, contraseña, id_rol, estado, usuario_ingreso)
    VALUES (NEW.cedula, NEW.contraseña, 1, NEW.estado, NEW.usuario_ingreso);
END;
//

DELIMITER ;

/* Triggers para administrador - AFTER UPDATE*/
DELIMITER //

CREATE TRIGGER after_update_administrador
AFTER UPDATE ON administrador
FOR EACH ROW
BEGIN
    -- Actualizar en la tabla de usuario
    UPDATE usuario
    SET usuario_ingreso = NEW.usuario_ingreso, contraseña = NEW.contraseña
    WHERE id_rol = 1 AND cedula = NEW.cedula;
END;
//

DELIMITER ;

/* Triggers para administrador - AFTER DELETE*/
DELIMITER //

CREATE TRIGGER after_delete_administrador
AFTER DELETE ON administrador
FOR EACH ROW
BEGIN
    -- No se realiza eliminación, se puede implementar lógica para inactivar
    -- UPDATE usuario SET estado = 'I' WHERE id_rol = 1 AND cedula = OLD.cedula;
END;
//

DELIMITER ;



/* Triggers para profesor - AFTER INSERT*/
DELIMITER //

CREATE TRIGGER after_insert_profesor
AFTER INSERT ON profesor
FOR EACH ROW
BEGIN
    -- Insertar en la tabla de usuario
    INSERT INTO usuario (cedula, contraseña, id_rol, estado, usuario_ingreso)
    VALUES (NEW.cedula, NEW.contraseña, 2, NEW.estado, NEW.usuario_ingreso);
END;
//

DELIMITER ;

/* Triggers para profesor - AFTER UPDATE*/
DELIMITER //

CREATE TRIGGER after_update_profesor
AFTER UPDATE ON profesor
FOR EACH ROW
BEGIN
    -- Actualizar en la tabla de usuario
    UPDATE usuario
    SET usuario_ingreso = NEW.usuario_ingreso, contraseña = NEW.contraseña
    WHERE id_rol = 2 AND cedula = NEW.cedula;
END;
//

DELIMITER ;

/* Triggers para profesor - AFTER DELETE*/
DELIMITER //

CREATE TRIGGER after_delete_profesor
AFTER DELETE ON profesor
FOR EACH ROW
BEGIN
    -- No se realiza eliminación, se puede implementar lógica para inactivar
    -- UPDATE usuario SET estado = 'I' WHERE id_rol = 2 AND cedula = OLD.cedula;
END;
//

DELIMITER ;


/*Triggers para padre - AFTER INSERT*/
DELIMITER //

CREATE TRIGGER after_insert_padre
AFTER INSERT ON padre
FOR EACH ROW
BEGIN
    -- Insertar en la tabla de usuario
    INSERT INTO usuario (cedula, contraseña, id_rol, estado, usuario_ingreso)
    VALUES (NEW.cedula, NEW.contraseña, 3, NEW.estado, NEW.usuario_ingreso);
END;
//

DELIMITER ;

/* Triggers para padre - AFTER UPDATE*/
DELIMITER //

CREATE TRIGGER after_update_padre
AFTER UPDATE ON padre
FOR EACH ROW
BEGIN
    -- Actualizar en la tabla de usuario
    UPDATE usuario
    SET usuario_ingreso = NEW.usuario_ingreso, contraseña = NEW.contraseña
    WHERE id_rol = 3 AND cedula = NEW.cedula;
END;
//

DELIMITER ;

/* Triggers para padre - AFTER DELETE*/
DELIMITER //

CREATE TRIGGER after_delete_padre
AFTER DELETE ON padre
FOR EACH ROW
BEGIN
    -- No se realiza eliminación, se puede implementar lógica para inactivar
    -- UPDATE usuario SET estado = 'I' WHERE id_rol = 3 AND cedula = OLD.cedula;
END;
//

DELIMITER ;


/*Triggers para estudiante - AFTER INSERT*/
DELIMITER //

CREATE TRIGGER after_insert_estudiante
AFTER INSERT ON estudiante
FOR EACH ROW
BEGIN
    -- No se crea usuario para el estudiante, ya que será el padre quien tenga acceso
    -- INSERT INTO usuario (cedula, contraseña, id_rol, estado, usuario_ingreso)
    -- VALUES (NEW.cedula, NEW.contraseña, 12, NEW.estado, NEW.usuario_ingreso);
END;
//

DELIMITER ;

/*Triggers para estudiante - AFTER UPDATE*/
DELIMITER //

CREATE TRIGGER after_update_estudiante
AFTER UPDATE ON estudiante
FOR EACH ROW
BEGIN
    -- No se actualiza usuario para el estudiante, ya que será el padre quien tenga acceso
    -- UPDATE usuario
    -- SET usuario_ingreso = NEW.usuario_ingreso, contraseña = NEW.contraseña
    -- WHERE id_rol = 12 AND cedula = NEW.cedula;
END;
//

DELIMITER ;

/*Triggers para estudiante - AFTER DELETE*/
DELIMITER //

CREATE TRIGGER after_delete_estudiante
AFTER DELETE ON estudiante
FOR EACH ROW
BEGIN
    -- No se realiza eliminación, se puede implementar lógica para inactivar
    -- UPDATE usuario SET estado = 'I' WHERE id_rol = 12 AND cedula = OLD.cedula;
END;
//

DELIMITER ;



