<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$sessionToken = $_SESSION['token'] ?? null;
if (!$sessionToken) {
    http_response_code(401);
    echo json_encode([
        "success" => false,
        "message" => "Unauthorized access"
    ]);
    exit;
}

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

include "connection/main_connection.php";
include "functions/select_sql.php";
include "functions/insert_sql.php";
include "functions/update_sql.php";
include "functions/en-de_crypt.php";
include "functions/send_email.php";
include "functions/greetings.php";
include "functions/config_msg.php";
include "functions/expiration_config.php";

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

// Check request method
if ($_SERVER['REQUEST_METHOD'] !== 'GET' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        "success" => false,
        "message" => "Method not allowed. Use GET or POST."
    ]);
    exit;
}

try {
    // Get strand name from request
    $strandName = null;

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $strandName = $_GET['strand'] ?? null;
    } else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        $strandName = $input['strand'] ?? $_POST['strand'] ?? null;
    }

    if (!$strandName || trim($strandName) === '') {
        echo json_encode([
            "success" => false,
            "message" => "Strand name is required"
        ]);
        exit;
    }

    // First, get the strand ID from the strand name
    $sql1 = "SELECT id FROM strand_track_reference WHERE name = ? AND status = 'active'";
    $types1 = "s";
    $params1 = [trim($strandName)];
    $result1 = executeSelect($conn, $sql1, $types1, $params1);

    if (!$result1['success'] || count($result1['data']) === 0) {
        echo json_encode([
            "success" => false,
            "message" => "Strand not found or inactive"
        ]);
        exit;
    }

    $strandId = $result1['data'][0]['id'];

    // Now get the programs linked to this strand
    $sql2 = "SELECT p.id, p.name 
             FROM program_reference p
             INNER JOIN strand_program_link spl ON p.id = spl.program_id
             WHERE spl.strand_id = ? AND p.status = 'active'
             ORDER BY p.name ASC";
    $types2 = "i";
    $params2 = [$strandId];
    $result2 = executeSelect($conn, $sql2, $types2, $params2);

    if (!$result2['success']) {
        echo json_encode([
            "success" => false,
            "message" => "Error fetching programs"
        ]);
        exit;
    }

    // Return the programs
    echo json_encode([
        "success" => true,
        "data" => $result2['data'],
        "message" => "Programs fetched successfully"
    ]);
} catch (Exception $e) {
    error_log("Error in get_programs_by_strand.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "An error occurred while fetching programs"
    ]);
}

$conn->close();
