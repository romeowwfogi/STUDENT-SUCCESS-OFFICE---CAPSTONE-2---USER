<?php
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

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
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
    echo json_encode(["status" => "error", "message" => $MESSAGE]);
    exit;
}

// 📨 Read JSON input
$input = json_decode(file_get_contents("php://input"), true);
$email = trim($input['email_address'] ?? '');
$password = $input['password'] ?? '';

$MESSAGE = getConfigValue(
    $conn,
    'EMPTY_EMAIL_PASS',
    "To continue, please make sure you’ve entered both your email address and password."
);

if (empty($email) || empty($password)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => $MESSAGE]);
    exit;
}

$MESSAGE = getConfigValue(
    $conn,
    'INVALID_EMAIL',
    "Hmm... that doesn't look like a valid email address."
);

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "message" => $MESSAGE
    ]);
    exit;
}

// Fetch all encrypted emails
$sql = "SELECT id, email FROM users WHERE acc_type = 'admission'";
$result = executeSelect($conn, $sql);

$emailFound = false;
$ACCOUNT_ID = null;

if ($result['success'] && count($result['data']) > 0) {
    foreach ($result['data'] as $row) {
        $decryptedEmail = decryptData($row['email']);

        if (strcasecmp($decryptedEmail, $email) === 0) {
            $emailFound = true;
            $ACCOUNT_ID = $row['id'];
            break;
        }
    }
}

// If not found, return error
if (!$emailFound) {
    $MESSAGE = getConfigValue(
        $conn,
        'NOT_REGISTER',
        "We couldn’t find an account with that email address. Please check and try again."
    );

    http_response_code(404);
    echo json_encode([
        "status" => "error",
        "message" => $MESSAGE
    ]);
    exit;
}

// ✅ Email exists — check verification and activation status

$sql = "SELECT acc_status FROM users WHERE id = ? AND acc_type = 'admission'";
$types = "i";
$params = [$ACCOUNT_ID];

$result = executeSelect($conn, $sql, $types, $params);

if ($result['success'] && count($result['data']) > 0) {
    $accStatus = $result['data'][0]['acc_status'];
}

if ($accStatus === "not_verified") {
    $MESSAGE = getConfigValue(
        $conn,
        'NOT_VERIFIED_ACCOUNT',
        "Your account hasn’t been verified yet. Please check your inbox (and spam folder) for the verification link."
    );
    http_response_code(403);
    echo json_encode([
        "status" => "not_verified",
        "message" => $MESSAGE
    ]);
    exit;
}

if ($accStatus === "banned" || $accStatus === "deleted") {
    $MESSAGE = getConfigValue(
        $conn,
        'ACCOUNT_BANNED_DELETED',
        "It looks like your account has been deactivated or suspended. If this is unexpected, please contact our support team to restore access."
    );
    http_response_code(403);
    echo json_encode([
        "status" => "error",
        "message" => $MESSAGE
    ]);
    exit;
}

if ($accStatus === "deactivated") {
    $MESSAGE = getConfigValue(
        $conn,
        'ACCOUNT_DEACTIVATED',
        "It looks like your account has been deactivated or suspended. If this is unexpected, please contact our support team to restore access."
    );
    http_response_code(403);
    echo json_encode([
        "status" => "deactivated",
        "message" => $MESSAGE
    ]);
    exit;
}

// $sql = "SELECT attempt_left FROM acc_locking WHERE id = ?";
// $types = "i";
// $params = [$ACCOUNT_ID];

// $result = executeSelect($conn, $sql, $types, $params);

// if ($result['success'] && count($result['data']) > 0) {
//     $attempt_left = $result['data'][0]['attempt_left'];
// }

// if ($attempt_left < 1) {
// }


$sql = "SELECT id, email, password_hash, acc_status FROM users WHERE acc_type = 'admission'";
$result = executeSelect($conn, $sql);

$emailFound = false;
$userData = [];
$EMAIL_ENCRYPTED = null;
$HASH_PASSWORD = null;

