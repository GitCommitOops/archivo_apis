<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");

$conn = new mysqli("localhost", "ivanportador", "Mintario153", "asistapp");
if ($conn->connect_error) {
    die(json_encode(["success" => false, "message" => "Error de conexión"]));
}

$data = json_decode(file_get_contents("php://input"), true);

// 1. Aceptar tanto 'embedding' como 'registro_facial' en el request
$inputEmbedding = $data['embedding'] ?? $data['registro_facial'] ?? null;

if (!$inputEmbedding) {
    die(json_encode([
        "success" => false,
        "message" => "Se requiere 'embedding' o 'registro_facial'",
        "received_data" => $data // Para debug
    ]));
}

// 2. Convertir a array de floats
$embeddingArray = is_array($inputEmbedding) ? 
    array_map('floatval', $inputEmbedding) : 
    array_map('floatval', explode(',', str_replace(['[', ']', ' '], '', $inputEmbedding)));

// 3. Normalizar el embedding recibido
function normalizeEmbedding(array $embedding): array {
    $norm = sqrt(array_sum(array_map(fn($x) => $x * $x, $embedding)));
    return $norm > 0 ? array_map(fn($x) => $x / $norm, $embedding) : $embedding;
}

$normalizedInput = normalizeEmbedding($embeddingArray);

// 4. Buscar coincidencias en la BD
$bestMatch = null;
$highestSimilarity = 0;
$threshold = 0.3; // Umbral ajustado para embeddings normalizados

$sql = "SELECT id, nombre, contraseña, registro_facial FROM users WHERE registro_facial IS NOT NULL";
$result = $conn->query($sql);

while ($user = $result->fetch_assoc()) {
    $dbEmbedding = array_map('floatval', explode(',', $user['registro_facial']));
    $normalizedDb = normalizeEmbedding($dbEmbedding);
    
    $similarity = cosineSimilarity($normalizedInput, $normalizedDb);
    
    if ($similarity > $highestSimilarity && $similarity >= $threshold) {
        $highestSimilarity = $similarity;
        $bestMatch = $user;
    }
}

// 5. Respuesta mejorada
if ($bestMatch) {
    echo json_encode([
        "success" => true,
        "user" => [
            "id" => $bestMatch['id'],
            "nombre" => $bestMatch['nombre']
        ],
        "similarity" => round($highestSimilarity, 4),
        "match_type" => "cosine_similarity"
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "No se encontró coincidencia",
        "debug_info" => [
            "input_length" => count($normalizedInput),
            "first_5_values" => array_slice($normalizedInput, 0, 5)
        ]
    ]);
}

// Función de similitud coseno optimizada
function cosineSimilarity(array $a, array $b): float {
    $dot = 0.0;
    $normA = 0.0;
    $normB = 0.0;
    foreach ($a as $i => $val) {
        $dot += $val * ($b[$i] ?? 0);
        $normA += $val * $val;
        $normB += ($b[$i] ?? 0) * ($b[$i] ?? 0);
    }
    return $normA && $normB ? $dot / (sqrt($normA) * sqrt($normB)) : 0;
}

$conn->close();
?>