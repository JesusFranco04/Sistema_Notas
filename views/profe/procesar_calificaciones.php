<?php
session_start();
include('../../Crud/config.php');

// Verificar si el usuario es un profesor
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'Profesor') {
    header("Location: ../../login.php");
    exit();
}

// Verificar que los datos requeridos estén presentes
if (!isset($_POST['id_curso'], $_POST['id_his_academico'], $_POST['id_periodo'])) {
    echo "Datos insuficientes.";
    exit();
}

$id_curso = intval($_POST['id_curso']);
$id_his_academico = intval($_POST['id_his_academico']);
$id_periodo = intval($_POST['id_periodo']);

// Obtener la lista de estudiantes
$estudiantes = obtenerEstudiantes($id_his_academico, $conn);

$errores = [];
$success = false;

foreach ($estudiantes as $estudiante) {
    $id_estudiante = $estudiante['id_estudiante'];
    
    if ($id_periodo == 1 || $id_periodo == 2) {
        $resultado = guardarCalificacionesPeriodos($id_curso, $id_estudiante, $id_his_academico, $id_periodo, $conn);
    } elseif ($id_periodo == 3) {
        $resultado = guardarCalificacionesPeriodo3($id_estudiante, $id_his_academico, $id_periodo, $conn);
    }

    if ($resultado !== true) {
        $errores[] = $resultado;
    }
}

// Llamar a la función para calcular y actualizar automáticamente
foreach ($estudiantes as $estudiante) {
    $id_estudiante = $estudiante['id_estudiante'];
    calcularYActualizar($id_estudiante, $id_his_academico, $conn);
}

if (empty($errores)) {
    $success = true;
    header("Location: registro_calificaciones.php?id_curso=$id_curso");
} else {
    echo "Se encontraron errores al procesar las calificaciones: <br>";
    foreach ($errores as $error) {
        echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8') . "<br>";
    }
}

// Cerrar la conexión
$conn->close();

/**
 * Obtener la lista de estudiantes
 *
 * @param int $id_his_academico
 * @param mysqli $conn
 * @return array
 */
function obtenerEstudiantes($id_his_academico, $conn) {
    $sql_estudiantes = "SELECT id_estudiante FROM estudiante WHERE id_his_academico = ?";
    $stmt = $conn->prepare($sql_estudiantes);
    $stmt->bind_param("i", $id_his_academico);
    $stmt->execute();
    $result = $stmt->get_result();
    $estudiantes = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $estudiantes;
}

/**
 * Guardar calificaciones para los periodos 1 y 2
 *
 * @param int $id_curso
 * @param int $id_estudiante
 * @param int $id_his_academico
 * @param int $id_periodo
 * @param mysqli $conn
 * @return string|true
 */
function guardarCalificacionesPeriodos($id_curso, $id_estudiante, $id_his_academico, $id_periodo, $conn) {
    $nota1_primer_parcial = isset($_POST['nota1_primer_parcial'][$id_estudiante]) ? floatval($_POST['nota1_primer_parcial'][$id_estudiante]) : null;
    $nota2_primer_parcial = isset($_POST['nota2_primer_parcial'][$id_estudiante]) ? floatval($_POST['nota2_primer_parcial'][$id_estudiante]) : null;
    $examen_primer_parcial = isset($_POST['examen_primer_parcial'][$id_estudiante]) ? floatval($_POST['examen_primer_parcial'][$id_estudiante]) : null;
    $nota1_segundo_parcial = isset($_POST['nota1_segundo_parcial'][$id_estudiante]) ? floatval($_POST['nota1_segundo_parcial'][$id_estudiante]) : null;
    $nota2_segundo_parcial = isset($_POST['nota2_segundo_parcial'][$id_estudiante]) ? floatval($_POST['nota2_segundo_parcial'][$id_estudiante]) : null;
    $examen_segundo_parcial = isset($_POST['examen_segundo_parcial'][$id_estudiante]) ? floatval($_POST['examen_segundo_parcial'][$id_estudiante]) : null;

    // Insertar nuevas calificaciones o actualizar si existe un registro
    $sql_insert = "INSERT INTO registro_nota (id_curso, id_estudiante, id_his_academico, id_periodo, nota1_primer_parcial, nota2_primer_parcial, examen_primer_parcial, nota1_segundo_parcial, nota2_segundo_parcial, examen_segundo_parcial) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE 
    nota1_primer_parcial = VALUES(nota1_primer_parcial),
    nota2_primer_parcial = VALUES(nota2_primer_parcial),
    examen_primer_parcial = VALUES(examen_primer_parcial),
    nota1_segundo_parcial = VALUES(nota1_segundo_parcial),
    nota2_segundo_parcial = VALUES(nota2_segundo_parcial),
    examen_segundo_parcial = VALUES(examen_segundo_parcial)";
    
    $stmt = $conn->prepare($sql_insert);
    $stmt->bind_param("iiiiiddddd", $id_curso, $id_estudiante, $id_his_academico, $id_periodo, $nota1_primer_parcial, $nota2_primer_parcial, $examen_primer_parcial, $nota1_segundo_parcial, $nota2_segundo_parcial, $examen_segundo_parcial);
    
    if ($stmt->execute()) {
        $stmt->close();
        return true;
    } else {
        $error = $stmt->error;
        $stmt->close();
        return $error;
    }
}

