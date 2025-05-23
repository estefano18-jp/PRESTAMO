    <?php
    require_once 'db/database.php';

    $database = new Database();
    $db = $database->getConnection();

    // Obtener estadísticas generales
    $query_stats = "
        SELECT 
            (SELECT COUNT(*) FROM beneficiarios) as total_beneficiarios,
            (SELECT COUNT(*) FROM contratos) as total_contratos,
            (SELECT COUNT(*) FROM contratos WHERE estado = 'ACT') as contratos_activos,
            (SELECT COUNT(*) FROM pagos WHERE fechapago IS NULL) as pagos_pendientes,
            (SELECT COUNT(*) FROM pagos WHERE fechapago IS NOT NULL) as pagos_realizados,
            (SELECT SUM(monto) FROM contratos WHERE estado = 'ACT') as monto_total_activo
    ";
    $stmt_stats = $db->prepare($query_stats);
    $stmt_stats->execute();
    $stats = $stmt_stats->fetch(PDO::FETCH_ASSOC);
    ?>

    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Dashboard - Sistema de Préstamos</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    </head>
    <body>
        <!-- NAVBAR -->
        <nav class="navbar navbar-expand-lg navbar-custom mb-4">
            <div class="container">
                <a class="navbar-brand d-flex align-items-center" href="index.php">
                    <i class="fas fa-handshake me-2 fs-4"></i>
                    <span class="fw-bold">Sistema de Préstamos</span>
                </a>
                <div class="navbar-nav ms-auto">
                    <span class="nav-link">
                        <i class="fas fa-calendar-alt me-1"></i>
                        <?= date('d/m/Y') ?>
                    </span>
                </div>
            </div>
        </nav>

        <div class="container">
            <!-- TÍTULO DE BIENVENIDA -->
            <div class="text-center mb-5">
                <h1 class="welcome-title display-4 fw-bold">
                    Dashboard de Préstamos
                </h1>
                <p class="lead text-white-50">
                    Gestiona beneficiarios, contratos y pagos desde un solo lugar
                </p>
            </div>

            <!-- ESTADÍSTICAS RÁPIDAS -->
            <div class="row mb-5">
                <div class="col-md-3 mb-3">
                    <div class="card stats-card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-users icon-large text-info"></i>
                            <h3 class="fw-bold"><?= $stats['total_beneficiarios'] ?></h3>
                            <p class="mb-0">Beneficiarios</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card stats-card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-file-contract icon-large text-success"></i>
                            <h3 class="fw-bold"><?= $stats['contratos_activos'] ?></h3>
                            <p class="mb-0">Contratos Activos</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card stats-card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-clock icon-large text-warning"></i>
                            <h3 class="fw-bold"><?= $stats['pagos_pendientes'] ?></h3>
                            <p class="mb-0">Pagos Pendientes</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="card stats-card h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-money-bill-wave icon-large text-primary"></i>
                            <h3 class="fw-bold">S/ <?= number_format($stats['monto_total_activo'] ?: 0, 0) ?></h3>
                            <p class="mb-0">Monto Total</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- BOTONES PRINCIPALES -->
            <div class="row justify-content-center">
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card dashboard-card h-100">
                        <div class="card-body text-center p-4">
                            <a href="beneficiarios.php" class="btn btn-dashboard btn-beneficiarios w-100">
                                <i class="fas fa-users icon-large d-block"></i>
                                <h4 class="mb-2">BENEFICIARIOS</h4>
                                <p class="mb-0">Gestionar personas beneficiarias del sistema</p>
                                <div class="mt-3">
                                    <small class="opacity-75">
                                        <i class="fas fa-plus me-1"></i> Registrar nuevo
                                        <br>
                                        <i class="fas fa-list me-1"></i> Ver listado completo
                                    </small>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card dashboard-card h-100">
                        <div class="card-body text-center p-4">
                            <a href="contratos.php" class="btn btn-dashboard btn-contratos w-100">
                                <i class="fas fa-file-contract icon-large d-block"></i>
                                <h4 class="mb-2">CONTRATOS</h4>
                                <p class="mb-0">Administrar contratos de préstamos</p>
                                <div class="mt-3">
                                    <small class="opacity-75">
                                        <i class="fas fa-plus me-1"></i> Crear contrato
                                        <br>
                                        <i class="fas fa-edit me-1"></i> Gestionar existentes
                                    </small>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card dashboard-card h-100">
                        <div class="card-body text-center p-4">
                            <a href="pagos.php" class="btn btn-dashboard btn-pagos w-100">
                                <i class="fas fa-calendar-check icon-large d-block"></i>
                                <h4 class="mb-2">CRONOGRAMA PAGOS</h4>
                                <p class="mb-0">Ver cronogramas y registrar pagos</p>
                                <div class="mt-3">
                                    <small class="opacity-75">
                                        <i class="fas fa-calendar-alt me-1"></i> Ver cronogramas
                                        <br>
                                        <i class="fas fa-money-check me-1"></i> Registrar pagos
                                    </small>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            

            <!-- FOOTER -->
            <div class="text-center mt-5 pb-4">
                <p class="text-white-50">
                    <i class="fas fa-code me-1"></i>
                    Sistema de Préstamos - Desarrollado con PHP & Bootstrap
                    <br>
                    <small><?= date('Y') ?> - Todos los derechos reservados</small>
                </p>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
        <script>
            // Efecto de animación al cargar la página
            document.addEventListener('DOMContentLoaded', function() {
                const cards = document.querySelectorAll('.dashboard-card');
                cards.forEach((card, index) => {
                    card.style.opacity = '0';
                    card.style.transform = 'translateY(30px)';
                    
                    setTimeout(() => {
                        card.style.transition = 'all 0.6s ease';
                        card.style.opacity = '1';
                        card.style.transform = 'translateY(0)';
                    }, index * 200);
                });
            });

            // Efecto de partículas en el fondo (opcional)
            function createParticle() {
                const particle = document.createElement('div');
                particle.style.position = 'fixed';
                particle.style.width = '4px';
                particle.style.height = '4px';
                particle.style.background = 'rgba(255,255,255,0.5)';
                particle.style.borderRadius = '50%';
                particle.style.pointerEvents = 'none';
                particle.style.left = Math.random() * window.innerWidth + 'px';
                particle.style.top = '-10px';
                particle.style.animation = 'fall 8s linear infinite';
                
                document.body.appendChild(particle);
                
                setTimeout(() => {
                    particle.remove();
                }, 8000);
            }

            // Crear partículas ocasionalmente
            setInterval(createParticle, 3000);

            // CSS para la animación de partículas
            const style = document.createElement('style');
            style.textContent = `
                @keyframes fall {
                    0% { transform: translateY(-10px) rotate(0deg); opacity: 1; }
                    100% { transform: translateY(100vh) rotate(360deg); opacity: 0; }
                }
            `;
            document.head.appendChild(style);
        </script>
    </body>
    </html>