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
    id_his_academico INT,
    estado CHAR(1) NOT NULL,
    usuario_ingreso VARCHAR(50) NOT NULL,
    fecha_ingreso TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_rol) REFERENCES rol(id_rol),
    FOREIGN KEY (id_his_academico) REFERENCES historial_academico(id_his_academico)
);


CREATE TABLE nivel ( -- En esta tabla se guardara los datos por ejemplo: Octavo, Noveno, Decimo, Primero de Bachillerato, Segundo de Bachillerato, Tercero de Bachillerato.
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
    fecha_ingreso TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, -- Fecha y hora de registro
    fecha_cierre_programada DATETIME NULL
);

-- Crear la tabla de registro event_log --- Esto sirve para programar la fecha de cierre en Ciclos Academicos 
CREATE TABLE IF NOT EXISTS event_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    message VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

DELIMITER //

-- Crear el nuevo evento con el registro en event_log
CREATE EVENT IF NOT EXISTS evento_actualizar_estado
ON SCHEDULE EVERY 1 HOUR
DO
BEGIN
    INSERT INTO event_log (message) VALUES ('Evento ejecutado');
    UPDATE historial_academico
    SET estado = 'I'
    WHERE fecha_cierre_programada <= NOW() AND estado = 'A';
END //

DELIMITER ;

-- Activar el event_scheduler
SET GLOBAL event_scheduler = ON;


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
    discapacidad BOOLEAN NOT NULL,  
    tipo_discapacidad VARCHAR(100) NULL,
    CHECK (tipo_discapacidad IN ('visual', 'auditiva', 'intelectual', 'motora', 'psicosocial', 'múltiple', 'habla_comunicacion', 'sensorial', 'enfermedades_cronicas')),
    porcentaje_discapacidad TINYINT UNSIGNED NULL,
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
    discapacidad BOOLEAN NOT NULL,  -- Cambiado a BOOLEAN
    tipo_discapacidad VARCHAR(100) NULL,  -- Cambiado a VARCHAR con CHECK
    CHECK (tipo_discapacidad IN ('visual', 'auditiva', 'intelectual', 'motora', 'psicosocial', 'múltiple', 'habla_comunicacion', 'sensorial', 'enfermedades_cronicas')),
    porcentaje_discapacidad TINYINT UNSIGNED NULL,
    id_usuario INT NOT NULL,
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario)
);


CREATE TABLE padre (
    id_padre INT AUTO_INCREMENT PRIMARY KEY,
    nombres VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    cedula VARCHAR(10) NOT NULL UNIQUE,
    parentesco ENUM('padre', 'madre', 'hermano/a mayor', 'familiar', 'otro') NOT NULL,  
    parentesco_otro VARCHAR(50) NULL,  
    telefono VARCHAR(10) NOT NULL,
    correo_electronico VARCHAR(100) NOT NULL,
    direccion VARCHAR(255) NOT NULL,
    fecha_nacimiento DATE NOT NULL,
    genero ENUM('femenino', 'masculino', 'otros') NOT NULL,
    discapacidad BOOLEAN NOT NULL,  -- Indica si tiene alguna discapacidad
    tipo_discapacidad VARCHAR(100) NULL,
    CHECK (tipo_discapacidad IN ('visual', 'auditiva', 'intelectual', 'motora', 'psicosocial', 'múltiple', 'habla_comunicacion', 'sensorial', 'enfermedades_cronicas')),
    porcentaje_discapacidad TINYINT UNSIGNED NULL,
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
    discapacidad BOOLEAN NOT NULL,  -- Indica si tiene alguna discapacidad
    tipo_discapacidad VARCHAR(100) NULL,
    CHECK (tipo_discapacidad IN ('visual', 'auditiva', 'intelectual', 'motora', 'psicosocial', 'múltiple', 'habla_comunicacion', 'sensorial', 'enfermedades_cronicas')),
    porcentaje_discapacidad TINYINT UNSIGNED NULL,
    estado_calificacion CHAR(1) NOT NULL DEFAULT 'P', -- P: Pendiente, A: Aprobado, R: Reprobado
    estado CHAR(1) NOT NULL DEFAULT 'A', -- A: Activo, I: Inactivo
    id_nivel INT NOT NULL, -- Nivel en el que está matriculado el estudiante
    id_subnivel INT NOT NULL,
    id_especialidad INT NOT NULL,
    id_paralelo INT NOT NULL, -- Paralelo en el que está matriculado el estudiante
    id_jornada INT NOT NULL, -- Jornada en la que está matriculado el estudiante
    id_his_academico INT NOT NULL, -- Año académico en el que está matriculado el estudiante
    fecha_ingreso TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, -- Fecha y hora de registro
    FOREIGN KEY (id_nivel) REFERENCES nivel(id_nivel),
    FOREIGN KEY (id_subnivel) REFERENCES subnivel(id_subnivel),
    FOREIGN KEY (id_especialidad) REFERENCES especialidad(id_especialidad),
    FOREIGN KEY (id_paralelo) REFERENCES paralelo(id_paralelo),
    FOREIGN KEY (id_jornada) REFERENCES jornada(id_jornada),
    FOREIGN KEY (id_his_academico) REFERENCES historial_academico(id_his_academico)
);


