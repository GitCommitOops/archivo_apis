<?php
    header("Content-Type: application/json; charset=UTF-8");
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    $servername = "localhost";
    $username = "root";
    $password = "123456";
    $dbname = "inventory_db";

    $accessExpTime = time() + (60 * 60); 
    $refreshExpTime = time() + (7 * 24 * 60 * 60); 

    try {
        // Conexión a la base de datos
        $conn = new mysqli($servername, $username, $password, $dbname);
        if ($conn->connect_error) {
            throw new Exception("Conexión fallida: " . $conn->connect_error);
        }

        // Obtener los datos enviados por la app
        $data = file_get_contents("php://input");
        if (!$data) {
            throw new Exception("No se recibieron datos.");
        }

        $data = json_decode($data, true);
        if (!is_array($data)) {
            throw new Exception("JSON mal formado.");
        }

        $email = trim($data['email'] ?? '');
        $password = trim($data['password'] ?? '');

        if (empty($email) || empty($password)) {
            throw new Exception("Email y contraseña son obligatorios.");
        }

        // Buscar usuario en la base de datos
        $sql = "SELECT id, email, password FROM inventory_login WHERE email = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Error en la consulta: " . $conn->error);
        }

        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows == 0) {
            echo json_encode(["success" => false, "message" => "Usuario no encontrado."]);
        } else {
            $stmt->bind_result($id, $dbEmail, $dbPassword);
            $stmt->fetch();

                $accessToken = "access_" . base64_encode($id . "_" . $email . "_" . $accessExpTime);
                $refreshToken = "refresh_" . base64_encode($id . "_" . $refreshExpTime);

                echo json_encode([
                    "success" => true,
                    "message" => "Inicio de sesión exitoso.",
                    "access" => $accessToken,
                    "refresh" => $refreshToken,
                    "email" => $dbEmail
                ]);
            } else {
                echo json_encode(["success" => false, "message" => "Contraseña incorrecta."]);
            }
        }

        $stmt->close();
        $conn->close();
    } catch (Exception $e) {
        echo json_encode(["success" => false, "message" => $e->getMessage()]);
    }
?>
