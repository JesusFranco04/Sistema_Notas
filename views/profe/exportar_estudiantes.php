<?php
include('../../Crud/config.php');

if (!isset($_GET['id_curso']) || !isset($_GET['año'])) {
    echo "Datos no enviados correctamente.";
    exit();
}

$id_curso = intval($_GET['id_curso']);
$año = $_GET['año'];

// Obtener datos del curso y estudiantes como antes
// ...

header('Content-Type: text/csv');
header('Content-Disposition: attachment;filename="estudiantes.csv"');

$output = fopen('php://output', 'w');
fputcsv($output, array('Cédula', 'Nombres', 'Apellidos', 'Fecha de Nacimiento', 'Género', 'Discapacidad', 'Cédula Padre', 'Nombre Padre', 'Apellido Padre', 'Parentesco'));

// Escribir filas de datos
while ($row = $result_estudiantes->fetch_assoc()) {
    fputcsv($output, $row);
}

fclose($output);
exit();
?>
