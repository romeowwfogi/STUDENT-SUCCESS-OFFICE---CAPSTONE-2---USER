<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

include "../connection/main_connection.php";
include "../functions/update_sql.php";
include "../functions/config_msg.php";

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Server connection failed. Please try again."]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $MESSAGE = getConfigValue($conn, 'REQUEST_METHOD_POST', 'Use POST to increment read count.');
    http_response_code(405);
    echo json_encode(["success" => false, "message" => $MESSAGE]);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$faqId = intval($input['id'] ?? 0);

if ($faqId <= 0) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Invalid FAQ id."]);
    exit;
}

$sql = "UPDATE faqs SET read_count = read_count + 1 WHERE id = ? AND status = 'active'";
$result = executeUpdate($conn, $sql, 'i', [$faqId]);

if ($result['success']) {
    echo json_encode(["success" => true, "message" => "Read count updated.", "affected" => $result['affected_rows'] ?? 0]);
} else {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => $result['message'] ?? 'Failed to update read count.']);
}

exit;
?>