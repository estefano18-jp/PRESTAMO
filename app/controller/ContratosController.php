<?php
// Permite el acceso desde cualquier origen (CORS abierto para desarrollo o APIs públicas)
header("Access-Control-Allow-Origin: *");
// Indica que la respuesta será en formato JSON y con codificación UTF-8
header("Content-Type: application/json; charset=UTF-8");
// Especifica los métodos HTTP permitidos para esta API (POST y GET)
header("Access-Control-Allow-Methods: POST, GET");
// Indica cuánto tiempo (en segundos) el navegador puede cachear la respuesta de preflight (opciones CORS)
header("Access-Control-Max-Age: 3600");
// Define los encabezados permitidos en las solicitudes a esta API
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once '../../db/database.php';
require_once '../models/Contrato.php';

$database = new Database();
$db = $database->getConnection();
$contrato = new Contrato($db);

// Determinar método de solicitud
$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        // Listar contratos
        if(isset($_GET['id'])) {
            // Obtener un contrato específico
            $contrato->idcontrato = $_GET['id'];
            $contrato_data = $contrato->obtenerPorId();
            
            if($contrato_data) {
                http_response_code(200);
                echo json_encode(array("success" => true, "data" => $contrato_data));
            } else {
                http_response_code(404);
                echo json_encode(array("success" => false, "message" => "Contrato no encontrado"));
            }
        } else if(isset($_GET['activos'])) {
            // Listar solo contratos activos
            $stmt = $contrato->listarActivos();
            $num = $stmt->rowCount();
            
            if($num > 0) {
                $contratos_arr = array();
                $contratos_arr["success"] = true;
                $contratos_arr["data"] = array();
                
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    array_push($contratos_arr["data"], $row);
                }
                
                http_response_code(200);
                echo json_encode($contratos_arr);
            } else {
                http_response_code(200);
                echo json_encode(array("success" => true, "data" => array()));
            }
        } else {
            // Listar todos los contratos
            $stmt = $contrato->listar();
            $num = $stmt->rowCount();
            
            if($num > 0) {
                $contratos_arr = array();
                $contratos_arr["success"] = true;
                $contratos_arr["data"] = array();
                
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    extract($row);
                    
                    $contrato_item = array(
                        "idcontrato" => $idcontrato,
                        "idbeneficiario" => $idbeneficiario,
                        "beneficiario_nombre" => $beneficiario_nombre,
                        "dni" => $dni,
                        "monto" => $monto,
                        "interes" => $interes,
                        "fechainicio" => $fechainicio,
                        "diapago" => $diapago,
                        "numcuotas" => $numcuotas,
                        "estado" => $estado,
                        "creado" => $creado
                    );
                    
                    array_push($contratos_arr["data"], $contrato_item);
                }
                
                http_response_code(200);
                echo json_encode($contratos_arr);
            } else {
                http_response_code(200);
                echo json_encode(array("success" => true, "data" => array()));
            }
        }
        break;
        
    case 'POST':
        // Crear contrato
        $data = json_decode(file_get_contents("php://input"));
        
        if(!empty($data->idbeneficiario) && !empty($data->monto) && isset($data->interes) && 
           !empty($data->fechainicio) && !empty($data->numcuotas)) {
            
            $contrato->idbeneficiario = $data->idbeneficiario;
            $contrato->monto = $data->monto;
            $contrato->interes = $data->interes;
            $contrato->fechainicio = $data->fechainicio;
            $contrato->diapago = isset($data->diapago) ? $data->diapago : 15;
            $contrato->numcuotas = $data->numcuotas;
            
            // Crear contrato
            if($contrato->crear()) {
                http_response_code(201);
                echo json_encode(array(
                    "success" => true, 
                    "message" => "Contrato creado exitosamente",
                    "idcontrato" => $contrato->idcontrato
                ));
            } else {
                http_response_code(503);
                echo json_encode(array("success" => false, "message" => "No se pudo crear el contrato"));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("success" => false, "message" => "Datos incompletos"));
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(array("success" => false, "message" => "Método no permitido"));
        break;
}
?>