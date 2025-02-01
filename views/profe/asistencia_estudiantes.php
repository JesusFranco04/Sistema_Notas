<?php  
// Iniciar sesión
session_start();

// Incluir FPDF
require('../../fphp/fpdf.php');
include('../../Crud/config.php');

// Verificar si el profesor está autenticado
if (!isset($_SESSION['id_profesor'])) {
    $mensaje_error = urlencode("No tienes permiso para acceder a este reporte. Es posible que no hayas iniciado sesión como profesor.");
    header("Location: ver_estudiantes.php?id_curso=<?php echo $id_curso; ?>&error=$mensaje_error");
    exit;
}

$id_profesor = $_SESSION['id_profesor'];

// Verificar que el parámetro id_curso esté presente y sea un número entero
if (!isset($_GET['id_curso']) || !filter_var($_GET['id_curso'], FILTER_VALIDATE_INT)) {
    $mensaje_error = urlencode("Error: El parámetro 'id_curso' no está presente o no es válido. Asegúrate de que la URL contenga el identificador correcto del curso.");
    header("Location: ver_estudiantes.php?id_curso=<?php echo $id_curso; ?>&error=$mensaje_error");
    exit;
}

// Obtener el id_curso desde la URL
$id_curso = $_GET['id_curso'];

// Obtener el año académico activo
$sql = "SELECT año FROM historial_academico WHERE estado = 'A' ORDER BY fecha_ingreso DESC LIMIT 1";
$result = $conn->query($sql);
$año_academico = $result->fetch_assoc()['año'] ?? null;

// Si no hay un año académico activo, mostrar error
if (!$año_academico) {
    $mensaje_error = urlencode("Error: No se encontró un año académico activo. Puede que no haya registros en el sistema o que el año académico esté inactivo.");
    header("Location: ver_estudiantes.php?id_curso=<?php echo $id_curso; ?>&error=$mensaje_error");
    exit;
}

// Eliminar espacios al principio y al final
$año_academico = trim($año_academico);

// Eliminar cualquier espacio dentro del valor (si existiera)
$año_academico = str_replace(' ', '', $año_academico);

// Validar el formato del año académico
if (!preg_match('/^\d{4}-\d{4}$/', $año_academico)) {
    $mensaje_error = urlencode("Error: Formato de año académico no válido. El valor obtenido es: " . $año_academico . ". Asegúrate de que el año académico siga el formato 'YYYY-YYYY'.");
    header("Location: ver_estudiantes.php?id_curso=<?php echo $id_curso; ?>&error=$mensaje_error");
    exit;
}

// Dividir el rango de años
list($primer_ano, $segundo_ano) = explode("-", $año_academico);

// Si la división de los años falla, mostrar un mensaje de error
if (!is_numeric($primer_ano) || !is_numeric($segundo_ano)) {
    $mensaje_error = urlencode("Error: Los años académicos no son números válidos. El valor obtenido es: $año_academico. Revisa el formato.");
    header("Location: ver_estudiantes.php?id_curso=<?php echo $id_curso; ?>&error=$mensaje_error");
    exit;
}

// Intentar encontrar el historial académico con el año exacto
$sql_historial = "SELECT id_his_academico FROM historial_academico WHERE año = ? AND estado = 'A' LIMIT 1";
$stmt_historial = $conn->prepare($sql_historial);
$stmt_historial->bind_param("s", $año_academico);
$stmt_historial->execute();
$result_historial = $stmt_historial->get_result();
$historial_data = $result_historial->fetch_assoc();
$stmt_historial->close();

// Si no se encuentra el historial para el año exacto, buscar el más reciente activo
if (!$historial_data) {
    $sql_historial = "SELECT id_his_academico, año FROM historial_academico WHERE estado = 'A' ORDER BY fecha_ingreso DESC LIMIT 1";
    $result_historial = $conn->query($sql_historial);
    $historial_data = $result_historial->fetch_assoc();
}

// Si aún así no se encuentra un historial activo, mostrar error
if (!$historial_data) {
    $mensaje_error = urlencode("Error: No se encontró ningún historial académico activo. Verifica si el historial académico está registrado y activo en el sistema.");
    header("Location: ver_estudiantes.php?id_curso=<?php echo $id_curso; ?>&error=$mensaje_error");
    exit;
}

