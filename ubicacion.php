<?php
/*
Este script se conecta a una base de datos MySQL usando PDO y recibe datos JSON con un ID de usuario, 
latitud y longitud. Valida que esos datos estén presentes y, si son válidos, guarda la ubicación 
en la tabla 'ubicaciones'. Si ocurre algún error en la conexión o en la inserción, responde con 
un mensaje de error en formato JSON.
*/

$host = "localhost";
$dbname = "asistapp";
$user = "ivanportador";
$pass = "Mintario153";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Error de conexión a BD"]);
    exit();
}

// Leer JSON recibido
$data = json_decode(file_get_contents('php://input'), true);

// Validar campos (cambiado a lat y lng para que coincidan con Android)
if (!isset($data['user_id'], $data['lat'], $data['lng'])) {
    http_response_code(400);
    echo json_encode(["error" => "Datos incompletos"]);
    exit();
}

$user_id = intval($data['user_id']);
$latitude = floatval($data['lat']);
$longitude = floatval($data['lng']);

try {
    $stmt = $pdo->prepare("INSERT INTO ubicaciones (user_id, latitude, longitude) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $latitude, $longitude]);
    echo json_encode(["success" => true, "message" => "Ubicación guardada"]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Error al guardar ubicación"]);
}
?>

