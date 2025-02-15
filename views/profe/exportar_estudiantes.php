<?php  
include('../../Crud/config.php');

// Configuración de codificación de caracteres para UTF-8
header('Content-Type: text/html; charset=utf-8'); // Asegura que la codificación sea UTF-8
if (!isset($_GET['id_curso']) || !isset($_GET['año'])) {
    echo "Datos no enviados correctamente.";
    exit();
}

$id_curso = $_GET['id_curso'];

// Obtener datos del curso
$sql_curso = "
    SELECT c.*, p.nombres AS profesor_nombre, p.apellidos AS profesor_apellido, m.nombre AS materia
    FROM curso c
    INNER JOIN profesor p ON c.id_profesor = p.id_profesor
    LEFT JOIN materia m ON c.id_materia = m.id_materia
    WHERE c.id_curso = ?";

$stmt_curso = $conn->prepare($sql_curso);
$stmt_curso->bind_param('i', $id_curso);
$stmt_curso->execute();
$result_curso = $stmt_curso->get_result();
$curso_info = $result_curso->fetch_assoc();

if (!$curso_info) {
    die("No se encontró información del curso especificado.");
}

// Consultar estudiantes
$sql_estudiantes = "
    SELECT 
        e.id_estudiante,
        CONCAT(e.nombres, ' ', e.apellidos) AS nombre_estudiante,
        e.cedula AS cedula_estudiante,
        TIMESTAMPDIFF(YEAR, e.fecha_nacimiento, CURDATE()) AS edad_estudiante,
        e.genero AS genero_estudiante,
        e.discapacidad AS discapacidad_estudiante,
        CONCAT(p.nombres, ' ', p.apellidos) AS nombre_completo_padre,
        p.cedula AS cedula_padre,
        p.telefono
    FROM 
        estudiante e
    LEFT JOIN 
        padre_x_estudiante px ON e.id_estudiante = px.id_estudiante
    LEFT JOIN 
        padre p ON px.id_padre = p.id_padre
    INNER JOIN 
        curso c ON 
        e.id_nivel = c.id_nivel AND
        e.id_subnivel = c.id_subnivel AND
        e.id_paralelo = c.id_paralelo AND
        e.id_jornada = c.id_jornada AND
        e.id_his_academico = c.id_his_academico
    WHERE 
        c.id_curso = ? AND 
        e.estado = 'A'";

$stmt_estudiantes = $conn->prepare($sql_estudiantes);
$stmt_estudiantes->bind_param('i', $id_curso);
$stmt_estudiantes->execute();
$result_estudiantes = $stmt_estudiantes->get_result();

if ($result_estudiantes->num_rows == 0) {
    die("No se encontraron estudiantes activos para el curso especificado.");
}

// Crear la cabecera HTML para el archivo Excel
$inicial_nombre = strtoupper(substr($curso_info['profesor_nombre'], 0, 1)); // Primera inicial del nombre
$inicial_apellido = strtoupper(substr($curso_info['profesor_apellido'], 0, 1)); // Primera inicial del apellido
$nombre_materia = str_replace(' ', '_', $curso_info['materia']); // Reemplazar espacios con guion bajo en la materia

$nombre_archivo = "{$inicial_nombre}_{$inicial_apellido}_Nomina_Estudiantes_{$nombre_materia}.xls";

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment;filename=\"{$nombre_archivo}\"");
header("Cache-Control: max-age=0");

// Título y subtítulo de la unidad educativa y nómina
echo "<table border='1' cellpadding='5' cellspacing='0' style='border-collapse: collapse; font-family: Arial, sans-serif;'>";
echo "<tr><td colspan='10' style='text-align: center; font-size: 18px; font-weight: bold; background-color: #f2f2f2;'>UNIDAD EDUCATIVA 'BENJAMÍN FRANKLIN'</td></tr>";
echo "<tr><td colspan='10' style='text-align: center; font-size: 16px; font-weight: bold;'>NÓMINA OFICIAL DE ESTUDIANTES DE LA CLASE</td></tr>";

// Espacio antes de la información del docente y materia
echo "<tr><td colspan='10'>&nbsp;</td></tr>";

// Mostrar el nombre del docente y materia antes de la tabla
echo "<tr><td colspan='10' style='text-align: center; font-size: 16px; font-weight: bold;'>Nombre del Docente: " . htmlspecialchars(utf8_encode($curso_info['profesor_nombre'])) . " " . htmlspecialchars(utf8_encode($curso_info['profesor_apellido'])) . "</td></tr>";
echo "<tr><td colspan='10' style='text-align: center; font-size: 16px; font-weight: bold;'>Materia: " . htmlspecialchars(utf8_encode($curso_info['materia'])) . "</td></tr>";

// Espacio antes de la tabla
echo "<tr><td colspan='10'>&nbsp;</td></tr>";

// Cabecera de la tabla
echo "<tr style='background-color: #f2f2f2; font-weight: bold;'>
            <th>ID Estudiante</th>
            <th>Nombre Estudiante</th>
            <th>Cédula</th>
            <th>Edad</th>
            <th>Género</th>
            <th>Discapacidad</th>
            <th>Nombre Padre</th>
            <th>Cédula Padre</th>
            <th>Teléfono</th>
        </tr>";

// Inicializar el contador
$contador = 1;

// Rellenar la tabla con los datos de los estudiantes
while ($row = $result_estudiantes->fetch_assoc()) {
    // Usamos el operador null-coalescing (??) para verificar si el índice existe, y en caso contrario mostramos un guion "-"
    echo "<tr>
            <td>" . $contador . "</td> <!-- Usamos el contador en lugar del id_estudiante -->
            <td>" . (isset($row['nombre_estudiante']) ? utf8_encode($row['nombre_estudiante']) : '-') . "</td>
            <td>" . (isset($row['cedula_estudiante']) ? utf8_encode($row['cedula_estudiante']) : '-') . "</td>
            <td>" . (isset($row['edad_estudiante']) ? utf8_encode($row['edad_estudiante']) : '-') . "</td>
            <td>" . (isset($row['genero_estudiante']) ? utf8_encode($row['genero_estudiante']) : '-') . "</td>
            <td>" . (isset($row['discapacidad_estudiante']) ? utf8_encode($row['discapacidad_estudiante']) : '-') . "</td>
            <td>" . (isset($row['nombre_completo_padre']) ? utf8_encode($row['nombre_completo_padre']) : '-') . "</td>
            <td>" . (isset($row['cedula_padre']) ? utf8_encode($row['cedula_padre']) : '-') . "</td>
            <td>" . (isset($row['telefono']) ? utf8_encode($row['telefono']) : '-') . "</td>
        </tr>";
    $contador++; // Incrementamos el contador
}

// Cerrar la tabla
echo "</table>";
exit();
?>