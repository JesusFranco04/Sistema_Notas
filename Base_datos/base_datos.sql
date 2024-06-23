
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

