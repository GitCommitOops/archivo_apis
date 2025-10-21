<?php
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

        // Obtener los datos del JSON
        $data = json_decode(file_get_contents("php://input"), true);
        if (!$data) {
            throw new Exception("JSON mal formado o vacío.");
        }

        $id = $data['id'] ?? '';
        $registro_facial = $data['registro_facial'] ?? '';

        if (empty($id) || empty($registro_facial)) {
            throw new Exception("El ID y el registro facial son obligatorios.");
        }

        // 🔍 Verificar si el usuario existe antes de actualizar
        $checkSql = "SELECT id FROM users WHERE id = ?";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param("i", $id);
        $checkStmt->execute();
        $checkStmt->store_result();

        if ($checkStmt->num_rows === 0) {
            throw new Exception("El usuario con ID $id no existe.");
        }
        $checkStmt->close();

        // 🔄 Actualizar el registro en la base de datos
        $sql = "UPDATE users SET registro_facial = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Error en la consulta: " . $conn->error);
        }

        $stmt->bind_param("si", $registro_facial, $id);
        $stmt->execute();

        // 🔍 Verificar si realmente se actualizó algún registro
        if ($stmt->affected_rows > 0) {
            echo json_encode(["success" => true, "message" => "Registro facial actualizado correctamente."]);
        } else {
            echo json_encode(["success" => false, "message" => "El valor ya estaba guardado o no se modificó."]);
        }

        $stmt->close();
        $conn->close();
    } catch (Exception $e) {
        echo json_encode(["success" => false, "message" => $e->getMessage()]);
    }

    // 🔍 Mostrar los valores que llegaron al script para depuración
    var_dump($id, $registro_facial);
    exit();
?>