CREATE TABLE padre_x_estudiante (
    id_padre INT,
    id_estudiante INT,
    PRIMARY KEY (id_padre, id_estudiante),
    FOREIGN KEY (id_padre) REFERENCES padre(id_padre),
    FOREIGN KEY (id_estudiante) REFERENCES estudiante(id_estudiante)
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

CREATE TABLE registro_nota (
    id_estudiante INT NOT NULL,
    id_curso INT NOT NULL,
    id_materia INT NOT NULL,
    id_periodo INT NOT NULL,
    id_his_academico INT NOT NULL,
    nota1_primer_parcial FLOAT DEFAULT NULL,
    nota2_primer_parcial FLOAT DEFAULT NULL,
    examen_primer_parcial FLOAT DEFAULT NULL,
    nota1_segundo_parcial FLOAT DEFAULT NULL,
    nota2_segundo_parcial FLOAT DEFAULT NULL,
    examen_segundo_parcial FLOAT DEFAULT NULL,
    PRIMARY KEY (id_estudiante, id_curso, id_materia, id_periodo, id_his_academico),
    FOREIGN KEY (id_estudiante) REFERENCES estudiante(id_estudiante),
    FOREIGN KEY (id_curso) REFERENCES curso(id_curso),
    FOREIGN KEY (id_materia) REFERENCES materia(id_materia),
    FOREIGN KEY (id_periodo) REFERENCES periodo_academico(id_periodo),
    FOREIGN KEY (id_his_academico) REFERENCES historial_academico(id_his_academico)
);


CREATE TABLE calificacion (
    id_estudiante INT NOT NULL,
    id_curso INT NOT NULL,
    id_materia INT NOT NULL,
    id_his_academico INT NOT NULL,
    promedio_primer_quimestre FLOAT,
    promedio_segundo_quimestre FLOAT,
    nota_final FLOAT,
    supletorio FLOAT,
    estado_calificacion CHAR(1),
    PRIMARY KEY (id_estudiante, id_curso, id_materia, id_his_academico),
    FOREIGN KEY (id_estudiante) REFERENCES estudiante(id_estudiante),
    FOREIGN KEY (id_curso) REFERENCES curso(id_curso),
    FOREIGN KEY (id_materia) REFERENCES materia(id_materia),
    FOREIGN KEY (id_his_academico) REFERENCES historial_academico(id_his_academico)
);

CREATE TABLE historial_log (   -- Esto es la tabla para la página de Perfil (PERFIL ADMINISTRADOR)
    id_actividad INT AUTO_INCREMENT PRIMARY KEY,  -- ID de la actividad en el historial
    id_usuario INT NULL,                         -- Permitimos que el id_usuario sea NULL
    tabla VARCHAR(50) NOT NULL,                   -- Nombre de la tabla afectada
    id_registro INT NOT NULL,                     -- ID del registro afectado
    accion VARCHAR(50) NOT NULL,                  -- Tipo de acción (Creación, Modificación)
    descripcion TEXT NOT NULL,                    -- Descripción de la acción
    fecha_actividad TIMESTAMP DEFAULT CURRENT_TIMESTAMP,  -- Fecha y hora de la actividad
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario)  -- Relación con la tabla 'usuario'
);


DELIMITER $$

