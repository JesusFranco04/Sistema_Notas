<?php
// Iniciar la sesión para obtener datos del usuario que está logueado
session_start();
// Incluir el archivo de configuración para conectarte a la base de datos
include('../../config.php');
date_default_timezone_set('America/Guayaquil'); // Establecer zona horaria a Ecuador

define('ROL_ADMIN', 'Administrador');
define('ROL_SUPER', 'Superusuario');

if (!isset($_SESSION['cedula']) || !isset($_SESSION['rol']) || 
    !in_array($_SESSION['rol'], [ROL_ADMIN, ROL_SUPER], true)) {
    session_destroy();
    header("Location: http://localhost/sistema_notas/login.php");
    exit();
}

// Variables para mensajes de error y éxito
$error_message = '';
$success_message = '';

// Función para obtener el año lectivo
function obtenerAnioLectivo($conn) {
    global $error_message;
    // Consulta para obtener el año académico activo más reciente
    $sqlActivo = "
    SELECT id_his_academico, año 
    FROM historial_academico 
    WHERE estado = 'A' AND fecha_cierre_programada IS NULL 
    ORDER BY fecha_ingreso DESC  
    LIMIT 1;";

    $resultActivo = $conn->query($sqlActivo);

    if ($resultActivo && $resultActivo->num_rows > 0) {
        $year_record = $resultActivo->fetch_assoc();
        return $year_record['año'];
    } else {
        $error_message = 'No hay un año académico funcionando. Registre un año lectivo antes de agregar un usuario.';
        return 'No se detectó ningún año lectivo.';
    }
}

