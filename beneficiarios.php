<?php
require_once 'db/database.php';

$database = new Database();
$db = $database->getConnection();

// Obtener todos los beneficiarios
$query_beneficiarios = "SELECT * FROM beneficiarios ORDER BY apellidos, nombres";
$stmt_beneficiarios = $db->prepare($query_beneficiarios);
$stmt_beneficiarios->execute();
$beneficiarios = $stmt_beneficiarios->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beneficiarios - Sistema de Préstamos</title>
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
                Gestion beneficiario
            </h1>
            <p class="lead text-white-50">

            </p>
        </div>

        <!-- MENSAJES -->
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>Beneficiario registrado exitosamente.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <?php
                $msg = isset($_GET['msg']) ? $_GET['msg'] : '';
                switch ($msg) {
                    case 'dni_duplicado':
                        $beneficiario = isset($_GET['beneficiario']) ? $_GET['beneficiario'] : '';
                        echo "El DNI ya está registrado";
                        if ($beneficiario) {
                            echo " para: <strong>" . htmlspecialchars(urldecode($beneficiario)) . "</strong>";
                        }
                        break;
                    case 'campos_vacios':
                        echo "Por favor, complete todos los campos obligatorios.";
                        break;
                    case 'dni_invalido':
                        echo "El DNI debe tener exactamente 8 dígitos numéricos.";
                        break;
                    case 'telefono_invalido':
                        echo "El teléfono debe tener exactamente 9 dígitos numéricos.";
                        break;
                    case 'error_sql':
                        echo "Error en la base de datos. ";
                        if (isset($_GET['code'])) {
                            echo "Código: " . htmlspecialchars($_GET['code']);
                        }
                        break;
                    case 'error_db':
                        echo "Error de conexión con la base de datos.";
                        if (isset($_GET['detail'])) {
                            echo "<br><small>" . htmlspecialchars(urldecode($_GET['detail'])) . "</small>";
                        }
                        break;
                    default:
                        echo "Error al registrar el beneficiario. Por favor, intente nuevamente.";
                }
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- CARD PRINCIPAL -->
        <div class="card card-custom">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-list me-2"></i>Lista de Beneficiarios
                </h5>
                <button class="btn btn-light btn-custom" data-bs-toggle="modal" data-bs-target="#modalBeneficiario">
                    <i class="fas fa-plus me-2"></i>Registrar Beneficiario
                </button>
            </div>
            <div class="card-body">
                <?php if (count($beneficiarios) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Apellidos y Nombres</th>
                                    <th>DNI</th>
                                    <th>Teléfono</th>
                                    <th>Dirección</th>
                                    <th>Fecha Registro</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($beneficiarios as $index => $beneficiario): ?>
                                    <tr>
                                        <td><?= $index + 1 ?></td>
                                        <td>
                                            <strong><?= htmlspecialchars($beneficiario['apellidos'] . ', ' . $beneficiario['nombres']) ?></strong>
                                        </td>
                                        <td><?= htmlspecialchars($beneficiario['dni']) ?></td>
                                        <td><?= htmlspecialchars($beneficiario['telefono']) ?></td>
                                        <td><?= htmlspecialchars($beneficiario['direccion'] ?: 'No registrada') ?></td>
                                        <td><?= date('d/m/Y', strtotime($beneficiario['creado'])) ?></td>
                                        <td>

                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-users fa-3x text-muted mb-3"></i>
                        <h4 class="text-muted">No hay beneficiarios registrados</h4>
                        <p class="text-muted">Comienza registrando el primer beneficiario</p>
                        <button class="btn btn-primary btn-custom" data-bs-toggle="modal" data-bs-target="#modalBeneficiario">
                            <i class="fas fa-plus me-2"></i>Registrar Primer Beneficiario
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
                            <div class="col-md-4">
                                <i class="fas fa-users fa-2x text-primary mb-2"></i>
                                <h3 class="text-primary"><?= count($beneficiarios) ?></h3>
                                <p class="text-muted">Total Beneficiarios</p>
                            </div>
                            <div class="col-md-4">
                                <i class="fas fa-calendar fa-2x text-success mb-2"></i>
                                <h3 class="text-success"><?= count($beneficiarios) > 0 ? date('d/m/Y', strtotime(end($beneficiarios)['creado'])) : 'N/A' ?></h3>
                                <p class="text-muted">Último Registro</p>
                            </div>
                            <div class="col-md-4">
                                <i class="fas fa-chart-line fa-2x text-info mb-2"></i>
                                <h3 class="text-info"><?= date('m/Y') ?></h3>
                                <p class="text-muted">Mes Actual</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Registrar Beneficiario -->
    <div class="modal fade" id="modalBeneficiario" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-user-plus me-2"></i>Registrar Beneficiario
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="procesar_beneficiario.php" method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="apellidos" class="form-label">
                                <i class="fas fa-user me-1"></i>Apellidos *
                            </label>
                            <input type="text" class="form-control" id="apellidos" name="apellidos" required>
                        </div>
                        <div class="mb-3">
                            <label for="nombres" class="form-label">
                                <i class="fas fa-user me-1"></i>Nombres *
                            </label>
                            <input type="text" class="form-control" id="nombres" name="nombres" required>
                        </div>
                        <div class="mb-3">
                            <label for="dni" class="form-label">
                                <i class="fas fa-id-card me-1"></i>DNI *
                            </label>
                            <input type="text" class="form-control" id="dni" name="dni" maxlength="8" pattern="[0-9]{8}" required>
                        </div>
                        <div class="mb-3">
                            <label for="telefono" class="form-label">
                                <i class="fas fa-phone me-1"></i>Teléfono *
                            </label>
                            <input type="text" class="form-control" id="telefono" name="telefono" maxlength="9" pattern="[0-9]{9}" required>
                        </div>
                        <div class="mb-3">
                            <label for="direccion" class="form-label">
                                <i class="fas fa-map-marker-alt me-1"></i>Dirección
                            </label>
                            <textarea class="form-control" id="direccion" name="direccion" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i>Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Registrar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function verBeneficiario(id) {
            alert('Ver detalles del beneficiario ID: ' + id);
            // Aquí puedes implementar la funcionalidad para ver detalles
        }

        function editarBeneficiario(id) {
            alert('Editar beneficiario ID: ' + id);
            // Aquí puedes implementar la funcionalidad para editar
        }
    </script>
</body>

</html>