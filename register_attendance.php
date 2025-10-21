<?php
// --- Conexión a la base de datos ---
$host = 'localhost';
$db = 'asistapp';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

// Respuesta JSON
header('Content-Type: application/json');

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión a la base de datos']);
    exit;
}

// --- Validar método HTTP ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

// --- Leer datos JSON del body ---
$input = json_decode(file_get_contents('php://input'), true);

// --- Validar campos obligatorios ---
if (
    !isset(
        $input['referencedId'],
        $input['referencedTable'],
        $input['fechaRegistro'],
        $input['horaRegistro'],
        $input['tipo']
    )
) {
    http_response_code(400);
    echo json_encode(['error' => 'Faltan campos requeridos']);
    exit;
}

$referencedId    = (int) $input['referencedId'];
$referencedTable = $input['referencedTable'];
$fecha           = $input['fechaRegistro'];
$hora            = $input['horaRegistro'];
$tipo            = $input['tipo'];

// --- Insertar en la base de datos ---
try {
    $stmt = $pdo->prepare("
        INSERT INTO attendance (referenced_id, referenced_table, fecha_registro, hora_registro, tipo)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([$referencedId, $referencedTable, $fecha, $hora, $tipo]);

    echo json_encode(['message' => 'Asistencia registrada correctamente']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al registrar asistencia']);
}
?>