// Llamada a la función para obtener el año lectivo
$active_year = obtenerAnioLectivo($conn);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && empty($error_message)) {
    // Validación si ya existe el usuario por cédula en la tabla usuario
    $cedula = trim($_POST['cedula']);
    $sql_check_cedula = "SELECT * FROM usuario WHERE cedula = ?";
    $stmt_check_cedula = $conn->prepare($sql_check_cedula);
    $stmt_check_cedula->bind_param('s', $cedula);
    $stmt_check_cedula->execute();
    $result_check_cedula = $stmt_check_cedula->get_result();

    if ($result_check_cedula->num_rows > 0) {
        $error_message = 'Este usuario ya está registrado. Por favor, verifica la información o usa una cédula diferente.';
    } else {
        // Validación por cédula y nombres en las tablas administrador, profesor y padre
        $nombres = trim($_POST['nombres']);
        $apellidos = trim($_POST['apellidos']);

                // Función para normalizar texto (minúsculas, sin tildes, sin espacios extra)
                function normalizarTexto($texto) {
                    $texto = trim($texto);
                    $texto = strtolower($texto);
                    $texto = strtr($texto, 'ÁÉÍÓÚÜÑáéíóúüñ', 'AEIOUUNaeiouun'); // Remueve tildes
                    return $texto;
                }
        
        $nombres_normalizados = normalizarTexto($nombres);
        $apellidos_normalizados = normalizarTexto($apellidos);
        
        // Consulta para verificar duplicados en administrador, profesor y padre
        $sql_check_roles = "
        SELECT tipo
        FROM (
            SELECT 'administrador' AS tipo, cedula, nombres, apellidos FROM administrador
            UNION ALL
            SELECT 'profesor', cedula, nombres, apellidos FROM profesor
            UNION ALL
            SELECT 'padre', cedula, nombres, apellidos FROM padre
        ) AS roles
        WHERE cedula = ? 
           OR (LOWER(TRIM(REPLACE(nombres, ' ', ''))) = LOWER(?) 
                AND LOWER(TRIM(REPLACE(apellidos, ' ', ''))) = LOWER(?));
        ";

        $stmt_check_roles = $conn->prepare($sql_check_roles);
        $stmt_check_roles->bind_param('sss', $cedula, $nombres_normalizados, $apellidos_normalizados);
        $stmt_check_roles->execute();
        $result_check_roles = $stmt_check_roles->get_result();

        if ($result_check_roles->num_rows > 0) {
            $row = $result_check_roles->fetch_assoc();
            $error_message = 'El usuario con esta cédula o nombre ya está registrado como: ' . $row['tipo'];  

        } else {
            // Validación del correo electrónico
            $correo_electronico = $_POST['correo_electronico'];
            
            if (!filter_var($correo_electronico, FILTER_VALIDATE_EMAIL)) {
                $error_message = 'El correo electrónico no tiene un formato válido.';
            } else {
                // Validar los nuevos campos
                if (empty($error_message)) {
                    $discapacidad = $_POST['discapacidad'];

                    // Si discapacidad es 0, tipo_discapacidad y porcentaje_discapacidad deben ser NULL
                    if ($discapacidad == 0) {
                        $tipo_discapacidad = null;
                        $porcentaje_discapacidad = null;
                    } else {
                        // Si discapacidad es 1, validar tipo_discapacidad y porcentaje_discapacidad
                        $valid_disabilities = ['visual', 'auditiva', 'intelectual', 'motora', 'psicosocial', 'múltiple', 'habla_comunicacion', 'sensorial', 'enfermedades_cronicas'];
                        $tipo_discapacidad_array = isset($_POST['tipo_discapacidad']) ? $_POST['tipo_discapacidad'] : [];

                        // Validar que solo se seleccione una discapacidad
                        if (count($tipo_discapacidad_array) > 1) {
                            $error_message = 'Solamente está permitido seleccionar uno de los tipos de discapacidad.';
                        } else {
                            $tipo_discapacidad = implode(',', array_intersect($valid_disabilities, $tipo_discapacidad_array));
                            $porcentaje_discapacidad = ($_POST['porcentaje_discapacidad']) ? $_POST['porcentaje_discapacidad'] : null;
                            
                            // Validar porcentaje de discapacidad
                            if ($porcentaje_discapacidad < 1 || $porcentaje_discapacidad > 100) {
                                $error_message = 'El porcentaje de discapacidad debe estar entre 1 y 100.';
                            }

                            if (empty($tipo_discapacidad) || empty($porcentaje_discapacidad)) {
                                $error_message = 'Debe seleccionar una discapacidad y un porcentaje válido.';
                            }
                        }
                    }

                    // Validación de la dirección
                    $direccion = $_POST['direccion'];
                    $regex_direccion = '/^(?=.*[A-Za-zÁÉÍÓÚáéíóúÑñ])(?=.*\d)[A-Za-zÁÉÍÓÚáéíóúÑñ0-9.,#°\-\s]{5,255}$/';

                    if (!preg_match($regex_direccion, $direccion)) {
                        $error_message = 'La dirección debe contener al menos una palabra y un número, y solo puede incluir letras, números, espacios, puntos, comas, guiones y símbolos como # y °.';
                    } else {

                    if (empty($error_message)) {
                        // Procede con la inserción en la base de datos
                        $nombres = $_POST['nombres'];
                        $apellidos = $_POST['apellidos'];
                        $telefono = $_POST['telefono'];
                        $direccion = $_POST['direccion'];
                        $fecha_nacimiento = $_POST['fecha_nacimiento'];
                        $genero = $_POST['genero'];
                        $id_rol = $_POST['id_rol'];
                        $contraseña = $_POST['contraseña'];
                        $estado = 'A';
                        $usuario_ingreso = $_SESSION['cedula'];
                        $fecha_ingreso = date('Y-m-d H:i:s');

                        // Verifica si el año académico fue obtenido correctamente
                        if ($active_year == 'No se detectó ningún año lectivo.') {
                            $error_message = 'No se puede registrar el usuario porque no hay un año académico activo.';
                        } else {
                            // Ahora puedes utilizar el año académico activo para la inserción
                            $sql_his_academico = "SELECT id_his_academico FROM historial_academico WHERE año = ?";
                            $stmt_his_academico = $conn->prepare($sql_his_academico);
                            $stmt_his_academico->bind_param('s', $active_year);
                            $stmt_his_academico->execute();
                            $result_his_academico = $stmt_his_academico->get_result();
                            
                            if ($result_his_academico->num_rows > 0) {
                                $year_record = $result_his_academico->fetch_assoc();
                                $id_his_academico = $year_record['id_his_academico']; // Asignar el ID del año académico
                            } else {
                                $error_message = 'No se pudo obtener el ID del año académico para el año activo.';
                            }
                        }
                    }

                        // Si no hay error, continúa con la inserción en la base de datos
                        if (empty($error_message)) {
                            $conn->begin_transaction();
                            try {
                                // Insertar en la tabla usuario con el año académico activo
                                $sql_usuario = "INSERT INTO usuario (cedula, contraseña, id_rol, id_his_academico, estado, usuario_ingreso, fecha_ingreso) 
                                                VALUES (?, ?, ?, ?, ?, ?, ?)";
                                $stmt_usuario = $conn->prepare($sql_usuario);
                                $stmt_usuario->bind_param('ssiisss', $cedula, $contraseña, $id_rol, $id_his_academico, $estado, $usuario_ingreso, $fecha_ingreso);
                                $stmt_usuario->execute();
                                $id_usuario = $conn->insert_id;

                                // Insertar en la tabla específica del rol
                                if ($id_rol == 1) {
                                    // Insertar administrador
                                    $sql_admin = "INSERT INTO administrador (nombres, apellidos, cedula, telefono, correo_electronico, direccion, fecha_nacimiento, genero, discapacidad, tipo_discapacidad, porcentaje_discapacidad, id_usuario) 
                                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                                    $stmt_admin = $conn->prepare($sql_admin);
                                    $stmt_admin->bind_param('sssssssssssi', $nombres, $apellidos, $cedula, $telefono, $correo_electronico, $direccion, $fecha_nacimiento, $genero, $discapacidad, $tipo_discapacidad, $porcentaje_discapacidad, $id_usuario);
                                    $stmt_admin->execute();
                                } elseif ($id_rol == 2) {
                                    // Insertar profesor
                                    $sql_profesor = "INSERT INTO profesor (nombres, apellidos, cedula, telefono, correo_electronico, direccion, fecha_nacimiento, genero, discapacidad, tipo_discapacidad, porcentaje_discapacidad, id_usuario) 
                                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                                    $stmt_profesor = $conn->prepare($sql_profesor);
                                    $stmt_profesor->bind_param('sssssssssssi', $nombres, $apellidos, $cedula, $telefono, $correo_electronico, $direccion, $fecha_nacimiento, $genero, $discapacidad, $tipo_discapacidad, $porcentaje_discapacidad, $id_usuario);
                                    $stmt_profesor->execute();
                                } elseif ($id_rol == 3) {
                                    // Insertar padre
                                    // Definir $parentesco correctamente antes de usarlo
                                    $parentesco = $_POST['parentesco'] ?? ''; // Se obtiene el parentesco
                                
                                    // Verificamos si el parentesco es "otro" y si el campo "parentesco_otro" está definido
                                    $parentesco_otro = ($parentesco == 'otro' && isset($_POST['parentesco_otro'])) ? $_POST['parentesco_otro'] : null;
                                
                                
                                    // Consulta SQL asegurando que los valores opcionales sean manejados correctamente
                                    $sql_padre = "INSERT INTO padre (nombres, apellidos, cedula, parentesco, parentesco_otro, telefono, correo_electronico, direccion, fecha_nacimiento, genero, discapacidad, tipo_discapacidad, porcentaje_discapacidad, id_usuario) 
                                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                                
                                    $stmt_padre = $conn->prepare($sql_padre);
                                
                                    // Enlace de parámetros asegurando que pasamos los valores correctos
                                    $stmt_padre->bind_param(
                                        'ssssssssssissi',
                                        $nombres,
                                        $apellidos,
                                        $cedula,
                                        $parentesco,
                                        $parentesco_otro,  
                                        $telefono,
                                        $correo_electronico,
                                        $direccion,
                                        $fecha_nacimiento,
                                        $genero,
                                        $discapacidad, 
                                        $tipo_discapacidad, 
                                        $porcentaje_discapacidad, 
                                        $id_usuario
                                    );
                                
                                    $stmt_padre->execute();
                                }
                                
                                // Finalizar transacción si todo ha ido bien
                                $conn->commit();
                                $success_message = "Usuario registrado exitosamente.";
                            } catch (Exception $e) {
                                $conn->rollback();
                                $error_message = "Error al registrar el usuario: " . $e->getMessage();
                            }
                        }
                    }
                }
            }
        }

    }
}

