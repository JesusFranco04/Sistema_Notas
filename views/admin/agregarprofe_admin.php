<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>Agregar Profesor | Sistema de Gestión UEBF</title>
    <link rel="shortcut icon" href="http://localhost/sistema_notas/imagenes/logo.png" type="image/x-icon">
    <!-- Custom fonts for this template-->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet"
        type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">
    <!-- Custom styles for this template-->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">


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
    // Incluye el archivo navbar_admin.php solo una vez desde el mismo directorio
    include_once 'navbar_admin.php';
    ?>


    <div class="container-fluid">
        <div class="row">
            <div class="container">
                <h1 class="mt-5 text-center">Tabla Solicitudes Profesores</h1>
                <div class="mb-5 mt-5">
                    <input type="text" class="form-control" id="filtroSolicitud"
                        placeholder="Filtrar Solicitudes a traves de su Cedula" onkeyup="filtrarSolicitudes()" />
                </div>

                <?php
                include '../../Crud/solicitud.php';
                ?>
            </div>
        </div>

        <div class="modal fade" id="modalActualizar" tabindex="-1" aria-labelledby="modalActualizarLabel"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="formularioModalLabel">Actualizar solicitud</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body">

                        <form action="#" method="post" id="formActualizar">


                            <!-- Nombre Completo -->
                            <div class="form-group">
                                <label for="nombre_completo">Nombre Completo:</label>
                                <input type="text" class="form-control" id="nombres" name="nombres" />
                            </div>

                            <!-- Apellido Completo -->
                            <div class="form-group">
                                <label for="apellido_completo">Apellido Completo:</label>
                                <input type="text" class="form-control" id="apellidos" name="apellidos" />
                            </div>

                            <!-- Número de Identificación -->
                            <div class="form-group">
                                <label for="numero_identificacion">Número de
                                    identificacion:</label>
                                <input type="text" class="form-control" id="cedula" name="cedula" />
                            </div>

                            <!-- Fecha de Nacimiento -->
                            <div class="form-group">
                                <label for="fecha_nacimiento">Fecha de Nacimiento:</label>
                                <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento"
                                    required>
                            </div>

                            <!-- Sexo -->
                            <div class="form-group">
                                <label>Genero:</label><br>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" id="hombre" name="sexo" value="hombre"
                                        required>
                                    <label class="form-check-label" for="hombre">Masculino</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" id="mujer" name="sexo" value="mujer">
                                    <label class="form-check-label" for="mujer">Femenino</label>
                                </div>
                            </div>

                            <!-- Dirección -->
                            <div class="form-group">
                                <label for="direccion">Dirección:</label>
                                <input type="text" class="form-control" id="direccion" name="direccion" required>
                            </div>

                            <!-- Celular -->
                            <div class="form-group">
                                <label for="celular">Telefono:</label>
                                <input type="text" class="form-control" id="telefono" name="telefono"
                                    oninput="validarTelefono(this)" />
                            </div>

                            <!-- Correo Electrónico -->
                            <div class="form-group">
                                <label for="correo_electronico">Correo Electrónico:</label>
                                <input type="email" class="form-control" id="correo_electronico"
                                    name="correo_electronico" />
                            </div>

                            <!-- Discapacidad -->
                            <div class="form-group">
                                <label>Discapacidad:</label><br>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" id="si" name="discapacidad" value="si"
                                        required>
                                    <label class="form-check-label" for="si">Sí</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" id="no" name="discapacidad" value="no">
                                    <label class="form-check-label" for="no">No</label>
                                </div>
                            </div>
                            <div class="col-md-15">
                                <label for="rol" class="form-label">Rol</label>
                                <select class="form-control" id="rol" name="rol" required>
                                    <option value="">Selecciona Rol</option>
                                    <option value="administrador">Administrador</option>
                                    <option value="profesor">Profesor</option>
                                    <option value="padre_familia">Padre de Familia</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="generar_contrasena">Generar Contraseña:</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="input-caja" placeholder="Ingrese texto"
                                        aria-label="Caja de texto" aria-describedby="button-generate" disabled>
                                    <div class="input-group-append">
                                        <button class="btn btn-primary" type="button" id="button-generate"
                                            onclick="generarClave()">Generar Clave</button>
                                    </div>
                                </div>
                            </div>

                            <!-- Seleccionar Archivo -->
                            <div class="form-group">
                                <label for="archivo">Selecciona un archivo:</label>
                                <input type="file" class="form-control-file" id="archivo" name="archivo"
                                    onchange="mostrarInfoArchivo()">
                                <div id="info-archivo"></div>
                            </div>

                            <div class="modal-footer justify-content-center">
                                <button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
                                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>



        <script src="http://localhost/sistema_notas/vendor/jquery/jquery.min.js"></script>
        <script src="http://localhost/sistema_notas/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
        <script src="http://localhost/sistema_notas/js/sb-admin-2.min.js"></script>
        <script>
            document.getElementById('sidebarToggle').addEventListener('click', function () {
                document.getElementById('accordionSidebar').classList.toggle('collapsed');
            });
        </script>
        <script>
            function generarClave() {
                const caracteres = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
                let clave = '';
                for (let i = 0; i < 12; i++) {
                    const randomIndex = Math.floor(Math.random() * caracteres.length);
                    clave += caracteres[randomIndex];
                }
                const inputCaja = document.getElementById('input-caja');
                inputCaja.value = clave;
                inputCaja.disabled = true;

                document.getElementById('button-generate').disabled = true;
            }
        </script>
        <script>
            function mostrarInfoArchivo() {
                const input = document.getElementById('archivo');
                const infoArchivo = document.getElementById('info-archivo');


                if (input.files.length > 0) {
                    const archivo = input.files[0];
                    const tamaño = archivo.size / 1024;
                    const tipo = archivo.type || 'Tipo desconocido';


                    infoArchivo.innerHTML = `
                    <p><strong>Nombre:</strong> ${archivo.name}</p>
                    <p><strong>Tipo:</strong> ${tipo}</p>
                    <p><strong>Tamaño:</strong> ${tamaño.toFixed(2)} KB</p>
                `;
                } else {

                    infoArchivo.innerHTML = '';
                }
            }
        </script>
        <script>
            function filtrarSolicitudes() {
                var input = document.getElementById("filtroSolicitud");
                var filter = input.value.toUpperCase();
                var table = document.getElementsByTagName("table")[0];
                var rows = table.getElementsByTagName("tr");

                for (var i = 1; i < rows.length; i++) {
                    var cells = rows[i].getElementsByTagName("td");
                    var cedulaCell = cells[1];

                    if (cedulaCell) {
                        var value = cedulaCell.textContent || cedulaCell.innerText;
                        if (value.toUpperCase().indexOf(filter) > -1) {
                            rows[i].style.display = "";
                        } else {
                            rows[i].style.display = "none";
                        }
                    }
                }
            }
        </script>
        <script>
            $(document).ready(function () {
                $('#modalActualizar').on('show.bs.modal', function (event) {
                    var button = $(event.relatedTarget);
                    var cedula = button.data('cedula');
                    var telefono = button.data('telefono');
                    var nombres = button.data('nombres');
                    var apellidos = button.data('apellidos');
                    var correo = button.data('correo');
                    var rol = button.data('rol');

                    var modal = $(this);
                    modal.find('#cedula').val(cedula);
                    modal.find('#telefono').val(telefono);
                    modal.find('#nombres').val(nombres);
                    modal.find('#apellidos').val(apellidos);
                    modal.find('#correo_electronico').val(correo);
                    modal.find('#rol').val(rol);
                });
            });
        </script>
        <script>
            $(document).ready(function () {
                $('#modalActualizar').on('show.bs.modal', function (event) {
                    var button = $(event.relatedTarget);
                    var id = button.data('id');
                    var cedula = button.data('cedula');
                    var telefono = button.data('telefono');
                    var nombres = button.data('nombres');
                    var apellidos = button.data('apellidos');
                    var correo = button.data('correo');
                    var rol = button.data('rol');

                    var modal = $(this);
                    modal.find('#cedula').val(cedula);
                    modal.find('#telefono').val(telefono);
                    modal.find('#nombres').val(nombres);
                    modal.find('#apellidos').val(apellidos);
                    modal.find('#correo_electronico').val(correo);
                    modal.find('#rol').val(rol);
                    modal.find('#id').val(id);
                });
            });

            function cargarDatos(id) {
                var cedula = document.getElementById('cedula');
                var telefono = document.getElementById('telefono');
                var nombres = document.getElementById('nombres');
                var apellidos = document.getElementById('apellidos');
                var correo = document.getElementById('correo_electronico');
                var rol = document.getElementById('rol');

                var form = document.getElementById('formActualizar');


                // Asignar el valor del ID al campo oculto
                form.id.value = id;

                // Asignar los demás valores a los campos del formulario
                var botonEditar = document.querySelector('button[data-id="' + id + '"]');
                cedula.value = botonEditar.getAttribute('data-cedula');
                telefono.value = botonEditar.getAttribute('data-telefono');
                nombres.value = botonEditar.getAttribute('data-nombres');
                apellidos.value = botonEditar.getAttribute('data-apellidos');
                correo.value = botonEditar.getAttribute('data-correo');
                rol.value = botonEditar.getAttribute('data-rol');
            }

        </body >

</html >