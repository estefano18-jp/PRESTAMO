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
require_once '../models/Beneficiario.php';

$database = new Database();
$db = $database->getConnection();
$beneficiario = new Beneficiario($db);

// Determinar método de solicitud
$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        // Listar beneficiarios
        if(isset($_GET['id'])) {
            // Obtener un beneficiario específico
            $beneficiario->idbeneficiario = $_GET['id'];
            
            if($beneficiario->obtenerPorId()) {
                $beneficiario_arr = array(
                    "idbeneficiario" => $beneficiario->idbeneficiario,
                    "apellidos" => $beneficiario->apellidos,
                    "nombres" => $beneficiario->nombres,
                    "dni" => $beneficiario->dni,
                    "telefono" => $beneficiario->telefono,
                    "direccion" => $beneficiario->direccion,
                    "creado" => $beneficiario->creado
                );
                
                http_response_code(200);
                echo json_encode(array("success" => true, "data" => $beneficiario_arr));
            } else {
                http_response_code(404);
                echo json_encode(array("success" => false, "message" => "Beneficiario no encontrado"));
            }
        } else {
            // Listar todos los beneficiarios
            $stmt = $beneficiario->listar();
            $num = $stmt->rowCount();
            
            if($num > 0) {
                $beneficiarios_arr = array();
                $beneficiarios_arr["success"] = true;
                $beneficiarios_arr["data"] = array();
                
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    extract($row);
                    
                    $beneficiario_item = array(
                        "idbeneficiario" => $idbeneficiario,
                        "apellidos" => $apellidos,
                        "nombres" => $nombres,
                        "dni" => $dni,
                        "telefono" => $telefono,
                        "direccion" => $direccion,
                        "creado" => $creado
                    );
                    
                    array_push($beneficiarios_arr["data"], $beneficiario_item);
                }
                
                http_response_code(200);
                echo json_encode($beneficiarios_arr);
            } else {
                http_response_code(200);
                echo json_encode(array("success" => true, "data" => array(), "message" => "No se encontraron beneficiarios"));
            }
        }
        break;
        
    case 'POST':
        // Crear beneficiario
        $data = json_decode(file_get_contents("php://input"));
        
        if(!empty($data->apellidos) && !empty($data->nombres) && !empty($data->dni) && !empty($data->telefono)) {
            
            $beneficiario->apellidos = $data->apellidos;
            $beneficiario->nombres = $data->nombres;
            $beneficiario->dni = $data->dni;
            $beneficiario->telefono = $data->telefono;
            $beneficiario->direccion = isset($data->direccion) ? $data->direccion : "";
            
            // Verificar si el DNI ya existe
            if($beneficiario->dniExiste()) {
                http_response_code(400);
                echo json_encode(array("success" => false, "message" => "El DNI ya está registrado"));
            } else {
                // Crear beneficiario
                if($beneficiario->crear()) {
                    http_response_code(201);
                    echo json_encode(array("success" => true, "message" => "Beneficiario creado exitosamente"));
                } else {
                    http_response_code(503);
                    echo json_encode(array("success" => false, "message" => "No se pudo crear el beneficiario"));
                }
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