<?php
include '../../Crud/config.php';

// Obtener filtros
$nivel = isset($_GET['nivel']) ? $_GET['nivel'] : '';
$especialidad = isset($_GET['especialidad']) ? $_GET['especialidad'] : '';
$jornada = isset($_GET['jornada']) ? $_GET['jornada'] : '';
$paralelo = isset($_GET['paralelo']) ? $_GET['paralelo'] : '';
$curso = isset($_GET['curso']) ? $_GET['curso'] : '';
$añoAcademico = isset($_GET['añoAcademico']) ? $_GET['añoAcademico'] : '';

// Construir la consulta SQL
$sql = "SELECT * FROM estudiantes WHERE 1=1";

if (!empty($nivel)) {
    $sql .= " AND nivel = '" . $conn->real_escape_string($nivel) . "'";
}
if (!empty($especialidad)) {
    $sql .= " AND especialidad = '" . $conn->real_escape_string($especialidad) . "'";
}
if (!empty($jornada)) {
    $sql .= " AND jornada = '" . $conn->real_escape_string($jornada) . "'";
}
if (!empty($paralelo)) {
    $sql .= " AND paralelo = '" . $conn->real_escape_string($paralelo) . "'";
}
if (!empty($curso)) {
    $sql .= " AND curso = '" . $conn->real_escape_string($curso) . "'";
}
if (!empty($añoAcademico)) {
    $sql .= " AND añoAcademico = '" . $conn->real_escape_string($añoAcademico) . "'";
}

// Ejecutar la consulta
$result = $conn->query($sql);

// Construir la tabla de resultados
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['id']) . "</td>";
        echo "<td>" . htmlspecialchars($row['nombres']) . "</td>";
        echo "<td>" . htmlspecialchars($row['apellidos']) . "</td>";
        echo "<td>" . htmlspecialchars($row['cedula']) . "</td>";
        echo "<td>" . htmlspecialchars($row['telefono']) . "</td>";
        echo "<td>" . htmlspecialchars($row['correo']) . "</td>";
        echo "<td>" . htmlspecialchars($row['direccion']) . "</td>";
        echo "<td>" . htmlspecialchars($row['fecha_nacimiento']) . "</td>";
        echo "<td>" . htmlspecialchars($row['genero']) . "</td>";
        echo "<td>" . htmlspecialchars($row['discapacidad']) . "</td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='10'>No se encontraron resultados.</td></tr>";
}
?>
