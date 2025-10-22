<?php
    header("Content-Type: application/json; charset=UTF-8");
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    $servername = "localhost";
    $username = "ivanportador";
    $password = "Mintario153";
    $dbname = "asistapp";

    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die(json_encode(["success" => false, "message" => "Conexión fallida"]));
    }

    $data = file_get_contents("php://input");
    if (!$data) {
        die(json_encode(["success" => false, "message" => "No se recibieron datos"]));
    }

    $data = json_decode($data);
    if (!$data) {
        die(json_encode(["success" => false, "message" => "JSON mal formado"]));
    }

    $username = $data->username;
    $email = $data->email;
    $password = $data->password;

    // Verificar si el usuario ya existe
    $sql = "SELECT id FROM usuarios WHERE username = ? OR email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo json_encode(["success" => false, "message" => "El usuario o correo ya existe."]);
    } else {
        // Cifrar la contraseña antes de guardarla
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $insertSql = "INSERT INTO usuarios (username, email, password) VALUES (?, ?, ?)";
        $insertStmt = $conn->prepare($insertSql);
        $insertStmt->bind_param("sss", $username, $email, $hashedPassword);

        if ($insertStmt->execute()) {
            echo json_encode(["success" => true, "message" => "Usuario registrado con éxito."]);
        } else {
            echo json_encode(["success" => false, "message" => "Error al registrar el usuario."]);
        }
    }

    $stmt->close();
    $conn->close();
?>
