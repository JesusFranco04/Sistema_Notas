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

// Obtener filtros
$fecha = isset($_GET['fecha']) ? $_GET['fecha'] : null;
$estado = isset($_GET['estado']) ? $_GET['estado'] : null;

// Depuración: Verificar qué parámetros se están pasando
// echo "Fecha: " . $fecha . " Estado: " . $estado . "<br>";

// Consulta para obtener el resumen de usuarios
$queryResumenUsuarios = "
    SELECT 
        COUNT(*) AS total_usuarios, 
        SUM(CASE WHEN estado = 'A' THEN 1 ELSE 0 END) AS usuarios_activos,
        SUM(CASE WHEN estado = 'I' THEN 1 ELSE 0 END) AS usuarios_inactivos
    FROM usuario
";

// Ejecutar la consulta para obtener el resumen de usuarios
$resultResumenUsuarios = $conn->query($queryResumenUsuarios);

// Obtener los resultados del resumen
$rowResumenUsuarios = $resultResumenUsuarios->fetch_assoc();

// Consulta SQL
$query = "
    SELECT 
        u.id_usuario,
        u.cedula,
        COALESCE(p.nombres, a.nombres, pr.nombres) AS nombres,
        COALESCE(p.apellidos, a.apellidos, pr.apellidos) AS apellidos,
        COALESCE(p.correo_electronico, a.correo_electronico, pr.correo_electronico) AS correo_electronico,
        u.contraseña,
        r.nombre AS rol,
        u.fecha_ingreso
    FROM usuario u
    LEFT JOIN padre p ON u.id_usuario = p.id_usuario
    LEFT JOIN administrador a ON u.id_usuario = a.id_usuario
    LEFT JOIN profesor pr ON u.id_usuario = pr.id_usuario
    INNER JOIN rol r ON u.id_rol = r.id_rol
    WHERE 1=1
";

// Parámetros para la consulta
$params = [];
$types = '';  // Para almacenar los tipos de parámetros

// Filtro de fecha
if ($fecha) {
    $query .= " AND DATE(u.fecha_ingreso) = ?";
    $params[] = $fecha;
    $types .= 's';  // 's' para string (fecha)
}

// Filtro de estado
if ($estado) {
    // Mapear 'activo' a 'A' y 'inactivo' a 'I'
    if ($estado == 'activo') {
        $estado = 'A';  // 'A' para activos
    } elseif ($estado == 'inactivo') {
        $estado = 'I';  // 'I' para inactivos
    }
    $query .= " AND u.estado = ?";
    $params[] = $estado;
    $types .= 's';  // 's' para string (estado)
} else {
    // Si no se selecciona un estado, por defecto solo mostrar activos
    $query .= " AND u.estado = 'A'";
}

// Depuración: Verificar la consulta generada
// echo "Consulta generada: " . $query . "<br>";

// Preparar la consulta con MySQLi
$stmt = $conn->prepare($query);

// Si hay filtros, los vinculamos
if (count($params) > 0) {
    $stmt->bind_param($types, ...$params);  // Usar spread operator para pasar todos los parámetros
}

// Ejecutar la consulta
$stmt->execute();

// Obtener resultados
$result = $stmt->get_result();

