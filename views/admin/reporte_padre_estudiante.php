<?php 
// Iniciar sesión
session_start();

// Incluir FPDF
require('../../fphp/fpdf.php'); // Ruta al archivo FPDF
include('../../Crud/config.php'); // Conexión a la base de datos

// Configurar la zona horaria de Ecuador
date_default_timezone_set('America/Guayaquil');

// Consulta SQL para obtener el total de estudiantes que tienen padres asociados
$queryEstudiantes = "SELECT COUNT(DISTINCT id_estudiante) AS total_estudiantes 
                     FROM padre_x_estudiante";
$resultEstudiantes = $conn->query($queryEstudiantes);
$totalEstudiantes = $resultEstudiantes->fetch_assoc()['total_estudiantes'];

// Consulta SQL para obtener el total de padres
$queryPadres = "SELECT COUNT(DISTINCT pe.id_padre) AS total_padres 
                FROM padre_x_estudiante pe";
$resultPadres = $conn->query($queryPadres);
$totalPadres = $resultPadres->fetch_assoc()['total_padres'];

// Consulta SQL para obtener los datos necesarios para el reporte
$query = "
    SELECT 
        e.id_estudiante, 
        e.nombres AS nombres_estudiante,
        e.apellidos AS apellidos_estudiante,
        e.cedula AS cedula_estudiante,
        n.nombre AS nivel,
        paralelo_table.nombre AS paralelo,
        padre_table.id_padre,
        padre_table.nombres AS nombres_padre,
        padre_table.apellidos AS apellidos_padre,
        padre_table.cedula AS cedula_padre,
        padre_table.parentesco,
        padre_table.telefono,
        padre_table.correo_electronico
    FROM 
        estudiante e
    JOIN 
        nivel n ON e.id_nivel = n.id_nivel
    JOIN 
        paralelo paralelo_table ON e.id_paralelo = paralelo_table.id_paralelo
    JOIN 
        padre_x_estudiante pe ON e.id_estudiante = pe.id_estudiante
    JOIN 
        padre padre_table ON pe.id_padre = padre_table.id_padre
    ORDER BY 
        CASE 
            WHEN n.nombre = 'Octavo' THEN 8
            WHEN n.nombre = 'Noveno' THEN 9
            WHEN n.nombre = 'Decimo' THEN 10
            WHEN n.nombre = 'Primero Bachillerato' THEN 11
            WHEN n.nombre = 'Segundo Bachillerato' THEN 12
            WHEN n.nombre = 'Tercero Bachillerato' THEN 13
            ELSE 99
        END ASC,
        paralelo_table.nombre ASC
";

// Ejecutar la consulta
$result = $conn->query($query);

// Definición de la clase PDF para el reporte
class PDF extends FPDF {
    function Header() {
        // Fondo blanco
        $this->SetFillColor(255, 255, 255);
        $this->Rect(0, 0, 297, 210, 'F'); // Tamaño de la página A4 horizontal (297x210)
        
        // Logo de la institución
        $this->Image('../../imagenes/logo.png', 10, 10, 20);
        
        // Título
        $this->SetFont('Arial', 'B', 14);
        $this->SetTextColor(178, 34, 34); // Rojo
        $this->Cell(0, 10, utf8_decode('UNIDAD EDUCATIVA "BENJAMÍN FRANKLIN"'), 0, 1, 'C');
        
        $this->SetFont('Arial', 'B', 11);
        $this->Cell(0, 10, utf8_decode('REPORTE DE ESTUDIANTES Y CONTACTO FAMILIAR'), 0, 1, 'C');
        
        $this->SetFont('Arial', 'I', 10);
        $fechaHora = date('d/m/Y H:i A');
        $this->Cell(0, 10, utf8_decode('Reporte generado el: ' . $fechaHora), 0, 1, 'R');

        $this->Ln(6);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(178, 34, 34);
        $this->Cell(0, 10, 'Pagina ' . $this->PageNo(), 0, 0, 'C');
    }