// Asignar el ID del historial académico activo
$id_his_academico = $historial_data['id_his_academico'];
$año_academico = $historial_data['año']; // Actualizar el año en caso de haber tomado el último activo

// Obtener información del curso y profesor en una sola consulta
$sql_curso = "SELECT c.id_curso, c.id_profesor, p.nombres AS profesor_nombres, p.apellidos AS profesor_apellidos,
                     n.nombre AS nivel, s.nombre AS subnivel, e.nombre AS especialidad, j.nombre AS jornada, 
                     pa.nombre AS paralelo
              FROM curso c
              JOIN profesor p ON c.id_profesor = p.id_profesor
              JOIN nivel n ON c.id_nivel = n.id_nivel
              JOIN subnivel s ON c.id_subnivel = s.id_subnivel
              JOIN especialidad e ON c.id_especialidad = e.id_especialidad
              JOIN jornada j ON c.id_jornada = j.id_jornada
              JOIN paralelo pa ON c.id_paralelo = pa.id_paralelo  
              WHERE c.id_curso = ?";

$stmt_curso = $conn->prepare($sql_curso);
$stmt_curso->bind_param("i", $id_curso);
$stmt_curso->execute();
$result_curso = $stmt_curso->get_result();
$curso_data = $result_curso->fetch_assoc();
$stmt_curso->close();

// Verificar que se encontró el curso y profesor
if (!$curso_data) {
    $mensaje_error = urlencode("Error: No se encontró el curso o el profesor. Verifica que el curso esté registrado correctamente y que el profesor esté asociado a él.");
    header("Location: ver_estudiantes.php?id_curso=<?php echo $id_curso; ?>&error=$mensaje_error");
    exit;
}

// Asignar las variables de curso y profesor
$profesor = $curso_data;
$curso = $curso_data;

// Consulta para obtener los estudiantes del curso
$sql_estudiantes = "SELECT e.id_estudiante, e.nombres, e.apellidos
    FROM estudiante e
    JOIN curso c 
    ON e.id_nivel = c.id_nivel 
    AND e.id_paralelo = c.id_paralelo
    AND e.id_subnivel = c.id_subnivel
    AND e.id_especialidad = c.id_especialidad
    AND e.id_jornada = c.id_jornada
    AND c.id_his_academico = ? 
    WHERE c.id_curso = ? /* Año académico activo */
    ORDER BY e.apellidos, e.nombres";

// Preparar la consulta
$stmt_estudiantes = $conn->prepare($sql_estudiantes);

// Asegúrate de usar dos parámetros: uno para el ID del año académico y otro para el ID del curso
$stmt_estudiantes->bind_param("ii", $id_his_academico, $id_curso);

// Ejecutar la consulta
$stmt_estudiantes->execute();

// Obtener el resultado
$result_estudiantes = $stmt_estudiantes->get_result();

// Almacenar los resultados en el arreglo $estudiantes
$estudiantes = [];
while ($row = $result_estudiantes->fetch_assoc()) {
    $estudiantes[] = $row;  // Añadir cada estudiante al array
}

// Cerrar la consulta
$stmt_estudiantes->close();

// Verificar si no hay estudiantes
if (empty($estudiantes)) {
    $mensaje_error = urlencode("Error: No se encontraron estudiantes para el curso. Asegúrate de que todos los estudiantes estén matriculados en el curso con el año académico correcto.");
    header("Location: ver_estudiantes.php?id_curso=<?php echo $id_curso; ?>&error=$mensaje_error");
    exit;
}

// Consulta para obtener el nombre de la materia
$sql_materia = "SELECT m.nombre AS nombre_materia
                FROM curso c
                JOIN materia m ON c.id_materia = m.id_materia
                WHERE c.id_curso = ?";
$stmt_materia = $conn->prepare($sql_materia);
$stmt_materia->bind_param("i", $id_curso);
$stmt_materia->execute();
$result_materia = $stmt_materia->get_result();
$materia_data = $result_materia->fetch_assoc();
$stmt_materia->close();

// Verificar que se encontró la materia
$nombre_materia = $materia_data['nombre_materia'] ?? 'Materia no encontrada';

