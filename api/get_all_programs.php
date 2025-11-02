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
    // Fetch all active programs without strand filtering
    $sql = "SELECT id, name FROM program_reference WHERE status = 'active' ORDER BY name ASC";
    $result = executeSelect($conn, $sql, "", []);

    if (!$result['success']) {
        echo json_encode([
            "success" => false,
            "message" => "Error fetching programs"
        ]);
        exit;
    }

    // Return all programs
    echo json_encode([
        "success" => true,
        "data" => $result['data'],
        "message" => "All programs fetched successfully"
    ]);
} catch (Exception $e) {
    error_log("Error in get_all_programs.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "An error occurred while fetching programs"
    ]);
}

$conn->close();
?>