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
    $sql = "SELECT 
                s.id AS submission_id,
                s.status,
                COALESCE(st.hex_color, '#64748b') AS status_color,
                s.remarks,
                COALESCE(at.name, 'N/A') AS type,
                CASE 
                    WHEN c.academic_year_start IS NOT NULL AND c.academic_year_end IS NOT NULL
                        THEN CONCAT(c.academic_year_start, '-', c.academic_year_end)
                    WHEN c.academic_year_start IS NOT NULL
                        THEN CAST(c.academic_year_start AS CHAR)
                    ELSE 'N/A'
                END AS academic_year,
                s.can_update,
                DATE_FORMAT(s.submitted_at, '%Y-%m-%d %H:%i:%s') AS submitted_at
            FROM submissions s
            LEFT JOIN applicant_types at ON at.id = s.applicant_type_id
            LEFT JOIN admission_cycles c ON c.id = at.admission_cycle_id
            LEFT JOIN statuses st ON st.name = s.status
            WHERE s.user_id = ?
            ORDER BY s.submitted_at DESC";

    $result = executeSelect($conn, $sql, "i", [$ACCOUNT_ID]);

    if (!$result['success']) {
        http_response_code(500);
        echo json_encode([
            "success" => false,
            "message" => $result['message'] ?? 'Failed to fetch applications'
        ]);
        exit;
    }

    echo json_encode([
        "success" => true,
        "data" => $result['data'] ?? [],
        "message" => "Applications fetched successfully"
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
