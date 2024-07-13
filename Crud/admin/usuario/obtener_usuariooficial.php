<?php
session_start();
include('../../config.php');

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['cedula'])) {
    $cedula = mysqli_real_escape_string($conn, $_GET['cedula']);

    $query = "SELECT id_rol, estado FROM usuario WHERE cedula = '$cedula' ORDER BY fecha_ingreso DESC LIMIT 1";
    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        echo json_encode($row);
    } else {
        echo json_encode(array());
    }

    mysqli_free_result($result);
    mysqli_close($conn);
    exit;
}
?>

