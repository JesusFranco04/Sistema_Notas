<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>Estudiantes | Sistema de Gestión UEBF</title>
    <link rel="shortcut icon" href="http://localhost/sistema_notas/imagenes/logo.png" type="image/x-icon">
    <!-- Custom fonts for this template-->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet"
        type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">
    <!-- Custom styles for this template-->
    <link href="http://localhost/sistema_notas/css/sb-admin-2.min.css" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <!-- Estilos personalizados -->
    <style>
        .sidebar-heading .collapse-header .bx {
            color: #ff8b97;
            /* Color rosa claro para los iconos en los encabezados de sección */
        }

        .bg-gradient-primary {
            background-color: #a2000e;
            /* Color rojo oscuro para el fondo de la barra lateral */
            background-image: none;
            /* Asegurar que no haya imagen de fondo (gradiente) */
        }
    </style>
</head>

<body>
    <?php
    // Incluir el archivo de conexión y verificar la conexión
    include '../../Crud/config.php';

    $sql = "SELECT * FROM estudiantes";
    $resultado = $conn->query($sql);

    if (!$resultado) {
        die("Error en la consulta: " . $conn->error);
    }
    ?>

    <?php include_once 'navbar_admin.php'; ?>

    <div class="container-fluid">
        <div class="row">
            <div class="container">
                <h1 class="mt-1 text-center">Tabla de Estudiantes</h1>
                <div class="mb-5 mt-5">
                    <input type="text" class="form-control" id="filtroSolicitud"
                        placeholder="Filtrar por Cédula del Estudiante" onkeyup="filtrarSolicitudes()">
                </div>
                <div class="mb-4 mt-3">
                    <div class="row justify-content-start">
                        <div class="col-auto">
                            <a href="../../Crud/estudiantes/agregar_estudiantes.php" class="btn btn-primary">Agregar
                                Estudiante</a>
                        </div>
                        <div class="col-auto">
                            <button type="button" class="btn btn-info" data-toggle="modal"
                                data-target="#modalInstrucciones1">Ver Manual de Uso</button>
                        </div>
                        <div class="col-auto">
                            <a href="reporte_estudiante.php" class="btn btn-success">Generar reportes</a>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombres</th>
                                <th>Apellidos</th>
                                <th>Cédula</th>
                                <th>Teléfono</th>
                                <th>Correo Electrónico</th>
                                <th>Fecha de Creación</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($fila = $resultado->fetch_assoc()) { ?>
                            <tr>
                                <td><?php echo $fila['id']; ?></td>
                                <td><?php echo $fila['nombres']; ?></td>
                                <td><?php echo $fila['apellidos']; ?></td>
                                <td><?php echo $fila['cedula']; ?></td>
                                <td><?php echo $fila['telefono']; ?></td>
                                <td><?php echo $fila['correo_electronico']; ?></td>
                                <td><?php echo $fila['date_creation']; ?></td>
                                <td>
                                    <a href="../../Crud/estudiantes/editar_estudiantes.php?id=<?php echo $fila['id']; ?>"
                                        class="btn btn-sm btn-primary">Editar</a>
                                    <a href="../../Crud/estudiantes/eliminar_estudiantes.php?id=<?php echo $fila['id']; ?>"
                                        class="btn btn-sm btn-danger">Eliminar</a>
                                </td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Manual de Uso - Parte 1 -->
    <div class="modal fade" id="modalInstrucciones1" tabindex="-1" role="dialog"
        aria-labelledby="modalInstruccionesLabel1" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalInstruccionesLabel1">Manual de Uso - Gestión de Estudiantes (1/4)
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <ol>
                        <li><strong>Agregar Estudiantes:</strong> Al presionar el botón 'Agregar estudiantes',
                            se abrirá un formulario para que pueda añadir información. Una vez que todo esté listo, se
                            debe
                            presionar 'Agregar', lo cual redirigirá a la página de las tablas con los datos ya creados.
                        </li>
                    </ol>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"
                        onclick="openNextModal('#modalInstrucciones2')">Siguiente</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Manual de Uso - Parte 2 -->
    <div class="modal fade" id="modalInstrucciones2" tabindex="-1" role="dialog"
        aria-labelledby="modalInstruccionesLabel2" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalInstruccionesLabel2">Manual de Uso - Gestión de Estudiantes (2/4)
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <ol start="2">
                        <li><strong>Editar Estudiante:</strong> Para modificar los datos de un estudiante existente,
                            haz clic en el botón 'Editar' junto al estudiante correspondiente. Esto abrirá un formulario
                            con
                            los datos ya registrados, permitiéndote editarlos en caso de que alguno de los campos esté
                            mal
                            registrado. Una vez hechos los cambios, podrás guardarlos y serás redirigido de nuevo a la
                            pantalla con los datos ya actualizados.
                        </li>
                    </ol>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"
                        onclick="openNextModal('#modalInstrucciones3')">Siguiente</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Manual de Uso - Parte 3 -->
    <div class="modal fade" id="modalInstrucciones3" tabindex="-1" role="dialog"
        aria-labelledby="modalInstruccionesLabel3" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalInstruccionesLabel3">Manual de Uso - Gestión de Estudiantes (3/4)
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <ol start="3">
                        <li><strong>Eliminar Estudiantes:</strong> Para eliminar un estudiante, haz clic en el
                            botón "Eliminar" correspondiente a la fila del estudiante la cual se elimina de inmediato.
                        </li>
                    </ol>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"
                        onclick="openNextModal('#modalInstrucciones4')">Siguiente</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Manual de Uso - Parte 4 -->
    <div class="modal fade" id="modalInstrucciones4" tabindex="-1" role="dialog"
        aria-labelledby="modalInstruccionesLabel4" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalInstruccionesLabel4">Manual de Uso - Gestión de Estudiantes (4/4)
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <ol start="4">
                        <li><strong>Generar Reportes:</strong> Haga clic en el botón "Generar reportes" para crear un
                            documento con toda la información  sobre los estudiantes. Podrá descargar el archivo para
                            su uso
                            posterior.
                        </li>
                    </ol>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <script>
        function openNextModal(nextModalId) {
            $(nextModalId).modal('show');
        }

        function filtrarSolicitudes() {
            var input, filter, table, tr, td, i, txtValue;
            input = document.getElementById("filtroSolicitud");
            filter = input.value.toUpperCase();
            table = document.getElementById("dataTable");
            tr = table.getElementsByTagName("tr");
            for (i = 0; i < tr.length; i++) {
                td = tr[i].getElementsByTagName("td")[3]; // Cambiar el índice a 3 para buscar en la columna de cédula
                if (td) {
                    txtValue = td.textContent || td.innerText;
                    if (txtValue.toUpperCase().indexOf(filter) > -1) {
                        tr[i].style.display = "";
                    } else {
                        tr[i].style.display = "none";
                    }
                }
            }
        }
    </script>
</body>

</html>
