<?php
/*
Este script actualiza el registro facial (embedding) de un usuario en la base de datos 'login_app_db'. 
Recibe un JSON con el 'id' y el 'registro_facial', valida que ambos estén presentes y realiza la actualización 
en la tabla 'users'. Si tiene éxito, responde con un mensaje de éxito, el ID del usuario y la cantidad de filas 
afectadas. En caso de error, devuelve un mensaje descriptivo en formato JSON.
*/

header("Content-Type: application/json; charset=UTF-8");
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configuración de conexión a la base de datos
$servername = "localhost";
$username = "ivanportador";
$password = "Mintario153";
$dbname = "asistapp";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    echo json_encode([
        "success" => false,
        "message" => "Conexión fallida: " . $conn->connect_error
    ]);
    exit;
}

// Procesar datos de entrada
$data = json_decode(file_get_contents("php://input"), true);

// Validación mínima
if (empty($data['id']) || empty($data['registro_facial'])) {
    echo json_encode([
        "success" => false,
        "message" => "Datos faltantes: se requieren id y registro_facial"
    ]);
    $conn->close();
    exit;
}

// Actualización directa
$stmt = $conn->prepare("UPDATE users SET registro_facial = ? WHERE id = ?");
if (!$stmt) {
    echo json_encode([
        "success" => false,
        "message" => "Error en la preparación: " . $conn->error
    ]);
    $conn->close();
    exit;
}

$stmt->bind_param("si", $data['registro_facial'], $data['id']);

if ($stmt->execute()) {
    // Verifica cuántas filas fueron afectadas
    $affectedRows = $stmt->affected_rows;
    
    echo json_encode([
        "success" => true,
        "id" => $data['id'],
        "affected_rows" => $affectedRows,
        "message" => "Embedding facial actualizado exitosamente"
    ]);
} else {
    echo json_encode([
        "success" => false,
        "error" => $stmt->error,
        "message" => "Error al actualizar el registro facial"
    ]);
}

// Cerrar conexiones
$stmt->close();
$conn->close();
?>
