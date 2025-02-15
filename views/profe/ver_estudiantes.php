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

// Asegúrate de que id_curso esté definido en la URL
if (!isset($_GET['id_curso'])) {
    echo "ID de curso no definido.";
    exit();
}

$id_curso = intval($_GET['id_curso']);

// Obtener los detalles del curso
$sql_curso = "SELECT c.id_curso, h.año AS año_academico
              FROM curso c
              JOIN historial_academico h ON c.id_his_academico = h.id_his_academico
              WHERE c.id_curso = ?";
$stmt_curso = $conn->prepare($sql_curso);
$stmt_curso->bind_param("i", $id_curso);
$stmt_curso->execute();
$result_curso = $stmt_curso->get_result();
$curso = $result_curso->fetch_assoc();
$stmt_curso->close();

if (!$curso) {
    echo "Curso no encontrado.";
    exit();
}

$año_academico = $curso['año_academico'];

// Verificar el estado del año académico
$sql_año = "SELECT estado FROM historial_academico WHERE año = ?";
$stmt_año = $conn->prepare($sql_año);
$stmt_año->bind_param("s", $año_academico);
$stmt_año->execute();
$result_año = $stmt_año->get_result();
$estado_año = $result_año->fetch_assoc()['estado']; // 'A' o 'I'
$stmt_año->close();

// Convertir estado a valores legibles
if ($estado_año === 'A') {
    $estado_año = 'activo';
} elseif ($estado_año === 'I') {
    $estado_año = 'inactivo';
} else {
    echo "Estado del año académico no reconocido.";
    exit();
}

// Obtener la cantidad de estudiantes en el curso
$sql_estudiantes = "SELECT COUNT(*) AS total_estudiantes
                    FROM estudiante e
                    JOIN curso c ON e.id_nivel = c.id_nivel
                                AND e.id_subnivel = c.id_subnivel
                                AND e.id_especialidad = c.id_especialidad
                                AND e.id_paralelo = c.id_paralelo
                                AND e.id_jornada = c.id_jornada
                                AND e.id_his_academico = c.id_his_academico
                    WHERE c.id_curso = ? AND e.estado = 'A'";  // Asegúrate de contar solo estudiantes activos

