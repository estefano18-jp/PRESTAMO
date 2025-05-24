<?php
class Contrato {
    private $conn;
    private $table_name = "contratos";
    
    // Propiedades
    public $idcontrato;
    public $idbeneficiario;
    public $monto;
    public $interes;
    public $fechainicio;
    public $diapago;
    public $numcuotas;
    public $estado;
    public $creado;
    
    // Constructor
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Listar todos los contratos
    public function listar() {
        $query = "SELECT c.*, 
                         CONCAT(b.apellidos, ', ', b.nombres) as beneficiario_nombre,
                         b.dni
                  FROM " . $this->table_name . " c
                  INNER JOIN beneficiarios b ON c.idbeneficiario = b.idbeneficiario
                  ORDER BY c.creado DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
    
    // Listar contratos activos
    public function listarActivos() {
        $query = "SELECT c.*, 
                         CONCAT(b.apellidos, ', ', b.nombres) as beneficiario_nombre
                  FROM " . $this->table_name . " c
                  INNER JOIN beneficiarios b ON c.idbeneficiario = b.idbeneficiario
                  WHERE c.estado = 'ACT'
                  ORDER BY c.creado DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
    
    // Crear contrato
    public function crear() {
        // Iniciar transacción
        $this->conn->beginTransaction();
        
        try {
            // Insertar contrato
            $query = "INSERT INTO " . $this->table_name . "
                      SET idbeneficiario=:idbeneficiario,
                          monto=:monto,
                          interes=:interes,
                          fechainicio=:fechainicio,
                          diapago=:diapago,
                          numcuotas=:numcuotas";
            
            $stmt = $this->conn->prepare($query);
            
            // Bind datos
            $stmt->bindParam(":idbeneficiario", $this->idbeneficiario);
            $stmt->bindParam(":monto", $this->monto);
            $stmt->bindParam(":interes", $this->interes);
            $stmt->bindParam(":fechainicio", $this->fechainicio);
            $stmt->bindParam(":diapago", $this->diapago);
            $stmt->bindParam(":numcuotas", $this->numcuotas);
            
            if(!$stmt->execute()) {
                throw new Exception("Error al crear contrato");
            }
            
            // Obtener ID del contrato creado
            $this->idcontrato = $this->conn->lastInsertId();
            
            // Crear cronograma de pagos
            if($this->crearCronograma()) {
                $this->conn->commit();
                return true;
            } else {
                throw new Exception("Error al crear cronograma");
            }
            
        } catch(Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }
    
    // Crear cronograma de pagos
    private function crearCronograma() {
        // Calcular cuota mensual
        if ($this->interes > 0) {
            $tasaMensual = $this->interes / 100 / 12;
            $cuotaMensual = $this->monto * ($tasaMensual * pow(1 + $tasaMensual, $this->numcuotas)) / 
                           (pow(1 + $tasaMensual, $this->numcuotas) - 1);
        } else {
            $cuotaMensual = $this->monto / $this->numcuotas;
        }
        
        $query = "INSERT INTO pagos (idcontrato, numcuota, monto) VALUES (:idcontrato, :numcuota, :monto)";
        $stmt = $this->conn->prepare($query);
        
        for ($i = 1; $i <= $this->numcuotas; $i++) {
            $stmt->bindParam(":idcontrato", $this->idcontrato);
            $stmt->bindParam(":numcuota", $i);
            $stmt->bindParam(":monto", $cuotaMensual);
            
            if(!$stmt->execute()) {
                return false;
            }
        }
        
        return true;
    }
    
    // Obtener contrato por ID
    public function obtenerPorId() {
        $query = "SELECT c.*, 
                         CONCAT(b.apellidos, ', ', b.nombres) as beneficiario_nombre,
                         b.dni,
                         b.telefono
                  FROM " . $this->table_name . " c
                  INNER JOIN beneficiarios b ON c.idbeneficiario = b.idbeneficiario
                  WHERE c.idcontrato = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->idcontrato);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>