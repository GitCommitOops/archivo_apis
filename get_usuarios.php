<?php
/*
Este script se conecta a la base de datos 'login_app_db' y obtiene todos los usuarios de la tabla 'users'. 
Devuelve los datos (como nombre, edad, correo, etc.) en formato JSON. Si no hay usuarios, retorna un arreglo vacío. 
También maneja errores de conexión a la base de datos.
*/

// Configuración de la base de datos
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "asistapp";// Nombre de tu base de datos

// Crea la conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verifica si la conexión es exitosa
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Consulta SQL para obtener todos los usuarios
$sql = "SELECT id, nombre, apellidos, fecha_nacimiento, edad, celular, horario, correo, contraseña, equipo, foto, registro_facial FROM users";
$result = $conn->query($sql);

$usuarios = array();

// Si hay resultados, agréguelos al array
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $usuarios[] = $row; // Cada fila es un usuario
    }
    // Enviar los resultados como JSON
    echo json_encode($usuarios);
} else {
    echo json_encode(array()); // Si no hay usuarios, enviar un array vacío
}

// Cierra la conexión
$conn->close();
?>
