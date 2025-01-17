<?php
// Iniciar sesión
session_start();

require('../../fphp/fpdf.php'); // Ruta al archivo FPDF
include('../../Crud/config.php'); // Conexión a la base de datos

// Configurar la zona horaria de Ecuador
date_default_timezone_set('America/Guayaquil');

// Función para calcular la edad
function calcularEdad($fechaNacimiento) {
    $fechaNacimiento = new DateTime($fechaNacimiento);  // Convertir la fecha a un objeto DateTime
    $hoy = new DateTime();  // Fecha actual
    $diferencia = $hoy->diff($fechaNacimiento);  // Calcular la diferencia entre la fecha actual y la fecha de nacimiento
    return $diferencia->y;  // Retorna la edad en años
}

// Consulta para obtener todos los profesores activos con todos los campos, incluyendo la contraseña
$query = "SELECT p.id_profesor, p.nombres, p.apellidos, p.cedula, p.telefono, 
                 p.correo_electronico, p.direccion, p.fecha_nacimiento, 
                 p.genero, p.discapacidad, p.id_usuario, u.contraseña
          FROM profesor p
          INNER JOIN usuario u ON p.id_usuario = u.id_usuario
          WHERE u.estado = 'A'";  // Solo profesores activos

// Ejecutar la consulta
$result = $conn->query($query);

// Definición de la clase PDF para el reporte
class PDF extends FPDF {
    function Header() {
        // Fondo blanco
        $this->SetFillColor(255, 255, 255);
        $this->Rect(0, 0, 297, 210, 'F'); // Cambiar tamaño para formato horizontal

        // Logo de la institución
        $this->Image('../../imagenes/logo.png', 10, 10, 20);

        // Título
        $this->SetFont('Arial', 'B', 16);  // Título con mayor tamaño
        $this->SetTextColor(178, 34, 34); // Rojo principal
        $this->Cell(0, 10, utf8_decode('UNIDAD EDUCATIVA "BENJAMÍN FRANKLIN"'), 0, 1, 'C');
        
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(0, 10, utf8_decode('REPORTE DE PROFESORES'), 0, 1, 'C');

        // Fecha de generación en formato simplificado
        $this->SetFont('Arial', 'I', 10);
        $fechaHora = date('d/m/Y H:i A'); // Formato simplificado: 16/01/2025 09:08 PM
        $this->Cell(0, 10, utf8_decode('Reporte generado el: ' . $fechaHora), 0, 1, 'R');

        $this->Ln(10);
    }

    function Footer() {
        // Pie de página
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 9);
        $this->SetTextColor(178, 34, 34); // Rojo principal
        $this->Cell(0, 10, 'Pagina ' . $this->PageNo(), 0, 0, 'C');
    }

    function TableHeader() {
        // Encabezado de la tabla con colores y fuentes
        $this->SetFont('Arial', 'B', 8);  // Fuente más grande
        $this->SetFillColor(178, 34, 34); // Rojo principal
        $this->SetTextColor(255, 255, 255); // Blanco
        $this->Cell(14, 8, 'N|', 1, 0, 'C', true);  // ID Profesor
        $this->Cell(25, 8, 'Nombres', 1, 0, 'C', true);
        $this->Cell(25, 8, 'Apellidos', 1, 0, 'C', true);
        $this->Cell(20, 8, 'Cedula', 1, 0, 'C', true);
        $this->Cell(20, 8, 'Contrasena', 1, 0, 'C', true);  // Nueva columna para la contraseña
        $this->Cell(20, 8, 'Telefono', 1, 0, 'C', true);
        $this->Cell(42, 8, 'Correo Electronico', 1, 0, 'C', true);
        $this->Cell(40, 8, 'Direccion', 1, 0, 'C', true);
        $this->Cell(12, 8, 'Edad', 1, 0, 'C', true);  // Edad en lugar de fecha de nacimiento
        $this->Cell(20, 8, 'Genero', 1, 0, 'C', true);
        $this->Cell(20, 8, 'Discapacidad', 1, 0, 'C', true);
        $this->Cell(20, 8, 'ID Usuario', 1, 1, 'C', true);  // ID Usuario
    }
}

// Crear objeto PDF con orientación horizontal (L para Landscape)
$pdf = new PDF('L', 'mm', 'A4');
$pdf->AddPage();
$pdf->SetFont('Arial', '', 8);  // Fuente más grande para los datos

// Encabezados de la tabla
$pdf->TableHeader();

// Mostrar los datos de los profesores activos
$fill = false; // Alternar colores para filas
while ($profesor = $result->fetch_assoc()) {
    // Calcular la edad
    $edad = calcularEdad($profesor['fecha_nacimiento']);  // Calcular la edad

    $pdf->SetFont('Arial', '', 8); // Fuente más grande
    $pdf->SetTextColor(0, 0, 0); // Color negro para el contenido de la tabla

    // Alternar color de las filas
    $fill = !$fill;  // Cambiar el estado de la fila para alternar colores
    $color = $fill ? [245, 245, 245] : [255, 255, 255]; // Gris muy suave y blanco

    $pdf->SetFillColor($color[0], $color[1], $color[2]);

    $pdf->Cell(14, 8, $profesor['id_profesor'], 1, 0, 'C', true);  // ID Profesor
    $pdf->Cell(25, 8, utf8_decode($profesor['nombres']), 1, 0, 'L', true);
    $pdf->Cell(25, 8, utf8_decode($profesor['apellidos']), 1, 0, 'L', true);
    $pdf->Cell(20, 8, $profesor['cedula'], 1, 0, 'C', true);
    $pdf->Cell(20, 8, utf8_decode($profesor['contraseña']), 1, 0, 'C', true);  // Contraseña
    $pdf->Cell(20, 8, $profesor['telefono'], 1, 0, 'C', true);  // Teléfono
    $pdf->Cell(42, 8, utf8_decode($profesor['correo_electronico']), 1, 0, 'L', true);
    $pdf->Cell(40, 8, utf8_decode($profesor['direccion']), 1, 0, 'L', true);
    $pdf->Cell(12, 8, $edad, 1, 0, 'C', true);  // Mostrar la edad
    $pdf->Cell(20, 8, utf8_decode($profesor['genero']), 1, 0, 'C', true);  // Género
    $pdf->Cell(20, 8, utf8_decode($profesor['discapacidad']), 1, 0, 'C', true);  // Discapacidad
    $pdf->Cell(20, 8, $profesor['id_usuario'], 1, 1, 'C', true);  // ID Usuario
}

// Salida del PDF
$pdf->Output('D', 'Reporte_Profesores.pdf');

// Cerrar la conexión
$conn->close();
?>
