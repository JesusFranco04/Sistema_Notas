<?php
// Iniciar sesión
session_start();

// Verificar si el usuario ha iniciado sesión y si su rol es "Administrador" o "Superusuario"
if (!in_array($_SESSION['rol'], ['Administrador', 'Superusuario'])) {
    // Si el rol no es adecuado, redirigir al login
    header("Location: ../../login.php");
    exit(); // Detener la ejecución del código después de redirigir
}

require('../../fphp/fpdf.php'); // Ruta al archivo FPDF
include('../../Crud/config.php'); // Conexión a la base de datos

// Configurar la zona horaria de Ecuador
date_default_timezone_set('America/Guayaquil');

// Función para calcular la edad
function calcularEdad($fechaNacimiento) {
    $fechaNacimiento = new DateTime($fechaNacimiento);
    $hoy = new DateTime();
    $diferencia = $hoy->diff($fechaNacimiento);
    return $diferencia->y;
}

// Consultas para obtener estudiantes activos e inactivos
$queryActivos = "SELECT COUNT(*) AS total_activos FROM estudiante WHERE estado = 'A'";
$queryInactivos = "SELECT COUNT(*) AS total_inactivos FROM estudiante WHERE estado = 'I'";

// Ejecutar las consultas
$resultActivos = $conn->query($queryActivos);
$totalActivos = $resultActivos->fetch_assoc()['total_activos'];

$resultInactivos = $conn->query($queryInactivos);
$totalInactivos = $resultInactivos->fetch_assoc()['total_inactivos'];

// Calcular el total de estudiantes
$totalEstudiantes = $totalActivos + $totalInactivos;

// Consulta SQL para obtener los estudiantes activos, agrupados por nivel y ordenados por paralelo y año lectivo
$queryEstudiantes = "
    SELECT 
        e.id_estudiante, e.nombres, e.apellidos, e.cedula, e.telefono, 
        e.correo_electronico, e.direccion, e.fecha_nacimiento, 
        e.genero, e.discapacidad, e.id_nivel, e.id_subnivel, 
        e.id_especialidad, e.id_paralelo, e.id_jornada, 
        n.nombre AS nivel, sn.abreviatura AS subnivel, 
        es.nombre AS especialidad, p.nombre AS paralelo, 
        j.nombre AS jornada, ha.año AS historial_academico
    FROM 
        estudiante e
    INNER JOIN nivel n ON e.id_nivel = n.id_nivel
    INNER JOIN subnivel sn ON e.id_subnivel = sn.id_subnivel
    INNER JOIN especialidad es ON e.id_especialidad = es.id_especialidad
    INNER JOIN paralelo p ON e.id_paralelo = p.id_paralelo
    INNER JOIN jornada j ON e.id_jornada = j.id_jornada
    INNER JOIN historial_academico ha ON e.id_his_academico = ha.id_his_academico
    WHERE e.estado = 'A'
    ORDER BY n.id_nivel, e.id_subnivel, e.id_especialidad, e.id_paralelo, ha.año"; // Agrupación por nivel, paralelo y año lectivo

// Ejecutar la consulta
$resultEstudiantes = $conn->query($queryEstudiantes);

// Definición de la clase PDF
class PDF extends FPDF {
    function Header() {
        $this->SetFillColor(255, 255, 255);
        $this->Rect(0, 0, 297, 210, 'F');


        // Logo de la institución 
        $this->Image('../../imagenes/logo.png', 10, 10, 20);

        $this->Image('../../imagenes/logo.png', 267, 10, 20);
        // Agregar espacio antes de la fecha
        $this->Ln(5);


        $this->SetFont('Arial', 'B', 10);
        $this->SetTextColor(178, 34, 34);
        $this->Cell(0, 10, utf8_decode('UNIDAD EDUCATIVA "BENJAMÍN FRANKLIN"'), 0, 1, 'C');
        
        $this->SetFont('Arial', 'B', 11);
        $this->Cell(0, 10, utf8_decode('REPORTE DE MATRICULAS DE ESTUDIANTES'), 0, 1, 'C');

        $this->SetFont('Arial', 'I', 10);
        $fechaHora = date('d/m/Y H:i A');
        $this->Cell(0, 10, utf8_decode('Reporte generado el: ' . $fechaHora), 0, 1, 'R');

        $this->Ln(6);
    }

    function Footer() {
        // Pie de página
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(178, 34, 34);
        $this->Cell(0, 10, 'Pagina ' . $this->PageNo(), 0, 0, 'C');
    }

