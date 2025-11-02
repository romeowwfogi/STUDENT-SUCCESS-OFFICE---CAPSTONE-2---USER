<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$sessionToken = $_SESSION['token'] ?? null;
if (!$sessionToken) {
    header("Location: ../login");
    exit;
}

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
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
date_default_timezone_set('Asia/Manila'); // Set your timezone

ini_set('display_errors', 0);              // Hide errors from browser (production-safe)
ini_set('log_errors', 1);                  // Enable error logging
ini_set('error_log', __DIR__ . '/php-error.log'); // Log errors to a file in this directory
error_reporting(E_ALL);                    // Report all errors

error_log("🚀 Error logging test triggered at " . date('Y-m-d H:i:s'));

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Oops! We couldn’t connect to the server. Please try again shortly."
    ]);
    exit;
}

$MESSAGE = getConfigValue(
    $conn,
    'REQUEST_METHOD_POST',
    "To interact with this endpoint, be sure to send a POST request — other methods aren’t supported."
);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(response_code: 405);
    echo json_encode(["success" => false, "message" => $MESSAGE]);
    exit;
}

// 📨 Read JSON input
$input = json_decode(file_get_contents("php://input"), true);
$firstName = trim($input['firstName'] ?? '');
$middleName = trim($input['middleName'] ?? '');
$lastName = trim($input['lastName'] ?? '');
$suffix = trim($input['suffix'] ?? '');

$MESSAGE = getConfigValue(
    $conn,
    'INVALID_FIRST_NAME',
    "To continue, please make sure you’ve entered a valid first name."
);

if (empty($firstName)) {
    echo json_encode(["success" => false, "message" => $MESSAGE]);
    exit;
}

$MESSAGE = getConfigValue(
    $conn,
    'INVALID_LAST_NAME',
    "To continue, please make sure you’ve entered a valid last name."
);

if (empty($lastName)) {
    echo json_encode(["success" => false, "message" => $MESSAGE]);
    exit;
}

$sessionToken = $_SESSION['token'] ?? null;
$encodedToken = substr($sessionToken, 7); // Remove 'Bearer ' prefix
$sessionToken = base64_decode($encodedToken);
$ACCOUNT_ID = base64_decode($_SESSION['user_id'] ?? '');

if (!$sessionToken || !$ACCOUNT_ID) {
    $MESSAGE = getConfigValue(
        $conn,
        'SESSION_EXPIRED',
        "Your session has expired or you’re not logged in. Please sign in to regain access."
    );
    http_response_code(401);
    echo json_encode([
        "success" => false,
        "message" => $MESSAGE
    ]);
    exit;
}

$sql = "SELECT * FROM tokenization 
        WHERE name = 'SESSION' 
          AND value = ? 
          AND user_id = ?";

$types = "si";
$params = [$sessionToken, $ACCOUNT_ID];

$result = executeSelect($conn, $sql, $types, $params);

// ❌ Invalid session or expired
if (!$result['success'] || count($result['data']) === 0) {
    $MESSAGE = getConfigValue(
        $conn,
        'SESSION_EXPIRED',
        "Your session has expired or you’re not logged in. Please sign in to regain access."
    );
    http_response_code(401);
    echo json_encode([
        "success" => false,
        "message" => $MESSAGE
    ]);
    exit;
}

$sql = "SELECT * FROM user_uniqe_path WHERE user_id = ?";
$types = "i";
$params = [$ACCOUNT_ID];
$result = executeSelect($conn, $sql, $types, $params);
if (!$result['success'] || count($result['data']) === 0) {
    do {
        // Generate a unique path key
        $unique_path_id = sprintf(
            'PLP-Admission-%s_%s',
            date('Y-m-d'),
            bin2hex(random_bytes(8))
        );

        // Check if it already exists
        $sql = "SELECT path_key FROM user_uniqe_path WHERE path_key = ?";
        $types = "s";
        $params = [$unique_path_id];
        $result = executeSelect($conn, $sql, $types, $params);

        $unique_path_idExists = ($result['success'] && count($result['data']) > 0);
    } while ($unique_path_idExists);

    $sql1 = "INSERT INTO user_uniqe_path (user_id, path_key) VALUES (?, ?)";

    $types1 = "is";
    $params1 = [$ACCOUNT_ID, $unique_path_id];
    executeInsert($conn, $sql1, $types1, $params1);
}

$conn->begin_transaction();
try {
    // Check if OTP record exists for this user
    $sqlCheck = "SELECT * FROM user_fullname WHERE user_id = ?";
    $typesCheck = "i";
    $paramsCheck = [$ACCOUNT_ID];
    $resultCheck = executeSelect($conn, $sqlCheck, $typesCheck, $paramsCheck);

    if ($resultCheck['success'] && count($resultCheck['data']) > 0) {
        $sql1 = "
        UPDATE user_fullname 
            SET 
                first_name = ?, 
                middle_name = ?, 
                last_name = ?,
                suffix = ?
            WHERE user_id = ?
        ";

        $types1 = "ssssi";
        $params1 = [$firstName, $middleName, $lastName, $suffix, $ACCOUNT_ID];
        $result1 = executeUpdate($conn, $sql1, $types1, $params1);

        if (!$result1['success']) {
            throw new Exception($result1['message']);
        }
        
        // Log if no rows were affected (could mean data was identical or user_id not found)
        if ($result1['affected_rows'] === 0) {
            error_log("UPDATE affected 0 rows for user_id: $ACCOUNT_ID. Data may be identical or user not found.");
        }
    } else {
        // 🆕 Insert new record if none exists
        $sql1 = "
            INSERT INTO user_fullname (user_id, first_name, middle_name, last_name, suffix)
            VALUES (?, ?, ?, ?, ?)
        ";

        $types1 = "issss";
        $params1 = [$ACCOUNT_ID, $firstName, $middleName, $lastName, $suffix];
        $result1 = executeInsert($conn, $sql1, $types1, $params1);

        if (!$result1['success']) {
            throw new Exception($result1['message']);
        }
    }

    $conn->commit();
    $MESSAGE = getConfigValue(
        $conn,
        'SET_PROFILE_SUCCESS',
        "Your profile has been set up successfully."
    );
    http_response_code(201);
    echo json_encode([
        "success" => true,
        "message" => $MESSAGE
    ]);
} catch (Exception $e) {
    // ❌ Roll back everything on error
    $conn->rollback();
    $MESSAGE = getConfigValue(
        $conn,
        'SET_PROFILE_FAILED',
        "We couldn’t update your profile at this time. Please check your input and try again. If the issue persists, contact support for help."
    );
    http_response_code(500);
    echo json_encode([
        "success" => false,
        // "message" => "Transaction failed: " . $e->getMessage()
        "message" => $MESSAGE
    ]);
}
