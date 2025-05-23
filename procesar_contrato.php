<?php
require_once 'db/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $database = new Database();
    $db = $database->getConnection();

    try {
        // Recibir datos del formulario
        $idbeneficiario = intval($_POST['idbeneficiario']);
        $monto = floatval($_POST['monto']);
        $cuotas = intval($_POST['cuotas']); // Este será numcuotas en la BD
        $tasa = floatval($_POST['tasa']); // Este será interes en la BD
        $fechainicio = $_POST['fechainicio'];
        $observaciones = isset($_POST['observaciones']) ? trim($_POST['observaciones']) : '';

        // Calcular día de pago (15 por defecto, puedes cambiarlo)
        $diapago = 15;

        // Iniciar transacción
        $db->beginTransaction();

        // Insertar contrato
        $query_contrato = "INSERT INTO contratos 
                          (idbeneficiario, monto, interes, fechainicio, diapago, numcuotas) 
                          VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt_contrato = $db->prepare($query_contrato);
        $stmt_contrato->execute([
            $idbeneficiario, 
            $monto, 
            $tasa, 
            $fechainicio, 
            $diapago, 
            $cuotas
        ]);

        // Obtener el ID del contrato insertado
        $idcontrato = $db->lastInsertId();

        // Calcular monto de cada cuota
        if ($tasa > 0) {
            $tasaMensual = $tasa / 100 / 12;
            $cuotaMensual = $monto * ($tasaMensual * pow(1 + $tasaMensual, $cuotas)) / 
                           (pow(1 + $tasaMensual, $cuotas) - 1);
        } else {
            $cuotaMensual = $monto / $cuotas;
        }

        // Crear el cronograma de pagos
        $query_pagos = "INSERT INTO pagos (idcontrato, numcuota, monto) VALUES (?, ?, ?)";
        $stmt_pagos = $db->prepare($query_pagos);

        for ($i = 1; $i <= $cuotas; $i++) {
            $stmt_pagos->execute([$idcontrato, $i, $cuotaMensual]);
        }

        // Confirmar transacción
        $db->commit();

        // Redireccionar con mensaje de éxito
        header('Location: contratos.php?success=1');
        exit();

    } catch (Exception $e) {
        // Revertir transacción en caso de error
        $db->rollBack();
        
        // Redireccionar con mensaje de error
        header('Location: contratos.php?error=1');
        exit();
    }
} else {
    // Si no es POST, redireccionar
    header('Location: contratos.php');
    exit();
}
?>