<?php
session_start();
include '../../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_periodo = $_POST['periodo'];

    // Desactivar otros períodos (excepto los que deben permanecer siempre activos)
    $sql_desactivar = "UPDATE periodo_academico SET estado = 0 WHERE id_periodo NOT IN (3)";
    $conn->query($sql_desactivar);

    // Activar el período seleccionado
    $sql_activar = "UPDATE periodo_academico SET estado = 1 WHERE id_periodo = ?";
    $stmt = $conn->prepare($sql_activar);
    $stmt->bind_param("i", $id_periodo);
    $stmt->execute();

    // Respuesta en JSON
    if ($stmt->affected_rows > 0) {
        echo json_encode(["success" => true, "message" => "Período activado correctamente."]);
    } else {
        echo json_encode(["success" => false, "message" => "Error al activar el período."]);
    }

    $stmt->close();
    $conn->close();
}
