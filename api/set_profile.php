<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$sessionToken = $_SESSION['token'] ?? null;
// Do not redirect from API endpoints; let the handler return JSON 401
// if the session is missing or expired, so fetch() can process it correctly.

// Lightweight file logger for troubleshooting
// Use a constant so it’s accessible inside the logger without globals
if (!defined('PROFILE_SETUP_LOG')) {
    define('PROFILE_SETUP_LOG', __DIR__ . '/profile_setup.txt');
}
function __profile_debug_log($message)
{
    $line = '[' . date('Y-m-d H:i:s') . ' Asia/Manila] ' . $message . PHP_EOL;
    @file_put_contents(PROFILE_SETUP_LOG, $line, FILE_APPEND);
}
__profile_debug_log('set_profile invoked');
__profile_debug_log('Session token present: ' . ($sessionToken ? 'yes' : 'no'));

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

require_once __DIR__ . '/../connection/main_connection.php';
require_once __DIR__ . '/../functions/select_sql.php';
require_once __DIR__ . '/../functions/insert_sql.php';
require_once __DIR__ . '/../functions/update_sql.php';
require_once __DIR__ . '/../functions/en-de_crypt.php';
require_once __DIR__ . '/../functions/greetings.php';
require_once __DIR__ . '/../functions/config_msg.php';
require_once __DIR__ . '/../functions/expiration_config.php';

// === DEBUG SETTINGS ===
date_default_timezone_set('Asia/Manila'); // Set your timezone

ini_set('display_errors', 0);              // Hide errors from browser (production-safe)
ini_set('log_errors', 1);                  // Enable error logging
ini_set('error_log', __DIR__ . '/php-error.log'); // Log errors to a file in this directory
error_reporting(E_ALL);                    // Report all errors

error_log("🚀 Error logging test triggered at " . date('Y-m-d H:i:s'));

if ($conn->connect_error) {
    __profile_debug_log('DB connect_error: ' . $conn->connect_error);
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
    __profile_debug_log('Invalid method: ' . ($_SERVER['REQUEST_METHOD'] ?? 'unknown'));
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
__profile_debug_log('Input parsed FN=' . $firstName . ' LN=' . $lastName . ' MN=' . ($middleName !== '' ? 'Y' : 'N') . ' SFX=' . ($suffix !== '' ? 'Y' : 'N'));

$MESSAGE = getConfigValue(
    $conn,
    'INVALID_FIRST_NAME',
    "To continue, please make sure you’ve entered a valid first name."
);

if (empty($firstName)) {
    __profile_debug_log('Missing firstName');
    echo json_encode(["success" => false, "message" => $MESSAGE]);
    exit;
}

$MESSAGE = getConfigValue(
    $conn,
    'INVALID_LAST_NAME',
    "To continue, please make sure you’ve entered a valid last name."
);

if (empty($lastName)) {
    __profile_debug_log('Missing lastName');
    echo json_encode(["success" => false, "message" => $MESSAGE]);
    exit;
}

$sessionToken = $_SESSION['token'] ?? null;
$encodedToken = substr($sessionToken, 7); // Remove 'Bearer ' prefix
$sessionToken = base64_decode($encodedToken);
$ACCOUNT_ID = base64_decode($_SESSION['user_id'] ?? '');
__profile_debug_log('Decoded session token present: ' . ($sessionToken ? 'yes' : 'no') . ' account_id: ' . ($ACCOUNT_ID !== '' ? $ACCOUNT_ID : 'missing'));

if (!$sessionToken || !$ACCOUNT_ID) {
    __profile_debug_log('Session missing or invalid');
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
__profile_debug_log('Validating session in tokenization: result_success=' . ($result['success'] ? 'yes' : 'no') . ' rows=' . (is_array($result['data']) ? count($result['data']) : 0));

// ❌ Invalid session or expired
if (!$result['success'] || count($result['data']) === 0) {
    __profile_debug_log('Session invalid or expired during tokenization check');
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

$conn->begin_transaction();
try {
    // Check if OTP record exists for this user
    $sqlCheck = "SELECT * FROM user_fullname WHERE user_id = ?";
    $typesCheck = "i";
    $paramsCheck = [$ACCOUNT_ID];
    $resultCheck = executeSelect($conn, $sqlCheck, $typesCheck, $paramsCheck);

    if ($resultCheck['success'] && count($resultCheck['data']) > 0) {
        __profile_debug_log('Updating user_fullname for user_id=' . $ACCOUNT_ID);
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
            __profile_debug_log('UPDATE affected 0 rows for user_id=' . $ACCOUNT_ID);
        }
    } else {
        // 🆕 Insert new record if none exists
        __profile_debug_log('Inserting user_fullname for user_id=' . $ACCOUNT_ID);
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
    __profile_debug_log('Profile set success; committed for user_id=' . $ACCOUNT_ID);
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
    __profile_debug_log('Transaction failed: ' . $e->getMessage());
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
