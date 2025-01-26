<?php
// Iniciar sesión
session_start();

require('../../fphp/fpdf.php'); // Ruta al archivo FPDF
include('../../Crud/config.php'); // Conexión a la base de datos

// Configurar la zona horaria de Ecuador
date_default_timezone_set('America/Guayaquil');


// Verificar si el padre está autenticado
if (!isset($_SESSION['id_padre'])) {
    die("No tienes permiso para acceder a este reporte.");
}

$id_padre = $_SESSION['id_padre'];

// Obtener el id_estudiante desde la URL
if (isset($_GET['id_estudiante']) && is_numeric($_GET['id_estudiante'])) {
    $id_estudiante = (int)$_GET['id_estudiante'];
} else {
    die("ID de estudiante no proporcionado o inválido.");
}

// Verificar si el padre está asociado al estudiante
$query_padre_estudiante = "
    SELECT e.id_estudiante
    FROM padre_x_estudiante pxe
    JOIN estudiante e ON pxe.id_estudiante = e.id_estudiante
    WHERE pxe.id_padre = ? AND e.id_estudiante = ?
";

$stmt_padre_estudiante = $conn->prepare($query_padre_estudiante);
$stmt_padre_estudiante->bind_param("ii", $id_padre, $id_estudiante);
$stmt_padre_estudiante->execute();
$result_padre_estudiante = $stmt_padre_estudiante->get_result();

if ($result_padre_estudiante->num_rows === 0) {
    die("No se encontró la relación entre el padre y el estudiante.");
}

// Consultar los detalles del estudiante y sus calificaciones
$query_estudiante = "
    SELECT 
        e.nombres AS estudiante_nombres, e.apellidos AS estudiante_apellidos,
        n.nombre AS nivel, s.nombre AS subnivel, es.nombre AS especialidad,
        p.nombre AS paralelo, j.nombre AS jornada, ha.año AS año_lectivo,
        m.nombre AS materia,
        rn.nota1_primer_parcial AS nota1_p1, rn.nota2_primer_parcial AS nota2_p1, rn.examen_primer_parcial AS examen_p1,
        rn.nota1_segundo_parcial AS nota1_p2, rn.nota2_segundo_parcial AS nota2_p2, rn.examen_segundo_parcial AS examen_p2,
        rn2.nota1_primer_parcial AS nota1_p1_q2, rn2.nota2_primer_parcial AS nota2_p1_q2, rn2.examen_primer_parcial AS examen_p1_q2,
        rn2.nota1_segundo_parcial AS nota1_p2_q2, rn2.nota2_segundo_parcial AS nota2_p2_q2, rn2.examen_segundo_parcial AS examen_p2_q2,
        c.promedio_primer_quimestre, c.promedio_segundo_quimestre, c.nota_final, c.estado_calificacion
    FROM estudiante e
    JOIN nivel n ON e.id_nivel = n.id_nivel
    JOIN subnivel s ON e.id_subnivel = s.id_subnivel
    JOIN especialidad es ON e.id_especialidad = es.id_especialidad
    JOIN paralelo p ON e.id_paralelo = p.id_paralelo
    JOIN jornada j ON e.id_jornada = j.id_jornada
    JOIN historial_academico ha ON e.id_his_academico = ha.id_his_academico
    JOIN registro_nota rn ON e.id_estudiante = rn.id_estudiante
    JOIN materia m ON rn.id_materia = m.id_materia
    LEFT JOIN registro_nota rn2 ON e.id_estudiante = rn2.id_estudiante AND rn2.id_materia = rn.id_materia AND rn2.id_periodo = 2
    JOIN calificacion c ON e.id_estudiante = c.id_estudiante AND rn.id_materia = c.id_materia
    WHERE e.id_estudiante = ?
";

$stmt_estudiante = $conn->prepare($query_estudiante);
$stmt_estudiante->bind_param("i", $id_estudiante);
$stmt_estudiante->execute();
$result_estudiante = $stmt_estudiante->get_result();

$id_his_academico = $_GET['id_his_academico'];  // Si viene de la URL



// Crear PDF
class PDF extends FPDF {
    var $widths;

    // Línea decorativa
    function AddDecorativeLine($color)
    {
        // Establecer el color para la línea
        $this->SetDrawColor($color[0], $color[1], $color[2]); // Color RGB
        $this->SetLineWidth(0.8); // Grosor de la línea
        $this->Line(10, $this->GetY(),  286, $this->GetY()); // Dibuja una línea horizontal
        $this->Ln(5); // Espaciado después de la línea
    }

