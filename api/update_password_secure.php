<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

require_once __DIR__ . '/../connection/main_connection.php';
require_once __DIR__ . '/../functions/select_sql.php';
require_once __DIR__ . '/../functions/update_sql.php';
require_once __DIR__ . '/../functions/config_msg.php';

date_default_timezone_set('Asia/Manila');
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php-error.log');
error_reporting(E_ALL);

function validatePassword($password)
{
    $errors = [];
    if (strlen($password) < 8 || strlen($password) > 16) {
        $errors[] = "Password must be 8-16 characters long.";
    }
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = "Password must contain at least one uppercase letter.";
    }
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = "Password must contain at least one lowercase letter.";
    }
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = "Password must contain at least one number.";
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

$input = json_decode(file_get_contents("php://input"), true);
$currentPassword = trim($input['currentPassword'] ?? '');
$newPassword = trim($input['newPassword'] ?? '');
$confirmPassword = trim($input['confirmPassword'] ?? '');

if ($currentPassword === '') {
    $MESSAGE = getConfigValue($conn, 'PASSWORD_REQUIRED', 'Please enter your current password to confirm this change.');
    http_response_code(400);
    echo json_encode(["success" => false, "message" => $MESSAGE]);
    exit;
}
if ($newPassword === '' || $confirmPassword === '') {
    $MESSAGE = getConfigValue($conn, 'INVALID_PASSWORD', 'Please enter and confirm your new password.');
    http_response_code(400);
    echo json_encode(["success" => false, "message" => $MESSAGE]);
    exit;
}
if ($newPassword !== $confirmPassword) {
    $MESSAGE = getConfigValue($conn, 'PASSWORD_MISMATCH', 'New password and confirmation do not match.');
    http_response_code(400);
    echo json_encode(["success" => false, "message" => $MESSAGE]);
    exit;
}

$validationErrors = validatePassword($newPassword);
if (!empty($validationErrors)) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => $validationErrors]);
    exit;
}

$sessionToken = $_SESSION['token'] ?? null;
$encodedToken = $sessionToken && strpos($sessionToken, 'Bearer ') === 0 ? substr($sessionToken, 7) : '';
$sessionTokenVal = $encodedToken ? base64_decode($encodedToken) : '';
$ACCOUNT_ID = base64_decode($_SESSION['user_id'] ?? '');
if (!$sessionTokenVal || !$ACCOUNT_ID) {
    $MESSAGE = getConfigValue($conn, 'SESSION_EXPIRED', "Your session has expired or you’re not logged in. Please sign in to regain access.");
    http_response_code(401);
    echo json_encode(["success" => false, "message" => $MESSAGE]);
    exit;
}

$tokRes = executeSelect($conn, "SELECT id FROM tokenization WHERE name='SESSION' AND value=? AND user_id=?", 'si', [$sessionTokenVal, $ACCOUNT_ID]);
if (!$tokRes['success'] || count($tokRes['data']) === 0) {
    $MESSAGE = getConfigValue($conn, 'SESSION_EXPIRED', "Your session has expired or you’re not logged in. Please sign in to regain access.");
    http_response_code(401);
    echo json_encode(["success" => false, "message" => $MESSAGE]);
    exit;
}

$accountRes = executeSelect($conn, "SELECT password_hash FROM users WHERE id = ?", 'i', [$ACCOUNT_ID]);
if (!$accountRes['success'] || count($accountRes['data']) === 0) {
    http_response_code(404);
    echo json_encode(["success" => false, "message" => "Account not found."]);
    exit;
}
$passwordHash = $accountRes['data'][0]['password_hash'] ?? '';
if (!$passwordHash || !password_verify($currentPassword, $passwordHash)) {
    $MESSAGE = getConfigValue($conn, 'INVALID_PASSWORD', 'The password you entered is incorrect. Please try again.');
    http_response_code(401);
    echo json_encode(["success" => false, "message" => $MESSAGE]);
    exit;
}

$newHash = password_hash($newPassword, PASSWORD_DEFAULT);
$upd = executeUpdate($conn, "UPDATE users SET password_hash = ? WHERE id = ?", 'si', [$newHash, $ACCOUNT_ID]);
if ($upd['success']) {
    $MESSAGE = getConfigValue($conn, 'CHANGE_PASSWORD_SUCCESS', 'Your password has been updated successfully!');
    echo json_encode(["success" => true, "message" => $MESSAGE]);
} else {
    $MESSAGE = getConfigValue($conn, 'CHANGE_PASSWORD_FAILED', 'We couldn’t update your password at this time. Please try again later.');
    http_response_code(500);
    echo json_encode(["success" => false, "message" => $MESSAGE]);
}

exit;
?>