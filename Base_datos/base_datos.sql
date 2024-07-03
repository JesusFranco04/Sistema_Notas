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

CREATE TABLE niveles(
 id INT AUTO_INCREMENT PRIMARY KEY NOT NULL,
 nombre VARCHAR(100) NOT NULL,
 fecha_ingreso DATE NOT NULL
);


CREATE TABLE paralelos (
  id INT AUTO_INCREMENT PRIMARY KEY NOT NULL,
  nombre VARCHAR(1) NOT NULL,
  fecha_ingreso DATE NOT NULL
  ); 

CREATE TABLE jornada (
  id INT AUTO_INCREMENT PRIMARY KEY NOT NULL,
  nombre VARCHAR(100) NOT NULL,
  fecha_ingreso DATE NOT NULL
); 
CREATE TABLE materias(
 id INT AUTO_INCREMENT PRIMARY KEY NOT NULL,
 nombre VARCHAR(100) NOT NULL,
 fecha_ingreso DATE NOT NULL
);

CREATE TABLE especialidades (
  id INT AUTO_INCREMENT PRIMARY KEY NOT NULL,
  nombre VARCHAR(100) NOT NULL,
  fecha_ingreso DATE NOT NULL
); 
CREATE TABLE subniveles (
  id  INT AUTO_INCREMENT PRIMARY KEY NOT NULL,
  nombre VARCHAR(100) NOT NULL,
  abreviatura VARCHAR(4) NULL DEFAULT NULL,
  fecha_ingreso DATE NOT NULL
);

CREATE TABLE periodo(
 id INT AUTO_INCREMENT PRIMARY KEY NOT NULL,
 ano VARCHAR(100) NOT NULL,
 fecha_ingreso DATE NOT NULL
);

CREATE TABLE curso (
  id INT AUTO_INCREMENT PRIMARY KEY NOT NULL,
  profesor_id INT NOT NULL,
  materia_id INT NOT NULL,
  nivel_id INT NOT NULL,
  paralelo_id INT NOT NULL,
  subnivel_id INT NOT NULL,
  especialidad_id INT NOT NULL,
  jornada_id INT NOT NULL,
  periodo_id INT NOT NULL,
  fecha_ingreso DATE NOT NULL,
  FOREIGN KEY (profesor_id) REFERENCES profesores(id),
  FOREIGN KEY (materia_id) REFERENCES materias(id),
  FOREIGN KEY (nivel_id) REFERENCES niveles(id),
  FOREIGN KEY (paralelo_id) REFERENCES paralelos(id),
  FOREIGN KEY (subnivel_id) REFERENCES subniveles(id),
  FOREIGN KEY (especialidad_id) REFERENCES especialidades(id),
  FOREIGN KEY (jornada_id) REFERENCES jornada(id),
  FOREIGN KEY (periodo_id) REFERENCES periodo(id)
);

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
    contraseña VARCHAR(255),
    rol VARCHAR(1)
);

DELIMITER //

CREATE TRIGGER after_insert_administrador
AFTER INSERT ON administrador
FOR EACH ROW
BEGIN
    INSERT INTO usuarios (nombre, apellido, cedula, contraseña, rol)
    VALUES (NEW.nombres, NEW.apellidos, NEW.cedula, NEW.contrasena, '1');
END; //

CREATE TRIGGER after_insert_profesor
AFTER INSERT ON profesores
FOR EACH ROW
BEGIN
    INSERT INTO usuarios (nombre, apellido, cedula, contraseña, rol)
    VALUES (NEW.nombres, NEW.apellidos, NEW.cedula, NEW.contrasena, '2');
END; //

CREATE TRIGGER after_insert_padres
AFTER INSERT ON padres
FOR EACH ROW
BEGIN
    INSERT INTO usuarios (nombre, apellido, cedula, contraseña, rol)
    VALUES (NEW.nombres, NEW.apellidos, NEW.cedula, NEW.contrasena, '3');
END; //

CREATE TRIGGER after_update_administrador
AFTER UPDATE ON administrador
FOR EACH ROW
BEGIN
    UPDATE usuarios
    SET nombre = NEW.nombres, apellido = NEW.apellidos, contraseña = NEW.contrasena
    WHERE cedula = NEW.cedula AND rol = '1';
END; //

CREATE TRIGGER after_update_profesor
AFTER UPDATE ON profesores
FOR EACH ROW
BEGIN
    UPDATE usuarios
    SET nombre = NEW.nombres, apellido = NEW.apellidos, contraseña = NEW.contrasena
    WHERE cedula = NEW.cedula AND rol = '2';
END; //

CREATE TRIGGER after_update_padres
AFTER UPDATE ON padres
FOR EACH ROW
BEGIN
    UPDATE usuarios
    SET nombre = NEW.nombres, apellido = NEW.apellidos, contraseña = NEW.contrasena
    WHERE cedula = NEW.cedula AND rol = '3';
END; //

CREATE TRIGGER after_delete_administrador
AFTER DELETE ON administrador
FOR EACH ROW
BEGIN
    DELETE FROM usuarios
    WHERE cedula = OLD.cedula AND rol = '1';
END; //

CREATE TRIGGER after_delete_profesor
AFTER DELETE ON profesores
FOR EACH ROW
BEGIN
    DELETE FROM usuarios
    WHERE cedula = OLD.cedula AND rol = '2';
END; //

CREATE TRIGGER after_delete_padres
AFTER DELETE ON padres
FOR EACH ROW
BEGIN
    DELETE FROM usuarios
    WHERE cedula = OLD.cedula AND rol = '3';
END; //

DELIMITER ;
