<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

$sessionToken = $_SESSION['token'] ?? null;
$ACCOUNT_ID = isset($_SESSION['user_id']) ? base64_decode($_SESSION['user_id']) : null;
if (!$sessionToken || !$ACCOUNT_ID) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Unauthorized access"]);
    exit;
}

// Extract raw token
if (strpos($sessionToken, 'Bearer ') === 0) {
    $encodedToken = substr($sessionToken, 7);
    $rawToken = base64_decode($encodedToken);
} else {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Invalid session token format"]);
    exit;
}

include __DIR__ . '/../connection/main_connection.php';
include __DIR__ . '/../functions/select_sql.php';
include __DIR__ . '/../functions/update_sql.php';

// Validate token
if ($conn && !$conn->connect_error) {
    $sql = "SELECT id FROM tokenization WHERE user_id = ? AND name = 'SESSION' AND value = ? AND is_used = 0";
    $types = "is";
    $params = [$ACCOUNT_ID, $rawToken];
    $result = executeSelect($conn, $sql, $types, $params);
    if (!$result['success'] || count($result['data']) === 0) {
        http_response_code(401);
        echo json_encode(["success" => false, "message" => "Session expired or invalid"]);
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Method not allowed. Use POST."]);
    exit;
}

try {
    // Attempt to update existing admission_submission row
    $sqlUpd = "UPDATE admission_submission SET can_update = 0, updated_at = NOW() WHERE user_id = ?";
    $resUpd = executeUpdate($conn, $sqlUpd, 'i', [$ACCOUNT_ID]);
    if (!$resUpd['success']) {
        throw new Exception($resUpd['message'] ?? 'Failed to update can_update flag');
    }

    $affected = intval($resUpd['affected_rows'] ?? 0);
    if ($affected === 0) {
        // Fallback: insert a row if none exists
        $sqlIns = "INSERT INTO admission_submission (user_id, can_apply, can_update, submitted_at, updated_at) VALUES (?, 0, 0, NOW(), NOW())";
        $resIns = executeUpdate($conn, $sqlIns, 'i', [$ACCOUNT_ID]);
        if (!$resIns['success']) {
            throw new Exception($resIns['message'] ?? 'Failed to insert admission_submission row');
        }
    }

    echo json_encode(["success" => true, "message" => "can_update locked for user."]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}

exit;
?>