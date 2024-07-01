<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>Tabla de Profesores | Sistema de Gestión UEBF</title>
    <link rel="shortcut icon" href="http://localhost/sistema_notas/imagenes/logo.png" type="image/x-icon">
    <!-- Custom fonts for this template-->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <!-- Custom styles for this template-->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="http://localhost/sistema_notas/css/sb-admin-2.min.css" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <!-- Estilos personalizados -->
    <style>
        .sidebar-heading .collapse-header .bx {
            color: #ff8b97;
        }

        .bg-gradient-primary {
            background-color: #a2000e;
            background-image: none;
        }
    </style>
</head>

<body>
    <?php
    // Incluir el archivo de conexión y verificar la conexión
    include '../../Crud/config.php';

    $sql = "SELECT * FROM profesores";
    $resultado = $conn->query($sql);

    if (!$resultado) {
        die("Error en la consulta: " . $conn->error);
    }
    ?>

    <?php include_once 'navbar_admin.php'; ?>

    <div class="container-fluid">
        <div class="row">
            <div class="container">
                <h1 class="mt-1 text-center text-dark fw-bold">Tabla de Profesores</h1>
                <div class="mb-4 mt-3">
                    <input type="text" class="form-control" id="filtroSolicitud" placeholder="Filtrar por Cédula del Profesor" onkeyup="filtrarSolicitudes()">
                </div>
                <div class="mb-4 mt-3">
                    <div class="row justify-content-start"><div class="row justify-content-start">
                        <div class="col-auto">
                            <a href="../../Crud/profesores/agregar_profe.php" class="btn btn-primary">Agregar Profesor</a>
                        </div>
                        <div class="col-auto">
                            <button type="button" class="btn btn-info" data-toggle="modal" data-target="#modalInstrucciones1">Ver Manual de Uso</button>
                        </div>
                        <div class="col-auto">
                            <a href="reporte_profe.php" class="btn btn-success">Generar reportes</a>
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
                                <th>Rol</th>
                                <th>Contraseña</th>
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
                                    <td><?php echo $fila['rol']; ?></td>
                                    <td><?php echo $fila['contrasena']; ?></td>
                                    <td><?php echo $fila['date_creation']; ?></td>
                                    <td>
                                        <a href="../../Crud/profesores/editar_profe.php?id=<?php echo $fila['id']; ?>" class="btn btn-sm btn-primary">Editar</a>
                                        <a href="../../Crud/profesores/eliminar_profe.php?id=<?php echo $fila['id']; ?>" class="btn btn-sm btn-danger">Eliminar</a>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>

                <!-- Manual de Uso - Parte 1 -->
                <div class="modal fade" id="modalInstrucciones1" tabindex="-1" role="dialog" aria-labelledby="modalInstruccionesLabel1" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="modalInstruccionesLabel1">Manual de Uso - Gestión de Profesores (1/4)</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <ol>
                                    <li><strong>Agregar Profesor:</strong> Al presionar el botón "Agregar Profesor", aparecerá un formulario para crear el profesor. 
                                    Uno de esos botones es para generar una contraseña aleatoria y única que no se repita con los otros perfiles de profesor
                                     creados en la tabla. Una vez que todo esté listo, se debe presionar "Agregar", lo cual redirigirá a la página de las
                                      tablas con los datos ya creados. </li>
                                </ol>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal" onclick="openNextModal('#modalInstrucciones2')">Siguiente</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Manual de Uso - Parte 2 -->
                <div class="modal fade" id="modalInstrucciones2" tabindex="-1" role="dialog" aria-labelledby="modalInstruccionesLabel2" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="modalInstruccionesLabel2">Manual de Uso - Gestión de Profesores (2/4)</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <ol start="2">
                                    <li><strong>Editar Profesor:</strong> Para modificar los datos de un profesor existente, haz clic en el botón 
                                    "Editar" junto al profesor correspondiente. Esto abrirá un formulario con los datos ya registrados, permitiéndote
                                     editarlos en caso de que alguno de los campos esté mal registrado. Una vez hechos los cambios, podrás guardarlos 
                                     y serás redirigido de nuevo a la pantalla con los datos ya actualizados .</li>
                                </ol>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal" onclick="openNextModal('#modalInstrucciones3')">Siguiente</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Manual de Uso - Parte 3 -->
                <div class="modal fade" id="modalInstrucciones3" tabindex="-1" role="dialog" aria-labelledby="modalInstruccionesLabel3" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="modalInstruccionesLabel3">Manual de Uso - Gestión de Profesores (3/4)</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <ol start="3">
                                    <li><strong>Eliminar Profesor:</strong> Para eliminar un profesor, haz clic en el botón "Eliminar" junto al profesor 
                                    correspondiente. Aparecerá un mensaje de confirmación para asegurarte de que deseas eliminar el registro. Si confirmas,
                                     el profesor será eliminado y el registro no aparecerá más en la tabla de profesores.</li>
                                </ol>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal" onclick="openNextModal('#modalInstrucciones4')">Siguiente</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Manual de Uso - Parte 4 -->
                <div class="modal fade" id="modalInstrucciones4" tabindex="-1" role="dialog" aria-labelledby="modalInstruccionesLabel4" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="modalInstruccionesLabel4">Manual de Uso - Gestión de Profesores (4/4)</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <ol start="4">
                                    <li><strong>Generar Reportes:</strong> Para crear un reporte de los datos de los profesores, haz clic en el botón 
                                    "Generar reportes". Se descargará un archivo PDF con la información de todos los profesores registrados.</li>
                                </ol>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script>
        function openNextModal(nextModal) {
            $(nextModal).modal('show');
        }

        function filtrarSolicitudes() {
            var input, filter, table, tr, td, i, txtValue;
            input = document.getElementById("filtroSolicitud");
            filter = input.value.toUpperCase();
            table = document.getElementById("dataTable");
            tr = table.getElementsByTagName("tr");
            for (i = 0; i < tr.length; i++) {
                td = tr[i].getElementsByTagName("td")[3]; // Filtrar por la cuarta columna (Cédula)
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

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.21/js/dataTables.bootstrap4.min.js"></script>
    <script src="http://localhost/sistema_notas/js/sb-admin-2.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#dataTable').DataTable();
        });
    </script>
</body>

</html>