// Verificar si hay estudiantes
if (empty($estudiantes)) {
    $mensaje_error = urlencode("Error: No se encontraron estudiantes para el curso.");
    header("Location: ver_estudiantes.php?id_curso=<?php echo $id_curso; ?>&error=$mensaje_error");
    exit;
}

// Definir las fechas de inicio y fin con los cambios solicitados
$fecha_inicio = DateTime::createFromFormat('d-m-Y', "01-01-$primer_ano"); // Comienza el 01 de enero del primer año
$fecha_fin = DateTime::createFromFormat('d-m-Y', "31-03-$segundo_ano"); // Termina el 31 de marzo del segundo año
// Verificar si la creación de fechas fue exitosa
if (!$fecha_inicio || !$fecha_fin) {
    $mensaje_error = urlencode("Error al formatear las fechas. Verifique el formato de las fechas.");
    header("Location: ver_estudiantes.php?id_curso=<?php echo $id_curso; ?>&error=$mensaje_error");
    exit;
}

// Crear el array de fechas dinámicamente
$fechas = [];
$current_date = clone $fecha_inicio;  // Clonar la fecha de inicio para no modificarla

// Recorrer todas las fechas desde $fecha_inicio hasta $fecha_fin
while ($current_date <= $fecha_fin) {
    // Agregar la fecha al array en el formato "Y-m-d"
    $fechas[] = $current_date->format('Y-m-d');
    
    // Avanzar un día
    $current_date->modify('+1 day');
}


// Clase personalizada de PDF
class PDF extends FPDF {
    var $fecha_inicio;
    var $fecha_fin;
    var $mes_nombre;
    var $fechas;

    // Constructor de la clase para pasar las fechas al PDF
    function __construct($fecha_inicio, $fecha_fin, $fechas) {
        parent::__construct('L', 'mm', 'A4'); // 'L' para landscape, 'mm' para milímetros, 'A4' para tamaño de hoja
        $this->fecha_inicio = $fecha_inicio;
        $this->fecha_fin = $fecha_fin;
        $this->fechas = $fechas;
    }
    
    function Header() {
        // Accedemos a las fechas formateadas
        $fecha_inicio_formateada = $this->fecha_inicio->format('d-m-Y');
        $fecha_fin_formateada = $this->fecha_fin->format('d-m-Y');

        // Aquí puedes seguir con el código de la cabecera del PDF
        $this->Image('../../imagenes/logo.png', 10, 10, 18); // Logo izquierdo más pequeño
        $this->Image('../../imagenes/logo.png', 263, 10, 18); // Logo derecho más pequeño

        // Títulos con fuente más pequeña
        $this->SetFont('Arial', 'B', 14); // Fuente más pequeña para el título
        $this->SetTextColor(178, 34, 34); // Rojo elegante
        $this->Cell(0, 10, utf8_decode('UNIDAD EDUCATIVA "BENJAMÍN FRANKLIN"'), 0, 1, 'C');
        $this->SetFont('Arial', 'B', 10); // Tamaño más pequeño para el mes
        $this->SetTextColor(178, 34, 34); // Rojo para el mes
        $this->Cell(0, 6, utf8_decode('CONTROL DE ASISTENCIA DEL MES: ' . strtoupper($this->mes_nombre)), 0, 1, 'C');
        // Aquí mostramos las fechas de inicio y fin
        $this->SetFont('Arial', '', 10);
        $this->Cell(0, 6, utf8_decode('Ciclo Académico: ' . $fecha_inicio_formateada . ' - ' . $fecha_fin_formateada), 0, 1, 'C');
        $this->Ln(3);
        $this->SetDrawColor(178, 34, 34);
        $this->SetLineWidth(0.5); // Línea más delgada
        $this->Line(10, 35, 287, 35);
        $this->Ln(5);
    }

