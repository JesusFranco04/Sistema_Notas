<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consulta por Clase | Sistema de Gestión UEBF</title>
    <link rel="shortcut icon" href="http://localhost/sistema_notas/imagenes/logo.png" type="image/x-icon">
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
            transition: background-color 0.3s ease;
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
            box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
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
            transition: background-color 0.3s ease;
        }

        .session-info button:hover {
            background-color: #ff4500;
        }

        /* Estilos para el contenido principal */
        main {
            padding: 20px;
        }

        .student-list {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .student-list h2 {
            margin-top: 0;
            color: #333;
            font-size: 24px;
        }

        .student-list table {
            width: 100%;
            border-collapse: collapse;
        }

        .student-list th, .student-list td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .student-list th {
            background-color: #007bff;
            color: #fff;
        }

        .student-list tbody tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        .student-list tbody tr:hover {
            background-color: #e9ecef;
        }

        /* Pie de página */
        footer {
            text-align: center;
            padding: 10px;
            background-color: #333;
            color: #fff;
            position: fixed;
            bottom: 0;
            width: 100%;
        }

        footer p {
            margin: 0;
        }
    </style>
</head>
<body>
    <header>
        <h1>SISTEMA DE GESTIÓN "UEBF"</h1>
    </header>
    <nav>
        <ul class="menu">
            <li><a href="http://localhost/sistema_notas/views/profe/index_profe.php">Inicio</a></li>
            <li><a href="http://localhost/sistema_notas/views/profe/registronota_profe.php">Registro de Calificaciones</a></li>
            <li class="dropdown">
                <a href="#">Consulta de Calificaciones</a>
                <ul class="dropdown-content">
                    <li><a href="http://localhost/sistema_notas/views/profe/notasclase_profe.php">Consulta por Clase</a></li>
                    <li><a href="http://localhost/sistema_notas/views/profe/notasestudiante_profe.php">Consulta por Estudiante</a></li>
                </ul>
            </li>
            <li class="dropdown">
                <a href="#">Reportes</a>
                <ul class="dropdown-content">
                    <li><a href="http://localhost/sistema_notas/views/profe/reporteclase_profe.php">Reporte por Clase</a></li>
                    <li><a href="http://localhost/sistema_notas/views/profe/reporteestudiante_profe.php">Reporte por Estudiante</a></li>
                </ul>
            </li>
        </ul>
        <div class="session-info">
            <img src="http://localhost/sistema_notas/imagenes/agenda.png" alt="Usuario">
            <span>Nombre Completo del Usuario</span>
            <button>Cerrar Sesión</button>
        </div>
    </nav>
    <main>
        <section class="student-list">
            <h2>Consulta por Clase - Notas de Estudiantes</h2>
            <table>
                <thead>
                    <tr>
                        <th>Cédula</th>
                        <th>Nombre Completo</th>
                        <th>Nota 1</th>
                        <th>Nota 2</th>
                        <th>Nota 3</th>
                        <th>Promedio</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1234567890</td>
                        <td>Juan Pérez</td>
                        <td>85</td>
                        <td>90</td>
                        <td>88</td>
                        <td>87.67</td>
                        <td>
                            <button>Ver Detalles</button>
                        </td>
                    </tr>
                    <tr>
                        <td>0987654321</td>
                        <td>María Gómez</td>
                        <td>78</td>
                        <td>82</td>
                        <td>79</td>
                        <td>79.67</td>
                        <td>
                            <button>Ver Detalles</button>
                        </td>
                    </tr>
                    <!-- Más filas según los datos -->
                </tbody>
            </table>
        </section>
    </main>
    <footer>
        <p>&copy; 2024 Instituto Superior Tecnológico Guayaquil. Desarrollado por Giullia Arias y Carlos Zambrano. Todos los derechos reservados.</p>
    </footer>
</body>
</html>
