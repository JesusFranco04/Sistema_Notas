<?php 
session_start();

// Incluir el archivo de conexión
include('../../Crud/config.php'); // Ruta absoluta

// Configurar la zona horaria de Ecuador
date_default_timezone_set('America/Guayaquil');

// Verificar si el usuario ha iniciado sesión y si su rol es "Profesor"
if (!isset($_SESSION['cedula']) || !in_array($_SESSION['rol'], ['Profesor'])) {
    // Redirigir a la página de login si no está autenticado o no tiene el rol adecuado
    header("Location: ../../login.php");
    exit(); // Asegurarse de que no se ejecute más código después de la redirección
}

// Asegúrate de que id_profesor esté definido en la sesión
if (!isset($_SESSION['id_profesor'])) {
    echo "ID de profesor no definido en la sesión.";
    exit();
}

$id_profesor = $_SESSION['id_profesor'];

// Obtener el año académico seleccionado de la URL, o usar el actual si no está especificado
$año_academico = isset($_GET['año_academico']) ? intval($_GET['año_academico']) : date('Y');

// Consulta para obtener los años académicos disponibles
$sql_años = "SELECT DISTINCT h.id_his_academico, h.año AS año_academico, h.estado, h.fecha_ingreso
             FROM historial_academico h
             JOIN curso c ON h.id_his_academico = c.id_his_academico
             WHERE c.id_profesor = ?
             ORDER BY h.año DESC";
$stmt_años = $conn->prepare($sql_años);
$stmt_años->bind_param('i', $id_profesor);
$stmt_años->execute();
$result_años = $stmt_años->get_result();
$años_academicos = $result_años->fetch_all(MYSQLI_ASSOC);

// Ordenar los años académicos según las prioridades especificadas
usort($años_academicos, function ($a, $b) {
    // Prioridad: el año actual primero
    $a_actual = date('Y');
    if ($a['año_academico'] == $a_actual) return -1;
    if ($b['año_academico'] == $a_actual) return 1;

    // Prioridad por estado: "A" primero, luego "I"
    if ($a['estado'] === 'A' && $b['estado'] === 'I') {
        return -1;
    } elseif ($a['estado'] === 'I' && $b['estado'] === 'A') {
        return 1;
    }

    // Si ambos tienen el mismo estado, ordenar por fecha_ingreso de forma descendente
    return strtotime($b['fecha_ingreso']) <=> strtotime($a['fecha_ingreso']);
});

// Consulta para obtener los cursos del profesor en el año académico seleccionado
$sql_cursos = "SELECT 
                    c.id_curso, 
                    m.nombre AS nombre_materia, 
                    n.nombre AS nombre_nivel, 
                    p.nombre AS nombre_paralelo, 
                    s.nombre AS nombre_subnivel, 
                    e.nombre AS nombre_especialidad, 
                    j.nombre AS nombre_jornada, 
                    h.año AS año_academico
                FROM curso c
                JOIN materia m ON c.id_materia = m.id_materia
                JOIN nivel n ON c.id_nivel = n.id_nivel
                JOIN paralelo p ON c.id_paralelo = p.id_paralelo
                JOIN subnivel s ON c.id_subnivel = s.id_subnivel
                JOIN especialidad e ON c.id_especialidad = e.id_especialidad
                JOIN jornada j ON c.id_jornada = j.id_jornada
                JOIN historial_academico h ON c.id_his_academico = h.id_his_academico
                WHERE c.id_profesor = ? AND h.año = ?
                ORDER BY c.id_curso ASC"; // Ordenar por id_curso
$stmt_cursos = $conn->prepare($sql_cursos);
$stmt_cursos->bind_param('ii', $id_profesor, $año_academico);
$stmt_cursos->execute();
$result_cursos = $stmt_cursos->get_result();
$cursos = $result_cursos->fetch_all(MYSQLI_ASSOC);

$stmt_años->close();
$stmt_cursos->close();
$conn->close();
?>



