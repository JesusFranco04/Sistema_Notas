<?php
session_start();
include('../../config.php');

// Consulta para obtener las últimas 5 cédulas desde la tabla usuario
$query = "SELECT cedula FROM usuario ORDER BY fecha_ingreso DESC LIMIT 5";
$result = mysqli_query($conn, $query);

$cedulas = array();
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $cedulas[] = $row['cedula'];
    }
}

// Devolver los datos como JSON
echo json_encode($cedulas);

mysqli_free_result($result);
mysqli_close($conn);
?>