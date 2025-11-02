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

// 🔐 Generate OTP using salted hash
function generateSaltedOTP($userId)
{
    $random = bin2hex(random_bytes(8));
    $salt = $userId . time();
    $hash = hash('sha256', $random . $salt);
    $digits = preg_replace('/\D/', '', $hash);
    return substr(str_pad($digits, 6, '0'), 0, 6);
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
unset($_SESSION['login-otp-email']);

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
        "status" => "error-resend",
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

// ⏱ Generate OTP and set expiry (10 minutes)
$OTP_CODE = generateSaltedOTP($ACCOUNT_ID);
$sql = "SELECT * FROM users WHERE id = ? AND acc_type = 'admission'";
$types = "i";
$params = [$ACCOUNT_ID];

$result = executeSelect($conn, $sql, $types, $params);

$conn->begin_transaction();
try {
    $type = 'login_otp';
    $response = executeExpirationConfig($conn, $type);

    if ($response['success']) {
        $intervalValue = $response['data']['interval_value'];
        $intervalUnit  = $response['data']['interval_unit'];
    } else {
        throw new Exception("Failed to get expiration config for '$type'");
    }

    // Check if OTP record exists for this user
    $sqlCheck = "SELECT id FROM otp_user WHERE user_id = ?";
    $typesCheck = "i";
    $paramsCheck = [$ACCOUNT_ID];
    $resultCheck = executeSelect($conn, $sqlCheck, $typesCheck, $paramsCheck);

    if ($resultCheck['success'] && count($resultCheck['data']) > 0) {
        // 🔁 Update existing OTP record
        $sql1 = "
            UPDATE otp_user 
            SET 
                value = ?,
                is_used = 0, 
                expires_at = DATE_ADD(NOW(), INTERVAL $intervalValue $intervalUnit)
            WHERE user_id = ?
        ";
        $types1 = "si";
        $params1 = [$OTP_CODE, $ACCOUNT_ID];
        $result1 = executeUpdate($conn, $sql1, $types1, $params1);

        if (!$result1['success']) {
            throw new Exception($result1['message']);
        }
    } else {
        // 🆕 Insert new OTP record if none exists
        $sql1 = "
            INSERT INTO otp_user (user_id, value, is_used, expires_at)
            VALUES (?, ?, 0, DATE_ADD(NOW(), INTERVAL $intervalValue $intervalUnit))
        ";

        $types1 = "is";
        $params1 = [$ACCOUNT_ID, $OTP_CODE];
        $result1 = executeInsert($conn, $sql1, $types1, $params1);

        if (!$result1['success']) {
            throw new Exception($result1['message']);
        }
    }

    $sql4 = "SELECT expires_at FROM otp_user WHERE user_id = ?";
    $types4 = "i";
    $params4 = [$ACCOUNT_ID];
    $result4 = executeSelect($conn, $sql4, $types4, $params4);

    if ($result4['success'] && count($result4['data']) > 0) {
        $expiresAt = $result4['data'][0]['expires_at'];
    } else {
        $expiresAt = null;
    }

    $sql = "SELECT subject, html_code 
        FROM email_template 
        WHERE is_active = 1 
          AND title = 'Login Account With OTP'";
    $result = executeSelect($conn, $sql);

    if ($result['success'] && count($result['data']) > 0) {
        // 🕒 Greeting message
        $greetings = getGreetingMessage();
        if ($expiresAt) {
            $formattedExpiresAt = (new DateTime($expiresAt))->format('F j, Y - h:i A');
        } else {
            // fallback: +1 day from now if somehow not fetched
            $formattedExpiresAt = (new DateTime('+1 day'))->format('F j, Y - h:i A');
        }
        // 🧩 Fetch email template fields
        $row = $result['data'][0];
        $subject = $row['subject'];
        $templateHTML = $row['html_code'];

        // 🔁 Replace placeholders
        $replacements = [
            '{{subject}}' => $subject,
            '{{greetings}}' => $greetings,
            '{{otp_code}}' => $OTP_CODE,
            '{{expire_at}}' => $formattedExpiresAt
        ];

        $finalEmailHTML = str_replace(
            array_keys($replacements),
            array_values($replacements),
            $templateHTML
        );
        sendEmail($email, $subject, $finalEmailHTML);
    }
    $MESSAGE = getConfigValue(
        $conn,
        'OTP_SEND_SUCCESS',
        "We’ve sent a 6-digit code to your registered email. Please check your inbox and enter the code to continue."
    );
    $_SESSION['login-otp-email'] = $email;
    http_response_code(200);
    echo json_encode([
        "status" => "success",
        "message" => $MESSAGE
    ]);
    // ✅ Commit transaction if everything succeeded
    $conn->commit();
} catch (Exception $e) {
    // ❌ Roll back everything on error
    $MESSAGE = getConfigValue(
        $conn,
        'OTP_SEND_FAILED',
        "We couldn’t send the OTP CODE due to a system issue or invalid email address. Please double-check your information and try again later. If the issue persists, contact support."
    );
    $conn->rollback();
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => $MESSAGE
    ]);
}
