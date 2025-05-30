<?php
// Incluye el archivo de conexión a la base de datos
require_once '../../db/database.php';
// Crea una instancia de la clase Database
$database = new Database();
// Obtiene la conexión PDO a la base de datos
$db = $database->getConnection();

// Consulta SQL para obtener todos los beneficiarios ordenados por apellidos y nombres
$query_beneficiarios = "SELECT * FROM beneficiarios ORDER BY apellidos, nombres";
// Prepara la consulta SQL
$stmt_beneficiarios = $db->prepare($query_beneficiarios);
// Ejecuta la consulta
$stmt_beneficiarios->execute();
// Obtiene todos los resultados como un arreglo asociativo
$beneficiarios = $stmt_beneficiarios->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8"> <!-- Define la codificación de caracteres -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Hace la página responsiva -->
    <title>Beneficiarios - Sistema de Préstamos</title> <!-- Título de la pestaña -->
    <!-- Incluye Bootstrap para estilos -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Incluye FontAwesome para iconos -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../css/beneficiarios.css">
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
                Gestion beneficiario
            </h1>
            <p class="lead text-white-50">
                <!-- Aquí puedes poner una descripción -->
            </p>
        </div>

        <!-- Mensaje de éxito al registrar un beneficiario -->
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>Beneficiario registrado exitosamente.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Mensaje de error si ocurre algún problema -->
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <?php
                // Muestra el mensaje de error correspondiente según el tipo
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

        <!-- Tarjeta principal con la lista de beneficiarios -->
        <div class="card card-custom">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-list me-2"></i>Lista de Beneficiarios
                </h5>
                <!-- Botón para abrir el modal de registro -->
                <button class="btn btn-light btn-custom" data-bs-toggle="modal" data-bs-target="#modalBeneficiario">
                    <i class="fas fa-plus me-2"></i>Registrar Beneficiario
                </button>
            </div>
            <div class="card-body">
                <?php if (count($beneficiarios) > 0): ?>
                    <!-- Tabla de beneficiarios -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>#</th> <!-- Número de fila -->
                                    <th>Apellidos y Nombres</th> <!-- Apellidos y nombres del beneficiario -->
                                    <th>DNI</th> <!-- DNI del beneficiario -->
                                    <th>Teléfono</th> <!-- Teléfono del beneficiario -->
                                    <th>Dirección</th> <!-- Dirección del beneficiario -->
                                    <th>Fecha Registro</th> <!-- Fecha de registro del beneficiario -->
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Recorre cada beneficiario y muestra sus datos -->
                                <?php foreach ($beneficiarios as $index => $beneficiario): ?>
                                    <tr>
                                        <td><?= $index + 1 ?></td> <!-- Muestra el número de fila -->
                                        <td>
                                            <strong><?= htmlspecialchars($beneficiario['apellidos'] . ', ' . $beneficiario['nombres']) ?></strong>
                                            <!-- Muestra apellidos y nombres en negrita y protegidos contra XSS -->
                                        </td>
                                        <td><?= htmlspecialchars($beneficiario['dni']) ?></td> <!-- Muestra el DNI protegido -->
                                        <td><?= htmlspecialchars($beneficiario['telefono']) ?></td>
                                        <!-- Muestra el teléfono protegido -->
                                        <td><?= htmlspecialchars($beneficiario['direccion'] ?: 'No registrada') ?></td>
                                        <!-- Muestra la dirección o "No registrada" -->
                                        <td><?= date('d/m/Y', strtotime($beneficiario['creado'])) ?></td>
                                        <!-- Muestra la fecha de registro en formato día/mes/año -->
                                        <td>
                                            <!-- Aquí puedes agregar botones de acción (ver, editar, eliminar) -->
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <!-- Mensaje si no hay beneficiarios registrados -->
                    <div class="text-center py-5">
                        <i class="fas fa-users fa-3x text-muted mb-3"></i>
                        <h4 class="text-muted">No hay beneficiarios registrados</h4>
                        <p class="text-muted">Comienza registrando el primer beneficiario</p>
                        <button class="btn btn-primary btn-custom" data-bs-toggle="modal"
                            data-bs-target="#modalBeneficiario">
                            <i class="fas fa-plus me-2"></i>Registrar Primer Beneficiario
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Estadísticas de beneficiarios -->
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card card-custom">
                    <div class="card-body text-center">
                        <div class="row">
                            <!-- Total de beneficiarios -->
                            <div class="col-md-4">
                                <i class="fas fa-users fa-2x text-primary mb-2"></i>
                                <h3 class="text-primary"><?= count($beneficiarios) ?></h3>
                                <!-- Muestra el total de beneficiarios -->
                                <p class="text-muted">Total Beneficiarios</p>
                            </div>
                            <!-- Fecha del último registro -->
                            <div class="col-md-4">
                                <i class="fas fa-calendar fa-2x text-success mb-2"></i>
                                <h3 class="text-success">
                                    <?= count($beneficiarios) > 0 ? date('d/m/Y', strtotime(end($beneficiarios)['creado'])) : 'N/A' ?>
                                    <!-- Muestra la fecha del último registro o N/A si no hay beneficiarios -->
                                </h3>
                                <p class="text-muted">Último Registro</p>
                            </div>
                            <!-- Mes actual -->
                            <div class="col-md-4">
                                <i class="fas fa-chart-line fa-2x text-info mb-2"></i>
                                <h3 class="text-info"><?= date('m/Y') ?></h3> <!-- Muestra el mes y año actual -->
                                <p class="text-muted">Mes Actual</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para registrar un nuevo beneficiario -->
    <div class="modal fade" id="modalBeneficiario" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-user-plus me-2"></i>Registrar Beneficiario
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <!-- Formulario de registro de beneficiario -->
                <form action="procesar_beneficiario.php" method="POST">
                    <div class="modal-body">
                        <!-- Campo Apellidos -->
                        <div class="mb-3">
                            <label for="apellidos" class="form-label">
                                <i class="fas fa-user me-1"></i>Apellidos *
                            </label>
                            <input type="text" class="form-control" id="apellidos" name="apellidos" required>
                        </div>
                        <!-- Campo Nombres -->
                        <div class="mb-3">
                            <label for="nombres" class="form-label">
                                <i class="fas fa-user me-1"></i>Nombres *
                            </label>
                            <input type="text" class="form-control" id="nombres" name="nombres" required>
                        </div>
                        <!-- Campo DNI -->
                        <div class="mb-3">
                            <label for="dni" class="form-label">
                                <i class="fas fa-id-card me-1"></i>DNI *
                            </label>
                            <input type="text" class="form-control" id="dni" name="dni" maxlength="8" pattern="[0-9]{8}"
                                required>
                        </div>
                        <!-- Campo Teléfono -->
                        <div class="mb-3">
                            <label for="telefono" class="form-label">
                                <i class="fas fa-phone me-1"></i>Teléfono *
                            </label>
                            <input type="text" class="form-control" id="telefono" name="telefono" maxlength="9"
                                pattern="[0-9]{9}" required>
                        </div>
                        <!-- Campo Dirección -->
                        <div class="mb-3">
                            <label for="direccion" class="form-label">
                                <i class="fas fa-map-marker-alt me-1"></i>Dirección
                            </label>
                            <textarea class="form-control" id="direccion" name="direccion" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <!-- Botón para cancelar -->
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-1"></i>Cancelar
                        </button>
                        <!-- Botón para registrar -->
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Registrar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts de Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Función para ver detalles de un beneficiario (a implementar)
        function verBeneficiario(id) {
            alert('Ver detalles del beneficiario ID: ' + id);
            // Aquí puedes implementar la funcionalidad para ver detalles
        }

        // Función para editar un beneficiario (a implementar)
        function editarBeneficiario(id) {
            alert('Editar beneficiario ID: ' + id);
            // Aquí puedes implementar la funcionalidad para editar
        }
    </script>
</body>

</html>