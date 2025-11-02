<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

include "../connection/main_connection.php";
include "../functions/select_sql.php";
include "../functions/config_msg.php";

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Server connection failed. Please try again."]);
    exit;
}

// Optional limit param; default to 5
$limit = 5;
if (isset($_GET['limit'])) {
    $limit = max(1, min(50, intval($_GET['limit'])));
}

$sql = "SELECT id, question, answer, read_count FROM faqs WHERE status = 'active' ORDER BY read_count DESC, updated_at DESC LIMIT ?";
$result = executeSelect($conn, $sql, 'i', [$limit]);

if ($result['success']) {
    echo json_encode([
        "success" => true,
        "faqs" => $result['data'],
        "count" => count($result['data'])
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => $result['message'] ?? 'Failed to fetch FAQs.'
    ]);
}

exit;
?>