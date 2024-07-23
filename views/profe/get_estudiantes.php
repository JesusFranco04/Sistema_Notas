<?php
include('../../Crud/config.php'); // Ruta absoluta

// Verifica si se han recibido los datos necesarios
if (!isset($_POST['id_curso']) || !isset($_POST['año'])) {
    echo "Datos no enviados correctamente.";
    exit();
}

$id_curso = intval($_POST['id_curso']);
$año = $_POST['año']; // Aquí 'año' es un varchar, no entero

// Validar los datos
if ($id_curso <= 0 || empty($año)) {
    echo "ID de curso o año académico no válidos.";
    exit();
}

// Obtener los detalles del curso
$sql_detalles = "SELECT id_nivel, id_paralelo, id_jornada FROM curso WHERE id_curso = ? AND id_his_academico = (SELECT id_his_academico FROM historial_academico WHERE año = ?)";
$stmt_detalles = $conn->prepare($sql_detalles);
$stmt_detalles->bind_param("is", $id_curso, $año);
$stmt_detalles->execute();
$result_detalles = $stmt_detalles->get_result();
$curso = $result_detalles->fetch_assoc();
$stmt_detalles->close();

if ($curso) {
    // Obtener estudiantes que coincidan con el curso
    $sql_estudiantes = "SELECT * FROM estudiante WHERE id_nivel = ? AND id_paralelo = ? AND id_jornada = ? AND id_his_academico = (SELECT id_his_academico FROM historial_academico WHERE año = ?)";
    $stmt_estudiantes = $conn->prepare($sql_estudiantes);
    $stmt_estudiantes->bind_param("iiis", $curso['id_nivel'], $curso['id_paralelo'], $curso['id_jornada'], $año);
    $stmt_estudiantes->execute();
    $result_estudiantes = $stmt_estudiantes->get_result();

    $estudiantes = array();
    while ($row = $result_estudiantes->fetch_assoc()) {
        $estudiantes[] = $row;
    }

    $stmt_estudiantes->close();
    $conn->close();

    if (!empty($estudiantes)) {
        echo "<h3>Lista de Estudiantes</h3><ul>";
        foreach ($estudiantes as $estudiante) {
            echo "<li>".$estudiante['nombres']." ".$estudiante['apellidos']."</li>";
        }
        echo "</ul>";
    } else {
        echo "No hay estudiantes para este curso.";
    }
} else {
    echo "Curso no encontrado.";
}
?>