$stmt_estudiantes = $conn->prepare($sql_estudiantes);
$stmt_estudiantes->bind_param("i", $id_curso);
$stmt_estudiantes->execute();
$result_estudiantes = $stmt_estudiantes->get_result();
$cantidad_estudiantes = $result_estudiantes->fetch_assoc()['total_estudiantes'];
$stmt_estudiantes->close();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Alumnos | Sistema de Gestión UEBF</title>
    <link rel="shortcut icon" href="http://localhost/sistema_notas/imagenes/logo.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://unpkg.com/boxicons/css/boxicons.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
    /* Reset de estilos para una base limpia */
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

    header .banner {
        background-color: #c61e1e;
        color: white;
        text-align: center;
        padding: 25px 0;
        font-size: 2.5rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 2px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        border-bottom: 3px solid #0052aa;
    }

    /* Contenedor principal */
    .container {
        max-width: 1400px;
        width: 90%;
        margin: 50px auto;
        padding: 25px;
        background: white;
        border-radius: 15px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    }

    /* Títulos */
    h2 {
        font-size: 2.5rem;
        color: #c61e1e;
        font-weight: bold;
        margin-bottom: 35px;
        text-align: center;
    }

    /* Contenedor del formulario */
    .search-form {
        display: flex;
        justify-content: center;
        align-items: center;
        margin-top: 20px;
        margin-bottom: 20px;
    }

    /* Contenedor principal */
    .search-container {
        display: flex;
        align-items: center;
        background-color: white;
        /* Fondo rojo claro */
        border: 2px solid #B71C1C;
        /* Borde rojo */
        border-radius: 25px;
        /* Bordes redondeados */
        padding: 5px 10px;
        max-width: 600px;
        width: 100%;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    /* Campo de entrada */
    .search-input {
        flex: 1;
        /* Ocupa todo el espacio disponible */
        border: none;
        /* Sin borde */
        background: none;
        /* Sin fondo adicional */
        padding: 12px 15px;
        font-size: 1.1rem;
        color: #B71C1C;
        /* Texto rojo */
        outline: none;
        /* Sin borde al enfocar */
    }

    .search-input::placeholder {
        color: #B71C1C;
        /* Color del texto del placeholder */
        opacity: 0.8;
    }

    /* Botón de búsqueda */
    .search-button {
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: #B71C1C;
        /* Fondo rojo */
        color: white;
        /* Texto blanco */
        border: none;
        /* Sin borde */
        border-radius: 20px;
        /* Bordes redondeados */
        padding: 10px 20px;
        font-size: 1.1rem;
        font-weight: bold;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }

    .search-button i {
        margin-right: 8px;
        font-size: 1.2rem;
    }

    .search-button:hover {
        background-color: #D32F2F;
        /* Rojo más oscuro al pasar el mouse */
        transform: translateY(-2px);
    }


    /* Tabla estilizada */
    table {
        width: 100%;
        border-collapse: collapse;
        background: white;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    th,
    td {
        padding: 18px;
        text-align: left;
        border-bottom: 2px solid #ddd;
        font-size: 1.1rem;
    }

    th {
        background: #3A8DFF;
        color: white;
        font-weight: bold;
        text-align: center;
    }

    tr:hover {
        background: #f1f1f1;
    }

    /* Estilo de los botones en general */
    .btn-custom {
        background-color: #ffffff;
        color: #D32F2F;
        /* Cambiado de azul a rojo */
        border: 2px solid #D32F2F;
        /* Cambiado de azul a rojo */
        padding: 16px 32px;
        border-radius: 35px;
        font-size: 1.1rem;
        font-weight: 600;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-right: 15px;
        margin-top: 15px;
    }

    .btn-custom i {
        margin-right: 10px;
    }

    .btn-custom:hover {
        background-color: #D32F2F;
        /* Cambiado de azul a rojo */
        color: white;
        transform: translateY(-4px);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    /* Estilos comunes para ambos botones */
    .btn {
        padding: 16px 32px;
        border-radius: 35px;
        font-size: 1.1rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
        text-decoration: none;
    }


    /* NUEVO: Botón Lista de Asistencia (color rojo, con estado deshabilitado) */
    .btn-asistencia {
        background-color: #ffffff;
        color: #D32F2F;
        border: 2px solid #D32F2F;
        padding: 16px 32px;
        border-radius: 35px;
        font-size: 1.1rem;
        font-weight: 600;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-right: 15px;
        margin-top: 15px;
    }

    .btn-asistencia i {
        margin-right: 10px;
    }

    .btn-asistencia:hover {
        background-color: #D32F2F;
        color: white;
        transform: translateY(-4px);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
    }

    /* Estilos para el botón deshabilitado */
    .btn-asistencia:disabled {
        background-color: #f1f1f1;
        /* Color de fondo cuando está deshabilitado */
        color: #ccc;
        /* Color de texto cuando está deshabilitado */
        border: 2px solid #ccc;
        /* Color del borde cuando está deshabilitado */
        cursor: not-allowed;
        /* Cursor en forma de prohibido */
        pointer-events: none;
        /* Desactiva cualquier interacción con el botón */
        opacity: 0.6;
        /* Añade un efecto visual para que parezca más deshabilitado */
    }

    /* Estilos para el botón Manual de Uso */
    .btn-open-modal {
        background-color: white;
        color: #050274;
        border: 2px solid #050274;
        margin-top: 15px;
        margin-right: 0;
    }

    .btn-open-modal i {
        margin-right: 10px;
    }

    .btn-open-modal:hover {
        background-color: #050274;
        color: white;
        transform: translateY(-4px);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
    }

    .modal-body {
        max-height: 400px;
        /* Ajusta la altura según lo que necesites */
        overflow-y: auto;
        /* Permite el scroll vertical */
    }

    /* Contenedor para los botones */
    .botones-en-linea {
        display: flex;
        justify-content: space-between;
        width: 100%;
    }

    /* ---------- MODAL GENERAL ---------- */
    .modal-content {
        border-radius: 10px;
        /* Bordes redondeados */
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        /* Sombra elegante */
        border: none;
    }

    /* ---------- ENCABEZADO ---------- */
    .modal-header {
        background: linear-gradient(135deg, #007bff, #0056b3);
        /* Azul principal */
        color: white;
        border-top-left-radius: 10px;
        border-top-right-radius: 10px;
        padding: 15px;
        font-weight: bold;
    }

    .modal-header .close {
        color: white;
        font-size: 22px;
        opacity: 0.8;
    }

    .modal-header .close:hover {
        opacity: 1;
    }

    /* ---------- CUERPO DEL MODAL ---------- */
    .modal-body {
        padding: 20px;
        font-size: 14px;
        line-height: 1.6;
        color: #333;
    }

    /* ---------- LISTAS EN EL MODAL ---------- */
    .modal-body ul {
        list-style: none;
        padding-left: 0;
    }

    .modal-body ul li::before {
        content: "✔ ";
        color: #28a745;
        /* Verde */
        font-weight: bold;
        margin-right: 5px;
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

    /* NUEVO: Botón Reporte (color azul) */
    .btn-reporte {
        background-color: #ffffff;
        color: #1565c0;
        /* Azul */
        border: 2px solid #1565c0;
        /* Azul */
        padding: 16px 32px;
        border-radius: 35px;
        font-size: 1.1rem;
        font-weight: 600;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-right: 15px;
        margin-top: 15px;
    }

    .btn-reporte:hover {
        background-color: #1565c0;
        /* Azul */
        color: white;
        transform: translateY(-4px);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }



    /* NUEVO: Botón Exportar a CSV (color verde) */
    .btn-exportar-csv {
        background-color: #ffffff;
        color: #388e3c;
        /* Verde */
        border: 2px solid #388e3c;
        /* Verde */
        padding: 16px 32px;
        border-radius: 35px;
        font-size: 1.1rem;
        font-weight: 600;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-right: 15px;
        margin-top: 15px;
    }

    .btn-exportar-csv:hover {
        background-color: #388e3c;
        /* Verde */
        color: white;
        transform: translateY(-4px);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    /* Footer */
    footer {
        border-top: 3px solid #073b73;
        /* Borde en la parte superior */
        background-color: #ad0f0f;
        color: white;
        text-align: center;
        padding: 25px;
        margin-top: 60px;
        font-size: 1.1rem;
        box-shadow: 0 -4px 12px rgba(0, 0, 0, 0.1);
        /* Sombra más pronunciada */
    }

    /* Estilos responsivos */
    @media (max-width: 768px) {
        .search-container {
            flex-direction: column;
            padding: 10px;
        }

        .search-input {
            width: 100%;
            margin-bottom: 10px;
        }

        .search-button {
            width: 100%;
        }

        .btn-custom,
        .btn-asistencia,
        .btn-reporte,
        .btn-exportar-csv {
            width: 100%;
            margin-right: 0;
        }

        th,
        td {
            font-size: 0.9rem;
        }

        h2 {
            font-size: 2rem;
        }
    }

    @media (max-width: 480px) {
        .container {
            padding: 15px;
        }

        header .banner {
            font-size: 2rem;
        }

        h2 {
            font-size: 1.5rem;
        }

        th,
        td {
            padding: 10px;
        }
    }

    /* Responsividad para botones */
    @media (max-width: 768px) {

        .btn-custom,
        .btn-asistencia,
        .btn-reporte,
        .btn-exportar-csv {
            width: 100%;
            /* Ocupan todo el ancho disponible */
            margin-right: 0;
            /* Elimina márgenes laterales */
            padding: 14px 20px;
            /* Reduce el padding para adaptarse mejor */
            font-size: 1rem;
            /* Tamaño de texto más pequeño */
        }
    }

    @media (max-width: 480px) {

        .btn-custom,
        .btn-asistencia,
        .btn-reporte,
        .btn-exportar-csv {
            padding: 12px 16px;
            /* Más compacto para móviles */
            font-size: 0.9rem;
            /* Ajusta el tamaño del texto */
        }
    }
    </style>
</head>

<body>
    <!-- Header con el Banner -->
    <header>
        <div class="banner">
            Sistema de Gestión UEBF
        </div>
    </header>

    <!-- Contenedor principal del sitio -->
    <div class="container">

        <!-- Contenedor principal con los elementos -->
        <div class="main-container">

            <!-- Sección con el título principal -->
            <section class="text-center my-3">
                <h2>Panel de Seguimiento de Alumnos</h2>
            </section>

            <!-- Formulario de búsqueda -->
            <form id="searchForm" class="search-form">
                <div class="search-container">
                    <input type="text" id="searchQuery" class="search-input"
                        placeholder="Buscar por cédula, apellido o nombre" aria-label="Buscar estudiantes" />
                    <button type="submit" class="search-button" aria-label="Buscar">
                        <i class='bx bx-search'></i> Buscar
                    </button>
                </div>
            </form>

            <!-- Contenedor para los botones en la misma línea -->
            <div class="botones-en-linea">
                <!-- Botón para ver lista de asistencia -->
                <button id="btn-asistencia" class="btn btn-asistencia"
                    onclick="location.href='asistencia_estudiantes.php?id_curso=<?php echo urlencode($id_curso); ?>'">
                    <i class='bx bx-list-check'></i> Lista de Asistencia
                </button>

                <!-- Botón para abrir el modal de Manual de Uso -->
                <button type="button" class="btn btn-open-modal" data-bs-toggle="modal"
                    data-bs-target="#modalInstrucciones">
                    <i class='bx bx-book'></i> Manual de Uso
                </button>
            </div>

            <!-- Verificar si hay mensajes de error -->
            <?php
            if (isset($_GET['error'])) {
                $error = urldecode($_GET['error']); // Decodifica el mensaje de error de la URL
                echo "<div class='error-message'>$error</div>"; // Muestra el mensaje en la página
            }
            ?>

            <!-- Contenedor para resultados y gráfica -->
            <section id="resultado-grafica" class="d-flex flex-column align-items-center mt-4">
                <!-- Contenedor de los resultados (tabla) -->
                <div id="resultado" class="table-responsive mb-4">
                    <!-- Aquí se mostrará la lista de estudiantes -->
                </div>

                <!-- Contenedor para la gráfica -->
                <div id="grafica" class="w-100 d-flex justify-content-center">
                    <canvas id="chartEstudiantes" width="600" height="300"></canvas>
                </div>
            </section>

            <!-- Botones de acción -->
            <div class="d-flex justify-content-end gap-2 mt-3">
                <button id="btn-regresar" class="btn btn-regresar btn-custom">
                    <i class='bx bx-arrow-back'></i> Regresar
                </button>
                <button id="btn-calificar" class="btn btn-calificar btn-custom" data-estado="<?php echo $estado_año; ?>"
                    data-id-curso="<?php echo $id_curso; ?>">
                    <i class='bx bx-pencil'></i> Calificar
                </button>
                <a href="nomina_estudiantes.php?id_curso=<?php echo $id_curso; ?>" class="btn btn-reporte btn-custom">
                    <i class='bx bx-file'></i> Reporte
                </a>
                <button id="btn-exportar" class="btn btn-exportar-csv btn-custom">
                    <i class='bx bx-export'></i> Exportar a CSV
                </button>
            </div>
            <!-- Modal único - Registro de Calificaciones -->
            <div class="modal fade" id="modalInstrucciones" tabindex="-1" role="dialog"
                aria-labelledby="modalInstruccionesLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <!-- Encabezado con franja azul -->
                        <div class="modal-header" style="background-color: #007bff; color: white;">
                            <h5 class="modal-title" id="modalInstruccionesLabel">Manual de Uso - Registro de
                                Calificaciones</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"
                                onclick="cerrarModal()" style="color: white; border: none; background: none;">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <!-- Cuerpo del modal -->
                        <div class="modal-body" id="modalContent">

                            <!-- Paso 1 - Año Lectivo Abierto (Ingreso de Notas) -->
                            <div id="step1">
                                <h6><strong>Paso 1: Ingresar las Calificaciones (Año Abierto)</strong></h6>
                                <p><strong>Ubicación:</strong> Verás una tabla con la lista de estudiantes y campos
                                    vacíos donde podrás ingresar sus calificaciones.</p>
                                <p><strong>Formato obligatorio:</strong></p>
                                <ul>
                                    <li>Las calificaciones deben estar entre <strong>0 y 10</strong>.</li>
                                    <li>Usa <strong>un solo decimal</strong> (ejemplo: **7,5** y no 7.55).</li>
                                    <li>El decimal se escribe con <strong>coma (,)</strong> y no con punto (.).</li>
                                </ul>
                                <p><strong>Acción:</strong> Haz clic en el campo vacío junto al nombre del estudiante y
                                    escribe la calificación siguiendo el formato correcto.</p>
                                <p><strong>Guardado automático:</strong> Algunas versiones del sistema guardan
                                    automáticamente la calificación. Si no es así, haz clic en el botón
                                    <strong>"Guardar"</strong>.
                                </p>
                                <p><strong>Nota importante:</strong> El sistema calcula automáticamente la <strong>nota
                                        final</strong> basándose en las calificaciones de los parciales y otras
                                    evaluaciones.</p>
                            </div>

                            <!-- Paso 2 - Corrección de Errores -->
                            <div id="step2" style="display:none;">
                                <h6><strong>Paso 2: Revisar y Corregir Errores</strong></h6>
                                <p><strong>Verificación:</strong> Antes de salir, revisa que todas las notas ingresadas
                                    sean correctas. Si ingresaste un valor incorrecto, aparecerá un mensaje de error.
                                </p>
                                <p><strong>Errores comunes:</strong></p>
                                <ul>
                                    <li>Si escribes **<strong>10.0</strong>**, el sistema te pedirá que uses una coma:
                                        <strong>**10,0**.</strong>
                                    </li>
                                    <li>Si dejas un espacio en blanco, el sistema te pedirá que completes todas las
                                        calificaciones.</li>
                                    <li>Si escribes más de un decimal (ejemplo: **8,55**), el sistema no lo aceptará.
                                    </li>
                                </ul>
                                <p><strong>Cómo corregir:</strong> Haz clic en la calificación incorrecta y escribe la
                                    nota en el formato correcto.</p>
                            </div>

                            <!-- Paso 3 - Guardar y Finalizar -->
                            <div id="step3" style="display:none;">
                                <h6><strong>Paso 3: Guardar y Finalizar</strong></h6>
                                <p><strong>Guardar cambios:</strong> Una vez que ingresaste todas las calificaciones,
                                    revisa que no haya errores y haz clic en <strong>"Guardar"</strong>.</p>
                                <p><strong>Finalización:</strong> Si todo está correcto, haz clic en
                                    <strong>"Finalizar"</strong> o
                                    simplemente sal de la página.
                                </p>
                                <p><strong>Nota:</strong> Si necesitas modificar una calificación después de haber
                                    guardado, puedes volver al curso y editarla.</p>
                                <p><strong>¿Tienes dudas?</strong> Si tienes problemas con el registro de notas, pide
                                    ayuda a un compañero o contacta a un administrador.</p>
                            </div>

                            <!-- Paso 4 - Año Lectivo Cerrado (Explicación del proceso cuando el año ya está cerrado) -->
                            <div id="step4" style="display:none;">
                                <h6><strong>Paso 4: Qué hacer cuando el Año Lectivo Está Cerrado</strong></h6>
                                <p><strong>Descripción:</strong> Cuando el año lectivo ya está cerrado, ya no podrás
                                    modificar las calificaciones y solamente se podra visualizar. El sistema ha
                                    calculado las calificaciones finales y ha determinado si los estudiantes aprobaron o
                                    no.</p>
                                <p><strong>Acción:</strong> El año lectivo cerrado significa que no se pueden realizar
                                    modificaciones a las calificaciones. <strong>Si alguna calificación es incorrecta,
                                        se
                                        quedara asi permanentemente.</strong></p>
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
</body>

<!-- Footer -->
<footer class="bg-footer text-white text-center py-2 mt-4">
    <p>&copy; 2024 Instituto Superior Tecnológico Guayaquil. Desarrollado por Giullia Arias y Carlos Zambrano. Todos los
        derechos reservados.</p>
</footer>


<script>
// Obtiene el estado del año académico y la cantidad de estudiantes desde PHP
const estado = "<?php echo $estado_año; ?>";
const cantidadEstudiantes =
    <?php echo $cantidad_estudiantes; ?>; // Se espera que $cantidad_estudiantes venga de PHP como un número

// Verifica las condiciones para deshabilitar el botón
if (estado === 'inactivo' || cantidadEstudiantes === 0) {
    document.getElementById('btn-asistencia').disabled = true;
}
</script>

<script>
$(document).ready(function() {
    // Llamada AJAX para obtener la lista de estudiantes
    function loadEstudiantes(query = '') {
        $.ajax({
            url: 'get_estudiantes.php',
            type: 'POST',
            data: {
                id_curso: <?php echo json_encode($id_curso); ?>,
                año: '<?php echo $año_academico; ?>',
                query: query
            },
            success: function(response) {
                $('#resultado').html(response);
            },
            error: function(xhr, status, error) {
                console.error('Error en la solicitud AJAX para estudiantes:', status, error);
            }
        });
    }

    // Cargar la lista de estudiantes al cargar la página
    loadEstudiantes();

    // Manejo de la búsqueda
    $('#searchForm').submit(function(e) {
        e.preventDefault();
        var query = $('#searchQuery').val();
        loadEstudiantes(query);
    });

    // Manejo del botón regresar
    $('#btn-regresar').click(function() {
        window.location.href = 'curso_profe.php'; // Cambia esta URL si es necesario
    });

    // Manejo del botón calificar
    $('#btn-calificar').click(function() {
        // Obtener el estado del año lectivo y el id del curso desde los atributos del botón
        const estadoAño = $(this).data('estado'); // "activo" o "inactivo"
        const idCurso = $(this).data('id-curso');

        // Redirigir según el estado del año lectivo
        if (estadoAño === 'activo') {
            // Año lectivo activo: enviar a registro_calificaciones.php
            window.location.href = `registro_calificaciones.php?id_curso=${idCurso}`;
        } else if (estadoAño === 'inactivo') {
            // Año lectivo inactivo: enviar a cursos_inactivos.php
            window.location.href = `cursos_inactivos.php?id_curso=${idCurso}`;
        } else {
            alert('Error: Estado del año lectivo no reconocido.');
        }
    });

    // Manejo del botón exportar
    $('#btn-exportar').click(function() {
        window.location.href =
            'exportar_estudiantes.php?id_curso=<?php echo $id_curso; ?>&año=<?php echo $año_academico; ?>';
    });

    // Llamada AJAX para obtener estadísticas
    $.ajax({
        url: 'get_estadisticas.php',
        type: 'POST',
        data: {
            id_curso: <?php echo json_encode($id_curso); ?>,
            año: '<?php echo $año_academico; ?>'
        },
        success: function(response) {
            var data = JSON.parse(response);

            var ctx = document.getElementById('chartEstudiantes').getContext('2d');

            // Definimos el color de fondo con un degradado sutil
            var gradient = ctx.createLinearGradient(0, 0, 0, 400);
            gradient.addColorStop(0, '#a5d6a7'); // Verde claro
            gradient.addColorStop(1, '#388e3c'); // Verde más oscuro

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: Object.keys(data.edades), // Etiquetas para el gráfico
                    datasets: [{
                        label: 'Número de Estudiantes por Edad',
                        data: Object.values(data.edades), // Datos para el gráfico
                        backgroundColor: gradient, // Aplicamos el color degradado
                        borderColor: '#388e3c', // Borde de las barras verde
                        borderWidth: 2, // Borde más fino
                        hoverBackgroundColor: '#66bb6a', // Color más claro en hover
                        hoverBorderColor: '#388e3c', // Borde en hover
                        barThickness: 30, // Aumentamos el grosor de las barras
                        borderRadius: 8, // Bordes redondeados en las barras para suavizar el aspecto
                        shadowOffsetX: 5, // Sombra más pronunciada para dar un efecto de profundidad
                        shadowOffsetY: 5, // Sombra más pronunciada para dar un efecto de profundidad
                        shadowBlur: 6, // Aumentamos el desenfoque de la sombra
                        shadowColor: 'rgba(0, 0, 0, 0.2)' // Color suave de sombra
                    }]
                },
                options: {
                    responsive: true, // Hace que el gráfico sea responsivo
                    maintainAspectRatio: false, // Permite que el gráfico cambie de tamaño según el contenedor
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                color: '#388e3c', // Color verde para los números en el eje Y
                                font: {
                                    size: 14, // Tamaño de la fuente
                                    weight: '600', // Peso de la fuente (semi-negrita)
                                    family: 'Arial, sans-serif', // Fuente limpia y moderna
                                }
                            },
                            grid: {
                                color: '#c8e6c9', // Color suave para las líneas de la cuadrícula
                                borderColor: '#388e3c', // Color del borde del gráfico
                                borderWidth: 1
                            }
                        },
                        x: {
                            ticks: {
                                color: '#388e3c', // Color de los números en el eje X
                                font: {
                                    size: 14, // Tamaño de la fuente
                                    weight: '600', // Peso de la fuente (semi-negrita)
                                    family: 'Arial, sans-serif', // Fuente limpia y moderna
                                }
                            },
                            grid: {
                                color: '#c8e6c9', // Color suave para las líneas de la cuadrícula
                                borderColor: '#388e3c', // Color del borde del gráfico
                                borderWidth: 1
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            labels: {
                                color: '#388e3c', // Color verde para las etiquetas
                                font: {
                                    size: 14, // Tamaño de la fuente de la leyenda
                                    weight: '600', // Peso de la fuente
                                    family: 'Arial, sans-serif', // Fuente limpia
                                }
                            }
                        }
                    },
                    animation: {
                        duration: 1200, // Duración de la animación
                        easing: 'easeOutQuart', // Tipo de animación (suave)
                        onComplete: function() {
                            console.log('Gráfico cargado con éxito.');
                        }
                    }
                }
            });
        },
        error: function(xhr, status, error) {
            console.error('Error en la solicitud AJAX:', status, error);
        }
    });
});


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