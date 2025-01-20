<?php
// Iniciar sesión
session_start();

// Incluir FPDF
require('../../fphp/fpdf.php'); // Ruta al archivo FPDF
include('../../Crud/config.php'); // Conexión a la base de datos

// Configurar la zona horaria de Ecuador
date_default_timezone_set('America/Guayaquil');

// Consultas para obtener los conteos
$queryActivos = "SELECT COUNT(*) AS cursos_activos FROM curso WHERE estado = 'A'";
$queryInactivos = "SELECT COUNT(*) AS cursos_inactivos FROM curso WHERE estado = 'I'";
$queryTotalCursos = "SELECT COUNT(*) AS total_cursos FROM curso";
$queryProfesores = "SELECT COUNT(DISTINCT id_profesor) AS total_profesores FROM curso WHERE estado = 'A'";

// Ejecutar las consultas
$resultActivos = $conn->query($queryActivos);
$resultInactivos = $conn->query($queryInactivos);
$resultTotalCursos = $conn->query($queryTotalCursos);
$resultProfesores = $conn->query($queryProfesores);

// Obtener los resultados
$rowActivos = $resultActivos->fetch_assoc();
$rowInactivos = $resultInactivos->fetch_assoc();
$rowTotalCursos = $resultTotalCursos->fetch_assoc();
$rowProfesores = $resultProfesores->fetch_assoc();


// Realizar la consulta
$query = "
SELECT 
    c.id_curso, m.nombre AS materia, 
    n.nombre AS nivel, 
    pa.nombre AS paralelo, 
    sn.abreviatura AS subnivel, 
    es.nombre AS especialidad, 
    j.nombre AS jornada, 
    ha.año AS historial_academico,
    p.nombres AS nombre_profesor, 
    p.apellidos AS apellido_profesor,
    c.usuario_ingreso, 
    c.fecha_ingreso
FROM 
    curso c
INNER JOIN profesor p ON c.id_profesor = p.id_profesor
INNER JOIN materia m ON c.id_materia = m.id_materia
INNER JOIN nivel n ON c.id_nivel = n.id_nivel
INNER JOIN subnivel sn ON c.id_subnivel = sn.id_subnivel
INNER JOIN especialidad es ON c.id_especialidad = es.id_especialidad
INNER JOIN paralelo pa ON c.id_paralelo = pa.id_paralelo
INNER JOIN jornada j ON c.id_jornada = j.id_jornada
INNER JOIN historial_academico ha ON c.id_his_academico = ha.id_his_academico
WHERE 
    c.estado = 'A' AND 
    p.id_usuario IN (SELECT id_usuario FROM usuario WHERE estado = 'A')
ORDER BY 
    p.apellidos,       -- Primero por Apellido del Profesor (A-Z)
    p.nombres,         -- Luego por Nombre del Profesor
    CASE n.nombre      -- Orden personalizado de los niveles
        WHEN 'Octavo' THEN 1
        WHEN 'Noveno' THEN 2
        WHEN 'Décimo' THEN 3
        WHEN 'Primero de Bachillerato' THEN 4
        WHEN 'Segundo de Bachillerato' THEN 5
        WHEN 'Tercero de Bachillerato' THEN 6
        ELSE 7                      -- Si hay otros niveles no definidos
    END,
    pa.nombre,                 -- Luego por Paralelo
    ha.año,                    -- Luego por Año
    sn.id_subnivel,            -- Luego por Subnivel
    j.id_jornada,              -- Luego por Jornada
    es.nombre,                 -- Luego por Especialidad
    m.nombre;                  -- Finalmente por Materia
";

// Usar $conn para realizar la consulta
$resultCursos = $conn->query($query);

// Verificar si la consulta fue exitosa
if (!$resultCursos) {
    die("Error en la consulta: " . $conn->error);
}