    function CursoInfo($data) {
        // Título del cuadro de información con fuente más pequeña
        $this->SetFont('Arial', 'B', 10); // Tamaño más pequeño para el título
        $this->SetFillColor(178, 34, 34); // Color del título
        $this->SetTextColor(255, 255, 255);
        $this->Cell(0, 7, utf8_decode('INFORMACIÓN DEL CURSO'), 0, 1, 'C', true);
        $this->Ln(3);

        // Detalles del curso con tamaño reducido
        $this->SetFont('Arial', '', 8); // Tamaño más pequeño para los valores
        $this->SetTextColor(0, 0, 0); // Texto negro para todos los valores
    
        $info = [
            ['Nombres:', $data['profesor_nombres'], 'Apellidos:', $data['profesor_apellidos']],
            ['ID Curso:', $data['id_curso'], 'Curso:', $data['materia']],
            ['Nivel:', $data['nivel'], 'Paralelo:', $data['paralelo']],
            ['Subnivel:', $data['subnivel'], 'Especialidad:', $data['especialidad']],
            ['Jornada:', $data['jornada'], 'Año Lectivo:', $data['año_lectivo']]
        ];
    
        // Estilo para los campos con tamaño reducido
        $this->SetFillColor(178, 34, 34); // Rojo para los campos
        $this->SetFont('Arial', 'B', 9); // Tamaño reducido para los campos
    
        // Ancho total de las celdas
        $totalWidth = 240; // 60 + 60 + 60 + 60
    
        // Alternancia de color en las filas para los valores
        $fill = false;
    
        foreach ($info as $row) {
            // Centrar el cuadro
            $this->SetX(($this->w - $totalWidth) / 2);
    
            // Campo con fondo rojo y texto blanco
            $this->SetTextColor(255, 255, 255); // Blanco para texto de campos
            $this->SetFillColor(178, 34, 34); // Rojo para el campo
            $this->Cell(60, 6, utf8_decode($row[0]), 1, 0, 'L', true); // Campo 1
            $this->SetFillColor(245, 245, 245); // Fondo gris claro para los valores
            $this->SetTextColor(0, 0, 0); // Negro para texto de valores
            $this->Cell(60, 6, utf8_decode($row[1]), 1, 0, 'L', $fill); // Valor 1

            // Campo con fondo rojo y texto blanco
            $this->SetTextColor(255, 255, 255); // Blanco para texto de campos
            $this->SetFillColor(178, 34, 34); // Rojo para el campo
            $this->Cell(60, 6, utf8_decode($row[2]), 1, 0, 'L', true); // Campo 2
            $this->SetFillColor(245, 245, 245); // Fondo gris claro para el valor
            $this->SetTextColor(0, 0, 0); // Negro para texto de valores
            $this->Cell(60, 6, utf8_decode($row[3]), 1, 1, 'L', $fill); // Valor 2
    
            // Alternar el color de fondo para las filas de valores
            $fill = !$fill;
        }
    
        $this->Ln(5); // Espaciado reducido
    }

