<?php
require_once 'db/database.php';

$database = new Database();
$db = $database->getConnection();

// Obtener ID del contrato si viene por parámetro
$contrato_id = isset($_GET['contrato']) ? intval($_GET['contrato']) : 0;

// Obtener lista de contratos activos para el select
$query_contratos = "
    SELECT c.idcontrato, 
           CONCAT(b.apellidos, ', ', b.nombres) as beneficiario_nombre,
           c.monto,
           c.estado
    FROM contratos c
    INNER JOIN beneficiarios b ON c.idbeneficiario = b.idbeneficiario
    WHERE c.estado = 'ACT'
    ORDER BY c.creado DESC
";
$stmt_contratos = $db->prepare($query_contratos);
$stmt_contratos->execute();
$contratos = $stmt_contratos->fetchAll(PDO::FETCH_ASSOC);

// Si hay un contrato seleccionado, obtener su cronograma
$pagos = [];
$info_contrato = null;
if ($contrato_id > 0) {
    // Información del contrato
    $query_info = "
        SELECT c.*, 
               CONCAT(b.apellidos, ', ', b.nombres) as beneficiario_nombre,
               b.dni,
               b.telefono
        FROM contratos c
        INNER JOIN beneficiarios b ON c.idbeneficiario = b.idbeneficiario
        WHERE c.idcontrato = ?
    ";
    $stmt_info = $db->prepare($query_info);
    $stmt_info->execute([$contrato_id]);
    $info_contrato = $stmt_info->fetch(PDO::FETCH_ASSOC);
    
    // Obtener cronograma de pagos
    $query_pagos = "
        SELECT * FROM pagos 
        WHERE idcontrato = ? 
        ORDER BY numcuota
    ";
    $stmt_pagos = $db->prepare($query_pagos);
    $stmt_pagos->execute([$contrato_id]);
    $pagos = $stmt_pagos->fetchAll(PDO::FETCH_ASSOC);
}

// Estadísticas del contrato seleccionado
$total_pagado = 0;
$total_pendiente = 0;
$cuotas_pagadas = 0;
$cuotas_pendientes = 0;

