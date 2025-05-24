<?php
class Pago {
    private $conn;
    private $table_name = "pagos";
    
    // Propiedades
    public $idpago;
    public $idcontrato;
    public $numcuota;
    public $monto;
    public $fechapago;
    public $penalidad;
    public $medio;
    
    // Constructor
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Listar pagos por contrato
    public function listarPorContrato() {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE idcontrato = :idcontrato 
                  ORDER BY numcuota";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":idcontrato", $this->idcontrato);
        $stmt->execute();
        
        return $stmt;
    }
    
    // Registrar pago
    public function registrarPago() {
        $query = "UPDATE " . $this->table_name . "
                  SET fechapago = :fechapago,
                      penalidad = :penalidad,
                      medio = :medio
                  WHERE idpago = :idpago";
        
        $stmt = $this->conn->prepare($query);
        
        // Bind datos
        $stmt->bindParam(":fechapago", $this->fechapago);
        $stmt->bindParam(":penalidad", $this->penalidad);
        $stmt->bindParam(":medio", $this->medio);
        $stmt->bindParam(":idpago", $this->idpago);
        
        if($stmt->execute()) {
            // Verificar si se completaron todos los pagos
            $this->verificarContratoCompleto();
            return true;
        }
        
        return false;
    }
    
    // Verificar si el contrato está completo
    private function verificarContratoCompleto() {
        $query = "SELECT COUNT(*) as pendientes 
                  FROM " . $this->table_name . "
                  WHERE idcontrato = :idcontrato AND fechapago IS NULL";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":idcontrato", $this->idcontrato);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Si no hay pagos pendientes, actualizar estado del contrato
        if($row['pendientes'] == 0) {
            $query_update = "UPDATE contratos SET estado = 'FIN' WHERE idcontrato = :idcontrato";
            $stmt_update = $this->conn->prepare($query_update);
            $stmt_update->bindParam(":idcontrato", $this->idcontrato);
            $stmt_update->execute();
        }
    }
    
    // Obtener resumen de pagos
    public function obtenerResumen() {
        $query = "SELECT 
                    COUNT(*) as total_cuotas,
                    COUNT(fechapago) as cuotas_pagadas,
                    SUM(CASE WHEN fechapago IS NOT NULL THEN monto + COALESCE(penalidad, 0) ELSE 0 END) as total_pagado,
                    SUM(CASE WHEN fechapago IS NULL THEN monto ELSE 0 END) as total_pendiente
                  FROM " . $this->table_name . "
                  WHERE idcontrato = :idcontrato";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":idcontrato", $this->idcontrato);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>