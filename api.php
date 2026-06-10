<?php
header("Content-Type: application/json");

// Permitir peticiones desde tu index.html local si es necesario
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS");

// Conexión a la base de datos (Ajusta los datos si usas MySQL local)
$conn = new mysqli("localhost", "root", "", "clinica_db");

// Si falla la conexión, terminamos con estilo
if ($conn->connect_error) {
    die(json_encode(["error" => "Error de conexión: " . $conn->connect_error]));
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// Manejar peticiones OPTIONS (Preflight de los navegadores)
if ($method === 'OPTIONS') {
    exit(0);
}

if ($method == 'GET') {
    $res = $conn->query("SELECT * FROM fisioterapeutas");
    if ($res) {
        echo json_encode($res->fetch_all(MYSQLI_ASSOC));
    } else {
        echo json_encode([]);
    }
} 

elseif ($method == 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    
    // Validar que los datos no vengan vacíos
    if (!empty($data['nombre']) && !empty($data['especialidad'])) {
        $stmt = $conn->prepare("INSERT INTO fisioterapeutas (nombre, especialidad, turno, cedula, costo) VALUES (?,?,?,?,?)");
        $stmt->bind_param("sssss", $data['nombre'], $data['especialidad'], $data['turno'], $data['cedula'], $data['costo']);
        
        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "Especialista registrado"]);
        } else {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => $stmt->error]);
        }
        $stmt->close();
    } else {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Datos incompletos"]);
    }
}

elseif ($method == 'DELETE') {
    // Evitar el warning si no se manda ID por la URL
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    
    if ($id > 0) {
        $stmt = $conn->prepare("DELETE FROM fisioterapeutas WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "Especialista eliminado"]);
        } else {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => $stmt->error]);
        }
        $stmt->close();
    } else {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "ID no válido"]);
    }
}

$conn->close();
?>