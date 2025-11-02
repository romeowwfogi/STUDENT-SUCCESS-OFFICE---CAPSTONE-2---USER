<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Basic auth check via session token
$sessionToken = $_SESSION['token'] ?? null;
$ACCOUNT_ID = isset($_SESSION['user_id']) ? base64_decode($_SESSION['user_id']) : null;
if (!$sessionToken || !$ACCOUNT_ID) {
    header('Content-Type: application/json');
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Unauthorized access."]); 
    exit;
}

// Extract raw token from 'Bearer <base64>' format
if (strpos($sessionToken, 'Bearer ') === 0) {
    $encodedToken = substr($sessionToken, 7);
    $rawToken = base64_decode($encodedToken);
} else {
    header('Content-Type: application/json');
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Invalid session token format."]); 
    exit;
}

// Validate token against DB (optional but safer)
include __DIR__ . '/../connection/main_connection.php';
include __DIR__ . '/../functions/select_sql.php';
if ($conn && !$conn->connect_error) {
    $sql = "SELECT id FROM tokenization WHERE user_id = ? AND name = 'SESSION' AND value = ? AND is_used = 0";
    $types = "is";
    $params = [$ACCOUNT_ID, $rawToken];
    $result = executeSelect($conn, $sql, $types, $params);
    if (!$result['success'] || count($result['data']) === 0) {
        header('Content-Type: application/json');
        http_response_code(401);
        echo json_encode(["success" => false, "message" => "Session expired or invalid."]); 
        exit;
    }
}

// Ensure credentials exist in session
$email = $_SESSION['email_address'] ?? '';
$password = $_SESSION['password'] ?? '';
if (!$email || !$password) {
    header('Content-Type: application/json');
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Missing credentials in session."]); 
    exit;
}

// Read URL (the file to preview from external API storage)
$url = $_GET['url'] ?? '';
$url = trim($url);
if (!$url) {
    header('Content-Type: application/json');
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Missing required parameter: url"]);
    exit;
}

// Normalize and validate path (handle encoded spaces and alternate base)
$path = $parsed['path'] ?? '';
$decodedPath = rawurldecode($path);

// If CAPSTONE path, rewrite to api uploads path for external preview service
if ($isCapstoneUploads) {
    $uploadsPos = strpos($decodedPath, '/uploads/requirements/');
    $tail = $uploadsPos !== false ? substr($decodedPath, $uploadsPos) : '';
    if (!$tail) {
        header('Content-Type: application/json');
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Invalid file path."]); 
        exit;
    }
    // Build normalized URL with encoded spaces in base
    $url = 'http://localhost/STUDENT%20SUCCESS%20OFFICE%20-%20api/' . ltrim($tail, '/');
}

// Build request to external preview API
$externalEndpoint = 'http://localhost/STUDENT%20SUCCESS%20OFFICE%20-%20api/preview-requirement.php';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $externalEndpoint);
curl_setopt($ch, CURLOPT_POST, true);
// Let cURL set proper multipart/form-data with boundary automatically
$postFields = [
    'email' => $email,
    'password' => $password,
    'url' => $url,
];
curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true); // include headers in output for content-type extraction
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
if ($response === false) {
    $err = curl_error($ch);
    curl_close($ch);
    header('Content-Type: application/json');
    http_response_code(502);
    echo json_encode(["success" => false, "message" => "Preview request failed: $err"]);
    exit;
}

$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
$headers = substr($response, 0, $headerSize);
$body = substr($response, $headerSize);
curl_close($ch);

// Pass through status code
http_response_code($statusCode ?: 200);

// If external responded JSON, forward as JSON
if ($contentType && stripos($contentType, 'application/json') !== false) {
    header('Content-Type: application/json');
    echo $body;
    exit;
}

// Otherwise stream bytes with detected content type
header('Content-Type: ' . ($contentType ?: 'application/octet-stream'));
// Try to pass content-length if present in upstream headers
if (preg_match('/^Content-Length:\s*(\d+)/mi', $headers, $m)) {
    header('Content-Length: ' . $m[1]);
}
echo $body;
exit;