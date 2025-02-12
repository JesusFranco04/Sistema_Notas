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
    $fechaNacimiento = new DateTime($fechaNacimiento);  // Convertir la fecha a un objeto DateTime
    $hoy = new DateTime();  // Fecha actual
    $diferencia = $hoy->diff($fechaNacimiento);  // Calcular la diferencia entre la fecha actual y la fecha de nacimiento
    return $diferencia->y;  // Retorna la edad en años
}

// Consulta para obtener el total de representantes activos
$queryActivos = "SELECT COUNT(*) AS total_activos 
                 FROM padre p 
                 INNER JOIN usuario u ON p.id_usuario = u.id_usuario 
                 WHERE u.estado = 'A'";
$resultActivos = $conn->query($queryActivos);
$totalActivos = $resultActivos->fetch_assoc()['total_activos'];

// Consulta para obtener el total de representantes inactivos
$queryInactivos = "SELECT COUNT(*) AS total_inactivos 
                   FROM padre p 
                   INNER JOIN usuario u ON p.id_usuario = u.id_usuario 
                   WHERE u.estado = 'I'";
$resultInactivos = $conn->query($queryInactivos);
$totalInactivos = $resultInactivos->fetch_assoc()['total_inactivos'];

// Calcular el total de representantes
$totalRepresentantes = $totalActivos + $totalInactivos;

// Consulta para obtener todos los padres activos con todos los campos, incluyendo la contraseña
$query = "SELECT p.id_padre, p.nombres, p.apellidos, p.cedula, p.parentesco, p.telefono, 
                 p.correo_electronico, p.direccion, p.fecha_nacimiento, 
                 p.genero, p.discapacidad, p.id_usuario, u.contraseña
          FROM padre p
          INNER JOIN usuario u ON p.id_usuario = u.id_usuario
          WHERE u.estado = 'A'";  // Solo padres activos

// Ejecutar la consulta
$result = $conn->query($query);

// Definición de la clase PDF para el reporte
class PDF extends FPDF {
    function Header() {
        // Fondo blanco
        $this->SetFillColor(255, 255, 255);
        $this->Rect(0, 0, 297, 210, 'F'); // Cambiar tamaño para formato horizontal

        // Logo de la institución
        $this->Image('../../imagenes/logo.png', 10, 10, 15);

        // Título
        $this->SetFont('Arial', 'B', 12);  // Título con tamaño de fuente más pequeño
        $this->SetTextColor(178, 34, 34); // Rojo principal
        $this->Cell(0, 10, utf8_decode('UNIDAD EDUCATIVA "BENJAMÍN FRANKLIN"'), 0, 1, 'C');
        
        $this->SetFont('Arial', 'B', 11);
        $this->Cell(0, 10, utf8_decode('REPORTE DE REPRESENTANTES'), 0, 1, 'C');

        // Fecha de generación en formato simplificado
        $this->SetFont('Arial', 'I', 10);
        $fechaHora = date('d/m/Y H:i A'); // Formato simplificado: 16/01/2025 09:08 PM
        $this->Cell(0, 10, utf8_decode('Reporte generado el: ' . $fechaHora), 0, 1, 'R');

        $this->Ln(6); // Reducir el espacio
    }

    function Footer() {
        // Pie de página
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(178, 34, 34); // Rojo principal
        $this->Cell(0, 10, 'Pagina ' . $this->PageNo(), 0, 0, 'C');
    }