// Crear la clase PDF personalizada
class PDF extends FPDF {
    function Header() {
        // Fondo de marca de agua con el logotipo institucional
        $this->Image('../../imagenes/logo.png', 50, 80, 110, 0, '', '', true); // Marca de agua centrada
        
        // Fondo blanco
        $this->SetFillColor(255, 255, 255);
        $this->Rect(0, 0, 297, 210, 'F'); // Tamaño de la página A4 horizontal (297x210)
        
        // Logo de la institución
        $this->Image('../../imagenes/logo.png', 10, 10, 20);
        
        // Título principal
        $this->SetFont('Arial', 'B', 16);
        $this->SetTextColor(178, 34, 34); // Rojo elegante
        $this->Cell(0, 10, utf8_decode('UNIDAD EDUCATIVA "BENJAMÍN FRANKLIN"'), 0, 1, 'C');
        
        // Subtítulo
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 10, utf8_decode('REPORTE DE CURSOS'), 0, 1, 'C');
        
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
        $this->SetFont('Arial', 'B', 8); // Fuente más pequeña para los encabezados
        $this->SetFillColor(178, 34, 34); // Rojo elegante
        $this->SetTextColor(255, 255, 255); // Blanco
        $this->Cell(15, 8, 'ID Curso', 1, 0, 'C', true);
        $this->Cell(32, 8, 'Materia', 1, 0, 'C', true);
        $this->Cell(30, 8, 'Nivel', 1, 0, 'C', true);
        $this->Cell(15, 8, 'Paralelo', 1, 0, 'C', true);
        $this->Cell(20, 8, 'Subnivel', 1, 0, 'C', true);
        $this->Cell(20, 8, 'Especialidad', 1, 0, 'C', true);
        $this->Cell(20, 8, 'Jornada', 1, 0, 'C', true);
        $this->Cell(25, 8, 'Ciclo Academico', 1, 0, 'C', true);
        $this->Cell(45, 8, 'Profesor', 1, 0, 'C', true);
        $this->Cell(24, 8, 'Usuario Ingreso', 1, 0, 'C', true);
        $this->Cell(30, 8, 'Fecha Ingreso', 1, 1, 'C', true);
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
        $this->Cell(15, 8, $row['id_curso'], 1, 0, 'C', true);
        $this->Cell(32, 8, utf8_decode($row['materia']), 1, 0, 'L', true);
        $this->Cell(30, 8, utf8_decode($row['nivel']), 1, 0, 'L', true);
        $this->Cell(15, 8, utf8_decode($row['paralelo']), 1, 0, 'C', true);
        $this->Cell(20, 8, utf8_decode($row['subnivel']), 1, 0, 'C', true);
        $this->Cell(20, 8, utf8_decode($row['especialidad']), 1, 0, 'L', true);
        $this->Cell(20, 8, utf8_decode($row['jornada']), 1, 0, 'L', true);
        $this->Cell(25, 8, utf8_decode($row['historial_academico']), 1, 0, 'C', true);
        $this->Cell(45, 8, utf8_decode($row['nombre_profesor'] . ' ' . $row['apellido_profesor']), 1, 0, 'L', true);
        $this->Cell(24, 8, $row['usuario_ingreso'], 1, 0, 'C', true);
        $this->Cell(30, 8, $row['fecha_ingreso'], 1, 1, 'C', true);
    }
}

// Crear objeto PDF con orientación Horizontal ('L' para landscape)
$pdf = new PDF('L', 'mm', 'A4'); // 'L' para horizontal, 'mm' para milímetros, 'A4' tamaño
$pdf->AddPage();

// Mostrar resumen antes de la tabla
$pdf->SetFont('Arial', 'B', 14);
$pdf->SetFillColor(178, 34, 34); // Rojo elegante para el fondo
$pdf->SetTextColor(255, 255, 255); // Blanco para el texto
$pdf->Cell(0, 10, utf8_decode('Resumen del Reporte'), 0, 1, 'C', true);


// Establecer el estilo para los cuadros de resumen
$pdf->SetFont('Arial', '', 10);
$pdf->SetFillColor(245, 245, 245); // Color de fondo suave (gris claro)
$pdf->SetTextColor(0, 0, 0); // Color de texto negro

// Cuadro para "Cursos Activos"
$pdf->Cell(69, 10, utf8_decode('Cursos Activos: ' . $rowActivos['cursos_activos']), 1, 0, 'C', true);

// Cuadro para "Cursos Inactivos"
$pdf->Cell(69, 10, utf8_decode('Cursos Inactivos: ' . $rowInactivos['cursos_inactivos']), 1, 0, 'C', true);

// Cuadro para "Total de Cursos"
$pdf->Cell(69, 10, utf8_decode('Total de Cursos: ' . $rowTotalCursos['total_cursos']), 1, 0, 'C', true);


// Cuadro para "Total de Profesores"
$pdf->Cell(70, 10, utf8_decode('Total de Profesores: ' . $rowProfesores['total_profesores']), 1, 1, 'C', true);

// Insertar un espacio antes de la tabla
$pdf->Ln(10);

// Encabezados de la tabla
$pdf->TableHeader();

// Insertar los datos de los cursos y profesores
$profesorActual = '';
$isOdd = false; // Alternar colores de fila
// Iterar sobre los resultados
while ($curso = $resultCursos->fetch_assoc()) {
    // Verificar si el apellido del profesor ha cambiado
    if ($profesorActual != $curso['apellido_profesor']) {
        $profesorActual = $curso['apellido_profesor'];  // Cambiar el valor del apellido actual

        // Resaltar el apellido del profesor (en orden alfabético A-Z)
        $pdf->Ln(5); // Salto de línea entre profesores
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->SetTextColor(178, 34, 34); // Rojo elegante
        $pdf->Cell(0, 10, utf8_decode('Profesor: ' . $curso['nombre_profesor'] . ' ' . $curso['apellido_profesor']), 0, 1, 'L');
        $pdf->SetFont('Arial', '', 8);
    }

    $isOdd = !$isOdd; // Alternar entre true y false
    $pdf->AddRow($curso, $isOdd); // Añadir la fila
}

// Salida del PDF
$pdf->Output('D', 'Reporte_Cursos.pdf');

// Cerrar la conexión
$conn->close();
?>