<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$sessionToken = $_SESSION['token'] ?? null;
$ACCOUNT_ID = base64_decode($_SESSION['user_id'] ?? '');
if (!$sessionToken || !$ACCOUNT_ID) {
    header("Content-Type: application/json");
    http_response_code(401);
    echo json_encode([
        "success" => false,
        "message" => "Unauthorized access",
    ]);
    exit;
}

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

include "../connection/main_connection.php";
include "../functions/select_sql.php";

// === DEBUG SETTINGS ===
date_default_timezone_set('Asia/Manila');
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php-error.log');
error_reporting(E_ALL);

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Database connection failed"
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "message" => "Method not allowed. Use GET."
    ]);
    exit;
}

try {
    // Fetch submissions for current user and include applicant type name, academic year, can_update gate, and status hex color
    $sql = "SELECT 
                s.id AS submission_id,
                s.applicant_type_id,
                s.status,
                s.remarks,
                COALESCE(at.name, 'N/A') AS type,
                COALESCE(c.cycle_name, 'N/A') AS academic_year,
                COALESCE(asm.can_update, 1) AS can_update,
                COALESCE(st.hex_color, '#64748b') AS status_color
            FROM submissions s
            LEFT JOIN applicant_types at 
                ON at.id = s.applicant_type_id AND at.is_active = 1
            LEFT JOIN admission_cycles c 
                ON c.id = at.admission_cycle_id
            LEFT JOIN admission_submission asm
                ON asm.user_id = s.user_id
            LEFT JOIN statuses st
                ON st.name = s.status
            WHERE s.user_id = ?
            ORDER BY s.id DESC";

    $types = "i";
    $params = [$ACCOUNT_ID];
    $result = executeSelect($conn, $sql, $types, $params);

    if (!$result['success']) {
        echo json_encode([
            "success" => false,
            "message" => "Error fetching applications",
            "data" => []
        ]);
        exit;
    }

    echo json_encode([
        "success" => true,
        "data" => $result['data'],
        "message" => "Submissions fetched successfully"
    ]);
} catch (Exception $e) {
    error_log("Error in get_my_applications.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "An error occurred while fetching applications"
    ]);
}

$conn->close();
?>