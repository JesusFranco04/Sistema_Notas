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

// Crear PDF
class PDF extends FPDF {
    var $widths;

    function SetWidths($w) {
        $this->widths = $w;
    }

    function Header() {
        $this->Image('../../imagenes/logo.png', 10, 10, 20);
        $this->SetFont('Arial', 'B', 14);
        $this->SetTextColor(178, 34, 34);
        $this->Cell(0, 10, utf8_decode('UNIDAD EDUCATIVA "BENJAMÍN FRANKLIN"'), 0, 1, 'C');
        $this->SetFont('Arial', '', 12);
        $this->Cell(0, 5, utf8_decode('REPORTE DE CALIFICACIONES'), 0, 1, 'C');
        $this->Ln(10);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $fechaHora = date('d/m/Y H:i:s');
        $this->Cell(0, 10, 'Reporte generado el: ' . $fechaHora . ' - Página ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }

    function StudentInfo($data) {
        $this->SetFont('Arial', '', 10);
        $this->Cell(0, 10, utf8_decode('Información del Estudiante'), 0, 1, 'C');
        $this->Ln(5);

        $this->SetFont('Arial', '', 8);
        $this->SetFillColor(240, 240, 240);

        $this->Cell(45, 6, utf8_decode("Nombres: "), 1, 0, 'L', true);
        $this->Cell(45, 6, utf8_decode($data['estudiante_nombres']), 1, 0, 'L');
        $this->Cell(45, 6, utf8_decode("Apellidos: "), 1, 0, 'L', true);
        $this->Cell(45, 6, utf8_decode($data['estudiante_apellidos']), 1, 1, 'L');

        $this->Cell(45, 6, utf8_decode("Nivel: "), 1, 0, 'L', true);
        $this->Cell(45, 6, utf8_decode($data['nivel']), 1, 0, 'L');
        $this->Cell(45, 6, utf8_decode("Subnivel: "), 1, 0, 'L', true);
        $this->Cell(45, 6, utf8_decode($data['subnivel']), 1, 1, 'L');

        $this->Cell(45, 6, utf8_decode("Especialidad: "), 1, 0, 'L', true);
        $this->Cell(45, 6, utf8_decode($data['especialidad']), 1, 0, 'L');
        $this->Cell(45, 6, utf8_decode("Paralelo: "), 1, 0, 'L', true);
        $this->Cell(45, 6, utf8_decode($data['paralelo']), 1, 1, 'L');

        $this->Cell(45, 6, utf8_decode("Jornada: "), 1, 0, 'L', true);
        $this->Cell(45, 6, utf8_decode($data['jornada']), 1, 0, 'L');
        $this->Cell(45, 6, utf8_decode("Año Lectivo: "), 1, 0, 'L', true);
        $this->Cell(45, 6, utf8_decode($data['año_lectivo']), 1, 1, 'L');
        $this->Ln(10);
    }

    function GradesTable($header, $data) {
        $this->SetWidths([26, 15, 15, 15, 15, 15, 15, 15, 15, 15, 15, 15, 15, 15, 15, 15]);
        $this->SetFont('Arial', 'B', 6);
        $this->SetFillColor(200, 200, 200);
        foreach ($header as $col) {
            $this->Cell(15, 5, utf8_decode($col), 1, 0, 'C', true);
        }
        $this->Ln();

        $this->SetFont('Arial', '', 5);
        foreach ($data as $row) {
            foreach ($row as $value) {
                $this->Cell(15, 5, utf8_decode($value), 1, 0, 'C');
            }
            $this->Ln();
        }
    }

    function Certificate() {
        $this->Ln(15);
        $this->SetFont('Arial', '', 10);
        $this->MultiCell(0, 8, utf8_decode(
            'El presente documento certifica que el estudiante ha completado satisfactoriamente el periodo lectivo en la institución.'
        ), 0, 'C');
    }

    function Signatures() {
        $this->Ln(15);
        $this->SetFont('Arial', '', 10);

        // Diseño de las firmas en la misma línea
        $this->Cell(60, 10, '________________________', 0, 0, 'C');
        $this->Cell(60, 10, '________________________', 0, 0, 'C');
        $this->Cell(60, 10, '________________________', 0, 1, 'C');

        $this->Cell(60, 5, 'Rector', 0, 0, 'C');
        $this->Cell(60, 5, 'Secretario', 0, 0, 'C');
        $this->Cell(60, 5, 'Tutor', 0, 1, 'C');
    }
}

$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage('L'); // Orientación horizontal

if ($studentInfo = $result_estudiante->fetch_assoc()) {
    $pdf->StudentInfo($studentInfo);
}

$header = [
    'Materia', 'Nota 1 P1', 'Nota 2 P1', 'Examen P1', 'Nota 1 P2', 'Nota 2 P2', 'Examen P2',
    'Nota 1 P1 Q2', 'Nota 2 P1 Q2', 'Examen P1 Q2', 'Nota 1 P2 Q2', 'Nota 2 P2 Q2', 'Examen P2 Q2',
    'Prom. 1Q', 'Prom. 2Q', 'Final', 'Estado'
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
$pdf->GradesTable($header, $data);

$pdf->Ln(10); // Espaciado superior
$pdf->SetFont('Arial', 'B', 12); // Fuente en negrita y tamaño 12 para el encabezado
$pdf->SetTextColor(0, 0, 0); // Negro puro para un estilo profesional
$pdf->Cell(0, 10, utf8_decode('CERTIFICADO ACADÉMICO'), 0, 1, 'C'); // Título centrado
$pdf->Ln(5); // Espaciado

$pdf->SetFont('Arial', '', 10); // Fuente normal para el cuerpo del texto
$pdf->SetTextColor(50, 50, 50); // Gris oscuro
$pdf->MultiCell(0, 8, utf8_decode(
    'La Unidad Educativa "Benjamín Franklin" hace constar que el estudiante ha culminado satisfactoriamente el periodo lectivo correspondiente. '
    . 'Este documento certifica su cumplimiento con los estándares académicos y normativos establecidos por la institución, '
    . 'reflejando un compromiso destacado con el aprendizaje y el desarrollo personal.'
), 0, 'J'); // Justificado para presentación formal
$pdf->Ln(10);


$pdf->Signatures();

$pdf->Output('I', 'Reporte_Calificaciones_' . $id_estudiante . '.pdf');
?>