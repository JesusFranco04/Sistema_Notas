<?php
include('../../Crud/config.php'); // Ruta absoluta

// Verifica si se han recibido los datos necesarios
if (!isset($_POST['id_curso']) || !isset($_POST['año'])) {
    echo "Datos no enviados correctamente.";
    exit();
}

$id_curso = intval($_POST['id_curso']);
$año = $_POST['año']; // Aquí 'año' es un varchar, no entero
$query = isset($_POST['query']) ? $_POST['query'] : ''; // Obtener el término de búsqueda

// Validar los datos
if ($id_curso <= 0 || empty($año)) {
    echo "ID de curso o año académico no válidos.";
    exit();
}

// Obtener los detalles del curso
$sql_detalles = "SELECT id_nivel, id_paralelo, id_jornada 
                 FROM curso 
                 WHERE id_curso = ? AND id_his_academico = (SELECT id_his_academico FROM historial_academico WHERE año = ?)";
$stmt_detalles = $conn->prepare($sql_detalles);
$stmt_detalles->bind_param("is", $id_curso, $año);
$stmt_detalles->execute();
$result_detalles = $stmt_detalles->get_result();
$curso = $result_detalles->fetch_assoc();
$stmt_detalles->close();

if ($curso) {
    // Obtener estudiantes que coincidan con el curso y la búsqueda, ordenados por apellidos
    $sql_estudiantes = "SELECT DISTINCT e.id_estudiante, e.cedula, e.nombres, e.apellidos, e.fecha_nacimiento, e.genero, e.discapacidad, 
    p.cedula AS cedula_padre, p.nombres AS nombres_padre, p.apellidos AS apellidos_padre, p.parentesco
    FROM estudiante e
    LEFT JOIN padre_x_estudiante pe ON e.id_estudiante = pe.id_estudiante
    LEFT JOIN padre p ON pe.id_padre = p.id_padre
    WHERE e.id_nivel = ? AND e.id_paralelo = ? AND e.id_jornada = ? AND e.id_his_academico = (SELECT id_his_academico FROM historial_academico WHERE año = ?)
    AND (e.cedula LIKE ? OR e.nombres LIKE ? OR e.apellidos LIKE ?)
    ORDER BY e.apellidos ASC";

    $searchTerm = "%$query%";
    $stmt_estudiantes = $conn->prepare($sql_estudiantes);
    $stmt_estudiantes->bind_param("iiissss", $curso['id_nivel'], $curso['id_paralelo'], $curso['id_jornada'], $año, $searchTerm, $searchTerm, $searchTerm);
    $stmt_estudiantes->execute();
    $result_estudiantes = $stmt_estudiantes->get_result();

    $estudiantes = array();
    $total_estudiantes = $result_estudiantes->num_rows; // Contar el número de estudiantes
    while ($row = $result_estudiantes->fetch_assoc()) {
        $row['edad'] = date_diff(date_create($row['fecha_nacimiento']), date_create('today'))->y;
        $estudiantes[] = $row;
    }

    $stmt_estudiantes->close();
    $conn->close();

    // Mostrar la información del curso y la lista de estudiantes
    echo "<h3>Lista de Estudiantes</h3>";
    echo "<p><strong>Total de Estudiantes:</strong> {$total_estudiantes}</p>";
    if (!empty($estudiantes)) {
        echo "<div class='table-wrapper'>
                <table class='table table-striped'>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Cédula</th>
                            <th>Nombres</th>
                            <th>Apellidos</th>
                            <th>Edad</th>
                            <th>Género</th>
                            <th>Discapacidad</th>
                            <th>Cédula Padre</th>
                            <th>Nombre Padre</th>
                            <th>Apellido Padre</th>
                            <th>Parentesco</th>
                        </tr>
                    </thead>
                    <tbody>";
        $num = 1;
        foreach ($estudiantes as $estudiante) {
            echo "<tr>
                    <td>{$num}</td>
                    <td>{$estudiante['cedula']}</td>
                    <td>{$estudiante['nombres']}</td>
                    <td>{$estudiante['apellidos']}</td>
                    <td>{$estudiante['edad']}</td>
                    <td>{$estudiante['genero']}</td>
                    <td>{$estudiante['discapacidad']}</td>
                    <td>{$estudiante['cedula_padre']}</td>
                    <td>{$estudiante['nombres_padre']}</td>
                    <td>{$estudiante['apellidos_padre']}</td>
                    <td>{$estudiante['parentesco']}</td>
                </tr>";
            $num++;
        }
        echo "</tbody>
            </table>
        </div>";
    } else {
        echo "No hay estudiantes para este curso.";
    }
} else {
    echo "Curso no encontrado.";
}
?>
