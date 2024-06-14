
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