    function AttendanceTable($estudiantes, $primer_ano, $segundo_ano) {
        $meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
        $dias_semana = ['L', 'M', 'Mi', 'J', 'V']; // Solo lunes a viernes
    
        // Crear 15 meses, de enero hasta marzo del segundo año.
        for ($mes_index = 0; $mes_index < 15; $mes_index++) {
            $anio_actual = $mes_index < 12 ? $primer_ano : $segundo_ano;
            $mes_nombre = $meses[$mes_index % 12];
            $dias_del_mes = cal_days_in_month(CAL_GREGORIAN, ($mes_index % 12) + 1, $anio_actual);
            $primer_dia_mes = date('N', strtotime("$anio_actual-" . (($mes_index % 12) + 1) . "-01"));
    
            $this->mes_nombre = $mes_nombre;
            if ($mes_index > 0) {
                $this->AddPage();
            }
    
            // Encabezado con los datos del curso y profesor
            $this->CursoInfo([
                'profesor_nombres' => $GLOBALS['profesor']['profesor_nombres'],
                'profesor_apellidos' => $GLOBALS['profesor']['profesor_apellidos'],
                'id_curso' => $GLOBALS['curso']['id_curso'],
                'materia' => $GLOBALS['nombre_materia'],
                'nivel' => $GLOBALS['curso']['nivel'],
                'subnivel' => $GLOBALS['curso']['subnivel'],
                'especialidad' => $GLOBALS['curso']['especialidad'],
                'paralelo' => $GLOBALS['curso']['paralelo'],
                'jornada' => $GLOBALS['curso']['jornada'],
                'año_lectivo' => $GLOBALS['año_academico']
            ]);
    
            $this->SetFillColor(127, 14, 14); // Rojo elegante
            $this->SetTextColor(255, 255, 255); // Blanco

            // Centrar la primera fila
            $this->SetX(($this->w - 248) / 2);
            $this->Cell(248, 8, strtoupper($mes_nombre), 1, 1, 'C', true); // El último parámetro 'true' activa el color de fondo

            // Centrar la tabla de asistencia
            $this->SetX(($this->w - 248) / 2);
    
            // Segunda fila: Semanas
            $this->SetFont('Arial', 'B', 6);
            $this->SetTextColor(255, 255, 255); // Blanco para texto de campos
            $this->SetFillColor(178, 34, 34); // Rojo para el campo
            $this->SetX(($this->w - 248) / 2);
            $this->Cell(8, 6, '', 1, 0, 'C', true);
            $this->Cell(50, 6, '', 1, 0, 'C', true);
            for ($i = 1; $i <= 5; $i++) {
            $this->Cell(38, 6, "Semana $i", 1, 0, 'C', true);
            }
            $this->Ln();
    
        // Tercera fila: Días de la semana
        $this->SetFillColor(255, 228, 225); 
        $this->SetTextColor(0, 0, 0);
        $this->SetX(($this->w - 248) / 2);
        $this->Cell(8, 6, '', 1, 0, 'C', true);
        $this->Cell(50, 6, '', 1, 0, 'C', true);

        $contador_semana = 0;
        $dia_actual = 1;
        $dia_semana = $primer_dia_mes;
        $dias_validos = [];
        while ($dia_actual <= $dias_del_mes) {
            if ($dia_semana <= 5) {
                $dias_validos[] = $dia_actual;
                $this->Cell(7.6, 6, $dias_semana[$dia_semana - 1], 1, 0, 'C', true);
                $contador_semana++;
                if ($contador_semana == 5) {
                    $contador_semana = 0;
                }
            }
            $dia_actual++;
            $dia_semana = ($dia_semana % 7) + 1;
        }

        // Completar espacios vacíos para la semana 5
        while ($contador_semana < 5 && $contador_semana > 0) {
            $this->Cell(7.6, 6, '', 1, 0, 'C', true);
            $contador_semana++;
        }
        $this->Ln();

        // Cuarta fila: Números de los días (sin sábados ni domingos)
        $this->SetX(($this->w - 248) / 2);
        $this->Cell(8, 6, 'N', 1, 0, 'C', true);
        $this->Cell(50, 6, 'Apellidos y Nombres', 1, 0, 'C', true);


        $dias_validos = [];
        for ($dia = 1; $dia <= $dias_del_mes; $dia++) {
            $dia_semana = date('N', strtotime("$anio_actual-" . (($mes_index % 12) + 1) . "-$dia"));
            if ($dia_semana <= 5) {
                $dias_validos[] = $dia;
            }
        }

        foreach ($dias_validos as $dia) {
            $this->Cell(7.6, 6, is_numeric($dia) ? $dia : '', 1, 0, 'C');
        }
        // Completar espacios vacíos hasta donde termina semana 5
        while (count($dias_validos) % 5 != 0) {
            $this->Cell(7.6, 6, '', 1, 0, 'C');
            $dias_validos[] = '';
        }
        
        $this->Ln();
    
        // Quinta fila: Estudiantes con colores alternos
        $fill = false;
        $semanas_totales = ceil(count($dias_validos) / 5);
        foreach ($estudiantes as $index => $estudiante) {
            $this->SetFillColor($fill ? 245 : 247, 247, 247);
            $fill = !$fill;
            $this->SetX(($this->w - 248) / 2);
            $this->Cell(8, 6, $index + 1, 1, 0, 'C', true);
            $this->Cell(50, 6, utf8_decode($estudiante['nombres'] . ' ' . $estudiante['apellidos']), 1, 0, 'L', true);
            foreach ($dias_validos as $dia) {
                $this->Cell(7.6, 6, '', 1, 0, 'C', true);
            }
    // Completar celdas vacías solo hasta la última semana existente (4 o 5 semanas según el mes)
    for ($i = count($dias_validos); $i < ($semanas_totales * 5); $i++) {
                $this->Cell(7.6, 6, '', 1, 0, 'C', true);
            }
            $this->Ln();
        }
        $this->Ln(5);
	
        $this->SetFont('Arial', 'B', 7);  // Fuente más pequeña para el título
        $this->SetTextColor(255, 255, 255); // Blanco para texto de campos
        $this->SetFillColor(178, 34, 34); // Rojo para el campo

        // Posicionar toda la tabla hacia la izquierda
        $this->SetX(10); // Margen izquierdo ajustado

        // Título "RESUMEN" con altura reducida y proporción más compacta
        $this->Cell(50, 6, "RESUMEN", 1, 1, 'C', true); // Ancho y altura reducidos

        // Reducir tamaño de fuente para las filas
        $this->SetFont('Arial', '', 6);  // Fuente aún más pequeña para contenido
        $this->SetFillColor(255, 228, 225); // Fondo claro para las celdas
        $this->SetTextColor(0, 0, 0); // Negro para texto


        // Lista de celdas en el resumen
        $resumen = [
            ["Total Estudiantes:", count($estudiantes)],
            ["Total Asistencias:", ""],
            ["Total Faltas:", ""],
            ["Faltas Justificadas:", ""],
            ["Retrasos:", ""]
        ];

        // Mostrar los campos con dos columnas (título y valor) y filas alternas con colores
        $fill = false; // Alternar color de fondo
        foreach ($resumen as $item) {
            $this->SetX(10); // Posicionar cada fila a la izquierda
            $this->Cell(25, 6, $item[0], 1, 0, 'L', $fill);  // Título del campo (ancho reducido)
            $this->Cell(25, 6, $item[1], 1, 1, 'L', $fill);  // Valor del campo (ancho reducido)
            $fill = !$fill; // Alternar color de fondo
        }
    }
}
            
