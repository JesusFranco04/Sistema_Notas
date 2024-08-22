<?php
session_start();
include('../../config.php');

$id_profesor = $_POST['id_profesor'];

$sql = "UPDATE notificaciones_docente SET estado = 'leida' WHERE id_profesor = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_profesor);
$stmt->execute();
?>
