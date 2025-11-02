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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Method not allowed. Use POST."]); 
    exit;
}

// Read multipart form-data: url + file
$url = $_POST['url'] ?? '';
$url = trim($url);
if (!$url || !isset($_FILES['file'])) {
    header('Content-Type: application/json');
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Missing required parameters: url or file"]); 
    exit;
}

// SSRF guard: allow only localhost Student Success API uploads path
$parsed = parse_url($url);
if (!$parsed || !isset($parsed['host']) || strtolower($parsed['host']) !== 'localhost') {
    header('Content-Type: application/json');
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Invalid file host."]); 
    exit;
}
// Normalize and validate path (handle encoded spaces and alternate base)
$path = $parsed['path'] ?? '';
$decodedPath = rawurldecode($path);
$isApiUploads = strpos($decodedPath, '/STUDENT SUCCESS OFFICE - api/uploads/requirements/') === 0;
$isCapstoneUploads = strpos($decodedPath, '/STUDENT SUCCESS OFFICE - CAPSTONE 2/uploads/requirements/') === 0;
if (!$isApiUploads && !$isCapstoneUploads) {
    header('Content-Type: application/json');
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Invalid file path."]); 
    exit;
}

// If CAPSTONE path, rewrite to api uploads path for external update service
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

// Build request to external update API from configuration
// Ensure main_connection.php provided $UPDATE_REQUIREMENTS_API_URL
if (!isset($UPDATE_REQUIREMENTS_API_URL) || !$UPDATE_REQUIREMENTS_API_URL) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Update API URL is not configured."]); 
    exit;
}

$externalEndpoint = $UPDATE_REQUIREMENTS_API_URL;

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $externalEndpoint);
curl_setopt($ch, CURLOPT_POST, true);

// Prepare fields: email, password, url, file
$fileTmp = $_FILES['file']['tmp_name'] ?? '';
$fileType = $_FILES['file']['type'] ?? 'application/octet-stream';
$fileName = $_FILES['file']['name'] ?? 'file.bin';
$curlFile = new CURLFile($fileTmp, $fileType, $fileName);

$postFields = [
    'email' => $email,
    'password' => $password,
    'url' => $url,
    'file' => $curlFile,
];

curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 60);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [ 'Accept: */*' ]);

$response = curl_exec($ch);
if ($response === false) {
    $err = curl_error($ch);
    $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    header('Content-Type: application/json');
    http_response_code(502);
    echo json_encode(["success" => false, "message" => "Update request failed: $err", "status" => $statusCode]);
    exit;
}

$statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
curl_close($ch);

// Try to decode as JSON, else pass raw
$decoded = json_decode($response, true);
if (is_array($decoded)) {
    header('Content-Type: application/json');
    http_response_code($statusCode ?: 200);
    echo json_encode($decoded);
    exit;
}

header('Content-Type: application/json');
http_response_code($statusCode ?: 200);
echo $response;
exit;