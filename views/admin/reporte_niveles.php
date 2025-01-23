<?php
// Iniciar sesión
session_start();

// Incluir FPDF
require('../../fphp/fpdf.php'); // Ruta al archivo FPDF
include('../../Crud/config.php'); // Conexión a la base de datos

// Configurar la zona horaria de Ecuador
date_default_timezone_set('America/Guayaquil');

// Consulta SQL para obtener los niveles activos
$query = "
    SELECT 
        id_nivel, 
        nombre, 
        estado, 
        usuario_ingreso, 
        fecha_ingreso 
    FROM 
        nivel 
    WHERE 
        estado = 'A' 
    ORDER BY 
        id_nivel ASC
";

// Consulta SQL para obtener los niveles inactivos (para el resumen)
$queryInactivos = "
    SELECT 
        id_nivel 
    FROM 
        nivel 
    WHERE 
        estado = 'I'
";

// Ejecutar las consultas
$result = $conn->query($query);
$resultInactivos = $conn->query($queryInactivos);

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
        $this->Cell(0, 10, utf8_decode('REPORTE DE NIVELES EDUCATIVOS'), 0, 1, 'C');
        
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
        // Encabezado de la tabla con colores y fuentes
        $this->SetFont('Arial', 'B', 10);
        $this->SetFillColor(178, 34, 34); // Rojo elegante
        $this->SetTextColor(255, 255, 255); // Blanco
        $this->Cell(20, 10, 'ID Nivel', 1, 0, 'C', true);
        $this->Cell(90, 10, 'Nombre del Nivel', 1, 0, 'C', true);
        $this->Cell(40, 10, 'Usuario Ingreso', 1, 0, 'C', true);
        $this->Cell(40, 10, 'Fecha de Ingreso', 1, 1, 'C', true);
    }

    function AddRow($row, $isOdd) {
        // Color de fondo para filas alternas (zebra striping)
        if ($isOdd) {
            $this->SetFillColor(255, 228, 225); // Rojo claro para filas alternas
        } else {
            $this->SetFillColor(255, 255, 255); // Blanco para otras filas
        }

        // Fuente y color del texto
        $this->SetFont('Arial', '', 10);
        $this->SetTextColor(0, 0, 0); // Negro para el contenido de la tabla

        // Mostrar los datos de la fila
        $this->Cell(20, 10, $row['id_nivel'], 1, 0, 'C', true);
        $this->Cell(90, 10, utf8_decode($row['nombre']), 1, 0, 'L', true);
        $this->Cell(40, 10, utf8_decode($row['usuario_ingreso']), 1, 0, 'L', true);
        $this->Cell(40, 10, date('d/m/Y', strtotime($row['fecha_ingreso'])), 1, 1, 'C', true);
    }

    // Función para mostrar el cuadro de resumen de niveles
    function SummaryBox($activos, $inactivos, $total) {
        // Título del cuadro
        $this->SetFont('Arial', 'B', 12);
        $this->SetFillColor(178, 34, 34);
        $this->SetTextColor(255, 255, 255);
        $this->Cell(189, 10, 'Resumen de Niveles', 0, 1, 'C', true);

        // Resumen de niveles activos, inactivos y total
        $this->SetFont('Arial', '', 10);
        $this->SetFillColor(245, 245, 245);
        $this->SetTextColor(0, 0, 0);

        $this->Cell(63, 10, 'Niveles Activos: ' . $activos, 1, 0, 'C', true);
        $this->Cell(63, 10, 'Niveles Inactivos: ' . $inactivos, 1, 0, 'C', true);
        $this->Cell(63, 10, 'Total de Niveles: ' . $total, 1, 1, 'C', true);
        $this->Ln(10);
    }
}

// Crear objeto PDF con orientación Vertical ('P' para portrait)
$pdf = new PDF('P', 'mm', 'A4'); // 'P' para vertical, 'mm' para milímetros, 'A4' tamaño
$pdf->AddPage();

// Calcular el número de niveles activos e inactivos
$totalActivos = $result->num_rows;
$totalInactivos = $resultInactivos->num_rows;
$totalNiveles = $totalActivos + $totalInactivos;

// Mostrar el cuadro de resumen de niveles
$pdf->SummaryBox($totalActivos, $totalInactivos, $totalNiveles);

// Encabezados de la tabla
$pdf->TableHeader();

// Mostrar los datos de los niveles activos
$isOdd = false; // Alternar colores de fila
while ($row = $result->fetch_assoc()) {
    $isOdd = !$isOdd; // Alternar entre true y false
    $pdf->AddRow($row, $isOdd);
}

// Salida del PDF
$pdf->Output('D', 'Reporte_Niveles_Educativos.pdf');

// Cerrar la conexión
$conn->close();
?>