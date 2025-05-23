<?php
require_once 'db/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $database = new Database();
    $db = $database->getConnection();

    try {
        // Recibir datos del formulario
        $idpago = intval($_POST['idpago']);
        $idcontrato = intval($_POST['idcontrato']);
        $fechapago = $_POST['fechapago'] . ' ' . date('H:i:s'); // Agregar hora actual
        $medio = $_POST['medio'];
        $penalidad = floatval($_POST['penalidad']);

        // Actualizar el pago
        $query = "UPDATE pagos 
                  SET fechapago = ?, 
                      penalidad = ?, 
                      medio = ? 
                  WHERE idpago = ?";
        
        $stmt = $db->prepare($query);
        $stmt->execute([$fechapago, $penalidad, $medio, $idpago]);

        // Verificar si todas las cuotas están pagadas
        $query_check = "SELECT COUNT(*) as pendientes 
                        FROM pagos 
                        WHERE idcontrato = ? AND fechapago IS NULL";
        $stmt_check = $db->prepare($query_check);
        $stmt_check->execute([$idcontrato]);
        $result = $stmt_check->fetch(PDO::FETCH_ASSOC);

        // Si no hay cuotas pendientes, actualizar estado del contrato a FIN
        if ($result['pendientes'] == 0) {
            $query_update = "UPDATE contratos SET estado = 'FIN' WHERE idcontrato = ?";
            $stmt_update = $db->prepare($query_update);
            $stmt_update->execute([$idcontrato]);
        }

        // Redireccionar con mensaje de éxito
        header('Location: pagos.php?success=1&contrato=' . $idcontrato);
        exit();

    } catch (Exception $e) {
        // En caso de error, redireccionar con mensaje de error
        header('Location: pagos.php?error=1&contrato=' . $idcontrato);
        exit();
    }
} else {
    // Si no es POST, redireccionar
    header('Location: pagos.php');
    exit();
}
?>