<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Cursos Asignados | Sistema de Gestión UEBF</title>
    <link rel="shortcut icon" href="http://localhost/sistema_notas/imagenes/logo.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/boxicons/css/boxicons.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
    /* Reset básico */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    /* Body y fondo general */
    body {
        font-family: 'Roboto', sans-serif;
        background-color: #f4f6f9;
        color: #333;
        line-height: 1.6;
    }

    .main-container {
        width: 97%;
        /* Aumenta ligeramente el ancho */
        max-width: 1400px;
        /* Incrementa el límite máximo */
        margin: 0 auto;
        padding: 3rem;
        background-color: #fff;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        display: flex;
        flex-direction: column;
        align-items: center;
        /* Centra el contenido horizontalmente */
        justify-content: center;
        /* Asegura buen espaciado */
    }

    /* Fondo para la página para dar contraste */
    body {
        background: linear-gradient(145deg, #ffffff, #f0f0f0);
        /* Fondo sutil con gradiente */
    }

    .banner {
        background-color: #c61e1e;
        color: white;
        padding: 2rem;
        text-align: center;
        font-size: 1.8rem;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 2px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        border-bottom: 3px solid #0052aa;
        margin-bottom: 3rem;
        /* Agrega el mismo espacio que tiene el footer */
    }


    /* Título principal */
    .text-center {
        text-align: center;
        margin-bottom: 2rem;
    }

    .text-center h2 {
        color: #ad0f0f;
        /* Rojo vibrante */
        font-size: 2rem;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 1px;
        position: relative;
        display: inline-block;
        padding-bottom: 10px;
    }

    .text-center h2::after {
        content: "";
        position: absolute;
        bottom: 0;
        left: 0;
        width: 50%;
        height: 3px;
        background: linear-gradient(90deg, #1976d2, #388e3c);
        /* Línea decorativa más elegante */
        transform: translateX(50%);
        /* Centrado perfecto */
    }

    /* Dropdown de Años Lectivos */
    .dropdown .btn-primary {
        background: #1976d2;
        /* Azul para el botón de Año Actual */
        border: none;
        color: #fff;
        padding: 0.9rem 1.5rem;
        border-radius: 30px;
        font-size: 1rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
        transition: background 0.3s ease, transform 0.3s ease;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    .dropdown .btn-primary:hover {
        background: #1565c0;
        transform: translateY(-5px);
    }

    .dropdown-menu {
        border-radius: 8px;
        overflow: hidden;
        border: 1px solid #ddd;
        /* Agregar scroll vertical */
        max-height: 300px;
        /* Ajusta la altura según necesidad */
        overflow-y: auto;
        /* Activa el scroll vertical */
        position: absolute;
        /* Evita que altere la estructura de la página */
        z-index: 1000;
        /* Asegura que el dropdown esté sobre otros elementos */
    }


    .dropdown-menu .dropdown-item {
        padding: 1rem;
        font-size: 1rem;
        color: #333;
        border-bottom: 1px solid #eee;
        transition: background-color 0.3s ease, padding-left 0.3s ease;
        display: flex;
        align-items: center;
    }

    .dropdown-menu .dropdown-item i {
        margin-right: 0.8rem;
        /* Espacio entre el icono y el texto */
    }

    .dropdown-menu .dropdown-item:hover {
        background-color: #f5f5f5;
        padding-left: 1.5rem;
    }

    /* Año seleccionado en el dropdown */
    .dropdown-menu .active,
    .dropdown-menu .current-year {
        background-color: #1976d2 !important;
        /* Azul */
        color: #fff !important;
        /* Blanco */
    }

    .dropdown-menu .active:hover,
    .dropdown-menu .current-year:hover {
        background-color: #388e3c !important;
    }

    /* Estilo para el contenedor de los botones */
    .button-container {
        display: flex;
        justify-content: space-between;
        /* Distribuye los botones a los extremos */
        align-items: center;
        /* Alinea los elementos verticalmente */
        gap: 20px;
        /* Espacio entre los botones */
        margin-top: 20px;
        /* Espaciado superior para separar de los demás elementos */
    }

    /* Estilo para el botón independiente (abrir modal) */
    .btn-open-modal {
        background-color: #fecdd4;
        color: #050274;
        padding: 1rem 2rem;
        border: none;
        border-radius: 30px;
        font-size: 1rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-top: 2rem;
        display: inline-block;
        text-decoration: none;
        transition: background-color 0.3s ease, transform 0.3s ease;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    /* Efecto hover para el botón de abrir modal */
    .btn-open-modal:hover {
        background-color: #050274;
        color: #fedfe4;
        transform: translateY(-5px);
    }

    /* Botón regresar */
    .btn-regresar {
        background-color: #388e3c;
        /* Verde */
        color: #fff;
        padding: 1rem 2rem;
        border: none;
        border-radius: 30px;
        font-size: 1rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-top: 2rem;
        display: inline-block;
        text-decoration: none;
        transition: background-color 0.3s ease, transform 0.3s ease;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        /* Añadimos margen automático para centrar el botón */
        margin-left: auto;
        margin-right: auto;
    }

    /* Efecto hover para el botón regresar */
    .btn-regresar:hover {
        background-color: #2e7d32;
        transform: translateY(-5px);
        color: #fff;
    }

    /* Contenedor específico para el botón "Regresar" centrado y "Abrir Modal" a la derecha */
    .button-container .btn-regresar {
        margin-right: auto;
        /* Asegura que el botón "Regresar" se quede al centro */
    }

    .button-container .btn-open-modal {
        margin-left: auto;
        /* Alinea el botón "Abrir Modal" al extremo derecho */
    }

    /* Tarjetas */
    .card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        background-color: #fff;
    }

    .card:hover {
        transform: translateY(-10px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
    }

    .card-header {
        background-color: #e53935;
        /* Rojo más profundo */
        color: #fff;
        padding: 1rem;
        font-size: 1rem;
        /* Tamaño ligeramente reducido */
        font-weight: bold;
        position: relative;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .card-header .course-id {
        position: absolute;
        top: 50%;
        right: 1rem;
        transform: translateY(-50%);
        background-color: #fff;
        color: #e53935;
        padding: 0.5rem 1rem;
        border-radius: 50px;
        font-size: 0.9rem;
        font-weight: 600;
        box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);
    }

    .card-body {
        padding: 1.5rem;
        font-size: 1rem;
        color: #666;
    }

    .card-body p {
        margin-bottom: 1rem;
    }

    .card-footer {
        text-align: center;
        padding: 1.5rem;
        background-color: #f5f5f5;
        border-top: 1px solid #ddd;
    }

    .card-footer .btn-view-students {
        background: linear-gradient(135deg, #083787, #1877d1);
        color: #fff;
        padding: 1rem 2rem;
        border: none;
        border-radius: 30px;
        font-size: 1.1rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        transition: background 0.3s ease, transform 0.3s ease;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    .card-footer .btn-view-students:hover {
        background: linear-gradient(135deg, #083787, #1877d1);
        transform: translateY(-5px);
    }

    .card-footer .btn-view-students:active {
        color: #fff;
        /* Mantiene el color blanco en las letras */
        background: linear-gradient(135deg, #083787, #1877d1);
        /* O puedes definir el color que desees para el fondo */
        transform: translateY(0);
        /* Elimina el efecto de movimiento al presionar */
    }

    /* Contenedor de cada tarjeta para que estén centradas */
    .card-container {
        width: 85%;
        max-width: 900px;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    /* Tarjetas alineadas correctamente */
    .card {
        width: 100%;
        margin-bottom: 1.5rem;
    }

    .alert {
        padding: 15px;
        border: 1px solid transparent;
        border-radius: 4px;
        margin-bottom: 20px;
        font-size: 14px;
    }

    .alert-error {
        color: #d32f2f;
        /* Rojo más moderno */
        background-color: #fdecea;
        /* Fondo rojo claro */
        border-color: #d32f2f;
    }

    .alert-success {
        color: #388e3c;
        /* Verde más moderno */
        background-color: #e8f5e9;
        /* Fondo verde claro */
        border-color: #388e3c;
    }

    .alert-warning {
        color: #f57c00;
        /* Naranja más vibrante */
        background-color: #fff4e5;
        /* Fondo naranja claro */
        border-color: #f57c00;
    }

    .alert {
        display: none;
        /* Ocultar alerta por defecto */
    }

    .alert.show {
        display: block;
        /* Mostrar alerta cuando sea necesario */
    }

    /* Estilo general del modal */
    .modal-content {
        border-radius: 12px;
        box-shadow: 0px 10px 25px rgba(0, 0, 0, 0.2);
        border: none;
        overflow: hidden;
    }

    /* Encabezado con un degradado elegante */
    .modal-header {
        background: linear-gradient(135deg, #007bff, #0056b3);
        color: white;
        border-top-left-radius: 12px;
        border-top-right-radius: 12px;
        padding: 15px;
        text-align: center;
        font-weight: bold;
        letter-spacing: 1px;
    }

    /* Cuerpo con fondo suave */
    .modal-body {
        background-color: #f9f9f9;
        padding: 20px;
        color: #333;
        font-size: 16px;
        line-height: 1.5;
    }

    /* ---------- PIE DE MODAL ---------- */
    .modal-footer {
        background-color: #f8f9fa;
        padding: 12px;
        border-bottom-left-radius: 10px;
        border-bottom-right-radius: 10px;
        display: flex;
        justify-content: space-between;
    }

    /* Botón de cerrar (X) */
    .close {
        font-size: 22px;
        color: white;
        opacity: 0.8;
        transition: all 0.3s ease-in-out;
        background: none;
        border: none;
    }

    .close:hover {
        opacity: 1;
        transform: scale(1.1);
    }

    /* ---------- BOTONES ---------- */
    .btn-modal {
        padding: 10px 20px;
        /* Aumenta el espacio interior (más grande) */
        font-size: 16px;
        /* Aumenta el tamaño del texto */
        font-weight: 600;
        /* Mantén el grosor de la fuente */
        border-radius: 30px;
        /* Bordes más redondeados */
        cursor: pointer;
        transition: all 0.3s ease-in-out;
        border: none;
        min-width: 120px;
        /* Asegura que los botones tengan un tamaño mínimo mayor */
        text-align: center;
        /* Asegura que el texto esté centrado */
    }


    /* Botón "Atrás" (Verde) */
    #btnPrev {
        background: linear-gradient(135deg, #34d058, #28a745);
        /* Verde más brillante */
        color: white;
        border: none;
        /* Eliminar el borde gris */
    }

    #btnPrev:hover {
        background: linear-gradient(135deg, #228b3f, #1e7e34);
        /* Cambio de degradado más pronunciado */
    }

    /* Botón "Siguiente" (Azul Principal) */
    #btnNext {
        background: linear-gradient(135deg, #007bff, #0056b3);
        color: white;
        border: none;
        /* Eliminar el borde gris */
    }

    #btnNext:hover {
        background: linear-gradient(135deg, #0056b3, #003366);
        /* Cambio de degradado en hover */
    }

    /* Botón "Cerrar" (Rojo) */
    #btnClose {
        background: linear-gradient(135deg, #ff4b5c, #c82333);
        /* Rojo más intenso */
        color: white;
        border: none;
        /* Eliminar el borde gris */
    }

    #btnClose:hover {
        background: linear-gradient(135deg, #e02e3e, #ad0f0f);
        /* Rojo más fuerte en hover */
    }


    .modal-body {
        max-height: 400px;
        /* Ajusta la altura según lo que necesites */
        overflow-y: auto;
        /* Permite el scroll vertical */
    }

    /* Footer */
    footer {
        border-top: 3px solid #073b73;
        /* Borde en la parte superior */
        background-color: #ad0f0f;
        color: white;
        text-align: center;
        /* Centrar el texto */
        padding: 20px 0;
        margin-top: 3rem;
        font-size: 1rem;
        /* Ancho completo */
        letter-spacing: 1px;
        box-shadow: 0 -4px 12px rgba(0, 0, 0, 0.1);
        /* Sombra más pronunciada */
    }

    footer p {
        margin: 0;
        /* Eliminar el margen de los párrafos */
    }
    </style>
</head>

<body>
    <!-- Banner -->
    <div class="banner">Sistema de Gestión UEBF</div>
    <div class="container">
        <div class="main-container">
            <!-- Texto centrado -->
            <div class="text-center my-3">
                <h2>Gestión de Cursos</h2>
            </div>

            <div class="dropdown">
                <a class="btn btn-primary dropdown-toggle" href="#" role="button" id="navbarDropdown"
                    data-bs-toggle="dropdown" aria-expanded="false">
                    Años Lectivos:
                </a>
                <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                    <!-- Año Actual -->
                    <li>
                        <a class="dropdown-item current-year <?php echo !isset($_GET['año_academico']) || $_GET['año_academico'] == date('Y') ? 'active' : ''; ?>"
                            href="?año_academico=<?php echo date('Y'); ?>">
                            <i class="fas fa-calendar-day"></i>Año Actual (<?php echo date('Y'); ?>)
                        </a>
                    </li>

                    <!-- Años Académicos Disponibles -->
                    <?php foreach ($años_academicos as $año): ?>
                    <li>
                        <a class="dropdown-item <?php echo isset($_GET['año_academico']) && $_GET['año_academico'] == $año['año_academico'] ? 'active' : ''; ?>"
                            href="?año_academico=<?php echo urlencode($año['año_academico']); ?>">
                            <i class="fas fa-calendar-alt"></i><?php echo htmlspecialchars($año['año_academico']); ?>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <!-- Botones "Regresar" y "Abrir Modal" alineados a la derecha -->
            <div class="button-container">
                <a href="http://localhost/sistema_notas/views/profe/index_profe.php" class="btn btn-regresar"> <i
                        class='bx bx-arrow-back'></i> Regresar</a>
                <button type="button" class="btn btn-open-modal" data-bs-toggle="modal"
                    data-bs-target="#modalInstrucciones"> <i class='bx bx-book'></i> Manual de Uso</button>
            </div>
            <div class="container mt-5">
                <div class="row g-4">
                    <?php if ($cursos): ?>
                    <?php foreach ($cursos as $curso): ?>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header position-relative">
                                <?php echo htmlspecialchars($curso['nombre_materia']); ?>
                                <div class="course-id"><?php echo htmlspecialchars($curso['id_curso']); ?></div>
                            </div>
                            <div class="card-body">
                                <p><strong>Nivel:</strong> <?php echo htmlspecialchars($curso['nombre_nivel']); ?></p>
                                <p><strong>Paralelo:</strong> <?php echo htmlspecialchars($curso['nombre_paralelo']); ?>
                                </p>
                                <p><strong>Subnivel:</strong> <?php echo htmlspecialchars($curso['nombre_subnivel']); ?>
                                </p>
                                <p><strong>Especialidad:</strong>
                                    <?php echo htmlspecialchars($curso['nombre_especialidad']); ?>
                                </p>
                                <p><strong>Jornada:</strong> <?php echo htmlspecialchars($curso['nombre_jornada']); ?>
                                </p>
                                <p><strong>Año Académico:</strong>
                                    <?php echo htmlspecialchars($curso['año_academico']); ?></p>
                            </div>
                            <div class="card-footer">
                                <a href="ver_estudiantes.php?id_curso=<?php echo urlencode($curso['id_curso']); ?>"
                                    class="btn btn-view-students">Ver Estudiantes</a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <div class="alert alert-error show">
                        No hay cursos disponibles para el año académico seleccionado.
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <!-- Modal único - Manual de Uso detallado -->
        <div class="modal fade" id="modalInstrucciones" tabindex="-1" role="dialog"
            aria-labelledby="modalInstruccionesLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <!-- Encabezado con franja azul -->
                    <div class="modal-header" style="background-color: #007bff; color: white;">
                        <h5 class="modal-title" id="modalInstruccionesLabel">Manual de Uso - Gestión de Cursos (1/4)
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"
                            onclick="cerrarModal()" style="color: white; border: none; background: none;">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <!-- Cuerpo del modal -->
                    <div class="modal-body" id="modalContent">
                        <!-- Paso 1 -->
                        <div id="step1">
                            <h6><strong>Paso 1: Cómo seleccionar un Año Académico</strong></h6>
                            <p><strong>Ubicación:</strong> En la parte superior de la pantalla, encontrarás un botón
                                que dice "Años Lectivos". Este botón está justo encima de la lista de cursos. Es un
                                botón grande, de color azul, con una flecha hacia abajo.</p>
                            <p><strong>Acción:</strong> Haz clic en ese botón. Esto abrirá una lista de todos los
                                años académicos disponibles, tanto el año actual como los años anteriores.</p>
                            <p><strong>Qué hacer:</strong> En la lista, encontrarás el "Año Actual", que tiene un
                                ícono de calendario al lado. Este es el año en el que estamos ahora. Si deseas ver
                                cursos de años pasados, solo haz clic en el año que prefieras.</p>
                            <p><strong>¿Qué sucede cuando seleccionas un año?</strong> Después de hacer clic en un
                                año, la página se actualizará automáticamente, y verás todos los cursos que están
                                disponibles para ese año específico.</p>
                        </div>
                        <!-- Paso 2 - Visualización de Cursos Disponibles -->
                        <div id="step2" style="display:none;">
                            <h6><strong>Paso 2: Visualización de Cursos Disponibles</strong></h6>
                            <p><strong>Ubicación:</strong> Después de seleccionar un año académico, verás una lista
                                de cursos en forma de tarjetas. Estas tarjetas están ubicadas en el centro de la
                                pantalla, una al lado de la otra.</p>
                            <p><strong>Acción:</strong> Cada tarjeta muestra información sobre un curso, y dentro de
                                cada tarjeta encontrarás los siguientes detalles:</p>
                            <ul>
                                <li><strong>Materia:</strong> El nombre de la asignatura, por ejemplo,
                                    "Matemáticas", "Historia".</li>
                                <li><strong>Nivel:</strong> El nivel de los estudiantes que toman el curso, como
                                    "Octavo", "Noveno", etc.</li>
                                <li><strong>Paralelo:</strong> El grupo de estudiantes que están en ese curso, como
                                    "A", "B", etc.</li>
                                <li><strong>Subnivel:</strong> Si aplica, indica un nivel adicional dentro del
                                    curso, como "Educación Básica General" o "Bachillerato Técnico Industrial".</li>
                                <li><strong>Especialidad:</strong> Si el curso pertenece a una especialidad, como
                                    "Educación Básica General", "Electrónica de Consumo", "Mecánica Automotriz", etc.
                                </li>
                                <li><strong>Jornada:</strong> El horario en que se imparte el curso, como "Matutina" o
                                    "Vespertina", etc.</li>
                                <li><strong>Año Académico:</strong> El año en el que se imparte el curso, como
                                    "2025 - 2026".</li>
                            </ul>
                            <p><strong>Qué hacer:</strong> Si quieres más información sobre un curso, haz clic en el
                                botón que dice "Ver Estudiantes" dentro de la tarjeta del curso. Esto te llevará a
                                una página donde podrás ver los estudiantes que están inscritos en ese curso.</p>
                        </div>
                        <!-- Paso 3 - Ver Estudiantes -->
                        <div id="step3" style="display:none;">
                            <h6><strong>Paso 3: Cómo ver la lista de estudiantes de un curso</strong></h6>

                            <p><strong>Ubicación:</strong> Para ver los estudiantes de un curso, primero debes buscar la
                                tarjeta que representa el curso en el que estás interesado.
                                Cada curso tiene su propia tarjeta con información como el nombre del curso y el año
                                académico.</p>

                            <p><strong>Dónde encontrar el botón:</strong> Dentro de la tarjeta del curso, en la parte
                                inferior, encontrarás un botón que dice <strong>"Ver Estudiantes"</strong>.
                                Este botón está justo debajo de los detalles del curso.</p>

                            <p><strong>Acción a realizar:</strong> Para ver la lista de estudiantes, simplemente haz
                                clic en el botón <strong>"Ver Estudiantes"</strong>.
                                Esto te llevará automáticamente a una nueva página donde podrás ver toda la información
                                de los estudiantes inscritos en ese curso.</p>

                            <p><strong>¿Qué puedes hacer en esta página?</strong></p>

                            <ul>
                                <li><strong>Ver la lista de estudiantes:</strong> La página te mostrará los nombres de
                                    los estudiantes inscritos en el curso.</li>
                                <li><strong>Buscar un estudiante:</strong> En la parte superior, encontrarás una barra
                                    de búsqueda donde puedes escribir el nombre, apellido o cédula de un estudiante para
                                    encontrarlo rápidamente.</li>
                                <li><strong>Consultar asistencia:</strong> Puedes hacer clic en el botón <strong>"Lista
                                        de Asistencia"</strong> descargar el PDF de la lista de los estudiantes de esa
                                    clase.</li>
                                <li><strong>Calificar a los estudiantes:</strong> Si necesitas ingresar notas, puedes
                                    hacer clic en el botón <strong>"Calificar"</strong>, el cual te llevará a la sección
                                    de calificaciones.</li>
                                <li><strong>Generar reportes:</strong> Si necesitas un informe con los datos de los
                                    estudiantes, puedes hacer clic en <strong>"Reporte"</strong> para descargarlo.</li>
                                <li><strong>Exportar datos:</strong> También tienes la opción de descargar la lista de
                                    estudiantes en un archivo CSV para trabajarlo en tu computadora.</li>
                            </ul>

                            <p>Si en algún momento deseas regresar a la página anterior, puedes hacer clic en el botón
                                <strong>"Regresar"</strong>, ubicado en la parte inferior del lado izquierdo.
                            </p>
                        </div>

                        <!-- Paso 4 - Regresar a la Página Principal -->
                        <div id="step4" style="display:none;">
                            <h6><strong>Paso 4: Cómo regresar a la página principal</strong></h6>

                            <p><strong>¿Dónde está el botón para regresar?</strong></p>
                            <p>Si deseas volver a la página principal del sistema, debes buscar un botón que dice
                                <strong>"Regresar"</strong>.
                                Este botón está ubicado en la parte superior de la pantalla, cerca del título de la
                                página.
                                También tiene un ícono con una flecha que apunta hacia la izquierda.
                            </p>

                            <p><strong>¿Qué debes hacer?</strong></p>
                            <p>Para volver a la página principal, simplemente coloca el puntero del mouse sobre el botón
                                "Regresar" y haz clic una vez con el botón izquierdo del mouse.</p>

                            <p><strong>¿Qué pasará después?</strong></p>
                            <p>Después de hacer clic, la pantalla cambiará automáticamente y te llevará de vuelta a la
                                página de inicio del programa.</p>

                            <p><strong>¿Qué encontrarás en la página principal?</strong></p>
                            <p>La página principal es el primer lugar que ves cuando inicias sesión en el sistema. Aquí
                                podrás hacer muchas cosas importantes, como:</p>

                            <ul>
                                <li><strong>Ver tu información personal:</strong> En la parte superior de la pantalla,
                                    aparecerá tu nombre, apellido y tu rol dentro del sistema (por ejemplo, Profesor).
                                </li>

                                <li><strong>Leer un mensaje de bienvenida:</strong> Justo debajo de tu nombre, verás un
                                    mensaje que te da la bienvenida al sistema.</li>

                                <li><strong>Mirar imágenes sobre la institución:</strong> En la parte central de la
                                    pantalla, habrá una serie de imágenes que representan la Unidad Educativa Benjamín
                                    Franklin.</li>

                                <li><strong>Acceder a tus cursos:</strong> Habrá un botón que te permitirá ver la lista
                                    de los cursos que enseñas. Desde ahí, podrás revisar a tus estudiantes y administrar
                                    sus calificaciones.</li>

                                <li><strong>Conocer la historia de la institución:</strong> También encontrarás una
                                    sección con información sobre los 55 años de trayectoria de la Unidad Educativa.
                                </li>

                                <li><strong>Aprender sobre los valores institucionales:</strong> En esta parte, se
                                    mostrarán los valores más importantes de la institución, como el compromiso, la
                                    responsabilidad y el trabajo en equipo.</li>

                                <li><strong>Leer la misión y visión:</strong> Aquí se explican los objetivos educativos
                                    de la institución y su propósito en la formación de los estudiantes.</li>

                                <li><strong>Explorar las especialidades:</strong> Podrás conocer más sobre las
                                    diferentes carreras técnicas que se enseñan, como Electrónica, Electricidad y
                                    Mecánica Automotriz.</li>

                                <li><strong>Ver fotos y videos:</strong> En la parte inferior de la pantalla, habrá una
                                    galería de imágenes y videos que muestran actividades y momentos importantes dentro
                                    de la institución.</li>
                            </ul>

                            <p>Recuerda que si en algún momento te pierdes dentro del sistema, siempre puedes hacer clic
                                en <strong>"Regresar"</strong> para volver hasta la página principal.</p>
                        </div>
                    </div>
                    <!-- Pie de página del modal -->
                    <div class="modal-footer">
                        <button type="button" class="btn-modal" id="btnPrev" style="display:none;"
                            onclick="navigateModal(-1)">Atrás</button>
                        <button type="button" class="btn-modal" id="btnNext"
                            onclick="navigateModal(1)">Siguiente</button>
                        <button type="button" class="btn-modal" id="btnClose" style="display:none;"
                            onclick="cerrarModal()">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
    <footer>
        <p>&copy; 2024 Instituto Superior Tecnológico Guayaquil. Desarrollado por Giullia Arias y Carlos Zambrano. Todos
            los derechos reservados.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.7/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    let currentStep = 1; // Controla el paso actual

    function navigateModal(direction) {
        const totalSteps = 4; // Total de pasos
        document.getElementById(`step${currentStep}`).style.display = 'none'; // Oculta el paso actual
        currentStep += direction; // Cambia el paso
        document.getElementById(`step${currentStep}`).style.display = 'block'; // Muestra el nuevo paso

        // Actualizar título
        document.getElementById("modalInstruccionesLabel").innerText =
            `Manual de Uso - Gestión de Cursos (${currentStep}/4)`;

        // Control de botones
        document.getElementById("btnPrev").style.display = currentStep > 1 ? "inline-block" : "none";
        document.getElementById("btnNext").style.display = currentStep < totalSteps ? "inline-block" : "none";
        document.getElementById("btnClose").style.display = currentStep === totalSteps ? "inline-block" : "none";
    }

    function cerrarModal() {
        let modalElement = document.getElementById('modalInstrucciones');
        let modal = bootstrap.Modal.getInstance(modalElement);

        if (modal) {
            modal.hide();
        }

        // Asegurar que la pantalla gris desaparezca
        document.body.classList.remove('modal-open');
        let backdrops = document.getElementsByClassName('modal-backdrop');
        while (backdrops[0]) {
            backdrops[0].parentNode.removeChild(backdrops[0]);
        }

        resetModal();
    }

    function resetModal() {
        currentStep = 1; // Reinicia al paso 1
        document.getElementById("modalInstruccionesLabel").innerText = "Manual de Uso - Gestión de Cursos (1/4)";
        document.querySelectorAll('.modal-body > div').forEach(div => div.style.display = 'none');
        document.getElementById("step1").style.display = 'block';
        document.getElementById("btnPrev").style.display = "none";
        document.getElementById("btnNext").style.display = "inline-block";
        document.getElementById("btnClose").style.display = "none";
    }

    // Asegura que el modal se reinicie si se cierra con la "X" o clic afuera
    document.getElementById('modalInstrucciones').addEventListener('hidden.bs.modal', resetModal);
    </script>
</body>

</html>