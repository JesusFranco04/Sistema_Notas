<?php  
require('../../fphp/fpdf.php'); // Incluir la librería FPDF

// Incluir la configuración de la base de datos
include '../../Crud/config.php';

// Recuperar los estudiantes que fueron seleccionados para subir de nivel
$estudiantes_seleccionados = isset($_GET['estudiantes']) ? $_GET['estudiantes'] : [];
if (empty($estudiantes_seleccionados)) {
    die('No se han seleccionado estudiantes.');
}


// Crear la consulta SQL para obtener los datos de los estudiantes
$placeholders = implode(',', array_fill(0, count($estudiantes_seleccionados), '?'));
$sql_estudiantes = "SELECT e.id_estudiante, e.nombres, e.apellidos, n.nombre AS nivel_actual, 
                    n1.nombre AS nivel_nuevo, e.usuario_ingreso, e.fecha_ingreso
                    FROM estudiante e
                    JOIN nivel n ON e.id_nivel = n.id_nivel
                    JOIN nivel n1 ON e.id_nivel + 1 = n1.id_nivel
                    WHERE e.id_estudiante IN ($placeholders)
                    ORDER BY e.apellidos ASC"; // Ordenar por apellidos (de la A a la Z)

// Preparar y ejecutar la consulta con los estudiantes seleccionados
if ($stmt = $conn->prepare($sql_estudiantes)) {
    $stmt->bind_param(str_repeat('i', count($estudiantes_seleccionados)), ...$estudiantes_seleccionados);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    die('Error en la consulta de estudiantes: ' . $conn->error);
}

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
        $this->Cell(0, 10, utf8_decode('REPORTE DE SUBIDA DE NIVEL DE ESTUDIANTES'), 0, 1, 'C');
        
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
        $this->SetFont('Arial', 'B', 8); // Fuente más grande para los encabezados
        $this->SetFillColor(178, 34, 34); // Rojo elegante
        $this->SetTextColor(255, 255, 255); // Blanco
        $this->Cell(21, 8, 'ID Estudiante', 1, 0, 'C', true);
        $this->Cell(37, 8, 'Nombres', 1, 0, 'C', true);
        $this->Cell(39, 8, 'Apellidos', 1, 0, 'C', true);
        $this->Cell(34, 8, 'Nivel Actual', 1, 0, 'C', true);
        $this->Cell(34, 8, 'Nivel Nuevo', 1, 1, 'C', true);
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

        // Mostrar los datos de la fila
        $this->Cell(21, 8, $row['id_estudiante'], 1, 0, 'C', true);
        $this->Cell(37, 8, utf8_decode($row['nombres']), 1, 0, 'L', true);
        $this->Cell(39, 8, utf8_decode($row['apellidos']), 1, 0, 'L', true);
        $this->Cell(34, 8, utf8_decode($row['nivel_actual']), 1, 0, 'C', true);
        $this->Cell(34, 8, utf8_decode($row['nivel_nuevo']), 1, 1, 'C', true);
    }
}

$pdf = new PDF();
$pdf->AddPage();

// Inicializar las variables para contar el total de estudiantes y los niveles actuales
$total_estudiantes = 0;
$niveles_actuales = [];
$niveles_nuevos = []; // Inicialización de la variable

// Agrupar los estudiantes por nivel nuevo
while ($row = $result->fetch_assoc()) {
    $niveles_actuales[] = $row['nivel_actual'];
    $niveles_nuevos[$row['nivel_nuevo']][] = $row;
    $total_estudiantes++;
}

// Mostrar el resumen antes de la tabla
$pdf->SetFont('Arial', 'B', 14);
$pdf->SetFillColor(178, 34, 34); // Rojo elegante para el fondo
$pdf->SetTextColor(255, 255, 255); // Blanco para el texto
$pdf->Cell(0, 10, utf8_decode('Resumen del Reporte'), 0, 1, 'C', true);

// Mostrar total de estudiantes y niveles actuales
$pdf->SetFont('Arial', '', 10);
$pdf->SetFillColor(245, 245, 245); // Color de fondo suave (gris claro)
$pdf->SetTextColor(0, 0, 0); // Color de texto negro

$pdf->Cell(0, 10, utf8_decode("Total de estudiantes que pasaron de nivel: " . $total_estudiantes), 0, 1, 'L', true);

// Mostrar los niveles actuales
$pdf->Cell(0, 10, utf8_decode("Niveles actuales de los estudiantes: " . implode(", ", array_unique($niveles_actuales))), 0, 1, 'L', true);

// Salto de línea antes de la tabla
$pdf->Ln(8);

// Mostrar los estudiantes agrupados por nivel nuevo
$isOdd = false; // Alternar colores de fila
foreach ($niveles_nuevos as $nivelNuevo => $estudiantes) {
    // Mostrar el título del nivel nuevo
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetTextColor(0, 0, 0); // Negro para el texto
    $pdf->Cell(0, 10, utf8_decode('Nivel Nuevo: ' . $nivelNuevo), 0, 1, 'L');
    $pdf->Ln(4); // Salto de línea entre el título del nivel y la tabla

    // Mostrar los encabezados de la tabla
    $pdf->TableHeader();

    // Agregar las filas de estudiantes para este nivel
    foreach ($estudiantes as $row) {
        $isOdd = !$isOdd; // Alternar entre true y false para las filas
        $pdf->AddRow($row, $isOdd); // Añadir la fila del estudiante
    }

    $pdf->Ln(6); // Espacio después de cada nivel
}

// Información adicional
$pdf->Ln(10);
$pdf->SetFont('Arial', 'I', 10);
$pdf->Cell(0, 10, "Generado por: " . $row['usuario_ingreso'] . " el " . date('d/m/Y H:i:s'), 0, 1, 'L');

// Salida del PDF
$pdf->Output('D', 'Reporte_Subida_Nivel.pdf');

// Cerrar la conexión
$conn->close();
?>
