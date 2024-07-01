<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calificaciones | Sistema de Gestión UEBF</title>
    
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            margin: 0;
            padding: 20px;
        }

        .tabs-container {
            display: flex;
            justify-content: flex-end;
            background-color: #f1f1f1;
            border-bottom: 1px solid #ccc;
            margin-bottom: 20px;
        }

        .tabs {
            display: flex;
            list-style-type: none;
            padding: 0;
            margin: 0;
        }

        .tablink {
            background-color: #ddd;
            border: 1px solid #ccc;
            cursor: pointer;
            padding: 10px 20px;
            transition: background-color 0.3s;
        }

        .tablink.active {
            background-color: #ccc;
        }

        .tabcontent {
            display: none;
        }

        .tabcontent.active {
            display: block;
        }

        .table-container {
            margin-top: 20px;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            background-color: #fff;
            margin-bottom: 20px;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: center;
        }

        th {
            background-color: #f2f2f2;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .filters {
            margin-bottom: 10px;
        }

        .filters label {
            margin-right: 10px;
        }

        /* Estilos para el modal */
        .modal {
            display: none; /* Por defecto oculto */
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
            padding-top: 60px;
        }

        .modal-content {
            background-color: #fefefe;
            margin: auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 600px;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        @media screen and (max-width: 600px) {
            .tabs-container {
                justify-content: flex-start;
            }
            .tablink {
                padding: 8px 16px;
            }
        }
    </style>
</head>
<body>

    <div class="tabs-container">
        <ul class="tabs">
            <li><button class="tablink active" onclick="openTab(event, 'primerQuimestre')">Primer Quimestre</button></li>
            <li><button class="tablink" onclick="openTab(event, 'segundoQuimestre')">Segundo Quimestre</button></li>
            <li><button class="tablink" onclick="openTab(event, 'notaFinal')">Nota Final</button></li>
        </ul>
    </div>

    <!-- Cejilla: Primer Quimestre -->
    <div id="primerQuimestre" class="tabcontent active">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID Estudiante</th>
                        <th>Nombre</th>
                        <th>Apellido</th>
                        <th>Materia</th>
                        <th>Nota Parcial 1</th>
                        <th>Nota Parcial 2</th>
                        <th>Examen</th>
                        <th>Promedio</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>001</td>
                        <td>Juan</td>
                        <td>Pérez</td>
                        <td>Matemáticas</td>
                        <td>8.5</td>
                        <td>8.0</td>
                        <td>9.0</td>
                        <td>8.5</td>
                        <td>
                            <button onclick="editarCalificacion(1)">Editar</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Cejilla: Segundo Quimestre -->
    <div id="segundoQuimestre" class="tabcontent">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID Estudiante</th>
                        <th>Nombre</th>
                        <th>Apellido</th>
                        <th>Materia</th>
                        <th>Nota Parcial 1</th>
                        <th>Nota Parcial 2</th>
                        <th>Examen</th>
                        <th>Promedio</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>003</td>
                        <td>José</td>
                        <td>Ramírez</td>
                        <td>Matemáticas</td>
                        <td>7.5</td>
                        <td>7.0</td>
                        <td>8.0</td>
                        <td>7.5</td>
                        <td>
                            <button onclick="editarCalificacion(3)">Editar</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Cejilla: Nota Final del Período Académico -->
    <div id="notaFinal" class="tabcontent">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID Estudiante</th>
                        <th>Nombre</th>
                        <th>Apellido</th>
                        <th>Promedio 1er Quimestre</th>
                        <th>Promedio 2do Quimestre</th>
                        <th>Nota Final</th>
                        <th>Resultado Final</th>
                        <th>Supletorio</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>001</td>
                        <td>Juan</td>
                        <td>Pérez</td>
                        <td>8.5</td>
                        <td>7.5</td>
                        <td>8.0</td>
                        <td>Aprobado</td>
                        <td>---</td>
                        <td>
                            <button onclick="editarNotaFinal(1)">Editar</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal para editar calificaciones -->
    <div id="myModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2>Editar Calificación</h2>
            <form id="editForm">
                <label for="editNota">Nueva Nota:</label>
                <input type="text" id="editNota" name="editNota" required>
                <br><br>
                <button type="submit">Guardar</button>
            </form>
        </div>
    </div>

    <script>
        // Función para abrir el contenido de las pestañas
        function openTab(evt, tabName) {
            var i, tabcontent, tablinks;
            tabcontent = document.getElementsByClassName("tabcontent");
            for (i = 0; i < tabcontent.length; i++) {
                tabcontent[i].style.display = "none";
            }
            tablinks = document.getElementsByClassName("tablink");
            for (i = 0; i < tablinks.length; i++) {
                tablinks[i].className = tablinks[i].className.replace(" active", "");
            }
            document.getElementById(tabName).style.display = "block";
            evt.currentTarget.className += " active";
        }

        // Mostrar modal para editar calificación
        function editarCalificacion(idEstudiante) {
            var modal = document.getElementById("myModal");
            modal.style.display = "block";
            // Aquí podrías implementar la lógica para prellenar el formulario con la información actual del estudiante
            // En este ejemplo, solo se muestra el modal
        }

        // Mostrar modal para editar nota final
        function editarNotaFinal(idEstudiante) {
            var modal = document.getElementById("myModal");
            modal.style.display = "block";
            // Aquí podrías implementar la lógica para prellenar el formulario con la información actual del estudiante
            // En este ejemplo, solo se muestra el modal
        }

        // Cerrar el modal
        function closeModal() {
            var modal = document.getElementById("myModal");
            modal.style.display = "none";
            // Aquí podrías implementar la lógica para limpiar el formulario o realizar otras acciones al cerrar el modal
        }

        // Event listener para cerrar el modal haciendo clic fuera de él
        window.onclick = function(event) {
            var modal = document.getElementById("myModal");
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }

        // Event listener para enviar el formulario de edición (simulado)
        document.getElementById("editForm").addEventListener("submit", function(event) {
            event.preventDefault();
            var nuevaNota = document.getElementById("editNota").value;
            alert("Guardar nueva nota: " + nuevaNota);
            closeModal();
            // Aquí podrías implementar la lógica para enviar la nueva nota al servidor y actualizar la tabla
        });
    </script>

</body>
</html>
