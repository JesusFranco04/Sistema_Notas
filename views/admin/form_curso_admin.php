<?php
// Establecer la zona horaria a Ecuador
date_default_timezone_set('America/Guayaquil');
session_start();

if (!isset($_SESSION["fecha_ingreso"])) {
    // Guardar la fecha y hora de inicio de sesión en una variable de sesión
    $_SESSION["fecha_ingreso"] = date('Y-m-d H:i:s');
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>Formulario De Registro De Cursos | Sistema De Gestión UEBF</title>
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
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Estilos personalizados -->
    <style>
    /* Aquí va tu código CSS */
    body,
    html {
        font-family: Arial, sans-serif;
        background-color: #f8f9fa;
        margin: 0;
        padding: 0;
    }

    .container {
        margin-top: 50px;
        background-color: #ffffff;
        padding: 30px;
        border-radius: 8px;
        box-shadow: 0px 0px 20px rgba(0, 0, 0, 0.1);
    }

    .stepper {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
    }

    .step {
        flex: 1;
        text-align: center;
        position: relative;
    }

    .step::after {
        content: '';
        position: absolute;
        top: 12px;
        left: calc(100% + 5px);
        width: 50%;
        height: 1px;
        background-color: #ccc;
    }

    .step:last-child::after {
        display: none;
    }

    .step.active {
        color: #dc3545;
        /* Rojo oscuro bonito */
    }

    .step.completed {
        color: #28a745;
    }

    .step-number {
        width: 30px;
        height: 30px;
        line-height: 30px;
        border: 2px solid #ccc;
        border-radius: 50%;
        display: inline-block;
        font-weight: bold;
        background-color: #fff;
        position: relative;
        z-index: 2;
    }

    .step-text {
        margin-top: 10px;
    }

    .form-section {
        display: none;
    }

    .form-section.active {
        display: block;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .btn-container {
        text-align: right;
    }

    .btn {
        padding: 10px 20px;
        font-size: 16px;
    }

    .is-invalid {
        border-color: #dc3545 !important;
        /* Rojo oscuro bonito para campos inválidos */
    }

    .invalid-feedback {
        color: #dc3545;
        /* Rojo oscuro bonito para mensaje de error */
        font-size: 14px;
    }

    .is-invalid {
        border-color: #dc3545 !important;
        transition: border-color 0.2s ease-in-out;
    }
    </style>
</head>

<body>
    <?php
    // Incluye el archivo navbar_admin.php solo una vez desde el mismo directorio
    include_once 'navbar_admin.php';
    ?>

    <div class="container">
        <div class="stepper">
            <!-- Paso 1: Profesor -->
            <div class="step active">
                <div class="step-number">1</div>
                <div class="step-text">Profesor</div>
            </div>

            <!-- Paso 2: Materia -->
            <div class="step">
                <div class="step-number">2</div>
                <div class="step-text">Materia</div>
            </div>

            <!-- Paso 3: Nivel -->
            <div class="step">
                <div class="step-number">3</div>
                <div class="step-text">Nivel</div>
            </div>

            <!-- Paso 4: Subnivel -->
            <div class="step">
                <div class="step-number">4</div>
                <div class="step-text">Subnivel</div>
            </div>

            <!-- Paso 5: Especialidad -->
            <div class="step">
                <div class="step-number">5</div>
                <div class="step-text">Especialidad</div>
            </div>

            <!-- Paso 6: Jornada -->
            <div class="step">
                <div class="step-number">6</div>
                <div class="step-text">Jornada</div>
            </div>

            <!-- Paso 7: Periodo -->
            <div class="step">
                <div class="step-number">7</div>
                <div class="step-text">Periodo</div>
            </div>
        </div>
    </div>
    <!-- Formulario -->
    <form id="stepperForm" method="POST" action="guardar_curso.php">
        <!-- Paso 1: Profesor -->
        <div class="form-section active" id="step1">
            <h3 class="mb-4">Paso 1: Profesor</h3>
            <div class="form-group">
                <label for="profesor">Profesor <span class="text-danger">*</span>:</label>
                <select class="form-control" id="profesor" name="profesor" required>
                    <option value="">Seleccione...</option>
                    <!-- Opciones de profesores obtenidas dinámicamente -->
                </select>
                <div class="invalid-feedback">Seleccione un profesor.</div>
            </div>
            <!-- Otros campos según sea necesario -->

            <!-- Botones de navegación -->
            <div class="btn-container">
                <button type="button" class="btn btn-primary next">Siguiente</button>
            </div>
        </div>

        <!-- Paso 2: Materia -->
        <div class="form-section" id="step2">
            <h3 class="mb-4">Paso 2: Materia</h3>
            <div class="form-group">
                <label for="materia">Materia <span class="text-danger">*</span>:</label>
                <select class="form-control" id="materia" name="materia" required>
                    <option value="">Seleccione...</option>
                    <!-- Opciones de materias obtenidas dinámicamente -->
                </select>
                <div class="invalid-feedback">Seleccione una materia.</div>
            </div>
            <!-- Otros campos según sea necesario -->

            <!-- Botones de navegación -->
            <div class="btn-container">
                <button type="button" class="btn btn-secondary prev">Anterior</button>
                <button type="button" class="btn btn-primary next">Siguiente</button>
            </div>
        </div>

        <!-- Paso 3: Nivel -->
        <div class="form-section" id="step3">
            <h3 class="mb-4">Paso 3: Nivel</h3>
            <div class="form-group">
                <label for="nivel">Nivel <span class="text-danger">*</span>:</label>
                <select class="form-control" id="nivel" name="nivel" required>
                    <option value="">Seleccione...</option>
                    <!-- Opciones de niveles obtenidas dinámicamente -->
                </select>
                <div class="invalid-feedback">Seleccione un nivel.</div>
            </div>
            <!-- Otros campos según sea necesario -->

            <!-- Botones de navegación -->
            <div class="btn-container">
                <button type="button" class="btn btn-secondary prev">Anterior</button>
                <button type="button" class="btn btn-primary next">Siguiente</button>
            </div>
        </div>

        <!-- Paso 4: Subnivel -->
        <div class="form-section" id="step4">
            <h3 class="mb-4">Paso 4: Subnivel</h3>
            <div class="form-group">
                <label for="subnivel">Subnivel <span class="text-danger">*</span>:</label>
                <select class="form-control" id="subnivel" name="subnivel" required>
                    <option value="">Seleccione...</option>
                    <!-- Opciones de subniveles obtenidas dinámicamente -->
                </select>
                <div class="invalid-feedback">Seleccione un subnivel.</div>
            </div>
            <!-- Otros campos según sea necesario -->

            <!-- Botones de navegación -->
            <div class="btn-container">
                <button type="button" class="btn btn-secondary prev">Anterior</button>
                <button type="button" class="btn btn-primary next">Siguiente</button>
            </div>
        </div>

        <!-- Paso 5: Especialidad -->
        <div class="form-section" id="step5">
            <h3 class="mb-4">Paso 5: Especialidad</h3>
            <div class="form-group">
                <label for="especialidad">Especialidad <span class="text-danger">*</span>:</label>
                <select class="form-control" id="especialidad" name="especialidad" required>
                    <option value="">Seleccione...</option>
                    <!-- Opciones de especialidades obtenidas dinámicamente -->
                </select>
                <div class="invalid-feedback">Seleccione una especialidad.</div>
            </div>
            <!-- Otros campos según sea necesario -->

            <!-- Botones de navegación -->
            <div class="btn-container">
                <button type="button" class="btn btn-secondary prev">Anterior</button>
                <button type="button" class="btn btn-primary next">Siguiente</button>
            </div>
        </div>

        <!-- Paso 6: Jornada -->
        <div class="form-section" id="step6">
            <h3 class="mb-4">Paso 6: Jornada</h3>
            <div class="form-group">
                <label for="jornada">Jornada <span class="text-danger">*</span>:</label>
                <select class="form-control" id="jornada" name="jornada" required>
                    <option value="">Seleccione...</option>
                    <!-- Opciones de jornadas obtenidas dinámicamente -->
                </select>
                <div class="invalid-feedback">Seleccione una jornada.</div>
            </div>
            <!-- Otros campos según sea necesario -->

            <!-- Botones de navegación -->
            <div class="btn-container">
                <button type="button" class="btn btn-secondary prev">Anterior</button>
                <button type="button" class="btn btn-primary next">Siguiente</button>
            </div>
        </div>

        <!-- Paso 7: Periodo -->
        <div class="form-section" id="step7">
            <h3 class="mb-4">Paso 7: Periodo</h3>
            <div class="form-group">
                <label for="periodo">Periodo <span class="text-danger">*</span>:</label>
                <select class="form-control" id="periodo" name="periodo" required>
                    <option value="">Seleccione...</option>
                    <!-- Opciones de periodos obtenidas dinámicamente -->
                </select>
                <div class="invalid-feedback">Seleccione un periodo.</div>
            </div>
            <!-- Otros campos según sea necesario -->

            <!-- Fecha de ingreso -->
            <input type="hidden" id="fecha_ingreso" name="fecha_ingreso" value="<?php echo date('Y-m-d'); ?>">

            <!-- Resumen -->
            <h4>Resumen del Registro</h4>
            <ul>
                <li><strong>Profesor:</strong> <span id="resumen_profesor"></span></li>
                <li><strong>Materia:</strong> <span id="resumen_materia"></span></li>
                <li><strong>Nivel:</strong> <span id="resumen_nivel"></span></li>
                <li><strong>Subnivel:</strong> <span id="resumen_subnivel"></span></li>
                <li><strong>Especialidad:</strong> <span id="resumen_especialidad"></span></li>
                <li><strong>Jornada:</strong> <span id="resumen_jornada"></span></li>
                <li><strong>Periodo:</strong> <span id="resumen_periodo"></span></li>
            </ul>

            <!-- Botones de navegación -->
            <div class="btn-container">
                <button type="button" class="btn btn-secondary prev">Anterior</button>
                <button type="submit" class="btn btn-success">Guardar</button>
            </div>
        </div>
    </form>


    <!-- Incluye jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <!-- Incluye Bootstrap JS -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <!-- Incluye SweetAlert2 para alertas -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
    <!-- Script personalizado -->
    <script>
    $(document).ready(function() {
        var currentStep = 0;

        // Mostrar el paso actual
        function showStep(step) {
            $('.form-section').removeClass('active');
            $('#step' + (step + 1)).addClass('active');
            updateStepper(step);
        }

        // Actualizar el stepper visual
        function updateStepper(step) {
            $('.step').each(function(index) {
                if (index === step) {
                    $(this).addClass('active').removeClass('completed');
                } else if (index < step) {
                    $(this).addClass('completed').removeClass('active');
                } else {
                    $(this).removeClass('active completed');
                }
            });
        }

        // Función para validar los campos de cada paso
        function validateStep(step) {
            var isValid = true;
            $('#step' + (step + 1) + ' input, #step' + (step + 1) + ' select').each(function() {
                if (!this.checkValidity()) {
                    $(this).addClass('is-invalid');
                    isValid = false;
                } else {
                    $(this).removeClass('is-invalid');
                }
            });
            return isValid;
        }

        // Botón "Siguiente"
        $('.next').click(function() {
            if (validateStep(currentStep)) {
                currentStep++;
                showStep(currentStep);
            }
        });

        // Botón "Anterior"
        $('.prev').click(function() {
            currentStep--;
            showStep(currentStep);
        });

        // Mostrar el primer paso al cargar la página
        showStep(currentStep);

       // Actualizar el resumen al mostrar el paso 5
       $('#step5').on('shown.bs.tab', function() {
            $('#resumen_profesor').text($('#profesor').val());
            $('#resumen_materia').text($('#materia').val());
            $('#resumen_nivel').text($('#nivel').val());
            $('#resumen_subnivel').text($('#subnivel').val());
            $('#resumen_paralelo').text($('#paralelo').val());
            $('#resumen_especialidad').text($('#especialidad').val());
            $('#resumen_curso').text($('#curso').val());
            $('#resumen_jornada').text($('#jornada').val());
            $('#resumen_periodo').text($('#periodo').val());
        });

        // Mensaje de confirmación al enviar el formulario
        $('#stepperForm').on('submit', function(event) {
            event.preventDefault();
            Swal.fire({
                title: '¿Estás seguro de enviar el formulario?',
                text: "Revisa los datos antes de enviar.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, enviar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.submit();
                }
            });
        });

        // Navegación entre pasos del formulario
        $('.next').click(function() {
            var nextId = $(this).parents('.form-section').next().attr('id');
            $('[href="#' + nextId + '"]').tab('show');
        });

        $('.prev').click(function() {
            var prevId = $(this).parents('.form-section').prev().attr('id');
            $('[href="#' + prevId + '"]').tab('show');
        });
    });
    </script>
</body>
</html>