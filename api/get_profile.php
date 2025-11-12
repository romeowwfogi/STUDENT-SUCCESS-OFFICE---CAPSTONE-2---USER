<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

require_once __DIR__ . '/../connection/main_connection.php';
require_once __DIR__ . '/../functions/auth_checker.php';
require_once __DIR__ . '/../functions/select_sql.php';
require_once __DIR__ . '/../functions/config_msg.php';
require_once __DIR__ . '/../functions/user_fullname.php';
require_once __DIR__ . '/../functions/en-de_crypt.php';

// Simple file-based logger for this endpoint
function log_api_error($message)
{
    $ts = date('Y-m-d H:i:s');
    $line = "[$ts] get_profile.php - $message\n";
    error_log($line, 3, __DIR__ . '/php-error.log');
}

try {
    $sessionToken = $_SESSION['token'] ?? null;
    if (!$sessionToken) {
        log_api_error('No session token present.');
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'No session.']);
        exit;
    }

    // Extract raw token from 'Bearer <base64>' format
    if (strpos($sessionToken, 'Bearer ') === 0) {
        $encodedToken = substr($sessionToken, 7);
        $token = base64_decode($encodedToken);
    } else {
        log_api_error('Invalid session format (missing Bearer prefix).');
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Invalid session format.']);
        exit;
    }

    $authResult = verifyAuthTokenfromDB($conn, $token);
    if (!$authResult['success']) {
        log_api_error('Unauthorized: token validation failed.');
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized.']);
        exit;
    }

    $user_id = (int)base64_decode($_SESSION['user_id']);
    if (!$user_id) {
        log_api_error('Invalid user id from session.');
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid user.']);
        exit;
    }

    // Fetch fullname
    $fn = fetchFullnameFromDB($conn, $user_id);
    $first_name = '';
    $middle_name = '';
    $last_name = '';
    $suffix = '';
    if ($fn['success'] === true && isset($fn['data'])) {
        $first_name = $fn['data']['first_name'] ?? '';
        $middle_name = $fn['data']['middle_name'] ?? '';
        $last_name = $fn['data']['last_name'] ?? '';
        $suffix = $fn['data']['suffix'] ?? '';
    }

    // Fetch decrypted email
    $EMAIL_ADDRESS = '';
    $resUserEmail = executeSelect($conn, 'SELECT email FROM users WHERE id = ?', 'i', [$user_id]);
    if ($resUserEmail['success'] && count($resUserEmail['data']) > 0) {
        $EMAIL_ADDRESS = decryptData($resUserEmail['data'][0]['email']);
    } else if (!$resUserEmail['success']) {
        log_api_error('Failed to fetch user email from DB.');
    }

    echo json_encode([
        'success' => true,
        'data' => [
            'first_name' => $first_name,
            'middle_name' => $middle_name,
            'last_name' => $last_name,
            'suffix' => $suffix,
            'email' => $EMAIL_ADDRESS,
        ]
    ]);
} catch (Throwable $e) {
    log_api_error('Server error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error.']);
}