<?php 
// Iniciar sesión
session_start();

// Verificar si el usuario ha iniciado sesión y si su rol es "Profesor"
if (!isset($_SESSION['cedula']) || !in_array($_SESSION['rol'], ['Profesor'])) {
    // Si el rol no es adecuado, redirigir al login
    header("Location: ../../login.php");
    exit(); // Detener la ejecución del código después de redirigir
}

// Incluir FPDF
require('../../fphp/fpdf.php');
include('../../Crud/config.php'); // Conexión a la base de datos

// Configurar la zona horaria
date_default_timezone_set('America/Guayaquil');

// Limpiar el búfer de salida
ob_start();

// Verificar si el profesor está autenticado
if (!isset($_SESSION['id_profesor'])) {
    die("No tienes permiso para acceder a este reporte.");
}

$id_profesor = $_SESSION['id_profesor'];

// Verificar que el parámetro id_curso esté presente
if (!isset($_GET['id_curso']) || !filter_var($_GET['id_curso'], FILTER_VALIDATE_INT)) {
    die("Error: El parámetro 'id_curso' no está presente o no es válido.");
}

$id_curso = $_GET['id_curso'];

// Obtener datos del curso
$sql_curso = "
    SELECT c.*, p.nombres AS profesor_nombre, p.apellidos AS profesor_apellido, m.nombre AS materia
    FROM curso c
    INNER JOIN profesor p ON c.id_profesor = p.id_profesor
    LEFT JOIN materia m ON c.id_materia = m.id_materia
    WHERE c.id_curso = ?
";

$stmt_curso = $conn->prepare($sql_curso);
$stmt_curso->bind_param('i', $id_curso);
$stmt_curso->execute();
$result_curso = $stmt_curso->get_result();
$curso_info = $result_curso->fetch_assoc();

if (!$curso_info) {
    die("No se encontró información del curso especificado.");
}

// Consultar estudiantes
$sql_estudiantes = "
    SELECT 
        e.id_estudiante,
        CONCAT(e.nombres, ' ', e.apellidos) AS nombre_estudiante,
        e.cedula AS cedula_estudiante,
        TIMESTAMPDIFF(YEAR, e.fecha_nacimiento, CURDATE()) AS edad_estudiante,
        e.genero AS genero_estudiante,
        e.discapacidad AS discapacidad_estudiante,
        CONCAT(p.nombres, ' ', p.apellidos) AS nombre_completo_padre,
        p.cedula AS cedula_padre,
        p.telefono
    FROM 
        estudiante e
    LEFT JOIN 
        padre_x_estudiante px ON e.id_estudiante = px.id_estudiante
    LEFT JOIN 
        padre p ON px.id_padre = p.id_padre
    INNER JOIN 
        curso c ON 
        e.id_nivel = c.id_nivel AND
        e.id_subnivel = c.id_subnivel AND
        e.id_paralelo = c.id_paralelo AND
        e.id_jornada = c.id_jornada AND
        e.id_his_academico = c.id_his_academico
    WHERE 
        c.id_curso = ? AND 
        e.estado = 'A'
    ORDER BY e.apellidos ASC
";

$stmt_estudiantes = $conn->prepare($sql_estudiantes);
$stmt_estudiantes->bind_param('i', $id_curso);
$stmt_estudiantes->execute();
$result_estudiantes = $stmt_estudiantes->get_result();

if ($result_estudiantes->num_rows == 0) {
    die("No se encontraron estudiantes activos para el curso especificado.");
}

// Clase PDF extendida
class PDF extends FPDF {
    private $curso_info;

    function __construct($curso_info) {
        parent::__construct('L', 'mm', 'A4'); // Horizontal (landscape)
        $this->curso_info = $curso_info;
    }

    function Header() {
        // Fondo blanco
        $this->SetFillColor(255, 255, 255);
        $this->Rect(0, 0, 297, 210, 'F'); // Tamaño de la página A4 horizontal (297x210)

        // Logo de la institución a la izquierda
        $this->Image('../../imagenes/logo.png', 10, 10, 20);
        
        // Logo de la institución a la derecha
        $this->Image('../../imagenes/logo.png', 267, 10, 20);
        // Agregar espacio antes de la fecha
        $this->Ln(5);

        // Título principal
        $this->SetFont('Arial', 'B', 16);
        $this->SetTextColor(178, 34, 34); // Rojo elegante
        $this->Cell(0, 10, utf8_decode('UNIDAD EDUCATIVA "BENJAMÍN FRANKLIN"'), 0, 1, 'C');
        
        // Subtítulo
        $this->SetFont('Arial', 'B', 11);
        $this->Cell(0, 10, utf8_decode('NÓMINA OFICIAL DE ESTUDIANTES DE LA CLASE'), 0, 1, 'C');
        
        // Cuadro de información del curso
        $this->SetFont('Arial', 'B', 10);
        $this->SetTextColor(0, 0, 0);
        
        // Fecha de reporte en rojo
        $this->SetFont('Arial', 'I', 10);
        $this->SetTextColor(178, 34, 34);
        $fechaHora = date('d/m/Y H:i A');
        $this->Cell(0, 10, utf8_decode('Reporte generado el: ' . $fechaHora), 0, 1, 'R');
        $this->Ln(2); // Espacio después del encabezado

       // Título de la tabla
        $this->SetFont('Arial', 'B', 12); // Fuente estándar Arial
        $this->SetFillColor(178, 34, 34); // Rojo elegante
        $this->SetTextColor(255, 255, 255); // Blanco
        $this->Cell(276.2, 10, utf8_decode('Información de Clase'), 0, 1, 'C', true);

        // Texto de las celdas
        $this->SetFont('Arial', '', 10); // Fuente Arial para el contenido
        $this->SetFillColor(245, 245, 245); // Gris claro para el fondo de las celdas
        $this->SetTextColor(0, 0, 0); // Negro para el texto

        // Celdas centradas con información del curso
        $this->Cell(92, 10, utf8_decode('Código del Curso: ') . $this->curso_info['id_curso'], 1, 0, 'C', true);
        $this->Cell(92, 10, utf8_decode('Nombre del Curso: ') . utf8_decode($this->curso_info['materia']), 1, 0, 'C', true);
        $this->Cell(92, 10, utf8_decode('Docente: ') . utf8_decode($this->curso_info['profesor_nombre'] . ' ' . $this->curso_info['profesor_apellido']), 1, 1, 'C', true);

        // Agregar espacio antes de la fecha
        $this->Ln(10);
    }

