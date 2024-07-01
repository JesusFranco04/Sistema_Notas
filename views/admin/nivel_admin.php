<?php
// Establecer la zona horaria a Ecuador
date_default_timezone_set('America/Guayaquil');


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

        <!-- Formulario -->
        <form id="stepperForm" method="POST" action="guardar_curso.php">
            <!-- Paso 1: Nivel -->
            <div class="form-section active" id="step1">
                <h3 class="mb-4">Nivel</h3>
                <div class="form-group">
                    <label for="nivel">Nombre del Nivel <span class="text-danger">*</span>:</label>
                    <input type="text" class="form-control" id="nivel" name="nivel" required maxlength="50">
                    <div class="invalid-feedback">Por favor, ingrese el nivel (máximo 50 caracteres).</div>
                </div>
                <div class="form-group">
                    <label for="fecha_ingreso1">Fecha y hora de ingreso:</label>
                    <input type="text" class="form-control" id="fecha_ingreso1" name="fecha_ingreso1"
                        value="<?php echo $_SESSION["fecha_ingreso"]; ?>" readonly>
                </div>

                <strong style="display: block; border-bottom: 1px solid #999; margin-bottom: 10px;"></strong>

                <!-- Instrucciones adicionales -->
                <p class="mb-4">
                    <strong style="color: #666;">Nota:</strong><br>
                    <strong style="color: #666;">&#8226;</strong>
                    <span style="font-size: 0.9em; color: #777; margin-left: 10px;">En el campo de nivel, ingrese un
                        único nivel educativo desde <strong>"Octavo"</strong> hasta <strong>"Tercero de
                            Bachillerato"</strong> (por ejemplo: "Noveno", "Segundo Bachillerato", etc.).</span><br>
                </p>

                <!-- Botón de guardar -->
                <div class="btn-container">
                <button type="submit" class="btn btn-success">Guardar</button>
                </div>

    <!-- Pie de Página -->
    <footer class="sticky-footer bg-white">
        <div class="container my-auto">
            <div class="copyright text-center my-auto">
                <p>&copy; 2024 Instituto Superior Tecnológico Guayaquil. Desarrollado por Giullia Arias y Carlos
                    Zambrano. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>


    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
    $(document).ready(function() {
        var currentStep = 0;
        var $stepperForm = $('#stepperForm');
        var $formSections = $stepperForm.find('.form-section');

        // Botón Siguiente
        $(".next").click(function() {
            var $step = $(".form-section").eq(currentStep);
            var $nextStep = $(".form-section").eq(currentStep + 1);

            if (validateStep($step)) {
                $step.removeClass("active");
                $nextStep.addClass("active");
                currentStep++;
                updateStepper();
            }
        });

        // Botón Anterior
        $(".previous").click(function() {
            var $step = $(".form-section").eq(currentStep);
            var $prevStep = $(".form-section").eq(currentStep - 1);

            $step.removeClass("active");
            $prevStep.addClass("active");
            currentStep--;
            updateStepper();
        });

        // Función para validar el paso actual
        function validateStep($step) {
            var isValid = true;
            $step.find("input, select").each(function() {
                if (!$(this).prop("disabled") && ($(this).prop("required") && !$(this).val())) {
                    $(this).addClass("is-invalid");
                    isValid = false;
                } else {
                    $(this).removeClass("is-invalid");
                }
            });
            return isValid;
        }

        // Función para actualizar el indicador de pasos
        function updateStepper() {
            $(".step").each(function(index) {
                if (index <= currentStep) {
                    $(this).addClass("completed");
                } else {
                    $(this).removeClass("completed");
                }

                if (index === currentStep) {
                    $(this).addClass("active");
                } else {
                    $(this).removeClass("active");
                }
            });
        }
    });
    </script>
    <!-- Bootstrap core JavaScript-->
    <script src="http://localhost/sistema_notas/vendor/jquery/jquery.min.js"></script>
    <script src="http://localhost/sistema_notas/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <!-- Custom scripts for all pages-->
    <script src="http://localhost/sistema_notas/js/sb-admin-2.min.js"></script>
    <!-- Otros scripts -->
    <script>
    document.getElementById('sidebarToggle').addEventListener('click', function() {
        document.getElementById('accordionSidebar').classList.toggle('collapsed');
    });
    </script>
</body>

</html>