    function TableHeader() {
        // Encabezado de la tabla con colores y fuentes
        $this->SetFont('Arial', 'B', 6);
        $this->SetFillColor(178, 34, 34);
        $this->SetTextColor(255, 255, 255);
        $this->Cell(8, 5, 'N|', 1, 0, 'C', true);
        $this->Cell(20, 5, 'Nombres', 1, 0, 'C', true);
        $this->Cell(16, 5, 'Apellidos', 1, 0, 'C', true);
        $this->Cell(15, 5, 'Cedula', 1, 0, 'C', true);
        $this->Cell(18, 5, 'Telefono', 1, 0, 'C', true);
        $this->Cell(28, 5, 'Correo', 1, 0, 'C', true);
        $this->Cell(30, 5, 'Direccion', 1, 0, 'C', true);
        $this->Cell(10, 5, 'Edad', 1, 0, 'C', true);
        $this->Cell(16, 5, 'Genero', 1, 0, 'C', true);
        $this->Cell(18, 5, 'Discapacidad', 1, 0, 'C', true);
        $this->Cell(22, 5, 'Nivel', 1, 0, 'C', true);
        $this->Cell(14, 5, 'Subnivel', 1, 0, 'C', true);
        $this->Cell(18, 5, 'Especialidad', 1, 0, 'C', true);
        $this->Cell(12, 5, 'Paralelo', 1, 0, 'C', true);
        $this->Cell(15, 5, 'Jornada', 1, 0, 'C', true);
        $this->Cell(18, 5, 'Periodo Lectivo', 1, 1, 'C', true);
    }
    function Resumen($activos, $inactivos, $total) {
        $this->SetFont('Arial', 'B', 12);
        $this->SetFillColor(178, 34, 34);
        $this->SetTextColor(255, 255, 255);
        $this->Cell(0, 10, 'Resumen de Estudiantes', 0, 1, 'C', true);

        $this->SetFont('Arial', '', 10);
        $this->SetFillColor(245, 245, 245);
        $this->SetTextColor(0, 0, 0);

        $ancho = ($this->GetPageWidth() - 20) / 3;

        $this->Cell($ancho, 10, utf8_decode('Estudiantes Activos: ' . $activos), 1, 0, 'C', true);
        $this->Cell($ancho, 10, utf8_decode('Estudiantes Inactivos: ' . $inactivos), 1, 0, 'C', true);
        $this->Cell($ancho, 10, utf8_decode('Total de Estudiantes: ' . $total), 1, 1, 'C', true);

        $this->Ln(10);
    }
}

$pdf = new PDF('L', 'mm', 'A4');
$pdf->AddPage();
$pdf->SetFont('Arial', '', 6);

// Resumen
$pdf->Resumen($totalActivos, $totalInactivos, $totalEstudiantes);

// Encabezado de la tabla
$pdf->TableHeader();

$nivelActual = ''; // Inicializamos la variable
while ($estudiante = $resultEstudiantes->fetch_assoc()) {
    if ($nivelActual != $estudiante['nivel']) {
        $nivelActual = $estudiante['nivel'];
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->SetTextColor(178, 34, 34);
        $pdf->Cell(0, 10, utf8_decode('Nivel: ' . $nivelActual), 0, 1, 'L');
        $pdf->SetTextColor(0, 0, 0);
    }

    $edad = calcularEdad($estudiante['fecha_nacimiento']); // Calcular edad

    $pdf->SetFont('Arial', '', 6);
    $pdf->Cell(8, 5, $estudiante['id_estudiante'], 1, 0, 'C');
    $pdf->Cell(20, 5, utf8_decode($estudiante['nombres']), 1, 0, 'L');
    $pdf->Cell(16, 5, utf8_decode($estudiante['apellidos']), 1, 0, 'L');
    $pdf->Cell(15, 5, $estudiante['cedula'], 1, 0, 'C');
    $pdf->Cell(18, 5, $estudiante['telefono'], 1, 0, 'C');
    $pdf->Cell(28, 5, utf8_decode($estudiante['correo_electronico']), 1, 0, 'L');
    $pdf->Cell(30, 5, utf8_decode($estudiante['direccion']), 1, 0, 'L');
    $pdf->Cell(10, 5, $edad, 1, 0, 'C');
    $pdf->Cell(16, 5, utf8_decode($estudiante['genero']), 1, 0, 'C');
    $pdf->Cell(18, 5, utf8_decode($estudiante['discapacidad']), 1, 0, 'C');
    $pdf->Cell(22, 5, utf8_decode($estudiante['nivel']), 1, 0, 'C');
    $pdf->Cell(14, 5, utf8_decode($estudiante['subnivel']), 1, 0, 'C');
    $pdf->Cell(18, 5, utf8_decode($estudiante['especialidad']), 1, 0, 'C');
    $pdf->Cell(12, 5, utf8_decode($estudiante['paralelo']), 1, 0, 'C');
    $pdf->Cell(15, 5, utf8_decode($estudiante['jornada']), 1, 0, 'C');
    $pdf->Cell(18, 5, utf8_decode($estudiante['historial_academico']), 1, 1, 'C');
}

// Salida del PDF
$pdf->Output('D', 'Reporte_Matriculas_Estudiantes.pdf');
// Cerrar la conexión
$conn->close();
?>