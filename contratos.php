<?php
require_once 'db/database.php';

$database = new Database();
$db = $database->getConnection();

// Obtener todos los beneficiarios para el select
$query_beneficiarios = "SELECT * FROM beneficiarios ORDER BY apellidos, nombres";
$stmt_beneficiarios = $db->prepare($query_beneficiarios);
$stmt_beneficiarios->execute();
$beneficiarios = $stmt_beneficiarios->fetchAll(PDO::FETCH_ASSOC);

// Primero verificar la estructura de la tabla contratos
try {
    $query_structure = "DESCRIBE contratos";
    $stmt_structure = $db->prepare($query_structure);
    $stmt_structure->execute();
    $columns = $stmt_structure->fetchAll(PDO::FETCH_ASSOC);

    // Debug: mostrar las columnas disponibles (puedes comentar esto después)
    // echo "<pre>Columnas disponibles en la tabla contratos:";
    // foreach($columns as $col) { echo "\n" . $col['Field']; }
    // echo "</pre>";

} catch (Exception $e) {
    // Manejar error
}

// Obtener todos los contratos con información del beneficiario
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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contratos - Sistema de Préstamos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">


</head>

<body>
    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg navbar-custom mb-4">
        <div class="container">
            <a class="navbar-brand text-white fw-bold" href="dashboard.php">
                <i class="fas fa-handshake me-2"></i>Sistema de Préstamos
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link text-white" href="dashboard.php">
                    <i class="fas fa-arrow-left me-1"></i> Volver al Dashboard
                </a>
            </div>
        </div>
    </nav>

    <div class="container">
        <!-- TÍTULO DE BIENVENIDA -->
        <div class="text-center mb-5">
            <h1 class="welcome-title display-4 fw-bold">
                Gestión de Contratos
            </h1>
            <p class="lead text-white-50">
                Administra los contratos de préstamo y su estado
            </p>
        </div>

        <!-- MENSAJES -->
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>Contrato procesado exitosamente.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>Error al procesar el contrato.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- CARD PRINCIPAL -->
        <div class="card card-custom">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-file-contract me-2"></i>Lista de Contratos
                </h5>
                <button class="btn btn-light btn-custom" data-bs-toggle="modal" data-bs-target="#modalContrato">
                    <i class="fas fa-plus me-2"></i>Nuevo Contrato
                </button>
            </div>
            <div class="card-body">
                <?php if (count($contratos) > 0): ?>
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
                                <?php foreach ($contratos as $index => $contrato): ?>
                                    <tr>
                                        <td><?= $index + 1 ?></td>
                                        <td>
                                            <strong><?= htmlspecialchars($contrato['beneficiario_nombre']) ?></strong>
                                        </td>
                                        <td><?= htmlspecialchars($contrato['dni']) ?></td>
                                        <td>
                                            <strong>S/ <?= number_format($contrato['monto'], 2) ?></strong>
                                        </td>
                                        <td>
                                            <?php
                                            // Buscar el campo de cuotas con diferentes nombres posibles
                                            $cuotas_value = 'N/A';
                                            if (isset($contrato['cuotas'])) {
                                                $cuotas_value = $contrato['cuotas'];
                                            } elseif (isset($contrato['numero_cuotas'])) {
                                                $cuotas_value = $contrato['numero_cuotas'];
                                            } elseif (isset($contrato['num_cuotas'])) {
                                                $cuotas_value = $contrato['num_cuotas'];
                                            } elseif (isset($contrato['total_cuotas'])) {
                                                $cuotas_value = $contrato['total_cuotas'];
                                            }
                                            echo $cuotas_value;
                                            ?> cuotas
                                        </td>
                                        <td>
                                            <?php
                                            // Buscar el campo de tasa con diferentes nombres posibles
                                            $tasa_value = '0';
                                            if (isset($contrato['tasa'])) {
                                                $tasa_value = $contrato['tasa'];
                                            } elseif (isset($contrato['tasa_interes'])) {
                                                $tasa_value = $contrato['tasa_interes'];
                                            } elseif (isset($contrato['interes'])) {
                                                $tasa_value = $contrato['interes'];
                                            } elseif (isset($contrato['porcentaje'])) {
                                                $tasa_value = $contrato['porcentaje'];
                                            }
                                            echo $tasa_value;
                                            ?>%
                                        </td>
                                        <td>
                                            <?php
                                            $estado_class = '';
                                            $estado_texto = '';
                                            switch ($contrato['estado']) {
                                                case 'ACT':
                                                    $estado_class = 'badge-activo';
                                                    $estado_texto = 'ACTIVO';
                                                    break;
                                                case 'FIN':
                                                    $estado_class = 'badge-finalizado';
                                                    $estado_texto = 'FINALIZADO';
                                                    break;
                                                case 'SUS':
                                                    $estado_class = 'badge-suspendido';
                                                    $estado_texto = 'SUSPENDIDO';
                                                    break;
                                            }
                                            ?>
                                            <span class="badge badge-estado <?= $estado_class ?>"><?= $estado_texto ?></span>
                                        </td>
                                        <td><?= date('d/m/Y', strtotime($contrato['creado'])) ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-success"
                                                onclick="verCronograma(<?= $contrato['idcontrato'] ?>)" title="Ver cronograma">
                                                <i class="fas fa-calendar-alt"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
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

        <!-- ESTADÍSTICAS -->
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card card-custom">
                    <div class="card-body text-center">
                        <div class="row">
                            <div class="col-md-3">
                                <i class="fas fa-file-contract fa-2x text-primary mb-2"></i>
                                <h3 class="text-primary"><?= count($contratos) ?></h3>
                                <p class="text-muted">Total Contratos</p>
                            </div>
                            <div class="col-md-3">
                                <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                                <h3 class="text-success">
                                    <?= count(array_filter($contratos, function ($c) {
                                        return $c['estado'] == 'ACT'; })) ?>
                                </h3>
                                <p class="text-muted">Contratos Activos</p>
                            </div>
                            <div class="col-md-3">
                                <i class="fas fa-money-bill-wave fa-2x text-info mb-2"></i>
                                <h3 class="text-info">S/
                                    <?= number_format(array_sum(array_map(function ($c) {
                                        return $c['estado'] == 'ACT' ? $c['monto'] : 0; }, $contratos)), 0) ?>
                                </h3>
                                <p class="text-muted">Monto Total Activo</p>
                            </div>
                            <div class="col-md-3">
                                <i class="fas fa-calendar fa-2x text-warning mb-2"></i>
                                <h3 class="text-warning"><?= count($contratos) > 0 ? date('m/Y') : 'N/A' ?></h3>
                                <p class="text-muted">Mes Actual</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Nuevo Contrato -->
    <div class="modal fade" id="modalContrato" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-file-contract me-2"></i>Nuevo Contrato de Préstamo
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="procesar_contrato.php" method="POST" id="formContrato">
                    <div class="modal-body">
                        <div class="row">
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
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="cuotas" class="form-label">
                                        <i class="fas fa-calendar-alt me-1"></i>Número de Cuotas *
                                    </label>
                                    <select class="form-select" id="cuotas" name="cuotas" required
                                        onchange="calcularCronograma()">
                                        <option value="">Seleccionar...</option>
                                        <option value="6">6 cuotas</option>
                                        <option value="12">12 cuotas</option>
                                        <option value="18">18 cuotas</option>
                                        <option value="24">24 cuotas</option>
                                        <option value="36">36 cuotas</option>
                                    </select>
                                </div>
                            </div>
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

                        <div class="mb-3">
                            <label for="observaciones" class="form-label">
                                <i class="fas fa-sticky-note me-1"></i>Observaciones
                            </label>
                            <textarea class="form-control" id="observaciones" name="observaciones" rows="3"
                                placeholder="Observaciones adicionales sobre el contrato..."></textarea>
                        </div>

                        <!-- Resumen del préstamo -->
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
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i>Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Crear Contrato
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function verContrato(id) {
            alert('Ver detalles del contrato ID: ' + id);
            // Implementar funcionalidad para ver detalles
        }

        function editarContrato(id) {
            alert('Editar contrato ID: ' + id);
            // Implementar funcionalidad para editar
        }

        function verCronograma(id) {
            window.location.href = 'pagos.php?contrato=' + id;
        }

        function calcularCronograma() {
            const monto = parseFloat(document.getElementById('monto').value) || 0;
            const cuotas = parseInt(document.getElementById('cuotas').value) || 0;
            const tasa = parseFloat(document.getElementById('tasa').value) || 0;

            if (monto > 0 && cuotas > 0 && tasa >= 0) {
                // Cálculo de cuota con interés compuesto
                const tasaMensual = tasa / 100 / 12;
                let cuotaMensual;

                if (tasa > 0) {
                    cuotaMensual = monto * (tasaMensual * Math.pow(1 + tasaMensual, cuotas)) / (Math.pow(1 + tasaMensual, cuotas) - 1);
                } else {
                    cuotaMensual = monto / cuotas;
                }

                const totalPagar = cuotaMensual * cuotas;
                const totalIntereses = totalPagar - monto;

                document.getElementById('cuotaMensual').textContent = 'S/ ' + cuotaMensual.toFixed(2);
                document.getElementById('totalPagar').textContent = 'S/ ' + totalPagar.toFixed(2);
                document.getElementById('totalIntereses').textContent = 'S/ ' + totalIntereses.toFixed(2);

                document.getElementById('resumenPrestamo').style.display = 'block';
            } else {
                document.getElementById('resumenPrestamo').style.display = 'none';
            }
        }

        // Validar que hay beneficiarios antes de mostrar el modal
        document.addEventListener('DOMContentLoaded', function () {
            const totalBeneficiarios = <?= count($beneficiarios) ?>;
            const modalContrato = document.getElementById('modalContrato');
            const botonesNuevoContrato = document.querySelectorAll('[data-bs-target="#modalContrato"]');

            botonesNuevoContrato.forEach(boton => {
                boton.addEventListener('click', function (e) {
                    if (totalBeneficiarios === 0) {
                        e.preventDefault();
                        alert('Debe registrar al menos un beneficiario antes de crear un contrato.');
                        window.location.href = 'beneficiarios.php';
                    }
                });
            });
        });
    </script>
</body>

</html>