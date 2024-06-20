<!DOCTYPE html>
<html lang="es">
<!-- Define el idioma de la página como español -->

<head>
    <meta charset="UTF-8">
    <!-- Establece la codificación de caracteres a UTF-8 -->

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Configura la vista para que sea compatible con dispositivos móviles -->

    <title>Reporte por Clase | Sistema de Gestión UEBF</title>
    <!-- Título de la página que aparece en la pestaña del navegador -->

    <link rel="shortcut icon" href="http://localhost/sistema_notas/imagenes/logo.png" type="image/x-icon">
    <!-- Icono que aparece en la pestaña del navegador -->

    <style>
        /* Estilos generales */
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
            color: #333;
        }

        /* Estilos para el encabezado */
        header {
            background-color: #8b0000;
            color: #fff;
            padding: 20px;
            text-align: center;
        }

        header h1 {
            margin: 0;
            font-size: 32px;
        }

        /* Estilos para la barra de navegación */
        nav {
            background-color: #a2000e;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 20px;
        }

        .menu {
            list-style-type: none;
            margin: 0;
            padding: 0;
            display: flex;
        }

        .menu li {
            position: relative;
        }

        .menu li a {
            display: block;
            color: #fff;
            text-align: center;
            padding: 14px 16px;
            text-decoration: none;
        }

        .menu li a:hover {
            background-color: #ff6347;
        }

        /* Dropdown */
        .dropdown-content {
            display: none;
            position: absolute;
            background-color: #ff6347;
            min-width: 160px;
            z-index: 1;
            left: 0;
            top: 100%;
        }

        .dropdown-content li {
            display: block;
        }

        .dropdown-content li a {
            color: #fff;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
            text-align: left;
        }

        .dropdown-content li a:hover {
            background-color: #ff4500;
        }

        /* Mostrar subsecciones al pasar el ratón */
        .dropdown:hover .dropdown-content {
            display: block;
        }

        /* Botón de cerrar sesión */
        .session-info {
            display: flex;
            align-items: center;
            color: #fff;
        }

        .session-info img {
            border-radius: 50%;
            margin-right: 10px;
            width: 32px;
            height: 32px;
        }

        .session-info span {
            font-size: 14px;
            color: #e5e5e5;
            margin-right: 20px;
        }

        .session-info button {
            background-color: #ff6347;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 50px;
            cursor: pointer;
        }

        .session-info button:hover {
            background-color: #ff4500;
        }
    </style>
</head>

<body>
    <!-- Cuerpo del documento -->
    <header>
        <h1>SISTEMA DE GESTIÓN "UEBF"</h1>
        <!-- Título principal en el encabezado -->
    </header>
    <nav>
        <!-- Barra de navegación -->
        <ul class="menu">
            <!-- Lista de elementos del menú -->
            <li><a href="http://localhost/sistema_notas/views/profe/index_profe.php">Inicio</a></li>
            <!-- Enlace a la página de inicio -->
            <li><a href="http://localhost/sistema_notas/views/profe/registronota_profe.php">Registro de Calificaciones</a></li>
            <!-- Enlace a la página de registro de calificaciones -->
            <li class="dropdown">
                <a href="#">Consulta de Calificaciones</a>
                <!-- Enlace que despliega un menú de consulta de calificaciones -->
                <ul class="dropdown-content">
                    <!-- Lista desplegable de consulta -->
                    <li><a href="http://localhost/sistema_notas/views/profe/notasclase_profe.php">Consulta por Clase</a></li>
                    <!-- Enlace a consulta por clase -->
                    <li><a href="http://localhost/sistema_notas/views/profe/notasestudiante_profe.php">Consulta por Estudiante</a></li>
                    <!-- Enlace a consulta por estudiante -->
                </ul>
            </li>
            <li class="dropdown">
                <a href="#">Reportes</a>
                <!-- Enlace que despliega un menú de reportes -->
                <ul class="dropdown-content">
                    <!-- Lista desplegable de reportes -->
                    <li><a href="http://localhost/sistema_notas/views/profe/reporteclase_profe.php">Reporte por Clase</a></li>
                    <!-- Enlace a reporte por clase -->
                    <li><a href="http://localhost/sistema_notas/views/profe/reporteestudiante_profe.php">Reporte por Estudiante</a></li>
                    <!-- Enlace a reporte por estudiante -->
                </ul>
            </li>
        </ul>
        <div class="session-info">
            <!-- Información de sesión del usuario -->
            <img src="http://localhost/sistema_notas/imagenes/agenda.png" alt="Usuario">
            <!-- Imagen del usuario -->
            <span>Nombre Completo del Usuario</span>
            <!-- Nombre del usuario -->
            <button>Cerrar Sesión</button>
            <!-- Botón para cerrar sesión -->
        </div>
    </nav>
    <section style="padding: 40px; text-align: center;">
        <!-- Sección principal con contenido centrado -->
        <h2>¡Bienvenido!</h2>
        <!-- Mensaje de bienvenida -->
        <p>Esta es el sistema de gestión para Calificaciones.</p>
        <!-- Descripción breve del sistema -->
    </section>
    <footer style="background-color: #8b0000; color: #fff; padding: 20px; text-align: center;">
        <!-- Pie de página -->
        <p>Derechos Reservados &copy; 2024</p>
        <!-- Información de derechos de autor -->
    </footer>
</body>

</html>