    function SetWidths($w) {
        $this->widths = $w;
    }

    function Header() {
        $this->Image('../../imagenes/logo.png', 10, 10, 20);
        $this->Image('../../imagenes/logo.png', 250, 10, 20); // Segundo logo alineado a la derecha
        $this->SetFont('Arial', 'B', 16);
        $this->SetTextColor(178, 34, 34); // Rojo elegante
        $this->Cell(0, 10, utf8_decode('UNIDAD EDUCATIVA "BENJAMÍN FRANKLIN"'), 0, 1, 'C');
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(0, 10, utf8_decode('LIBRETA DE CALIFICACIONES'), 0, 1, 'C');
        $this->Ln(5);
    }

    // Configuración del footer del PDF
    function Footer() {
        $this->SetY(-15); // Posicionar el pie a 15 mm desde el final de la página (bajarlo más)
        $this->SetFont('Arial', 'I', 8); // Fuente cursiva y tamaño reducido

        // Fecha, hora y número de página
        $fechaHora = date('d/m/Y H:i:s');
        $this->SetTextColor(67, 67, 67); // Gris intermedio
        $this->Cell(0, 3, utf8_decode('Reporte generado el: ') . $fechaHora . utf8_decode(' - Página ') . $this->PageNo() . '/{nb}', 0, 1, 'C');

        // Línea decorativa gris clara exactamente después del texto generado
        $this->SetDrawColor(220, 220, 220);
        $this->Line(43, $this->GetY() + 2, 252, $this->GetY() + 2); // Línea inmediatamente después del texto
        $this->Ln(3); // Espaciado entre la línea y la nota adicional

        // Nota adicional
        $this->SetFont('Arial', '', 8); // Reducir fuente para la nota
        $this->SetTextColor(67, 67, 67); // Gris intermedio
        $this->MultiCell(0, 5, utf8_decode(
            'Nota: Este certificado tiene validez únicamente para propósitos académicos. Para consultas adicionales, comuníquese con la Unidad Educativa "Benjamín Franklin".'
        ), 0, 'C'); // Texto centrado
    }

    function StudentInfo($data) {
        $this->SetFont('Arial', 'B', 11); // Tamaño de letra del encabezado ligeramente más pequeño
        $this->SetFillColor(178, 34, 34); // Rojo elegante para encabezados
        $this->SetTextColor(255, 255, 255); // Blanco para el texto
        $this->Cell(0, 10, utf8_decode('INFORMACIÓN DEL ESTUDIANTE'), 0, 1, 'C', true);
        $this->Ln(5);
        
        $this->SetFont('Arial', '', 9); // Tamaño de letra del contenido ligeramente más pequeño
        $this->SetTextColor(0, 0, 0); // Negro para el contenido
        $this->SetFillColor(245, 245, 245); // Gris claro para las celdas
        
        // Ajustamos el ancho de la tabla para que coincida con el encabezado
        $tableWidth = 276; // Ancho total de la tabla
        $this->SetX((297 - $tableWidth) / 2); // Centrar en una página A4 horizontal
    
        // Ajustando el alto de las celdas para que la tabla se vea más compacta
        $cellHeight = 6; // Alto más pequeño para las celdas de la tabla
        
        // Generamos las celdas de la tabla, manteniendo el mismo ancho
        $this->Cell(69, $cellHeight, utf8_decode("Nombres:"), 1, 0, 'L', true);
        $this->Cell(69, $cellHeight, utf8_decode($data['estudiante_nombres']), 1, 0, 'L');
        $this->Cell(69, $cellHeight, utf8_decode("Apellidos:"), 1, 0, 'L', true);
        $this->Cell(69, $cellHeight, utf8_decode($data['estudiante_apellidos']), 1, 1, 'L');
    
        $this->SetX((297 - $tableWidth) / 2);
        $this->Cell(69, $cellHeight, utf8_decode("Nivel:"), 1, 0, 'L', true);
        $this->Cell(69, $cellHeight, utf8_decode($data['nivel']), 1, 0, 'L');
        $this->Cell(69, $cellHeight, utf8_decode("Paralelo:"), 1, 0, 'L', true);
        $this->Cell(69, $cellHeight, utf8_decode($data['paralelo']), 1, 1, 'L');
    
        $this->SetX((297 - $tableWidth) / 2);
        $this->Cell(69, $cellHeight, utf8_decode("Subnivel:"), 1, 0, 'L', true);
        $this->Cell(69, $cellHeight, utf8_decode($data['subnivel']), 1, 0, 'L');
        $this->Cell(69, $cellHeight, utf8_decode("Especialidad:"), 1, 0, 'L', true);
        $this->Cell(69, $cellHeight, utf8_decode($data['especialidad']), 1, 1, 'L');
    
        $this->SetX((297 - $tableWidth) / 2);
        $this->Cell(69, $cellHeight, utf8_decode("Jornada:"), 1, 0, 'L', true);
        $this->Cell(69, $cellHeight, utf8_decode($data['jornada']), 1, 0, 'L');
        $this->Cell(69, $cellHeight, utf8_decode("Año Lectivo:"), 1, 0, 'L', true);
        $this->Cell(69, $cellHeight, utf8_decode($data['año_lectivo']), 1, 1, 'L');
        
        $this->Ln(10);
    }
    

