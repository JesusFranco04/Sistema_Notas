
CREATE DATABASE Sistema_Gestion;

USE Sistema_Gestion;

CREATE TABLE solicitudes (
    id INT AUTO_INCREMENT,
    cedula VARCHAR(20) NOT NULL,
    telefono VARCHAR(10),
    nombres VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    correo_electronico VARCHAR(100),
    rol VARCHAR(20) NOT NULL,
    date_creation VARCHAR(45) NOT NULL,
    PRIMARY KEY (cedula),
    UNIQUE KEY (id)
);


CREATE TABLE soli_profe (
    id INT AUTO_INCREMENT,
    nombres VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    cedula VARCHAR(20) NOT NULL,
    fecha_nacimiento DATE NOT NULL,
    genero VARCHAR(20) NOT NULL,
    direccion VARCHAR(255) NOT NULL,
    telefono VARCHAR(10),
    correo_electronico VARCHAR(100),
    discapacidad VARCHAR(10) NOT NULL,
    rol VARCHAR(20) NOT NULL,
    codigo_de_perfil VARCHAR(1) NOT NULL,
    contrasena VARCHAR(50) NOT NULL,
    archivo VARCHAR(500),
    date_creation VARCHAR(45) NOT NULL,
    PRIMARY KEY (cedula),
    UNIQUE KEY (id)
);