// Definición de la clase PDF para el reporte
class PDF extends FPDF {
    function Header() {
        // Fondo blanco
        $this->SetFillColor(255, 255, 255);
        $this->Rect(0, 0, 210, 297, 'F');
        
        // Logo de la institución
        $this->Image('../../imagenes/logo.png', 10, 10, 20);
        
        // Título
        $this->SetFont('Arial', 'B', 14);
        $this->SetTextColor(178, 34, 34); // Rojo
        $this->Cell(0, 10, utf8_decode('UNIDAD EDUCATIVA "BENJAMÍN FRANKLIN"'), 0, 1, 'C');
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 10, utf8_decode('REPORTE DE USUARIOS'), 0, 1, 'C');
        $this->Ln(10);
    }

    function Footer() {
        // Pie de página
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 9);
        $this->SetTextColor(0, 0, 0); // Negro
        $this->Cell(0, 10, 'Fecha: ' . date('d/m/Y') . ' - Pagina ' . $this->PageNo(), 0, 0, 'C');
    }

    function TableHeader() {
        // Encabezado de la tabla con colores y fuentes
        $this->SetFont('Arial', 'B', 6);  // Fuente más pequeña
        $this->SetFillColor(178, 34, 34); // Rojo
        $this->SetTextColor(255, 255, 255); // Blanco
        $this->Cell(18, 6, 'ID Usuario', 1, 0, 'C', true);
        $this->Cell(25, 6, 'Nombres', 1, 0, 'C', true);
        $this->Cell(25, 6, 'Apellidos', 1, 0, 'C', true);
        $this->Cell(20, 6, 'Cedula', 1, 0, 'C', true);
        $this->Cell(25, 6, 'Contrasena', 1, 0, 'C', true); // Nueva columna de Contraseña
        $this->Cell(35, 6, 'Correo Electronico', 1, 0, 'C', true);
        $this->Cell(18, 6, 'Rol', 1, 0, 'C', true);
        $this->Cell(22, 6, 'Fecha de Ingreso', 1, 1, 'C', true);
    }
}

// Crear objeto PDF
$pdf = new PDF();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 6);  // Fuente más pequeña para los datos

// Mostrar el resumen de usuarios
$pdf->SetFont('Arial', 'B',12);
$pdf->SetFillColor(178, 34, 34); // Rojo
$pdf->SetTextColor(255, 255, 255); // Blanco
$pdf->Cell(189, 10, 'Resumen de Usuarios', 0, 1, 'C', true);

// Establecer el estilo para los cuadros de resumen
$pdf->SetFont('Arial', '', 10);
$pdf->SetFillColor(245, 245, 245); // Color de fondo suave (gris claro)
$pdf->SetTextColor(0, 0, 0); // Color de texto negro

// Cuadro para "Usuarios Activos"
$pdf->Cell(63, 10, utf8_decode('Usuarios Activos: ' . $rowResumenUsuarios['usuarios_activos']), 1, 0, 'C', true);

// Cuadro para "Usuarios Inactivos"
$pdf->Cell(63, 10, utf8_decode('Usuarios Inactivos: ' . $rowResumenUsuarios['usuarios_inactivos']), 1, 0, 'C', true);

// Cuadro para "Total de Usuarios"
$pdf->Cell(63, 10, utf8_decode('Total de Usuarios: ' . $rowResumenUsuarios['total_usuarios']), 1, 1, 'C', true);

// Insertar un espacio antes de la tabla
$pdf->Ln(10);

// Encabezados de la tabla
$pdf->TableHeader();

// Mostrar los datos de los usuarios
while ($usuario = $result->fetch_assoc()) {
    $pdf->SetFont('Arial', '', 6); // Fuente más pequeña
    $pdf->SetTextColor(0, 0, 0); // Color negro para el contenido de la tabla
    $pdf->Cell(18, 6, $usuario['id_usuario'], 1, 0, 'C');
    $pdf->Cell(25, 6, utf8_decode($usuario['nombres']), 1, 0, 'L');
    $pdf->Cell(25, 6, utf8_decode($usuario['apellidos']), 1, 0, 'L');
    $pdf->Cell(20, 6, $usuario['cedula'], 1, 0, 'C');
    $pdf->Cell(25, 6, utf8_decode($usuario['contraseña']), 1, 0, 'C'); // Contraseña en texto plano
    $pdf->Cell(35, 6, utf8_decode($usuario['correo_electronico']), 1, 0, 'L');
    $pdf->Cell(18, 6, utf8_decode($usuario['rol']), 1, 0, 'C');
    $pdf->Cell(22, 6, $usuario['fecha_ingreso'], 1, 1, 'C');
}

// Salida del PDF
$pdf->Output('D', 'Reporte_Usuarios.pdf');

// Cerrar la conexión
$stmt->close();
$conn->close();
?>
