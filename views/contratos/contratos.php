<?php
// Incluye el archivo de conexión a la base de datos
require_once '../../db/database.php';

// Crea una instancia de la clase Database
$database = new Database();
// Obtiene la conexión PDO a la base de datos
$db = $database->getConnection();

// Consulta SQL para obtener todos los beneficiarios (para el select del formulario)
$query_beneficiarios = "SELECT * FROM beneficiarios ORDER BY apellidos, nombres";
$stmt_beneficiarios = $db->prepare($query_beneficiarios);
$stmt_beneficiarios->execute();
$beneficiarios = $stmt_beneficiarios->fetchAll(PDO::FETCH_ASSOC);

// Verifica la estructura de la tabla contratos (opcional, para debug o desarrollo)
try {
    $query_structure = "DESCRIBE contratos";
    $stmt_structure = $db->prepare($query_structure);
    $stmt_structure->execute();
    $columns = $stmt_structure->fetchAll(PDO::FETCH_ASSOC);

    // Puedes mostrar las columnas para depuración (comentado)
    // echo "<pre>Columnas disponibles en la tabla contratos:";
    // foreach($columns as $col) { echo "\n" . $col['Field']; }
    // echo "</pre>";

} catch (Exception $e) {
    // Maneja el error si ocurre
}

// Consulta SQL para obtener todos los contratos junto con los datos del beneficiario
$query_contratos = "
    SELECT c.*, 
           CONCAT(b.apellidos, ', ', b.nombres) as beneficiario_nombre,
           b.dni
    FROM contratos c
    INNER JOIN beneficiarios b ON c.idbeneficiario = b.idbeneficiario
    ORDER BY c.creado DESC
";
$stmt_contratos = $db->prepare($query_contratos);
$stmt_contratos->execute();
$contratos = $stmt_contratos->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8"> <!-- Codificación de caracteres -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Responsive -->
    <title>Contratos - Sistema de Préstamos</title> <!-- Título de la página -->
    <!-- Bootstrap para estilos -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome para iconos -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../css/contratos.css">
</head>

