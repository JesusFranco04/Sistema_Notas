<?php
include('../../Crud/config.php'); // Ruta absoluta

// Verifica si se han recibido los datos necesarios
if (!isset($_POST['id_curso']) || !isset($_POST['año'])) {
    echo json_encode(["error" => "Datos no enviados correctamente."]);
    exit();
}

$id_curso = intval($_POST['id_curso']);
$año = $_POST['año']; // Aquí 'año' es un varchar, no entero

// Validar los datos
if ($id_curso <= 0 || empty($año)) {
    echo json_encode(["error" => "ID de curso o año académico no válidos."]);
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
    // Obtener estadísticas sobre edad, género y discapacidad
    $sql_estadisticas = "SELECT
                            TIMESTAMPDIFF(YEAR, e.fecha_nacimiento, CURDATE()) AS edad,
                            e.genero,
                            e.discapacidad,
                            COUNT(*) AS cantidad
                         FROM estudiante e
                         WHERE e.id_nivel = ? AND e.id_paralelo = ? AND e.id_jornada = ? AND e.id_his_academico = (SELECT id_his_academico FROM historial_academico WHERE año = ?)
                         GROUP BY edad, genero, discapacidad";
    $stmt_estadisticas = $conn->prepare($sql_estadisticas);
    $stmt_estadisticas->bind_param("iiis", $curso['id_nivel'], $curso['id_paralelo'], $curso['id_jornada'], $año);
    $stmt_estadisticas->execute();
    $result_estadisticas = $stmt_estadisticas->get_result();

    $datos = [
        'edades' => [],
        'generos' => [],
        'discapacidades' => [],
        'valores' => []
    ];

    while ($row = $result_estadisticas->fetch_assoc()) {
        $edad = $row['edad'];
        $genero = $row['genero'];
        $discapacidad = $row['discapacidad'];
        $cantidad = $row['cantidad'];

        // Agregar datos a las categorías correspondientes
        if (!isset($datos['edades'][$edad])) {
            $datos['edades'][$edad] = 0;
        }
        $datos['edades'][$edad] += $cantidad;

        if (!isset($datos['generos'][$genero])) {
            $datos['generos'][$genero] = 0;
        }
        $datos['generos'][$genero] += $cantidad;

        if (!isset($datos['discapacidades'][$discapacidad])) {
            $datos['discapacidades'][$discapacidad] = 0;
        }
        $datos['discapacidades'][$discapacidad] += $cantidad;
    }

    $stmt_estadisticas->close();
    $conn->close();

    // Enviar datos en formato JSON
    echo json_encode([
        'edades' => $datos['edades'],
        'generos' => $datos['generos'],
        'discapacidades' => $datos['discapacidades']
    ]);
} else {
    echo json_encode(["error" => "Curso no encontrado."]);
}
?>