    function Footer() {
        // Posición a 15 mm del final
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 10);
        $this->SetTextColor(178, 34, 34); // Rojo elegante
        $this->Cell(0, 10, utf8_decode('Página ') . $this->PageNo(), 0, 0, 'C');
    }

    function TableHeader() {
        // Ancho total de la tabla
        $tableWidth = 280; // Suma de los anchos de las columnas: 20 + 40 + 30 + 20 + 20 + 20 + 50 + 30 + 40
        $pageWidth = $this->GetPageWidth(); // Ancho de la página
        $this->SetX(($pageWidth - $tableWidth) / 2); // Centrar la tabla horizontalmente
    
        // Encabezado de la tabla con colores y fuentes
        $this->SetFont('Arial', 'B', 8); // Fuente más pequeña para los encabezados
        $this->SetFillColor(178, 34, 34); // Rojo elegante
        $this->SetTextColor(255, 255, 255); // Blanco
        $this->Cell(20, 8, 'ID Estudiante', 1, 0, 'C', true);
        $this->Cell(50, 8, utf8_decode('Nombre Estudiante'), 1, 0, 'C', true);
        $this->Cell(30, 8, utf8_decode('Cédula'), 1, 0, 'C', true);
        $this->Cell(20, 8, utf8_decode('Edad'), 1, 0, 'C', true);
        $this->Cell(20, 8, utf8_decode('Género'), 1, 0, 'C', true);
        $this->Cell(22, 8, utf8_decode('Discapacidad'), 1, 0, 'C', true);
        $this->Cell(50, 8, utf8_decode('Nombre Padre'), 1, 0, 'C', true);
        $this->Cell(30, 8, utf8_decode('Cédula Padre'), 1, 0, 'C', true);
        $this->Cell(40, 8, utf8_decode('Teléfono'), 1, 1, 'C', true);
    }
    
    // Modificación de la función AddRow
    function AddRow($row, $isOdd, &$contador) {  // Pasamos $contador por referencia
        // Ancho total de la tabla
        $tableWidth = 280; // Suma de los anchos de las columnas: 20 + 40 + 30 + 20 + 20 + 20 + 50 + 30 + 40
        $pageWidth = $this->GetPageWidth(); // Ancho de la página
        $this->SetX(($pageWidth - $tableWidth) / 2); // Centrar la tabla horizontalmente
    
        // Color de fondo para filas alternas (zebra striping)
        if ($isOdd) {
            $this->SetFillColor(255, 228, 225); // Rojo claro para filas alternas
        } else {
            $this->SetFillColor(255, 255, 255); // Blanco para otras filas
        }
    
        // Fuente y color del texto
        $this->SetFont('Arial', '', 8); // Fuente más pequeña para los datos
        $this->SetTextColor(0, 0, 0); // Negro para el contenido de la tabla

        // Mostrar el contador en lugar del id_estudiante
        $this->Cell(20, 8, $contador, 1, 0, 'C', true); // Usamos $contador en lugar de id_estudiante
        $contador++; // Incrementamos el contador

        $this->Cell(50, 8, utf8_decode($row['nombre_estudiante']), 1, 0, 'L', true);
        $this->Cell(30, 8, $row['cedula_estudiante'], 1, 0, 'C', true);
        $this->Cell(20, 8, $row['edad_estudiante'], 1, 0, 'C', true);
        $this->Cell(20, 8, utf8_decode($row['genero_estudiante']), 1, 0, 'C', true);
        $this->Cell(22, 8, utf8_decode($row['discapacidad_estudiante']), 1, 0, 'C', true);
        $this->Cell(50, 8, utf8_decode($row['nombre_completo_padre']), 1, 0, 'L', true);
        $this->Cell(30, 8, $row['cedula_padre'], 1, 0, 'C', true);
        $this->Cell(40, 8, $row['telefono'], 1, 1, 'C', true);
    }
}

// Crear objeto PDF con la información del curso
$pdf = new PDF($curso_info);
$pdf->AddPage();

// Encabezados de la tabla
$pdf->TableHeader();

// Inicializar el contador
$contador = 1;

// Mostrar los datos de los estudiantes
$isOdd = false; // Alternar colores de fila
while ($row = $result_estudiantes->fetch_assoc()) {
    $isOdd = !$isOdd; // Alternar entre true y false
    $pdf->AddRow($row, $isOdd, $contador); // Pasar el contador como parámetro
}

// Salida del PDF
$pdf->Output('D', 'Reporte_Nomina_Estudiantes.pdf');

// Cerrar la conexión
$conn->close();
?>