<?php
/*
Este script recibe datos JSON para registrar un nuevo usuario en la base de datos 'asistapp'. 
Incluye el campo 'domicilio' además de los datos personales, de contacto y de registro facial. 
Valida que el campo 'nombre' esté presente, hashea la contraseña y guarda los datos en la tabla 'users'. 
Responde en formato JSON con el ID insertado o un mensaje de error.
*/
header("Content-Type: application/json; charset=UTF-8");
error_reporting(E_ALL);
ini_set('display_errors', 1);

$servername = "localhost";
$username = "ivanportador";
$password = "Mintario153";
$dbname = "asistapp";

try {
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        throw new Exception("Conexión fallida: " . $conn->connect_error);
    }

    $data = json_decode(file_get_contents("php://input"), true);
    if (!$data) {
        throw new Exception("JSON mal formado o vacío.");
    }

    $foto = $data['foto'] ?? '';
    $nombre = trim($data['nombre'] ?? '');
    $apellidos = trim($data['apellidos'] ?? '');
    $edad = intval($data['edad'] ?? 0);
    $domicilio = trim($data['domicilio'] ?? ''); // ✅ nuevo campo
    $celular = trim($data['celular'] ?? '');
    $horario = trim($data['horario'] ?? '');
    $correo = trim($data['correo'] ?? '');
    $contraseña = trim($data['contraseña'] ?? '');
    $fecha_nacimiento = trim($data['fechaNacimiento'] ?? '');
    $equipo = trim($data['equipo'] ?? '');
    $registro_facial = $data['registro_facial'] ?? '';

    if (empty($nombre)) {
        throw new Exception("El nombre es obligatorio.");
    }

    // Hash de la contraseña
    $contraseña_hashed = !empty($contraseña) ? password_hash($contraseña, PASSWORD_DEFAULT) : null;

    // ✅ Se añadió 'domicilio' en la consulta SQL
    $sql = "INSERT INTO users (foto, nombre, apellidos, edad, domicilio, celular, horario, correo, contrasea, fecha_nacimiento, equipo, registro_facial) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Error en la consulta: " . $conn->error);
    }

    // ✅ Se añadió $domicilio en el bind_param
    $stmt->bind_param("ssssssssssss",$foto,$nombre,$apellidos,$edad,$domicilio,$celular,$horario,$correo,$contraseña_hashed,$fecha_nacimiento,$equipo,$registro_facial);

    if ($stmt->execute()) {
        echo json_encode([
            "success" => true,
            "id" => $stmt->insert_id
        ]);
    } else {
        throw new Exception("Error al registrar usuario: " . $stmt->error);
    }

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
?>