<?php
function verifyAuthTokenfromDB($conn, $token)
{
    date_default_timezone_set('Asia/Manila');
    ini_set('display_errors', 0);              // Hide errors from browser (production-safe)
    ini_set('log_errors', 1);                  // Enable error logging
    ini_set('error_log', __DIR__ . '/php-error.log'); // Log errors to a file in this directory
    error_reporting(E_ALL);                    // Report all errors

    error_log("[" . date('Y-m-d H:i:s') . "]");

    $sql = "SELECT * FROM tokenization WHERE value = ?";
    $types = "s";
    $params = [$token];
    $result = executeSelect($conn, $sql, $types, $params);
    if ($result['success'] && count($result['data']) > 0) {
        $expires_at = $result['data'][0]['expires_at'];
        
        $user_id = $result['data'][0]['user_id'];
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['user_id'] = base64_encode($user_id);

        $current_time = new DateTime();
        $expiry_time = new DateTime($expires_at);

        if ($current_time > $expiry_time) {
            $MESSAGE = getConfigValue(
                $conn,
                'SESSION_EXPIRED',
                "Your session has expired or you’re not logged in. Please sign in to regain access."
            );
            return [
                "success" => false,
                "message" => $MESSAGE
            ];
        }

        $MESSAGE = getConfigValue(
            $conn,
            'LOGIN_SUCCESS',
            "Login successful! Your account is now active and ready to use."
        );
        return [
            "success" => true,
            "message" => $MESSAGE
        ];
    }


    $MESSAGE = getConfigValue(
        $conn,
        'UNAUTHENTICATED',
        "Authentication required. Please log in to access this resource."
    );
    return [
        "success" => false,
        "message" => ''
    ];
}
