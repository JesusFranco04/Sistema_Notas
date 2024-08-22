<?php
session_start();
include('../../config.php');

$id_profesor = $_GET['id_profesor'];

$sql = "SELECT * FROM notificaciones_docente WHERE id_profesor = ? AND estado = 'no_leida'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_profesor);
$stmt->execute();
$result = $stmt->get_result();

$notificaciones = [];
while ($row = $result->fetch_assoc()) {
    $notificaciones[] = $row;
}

echo json_encode($notificaciones);
?>