// Función para consultar la cédula en múltiples tablas
function consultarCedula($cedula) {
    global $conn;
    $usuario = null;

    // Consultar en la tabla administrador
    $stmt = $conn->prepare("SELECT nombres, apellidos, cedula, telefono, correo_electronico, direccion, fecha_nacimiento, genero, discapacidad, tipo_discapacidad, porcentaje_discapacidad, '1' AS id_rol, (SELECT contraseña FROM usuario WHERE usuario.cedula = administrador.cedula) AS contraseña FROM administrador WHERE cedula = ?");
    $stmt->bind_param("s", $cedula);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $usuario = $result->fetch_assoc();
    }

    // Consultar en la tabla profesor si no se encontró en administrador
    if (!$usuario) {
        $stmt = $conn->prepare("SELECT nombres, apellidos, cedula, telefono, correo_electronico, direccion, fecha_nacimiento, genero, discapacidad, tipo_discapacidad, porcentaje_discapacidad, '2' AS id_rol, (SELECT contraseña FROM usuario WHERE usuario.cedula = profesor.cedula) AS contraseña FROM profesor WHERE cedula = ?");
        $stmt->bind_param("s", $cedula);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $usuario = $result->fetch_assoc();
        }
    }

    // Consultar en la tabla padre si no se encontró en administrador ni profesor
    if (!$usuario) {
        $stmt = $conn->prepare("SELECT nombres, apellidos, cedula, telefono, correo_electronico, direccion, fecha_nacimiento, genero, discapacidad, tipo_discapacidad, porcentaje_discapacidad, '3' AS id_rol, (SELECT contraseña FROM usuario WHERE usuario.cedula = padre.cedula) AS contraseña, parentesco, parentesco_otro FROM padre WHERE cedula = ?");
        $stmt->bind_param("s", $cedula);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $usuario = $result->fetch_assoc();
        }
    }

    return $usuario;
}

// Manejo de la solicitud del botón
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['cedula'])) {
    $cedula = $_POST["cedula"];
    $usuario = consultarCedula($cedula);

    if ($usuario) {
        // Usuario ya registrado
        $response = ['success' => true] + $usuario;
    } else {
        // Usuario no registrado
        $response = ['success' => false];
    }
    echo json_encode($response);
    exit;
}
?>



