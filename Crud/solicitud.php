<?php

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $cedula = $_POST['cedula'];
    $telefono = $_POST['telefono'];
    $nombres = $_POST['nombres'];
    $apellidos = $_POST['apellidos'];
    $correo_electronico = $_POST['correo_electronico'];
    $rol = $_POST['rol'];

    include 'config.php';
    
    $sql = "INSERT INTO solicitudes (cedula, telefono, nombres, apellidos, correo_electronico, rol, date_creation)
            VALUES ('$cedula', '$telefono', '$nombres', '$apellidos', '$correo_electronico', '$rol', NOW())";


    if ($conn->query($sql) === TRUE) {
        echo '<script>alert("Solicitud enviada exitosamente.");</script>';
        echo '<script>window.location.replace("http://localhost/Sistema_Notas/Enviar_soli.php");</script>';
        exit;
    } else {
        echo "Error al insertar el registro: " . $conn->error;
    }

    $conn->close();
}
?>
