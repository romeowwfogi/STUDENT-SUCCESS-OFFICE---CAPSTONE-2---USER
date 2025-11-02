<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

include "../connection/main_connection.php";
include "../functions/config_msg.php";
include "../functions/insert_sql.php";
include "../functions/select_sql.php";

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Server connection failed. Please try again."]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $MESSAGE = getConfigValue($conn, 'REQUEST_METHOD_POST', 'Use POST to send support messages.');
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => $MESSAGE]);
    exit;
}

$input = json_decode(file_get_contents("php://input"), true);
$name = trim($input['name'] ?? '');
$email = trim($input['email_address'] ?? '');
$subject = trim($input['subject'] ?? '');
$message = trim($input['message'] ?? '');

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $MESSAGE = getConfigValue($conn, 'INVALID_EMAIL', "Please provide a valid email address.");
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => $MESSAGE]);
    exit;
}

if ($subject === '' || $message === '') {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Subject and message are required."]);
    exit;
}

// Optional: default name if not provided
if ($name === '') {
    $name = 'Anonymous';
}

// Store contact in contact_support table
$insertSql = "INSERT INTO contact_support (name, email, subject, message) VALUES (?, ?, ?, ?)";
$insert = executeInsert($conn, $insertSql, "ssss", [$name, $email, $subject, $message]);

if (!$insert['success']) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Failed to record your message. Please try again later."]);
    exit;
}

$MESSAGE = getConfigValue(
    $conn,
    'SUPPORT_MESSAGE_SENT',
    'Thanks! Your message has been sent. We will reply to your email.'
);

http_response_code(200);
echo json_encode(["status" => "success", "message" => $MESSAGE]);