<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Basic auth guard (require logged-in user)
$ACCOUNT_ID = isset($_SESSION['user_id']) ? base64_decode($_SESSION['user_id']) : null;
if (!$ACCOUNT_ID) {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(["success" => false, "message" => "Unauthorized"]);
    exit;
}

$url = isset($_GET['url']) ? trim($_GET['url']) : '';
if ($url === '') {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(["success" => false, "message" => "Missing url"]);
    exit;
}

$parsed = parse_url($url);
$projectRoot = dirname(__DIR__);
$privateDir = $projectRoot . DIRECTORY_SEPARATOR . 'pages' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . 'private' . DIRECTORY_SEPARATOR;

$isLocalPrivate = false;
$targetFsPath = null;
if ($parsed && isset($parsed['path'])) {
    $path = str_replace(['\\'], '/', $parsed['path']);
    $pos = stripos($path, '/pages/src/media/private/');
    if ($pos !== false) {
        $filename = substr($path, $pos + strlen('/pages/src/media/private/'));
        if ($filename !== '' && preg_match('/^[A-Za-z0-9._-]+$/', $filename)) {
            $targetFsPath = $privateDir . $filename;
            $isLocalPrivate = file_exists($targetFsPath);
        }
    }
}

if ($isLocalPrivate && $targetFsPath) {
    // Detect content type by extension
    $ext = strtolower(pathinfo($targetFsPath, PATHINFO_EXTENSION));
    $ct = 'application/octet-stream';
    if (in_array($ext, ['png','jpg','jpeg','gif','webp'])) $ct = 'image/' . ($ext === 'jpg' ? 'jpeg' : $ext);
    elseif ($ext === 'pdf') $ct = 'application/pdf';
    elseif (in_array($ext, ['txt','log'])) $ct = 'text/plain; charset=utf-8';
    elseif (in_array($ext, ['csv'])) $ct = 'text/csv; charset=utf-8';
    elseif (in_array($ext, ['json'])) $ct = 'application/json; charset=utf-8';
    elseif (in_array($ext, ['doc','docx'])) $ct = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
    elseif (in_array($ext, ['xls','xlsx'])) $ct = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';

    header('Content-Type: ' . $ct);
    header('X-Proxy-Source: local');
    readfile($targetFsPath);
    exit;
}

// For non-local paths, deny for safety
http_response_code(403);
header('Content-Type: application/json');
echo json_encode(["success" => false, "message" => "Preview not allowed for remote URL"]);
exit;
?>