    function GradesTable($thirdRow, $data) {
        // Calcular el ancho total de la tabla (suma de los anchos de las columnas)
        $totalWidth = 30 + (15 * 12) + (22 * 2) + (16 * 2);
    
        // Obtener el ancho de la página para centrar la tabla
        $pageWidth = $this->GetPageWidth();
        $startX = ($pageWidth - $totalWidth) / 2; // Posición inicial para centrar

    
        // Primera fila con dimensiones específicas
        $this->SetX($startX);
        $header = [
            '', 'Primer Quimestre', 'Segundo Quimestre', 'Calificación Final'
        ];
    
        $this->SetFont('Arial', 'B', 10); // Establecer la fuente Arial, negrita y tamaño ajustado a 10
        $this->SetFillColor(178, 34, 34); // Fondo rojo
        $this->SetTextColor(255, 255, 255); // Texto blanco
    
            // Mostrar la primera fila con el encabezado
        $columnCount = 0; // Contador para saber en qué celda estamos
        foreach ($header as $col) {
            if ($columnCount === 0) {
                    // Primera celda con ancho 30
                $this->Cell(30, 10, utf8_decode($col), 1, 0, 'C', true);
            } elseif ($columnCount === 1 || $columnCount === 2) {
                    // Segunda y tercera celdas con ancho 90
                $this->Cell(90, 10, utf8_decode($col), 1, 0, 'C', true);
            } elseif ($columnCount === 3) {
                    // Cuarta celda con ancho 60
                $this->Cell(76, 10, utf8_decode($col), 1, 0, 'C', true);
            }
                $columnCount++; // Incrementar el contador
        }
        $this->Ln(); // Salto de línea después de imprimir el encabezado
    
        // Segunda fila con dimensiones específicas
        $this->SetX($startX); // Asegurar que las celdas se dibujen centradas
        $secondRow = [
            '', 'Primer Parcial', 'Segundo Parcial', 'Primer Parcial', 'Segundo Parcial', 'Primer Parcial', 'Segundo Parcial',
            '', ''
        ];
            $this->SetFont('Arial', '', 8); // Fuente para la segunda fila
            $this->SetFillColor(255, 228, 225); // Rojo claro para filas alternas
            $this->SetTextColor(0, 0, 0); // Texto negro
            
        $columnCount = 0; // Reiniciar contador para la segunda fila
        foreach ($secondRow as $col) {
            if ($columnCount === 0) {
                    // Primera celda con ancho 30
                $this->Cell(30, 8, utf8_decode($col), 1, 0, 'C', true);
            } elseif ($columnCount >= 1 && $columnCount <= 4) {
                    // Segunda, tercera, cuarta y quinta celdas con ancho 45
                $this->Cell(45, 8, utf8_decode($col), 1, 0, 'C', true);
            } elseif ($columnCount === 5 || $columnCount === 6) {
                    // Sexta y séptima celdas con ancho 15
                $this->Cell(22, 8, utf8_decode($col), 1, 0, 'C', true);
            } elseif ($columnCount === 7 || $columnCount === 8) {
                    // Octava y novena celdas con ancho 15
                $this->Cell(16, 8, utf8_decode($col), 1, 0, 'C', true);
            }
                $columnCount++; // Incrementar el contador
        }
        $this->Ln(); // Salto de línea después de imprimir la segunda fila
    
        // Tercera fila: todos los campos 
        $thirdRow = [
            'Asignatura', 'Nota 1', 'Nota 2', 'Examen Final', 
            'Nota 1', 'Nota 2', 'Examen Final', 
            'Nota 1', 'Nota 2', 'Examen Final', 
            'Nota 1', 'Nota 2', 'Examen Final', 
            'Promedio', 'Promedio', 'Promedio Final', 'Resultado'
        ];
        
        $this->SetFont('Arial', 'B', 6); // Fuente para la tercera fila
        // Configuración del color de fondo (puedes elegir entre gris oscuro o rojo pastel)
        $this->SetFillColor(240, 240, 240); // Fondo gris claro
        
        // Asegurar la posición centrada
        $this->SetX($startX);
        
        $cellIndex = 1; // Contador para las celdas
        foreach ($thirdRow as $col) {
            if ($cellIndex === 1) {
                // Primera celda con ancho 30
                $this->Cell(30, 6, utf8_decode($col), 1, 0, 'C', true);
            } elseif ($cellIndex >= 2 && $cellIndex <= 13) {
                // Celdas de la 2 a la 12 con ancho 15
                $this->Cell(15, 6, utf8_decode($col), 1, 0, 'C', true);
            } elseif ($cellIndex === 14 || $cellIndex === 15) {
                // Celdas 13 y 14 con ancho 22
                $this->Cell(22, 6, utf8_decode($col), 1, 0, 'C', true);
            } elseif ($cellIndex === 16 || $cellIndex === 17) {
                // Celdas 15 y 16 con ancho 16
                $this->Cell(16, 6, utf8_decode($col), 1, 0, 'C', true);
            }
            $cellIndex++; // Incrementar el contador de celdas
        }
        
        $this->Ln(); // Salto de línea para pasar a la siguiente fila
        
        // Cuarta fila: datos de las calificaciones
        $this->SetFont('Arial', '', 6); // Fuente para los datos
        $this->SetFillColor(255, 255, 255); // Blanco para otras filas
        $this->SetTextColor(0, 0, 0); // Negro para el contenido de la tabla

        foreach ($data as $row) {
            // Asegúrate de que $row contiene los datos esperados antes de pasar a la celda
            $this->SetX($startX); // Restablecer la posición para centrarla correctamente.

            $cellIndex = 1; // Contador para las celdas dentro de la fila
            foreach ($row as $value) {
                if ($cellIndex === 1) {
                    // Primera celda con ancho 30
                    $this->Cell(30, 6, utf8_decode($value), 1, 0, 'C');
                } elseif ($cellIndex >= 2 && $cellIndex <= 13) {
                    // Celdas de la 2 a la 13 con ancho 15
                    $this->Cell(15, 6, utf8_decode($value), 1, 0, 'C');
                } elseif ($cellIndex === 14 || $cellIndex === 15) {
                    // Celdas 14 y 15 con ancho 22
                    $this->Cell(22, 6, utf8_decode($value), 1, 0, 'C');
                } elseif ($cellIndex === 16 || $cellIndex === 17) {
                    // Celdas 16 y 17 con ancho 16
                    $this->Cell(16, 6, utf8_decode($value), 1, 0, 'C');
                }

                $cellIndex++; // Incrementar el contador de celdas
            }
            $this->Ln(); // Salto de línea para pasar a la siguiente fila
        }    

    }

