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
require_once __DIR__ . '/../functions/en-de_crypt.php';
require_once __DIR__ . '/../functions/config_msg.php';

date_default_timezone_set('Asia/Manila');
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php-error.log');
error_reporting(E_ALL);

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
$newEmail = trim($input['newEmail'] ?? '');
$password = trim($input['password'] ?? '');

if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
    $MESSAGE = getConfigValue(
        $conn,
        'INVALID_EMAIL',
        'Please enter a valid email address.'
    );
    http_response_code(400);
    echo json_encode(["success" => false, "message" => $MESSAGE]);
    exit;
}
if ($password === '') {
    $MESSAGE = getConfigValue(
        $conn,
        'PASSWORD_REQUIRED',
        'Please enter your current password to confirm this change.'
    );
    http_response_code(400);
    echo json_encode(["success" => false, "message" => $MESSAGE]);
    exit;
}

$sessionToken = $_SESSION['token'] ?? null;
$encodedToken = $sessionToken && strpos($sessionToken, 'Bearer ') === 0 ? substr($sessionToken, 7) : '';
$sessionTokenVal = $encodedToken ? base64_decode($encodedToken) : '';
$ACCOUNT_ID = base64_decode($_SESSION['user_id'] ?? '');
if (!$sessionTokenVal || !$ACCOUNT_ID) {
    $MESSAGE = getConfigValue(
        $conn,
        'SESSION_EXPIRED',
        "Your session has expired or you’re not logged in. Please sign in to regain access."
    );
    http_response_code(401);
    echo json_encode(["success" => false, "message" => $MESSAGE]);
    exit;
}

// Validate session tokenization
$sql = "SELECT id FROM tokenization WHERE name = 'SESSION' AND value = ? AND user_id = ?";
$result = executeSelect($conn, $sql, 'si', [$sessionTokenVal, $ACCOUNT_ID]);
if (!$result['success'] || count($result['data']) === 0) {
    $MESSAGE = getConfigValue(
        $conn,
        'SESSION_EXPIRED',
        "Your session has expired or you’re not logged in. Please sign in to regain access."
    );
    http_response_code(401);
    echo json_encode(["success" => false, "message" => $MESSAGE]);
    exit;
}

// Verify current password
$accountRes = executeSelect($conn, "SELECT password_hash FROM users WHERE id = ?", 'i', [$ACCOUNT_ID]);
if (!$accountRes['success'] || count($accountRes['data']) === 0) {
    http_response_code(404);
    echo json_encode(["success" => false, "message" => "Account not found."]);
    exit;
}
$passwordHash = $accountRes['data'][0]['password_hash'] ?? '';
if (!$passwordHash || !password_verify($password, $passwordHash)) {
    $MESSAGE = getConfigValue(
        $conn,
        'INVALID_PASSWORD',
        'The password you entered is incorrect. Please try again.'
    );
    http_response_code(401);
    echo json_encode(["success" => false, "message" => $MESSAGE]);
    exit;
}

// Check email uniqueness (decrypt and compare)
$allRes = executeSelect($conn, "SELECT id, email FROM users");
if ($allRes['success'] && count($allRes['data']) > 0) {
    foreach ($allRes['data'] as $row) {
        $decrypted = decryptData($row['email']);
        if (strcasecmp($decrypted, $newEmail) === 0 && (int)$row['id'] !== (int)$ACCOUNT_ID) {
            $MESSAGE = getConfigValue(
                $conn,
                'ALREADY_REGISTERED',
                "Looks like this email is already registered. Try a different email."
            );
            http_response_code(409);
            echo json_encode(["success" => false, "message" => $MESSAGE]);
            exit;
        }
    }
}

// Update email (encrypt before store)
$ENCRYPTED_EMAIL = encryptData($newEmail);
$upd = executeUpdate($conn, "UPDATE users SET email = ? WHERE id = ?", 'si', [$ENCRYPTED_EMAIL, $ACCOUNT_ID]);
if ($upd['success']) {
    $MESSAGE = getConfigValue(
        $conn,
        'UPDATE_EMAIL_SUCCESS',
        'Your email address has been updated successfully.'
    );
    echo json_encode(["success" => true, "message" => $MESSAGE]);
} else {
    $MESSAGE = getConfigValue(
        $conn,
        'UPDATE_EMAIL_FAILED',
        'We couldn’t update your email at this time. Please try again later.'
    );
    http_response_code(500);
    echo json_encode(["success" => false, "message" => $MESSAGE]);
}

exit;
?>