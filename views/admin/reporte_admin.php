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

// Consultas para obtener el resumen de administradores
$queryResumen = "SELECT 
                     (SELECT COUNT(*) FROM administrador a INNER JOIN usuario u ON a.id_usuario = u.id_usuario WHERE u.estado = 'A') AS activos,
                     (SELECT COUNT(*) FROM administrador a INNER JOIN usuario u ON a.id_usuario = u.id_usuario WHERE u.estado = 'I') AS inactivos,
                     (SELECT COUNT(*) FROM administrador a INNER JOIN usuario u ON a.id_usuario = u.id_usuario) AS total
                 FROM dual"; // Consulta para obtener los totales de activos, inactivos y el total

// Ejecutar la consulta de resumen
$resumenResult = $conn->query($queryResumen);
$resumen = $resumenResult->fetch_assoc();

// Consulta para obtener todos los administradores activos con todos los campos
$query = "SELECT a.id_administrador, a.nombres, a.apellidos, u.cedula, u.contraseña, a.telefono, a.correo_electronico, 
                 a.direccion, a.fecha_nacimiento, a.genero, a.discapacidad, u.id_usuario
          FROM administrador a
          INNER JOIN usuario u ON a.id_usuario = u.id_usuario
          WHERE u.estado = 'A'";  // Solo administradores activos

// Ejecutar la consulta de administradores
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
        $this->Cell(0, 10, utf8_decode('REPORTE DE ADMINISTRADORES'), 0, 1, 'C');
        
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
        $this->Cell(14, 8, 'N|', 1, 0, 'C', true);  // ID Admin
        $this->Cell(25, 8, 'Nombres', 1, 0, 'C', true);
        $this->Cell(25, 8, 'Apellidos', 1, 0, 'C', true);
        $this->Cell(20, 8, 'Cedula', 1, 0, 'C', true);
        $this->Cell(20, 8, 'Contrasena', 1, 0, 'C', true);  // Contraseña
        $this->Cell(20, 8, 'Telefono', 1, 0, 'C', true);
        $this->Cell(42, 8, 'Correo Electronico', 1, 0, 'C', true);
        $this->Cell(40, 8, 'Direccion', 1, 0, 'C', true);
        $this->Cell(12, 8, 'Edad', 1, 0, 'C', true);  // Edad en lugar de fecha de nacimiento
        $this->Cell(20, 8, 'Genero', 1, 0, 'C', true);
        $this->Cell(20, 8, 'Discapacidad', 1, 0, 'C', true);
        $this->Cell(20, 8, 'ID Usuario', 1, 1, 'C', true);  // ID Usuario
    }

    function TableSummary($activos, $inactivos, $total) {
        // Mostrar el resumen de administradores
        $this->SetFont('Arial', 'B', 12);
        $this->SetFillColor(178, 34, 34); // Rojo
        $this->SetTextColor(255, 255, 255); // Blanco
        $this->Cell(276, 10, 'Resumen de Administradores', 0, 1, 'C', true);

        // Establecer el estilo para los cuadros de resumen
        $this->SetFont('Arial', '', 10);
        $this->SetFillColor(245, 245, 245); // Color de fondo suave (gris claro)
        $this->SetTextColor(0, 0, 0); // Color de texto negro

        // Cuadro para "Administradores Activos"
        $this->Cell(92, 10, utf8_decode('Administradores Activos: ' . $activos), 1, 0, 'C', true);

        // Cuadro para "Administradores Inactivos"
        $this->Cell(92, 10, utf8_decode('Administradores Inactivos: ' . $inactivos), 1, 0, 'C', true);

        // Cuadro para "Total de Administradores"
        $this->Cell(92, 10, utf8_decode('Total de Administradores: ' . $total), 1, 1, 'C', true);

        // Insertar un espacio antes de la tabla
        $this->Ln(10);
    }
}

// Crear objeto PDF con orientación horizontal (L para Landscape)
$pdf = new PDF('L', 'mm', 'A4');
$pdf->AddPage();
$pdf->SetFont('Arial', '', 8);  // Fuente más grande para los datos

// Mostrar el resumen de administradores
$pdf->TableSummary($resumen['activos'], $resumen['inactivos'], $resumen['total']);

// Encabezados de la tabla
$pdf->TableHeader();

// Mostrar los datos de los administradores activos
$fill = false; // Alternar colores para filas
while ($usuario = $result->fetch_assoc()) {
    // Calcular la edad
    $edad = calcularEdad($usuario['fecha_nacimiento']);  // Calcular la edad

    $pdf->SetFont('Arial', '', 8); // Fuente más grande
    $pdf->SetTextColor(0, 0, 0); // Color negro para el contenido de la tabla

    // Alternar color de las filas
    $fill = !$fill;  // Cambiar el estado de la fila para alternar colores
    $color = $fill ? [245, 245, 245] : [255, 255, 255]; // Gris muy suave y blanco

    $pdf->SetFillColor($color[0], $color[1], $color[2]);

    $pdf->Cell(14, 8, $usuario['id_administrador'], 1, 0, 'C', true);  // ID Admin
    $pdf->Cell(25, 8, utf8_decode($usuario['nombres']), 1, 0, 'L', true);
    $pdf->Cell(25, 8, utf8_decode($usuario['apellidos']), 1, 0, 'L', true);
    $pdf->Cell(20, 8, $usuario['cedula'], 1, 0, 'C', true);
    $pdf->Cell(20, 8, utf8_decode($usuario['contraseña']), 1, 0, 'C', true);  // Contraseña
    $pdf->Cell(20, 8, $usuario['telefono'], 1, 0, 'C', true);  // Teléfono
    $pdf->Cell(42, 8, utf8_decode($usuario['correo_electronico']), 1, 0, 'L', true);
    $pdf->Cell(40, 8, utf8_decode($usuario['direccion']), 1, 0, 'L', true);
    $pdf->Cell(12, 8, $edad, 1, 0, 'C', true);  // Mostrar la edad
    $pdf->Cell(20, 8, utf8_decode($usuario['genero']), 1, 0, 'C', true);  // Género
    $pdf->Cell(20, 8, utf8_decode($usuario['discapacidad']), 1, 0, 'C', true);  // Discapacidad
    $pdf->Cell(20, 8, $usuario['id_usuario'], 1, 1, 'C', true);  // ID Usuario
}

// Salida del PDF
$pdf->Output('D', 'Reporte_Administradores.pdf');

// Cerrar la conexión
$conn->close();
?>