    function Signatures() {
        // Espaciado antes de las firmas
        $this->Ln(20); // Ajusta según la necesidad de espacio en el diseño
    
        // Configuración de la fuente para las líneas y títulos
        $this->SetFont('Arial', '', 10);
    
        // Ancho de la página (A4 horizontal es 278mm de ancho)
        $pageWidth = 278;
        $signatureWidth = 60; // Ancho de cada bloque de firma
    
        // Calculamos las posiciones centradas para cada sección
        $spacing = ($pageWidth - (3 * $signatureWidth)) / 4; // Espacio entre bloques de firma
    
        // Cambiar color de las líneas a negro
        $this->SetDrawColor(0, 0, 0); // Color negro para las líneas
        $this->SetLineWidth(0.2); // Grosor de la línea
    
        // Dibujar las líneas de firma centradas
        $this->Cell($spacing, 10, '', 0, 0, 'C'); // Espacio antes de la primera línea
        $this->Cell($signatureWidth, 0, '', 'B', 0, 'C'); // Línea del Rector
        $this->Cell($spacing, 10, '', 0, 0, 'C'); // Espacio entre líneas
        $this->Cell($signatureWidth, 0, '', 'B', 0, 'C'); // Línea del Secretario
        $this->Cell($spacing, 10, '', 0, 0, 'C'); // Espacio entre líneas
        $this->Cell($signatureWidth, 0, '', 'B', 1, 'C'); // Línea del Tutor
    
        // Espaciado entre las líneas y los títulos
        $this->Ln(5);
    
        // Cambiar a fuente en negrita para los títulos
        $this->SetFont('Arial', 'B', 10);
    
        // Dibujar los títulos centrados debajo de cada línea
        $this->Cell($spacing, 5, '', 0, 0, 'C'); // Espacio antes del primer título
        $this->Cell($signatureWidth, 5, 'Rector/(@)', 0, 0, 'C'); // Título del Rector
        $this->Cell($spacing, 5, '', 0, 0, 'C'); // Espacio entre títulos
        $this->Cell($signatureWidth, 5, 'Secretario/(@)', 0, 0, 'C'); // Título del Secretario
        $this->Cell($spacing, 5, '', 0, 0, 'C'); // Espacio entre títulos
        $this->Cell($signatureWidth, 5, 'Tutor/(@)', 0, 1, 'C'); // Título del Tutor
    }    
}

