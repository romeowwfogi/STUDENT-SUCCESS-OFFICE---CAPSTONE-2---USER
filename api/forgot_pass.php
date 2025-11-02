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
$email = trim($input['email'] ?? '');

$MESSAGE = getConfigValue(
    $conn,
    'EMPTY_EMAIL_PASS',
    "To continue, please make sure you’ve entered both your email address and password."
);

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

$sql = "SELECT id, email, acc_status FROM users WHERE acc_type = 'admission'";
$result = executeSelect($conn, $sql);

$emailFound = false;
$ACCOUNT_ID = null;
$ACCOUNT_STATUS = null;

if ($result['success'] && count($result['data']) > 0) {
    foreach ($result['data'] as $row) {
        $decryptedEmail = decryptData($row['email']);

        if (strcasecmp($decryptedEmail, $email) === 0) {
            $emailFound = true;
            $ACCOUNT_ID = $row['id'];
            $ACCOUNT_STATUS = $row['acc_status'];
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

if ($ACCOUNT_STATUS === "not_verified") {
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

if ($ACCOUNT_STATUS === "banned" || $ACCOUNT_STATUS === "deleted") {
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

if ($ACCOUNT_STATUS === "deactivated") {
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

$conn->begin_transaction();
try {
    $type = 'password_reset';
    $response = executeExpirationConfig($conn, $type);

    if ($response['success']) {
        $intervalValue = $response['data']['interval_value'];
        $intervalUnit  = $response['data']['interval_unit'];
    } else {
        throw new Exception("Failed to get expiration config for '$type'");
    }

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
    $paramsCheck = [$ACCOUNT_ID, 'RESET_PASSWORD'];
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
            AND name = 'RESET_PASSWORD'
        ";
        $types3 = "si";
        $params3 = [$token, $ACCOUNT_ID];
        $result3 = executeUpdate($conn, $sql3, $types3, $params3);
    } else {
        // 🆕 INSERT new token
        $sql3 = "
            INSERT INTO tokenization (user_id, name, value, expires_at, is_used)
            VALUES (?, 'RESET_PASSWORD', ?, DATE_ADD(NOW(), INTERVAL $intervalValue $intervalUnit), 0)
        ";
        $types3 = "is";
        $params3 = [$ACCOUNT_ID, $token];
        $result3 = executeInsert($conn, $sql3, $types3, $params3);
    }

    // ⚠️ Handle errors
    if (!$result3['success']) {
        throw new Exception($result3['message']);
    }

    $sql4 = "SELECT expires_at FROM tokenization WHERE user_id = ? AND name = 'RESET_PASSWORD' ORDER BY id DESC LIMIT 1";
    $types4 = "i";
    $params4 = [$ACCOUNT_ID];
    $result4 = executeSelect($conn, $sql4, $types4, $params4);

    if ($result4['success'] && count($result4['data']) > 0) {
        $expiresAt = $result4['data'][0]['expires_at'];
    } else {
        $expiresAt = null;
    }

    // 🎨 Fetch the email template using your helper
    $sql = "SELECT subject, html_code 
        FROM email_template 
        WHERE is_active = 1 
          AND title = 'Reset Password With Link'";
    $result = executeSelect($conn, $sql);

    if ($result['success'] && count($result['data']) > 0) {
        // 🕒 Greeting message
        $greetings = getGreetingMessage();

        // 🌐 Build token verification link
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443)
            ? "https://" : "http://";
        $host = $_SERVER['HTTP_HOST'];
        $requestUri = $_SERVER['REQUEST_URI'];
        $currentUrl = $protocol . $host . $requestUri;

        $tokenLink = str_replace(
            "api/reset-password",
            "change-password?token=" . $token,
            $currentUrl
        );

        // 🕓 Format expiration date (based on DB field or 24hr default)
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
            '{{expire_at}}' => $formattedExpiresAt,
            '{{reset_link}}' => $tokenLink
        ];

        $finalEmailHTML = str_replace(
            array_keys($replacements),
            array_values($replacements),
            $templateHTML
        );

        // ✉️ Send the email
        sendEmail($email, $subject, $finalEmailHTML);

        // ✅ Return success response

        $conn->commit();
        $MESSAGE = getConfigValue(
            $conn,
            'RESET_PASSWORD_SEND_SUCCESS',
            "A password reset link has been sent to your email. Please check your inbox and reset your password within 24 hours to complete the process."
        );
        http_response_code(201);
        echo json_encode([
            "status" => "success",
            "message" => $MESSAGE
        ]);
    } else {
        // Fallback if no template found

        $conn->commit();
        $MESSAGE = getConfigValue(
            $conn,
            'RESET_PASSWORD_SEND_FAILED',
            "We couldn’t send the password reset link due to a system issue or invalid email address. Please double-check your information and try again later. If the issue persists, contact support."
        );
        http_response_code(201);
        echo json_encode([
            "status" => "success",
            "message" => $MESSAGE
        ]);
    }
} catch (Exception $e) {
    // ❌ Roll back everything on error
    $conn->rollback();
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        // "message" => "Transaction failed: " . $e->getMessage()
        "message" => "We couldn’t send the password reset link due to a system issue or invalid email address. Please double-check your information and try again later. If the issue persists, contact support."
    ]);
}
