<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Calificaciones | Sistema de Gestión UEBF</title>
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

        .container {
            max-width: 1200px;
            margin: 20px auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        table th,
        table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: center;
        }

        table th {
            background-color: #f2f2f2;
            color: #333;
            text-transform: uppercase;
        }

        table td input[type="number"] {
            width: 80px;
            text-align: center;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            transition: box-shadow 0.3s ease;
        }

        table td input[type="number"]:focus {
            outline: none;
            box-shadow: 0 0 5px rgba(0, 128, 255, 0.8);
        }

        .acciones {
            display: flex;
            justify-content: center;
            gap: 10px;
        }

        .acciones button {
            padding: 10px 20px;
            font-size: 14px;
            cursor: pointer;
            border: none;
            border-radius: 4px;
            transition: background-color 0.3s ease;
        }

        .acciones button.editar {
            background-color: #4CAF50;
            color: #fff;
        }

        .acciones button.editar:hover {
            background-color: #45a049;
        }

        .acciones button.eliminar {
            background-color: #f44336;
            color: #fff;
        }

        .acciones button.eliminar:hover {
            background-color: #f22f26;
        }

        .botones {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }

        .botones button {
            padding: 10px 20px;
            font-size: 14px;
            cursor: pointer;
            background-color: #008CBA;
            color: #fff;
            border: none;
            border-radius: 4px;
            transition: background-color 0.3s ease;
        }

        .botones button:hover {
            background-color: #0073aa;
        }

        .tabs {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }

        .tabs button {
            padding: 10px 20px;
            font-size: 14px;
            cursor: pointer;
            border: none;
            border-radius: 4px;
            transition: background-color 0.3s ease;
        }

        .tabs button.active {
            background-color: #4CAF50;
            color: #fff;
        }

        .tabs button:not(.active):hover {
            background-color: #45a049;
        }

        footer {
            background-color: #8b0000;
            color: #fff;
            padding: 20px;
            text-align: center;
            margin-top: 20px;
            clear: both;
        }
    </style>
</head>
<body>
    <header>
        <h1>SISTEMA DE GESTIÓN UEBF</h1>
        <p>Bienvenido(a) Profesor(a) Nombre del Profesor</p>
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
            <img src="http://localhost/sistema_notas/imagenes/media/{{$_SESSION['archivo']}}" alt="Imagen de perfil">
            <span>Nombre del Profesor</span>
            <button onclick="window.location.href='http://localhost/sistema_notas/login.php'">Cerrar Sesión</button>
        </div>
    </nav>

    <div class="container">
        <h1>Registro de Calificaciones</h1>

        <div class="filters">
            <select name="jornada" id="jornada">
                <option value="">Jornada</option>
                <option value="matutina">Matutina</option>
                <option value="vespertina">Vespertina</option>
            </select>
            <select name="paralelo" id="paralelo">
                <option value="">Paralelo</option>
                <option value="a">A</option>
                <option value="b">B</option>
                <option value="c">C</option>
            </select>
            <button onclick="applyFilters()">Aplicar Filtros</button>
        </div>

        <table id="tabla-calificaciones">
            <thead>
                <tr>
                    <th>ID Estudiante</th>
                    <th>Nombre Completo</th>
                    <th>Quimestre 1</th>
                    <th>Quimestre 2</th>
                    <th>Nota Final</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>001</td>
                    <td>Juan Pérez</td>
                    <td><input type="number" value="8.5"></td>
                    <td><input type="number" value="7.9"></td>
                    <td>8.2</td>
                    <td class="acciones">
                        <button class="editar" onclick="editRow(this)">Editar</button>
                        <button class="eliminar" onclick="deleteRow(this)">Eliminar</button>
                    </td>
                </tr>
                <tr>
                    <td>002</td>
                    <td>María González</td>
                    <td><input type="number" value="9.2"></td>
                    <td><input type="number" value="8.7"></td>
                    <td>9.0</td>
                    <td class="acciones">
                        <button class="editar" onclick="editRow(this)">Editar</button>
                        <button class="eliminar" onclick="deleteRow(this)">Eliminar</button>
                    </td>
                </tr>
                <!-- Aquí irían más filas con datos de calificaciones -->
            </tbody>
        </table>

        <div class="botones">
            <button onclick="window.location.href='http://localhost/sistema_notas/views/admin/form_curso_admin.php'">Agregar Curso</button>
            <button onclick="exportToPDF()">Exportar a PDF</button>
            <button onclick="exportToExcel()">Exportar a Excel</button>
        </div>
    </div>

    <footer>
        &copy; 2024 Instituto Superior Tecnológico Guayaquil. Desarrollado por Giullia Arias y Carlos Zambrano. Todos los derechos reservados.
    </footer>

    <script>
        function applyFilters() {
            // Implementa la lógica para aplicar filtros según la jornada y el paralelo seleccionados
            // Puedes utilizar AJAX para filtrar los datos dinámicamente si es necesario
            console.log('Filtros aplicados');
        }

        function editRow(button) {
            // Implementa la lógica para editar la fila correspondiente
            let row = button.closest('tr');
            let cells = row.querySelectorAll('td');

            // Aquí puedes abrir un modal con los campos para editar y actualizar la fila
            // Ejemplo básico: alert('Editar fila con ID: ' + cells[0].innerText);
        }

        function deleteRow(button) {
            // Implementa la lógica para eliminar la fila correspondiente
            let row = button.closest('tr');
            let cells = row.querySelectorAll('td');

            // Aquí puedes mostrar un mensaje de confirmación antes de eliminar
            if (confirm('¿Estás seguro de que quieres eliminar la fila con ID ' + cells[0].innerText + '?')) {
                row.remove();
                console.log('Fila eliminada');
            }
        }

        function exportToPDF() {
            // Implementa la lógica para exportar la tabla a PDF
            console.log('Exportando a PDF');
        }

        function exportToExcel() {
            // Implementa la lógica para exportar la tabla a Excel
            console.log('Exportando a Excel');
        }
    </script>
</body>
</html>
