<?php
function fetchFullnameFromDB($conn, $user_id)
{
    date_default_timezone_set('Asia/Manila');
    ini_set('display_errors', 0);              // Hide errors from browser (production-safe)
    ini_set('log_errors', 1);                  // Enable error logging
    ini_set('error_log', __DIR__ . '/php-error.log'); // Log errors to a file in this directory
    error_reporting(E_ALL);                    // Report all errors

    error_log("[" . date('Y-m-d H:i:s') . "]");

    $sql = "SELECT * FROM user_fullname WHERE user_id = ?";
    $types = "i";
    $params = [$user_id];
    $result = executeSelect($conn, $sql, $types, $params);
    if ($result['success'] && count($result['data']) > 0) {
        $user_id = $result['data'][0]['user_id'];
        $first_name = $result['data'][0]['first_name'];
        $middle_name = $result['data'][0]['middle_name'];
        $last_name = $result['data'][0]['last_name'];
        $suffix = $result['data'][0]['suffix'];
        return [
            "success" => true,
            "data" => [
                "first_name" => $first_name,
                "middle_name" => $middle_name,
                "last_name" => $last_name,
                "suffix" => $suffix
            ]
        ];
    }

    return [
        "success" => false,
        "message" => ''
    ];
}
