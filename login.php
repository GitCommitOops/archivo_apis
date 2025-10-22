<?php
/*
Este script recibe datos JSON con un usuario y contraseña, conecta a una base de datos MySQL 
y verifica las credenciales primero en la tabla de administradores (usuarios) y luego en la 
de usuarios normales (users). Si las credenciales son correctas, responde con tokens de acceso 
y datos del usuario en formato JSON. También maneja errores como falta de datos, credenciales 
inválidas o problemas de conexión, siempre respondiendo en formato JSON.
*/
header("Content-Type: application/json; charset=UTF-8");
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Conexión
$servername = "localhost";
$dbusername = "ivanportador";
$dbpassword = "Mintario153";
$dbname = "asistapp";
$conn = new mysqli($servername, $dbusername, $dbpassword, $dbname);

if ($conn->connect_error) {
    die(json_encode([
        "success" => false, 
        "message" => "Conexión fallida: " . $conn->connect_error
    ]));
}

$data = file_get_contents("php://input");
if (!$data) {
    die(json_encode([
        "success" => false,
        "message" => "No se recibieron datos"
    ]));
}

$data = json_decode($data);
$username = $data->username ?? null;
$password = $data->password ?? null;

if (!$username || !$password) {
    die(json_encode([
        "success" => false,
        "message" => "Faltan usuario o contraseña"
    ]));
}

// Buscar en tabla de administradores
$stmt = $conn->prepare("SELECT id, username, password FROM usuarios WHERE username = ? OR email = ?");
$stmt->bind_param("ss", $username, $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $admin = $result->fetch_assoc();
    if (password_verify($password, $admin['password'])) {
        echo json_encode([
            "success" => true,
            "access" => generateAccessToken($admin['id']),
            "refresh" => generateRefreshToken($admin['id']),
            "username" => $admin['username'],
            "rol" => "admin",
            "referencedId" => $admin['id'],
            "referencedTable" => "usuarios"
        ]);
        exit;
    } else {
        echo json_encode(["success" => false, "message" => "Contraseña incorrecta"]);
        exit;
    }
}
$stmt->close();

// Buscar en tabla de usuarios normales
$stmt2 = $conn->prepare("SELECT id, nombre, contraseña FROM users WHERE nombre = ?");
$stmt2->bind_param("s", $username);
$stmt2->execute();
$result2 = $stmt2->get_result();

if ($result2->num_rows > 0) {
    $user = $result2->fetch_assoc();
    if (password_verify($password, $user['contraseña'])) {
        echo json_encode([
            "success" => true,
            "access" => generateAccessToken($user['id']),
            "refresh" => generateRefreshToken($user['id']),
            "username" => $user['nombre'],
            "rol" => "usuario",
            "referencedId" => $user['id'],
            "referencedTable" => "users"
        ]);
        exit;
    } else {
        echo json_encode(["success" => false, "message" => "Contraseña incorrecta"]);
        exit;
    }
}
$stmt2->close();

echo json_encode(["success" => false, "message" => "Usuario no encontrado"]);
$conn->close();

function generateAccessToken($userId) {
    return "access" . $userId;
}

function generateRefreshToken($userId) {
    return "refresh" . $userId;
}
?>
