<?php

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

include "connection/main_connection.php";
include "functions/select_sql.php";
include "functions/update_sql.php";
include "functions/insert_sql.php";
include "functions/en-de_crypt.php";
include "functions/send_email.php";
include "functions/greetings.php";
include "functions/config_msg.php";

// === DEBUG SETTINGS ===
date_default_timezone_set('Asia/Manila'); // Set your timezone

ini_set('display_errors', 0);              // Hide errors from browser (production-safe)
ini_set('log_errors', 1);                  // Enable error logging
ini_set('error_log', __DIR__ . '/php-error.log'); // Log errors to a file in this directory
error_reporting(E_ALL);                    // Report all errors

error_log("🚀 Error logging test triggered at " . date('Y-m-d H:i:s'));

function validatePassword($password)
{
    $errors = [];

    // 1. Length between 8 and 16
    if (strlen($password) < 8 || strlen($password) > 16) {
        $errors[] = "Password must be 8-16 characters long.";
    }

    // 2. At least one uppercase letter
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = "Password must contain at least one uppercase letter.";
    }

    // 3. At least one number
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = "Password must contain at least one number.";
    }

    // 4. At least one special character
    if (!preg_match('/[\W_]/', $password)) {
        $errors[] = "Password must contain at least one special character.";
    }

    return $errors;
}

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
    http_response_code(405);
    echo json_encode(["success" => false, "message" => $MESSAGE]);
    exit;
}

// READ JSON INPUT FROM REQUEST BODY
$input = json_decode(file_get_contents("php://input"), true);
$token = trim($input['token'] ?? '');
$password = trim($input['password'] ?? '');

$MESSAGE = getConfigValue(
    $conn,
    'RESET_PASSWORD_LINK_INVALID',
    "We couldn’t find an account with this reset password link. Please check your reset password link and try again."
);
// VALIDATE TOKEN PRESENCE
if (empty($token)) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => $MESSAGE
    ]);
    exit;
}

// VALIDATE PASSWORD RULES
$validationErrors = validatePassword($password);
if (!empty($validationErrors)) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => $validationErrors
    ]);
    exit;
}

$sql = "SELECT * FROM tokenization WHERE value = ? AND name = 'RESET_PASSWORD'";
$types = "s";
$params = [$token];
$result = executeSelect($conn, $sql, $types, $params);
if ($result['success'] && count($result['data']) > 0) {
    $row = $result['data'][0];
    $userId = $row['user_id'];
    $expiresAt = new DateTime($row['expires_at']);
    $now = new DateTime();
    // Check expiration against current time
    if ($expiresAt < $now) {
        http_response_code(410);
        $MESSAGE = getConfigValue(
            $conn,
            'RESET_PASSWORD_LINK_EXPIRED',
            "This reset password link has expired for security reasons. Please request a new link to verify your account."
        );
        echo json_encode([
            "status" => "error",
            "message" => $MESSAGE
        ]);
        exit;
    }

    // If tokenization has an is_used flag, respect it
    $tokenIsUsed = (int)($row['is_used'] ?? 0);
    if ($tokenIsUsed === 1) {
        http_response_code(410);
        $MESSAGE = getConfigValue(
            $conn,
            'RESET_PASSWORD_LINK_INVALID',
            "We couldn’t find an account with this reset password link. Please check your reset password link and try again."
        );
        echo json_encode([
            "status" => "expired",
            "message" => $MESSAGE
        ]);
        exit;
    }

    // Use admission account id from tokenization
    $ACCOUNT_ID = $userId;
} else {
    $MESSAGE = getConfigValue(
        $conn,
        'RESET_PASSWORD_LINK_INVALID',
        "We couldn’t find an account with this reset password link. Please check your reset password link and try again."
    );
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => $MESSAGE
    ]);
    exit;
}

// GET ACCOUNT DETAILS USING CRUD HELPER
$checkSql = "SELECT id, email, password_hash FROM users WHERE id = ? LIMIT 1";
$types = "i";
$params = [$ACCOUNT_ID];
$accountResult = executeSelect($conn, $checkSql, $types, $params);

// HANDLE CASE WHEN ACCOUNT NOT FOUND
if (!$accountResult['success'] || count($accountResult['data']) === 0) {
    http_response_code(404);
    $MESSAGE = getConfigValue(
        $conn,
        'USER_NOT_FOUND',
        "We couldn’t find an account with this verification link. Please check your information and try again."
    );
    echo json_encode([
        "status" => "error",
        "message" => $MESSAGE
    ]);
    exit;
}

// EMAIL EXISTS — HASH NEW PASSWORD
$passwordHash = password_hash($password, PASSWORD_DEFAULT);

// UPDATE PASSWORD USING CRUD HELPER
$updateSql = "UPDATE users SET password_hash = ? WHERE id = ?";
$typesUpdate = "si";
$paramsUpdate = [$passwordHash, $ACCOUNT_ID];
$updateResult = executeUpdate($conn, $updateSql, $typesUpdate, $paramsUpdate);

// CHECK IF PASSWORD UPDATE SUCCEEDED
if ($updateResult['success']) {
    // ✅ Mark token as used in tokenization using CRUD helper
    $updateTokenSql = "UPDATE tokenization SET is_used = 1 WHERE value = ? AND name = 'RESET_PASSWORD'";
    $typesToken = "s";
    $paramsToken = [$token];
    executeUpdate($conn, $updateTokenSql, $typesToken, $paramsToken);

    $MESSAGE = getConfigValue(
        $conn,
        'CHANGE_PASSWORD_SUCCESS',
        "Your password has been updated successfully! You can now log in with your new password."
    );
    echo json_encode([
        "status" => "success",
        "message" => $MESSAGE
    ]);
} else {
    $MESSAGE = getConfigValue(
        $conn,
        'CHANGE_PASSWORD_SUCCESS',
        "Your password has been updated successfully! You can now log in with your new password."
    );
    echo json_encode([
        "status" => "error",
        "message" => $MESSAGE
    ]);
}
