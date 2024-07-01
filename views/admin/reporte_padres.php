<?php
require('../../fphp/fpdf.php'); // Asegúrate de que la ruta a FPDF sea correcta
include '../../Crud/config.php';

// Consulta para obtener los datos de los profesores
$sql = "SELECT * FROM padres";
$resultado = $conn->query($sql);

if (!$resultado) {
    die("Error en la consulta: " . $conn->error);
}

// Crear una instancia de FPDF
$pdf = new FPDF();
$pdf->AddPage();

// Configurar el título del documento
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, 'Reporte de Padres', 0, 1, 'C');

// Espacio entre el título y la tabla
$pdf->Ln(10);

// Configurar el encabezado de la tabla
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(10, 10, 'ID', 1);
$pdf->Cell(30, 10, 'Nombres', 1);
$pdf->Cell(30, 10, 'Apellidos', 1);
$pdf->Cell(30, 10, 'Cedula', 1);
$pdf->Cell(30,10, 'contrasena');
$pdf->Ln();

// Configurar el contenido de la tabla
$pdf->SetFont('Arial', '', 10);
while ($fila = $resultado->fetch_assoc()) {
    $pdf->Cell(10, 10, $fila['id'], 1);
    $pdf->Cell(30, 10, $fila['nombres'], 1);
    $pdf->Cell(30, 10, $fila['apellidos'], 1);
    $pdf->Cell(30, 10, $fila['cedula'], 1);
    $pdf->Cell(30, 10, $fila['contrasena'], 1);
 
    $pdf->Ln();
}

// Output the PDF
$pdf->Output('D', 'reporte_padres.pdf');
?>
