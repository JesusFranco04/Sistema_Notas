<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title> Administrador</title>

    <!-- Custom fonts for this template-->

    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">
    <!-- Custom styles for this template-->
    <link href="css/sb-admin-2.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" />


</head>

<body id="page-top">

        <?php include_once('nabvar.php') ?>

                <div class="container-fluid">
                    <div class="row">
                        <div class="container">
                            <h1 class="mt-5 text-center">Tabla Solicitudes Administradores</h1>
                            <div class="mb-5 mt-5">
                                <input type="text" class="form-control" id="filtroSolicitud"
                                    placeholder="Filtrar Solicitudes a traves de su Cedula"
                                    onkeyup="filtrarSolicitudes()" />
                            </div>
                            
                            <?php
                            include '../Sistema_Notas/Crud/solicitud.php';
                            ?>
                        </div>
                    </div>
                    <!-- Modal Actualizar -->

                    <div class="modal fade" id="modalActualizar" tabindex="-1" aria-labelledby="modalActualizarLabel"
                        aria-hidden="true">
                        <div class="modal-dialog" style="max-width: 80%;">
                            <!-- Ajuste del ancho de la modal -->
                            <div class="modal-content bg-success">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="modalActualizarLabel">
                                        Actualizar Solicitud
                                    </h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>

                                <div class="modal-body bg-info text-center">
                                    <form action="#" method="post" id="formActualizar">
                                        <div class="row mb-3">
                                            <div class="col-md-4">
                                                <label for="cedula" class="form-label">Cédula</label>
                                                <input type="text" class="form-control" id="cedula" name="cedula" />
                                            </div>
                                            <div class="col-md-4">
                                                <label for="telefono" class="form-label">Teléfono</label>
                                                <input type="text" class="form-control" id="telefono" name="telefono"
                                                    oninput="validarTelefono(this)" />
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-4">
                                                <label for="nombres" class="form-label">Nombres</label>
                                                <input type="text" class="form-control" id="nombres" name="nombres" />
                                            </div>
                                            <div class="col-md-4">
                                                <label for="apellidos" class="form-label">Apellidos</label>
                                                <input type="text" class="form-control" id="apellidos"
                                                    name="apellidos" />
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-4">
                                                <label for="correo_electronico" class="form-label">Correo
                                                    Electrónico</label>
                                                <input type="email" class="form-control" id="correo_electronico"
                                                    name="correo_electronico" />
                                            </div>
                                            <div class="col-md-4">
                                                <label for="edad" class="form-label">Edad</label>
                                                <input type="number" class="form-control" id="edad" name="edad" />
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-4">
                                                <label for="fecha_nacimiento" class="form-label">Fecha de
                                                    Nacimiento</label>
                                                <input type="date" class="form-control" id="fecha_nacimiento"
                                                    name="fecha_nacimiento" />
                                            </div>
                                            <div class="col-md-4">
                                                <label for="genero" class="form-label">Género</label>
                                                <select class="form-control" id="genero" name="genero" required>
                                                    <option value="">Selecciona Género</option>
                                                    <option value="hombre">Hombre</option>
                                                    <option value="mujer">Mujer</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-4">
                                                <label for="direccion" class="form-label">Dirección</label>
                                                <input type="text" class="form-control" id="direccion"
                                                    name="direccion" />
                                            </div>
                                            <div class="col-md-4">
                                                <label for="sexo" class="form-label ">Sexo</label>
                                                <div>
                                                    <input type="radio" id="sexo_m" name="sexo" value="masculino"
                                                        required>
                                                    <label for="sexo_m">Masculino</label>
                                                    <input type="radio" id="sexo_f" name="sexo" value="femenino"
                                                        required>
                                                    <label for="sexo_f">Femenino</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-4">
                                                <label for="ciudad" class="form-label">Ciudad</label>
                                                <select class="form-control" id="ciudad" name="ciudad" required>
                                                    <option value="">Selecciona Ciudad</option>
                                                    <option value="ciudad1">Ciudad 1</option>
                                                    <option value="ciudad2">Ciudad 2</option>
                                                    <option value="ciudad3">Ciudad 3</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <label for="rol" class="form-label">Rol</label>
                                                <select class="form-control" id="rol" name="rol" required>
                                                    <option value="">Selecciona Rol</option>
                                                    <option value="administrador">Administrador</option>
                                                    <option value="profesor">Profesor</option>
                                                    <option value="padre_familia">Padre de Familia</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-4">
                                                <label for="foto" class="form-label">Foto</label>
                                                <div class="input-group">
                                                    <input type="file" class="form-control" id="foto" name="foto">
                                                    <label class="input-group-btn">
                                                        <span class="btn btn-secondary">Subir Foto</span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-4">
                                                <label for="generar_contrasena" class="form-label">Generar
                                                    Contraseña</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control" id="generar_contrasena"
                                                        name="generar_contrasena" readonly />
                                                    <button type="button" class="btn btn-secondary" id="generarBtn"
                                                        onclick="generarContrasena()">Generar</button>
                                                </div>
                                            </div>
                                        </div>
                                        <input type="hidden" id="id" name="id" value="">
                                        <div class="modal-footer justify-content-center">
                                            <button type="button" class="btn btn-danger"
                                                data-dismiss="modal">Cerrar</button>
                                            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                                        </div>
                                    </form>
                                </div>

                                <style>
                                .input-group-btn {
                                    display: flex;
                                    align-items: center;
                                }

                                .input-group-btn .btn {
                                    margin-left: 10px;
                                }
                                </style>

                                <a class="scroll-to-top rounded" href="#page-top">
                                    <i class="fas fa-angle-up"></i>
                                </a>

                                <!-- Salir seccion-->
                                <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog"
                                    aria-labelledby="exampleModalLabel" aria-hidden="true">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="exampleModalLabel">Seguro que quieres salir?
                                                </h5>
                                                <button class="close" type="button" data-dismiss="modal"
                                                    aria-label="Close">
                                                    <span aria-hidden="true">×</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">Seleccionar "salir" si quieres cerrar sesion.</div>
                                            <div class="modal-footer">
                                                <button class="btn btn-secondary" type="button"
                                                    data-dismiss="modal">Cancelar</button>
                                                <a class="btn btn-primary" href="login.html">salir</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>

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
                                $(document).ready(function() {
                                    $('#modalActualizar').on('show.bs.modal', function(event) {
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
                                $(document).ready(function() {
                                    $('#modalActualizar').on('show.bs.modal', function(event) {
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

                                function validarTelefono(input) {
                                    // Obtener el valor ingresado en el campo de entrada
                                    var telefono = input.value;

                                    // Eliminar cualquier carácter que no sea un número
                                    telefono = telefono.replace(/\D/g, '');

                                    // Limitar el número de dígitos a 10
                                    telefono = telefono.slice(0, 10);

                                    // Actualizar el valor del campo de entrada con los dígitos válidos
                                    input.value = telefono;
                                }

                                function generarContrasena() {
                                    var chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
                                    var passwordLength = 8;
                                    var password = "";

                                    do {
                                        password = "";
                                        for (var i = 0; i < passwordLength; i++) {
                                            var randomNumber = Math.floor(Math.random() * chars.length);
                                            password += chars.substring(randomNumber, randomNumber + 1);
                                        }
                                    } while (generatedPasswords.has(password));

                                    generatedPasswords.add(password);

                                    document.getElementById("generar_contrasena").value = password;
                                    document.getElementById("generarBtn").disabled = true;
                                }
                                </script>
                                <script src="js/sb-admin-2.min.js"></script>
                                <script src="vendor/chart.js/Chart.min.js"></script>
                                <script src="js/demo/chart-area-demo.js"></script>
                                <script src="js/demo/chart-pie-demo.js"></script>
                                <script src="vendor/jquery/jquery.min.js"></script>
                                <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
                                <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
                                <script src="js/sb-admin-2.min.js"></script>
                                <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
                                <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
</body>

</html>