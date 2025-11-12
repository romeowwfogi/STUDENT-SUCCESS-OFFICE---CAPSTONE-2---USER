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

$submissionId = isset($_GET['submission_id']) ? intval($_GET['submission_id']) : 0;
if ($submissionId <= 0) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Invalid or missing submission_id"
    ]);
    exit;
}

try {
    // Verify submission belongs to the current user and fetch its summary
    $sqlSummary = "SELECT 
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
            DATE_FORMAT(s.submitted_at, '%Y-%m-%d %H:%i:%s') AS submitted_at
        FROM submissions s
        LEFT JOIN applicant_types at ON at.id = s.applicant_type_id
        LEFT JOIN admission_cycles c ON c.id = at.admission_cycle_id
        LEFT JOIN statuses st ON st.name = s.status
        WHERE s.id = ? AND s.user_id = ?";

    $resultSummary = executeSelect($conn, $sqlSummary, "ii", [$submissionId, $ACCOUNT_ID]);
    if (!$resultSummary['success'] || empty($resultSummary['data'])) {
        http_response_code(404);
        echo json_encode([
            "success" => false,
            "message" => "Submission not found or access denied"
        ]);
        exit;
    }

    $summary = $resultSummary['data'][0];

    // Fetch applicant number for the current user (fallback to N/A if not found)
    $applicantNumber = 'N/A';
    try {
        $sqlApplicant = "SELECT applicant_number, applicant_num FROM applicant_number WHERE user_id = ? LIMIT 1";
        $resApplicant = executeSelect($conn, $sqlApplicant, "i", [$ACCOUNT_ID]);
        if ($resApplicant['success'] && !empty($resApplicant['data'])) {
            $row = $resApplicant['data'][0];
            if (isset($row['applicant_number']) && $row['applicant_number'] !== null && $row['applicant_number'] !== '') {
                $applicantNumber = $row['applicant_number'];
            } elseif (isset($row['applicant_num']) && $row['applicant_num'] !== null && $row['applicant_num'] !== '') {
                $applicantNumber = $row['applicant_num'];
            }
        }
    } catch (Exception $e) {
        // Keep applicantNumber as 'N/A' on any error
        error_log("Applicant number lookup error: " . $e->getMessage());
    }
    $summary['applicant_number'] = $applicantNumber;

    // Fetch all field data
    $sqlData = "SELECT field_name, field_value 
                FROM submission_data 
                WHERE submission_id = ? 
                ORDER BY field_name ASC";
    $resultData = executeSelect($conn, $sqlData, "i", [$submissionId]);
    $dataFields = $resultData['success'] ? $resultData['data'] : [];

    // Fetch all files
    $sqlFiles = "SELECT field_name, original_filename, file_path 
                 FROM submission_files 
                 WHERE submission_id = ? 
                 ORDER BY id ASC";
    $resultFiles = executeSelect($conn, $sqlFiles, "i", [$submissionId]);
    $files = $resultFiles['success'] ? $resultFiles['data'] : [];

    echo json_encode([
        "success" => true,
        "data" => [
            "submission" => $summary,
            "data_fields" => $dataFields,
            "files" => $files
        ],
        "message" => "Submission details fetched successfully"
    ]);
} catch (Exception $e) {
    error_log("Error in get_submission_details.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "An error occurred while fetching submission details"
    ]);
}

$conn->close();
?>