CREATE TABLE `niveles` (
  `id_nivel` INT NOT NULL,
  `nombre` VARCHAR(50) NOT NULL,
  `estado` CHAR(1) NOT NULL,
  `usuario_ingreso` VARCHAR(50) NOT NULL,
  `fecha_ingreso` DATE NOT NULL,
  PRIMARY KEY (`id_nivel`)
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;

CREATE TABLE `subniveles` (
  `id_subnivel` INT NOT NULL,
  `nombre` VARCHAR(80) NOT NULL,
  `abreviatura` VARCHAR(8) NULL DEFAULT NULL,
  `estado` CHAR(1) NOT NULL,
  `usuario_ingreso` VARCHAR(50) NOT NULL,
  `fecha_ingreso` DATE NOT NULL,
  PRIMARY KEY (`id_subnivel`)
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;

CREATE TABLE `paralelos` (
  `id_paralelo` INT NOT NULL,
  `nombre` VARCHAR(1) NOT NULL,
  `estado` CHAR(1) NOT NULL,
  `usuario_ingreso` VARCHAR(50) NOT NULL,
  `fecha_ingreso` DATE NOT NULL,
  PRIMARY KEY (`id_paralelo`)
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;

CREATE TABLE `especialidades` (
  `id_especialidad` INT NOT NULL,
  `nombre` VARCHAR(100) NOT NULL,
  `estado` CHAR(1) NOT NULL,
  `usuario_ingreso` VARCHAR(50) NOT NULL,
  `fecha_ingreso` DATE NOT NULL,
  PRIMARY KEY (`id_especialidad`)
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;



CREATE TABLE `cursos` (
  `id_curso` INT NOT NULL,
  `id_nivel` INT NOT NULL,
  `id_subnivel` INT NOT NULL,
  `id_especialidad` INT NOT NULL,
  `id_paralelo` INT NOT NULL,
  `estado` CHAR(1) NOT NULL,
  `usuario_ingreso` VARCHAR(50) NOT NULL,
  `fecha_ingreso` DATE NOT NULL,
  PRIMARY KEY (`id_curso`),
  FOREIGN KEY (`id_nivel`) REFERENCES `niveles`(`id_nivel`),
  FOREIGN KEY (`id_subnivel`) REFERENCES `subniveles`(`id_subnivel`),
  FOREIGN KEY (`id_especialidad`) REFERENCES `especialidades`(`id_especialidad`),
  FOREIGN KEY (`id_paralelo`) REFERENCES `paralelos`(`id_paralelo`)
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;

CREATE TABLE profesores(
   id INT AUTO_INCREMENT,
    nombres VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    cedula VARCHAR(20) NOT NULL,
    telefono VARCHAR(10),
	correo_electronico VARCHAR(100),
	direccion VARCHAR(255) NOT NULL,
    fecha_nacimiento DATE NOT NULL,
    genero VARCHAR(20) NOT NULL,
    discapacidad VARCHAR(10) NOT NULL,
    rol VARCHAR(1) NOT NULL,
    contrasena VARCHAR(50) NOT NULL,
    archivo VARCHAR(500),
    date_creation VARCHAR(45) NOT NULL,
    PRIMARY KEY (cedula),
    UNIQUE KEY (id)
);

CREATE TABLE padres(
   id INT AUTO_INCREMENT,
    nombres VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    cedula VARCHAR(20) NOT NULL,
    telefono VARCHAR(10),
	correo_electronico VARCHAR(100),
	direccion VARCHAR(255) NOT NULL,
    fecha_nacimiento DATE NOT NULL,
    genero VARCHAR(20) NOT NULL,
    discapacidad VARCHAR(10) NOT NULL,
    rol VARCHAR(1) NOT NULL,
    contrasena VARCHAR(50) NOT NULL,
    date_creation VARCHAR(45) NOT NULL,
    PRIMARY KEY (cedula),
    UNIQUE KEY (id)
);
CREATE TABLE administrador(
   id INT AUTO_INCREMENT,
    nombres VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    cedula VARCHAR(20) NOT NULL,
    telefono VARCHAR(10),
	correo_electronico VARCHAR(100),
	direccion VARCHAR(255) NOT NULL,
    fecha_nacimiento DATE NOT NULL,
    genero VARCHAR(20) NOT NULL,
    discapacidad VARCHAR(10) NOT NULL,
    rol VARCHAR(1) NOT NULL,
    contrasena VARCHAR(50) NOT NULL,
    archivo VARCHAR(500),
    date_creation VARCHAR(45) NOT NULL,
    PRIMARY KEY (cedula),
    UNIQUE KEY (id)
);
CREATE TABLE estudiantes(
   id INT AUTO_INCREMENT,
    nombres VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    cedula VARCHAR(20) NOT NULL,
    telefono VARCHAR(10),
	correo_electronico VARCHAR(100),
	direccion VARCHAR(255) NOT NULL,
    fecha_nacimiento DATE NOT NULL,
    genero VARCHAR(20) NOT NULL,
    discapacidad VARCHAR(10) NOT NULL,
    date_creation VARCHAR(45) NOT NULL,
    PRIMARY KEY (cedula),
    UNIQUE KEY (id)
);
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255),
    apellido VARCHAR(255),
    cedula VARCHAR(255) UNIQUE,
    contrasea VARCHAR(255),
    rol varchar(1)
);
DELIMITER //

CREATE TRIGGER after_insert_administrador
AFTER INSERT ON administrador
FOR EACH ROW
BEGIN
    INSERT INTO usuarios (nombre, apellido, cedula, contraseña, rol)
    VALUES (NEW.nombres, NEW.apellidos, NEW.cedula, NEW.contrasena, 1);
END; //

DELIMITER ;
DELIMITER //

CREATE TRIGGER after_insert_profesor
AFTER INSERT ON profesores
FOR EACH ROW
BEGIN
    INSERT INTO usuarios (nombre, apellido, cedula, contraseña, rol)
    VALUES (NEW.nombres, NEW.apellidos, NEW.cedula, NEW.contrasena, 2);
END; //

DELIMITER ;
DELIMITER //

CREATE TRIGGER after_insert_padres
AFTER INSERT ON padres
FOR EACH ROW
BEGIN
    INSERT INTO usuarios (nombre, apellido, cedula, contraseña, rol)
    VALUES (NEW.nombres, NEW.apellidos, NEW.cedula, NEW.contrasena, 3);
END; //

DELIMITER ;

-- Insertar datos desde la tabla `administrador`
DELIMITER //

CREATE TRIGGER after_update_administrador
AFTER UPDATE ON administrador
FOR EACH ROW
BEGIN
    UPDATE usuarios
    SET nombre = NEW.nombres, apellido = NEW.apellidos, contraseña = NEW.contrasena
    WHERE cedula = NEW.cedula AND rol = 1;
END; //

DELIMITER ;

DELIMITER //

CREATE TRIGGER after_update_profesor
AFTER UPDATE ON profesores
FOR EACH ROW
BEGIN
    UPDATE usuarios
    SET nombre = NEW.nombres, apellido = NEW.apellidos, contraseña = NEW.contrasena
    WHERE cedula = NEW.cedula AND rol = 2;
END; //

DELIMITER ;
DELIMITER //

CREATE TRIGGER after_update_padres
AFTER UPDATE ON padres
FOR EACH ROW
BEGIN
    UPDATE usuarios
    SET nombre = NEW.nombres, apellido = NEW.apellidos, contraseña = NEW.contrasena
    WHERE cedula = NEW.cedula AND rol = 3;
END; //

DELIMITER ;
-- se actualizara si etan eliminando
DELIMITER //

CREATE TRIGGER after_delete_administrador
AFTER DELETE ON administrador
FOR EACH ROW
BEGIN
    DELETE FROM usuarios
    WHERE cedula = OLD.cedula AND rol = 1;
END; //

DELIMITER ;
DELIMITER //

CREATE TRIGGER after_delete_profesor
AFTER DELETE ON profesores
FOR EACH ROW
BEGIN
    DELETE FROM usuarios
    WHERE cedula = OLD.cedula AND rol = 2;
END; //

DELIMITER ;
DELIMITER //

CREATE TRIGGER after_delete_padres
AFTER DELETE ON padres
FOR EACH ROW
BEGIN
    DELETE FROM usuarios
    WHERE cedula = OLD.cedula AND rol = 3;
END; //

DELIMITER ;