    function TableHeader() {
        // Encabezado de la tabla con colores y fuentes
        $this->SetFont('Arial', 'B', 6);  // Fuente más pequeña
        $this->SetFillColor(178, 34, 34); // Rojo
        $this->SetTextColor(255, 255, 255); // Blanco
        $this->Cell(18, 6, 'ID Estudiante', 1, 0, 'C', true);
        $this->Cell(25, 6, 'Nombres Estudiante', 1, 0, 'C', true);
        $this->Cell(25, 6, 'Apellidos Estudiante', 1, 0, 'C', true);
        $this->Cell(20, 6, 'Cedula', 1, 0, 'C', true);
        $this->Cell(22, 6, 'Nivel', 1, 0, 'C', true);
        $this->Cell(16, 6, 'Paralelo', 1, 0, 'C', true);
        $this->Cell(14, 6, 'ID Padre', 1, 0, 'C', true);
        $this->Cell(25, 6, 'Nombres Padre', 1, 0, 'C', true);
        $this->Cell(25, 6, 'Apellidos Padre', 1, 0, 'C', true);
        $this->Cell(20, 6, 'Cedula Padre', 1, 0, 'C', true);
        $this->Cell(20, 6, 'Parentesco', 1, 0, 'C', true);
        $this->Cell(20, 6, 'Telefono', 1, 0, 'C', true);
        $this->Cell(30, 6, 'Correo Electronico', 1, 1, 'C', true);
    }

    function Resumen($estudiantes, $padres) {
        $this->SetFont('Arial', 'B', 12);
        $this->SetFillColor(178, 34, 34);
        $this->SetTextColor(255, 255, 255);
        $this->Cell(0, 10, 'Resumen de Estudiantes y Padres', 0, 1, 'C', true);

        $this->SetFont('Arial', '', 10);
        $this->SetFillColor(245, 245, 245);
        $this->SetTextColor(0, 0, 0);

        $ancho = ($this->GetPageWidth() - 20) / 2;

        $this->Cell($ancho, 10, utf8_decode('Total de Estudiantes: ' . $estudiantes), 1, 0, 'C', true);
        $this->Cell($ancho, 10, utf8_decode('Total de Padres: ' . $padres), 1, 1, 'C', true);

        $this->Ln(10);
    }
}

// Crear objeto PDF con orientación Horizontal ('L' para landscape)
$pdf = new PDF('L', 'mm', 'A4'); // 'L' para horizontal, 'mm' para milímetros, 'A4' tamaño
$pdf->AddPage();
$pdf->SetFont('Arial', '', 5.5);  // Fuente más pequeña para los datos

// Resumen
$pdf->Resumen($totalEstudiantes, $totalPadres);

// Encabezado de la tabla
$pdf->TableHeader();

// Mostrar los datos de los estudiantes y sus padres
while ($row = $result->fetch_assoc()) {
    $pdf->SetFont('Arial', '', 5.5); // Fuente más pequeña
    $pdf->SetTextColor(0, 0, 0); // Color negro para el contenido de la tabla

    // Información del estudiante
    $pdf->Cell(18, 6, $row['id_estudiante'], 1, 0, 'C');
    $pdf->Cell(25, 6, utf8_decode($row['nombres_estudiante']), 1, 0, 'L');
    $pdf->Cell(25, 6, utf8_decode($row['apellidos_estudiante']), 1, 0, 'L');
    $pdf->Cell(20, 6, $row['cedula_estudiante'], 1, 0, 'C');
    $pdf->Cell(22, 6, utf8_decode($row['nivel']), 1, 0, 'C');
    $pdf->Cell(16, 6, utf8_decode($row['paralelo']), 1, 0, 'C');

    // Información del padre
    $pdf->Cell(14, 6, $row['id_padre'], 1, 0, 'C');
    $pdf->Cell(25, 6, utf8_decode($row['nombres_padre']), 1, 0, 'L');
    $pdf->Cell(25, 6, utf8_decode($row['apellidos_padre']), 1, 0, 'L');
    $pdf->Cell(20, 6, $row['cedula_padre'], 1, 0, 'C');
    $pdf->Cell(20, 6, utf8_decode($row['parentesco']), 1, 0, 'C');
    $pdf->Cell(20, 6, $row['telefono'], 1, 0, 'C');
    $pdf->Cell(30, 6, utf8_decode($row['correo_electronico']), 1, 1, 'L');
}

// Salida del PDF
$pdf->Output('D', 'Informe_Familia_Educativa.pdf');

// Cerrar la conexión
$conn->close();
?>