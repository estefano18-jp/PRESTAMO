<?php
require_once 'db/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $database = new Database();
    $db = $database->getConnection();

    try {
        // Recibir datos del formulario
        $apellidos = trim($_POST['apellidos']);
        $nombres = trim($_POST['nombres']);
        $dni = trim($_POST['dni']);
        $telefono = trim($_POST['telefono']);
        $direccion = isset($_POST['direccion']) ? trim($_POST['direccion']) : null;

        // Validar que el DNI no exista
        $query_check = "SELECT COUNT(*) as total FROM beneficiarios WHERE dni = ?";
        $stmt_check = $db->prepare($query_check);
        $stmt_check->execute([$dni]);
        $result = $stmt_check->fetch(PDO::FETCH_ASSOC);

        if ($result['total'] > 0) {
            header('Location: beneficiarios.php?error=1&msg=dni_exists');
            exit();
        }

        // Insertar beneficiario
        $query = "INSERT INTO beneficiarios (apellidos, nombres, dni, telefono, direccion) 
                  VALUES (?, ?, ?, ?, ?)";
        
        $stmt = $db->prepare($query);
        $stmt->execute([$apellidos, $nombres, $dni, $telefono, $direccion]);

        // Redireccionar con mensaje de éxito
        header('Location: beneficiarios.php?success=1');
        exit();

    } catch (Exception $e) {
        // En caso de error, redireccionar con mensaje de error
        header('Location: beneficiarios.php?error=1');
        exit();
    }
} else {
    // Si no es POST, redireccionar
    header('Location: beneficiarios.php');
    exit();
}
?>