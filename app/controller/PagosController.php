<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once '../../db/database.php';
require_once '../models/Pago.php';

$database = new Database();
$db = $database->getConnection();
$pago = new Pago($db);

// Determinar método de solicitud
$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        // Listar pagos por contrato
        if(isset($_GET['idcontrato'])) {
            $pago->idcontrato = $_GET['idcontrato'];
            
            // Obtener cronograma de pagos
            $stmt = $pago->listarPorContrato();
            $num = $stmt->rowCount();
            
            // Obtener resumen
            $resumen = $pago->obtenerResumen();
            
            if($num > 0) {
                $pagos_arr = array();
                $pagos_arr["success"] = true;
                $pagos_arr["resumen"] = $resumen;
                $pagos_arr["data"] = array();
                
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    extract($row);
                    
                    $pago_item = array(
                        "idpago" => $idpago,
                        "idcontrato" => $idcontrato,
                        "numcuota" => $numcuota,
                        "monto" => $monto,
                        "fechapago" => $fechapago,
                        "penalidad" => $penalidad,
                        "medio" => $medio,
                        "estado" => $fechapago ? "PAGADO" : "PENDIENTE"
                    );
                    
                    array_push($pagos_arr["data"], $pago_item);
                }
                
                http_response_code(200);
                echo json_encode($pagos_arr);
            } else {
                http_response_code(200);
                echo json_encode(array("success" => true, "data" => array()));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("success" => false, "message" => "ID de contrato requerido"));
        }
        break;
        
    case 'POST':
        // Registrar pago
        $data = json_decode(file_get_contents("php://input"));
        
        if(!empty($data->idpago) && !empty($data->idcontrato) && !empty($data->fechapago) && !empty($data->medio)) {
            
            $pago->idpago = $data->idpago;
            $pago->idcontrato = $data->idcontrato;
            $pago->fechapago = $data->fechapago . ' ' . date('H:i:s');
            $pago->penalidad = isset($data->penalidad) ? $data->penalidad : 0;
            $pago->medio = $data->medio;
            
            // Registrar pago
            if($pago->registrarPago()) {
                http_response_code(200);
                echo json_encode(array("success" => true, "message" => "Pago registrado exitosamente"));
            } else {
                http_response_code(503);
                echo json_encode(array("success" => false, "message" => "No se pudo registrar el pago"));
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