<?php
// Establecer la zona horaria a Ecuador
date_default_timezone_set('America/Guayaquil');

// Verificar si la sesión no está ya iniciada antes de llamar a session_start()
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar si las variables de sesión están definidas
if (!isset($_SESSION["cedula"]) || !isset($_SESSION["rol"])) {
    echo "La sesión ha caducado o no se ha iniciado correctamente.";
    exit;
}

if (!isset($_SESSION["fecha_ingreso"])) {
    // Guardar la fecha y hora de inicio de sesión en una variable de sesión
    $_SESSION["fecha_ingreso"] = date('Y-m-d H:i:s');
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sección de Curso - Steppers</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
    body {
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
    </style>
</head>

<body>
    <div class="container">
        <div class="stepper">
            <!-- Pasos del formulario -->
            <!-- Paso 1: Nivel -->
            <div class="step active">
                <div class="step-number">1</div>
                <div class="step-text">Nivel</div>
            </div>

            <!-- Paso 2: Subnivel -->
            <div class="step">
                <div class="step-number">2</div>
                <div class="step-text">Subnivel</div>
            </div>

            <!-- Paso 3: Paralelo -->
            <div class="step">
                <div class="step-number">3</div>
                <div class="step-text">Paralelo</div>
            </div>

            <!-- Paso 4: Especialidad -->
            <div class="step">
                <div class="step-number">4</div>
                <div class="step-text">Especialidad</div>
            </div>

            <!-- Paso 5: Curso -->
            <div class="step">
                <div class="step-number">5</div>
                <div class="step-text">Curso</div>
            </div>
        </div>

        <!-- Formulario -->
        <form id="stepperForm" method="POST" action="guardar_curso.php">
            <!-- Paso 1: Nivel -->
            <div class="form-section active" id="step1">
                <h3 class="mb-4">Paso 1: Nivel</h3>
                <div class="form-group">
                    <label for="nivel">Nombre del Nivel <span class="text-danger">*</span>:</label>
                    <input type="text" class="form-control" id="nivel" name="nivel" required maxlength="50">
                    <div class="invalid-feedback">Por favor, ingrese el nivel (máximo 50 caracteres).</div>
                </div>
                <div class="form-group">
                    <label for="estado1">Estado <span class="text-danger">*</span>:</label>
                    <select class="form-control" id="estado1" name="estado1" required>
                        <option value="">Seleccione...</option>
                        <option value="A">Activo</option>
                        <option value="I">Inactivo</option>
                    </select>
                    <div class="invalid-feedback">Seleccione el estado del nivel.</div>
                </div>
                <div class="form-group">
                    <label for="usuario_ingreso1">Usuario que ingresa el registro:</label>
                    <input type="text" class="form-control" id="usuario_ingreso1" name="usuario_ingreso1"
                        value="<?php echo $_SESSION['cedula'] . ' ' . $_SESSION['rol']; ?>" readonly>
                </div>
                <div class="form-group">
                    <label for="fecha_ingreso1">Fecha y hora de ingreso:</label>
                    <input type="text" class="form-control" id="fecha_ingreso1" name="fecha_ingreso1"
                        value="<?php echo date('Y-m-d H:i:s'); ?>" readonly>
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

                <!-- Botones de navegación -->
                <div class="btn-container">
                    <button type="button" class="btn btn-primary next">Siguiente</button>
                </div>
            </div>

            <!-- Paso 2: Subnivel -->
            <div class="form-section" id="step2">
                <h3 class="mb-4">Paso 2: Subnivel</h3>
                <div class="form-group">
                    <label for="subnivel">Nombre del Subnivel<span class="text-danger">*</span>:</label>
                    <input type="text" class="form-control" id="subnivel" name="subnivel" required maxlength="50">
                    <div class="invalid-feedback">Ingrese el Subnivel, máximo 50 caracteres.</div>
                </div>
                <div class="form-group">
                    <label for="abreviatura">Abreviatura <span class="text-danger">*</span>:</label>
                    <input type="text" class="form-control" id="abreviatura" name="abreviatura" required maxlength="3">
                    <div class="invalid-feedback">Ingrese la Abreviatura, máximo 3 caracteres.</div>
                </div>
                <div class="form-group">
                    <label for="estado1">Estado <span class="text-danger">*</span>:</label>
                    <select class="form-control" id="estado1" name="estado1" required>
                        <option value="">Seleccione...</option>
                        <option value="A">Activo</option>
                        <option value="I">Inactivo</option>
                    </select>
                    <div class="invalid-feedback">Seleccione el estado del nivel.</div>
                </div>
                <div class="form-group">
                    <label for="usuario_ingreso1">Usuario que ingresa el registro:</label>
                    <input type="text" class="form-control" id="usuario_ingreso1" name="usuario_ingreso1"
                        value="<?php echo $_SESSION['cedula'] . ' ' . $_SESSION['rol']; ?>" readonly>
                </div>
                <div class="form-group">
                    <label for="fecha_ingreso1">Fecha y hora de ingreso:</label>
                    <input type="text" class="form-control" id="fecha_ingreso1" name="fecha_ingreso1"
                        value="<?php echo date('Y-m-d H:i:s'); ?>" readonly>
                </div>

                <strong style="display: block; border-bottom: 1px solid #999; margin-bottom: 10px;"></strong>

                <!-- Instrucciones adicionales -->
                <p class="mb-4">
                    <strong style="color: #666;">Nota:</strong><br>
                    <strong style="color: #666;">&#8226;</strong>
                    <span style="font-size: 0.9em; color: #777; margin-left: 10px;">En el campo de <strong>nombre del
                            subnivel</strong>, ingrese el nombre completo del subnivel educativo, por ejemplo:
                        "Educación General Básica", "Bachillerato Técnico Industrial".</span><br>
                    <strong style="color: #666;">&#8226;</strong>
                    <span style="font-size: 0.9em; color: #777; margin-left: 10px;">En el campo de
                        <strong>abreviatura</strong>, escriba la abreviatura correspondiente, por ejemplo: "EGB",
                        "BTI".</span>
                </p>

                <!-- Botones de navegación -->
                <div class="btn-container">
                    <button type="button" class="btn btn-secondary prev">Anterior</button>
                    <button type="button" class="btn btn-primary next">Siguiente</button>
                </div>
            </div>




            <!-- Paso 3: Paralelo -->
            <div class="form-section" id="step3">
                <h3 class="mb-4">Paso 3: Paralelo</h3>
                <div class="form-group">
                    <label for="paralelo">Nombre del Paralelo<span class="text-danger">*</span>:</label>
                    <input type="text" class="form-control" id="paralelo" name="paralelo" required maxlength="3">
                    <div class="invalid-feedback">Ingrese el Paralelo, máximo 2 caracteres.</div>
                </div>

                <div class="form-group">
                    <label for="estado1">Estado <span class="text-danger">*</span>:</label>
                    <select class="form-control" id="estado1" name="estado1" required>
                        <option value="">Seleccione...</option>
                        <option value="A">Activo</option>
                        <option value="I">Inactivo</option>
                    </select>
                    <div class="invalid-feedback">Seleccione el estado del nivel.</div>
                </div>
                <div class="form-group">
                    <label for="usuario_ingreso1">Usuario que ingresa el registro:</label>
                    <input type="text" class="form-control" id="usuario_ingreso1" name="usuario_ingreso1"
                        value="<?php echo $_SESSION['cedula'] . ' ' . $_SESSION['rol']; ?>" readonly>
                </div>
                <div class="form-group">
                    <label for="fecha_ingreso1">Fecha y hora de ingreso:</label>
                    <input type="text" class="form-control" id="fecha_ingreso1" name="fecha_ingreso1"
                        value="<?php echo date('Y-m-d H:i:s'); ?>" readonly>
                </div>

                <strong style="display: block; border-bottom: 1px solid #999; margin-bottom: 10px;"></strong>

                <!-- Instrucciones adicionales -->
                <p class="mb-4">
                    <strong style="color: #666;">Nota:</strong><br>
                    <strong style="color: #666;">&#8226;</strong>
                    <span style="font-size: 0.9em; color: #777; margin-left: 10px;">En el campo de
                        <strong>"Nombre del Paralelo"</strong>, ingrese el paralelo, por ejemplo:
                        "A", "B", "C".</span>
                </p>

                <!-- Botones de navegación -->
                <div class="btn-container">
                    <button type="button" class="btn btn-secondary prev">Anterior</button>
                    <button type="button" class="btn btn-primary next">Siguiente</button>
                </div>
            </div>




            <!-- Paso 4: Especialidad -->
            <div class="form-section" id="step4">
                <h3 class="mb-4">Paso 4: Especialidad</h3>
                <div class="form-group">
                    <label for="especialidad">Nombre de la especialidad<span class="text-danger">*</span>:</label>
                    <input type="text" class="form-control" id="especialidad" name="especialidad" required maxlength="50">
                    <div class="invalid-feedback">Ingrese la Especialidad, máximo 50 caracteres.</div>
                </div>
                <div class="form-group">
                    <label for="estado1">Estado <span class="text-danger">*</span>:</label>
                    <select class="form-control" id="estado1" name="estado1" required>
                        <option value="">Seleccione...</option>
                        <option value="A">Activo</option>
                        <option value="I">Inactivo</option>
                    </select>
                    <div class="invalid-feedback">Seleccione el estado del nivel.</div>
                </div>
                <div class="form-group">
                    <label for="usuario_ingreso1">Usuario que ingresa el registro:</label>
                    <input type="text" class="form-control" id="usuario_ingreso1" name="usuario_ingreso1"
                        value="<?php echo $_SESSION['cedula'] . ' ' . $_SESSION['rol']; ?>" readonly>
                </div>
                <div class="form-group">
                    <label for="fecha_ingreso1">Fecha y hora de ingreso:</label>
                    <input type="text" class="form-control" id="fecha_ingreso1" name="fecha_ingreso1"
                        value="<?php echo date('Y-m-d H:i:s'); ?>" readonly>
                </div>

                <strong style="display: block; border-bottom: 1px solid #999; margin-bottom: 10px;"></strong>

                <!-- Instrucciones adicionales -->
                <p class="mb-4">
                    <strong style="color: #666;">Nota:</strong><br>
                    <strong style="color: #666;">&#8226;</strong>
                    <span style="font-size: 0.9em; color: #777; margin-left: 10px;">En el campo de
                        <strong>"Nombre del Paralelo"</strong>, ingrese el paralelo, por ejemplo:
                        "A", "B", "C".</span>
                </p>

                <!-- Botones de navegación -->
                <div class="btn-container">
                    <button type="button" class="btn btn-secondary prev">Anterior</button>
                    <button type="button" class="btn btn-primary next">Siguiente</button>
                </div>
            </div>



            <!-- Paso 5: Curso -->
            <div class="form-section" id="step5">
                <h3 class="mb-4">Paso 5: Curso</h3>
                <div class="form-group">
                    <label for="curso">Nombre <span class="text-danger">*</span>:</label>
                    <input type="text" class="form-control" id="curso" name="curso" required maxlength="50">
                    <div class="invalid-feedback">Ingrese el Curso, máximo 50 caracteres.</div>
                </div>
                <div class="form-group">
                    <label for="estado5">Estado <span class="text-danger">*</span>:</label>
                    <select class="form-control" id="estado5" name="estado5" required>
                        <option value="">Seleccione...</option>
                        <option value="A">Activo</option>
                        <option value="I">Inactivo</option>
                    </select>
                    <div class="invalid-feedback">Seleccione el Estado.</div>
                </div>
                <div class="form-group">
                    <label for="usuario_ingreso5">Usuario que Ingresa el Registro:</label>
                    <input type="text" class="form-control" id="usuario_ingreso5" name="usuario_ingreso5" value="Admin"
                        readonly>
                </div>
                <div class="form-group">
                    <label for="fecha_ingreso5">Fecha y Hora de Ingreso:</label>
                    <input type="text" class="form-control" id="fecha_ingreso5" name="fecha_ingreso5"
                        value="<?php echo date('Y-m-d H:i:s'); ?>" readonly>
                </div>

                <!-- Botones de navegación -->
                <div class="btn-container">
                    <button type="button" class="btn btn-secondary prev">Anterior</button>
                    <button type="submit" class="btn btn-success">Guardar</button>
                </div>
            </div>
        </form>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
    $(document).ready(function() {
        var currentStep = 0;
        var $stepperForm = $('#stepperForm');
        var $formSections = $stepperForm.find('.form-section');

        // Ocultar todos los formularios excepto el primero
        $formSections.slice(1).removeClass('active');

        // Botón Siguiente
        $stepperForm.find('.next').click(function() {
            if (currentStep < $formSections.length - 1) {
                if (validateForm(currentStep)) {
                    $formSections.eq(currentStep).removeClass('active');
                    $formSections.eq(++currentStep).addClass('active');
                    updateStepper();
                }
            }
        });

        // Botón Anterior
        $stepperForm.find('.prev').click(function() {
            if (currentStep > 0) {
                $formSections.eq(currentStep).removeClass('active');
                $formSections.eq(--currentStep).addClass('active');
                updateStepper();
            }
        });

        // Función para validar el formulario actual
        function validateForm(step) {
            var $currentSection = $formSections.eq(step);
            var isValid = true;

            // Validar campos requeridos en la sección actual
            $currentSection.find('input, select').each(function() {
                if ($(this).prop('required') && $(this).val() === '') {
                    $(this).addClass('is-invalid');
                    isValid = false;
                } else {
                    $(this).removeClass('is-invalid');
                }
            });

            return isValid;
        }

        // Función para actualizar el indicador de paso activo
        function updateStepper() {
            $stepperForm.find('.step').removeClass('active');
            $stepperForm.find('.step').eq(currentStep).addClass('active');
        }
    });
    </script>
</body>

</html>