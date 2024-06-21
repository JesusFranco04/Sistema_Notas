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

</body>

</html>