<?php
/*
Este script recibe datos JSON para registrar un nuevo usuario en la base de datos 'login_app_db'. 
Valida que el campo 'nombre' esté presente, hashea la contraseña si se proporciona y guarda los datos 
(personales, de contacto y de registro facial) en la tabla 'users'. Si todo sale bien, responde con 
éxito y el ID insertado. En caso de error en conexión, validación o inserción, responde con un mensaje 
de error en formato JSON.
*/
    header("Content-Type: application/json; charset=UTF-8");
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    $servername = "localhost";
    $username = "root";
    $password = "";
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

        $sql = "INSERT INTO users (foto, nombre, apellidos, edad, celular, horario, correo, contraseña, fecha_nacimiento, equipo, registro_facial) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Error en la consulta: " . $conn->error);
        }

        $stmt->bind_param("sssssssssss", $foto, $nombre, $apellidos, $edad, $celular, $horario, $correo, $contraseña_hashed, $fecha_nacimiento, $equipo, $registro_facial);

        if ($stmt->execute()) {
            // Respuesta simplificada: solo éxito e ID
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