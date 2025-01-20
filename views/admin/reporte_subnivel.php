<?php
// Iniciar sesión
session_start();

// Incluir FPDF
require('../../fphp/fpdf.php'); // Ruta al archivo FPDF
include('../../Crud/config.php'); // Conexión a la base de datos

// Configurar la zona horaria de Ecuador
date_default_timezone_set('America/Guayaquil');

// Consulta SQL para obtener los subniveles activos
$query = "
    SELECT 
        id_subnivel, 
        nombre, 
        abreviatura, 
        usuario_ingreso, 
        fecha_ingreso 
    FROM 
        subnivel 
    WHERE 
        estado = 'A' 
    ORDER BY 
        id_subnivel ASC
";

// Ejecutar la consulta
$result = $conn->query($query);

// Definición de la clase PDF para el reporte
class PDF extends FPDF {
    function Header() {
        // Fondo de marca de agua con el logotipo institucional
        $this->Image('../../imagenes/logo.png', 50, 80, 110, 0, '', '', true); // Marca de agua centrada
        
        // Fondo blanco
        $this->SetFillColor(255, 255, 255);
        $this->Rect(0, 0, 210, 297, 'F'); // Tamaño de la página A4 vertical (210x297)
        
        // Logo de la institución
        $this->Image('../../imagenes/logo.png', 10, 10, 20);
        
        // Título principal
        $this->SetFont('Arial', 'B', 16);
        $this->SetTextColor(178, 34, 34); // Rojo elegante
        $this->Cell(0, 10, utf8_decode('UNIDAD EDUCATIVA "BENJAMÍN FRANKLIN"'), 0, 1, 'C');
        
        // Subtítulo
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 10, utf8_decode('REPORTE DE SUBNIVELES EDUCATIVOS'), 0, 1, 'C');
        
        // Fecha y hora
        $this->SetFont('Arial', 'I', 10);
        $fechaHora = date('d/m/Y H:i A');
        $this->Cell(0, 10, utf8_decode('Reporte generado el: ' . $fechaHora), 0, 1, 'R');

        $this->Ln(6); // Espacio después del encabezado
    }

    function Footer() {
        // Posición a 15 mm del final
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(178, 34, 34); // Rojo elegante
        $this->Cell(0, 10, utf8_decode('Página ') . $this->PageNo(), 0, 0, 'C');
    }

    function TableHeader() {
        // Encabezado de la tabla con colores y fuentes (sin el campo 'Estado')
        $this->SetFont('Arial', 'B', 8); // Fuente más pequeña para los encabezados
        $this->SetFillColor(178, 34, 34); // Rojo elegante
        $this->SetTextColor(255, 255, 255); // Blanco
        $this->Cell(20, 8, 'ID Subnivel', 1, 0, 'C', true);
        $this->Cell(60, 8, 'Nombre del Subnivel', 1, 0, 'C', true);
        $this->Cell(30, 8, 'Abreviatura', 1, 0, 'C', true);
        $this->Cell(40, 8, 'Usuario Ingreso', 1, 0, 'C', true);
        $this->Cell(40, 8, 'Fecha de Ingreso', 1, 1, 'C', true);
    }

    function AddRow($row, $isOdd) {
        // Color de fondo para filas alternas (zebra striping)
        if ($isOdd) {
            $this->SetFillColor(255, 228, 225); // Rojo claro para filas alternas
        } else {
            $this->SetFillColor(255, 255, 255); // Blanco para otras filas
        }

        // Fuente y color del texto
        $this->SetFont('Arial', '', 8); // Fuente más pequeña para los datos
        $this->SetTextColor(0, 0, 0); // Negro para el contenido de la tabla

        // Mostrar los datos de la fila (sin el campo 'estado')
        $this->Cell(20, 8, $row['id_subnivel'], 1, 0, 'C', true);
        $this->Cell(60, 8, utf8_decode($row['nombre']), 1, 0, 'L', true);
        $this->Cell(30, 8, utf8_decode($row['abreviatura']), 1, 0, 'C', true);
        $this->Cell(40, 8, utf8_decode($row['usuario_ingreso']), 1, 0, 'L', true);
        $this->Cell(40, 8, date('d/m/Y', strtotime($row['fecha_ingreso'])), 1, 1, 'C', true);
    }
}

// Crear objeto PDF con orientación Vertical ('P' para portrait)
$pdf = new PDF('P', 'mm', 'A4'); // 'P' para vertical, 'mm' para milímetros, 'A4' tamaño
$pdf->AddPage();

// Encabezados de la tabla
$pdf->TableHeader();

// Mostrar los datos de los subniveles activos
$isOdd = false; // Alternar colores de fila
while ($row = $result->fetch_assoc()) {
    $isOdd = !$isOdd; // Alternar entre true y false
    $pdf->AddRow($row, $isOdd);
}

// Salida del PDF
$pdf->Output('D', 'Reporte_Subniveles_Educativos.pdf');

// Cerrar la conexión
$conn->close();
?>