
CREATE DATABASE Sistema_Gestion;

USE Sistema_Gestion;

-- Tabla `perfiles`
CREATE TABLE IF NOT EXISTS `perfiles` (
  `id_perfil` INT NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(50) NOT NULL,
  `estado` CHAR(1) NOT NULL,
  `usuario_ingreso` VARCHAR(50) NOT NULL,
  `fecha_ingreso` DATE NOT NULL,
  PRIMARY KEY (`id_perfil`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_0900_ai_ci;

-- Tabla `usuarios`
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id_usuario` INT NOT NULL AUTO_INCREMENT,
  `usuario` VARCHAR(20) NOT NULL,
  `numero_identificacion` VARCHAR(15) NOT NULL,
  `contrasena` VARCHAR(10) NOT NULL,
  `id_perfil` INT NOT NULL,
  `codigo` VARCHAR(5) NOT NULL,
  `estado` CHAR(1) NOT NULL,
  `usuario_ingreso` VARCHAR(50) NOT NULL,
  `fecha_ingreso` DATE NOT NULL,
  PRIMARY KEY (`id_usuario`),
  FOREIGN KEY (`id_perfil`) REFERENCES `perfiles`(`id_perfil`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_0900_ai_ci;



