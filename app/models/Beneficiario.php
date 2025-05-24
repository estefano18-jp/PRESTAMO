<?php
class Beneficiario {
    private $conn;
    private $table_name = "beneficiarios";
    
    // Propiedades
    public $idbeneficiario;
    public $apellidos;
    public $nombres;
    public $dni;
    public $telefono;
    public $direccion;
    public $creado;
    
    // Constructor
    public function __construct($db) {
        $this->conn = $db;
    }
    
    // Listar todos los beneficiarios
    public function listar() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY apellidos, nombres";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
    
    // Crear beneficiario
    public function crear() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET apellidos=:apellidos, 
                      nombres=:nombres, 
                      dni=:dni, 
                      telefono=:telefono, 
                      direccion=:direccion";
        
        $stmt = $this->conn->prepare($query);
        
        // Limpiar datos
        $this->apellidos = htmlspecialchars(strip_tags($this->apellidos));
        $this->nombres = htmlspecialchars(strip_tags($this->nombres));
        $this->dni = htmlspecialchars(strip_tags($this->dni));
        $this->telefono = htmlspecialchars(strip_tags($this->telefono));
        $this->direccion = htmlspecialchars(strip_tags($this->direccion));
        
        // Bind datos
        $stmt->bindParam(":apellidos", $this->apellidos);
        $stmt->bindParam(":nombres", $this->nombres);
        $stmt->bindParam(":dni", $this->dni);
        $stmt->bindParam(":telefono", $this->telefono);
        $stmt->bindParam(":direccion", $this->direccion);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }
    
    // Verificar si DNI existe
    public function dniExiste() {
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name . " WHERE dni = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->dni);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row['total'] > 0) {
            return true;
        }
        return false;
    }
    
    // Obtener un beneficiario por ID
    public function obtenerPorId() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE idbeneficiario = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->idbeneficiario);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row) {
            $this->apellidos = $row['apellidos'];
            $this->nombres = $row['nombres'];
            $this->dni = $row['dni'];
            $this->telefono = $row['telefono'];
            $this->direccion = $row['direccion'];
            $this->creado = $row['creado'];
            return true;
        }
        return false;
    }
}
?>