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
include __DIR__ . '/../functions/update_sql.php';
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

// Validate and normalize the incoming URL
$parsed = parse_url($url);
if (!$parsed || !isset($parsed['path'])) {
    header('Content-Type: application/json');
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Invalid URL format."]); 
    exit;
}

// Normalize and validate path (handle encoded spaces and alternate base)
$path = $parsed['path'] ?? '';
$decodedPath = rawurldecode($path);

// We only allow operations within uploads/requirements path for safety
$uploadsPos = strpos($decodedPath, '/uploads/requirements/');
if ($uploadsPos === false) {
    header('Content-Type: application/json');
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "URL must point under /uploads/requirements/."]); 
    exit;
}

// Detect environment-specific bases for local rewriting when needed
$isApiUploads = strpos($decodedPath, '/STUDENT SUCCESS OFFICE - api/uploads/requirements/') === 0;
$isCapstoneUploads = strpos($decodedPath, '/STUDENT SUCCESS OFFICE - CAPSTONE 2/uploads/requirements/') === 0;

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
    // Build normalized URL with encoded spaces in base for local API mirror
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

// Ensure base host URL is available for the external updater
$hostBase = isset($UPLOAD_REQUIREMENTS_BASE_URL) ? rtrim($UPLOAD_REQUIREMENTS_BASE_URL, '/') . '/' : '';

$postFields = [
    'email' => $email,
    'password' => $password,
    'url' => $url,
    'host_url' => $hostBase,
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
// Try to decode as JSON
$decoded = json_decode($response, true);

// Derive tail path under uploads/requirements from the original URL
$uploadsPos = strpos($decodedPath, '/uploads/requirements/');
$tail = $uploadsPos !== false ? substr($decodedPath, $uploadsPos) : '';
$oldRelativePath = ltrim($tail, '/'); // e.g., uploads/requirements/...

// Compute new URL and relative path
$newUrl = null;
if (is_array($decoded)) {
    // Prefer explicit fields from upstream response
    $newUrl = $decoded['updated_url'] ?? $decoded['new_url'] ?? $decoded['file_url'] ?? $decoded['url'] ?? null;
}
if (!$newUrl && $hostBase && $tail) {
    // Fallback: rehost on configured base
    $newUrl = rtrim($hostBase, '/') . '/' . ltrim($tail, '/');
}

$newRelativePath = null;
if ($newUrl) {
    $pNew = parse_url($newUrl);
    $pNewPath = rawurldecode($pNew['path'] ?? '');
    $posNew = strpos($pNewPath, '/uploads/requirements/');
    if ($posNew !== false) {
        $newRelativePath = ltrim(substr($pNewPath, $posNew), '/');
    }
}

// Perform DB updates when we can reliably determine targets
$updates = [
    'requirements_uploads_updated' => 0,
    'submission_files_updated' => 0,
    'admission_submission_locked' => 0,
];

if (isset($conn) && !$conn->connect_error) {
    // Update requirements_uploads: replace old file_url with newUrl, matching multiple URL variants
    if ($newUrl) {
        $oldUrlVariants = [];
        $oldUrlVariants[] = $url;
        if ($hostBase && $oldRelativePath) {
            $oldUrlVariants[] = rtrim($hostBase, '/') . '/' . ltrim($oldRelativePath, '/');
        }
        // Add space/encoding variants
        $variantExtras = [];
        foreach ($oldUrlVariants as $v) {
            $variantExtras[] = str_replace(' ', '%20', $v);
            $variantExtras[] = str_replace('%20', ' ', $v);
        }
        $oldUrlVariants = array_values(array_unique(array_merge($oldUrlVariants, $variantExtras)));

        // Build dynamic IN clause
        if (count($oldUrlVariants) > 0) {
            $placeholders = implode(',', array_fill(0, count($oldUrlVariants), '?'));
            $sqlReq = "UPDATE requirements_uploads SET file_url = ? WHERE file_url IN ($placeholders)";
            $typesReq = 's' . str_repeat('s', count($oldUrlVariants));
            $paramsReq = array_merge([$newUrl], $oldUrlVariants);
            $resReq = executeUpdate($conn, $sqlReq, $typesReq, $paramsReq);
            if ($resReq['success']) {
                $updates['requirements_uploads_updated'] = intval($resReq['affected_rows'] ?? 0);
            }
        }
    }

    // Update submission_files: replace file_path when path changes; handle relative and absolute variants
    if ($oldRelativePath && $newRelativePath && $newRelativePath !== $oldRelativePath) {
        $subAffected = 0;
        // Relative variant
        $resSubRel = executeUpdate($conn, "UPDATE submission_files SET file_path = ? WHERE file_path = ?", 'ss', [$newRelativePath, $oldRelativePath]);
        if ($resSubRel['success']) {
            $subAffected += intval($resSubRel['affected_rows'] ?? 0);
        }

        // Absolute variants based on host base
        if ($hostBase) {
            $absOld = rtrim($hostBase, '/') . '/' . ltrim($oldRelativePath, '/');
            $absNew = rtrim($hostBase, '/') . '/' . ltrim($newRelativePath, '/');
            $absOldVariants = array_values(array_unique([
                $absOld,
                str_replace(' ', '%20', $absOld),
                str_replace('%20', ' ', $absOld)
            ]));
            foreach ($absOldVariants as $vOld) {
                $resSubAbs = executeUpdate($conn, "UPDATE submission_files SET file_path = ? WHERE file_path = ?", 'ss', [$absNew, $vOld]);
                if ($resSubAbs['success']) {
                    $subAffected += intval($resSubAbs['affected_rows'] ?? 0);
                }
            }
        }

        $updates['submission_files_updated'] = $subAffected;
    }

    // If external update succeeded, lock further edits for this user
    if ($statusCode && $statusCode >= 200 && $statusCode < 300) {
        $sqlLock = "UPDATE admission_submission SET can_update = 0, updated_at = NOW() WHERE user_id = ?";
        $resLock = executeUpdate($conn, $sqlLock, 'i', [$ACCOUNT_ID]);
        if ($resLock['success']) {
            $updates['admission_submission_locked'] = intval($resLock['affected_rows'] ?? 0);
        }
    }
}

// Build final response payload
header('Content-Type: application/json');
http_response_code($statusCode ?: 200);
if (is_array($decoded)) {
    $decoded['db_updates'] = $updates;
    echo json_encode($decoded);
} else {
    echo json_encode([
        'success' => ($statusCode >= 200 && $statusCode < 300),
        'raw' => $response,
        'db_updates' => $updates,
    ]);
}
exit;