    function Footer() {
        // Posición a 1.5 cm del final
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(128, 128, 128);

        // Nota adicional
        $this->SetFont('Arial', '', 8); // Reducir fuente para la nota
        $this->SetTextColor(67, 67, 67); // Gris intermedio
        $this->SetX(180); // Ajusta la posición X para alinear a la derecha (ajustar valor según sea necesario)
        $this->MultiCell(0, 5, utf8_decode('Firma del Docente: ______________________'), 0, 'R'); // 'R' es para alineación a la derecha

        // Línea decorativa gris clara exactamente después del texto generado
        $this->SetDrawColor(220, 220, 220);
        $this->Line(43, $this->GetY() + 2, 252, $this->GetY() + 2); // Línea inmediatamente después del texto
        $this->Ln(3); // Espaciado entre la línea y la nota adicional
    
        // Mensaje de confidencialidad
        $this->SetFont('Arial', '', 8); // Reducir fuente para la nota
        $this->SetTextColor(67, 67, 67); // Gris intermedio
        $this->MultiCell(0, 5, utf8_decode('Este documento contiene información confidencial y está destinado exclusivamente para uso interno. La divulgación, distribución o reproducción de este documento sin autorización está estrictamente prohibido.'), 0, 1, 'C');
    
    }
}

// Crear una instancia de la clase PDF
$pdf = new PDF('L', 'mm', 'A4'); // 'L' para landscape, 'mm' para milímetros, 'A4' para tamaño de hoja

// Crear una instancia de la clase PDF y pasar las fechas al constructor
$pdf = new PDF($fecha_inicio, $fecha_fin, $fechas);

// Información del curso
$curso_info = [
    'profesor_nombres' => $profesor['profesor_nombres'],
    'profesor_apellidos' => $profesor['profesor_apellidos'],
    'id_curso' => $curso['id_curso'],
    'materia' => $nombre_materia,
    'nivel' => $curso['nivel'],
    'subnivel' => $curso['subnivel'],
    'especialidad' => $curso['especialidad'],
    'paralelo' => $curso['paralelo'],
    'jornada' => $curso['jornada'],
    'año_lectivo' => $año_academico
];

// Agregar una página en orientación horizontal (landscape)
$pdf->AddPage('L');  // 'L' es para horizontal

// Agregar la tabla de asistencia
$pdf->AttendanceTable($estudiantes, $primer_ano, $segundo_ano);


// Salida del PDF
$pdf->Output('D', 'Asistencia_Estudiantes_' . $nombre_materia . '.pdf');
$conn->close();
?>