CREATE PROCEDURE log_historial (
    IN p_cedula_usuario VARCHAR(10),  -- Cedula para identificar al usuario (administrador)
    IN p_tabla VARCHAR(50),           -- Nombre de la tabla afectada
    IN p_id_registro INT,             -- ID del registro afectado
    IN p_accion VARCHAR(50),          -- Tipo de acción (Creación, Modificación, etc.)
    IN p_descripcion TEXT            -- Descripción de la acción
)
BEGIN
    DECLARE user_id INT;

    -- Intentar obtener el ID del usuario basado en la cédula (administrador)
    SET user_id = (SELECT id_usuario FROM usuario WHERE cedula = p_cedula_usuario LIMIT 1);

    -- Si no se encuentra el usuario (superusuario), se asume que es un superusuario
    IF user_id IS NULL THEN
        -- Llamamos al procedimiento específico para superusuarios (sin registrar id_usuario)
        CALL log_historial_superusuario(p_cedula_usuario, p_tabla, p_id_registro, p_accion, p_descripcion);
    ELSE
        -- Insertar el registro para el administrador
        INSERT INTO historial_log (id_usuario, tabla, id_registro, accion, descripcion)
        VALUES (user_id, p_tabla, p_id_registro, p_accion, p_descripcion);
    END IF;
END $$

DELIMITER ;

DELIMITER $$

CREATE PROCEDURE log_historial_superusuario (
    IN p_cedula_usuario VARCHAR(10),  -- Cedula para identificar al superusuario (no se usa en este caso)
    IN p_tabla VARCHAR(50),           -- Nombre de la tabla afectada
    IN p_id_registro INT,             -- ID del registro afectado
    IN p_accion VARCHAR(50),          -- Tipo de acción (Creación, Modificación, etc.)
    IN p_descripcion TEXT            -- Descripción de la acción
)
BEGIN
    -- Insertar el registro para el superusuario sin asociar id_usuario ni cedula
    INSERT INTO historial_log (tabla, id_registro, accion, descripcion)
    VALUES (p_tabla, p_id_registro, p_accion, p_descripcion);
END $$

DELIMITER ;

DELIMITER $$

CREATE TRIGGER after_usuario_insert
AFTER INSERT ON usuario
FOR EACH ROW
BEGIN
    -- Llamamos al procedimiento centralizado para registrar la acción, ya sea superusuario o administrador
    CALL log_historial(NEW.usuario_ingreso, 'usuario', NEW.id_usuario, 'Creación', CONCAT('Se ha creado un nuevo usuario: ', NEW.cedula));
END $$

DELIMITER ;

DELIMITER $$

CREATE TRIGGER after_usuario_update
AFTER UPDATE ON usuario
FOR EACH ROW
BEGIN
    -- Llamamos al procedimiento centralizado para registrar la acción
    CALL log_historial(NEW.usuario_ingreso, 'usuario', NEW.id_usuario, 'Modificación', CONCAT('El usuario ha sido actualizado: ', OLD.cedula, ' a ', NEW.cedula));
END $$

DELIMITER ;

DELIMITER $$

-- Trigger para registrar la inserción de un nuevo curso
CREATE TRIGGER after_curso_insert
AFTER INSERT ON curso
FOR EACH ROW
BEGIN
    CALL log_historial(NEW.usuario_ingreso, 'curso', NEW.id_curso, 'Creación', 
        CONCAT('Se ha creado un nuevo curso: Profesor ID ', NEW.id_profesor, ', Materia ID ', NEW.id_materia));
END $$
DELIMITER ;

DELIMITER $$

-- Trigger para registrar la actualización de un curso
CREATE TRIGGER after_curso_update
AFTER UPDATE ON curso
FOR EACH ROW
BEGIN
    CALL log_historial(NEW.usuario_ingreso, 'curso', NEW.id_curso, 'Modificación', 
        CONCAT('El curso ha sido actualizado: Profesor ID ', OLD.id_profesor, ', Materia ID ', OLD.id_materia, 
        ' a Profesor ID ', NEW.id_profesor, ', Materia ID ', NEW.id_materia));
END $$
DELIMITER ;

DELIMITER $$

CREATE TRIGGER after_nivel_insert
AFTER INSERT ON nivel
FOR EACH ROW
BEGIN
    -- Llamamos al procedimiento centralizado para registrar la acción
    CALL log_historial(NEW.usuario_ingreso, 'nivel', NEW.id_nivel, 'Creación', CONCAT('Se ha creado un nuevo nivel: ', NEW.nombre));
END $$

DELIMITER ;

DELIMITER $$

-- Trigger para registrar la inserción de un nuevo subnivel
CREATE TRIGGER after_subnivel_insert
AFTER INSERT ON subnivel
FOR EACH ROW
BEGIN
    CALL log_historial(NEW.usuario_ingreso, 'subnivel', NEW.id_subnivel, 'Creación', 
        CONCAT('Se ha creado un nuevo subnivel: ', NEW.abreviatura));
END $$
DELIMITER ;

DELIMITER $$