<body>
    <!-- Barra de navegación superior -->
    <nav class="navbar navbar-expand-lg navbar-custom mb-4">
        <div class="container">
            <!-- Logo y nombre del sistema -->
            <a class="navbar-brand text-white fw-bold" href="dashboard.php">
                <i class="fas fa-handshake me-2"></i>Sistema de Préstamos
            </a>
            <!-- Enlace para volver al dashboard -->
            <div class="navbar-nav ms-auto">
                <a class="nav-link text-white" href="../../index.php">
                    <i class="fas fa-arrow-left me-1"></i> Volver al Dashboard
                </a>
            </div>
        </div>
    </nav>

    <div class="container">
        <!-- Título de bienvenida -->
        <div class="text-center mb-5">
            <h1 class="welcome-title display-4 fw-bold">
                Gestión de Contratos
            </h1>
            <p class="lead text-white-50">
                Administra los contratos de préstamo y su estado
            </p>
        </div>

        <!-- Mensaje de éxito al procesar contrato -->
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>Contrato procesado exitosamente.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Mensaje de error al procesar contrato -->
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>Error al procesar el contrato.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Tarjeta principal con la lista de contratos -->
        <div class="card card-custom">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-file-contract me-2"></i>Lista de Contratos
                </h5>
                <!-- Botón para abrir el modal de nuevo contrato -->
                <button class="btn btn-light btn-custom" data-bs-toggle="modal" data-bs-target="#modalContrato">
                    <i class="fas fa-plus me-2"></i>Nuevo Contrato
                </button>
            </div>
            <div class="card-body">
                <?php if (count($contratos) > 0): ?>
                    <!-- Tabla de contratos -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Beneficiario</th>
                                    <th>DNI</th>
                                    <th>Monto</th>
                                    <th>Cuotas</th>
                                    <th>Tasa</th>
                                    <th>Estado</th>
                                    <th>Fecha</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Recorre cada contrato y muestra sus datos -->
                                <?php foreach ($contratos as $index => $contrato): ?>
                                    <!-- Inicia un ciclo para cada contrato en el arreglo $contratos -->
                                    <tr>
                                        <td><?= $index + 1 ?></td><!-- Muestra el número de fila (empezando en 1) -->
                                        <td>
                                            <strong><?= htmlspecialchars($contrato['beneficiario_nombre']) ?></strong>
                                            <!-- Muestra el número de fila (empezando en 1) -->
                                        </td>
                                        <td><?= htmlspecialchars($contrato['dni']) ?></td>
                                        <!-- Muestra el DNI del beneficiario protegido contra XSS -->
                                        <td>
                                            <strong>S/ <?= number_format($contrato['monto'], 2) ?></strong>
                                            <!-- Muestra el monto del contrato con dos decimales y en negrita -->
                                        </td>
                                        <td>
                                            <?php
                                            // Busca el campo de cuotas según el nombre disponible
                                            $cuotas_value = 'N/A'; // Valor por defecto si no encuentra el campo
                                            if (isset($contrato['cuotas'])) {
                                                $cuotas_value = $contrato['cuotas']; // Si existe 'cuotas', lo usa
                                            } elseif (isset($contrato['numero_cuotas'])) {
                                                $cuotas_value = $contrato['numero_cuotas'];// Si existe 'numero_cuotas', lo usa
                                            } elseif (isset($contrato['num_cuotas'])) {
                                                $cuotas_value = $contrato['num_cuotas']; // Si existe 'num_cuotas', lo usa
                                            } elseif (isset($contrato['total_cuotas'])) {
                                                $cuotas_value = $contrato['total_cuotas']; // Si existe 'total_cuotas', lo usa
                                            }
                                            echo $cuotas_value;// Muestra el número de cuotas encontrado
                                            ?> cuotas <!-- Texto fijo para indicar que es cantidad de cuotas -->
                                        </td>
                                        <td>
                                            <?php
                                            // Busca el campo de tasa según el nombre disponible
                                            $tasa_value = '0'; // Valor por defecto si no encuentra el campo
                                            if (isset($contrato['tasa'])) {
                                                $tasa_value = $contrato['tasa']; // Si existe 'tasa', lo usa
                                            } elseif (isset($contrato['tasa_interes'])) {
                                                $tasa_value = $contrato['tasa_interes']; // Si existe 'tasa_interes', lo usa
                                            } elseif (isset($contrato['interes'])) {
                                                $tasa_value = $contrato['interes']; // Si existe 'interes', lo usa
                                            } elseif (isset($contrato['porcentaje'])) {
                                                $tasa_value = $contrato['porcentaje']; // Si existe 'interes', lo usa
                                            }
                                            echo $tasa_value; // Muestra la tasa encontrada
                                            ?>% <!-- Texto fijo para indicar porcentaje -->
                                        </td>
                                        <td>
                                            <?php
                                            // Muestra el estado con un badge de color
                                            $estado_class = '';// Clase CSS para el color del badge
                                            $estado_texto = '';// Texto a mostrar según el estado
                                            switch ($contrato['estado']) {
                                                case 'ACT':
                                                    $estado_class = 'badge-activo';// Clase para estado activo
                                                    $estado_texto = 'ACTIVO';// Texto para estado activo
                                                    break;
                                                case 'FIN':
                                                    $estado_class = 'badge-finalizado';// Clase para estado finalizado
                                                    $estado_texto = 'FINALIZADO';// Texto para estado finalizado
                                                    break;
                                                case 'SUS':
                                                    $estado_class = 'badge-suspendido'; // Clase para estado suspendido
                                                    $estado_texto = 'SUSPENDIDO'; // Texto para estado suspendido
                                                    break;
                                            }
                                            ?>
                                            <span class="badge badge-estado <?= $estado_class ?>"><?= $estado_texto ?></span>
                                            <!-- Muestra el badge con color y texto según estado -->
                                        </td>
                                        <td><?= date('d/m/Y', strtotime($contrato['creado'])) ?></td>
                                        <!-- Muestra la fecha de creación del contrato en formato día/mes/año -->
                                        <td>
                                            <!-- Botón para ver cronograma de pagos -->
                                            <button class="btn btn-sm btn-success"
                                                onclick="verCronograma(<?= $contrato['idcontrato'] ?>)" title="Ver cronograma">
                                                <i class="fas fa-calendar-alt"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?><!-- Fin del ciclo de contratos -->
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <!-- Mensaje si no hay contratos registrados -->
                    <div class="text-center py-5">
                        <i class="fas fa-file-contract fa-3x text-muted mb-3"></i>
                        <h4 class="text-muted">No hay contratos registrados</h4>
                        <p class="text-muted">Comienza creando el primer contrato de préstamo</p>
                        <button class="btn btn-primary btn-custom" data-bs-toggle="modal" data-bs-target="#modalContrato">
                            <i class="fas fa-plus me-2"></i>Crear Primer Contrato
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Estadísticas de contratos -->
        <div class="row mt-4"><!-- Fila con margen superior para separar del contenido anterior -->
            <div class="col-md-12"><!-- Columna que ocupa todo el ancho en pantallas medianas o más grandes -->
                <div class="card card-custom"><!-- Tarjeta personalizada para mostrar las estadísticas -->
                    <div class="card-body text-center"><!-- Cuerpo de la tarjeta centrado -->
                        <div class="row"><!-- Fila interna para las 4 estadísticas -->
                            <!-- Total de contratos -->
                            <div class="col-md-3">
                                <i class="fas fa-file-contract fa-2x text-primary mb-2"></i><!-- Icono de contrato -->
                                <h3 class="text-primary"><?= count($contratos) ?></h3>
                                <!-- Muestra el total de contratos usando count() -->
                                <p class="text-muted">Total Contratos</p><!-- Texto descriptivo -->
                            </div>
                            <!-- Contratos activos -->
                            <div class="col-md-3"> <!-- Columna para contratos activos -->
                                <i class="fas fa-check-circle fa-2x text-success mb-2"></i><!-- Icono de check -->
                                <h3 class="text-success">
                                    <?= count(array_filter($contratos, function ($c) {
                                        return $c['estado'] == 'ACT';
                                    })) ?><!-- Cuenta cuántos contratos tienen estado 'ACT' (activos) -->
                                </h3>
                                <p class="text-muted">Contratos Activos</p><!-- Texto descriptivo -->
                            </div>
                            <!-- Monto total activo -->
                            <div class="col-md-3">
                                <i class="fas fa-money-bill-wave fa-2x text-info mb-2"></i>
                                <h3 class="text-info">S/
                                    <?= number_format(array_sum(array_map(function ($c) {
                                        return $c['estado'] == 'ACT' ? $c['monto'] : 0;
                                    }, $contratos)), 0) ?><!-- Suma los montos de los contratos activos y los muestra formateados -->
                                </h3>
                                <p class="text-muted">Monto Total Activo</p>
                            </div>
                            <!-- Mes actual -->
                            <div class="col-md-3">
                                <i class="fas fa-calendar fa-2x text-warning mb-2"></i>
                                <h3 class="text-warning"><?= count($contratos) > 0 ? date('m/Y') : 'N/A' ?></h3>
                                <!-- Muestra el mes/año actual si hay contratos, si no muestra N/A -->
                                <p class="text-muted">Mes Actual</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para crear un nuevo contrato -->
    <div class="modal fade" id="modalContrato" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-file-contract me-2"></i>Nuevo Contrato de Préstamo
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <!-- Formulario para crear contrato -->
                <form action="procesar_contrato.php" method="POST">
                    <div class="modal-body">
                        <div class="row">
                            <!-- Selección de beneficiario -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="idbeneficiario" class="form-label">
                                        <i class="fas fa-user me-1"></i>Beneficiario *
                                    </label>
                                    <select class="form-select" id="idbeneficiario" name="idbeneficiario" required>
                                        <option value="">Seleccionar beneficiario...</option>
                                        <?php foreach ($beneficiarios as $beneficiario): ?>
                                            <option value="<?= $beneficiario['idbeneficiario'] ?>">
                                                <?= htmlspecialchars($beneficiario['apellidos'] . ', ' . $beneficiario['nombres']) ?>
                                                - DNI: <?= $beneficiario['dni'] ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <!-- Monto del préstamo -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="monto" class="form-label">
                                        <i class="fas fa-money-bill me-1"></i>Monto del Préstamo *
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">S/</span>
                                        <input type="number" class="form-control" id="monto" name="monto" step="0.01"
                                            min="100" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Número de cuotas -->
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="cuotas" class="form-label">
                                        <i class="fas fa-calendar-alt me-1"></i>Número de Cuotas *
                                    </label>
                                    <select class="form-select" id="cuotas" name="cuotas" required
                                        onchange="calcularCronograma()">
                                        <option value="">Seleccionar...</option>
                                    </select>
                                </div>
                            </div>
                            <!-- Tasa de interés -->
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="tasa" class="form-label">
                                        <i class="fas fa-percentage me-1"></i>Tasa de Interés *
                                    </label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="tasa" name="tasa" step="0.1"
                                            min="0" max="100" required onchange="calcularCronograma()">
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>
                            </div>
                            <!-- Fecha de inicio -->
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="fechainicio" class="form-label">
                                        <i class="fas fa-calendar me-1"></i>Fecha de Inicio *
                                    </label>
                                    <input type="date" class="form-control" id="fechainicio" name="fechainicio" required
                                        value="<?= date('Y-m-d') ?>">
                                </div>
                            </div>
                        </div>

                        <!-- Observaciones adicionales -->
                        <div class="mb-3">
                            <label for="observaciones" class="form-label">
                                <i class="fas fa-sticky-note me-1"></i>Observaciones
                            </label>
                            <textarea class="form-control" id="observaciones" name="observaciones" rows="3"
                                placeholder="Observaciones adicionales sobre el contrato..."></textarea>
                        </div>

                        <!-- Resumen del préstamo (se muestra al calcular) -->
                        <div class="card bg-light mt-3" id="resumenPrestamo" style="display: none;">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="fas fa-calculator me-2"></i>Resumen del Préstamo</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4 text-center">
                                        <strong>Cuota Mensual</strong>
                                        <div class="h4 text-primary" id="cuotaMensual">S/ 0.00</div>
                                    </div>
                                    <div class="col-md-4 text-center">
                                        <strong>Total a Pagar</strong>
                                        <div class="h4 text-info" id="totalPagar">S/ 0.00</div>
                                    </div>
                                    <div class="col-md-4 text-center">
                                        <strong>Total Intereses</strong>
                                        <div class="h4 text-warning" id="totalIntereses">S/ 0.00</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <!-- Botón para cancelar -->
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i>Cancelar
                        </button>
                        <!-- Botón para crear contrato -->
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Crear Contrato
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts de Bootstrap y funciones JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Función para ver detalles del contrato (a implementar)
        function verContrato(id) {
            alert('Ver detalles del contrato ID: ' + id);
        }

        // Función para editar contrato (a implementar)
        function editarContrato(id) {
            alert('Editar contrato ID: ' + id);
        }

        // Función para ver el cronograma de pagos
        function verCronograma(id) {
            window.location.href = '../pagos/pagos.php?contrato=' + id;
        }

        // Calcula el resumen del préstamo según monto, cuotas y tasa
        function calcularCronograma() {
            // Obtiene el monto ingresado y lo convierte a número flotante
            const monto = parseFloat(document.getElementById('monto').value) || 0;
            // Obtiene el número de cuotas y lo convierte a entero
            const cuotas = parseInt(document.getElementById('cuotas').value) || 0;
            // Obtiene la tasa de interés y la convierte a número flotante
            const tasa = parseFloat(document.getElementById('tasa').value) || 0;

            // Verifica que los valores sean válidos para calcular
            if (monto > 0 && cuotas > 0 && tasa >= 0) {
                // Calcula la tasa mensual (tasa anual dividida entre 12 y entre 100)
                const tasaMensual = tasa / 100 / 12;
                let cuotaMensual;

                // Si la tasa es mayor a 0, calcula la cuota con fórmula de interés compuesto
                if (tasa > 0) {
                    cuotaMensual = monto * (tasaMensual * Math.pow(1 + tasaMensual, cuotas)) / (Math.pow(1 + tasaMensual, cuotas) - 1);
                } else {
                    // Si la tasa es 0, simplemente divide el monto entre las cuotas
                    cuotaMensual = monto / cuotas;
                }

                // Calcula el total a pagar sumando todas las cuotas
                const totalPagar = cuotaMensual * cuotas;
                // Calcula el total de intereses restando el monto original al total a pagar
                const totalIntereses = totalPagar - monto;

                // Muestra la cuota mensual calculada en el HTML
                document.getElementById('cuotaMensual').textContent = 'S/ ' + cuotaMensual.toFixed(2);
                // Muestra el total a pagar en el HTML
                document.getElementById('totalPagar').textContent = 'S/ ' + totalPagar.toFixed(2);
                // Muestra el total de intereses en el HTML
                document.getElementById('totalIntereses').textContent = 'S/ ' + totalIntereses.toFixed(2);

                // Muestra el resumen del préstamo
                document.getElementById('resumenPrestamo').style.display = 'block';
            } else {
                // Si los datos no son válidos, oculta el resumen
                document.getElementById('resumenPrestamo').style.display = 'none';
            }
        }

        // Genera automáticamente las opciones de cuotas de 1 a 60
        const cuotasSelect = document.getElementById('cuotas');
        for (let i = 1; i <= 60; i++) {
            // Crea una opción para cada número de cuota
            const option = document.createElement('option');
            option.value = i;
            option.textContent = `${i} cuota${i > 1 ? 's' : ''}`; // Pluraliza si es más de 1
            cuotasSelect.appendChild(option); // Agrega la opción al select
        }

        // Valida que haya beneficiarios antes de mostrar el modal de contrato
        document.addEventListener('DOMContentLoaded', function () {
            // Cuenta cuántos beneficiarios existen
            const totalBeneficiarios = <?= count($beneficiarios) ?>;
            // Obtiene el modal de contrato
            const modalContrato = document.getElementById('modalContrato');
            // Selecciona todos los botones que abren el modal de contrato
            const botonesNuevoContrato = document.querySelectorAll('[data-bs-target="#modalContrato"]');

            // Por cada botón, agrega un listener para el click
            botonesNuevoContrato.forEach(boton => {
                boton.addEventListener('click', function (e) {
                    // Si no hay beneficiarios, evita abrir el modal y muestra alerta
                    if (totalBeneficiarios === 0) {
                        e.preventDefault();
                        alert('Debe registrar al menos un beneficiario antes de crear un contrato.');
                        window.location.href = 'beneficiarios.php'; // Redirige a la página de beneficiarios
                    }
                });
            });
        });
    </script>
</body>

</html>