<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Registro de Usuarios | Sistema de Gestión UEBF</title>
    <link rel="shortcut icon" href="http://localhost/sistema_notas/imagenes/logo.png" type="image/x-icon">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet"
        type="text/css">
    <style>
    body {
        font-family: Arial, sans-serif;
        background-color: #f4f4f4;
        margin: 0;
        padding: 0;
    }

    .header-banner {
        background-color: #c1121f;
        color: #fff;
        text-align: center;
        padding: 20px 0;
    }

    .header-banner h1 {
        margin: 0;
        font-size: 24px;
    }

    .container {
        max-width: 800px;
        margin: 50px auto;
        background-color: #fff;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0px 0px 20px 0px rgba(0, 0, 0, 0.1);
    }

    h2 {
        text-align: center;
        margin-bottom: 30px;
        color: #333;
        background-color: #e71b2a;
        padding: 10px;
        border-radius: 10px;
        color: #fff;
    }

    .required {
        color: red;
        /* Color rojo para los campos obligatorios */
        margin-left: 5px;
    }

    .error-message {
        color: red;
        font-weight: bold;
        word-wrap: break-word;
    }

    .success-message {
        color: green;
        font-weight: bold;
        word-wrap: break-word;
    }

    .form-group {
        margin-bottom: 20px;
    }

    /* Alinear el grupo de botones a la derecha */
    .button-group {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        /* Espacio entre los botones */
    }

    /* Estilos generales para los botones */
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        /* Tamaño de fuente reducido */
        padding: 8px 16px;
        /* Reducción de relleno */
        border-radius: 6px;
        /* Borde más suave */
        border: none;
        cursor: pointer;
        /* Cambia el cursor a una mano cuando se pasa el ratón sobre el botón */
        transition: background-color 0.3s ease;
        /* Añade una transición suave al color de fondo cuando cambia */
        text-transform: uppercase;
        font-weight: bold;
        color: white;
        /* Color del texto en todos los botones */
    }

    /* Estilos para el botón Regresar */
    .btn-regresar {
        background-color: #6c757d;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        color: white;
        /* Asegurar que el texto sea blanco */
    }

    .btn-regresar:hover {
        background-color: #5a6268;
        box-shadow: 0 6px 8px rgba(0, 0, 0, 0.15);
        color: white;
        /* Asegurar que el texto sea blanco */
    }

    .btn-regresar:active {
        background-color: #545b62;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        color: white;
        /* Asegurar que el texto sea blanco */
    }

    /* Estilos para el botón Crear Usuario */
    .btn-crear-usuario {
        background-color: #e71b2a;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        color: white;
        /* Asegurar que el texto sea blanco */
    }

    .btn-crear-usuario:hover {
        background-color: #c21623;
        box-shadow: 0 6px 8px rgba(0, 0, 0, 0.15);
        color: white;
        /* Asegurar que el texto sea blanco */
    }

    .btn-crear-usuario:active {
        background-color: #a0121d;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        color: white;
        /* Asegurar que el texto sea blanco */
    }

    /* Icono dentro del botón */
    .btn i {
        margin-right: 8px;
    }

    .btn-primary {
        background-color: #e71b2a;
        /* Color de fondo del botón */
        color: #fff;
        /* Color del texto dentro del botón */
        border: none;
        /* Elimina cualquier borde alrededor del botón */
        border-radius: 10px;
        /* Redondea las esquinas del botón con un radio de 10 píxeles */
        padding: 10px 20px;
        /* Añade espacio interno alrededor del contenido del botón */
        cursor: pointer;
        /* Cambia el cursor a una mano cuando se pasa el ratón sobre el botón */
        transition: background-color 0.3s ease;
        /* Añade una transición suave al color de fondo cuando cambia */
        margin-left: 0px;
        /* Espacio a la izquierda del botón */
        width: 150px;
        /* Ancho del botón */
        height: 38px;
        /* Altura del botón */
    }

    .btn-primary:hover {
        background-color: #c1121f;
    }

    .form-label {
        font-weight: bold;
        color: #333;
    }

    .bx {
        margin-right: 10px;
    }

    #button-generate {
        background-color: #e71b2a;
        color: #fff;
        border-color: #e71b2a;
        width: 100%;
    }

    #button-generate:hover {
        background-color: #c1121f;
        border-color: #c1121f;
    }

    footer {
        background-color: #c1121f;
        color: #fff;
        text-align: center;
        padding: 20px 0;
        width: 100%;
    }

    footer p {
        margin: 0;
    }

    .error-message,
    .success-message {
        margin: 10px 0;
        padding: 10px;
        border-radius: 5px;
        text-align: center;
    }

    .error-message {
        background-color: #f8d7da;
        color: #721c24;
    }

    .success-message {
        background-color: #d4edda;
        color: #155724;
    }

    .consulta-cedula-form {
        max-width: 450px;
        margin: 20px auto;
        /* Centrar horizontalmente */
        text-align: center;
        /* Centrar contenido en el contenedor */
    }

    .consulta-cedula-form label {
        font-size: 1.2rem;
        font-weight: 600;
        color: #145d8b;
        display: inline-flex;
        /* Para que el ícono y el texto queden en línea */
        align-items: center;
        margin-bottom: 10px;
        /* Reducir el espacio vertical debajo del label */
    }

    .consulta-cedula-form label i {
        font-size: 1.5rem;
        margin-right: 12px;
        color: #145d8b;
        /* Azul suave */
    }

    /* Contenedor del input y botón */
    .input-group-cedula {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 5px;
        /* Menos separación horizontal entre input y botón */
        margin-bottom: 10px;
        /* Reducir espacio vertical debajo del input+botón */
    }

    /* Campo de texto */
    .form-control-cedula {
        font-size: 1.1rem;
        padding: 12px 15px;
        border: 2px solid #ccc;
        border-radius: 6px;
        width: 70%;
        box-sizing: border-box;
        transition: border-color 0.3s ease, box-shadow 0.3s ease;
    }

    .form-control-cedula:focus {
        outline: none;
        border-color: #3498db;
        /* Azul suave */
        box-shadow: 0 0 8px rgba(52, 152, 219, 0.4);
    }

    .form-control-cedula::placeholder {
        color: #3498db;
    }

    /* Botón de consultar */
    .btn-cedula {
        background-color: #3498db;
        /* Azul suave */
        color: #fff;
        border: none;
        padding: 12px 15px;
        /* Mismo padding vertical que el input */
        border-radius: 6px;
        font-size: 1.1rem;
        cursor: pointer;
        transition: background-color 0.3s ease;
        white-space: nowrap;
        /* Evita que el texto se parta */
    }

    .btn-cedula:hover {
        background-color: #2980b9;
        /* Azul más oscuro */
    }

    .btn-cedula:active {
        background-color: #1c5980;
        /* Azul profundo */
    }

    /* Mensaje de error */
    .mensaje-error-cedula {
        font-size: 0.95rem;
        margin-top: 15px;
        color: red;
        font-weight: 500;
    }

    /* Estilos para el contenedor de tipos de discapacidad */
    .discapacidad-container {
        max-height: 200px;
        overflow-y: auto;
        border: 1px solid #ddd;
        padding: 15px;
        border-radius: 8px;
        background-color: #fff;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        transition: box-shadow 0.3s ease;
    }

    .discapacidad-container:hover {
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
    }

    /* Estilos elegantes para la instrucción */
    .instruccion-usuario {
        font-size: 13px;
        color: #888;
        margin-bottom: 10px;
        font-style: italic;
        padding: 5px 10px;
        border-radius: 5px;
    }

    /* Estilos para las descripciones */
    .descripcion {
        color: #777;
        font-style: italic;
        margin-left: 8px;
        font-size: 14px;
        /* Texto más pequeño */
        text-align: left;
        /* Alineación a la izquierda */
    }

    /* Estilos para los checkboxes */
    .form-check-input {
        margin-top: 0.3rem;
        accent-color: #248ed3;
    }

    .form-check-label {
        margin-left: 0.5rem;
        color: #333;
        font-weight: 500;
        display: flex;
        align-items: center;
        transition: color 0.3s ease;
    }

    .form-check-label:hover {
        color: #248ed3;
    }

    /* Estilos para el campo de porcentaje */
    .porcentaje-container {
        position: relative;
        /* Para posicionar el icono */
    }

    .porcentaje-container label {
        display: flex;
        align-items: center;
    }

    .porcentaje-container label i {
        margin-right: 8px;
        /* Espacio entre el icono y el texto */
    }

    .porcentaje-container .input-group {
        position: relative;
    }

    .porcentaje-container .input-group input {
        padding-right: 35px;
        /* Espacio para el icono a la derecha */
        height: 100%;
        box-sizing: border-box;
    }

    .porcentaje-container .input-group-append {
        display: flex;
        align-items: center;
        height: 100%;
    }

    .porcentaje-container .input-group-append .input-group-text {
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        box-sizing: border-box;
    }

    .porcentaje-container .input-group-append i {
        font-size: 18px;
        color: #555;
    }

    /* Responsivo para pantallas pequeñas */
    @media screen and (max-width: 480px) {
        .consulta-cedula-form {
            padding: 20px;
            width: 90%;
        }

        .form-control-cedula {
            font-size: 1rem;
            width: 100%;
        }

        .btn-cedula {
            font-size: 1rem;
            padding: 10px 20px;
        }

        .input-group-cedula {
            flex-direction: column;
            /* Input y botón uno debajo del otro en pantallas pequeñas */
            gap: 15px;
        }
    }
    </style>