-- Trigger para registrar la inserción de un nuevo paralelo
CREATE TRIGGER after_paralelo_insert
AFTER INSERT ON paralelo
FOR EACH ROW
BEGIN
    CALL log_historial(NEW.usuario_ingreso, 'paralelo', NEW.id_paralelo, 'Creación', 
        CONCAT('Se ha creado un nuevo paralelo: ', NEW.nombre));
END $$
DELIMITER ;

DELIMITER $$

-- Trigger para registrar la inserción de una nueva especialidad
CREATE TRIGGER after_especialidad_insert
AFTER INSERT ON especialidad
FOR EACH ROW
BEGIN
    CALL log_historial(NEW.usuario_ingreso, 'especialidad', NEW.id_especialidad, 'Creación', 
        CONCAT('Se ha creado una nueva especialidad: ', NEW.nombre));
END $$
DELIMITER ;

DELIMITER $$

-- Trigger para registrar la inserción de una nueva jornada
CREATE TRIGGER after_jornada_insert
AFTER INSERT ON jornada
FOR EACH ROW
BEGIN
    CALL log_historial(NEW.usuario_ingreso, 'jornada', NEW.id_jornada, 'Creación', 
        CONCAT('Se ha creado una nueva jornada: ', NEW.nombre));
END $$
DELIMITER ;

DELIMITER $$

-- Trigger para registrar la inserción de una nueva materia
CREATE TRIGGER after_materia_insert
AFTER INSERT ON materia
FOR EACH ROW
BEGIN
    CALL log_historial(NEW.usuario_ingreso, 'materia', NEW.id_materia, 'Creación', 
        CONCAT('Se ha creado una nueva materia: ', NEW.nombre));
END $$
DELIMITER ;

DELIMITER $$

-- Trigger para registrar la inserción de un nuevo período académico
CREATE TRIGGER after_periodo_academico_insert
AFTER INSERT ON periodo_academico
FOR EACH ROW
BEGIN
    CALL log_historial(NEW.usuario_ingreso, 'periodo_academico', NEW.id_periodo, 'Creación', 
        CONCAT('Se ha creado un nuevo período académico: ', NEW.nombre));
END $$
DELIMITER ;

DELIMITER $$

-- Trigger para registrar la inserción de un nuevo historial académico
CREATE TRIGGER after_historial_academico_insert
AFTER INSERT ON historial_academico
FOR EACH ROW
BEGIN
    CALL log_historial(NEW.usuario_ingreso, 'historial_academico', NEW.id_his_academico, 'Creación', 
        CONCAT('Se ha creado un nuevo año lectivo: ', NEW.año));
END $$
DELIMITER ;


-- Índices compuestos para búsquedas por filtros
CREATE INDEX idx_estudiante_filtros ON estudiante (id_his_academico, id_nivel, id_paralelo, id_especialidad, id_jornada);

-- Índice para búsquedas por nombre/apellidos (si son búsquedas frecuentes)
CREATE INDEX idx_estudiante_nombres_apellidos ON estudiante (nombres, apellidos);

-- Índices compuestos para búsquedas por estudiante y estado de calificación
CREATE INDEX idx_calificacion_estudiante_his ON calificacion (id_estudiante, id_his_academico, estado_calificacion);

-- Índice para búsquedas relacionadas con materias aprobadas/reprobadas
CREATE INDEX idx_calificacion_materia_estado ON calificacion (id_materia, estado_calificacion);

-- Índice compuesto para búsquedas frecuentes (estudiante + curso/materia)
CREATE INDEX idx_calificacion_estudiante_materia ON calificacion (id_estudiante, id_materia);

-- Índice para búsquedas por año académico
CREATE INDEX idx_historial_anio ON historial_academico (año);

-- Índice para filtrar por estado (años activos/inactivos)
CREATE INDEX idx_historial_estado ON historial_academico (estado);

-- Índice para consultas por usuario que registró
CREATE INDEX idx_historial_usuario ON historial_academico (usuario_ingreso);

-- Índice para búsquedas por fechas de cierre programadas
CREATE INDEX idx_historial_fecha_cierre ON historial_academico (fecha_cierre_programada);

-- Agregar índices en la tabla padre
ALTER TABLE padre 
ADD INDEX idx_cedula (cedula),
ADD INDEX idx_genero (genero),
ADD INDEX idx_discapacidad (discapacidad),
ADD INDEX idx_parentesco (parentesco);

-- Agregar índices en la tabla estudiante
ALTER TABLE estudiante 
ADD INDEX idx_estado (estado),
ADD INDEX idx_apellidos (apellidos);