    function TableHeader() {
        // Encabezado de la tabla con colores y fuentes
        $this->SetFont('Arial', 'B', 8);  // Fuente más grande pero aún compacta
        $this->SetFillColor(178, 34, 34); // Rojo principal
        $this->SetTextColor(255, 255, 255); // Blanco
        $this->Cell(10, 8, 'N|', 1, 0, 'C', true);  // ID Representante
        $this->Cell(24, 8, 'Nombres', 1, 0, 'C', true);
        $this->Cell(22, 8, 'Apellidos', 1, 0, 'C', true);
        $this->Cell(19, 8, 'Cedula', 1, 0, 'C', true);
        $this->Cell(22, 8, 'Contrasena', 1, 0, 'C', true);  // Contraseña
        $this->Cell(24, 8, 'Parentesco', 1, 0, 'C', true);  // Parentesco
        $this->Cell(19, 8, 'Telefono', 1, 0, 'C', true);
        $this->Cell(39, 8, 'Correo Electronico', 1, 0, 'C', true);
        $this->Cell(38, 8, 'Direccion', 1, 0, 'C', true);
        $this->Cell(10, 8, 'Edad', 1, 0, 'C', true);  // Edad
        $this->Cell(18, 8, 'Genero', 1, 0, 'C', true);
        $this->Cell(21, 8, 'Discapacidad', 1, 0, 'C', true);
        $this->Cell(17, 8, 'ID Usuario', 1, 1, 'C', true);  // ID Usuario
    }
    function Resumen($activos, $inactivos, $total) {
        $this->SetFont('Arial', 'B', 12);
        $this->SetFillColor(178, 34, 34);
        $this->SetTextColor(255, 255, 255);
        $this->Cell(0, 10, 'Resumen de Representantes', 0, 1, 'C', true);

        $this->SetFont('Arial', '', 10);
        $this->SetFillColor(245, 245, 245);
        $this->SetTextColor(0, 0, 0);

        $ancho = ($this->GetPageWidth() - 20) / 3;

        $this->Cell($ancho, 10, utf8_decode('Representantes Activos: ' . $activos), 1, 0, 'C', true);
        $this->Cell($ancho, 10, utf8_decode('Representantes Inactivos: ' . $inactivos), 1, 0, 'C', true);
        $this->Cell($ancho, 10, utf8_decode('Total de Representantes: ' . $total), 1, 1, 'C', true);

        $this->Ln(10);
    }
}

// Crear objeto PDF con orientación horizontal (L para Landscape)
$pdf = new PDF('L', 'mm', 'A4');
$pdf->AddPage();
$pdf->SetFont('Arial', '', 8);  // Fuente más legible para los datos

// Resumen
$pdf->Resumen($totalActivos, $totalInactivos, $totalRepresentantes);

// Encabezados de la tabla
$pdf->TableHeader();

// Mostrar los datos de los padres activos
$fill = false; // Alternar colores para filas
while ($padre = $result->fetch_assoc()) {
    // Calcular la edad
    $edad = calcularEdad($padre['fecha_nacimiento']);  // Calcular la edad

    $pdf->SetFont('Arial', '', 8); // Fuente más legible
    $pdf->SetTextColor(0, 0, 0); // Color negro para el contenido de la tabla

    // Alternar color de las filas
    $fill = !$fill;  // Cambiar el estado de la fila para alternar colores
    $color = $fill ? [245, 245, 245] : [255, 255, 255]; // Gris muy suave y blanco

    $pdf->SetFillColor($color[0], $color[1], $color[2]);

    $pdf->Cell(10, 8, $padre['id_padre'], 1, 0, 'C', true);  // ID Representante
    $pdf->Cell(24, 8, utf8_decode($padre['nombres']), 1, 0, 'L', true);
    $pdf->Cell(22, 8, utf8_decode($padre['apellidos']), 1, 0, 'L', true);
    $pdf->Cell(19, 8, $padre['cedula'], 1, 0, 'C', true);
    $pdf->Cell(22, 8, utf8_decode($padre['contraseña']), 1, 0, 'C', true);  // Contraseña
    $pdf->Cell(24, 8, utf8_decode($padre['parentesco']), 1, 0, 'C', true);  // Parentesco
    $pdf->Cell(19, 8, $padre['telefono'], 1, 0, 'C', true);  // Teléfono
    $pdf->Cell(39, 8, utf8_decode($padre['correo_electronico']), 1, 0, 'L', true);
    $pdf->Cell(38, 8, utf8_decode($padre['direccion']), 1, 0, 'L', true);
    $pdf->Cell(10, 8, $edad, 1, 0, 'C', true);  // Mostrar la edad
    $pdf->Cell(18, 8, utf8_decode($padre['genero']), 1, 0, 'C', true);  // Género
    $pdf->Cell(21, 8, utf8_decode($padre['discapacidad']), 1, 0, 'C', true);  // Discapacidad
    $pdf->Cell(17, 8, $padre['id_usuario'], 1, 1, 'C', true);  // ID Usuario
}

// Salida del PDF
$pdf->Output('D', 'Reporte_Representantes.pdf');

// Cerrar la conexión
$conn->close();
?>
