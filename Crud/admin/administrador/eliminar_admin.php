<?php
session_start();
include('../../config.php');
date_default_timezone_set('America/Guayaquil');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cedula = isset($_POST['cedula']) ? $_POST['cedula'] : null;
    $estado = isset($_POST['estado']) ? $_POST['estado'] : null;

    if ($cedula && $estado) {
        $stmt = $conn->prepare("UPDATE administrador SET estado = ? WHERE cedula = ?");
        $stmt->bind_param("ss", $estado, $cedula);

        if ($stmt->execute()) {
            echo 'Estado actualizado correctamente.';
        } else {
            echo 'Error al actualizar el estado.';
        }

        $stmt->close();
    } else {
        echo 'Datos incompletos.';
    }

    $conn->close();
}
?>