$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage('L'); // Orientación horizontal

// Consulta el año lectivo antes de generar la tabla
$sql = "SELECT DISTINCT año FROM historial_academico WHERE id_his_academico = $id_his_academico LIMIT 1";
$result_year = $conn->query($sql);
$year = ''; // Variable para almacenar el año lectivo

if ($result_year->num_rows > 0) {
    $row = $result_year->fetch_assoc();
    $year = $row['año']; // Asignar el valor del año correctamente
} else {
    $year = 'Año no disponible'; // Si no se encuentra, asigna un valor predeterminado
}

// Si el estudiante tiene información, se incluye en el PDF
if ($studentInfo = $result_estudiante->fetch_assoc()) {
    $pdf->StudentInfo($studentInfo);
}

// Tercera fila: todos los campos 
$thirdRow = [
    'Asignatura', 'Nota 1', 'Nota 2', 'Examen Final', 
    'Nota 1', 'Nota 2', 'Examen Final', 
    'Nota 1', 'Nota 2', 'Examen Final', 
    'Nota 1', 'Nota 2', 'Examen Final', 
    'Promedio', 'Promedio', 'Promedio Final', 'Resultado'
];
$data = [];
while ($row = $result_estudiante->fetch_assoc()) {
    $data[] = [
        $row['materia'],
        $row['nota1_p1'], $row['nota2_p1'], $row['examen_p1'],
        $row['nota1_p2'], $row['nota2_p2'], $row['examen_p2'],
        $row['nota1_p1_q2'], $row['nota2_p1_q2'], $row['examen_p1_q2'],
        $row['nota1_p2_q2'], $row['nota2_p2_q2'], $row['examen_p2_q2'],
        $row['promedio_primer_quimestre'], $row['promedio_segundo_quimestre'],
        $row['nota_final'], $row['estado_calificacion']
    ];
}
$pdf->GradesTable($thirdRow, $data);


// Espaciado superior
$pdf->Ln(5);

// Título del certificado
$pdf->SetFont('Arial', 'B', 14); // Fuente en negrita y tamaño grande
$pdf->SetTextColor(0, 51, 102); // Azul oscuro
$pdf->Cell(0, 10, utf8_decode('CERTIFICADO ACADÉMICO'), 0, 1, 'C'); // Título centrado
$pdf->Ln(3); // Espaciado

// Línea decorativa debajo del título
$pdf->AddDecorativeLine([0, 51, 102]); // Azul oscuro

// Cuerpo principal del texto
$pdf->SetFont('Arial', '', 10); // Fuente normal con tamaño ajustado a 10
$pdf->SetTextColor(50, 50, 50); // Gris oscuro
$pdf->MultiCell(0, 8, utf8_decode(
    'La Unidad Educativa "Benjamín Franklin" certifica que el estudiante ha culminado el periodo lectivo (' . $year . ') correspondiente. '
    . 'Este documento valida su cumplimiento con los estándares académicos y normativos establecidos por la institución, '
    . 'demostrando un compromiso destacado con el aprendizaje y su desarrollo personal.'
), 0, 'J'); // Texto justificado
$pdf->Ln(5); // Espaciado

$pdf->Signatures();
$pdf->Output('I', 'Reporte_Calificaciones_' . $id_estudiante . '.pdf');
?>