/**
 * Guardar calificaciones para el periodo 3
 *
 * @param int $id_estudiante
 * @param int $id_his_academico
 * @param int $id_periodo
 * @param mysqli $conn
 * @return string|true
 */
function guardarCalificacionesPeriodo3($id_estudiante, $id_his_academico, $id_periodo, $conn) {
    $promedio_primer_quimestre = isset($_POST['promedio_primer_quimestre'][$id_estudiante]) ? floatval($_POST['promedio_primer_quimestre'][$id_estudiante]) : null;
    $promedio_segundo_quimestre = isset($_POST['promedio_segundo_quimestre'][$id_estudiante]) ? floatval($_POST['promedio_segundo_quimestre'][$id_estudiante]) : null;
    $nota_final = ($promedio_primer_quimestre + $promedio_segundo_quimestre) / 2;

    if ($nota_final > 10) {
        $nota_final = 10;
    }

    $estado_calificacion = $nota_final >= 7 ? 'A' : 'R';
    $supletorio = $estado_calificacion == 'R' ? (isset($_POST['supletorio'][$id_estudiante]) ? floatval($_POST['supletorio'][$id_estudiante]) : null) : null;

    // Insertar o actualizar la calificación
    $sql_insert = "INSERT INTO calificacion (id_estudiante, promedio_primer_quimestre, promedio_segundo_quimestre, nota_final, supletorio, estado_calificacion) 
    VALUES (?, ?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE 
    promedio_primer_quimestre = VALUES(promedio_primer_quimestre),
    promedio_segundo_quimestre = VALUES(promedio_segundo_quimestre),
    nota_final = VALUES(nota_final),
    supletorio = VALUES(supletorio),
    estado_calificacion = VALUES(estado_calificacion)";
    
    $stmt = $conn->prepare($sql_insert);
    $stmt->bind_param("idddds", $id_estudiante, $promedio_primer_quimestre, $promedio_segundo_quimestre, $nota_final, $supletorio, $estado_calificacion);

    if ($stmt->execute()) {
        $stmt->close();
        return true;
    } else {
        $error = $stmt->error;
        $stmt->close();
        return $error;
    }
}

/**
 * Calcular y actualizar promedios, nota final y estado de calificación
 *
 * @param int $id_estudiante
 * @param int $id_his_academico
 * @param mysqli $conn
 */
function calcularYActualizar($id_estudiante, $id_his_academico, $conn) {
    // Calcular promedios de los quimestres
    $sql_calculate = "SELECT
        AVG((nota1_primer_parcial * 0.35) + (nota2_primer_parcial * 0.35) + (examen_primer_parcial * 0.30)) AS promedio_primer_quimestre,
        AVG((nota1_segundo_parcial * 0.35) + (nota2_segundo_parcial * 0.35) + (examen_segundo_parcial * 0.30)) AS promedio_segundo_quimestre
        FROM registro_nota
        WHERE id_estudiante = ? AND id_his_academico = ?";

    $stmt = $conn->prepare($sql_calculate);
    $stmt->bind_param("ii", $id_estudiante, $id_his_academico);
    $stmt->execute();
    $stmt->bind_result($promedio_primer_quimestre, $promedio_segundo_quimestre);
    $stmt->fetch();
    $stmt->close();

    $nota_final = ($promedio_primer_quimestre + $promedio_segundo_quimestre) / 2;
    $estado_calificacion = $nota_final >= 7 ? 'A' : 'R';

    // Actualizar calificaciones
    $sql_update = "INSERT INTO calificacion (id_estudiante, promedio_primer_quimestre, promedio_segundo_quimestre, nota_final, estado_calificacion)
    VALUES (?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE 
    promedio_primer_quimestre = VALUES(promedio_primer_quimestre),
    promedio_segundo_quimestre = VALUES(promedio_segundo_quimestre),
    nota_final = VALUES(nota_final),
    estado_calificacion = VALUES(estado_calificacion)";
    
    $stmt = $conn->prepare($sql_update);
    $stmt->bind_param("iddds", $id_estudiante, $promedio_primer_quimestre, $promedio_segundo_quimestre, $nota_final, $estado_calificacion);
    $stmt->execute();
    $stmt->close();

    // Manejar supletorio
    if ($estado_calificacion === 'R') {
        $supletorio = isset($_POST['supletorio'][$id_estudiante]) ? floatval($_POST['supletorio'][$id_estudiante]) : null;
        
        if ($supletorio !== null) {
            $nota_final_con_supletorio = ($nota_final + $supletorio) / 2;
            $estado_calificacion = $nota_final_con_supletorio >= 7 ? 'A' : 'R';

            $sql_update_supletorio = "UPDATE calificacion
            SET supletorio = ?, estado_calificacion = ?
            WHERE id_estudiante = ?";
            
            $stmt = $conn->prepare($sql_update_supletorio);
            $stmt->bind_param("dsi", $supletorio, $estado_calificacion, $id_estudiante);
            $stmt->execute();
            $stmt->close();
        }
    }

    // Actualizar nivel
    if ($estado_calificacion === 'A') {
        $sql_update_nivel = "UPDATE estudiante SET id_nivel = id_nivel + 1 WHERE id_estudiante = ?";
        $stmt = $conn->prepare($sql_update_nivel);
        $stmt->bind_param("i", $id_estudiante);
        $stmt->execute();
        $stmt->close();
    }
}
?>