if ($result['success'] && count($result['data']) > 0) {
    foreach ($result['data'] as $row) {
        $decryptedEmail = decryptData($row['email']);
        $EMAIL_ENCRYPTED = $row['email'];

        if (strcasecmp($decryptedEmail, $email) === 0) {
            $emailFound = true;
            $userData = $row;
            break;
        }
    }
}

if (!$emailFound) {
    http_response_code(404);
    $MESSAGE = getConfigValue(
        $conn,
        'NOT_REGISTER',
        "We couldn’t find an account with that email address. Please check and try again."
    );
    echo json_encode([
        "status" => "error",
        "message" => $MESSAGE
    ]);
    exit;
}

$HASH_PASSWORD = $userData['password_hash'];

if (!password_verify($password, $HASH_PASSWORD)) {
    http_response_code(401);
    $MESSAGE = getConfigValue(
        $conn,
        'INVALID_PASSWORD',
        "Login unsuccessful — the password entered is incorrect. You can try again or reset your password to regain access."
    );
    echo json_encode([
        "status" => "error",
        "message" => $MESSAGE
    ]);
    exit;
}

$conn->begin_transaction();
try {
    $type = 'session';
    $response = executeExpirationConfig($conn, $type);

    if ($response['success']) {
        $intervalValue = $response['data']['interval_value'];
        $intervalUnit  = $response['data']['interval_unit'];
    } else {
        throw new Exception("Failed to get expiration config for '$type'");
    }

    $token = null;

    do {
        // Generate a random 64-character token
        $token = bin2hex(random_bytes(32));

        // Use your executeSelect() helper to check for duplicates
        $sql = "SELECT value FROM tokenization WHERE value = ?";
        $types = "s";
        $params = [$token];
        $result = executeSelect($conn, $sql, $types, $params);

        // Token exists if query succeeded and returned at least one row
        $tokenExists = ($result['success'] && count($result['data']) > 0);
    } while ($tokenExists);

    $sqlCheck = "SELECT id FROM tokenization WHERE user_id = ? AND name = ?";
    $typesCheck = "is";
    $paramsCheck = [$ACCOUNT_ID, 'SESSION'];
    $resultCheck = executeSelect($conn, $sqlCheck, $typesCheck, $paramsCheck);

    if ($resultCheck['success'] && count($resultCheck['data']) > 0) {
        // 🔁 UPDATE existing token
        $sql3 = "
            UPDATE tokenization
            SET 
                value = ?, 
                expires_at = DATE_ADD(NOW(), INTERVAL $intervalValue $intervalUnit),
                is_used = 0
            WHERE user_id = ?
            AND name = 'SESSION'
        ";
        $types3 = "si";
        $params3 = [$token, $ACCOUNT_ID];
        $result3 = executeUpdate($conn, $sql3, $types3, $params3);
    } else {
        // 🆕 INSERT new token
        $sql3 = "
            INSERT INTO tokenization (user_id, name, value, expires_at, is_used)
            VALUES (?, 'SESSION', ?, DATE_ADD(NOW(), INTERVAL $intervalValue $intervalUnit), 0)
        ";
        $types3 = "is";
        $params3 = [$ACCOUNT_ID, $token];
        $result3 = executeInsert($conn, $sql3, $types3, $params3);
    }

    // ⚠️ Handle errors
    if (!$result3['success']) {
        throw new Exception($result3['message']);
    }

    http_response_code(200);
    $MESSAGE = getConfigValue(
        $conn,
        'LOGIN_SUCCESS',
        "Login successful! Your account is now active and ready to use."
    );
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $_SESSION['token'] = 'Bearer ' . base64_encode($token);
    $_SESSION['email_address'] = $email;
    $_SESSION['password'] = $password;

    $conn->commit();
    echo json_encode([
        "status" => "success",
        "message" => $MESSAGE,
        "token" => 'Bearer ' . base64_encode($token)
    ]);
} catch (Exception $e) {
    // ❌ Roll back everything on error
    $conn->rollback();
    $MESSAGE = getConfigValue(
        $conn,
        'LOGIN_ERROR',
        "We couldn’t login your account due to a system issue. Please double-check your information and try again later. If the issue persists, contact support."
    );
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        // "message" => "Transaction failed: " . $e->getMessage()
        "message" => $MESSAGE,
    ]);
}