</head>



<body>
    <div class="header-banner">
        <h1>Formulario de Registro de Usuarios | Sistema de Gestión UEBF</h1>
    </div>
    <div class="container">
        <h2><i class='bx bxs-user-plus'></i> Registro de Usuario</h2>

        <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
        <script>
        $(document).ready(function() {
            $('#btn-consultar').click(function() {
                var cedula = $('#consulta_cedula').val();
                $.post('http://localhost/sistema_notas/Crud/admin/usuario/registrar_usuario.php', {
                    cedula: cedula
                }, function(response) {
                    if (response.status === 'success') {
                        // Mostrar datos del usuario
                        console.log(response);
                    } else {
                        // Mostrar mensaje de error
                        $('#mensaje-error-cédula').show();
                    }
                }, 'json').fail(function(jqXHR, textStatus, errorThrown) {
                    console.error("Error en la solicitud:", textStatus, errorThrown);
                    alert("Este usuario no está registrado. Por favor, proceda a llenar el formulario.");
                });
            });
        });
        </script>

        <!-- Campo de consulta de cédula -->
        <div class="consulta-cedula-form">
            <label for="consulta_cedula"><i class="bx bx-search"></i> Consultar por Cédula:</label>
            <div class="input-group">
                <input type="text" class="form-control" id="consulta_cedula" name="cedula" maxlength="10"
                    pattern="[0-9]{10}" title="Ingrese un número de cédula de 10 dígitos"
                    placeholder="Ingrese la cédula del usuario">
                <div class="input-group-append">
                    <button class="btn btn-info" type="button" id="btn-consultar">Consultar</button>
                </div>
            </div>
            <small class="form-text text-muted" id="mensaje-error-cédula" style="color: red; display: none;">
                Usuario registrado.
            </small>
        </div>

        <!-- Mostrar mensaje de error o éxito -->
        <?php if ($error_message): ?>
        <div class="error-message"><?php echo $error_message; ?></div>
        <?php elseif ($success_message): ?>
        <div class="success-message"><?php echo $success_message; ?></div>
        <?php endif; ?>

        <form method="POST" action="" onsubmit="return validarFormulario();">
            <!-- Fila 1 -->
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="nombres"><i class='bx bxs-user'></i> Nombres:<span class="required">*</span></label>
                        <input type="text" class="form-control" id="nombres" name="nombres"
                            pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+" title="Ingrese solo letras y espacios" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="apellidos"><i class='bx bxs-user-detail'></i> Apellidos:<span
                                class="required">*</span></label>
                        <input type="text" class="form-control" id="apellidos" name="apellidos"
                            pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+" title="Ingrese solo letras y espacios" required>
                    </div>
                </div>
            </div>

            <!-- Fila 2 -->
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="cedula"><i class='bx bxs-id-card'></i> Cédula:<span
                                class="required">*</span></label>
                        <input type="text" class="form-control" id="cedula" name="cedula" maxlength="10"
                            pattern="[0-9]{10}" title="Ingrese exactamente 10 dígitos numéricos" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="telefono"><i class='bx bxs-phone'></i> Teléfono:<span
                                class="required">*</span></label>
                        <input type="text" class="form-control" id="telefono" name="telefono" maxlength="10"
                            pattern="09[0-9]{8}" title="El teléfono debe iniciar con 09 seguido de 8 dígitos numéricos"
                            required>
                    </div>
                </div>
            </div>

            <!-- Fila 3 -->
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="correo_electronico"><i class='bx bxs-envelope'></i> Correo Electrónico:<span
                                class="required">*</span></label>
                        <input type="email" class="form-control" id="correo_electronico" name="correo_electronico"
                            required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="direccion"><i class='bx bxs-map'></i> Dirección:<span
                                class="required">*</span></label>
                        <input type="text" class="form-control" id="direccion" name="direccion" required
                            pattern="^(?=.*[A-Za-zÁÉÍÓÚáéíóúÑñ])(?=.*\d)[A-Za-zÁÉÍÓÚáéíóúÑñ0-9.,#°\-\s]{5,255}$"
                            title="La dirección debe contener al menos una palabra y un número, y puede incluir caracteres como puntos, comas, guiones y números.">
                    </div>
                </div>
            </div>

            <!-- Fila 4 -->
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="fecha_nacimiento"><i class='bx bxs-calendar'></i> Fecha de Nacimiento:<span
                                class="required">*</span></label>
                        <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="genero"><i class='bx bx-female-sign'></i> Género:<span
                                class="required">*</span></label>
                        <select class="form-control" id="genero" name="genero" required>
                            <option value="">Seleccionar género</option>
                            <option value="femenino">Femenino</option>
                            <option value="masculino">Masculino</option>
                            <option value="otros">Otros</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Fila 5 -->
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="discapacidad"><i class='bx bx-handicap'></i> ¿Tiene Discapacidad?:<span
                                class="required">*</span></label>
                        <select class="form-control" id="discapacidad" name="discapacidad" required>
                            <option value="">Seleccionar discapacidad</option>
                            <option value="1">Sí</option>
                            <option value="0">No</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="id_rol"><i class='bx bxs-user-circle'></i> Rol:<span
                                class="required">*</span></label>
                        <select class="form-control" id="id_rol" name="id_rol" required>
                            <option value="">Seleccionar rol</option>
                            <option value="1">Administrador</option>
                            <option value="2">Profesor</option>
                            <option value="3">Padre</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Campos ocultos (discapacidad y porcentaje) -->
            <div class="row" id="discapacidadCampos" style="display: none;">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="tipo_discapacidad"><i class='bx bx-list-check'></i> Tipo de
                            discapacidad:</label>
                        <!-- Instrucción para el usuario -->
                        <p class="instruccion-usuario">Seleccione solo un tipo de discapacidad:
                        </p>
                        <div class="discapacidad-container">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="visual" name="tipo_discapacidad[]"
                                    value="visual">
                                <label class="form-check-label" for="visual">
                                    <strong>Visual</strong> <span class="descripcion">(Pérdida de visión)</span>
                                </label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="auditiva" name="tipo_discapacidad[]"
                                    value="auditiva">
                                <label class="form-check-label" for="auditiva">
                                    <strong>Auditiva</strong> <span class="descripcion">(Pérdida de audición)</span>
                                </label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="intelectual"
                                    name="tipo_discapacidad[]" value="intelectual">
                                <label class="form-check-label" for="intelectual">
                                    <strong>Intelectual</strong> <span class="descripcion">(Dificultades
                                        cognitivas)</span>
                                </label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="motora" name="tipo_discapacidad[]"
                                    value="motora">
                                <label class="form-check-label" for="motora">
                                    <strong>Motora</strong> <span class="descripcion">(Problemas de
                                        movimiento)</span>
                                </label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="psicosocial"
                                    name="tipo_discapacidad[]" value="psicosocial">
                                <label class="form-check-label" for="psicosocial">
                                    <strong>Psicosocial</strong> <span class="descripcion">(Condiciones
                                        mentales)</span>
                                </label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="múltiple" name="tipo_discapacidad[]"
                                    value="múltiple">
                                <label class="form-check-label" for="múltiple">
                                    <strong>Múltiple</strong> <span class="descripcion">(Combinación de
                                        discapacidades)</span>
                                </label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="habla_comunicacion"
                                    name="tipo_discapacidad[]" value="habla_comunicacion">
                                <label class="form-check-label" for="habla_comunicacion">
                                    <strong>Habla y Comunicación</strong> <span class="descripcion">(Dificultades
                                        para
                                        expresarse)</span>
                                </label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="sensorial"
                                    name="tipo_discapacidad[]" value="sensorial">
                                <label class="form-check-label" for="sensorial">
                                    <strong>Sensorial</strong> <span class="descripcion">(Limitación en los
                                        sentidos)</span>
                                </label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="enfermedades_cronicas"
                                    name="tipo_discapacidad[]" value="enfermedades_cronicas">
                                <label class="form-check-label" for="enfermedades_cronicas">
                                    <strong>Enfermedades Crónicas</strong> <span class="descripcion">(Condiciones
                                        prolongadas)</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group porcentaje-container">
                        <label for="porcentaje_discapacidad">
                            <i class='bx bx-accessibility'></i> Porcentaje de discapacidad:
                        </label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="porcentaje_discapacidad"
                                name="porcentaje_discapacidad" min="1" max="100" oninput="validarPorcentaje(this)"
                                placeholder="Ingresa un valor entre 1 y 100">
                            <div class="input-group-append">
                                <span class="input-group-text">
                                    <i class='fas fa-percent'></i> <!-- Icono de porcentaje de Font Awesome -->
                                </span>
                            </div>
                        </div>
                        <small id="porcentajeError" class="form-text text-danger" style="display: none;">
                            Porcentaje no válido.
                        </small>
                    </div>
                </div>
            </div>

            <!-- Fila 6: Parentesco (solo visible si el rol es 3 - Padre) -->
            <div class="row">
                <div class="col-md-6" id="parentescoCampos" style="display: none;">
                    <div class="form-group">
                        <label for="parentesco"><i class='bx bxs-group'></i> Parentesco:<span
                                class="required">*</span></label>
                        <select class="form-control" id="parentesco" name="parentesco">
                            <option value="">Seleccionar parentesco</option>
                            <option value="padre">Padre</option>
                            <option value="madre">Madre</option>
                            <option value="hermano/a mayor">Hermano/a Mayor</option>
                            <option value="familiar">Familiar</option>
                            <option value="otro">Otro</option>
                        </select>
                    </div>
                </div>

                <!-- Campo adicional de parentesco si selecciona "Otro" -->
                <div class="col-md-6">
                    <div class="form-group" id="otroParentescoInput" style="display: none;">
                        <label for="otro_parentesco"><i class='bx bxs-edit-alt'></i> Especificar Parentesco:</label>
                        <input type="text" class="form-control" id="otro_parentesco" name="parentesco_otro"
                            pattern="^(?!.*\b(padre|papá|madre|mamá|hermano mayor|hermana mayor|familiar|familia)\b)[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$"
                            title="No se permiten números, caracteres especiales ni las palabras prohibidas.">
                    </div>
                </div>
            </div>

            <!-- Fila 7 -->
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="contraseña"><i class='bx bxs-lock'></i> Contraseña:<span
                                class="required">*</span></label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="contraseña" name="contraseña" minlength="8"
                                required>
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="button" id="button-generate"
                                    onclick="generarClave()">Generar Clave</button>
                            </div>
                        </div>
                        <small id="passwordHelp" class="form-text text-muted" ondblclick="mostrarContrasena()">
                            Haga doble clic para mostrar/ocultar la contraseña.
                        </small>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="anio_lectivo"><i class='bx bxs-calendar-event'></i> Año Lectivo:<span
                                class="required">*</span></label>
                        <input type="text" class="form-control" id="anio_lectivo" name="anio_lectivo" readonly
                            value="<?php echo obtenerAnioLectivo($conn); ?>">
                    </div>
                </div>
            </div>

            <div class="button-group mt-4">
                <button type="button" class="btn btn-regresar"
                    onclick="location.href='http://localhost/sistema_notas/views/admin/usuario.php';">
                    <i class='bx bx-arrow-back'></i> Regresar
                </button>
                <button type="submit" class="btn btn-crear-usuario">
                    <i class='bx bx-save'></i> Crear Usuario
                </button>
            </div>
        </form>
    </div>

    <footer>
        <p>&copy; 2024 Instituto Superior Tecnológico Guayaquil. Desarrollado por Giullia Arias y Carlos
            Zambrano. Todos los derechos reservados.</p>
    </footer>

    <script>
    function validarPorcentaje() {
        // Lógica de validación del porcentaje
        var porcentaje = document.getElementById('porcentaje_discapacidad').value;
        if (porcentaje < 0 || porcentaje > 100) {
            alert('El porcentaje de discapacidad debe estar entre 0 y 100.');
            return false;
        }
        return true;
    }

    function validarFormulario() {
        var fechaNacimiento = document.getElementById('fecha_nacimiento').value.trim();
        var hoy = new Date();
        var fechaNac = new Date(fechaNacimiento);
        var edad = hoy.getFullYear() - fechaNac.getFullYear();
        var mes = hoy.getMonth() - fechaNac.getMonth();
        if (mes < 0 || (mes === 0 && hoy.getDate() < fechaNac.getDate())) {
            edad--;
        }
        if (edad < 18 || edad > 80) {
            alert('La fecha de nacimiento no es válida. Debe tener entre 18 y 80 años.');
            return false;
        }
        return true;
    }

    function mostrarContrasena() {
        var contraseña = document.getElementById("contraseña");
        if (contraseña.type === "password") {
            contraseña.type = "text";
        } else {
            contraseña.type = "password";
        }
    }

    function generarClave() {
        // Definir los tipos de caracteres que se pueden usar
        const mayusculas = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        const minusculas = 'abcdefghijklmnopqrstuvwxyz';
        const numeros = '0123456789';
        const especiales = '!@#$%^&*()_+[]{}|;:,.<>?';

        // Unir todos los tipos de caracteres
        const caracteres = mayusculas + minusculas + numeros + especiales;

        // Inicializar la contraseña generada
        let clave = '';

        // Asegurarse de que la contraseña tenga al menos un carácter de cada tipo
        clave += mayusculas[Math.floor(Math.random() * mayusculas.length)];
        clave += minusculas[Math.floor(Math.random() * minusculas.length)];
        clave += numeros[Math.floor(Math.random() * numeros.length)];
        clave += especiales[Math.floor(Math.random() * especiales.length)];

        // Generar el resto de la contraseña para completar 8 caracteres
        for (let i = clave.length; i < 8; i++) { // Limitar la longitud a 8
            const randomIndex = Math.floor(Math.random() * caracteres.length);
            clave += caracteres[randomIndex];
        }

        // Mezclar la contraseña para evitar que los primeros caracteres estén en un orden predecible
        clave = clave.split('').sort(() => Math.random() - 0.5).join('');

        // Asignar la contraseña generada al campo de entrada
        const input_contrasena = document.getElementById('contraseña');
        input_contrasena.value = clave;

        // Habilitar el campo de contraseña si estaba deshabilitado
        input_contrasena.disabled = false;

        // Deshabilitar el botón de generar para evitar múltiples clics
        document.getElementById('button-generate').disabled = true;
    }

    function mostrarMensajeError(mensaje) {
        var errorMessageContainer = document.querySelector('.error-message');
        if (!errorMessageContainer) {
            errorMessageContainer = document.createElement('div');
            errorMessageContainer.className = 'error-message';
            document.querySelector('.container').insertBefore(errorMessageContainer, document.querySelector(
                'form'));
        }
        errorMessageContainer.textContent = mensaje;
    }

    document.addEventListener("DOMContentLoaded", function() {
        const rolSelect = document.getElementById("id_rol");
        const parentescoSelect = document.getElementById("parentesco");
        const parentescoCampos = document.getElementById("parentescoCampos");
        const otroParentescoInputDiv = document.getElementById("otroParentescoInput");
        const otroParentescoInput = document.getElementById("otro_parentesco");

        const valoresProhibidos = ["padre", "papá", "madre", "mamá", "hermano mayor", "hermana mayor",
            "familiar", "familia"
        ];

        // Función para mostrar/ocultar "Parentesco"
        function mostrarParentesco() {
            parentescoCampos.style.display = rolSelect.value === "3" ? "block" : "none";
        }

        // Función para mostrar/ocultar "Especificar Parentesco"
        function mostrarOtroParentesco() {
            if (rolSelect.value === "3" && parentescoSelect.value === "otro") {
                otroParentescoInputDiv.style.display = "block";
            } else {
                otroParentescoInputDiv.style.display = "none";
                otroParentescoInput.value = ""; // Limpiar el campo si se oculta
            }
        }

        // Función para validar "Especificar Parentesco"
        function validarOtroParentesco() {
            let input = otroParentescoInput.value.trim().toLowerCase();

            // Expresión regular: permite solo letras, espacios y tildes
            let regexSoloLetras = /^[a-záéíóúüñ\s]+$/i;

            if (!regexSoloLetras.test(input)) {
                alert(
                    "Solo se permiten letras en el campo 'Especificar Parentesco'. No se permiten caracteres especiales ni números."
                );
                otroParentescoInput.value = "";
                return;
            }

            // Eliminar caracteres extraños que puedan haber sido ingresados (guiones, asteriscos, etc.)
            let inputLimpio = input.replace(/[^a-záéíóúüñ\s]/gi, "");

            // Verificar si el texto limpio sigue conteniendo una palabra prohibida
            if (valoresProhibidos.some(palabra => inputLimpio.includes(palabra))) {
                alert("El valor ingresado no es válido. Por favor, ingrese un parentesco diferente.");
                otroParentescoInput.value = "";
            }
        }

        // Agregar eventos
        rolSelect.addEventListener("change", () => {
            mostrarParentesco();
            mostrarOtroParentesco();
        });

        parentescoSelect.addEventListener("change", mostrarOtroParentesco);
        otroParentescoInput.addEventListener("input", validarOtroParentesco);

        // Inicializar
        mostrarParentesco();
        mostrarOtroParentesco();
    });

    // Obtén el elemento select del campo "Discapacidad"
    const discapacidadSelect = document.getElementById("discapacidad");

    // Obtén el contenedor de los campos ocultos
    const discapacidadCampos = document.getElementById("discapacidadCampos");

    // Agrega un evento de cambio al select
    discapacidadSelect.addEventListener("change", () => {
        // Si el valor seleccionado es "1" (Sí)
        if (discapacidadSelect.value === "1") {
            // Muestra los campos ocultos
            discapacidadCampos.style.display = "block";
        } else {
            // Oculta los campos ocultos
            discapacidadCampos.style.display = "none";
        }
    });

    function validarYAgregarSimboloPorcentaje(input) {
        // Obtener el valor ingresado
        let valor = input.value;

        // Validar que el valor sea un número y esté entre 1 y 100
        if (isNaN(valor) || valor < 1 || valor > 100) {
            alert("Por favor, ingrese un porcentaje válido entre 1 y 100.");
            input.value = ""; // Limpiar el campo si la validación falla
        } else {
            // Agregar el símbolo de porcentaje
            input.value = valor + "%";
        }
    }

    document.getElementById("direccion").addEventListener("input", function() {
        let valor = this.value;
        let regex = /^(?=.*[A-Za-zÁÉÍÓÚáéíóúÑñ])(?=.*\d)[A-Za-zÁÉÍÓÚáéíóúÑñ0-9.,#°\-\s]{5,255}$/;

        if (!regex.test(valor)) {
            this.setCustomValidity(
                "La dirección debe contener al menos una palabra y un número, y solo puede incluir letras, números, espacios, puntos, comas, guiones y símbolos como # y °."
            );
        } else {
            this.setCustomValidity("");
        }
    });


    document.getElementById('btn-consultar').addEventListener('click', function() {
        var cedula = document.getElementById('consulta_cedula').value;

        if (cedula.length === 10 && /^[0-9]+$/.test(cedula)) {
            var xhr = new XMLHttpRequest();
            xhr.open('POST', '', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    try {
                        var response = JSON.parse(xhr.responseText);

                        if (response.success) {
                            alert('Este usuario ya estaba registrado');
                            document.getElementById('nombres').value = response.nombres;
                            document.getElementById('apellidos').value = response.apellidos;
                            document.getElementById('cedula').value = response.cedula;
                            document.getElementById('telefono').value = response.telefono;
                            document.getElementById('correo_electronico').value = response
                                .correo_electronico;
                            document.getElementById('direccion').value = response.direccion;
                            document.getElementById('fecha_nacimiento').value = response.fecha_nacimiento;
                            document.getElementById('genero').value = response.genero;
                            document.getElementById('discapacidad').value = response.discapacidad;
                            document.getElementById('id_rol').value = response.id_rol;
                            document.getElementById('contraseña').value = response.contraseña;

                            // Mostrar campos adicionales si es necesario
                            if (response.id_rol == '3') { // Si es padre
                                document.getElementById('parentescoCampos').style.display = 'block';
                                document.getElementById('parentesco').value = response.parentesco;
                                if (response.parentesco == 'otro') {
                                    document.getElementById('otroParentescoInput').style.display = 'block';
                                    document.getElementById('otro_parentesco').value = response
                                        .parentesco_otro;
                                }
                            } else {
                                document.getElementById('parentescoCampos').style.display = 'none';
                            }

                            if (response.discapacidad == '1') {
                                document.getElementById('discapacidadCampos').style.display = 'block';
                                // Marcar los checkboxes de tipo_discapacidad
                                var tiposDiscapacidad = response.tipo_discapacidad.split(',');
                                tiposDiscapacidad.forEach(function(tipo) {
                                    var checkbox = document.getElementById(tipo);
                                    if (checkbox) checkbox.checked = true;
                                });
                                document.getElementById('porcentaje_discapacidad').value = response
                                    .porcentaje_discapacidad;
                            } else {
                                document.getElementById('discapacidadCampos').style.display = 'none';
                            }

                            // Deshabilitar botones
                            document.getElementById('btn-consultar').disabled = true;
                            document.querySelector('.btn-crear-usuario').disabled = true;
                            document.getElementById('button-generate').disabled = true;

                        } else {
                            // Mostrar el mensaje si el usuario no está registrado
                            alert(response.message ||
                                'Este usuario no está registrado, puede proceder a llenar el formulario'
                            );
                        }
                    } catch (e) {
                        console.error("Error en JSON:", e);
                        alert(
                            "Hubo un error al procesar.");
                    }
                }
            };
            xhr.send('cedula=' + encodeURIComponent(cedula));
        } else {
            alert('Por favor, ingrese un número de cédula válido de 10 dígitos.');
        }
    });
    </script>
</body>

</html>