foreach ($pagos as $pago) {
    if ($pago['fechapago']) {
        $total_pagado += $pago['monto'] + $pago['penalidad'];
        $cuotas_pagadas++;
    } else {
        $total_pendiente += $pago['monto'];
        $cuotas_pendientes++;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cronograma de Pagos - Sistema de Préstamos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            min-height: 100vh;
            color: #333;
        }
        
        .navbar-custom {
            background: rgba(0,0,0,0.2);
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .welcome-title {
            color: white;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
            margin-bottom: 10px;
        }
        
        .card-custom {
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            border: none;
            overflow: hidden;
        }
        
        .btn-custom {
            border-radius: 25px;
            padding: 8px 20px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .badge-pagado {
            background: #28a745;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
        }
        
        .badge-pendiente {
            background: #ffc107;
            color: #333;
            padding: 5px 15px;
            border-radius: 20px;
        }
        
        .badge-vencido {
            background: #dc3545;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
        }
        
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
        }
        
        .table-hover tbody tr:hover {
            background-color: rgba(0,0,0,0.02);
            cursor: pointer;
        }
        
        .cuota-pagada {
            background-color: #d4edda !important;
        }
        
        .cuota-vencida {
            background-color: #f8d7da !important;
        }
    </style>
</head>
<body>
    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg navbar-custom mb-4">
        <div class="container">
            <a class="navbar-brand text-white fw-bold" href="dashboard.php">
                <i class="fas fa-handshake me-2"></i>Sistema de Préstamos
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link text-white" href="contratos.php">
                    <i class="fas fa-file-contract me-1"></i> Contratos
                </a>
                <a class="nav-link text-white" href="dashboard.php">
                    <i class="fas fa-home me-1"></i> Dashboard
                </a>
            </div>
        </div>
    </nav>

    <div class="container">
        <!-- TÍTULO -->
        <div class="text-center mb-5">
            <h1 class="welcome-title display-4 fw-bold">
                Cronograma de Pagos
            </h1>
            <p class="lead text-white-50">
                Gestiona los pagos de los contratos de préstamo
            </p>
        </div>

        <!-- MENSAJES -->
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>Pago registrado exitosamente.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>Error al registrar el pago.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- SELECTOR DE CONTRATO -->
        <div class="card card-custom mb-4">
            <div class="card-body">
                <form method="GET" action="">
                    <div class="row align-items-end">
                        <div class="col-md-10">
                            <label for="contrato" class="form-label">
                                <i class="fas fa-search me-1"></i>Seleccionar Contrato
                            </label>
                            <select class="form-select" name="contrato" id="contrato" onchange="this.form.submit()">
                                <option value="">-- Seleccione un contrato --</option>
                                <?php foreach ($contratos as $contrato): ?>
                                    <option value="<?= $contrato['idcontrato'] ?>" 
                                            <?= $contrato_id == $contrato['idcontrato'] ? 'selected' : '' ?>>
                                        Contrato #<?= $contrato['idcontrato'] ?> - 
                                        <?= htmlspecialchars($contrato['beneficiario_nombre']) ?> - 
                                        S/ <?= number_format($contrato['monto'], 2) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary btn-custom w-100">
                                <i class="fas fa-search me-1"></i>Buscar
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <?php if ($info_contrato && count($pagos) > 0): ?>
            <!-- INFORMACIÓN DEL CONTRATO -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card card-custom">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-info-circle me-2"></i>Información del Contrato #<?= $contrato_id ?>
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <p><strong>Beneficiario:</strong> <?= htmlspecialchars($info_contrato['beneficiario_nombre']) ?></p>
                                    <p><strong>DNI:</strong> <?= htmlspecialchars($info_contrato['dni']) ?></p>
                                    <p><strong>Teléfono:</strong> <?= htmlspecialchars($info_contrato['telefono']) ?></p>
                                </div>
                                <div class="col-md-4">
                                    <p><strong>Monto Préstamo:</strong> S/ <?= number_format($info_contrato['monto'], 2) ?></p>
                                    <p><strong>Tasa Interés:</strong> <?= $info_contrato['interes'] ?>%</p>
                                    <p><strong>N° Cuotas:</strong> <?= $info_contrato['numcuotas'] ?></p>
                                </div>
                                <div class="col-md-4">
                                    <p><strong>Fecha Inicio:</strong> <?= date('d/m/Y', strtotime($info_contrato['fechainicio'])) ?></p>
                                    <p><strong>Día de Pago:</strong> <?= $info_contrato['diapago'] ?> de cada mes</p>
                                    <p><strong>Estado:</strong> 
                                        <span class="badge <?= $info_contrato['estado'] == 'ACT' ? 'bg-success' : 'bg-secondary' ?>">
                                            <?= $info_contrato['estado'] == 'ACT' ? 'ACTIVO' : 'FINALIZADO' ?>
                                        </span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ESTADÍSTICAS -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="stats-card">
                        <i class="fas fa-check-circle fa-2x mb-2"></i>
                        <h4><?= $cuotas_pagadas ?>/<?= count($pagos) ?></h4>
                        <p class="mb-0">Cuotas Pagadas</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <i class="fas fa-clock fa-2x mb-2"></i>
                        <h4><?= $cuotas_pendientes ?></h4>
                        <p class="mb-0">Cuotas Pendientes</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <i class="fas fa-money-check-alt fa-2x mb-2"></i>
                        <h4>S/ <?= number_format($total_pagado, 2) ?></h4>
                        <p class="mb-0">Total Pagado</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <i class="fas fa-hand-holding-usd fa-2x mb-2"></i>
                        <h4>S/ <?= number_format($total_pendiente, 2) ?></h4>
                        <p class="mb-0">Total Pendiente</p>
                    </div>
                </div>
            </div>

            <!-- CRONOGRAMA -->
            <div class="card card-custom">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-calendar-alt me-2"></i>Cronograma de Pagos
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Cuota</th>
                                    <th>Fecha Vencimiento</th>
                                    <th>Monto Cuota</th>
                                    <th>Fecha Pago</th>
                                    <th>Penalidad</th>
                                    <th>Total Pagado</th>
                                    <th>Medio</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pagos as $pago): 
                                    // Calcular fecha de vencimiento
                                    $fecha_vencimiento = date('Y-m-d', strtotime($info_contrato['fechainicio'] . ' + ' . ($pago['numcuota'] - 1) . ' months'));
                                    $fecha_vencimiento = date('Y-m-' . $info_contrato['diapago'], strtotime($fecha_vencimiento));
                                    
                                    // Determinar estado
                                    $hoy = date('Y-m-d');
                                    $estado_cuota = '';
                                    $clase_fila = '';
                                    
                                    if ($pago['fechapago']) {
                                        $estado_cuota = 'PAGADO';
                                        $clase_fila = 'cuota-pagada';
                                    } elseif ($fecha_vencimiento < $hoy) {
                                        $estado_cuota = 'VENCIDO';
                                        $clase_fila = 'cuota-vencida';
                                    } else {
                                        $estado_cuota = 'PENDIENTE';
                                    }
                                ?>
                                    <tr class="<?= $clase_fila ?>">
                                        <td><strong>#<?= $pago['numcuota'] ?></strong></td>
                                        <td><?= date('d/m/Y', strtotime($fecha_vencimiento)) ?></td>
                                        <td><strong>S/ <?= number_format($pago['monto'], 2) ?></strong></td>
                                        <td>
                                            <?= $pago['fechapago'] ? date('d/m/Y', strtotime($pago['fechapago'])) : '-' ?>
                                        </td>
                                        <td>
                                            <?= $pago['penalidad'] > 0 ? 'S/ ' . number_format($pago['penalidad'], 2) : '-' ?>
                                        </td>
                                        <td>
                                            <?php if ($pago['fechapago']): ?>
                                                <strong>S/ <?= number_format($pago['monto'] + $pago['penalidad'], 2) ?></strong>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($pago['medio']): ?>
                                                <span class="badge bg-secondary">
                                                    <?= $pago['medio'] == 'EFC' ? 'EFECTIVO' : 'DEPÓSITO' ?>
                                                </span>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php
                                            $badge_class = '';
                                            switch ($estado_cuota) {
                                                case 'PAGADO':
                                                    $badge_class = 'badge-pagado';
                                                    break;
                                                case 'PENDIENTE':
                                                    $badge_class = 'badge-pendiente';
                                                    break;
                                                case 'VENCIDO':
                                                    $badge_class = 'badge-vencido';
                                                    break;
                                            }
                                            ?>
                                            <span class="badge <?= $badge_class ?>"><?= $estado_cuota ?></span>
                                        </td>
                                        <td>
                                            <?php if (!$pago['fechapago'] && $info_contrato['estado'] == 'ACT'): ?>
                                                <button class="btn btn-sm btn-success" 
                                                        onclick="registrarPago(<?= $pago['idpago'] ?>, <?= $pago['numcuota'] ?>, <?= $pago['monto'] ?>, '<?= $fecha_vencimiento ?>')"
                                                        title="Registrar pago">
                                                    <i class="fas fa-dollar-sign"></i>
                                                </button>
                                            <?php else: ?>
                                                <button class="btn btn-sm btn-info" 
                                                        onclick="verDetallePago(<?= $pago['idpago'] ?>)"
                                                        title="Ver detalle">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php elseif ($contrato_id > 0): ?>
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle me-2"></i>
                No se encontró información del contrato seleccionado.
            </div>
        <?php endif; ?>
    </div>

    <!-- Modal Registrar Pago -->
    <div class="modal fade" id="modalPago" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-money-check-alt me-2"></i>Registrar Pago
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="procesar_pago.php" method="POST" id="formPago">
                    <input type="hidden" name="idpago" id="idpago">
                    <input type="hidden" name="idcontrato" value="<?= $contrato_id ?>">
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Cuota #<span id="numCuotaPago"></span></strong><br>
                            Monto: S/ <span id="montoCuotaPago"></span><br>
                            Vencimiento: <span id="fechaVencimientoPago"></span>
                        </div>

                        <div class="mb-3">
                            <label for="fechapago" class="form-label">
                                <i class="fas fa-calendar me-1"></i>Fecha de Pago *
                            </label>
                            <input type="date" class="form-control" id="fechapago" name="fechapago" 
                                   value="<?= date('Y-m-d') ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="medio" class="form-label">
                                <i class="fas fa-wallet me-1"></i>Medio de Pago *
                            </label>
                            <select class="form-select" id="medio" name="medio" required>
                                <option value="">Seleccionar...</option>
                                <option value="EFC">EFECTIVO</option>
                                <option value="DEP">DEPÓSITO</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="penalidad" class="form-label">
                                <i class="fas fa-exclamation-triangle me-1"></i>Penalidad
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">S/</span>
                                <input type="number" class="form-control" id="penalidad" name="penalidad" 
                                       step="0.01" min="0" value="0">
                            </div>
                            <small class="text-muted">
                                Se aplicará automáticamente 10% si el pago es posterior al vencimiento
                            </small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">
                                <i class="fas fa-money-bill-wave me-1"></i>Total a Pagar
                            </label>
                            <div class="h4 text-success" id="totalAPagar">S/ 0.00</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i>Cancelar
                        </button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check me-1"></i>Registrar Pago
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let montoCuotaActual = 0;

        function registrarPago(idpago, numcuota, monto, fechaVencimiento) {
            document.getElementById('idpago').value = idpago;
            document.getElementById('numCuotaPago').textContent = numcuota;
            document.getElementById('montoCuotaPago').textContent = monto.toFixed(2);
            document.getElementById('fechaVencimientoPago').textContent = formatDate(fechaVencimiento);
            
            montoCuotaActual = monto;
            
            // Calcular penalidad automática si está vencido
            const hoy = new Date();
            const vencimiento = new Date(fechaVencimiento);
            
            if (hoy > vencimiento) {
                const penalidad = monto * 0.10; // 10% de penalidad
                document.getElementById('penalidad').value = penalidad.toFixed(2);
            } else {
                document.getElementById('penalidad').value = '0';
            }
            
            calcularTotalPago();
            
            const modal = new bootstrap.Modal(document.getElementById('modalPago'));
            modal.show();
        }

        function calcularTotalPago() {
            const penalidad = parseFloat(document.getElementById('penalidad').value) || 0;
            const total = montoCuotaActual + penalidad;
            document.getElementById('totalAPagar').textContent = 'S/ ' + total.toFixed(2);
        }

        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('es-PE');
        }

        function verDetallePago(idpago) {
            alert('Ver detalle del pago ID: ' + idpago);
            // Aquí puedes implementar la funcionalidad para ver detalles
        }

        // Listener para cambios en penalidad
        document.getElementById('penalidad').addEventListener('input', calcularTotalPago);

        // Listener para cambios en fecha de pago
        document.getElementById('fechapago').addEventListener('change', function() {
            const fechaPago = new Date(this.value);
            const fechaVencimiento = new Date(document.getElementById('fechaVencimientoPago').textContent.split('/').reverse().join('-'));
            
            if (fechaPago > fechaVencimiento && document.getElementById('penalidad').value == '0') {
                const penalidad = montoCuotaActual * 0.10;
                document.getElementById('penalidad').value = penalidad.toFixed(2);
                calcularTotalPago();
            }
        });
    </script>
</body>
</html>