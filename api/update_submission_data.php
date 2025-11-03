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

$input = json_decode(file_get_contents('php://input'), true);
$submissionId = intval($input['submission_id'] ?? 0);
$fields = $input['fields'] ?? [];

if ($submissionId <= 0 || !is_array($fields)) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Invalid payload: missing submission_id or fields"]);
    exit;
}

// Verify submission belongs to user
$sqlSub = "SELECT id FROM submissions WHERE id = ? AND user_id = ?";
$resSub = executeSelect($conn, $sqlSub, 'ii', [$submissionId, $ACCOUNT_ID]);
if (!$resSub['success'] || count($resSub['data']) === 0) {
    http_response_code(403);
    echo json_encode(["success" => false, "message" => "Submission not found or access denied"]);
    exit;
}

$conn->begin_transaction();
try {
    $updatedCount = 0;
    foreach ($fields as $f) {
        $name = isset($f['field_name']) ? trim($f['field_name']) : '';
        $value = isset($f['field_value']) ? trim($f['field_value']) : '';
        if ($name === '') continue;

        // Update existing field only; do not insert new keys here
        $sqlUpd = "UPDATE submission_data SET field_value = ? WHERE submission_id = ? AND field_name = ?";
        $resUpd = executeUpdate($conn, $sqlUpd, 'sis', [$value, $submissionId, $name]);
        if ($resUpd['success']) {
            $updatedCount += $resUpd['affected_rows'] ?? 0;
        } else {
            throw new Exception($resUpd['message'] ?? 'Update failed');
        }
    }

    // After successful field updates, lock further edits for this user
    $sqlLock = "UPDATE admission_submission SET can_update = 0, updated_at = NOW() WHERE user_id = ?";
    $resLock = executeUpdate($conn, $sqlLock, 'i', [$ACCOUNT_ID]);
    if (!$resLock['success']) {
        throw new Exception($resLock['message'] ?? 'Failed to update admission_submission lock');
    }

    $conn->commit();
    echo json_encode(["success" => true, "message" => "Data updated", "updated" => $updatedCount]);
} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}

exit;
?>