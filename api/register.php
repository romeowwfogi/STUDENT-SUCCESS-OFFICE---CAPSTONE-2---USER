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

$input = json_decode(file_get_contents("php://input"), true);
$email = trim($input['email'] ?? '');
$password = $input['password'] ?? '';

$MESSAGE = getConfigValue(
    $conn,
    'EMPTY_EMAIL_PASS',
    "To continue, please make sure you’ve entered both your email address and password."
);

if (empty($email) || empty($password)) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => $MESSAGE]);
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
        "success" => false,
        "message" => $MESSAGE
    ]);
    exit;
}

$validationErrors = validatePassword($password);

if (!empty($validationErrors)) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => $validationErrors
    ]);
    exit;
}

$MESSAGE = getConfigValue(
    $conn,
    'ALREADY_REGISTERED',
    "Looks like this email is already registered. Try logging in instead."
);

$sql = "SELECT email FROM users";
$result = executeSelect($conn, $sql);

if ($result['success'] && count($result['data']) > 0) {
    foreach ($result['data'] as $row) {
        $decryptedEmail = decryptData($row['email']);

        if (strcasecmp($decryptedEmail, $email) === 0) {
            http_response_code(409);
            echo json_encode([
                "success" => false,
                "message" => $MESSAGE
            ]);
            exit;
        }
    }
}

$conn->begin_transaction();
try {
    // Use a consistent expiration config for account verification links
    $type = 'activation_account';
    $response = executeExpirationConfig($conn, $type);

    if ($response['success']) {
        $intervalValue = $response['data']['interval_value'];
        $intervalUnit  = $response['data']['interval_unit'];
    } else {
        throw new Exception("Failed to get expiration config for '$type'");
    }

    // Insert user
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $ENCRYPTED_EMAIL = encryptData($email);

    $sql1 = "INSERT INTO users (email, password_hash, role, acc_type)
             VALUES (?, ?, ?, ?)";
    $types1 = "ssss";
    $params1 = [$ENCRYPTED_EMAIL, $passwordHash, "applicant", "admission"];
    $result1 = executeInsert($conn, $sql1, $types1, $params1);

    if (!$result1['success']) {
        throw new Exception($result1['message']);
    }

    $userId = $result1['insert_id'];

    // Insert into acc_locking
    $sql2 = "INSERT INTO acc_locking (user_id)
             VALUES (?)";
    $types2 = "i";
    $params2 = [$userId];
    $result2 = executeInsert($conn, $sql2, $types2, $params2);

    if (!$result2['success']) {
        throw new Exception($result2['message']);
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

    // Insert into tokenization
    $sql3 = "INSERT INTO tokenization (user_id, name, value, expires_at)
         VALUES (?, ?, ?, DATE_ADD(NOW(), INTERVAL $intervalValue $intervalUnit))";

    $types3 = "iss";
    $params3 = [$userId, 'VERIFY_ACCOUNT', $token];
    $result3 = executeInsert($conn, $sql3, $types3, $params3);

    if (!$result3['success']) {
        throw new Exception($result3['message']);
    }

    $sql4 = "SELECT expires_at FROM tokenization WHERE user_id = ? AND name = 'VERIFY_ACCOUNT' ORDER BY id DESC LIMIT 1";
    $types4 = "i";
    $params4 = [$userId];
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
          AND title = 'Account Registration'";
    $result = executeSelect($conn, $sql);

    if ($result['success'] && count($result['data']) > 0) {
        // 🕒 Greeting message
        $greetings = getGreetingMessage();

        // 🌐 Build absolute verification link independent of current route
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443)
            ? "https://" : "http://";
        $host = $_SERVER['HTTP_HOST'];
        $requestUri = $_SERVER['REQUEST_URI'];
        $pathOnly = parse_url($requestUri, PHP_URL_PATH);
        // Remove any trailing /api/... segment to get app base path
        $basePath = preg_replace('#/api/.*$#', '', $pathOnly);
        $tokenLink = $protocol . $host . rtrim($basePath, '/') . '/verify-account?token=' . urlencode($token);

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
            '{{verification_link}}' => $tokenLink,
            '{{expire_at}}' => $formattedExpiresAt
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
            'REGISTER_SUCCESS',
            "Your account has been created successfully! Please check your email for a verification link."
        );
        http_response_code(201);
        echo json_encode([
            "success" => true,
            "message" => $MESSAGE
        ]);
    } else {
        // Fallback if no template found

        $conn->commit();
        $MESSAGE = getConfigValue(
            $conn,
            'REGISTER_SUCCESS',
            "Your account has been created successfully! Please check your email for a verification link."
        );
        http_response_code(201);
        echo json_encode([
            "success" => true,
            "message" => $MESSAGE
        ]);
    }
} catch (Exception $e) {
    // ❌ Roll back everything on error
    $conn->rollback();
    http_response_code(500);
    echo json_encode([
        "success" => false,
        // "message" => "Transaction failed: " . $e->getMessage()
        "message" => "We couldn’t create your account right now. Please try again in a moment."
    ]);
}
