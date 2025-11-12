<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

// Basic auth guard (require logged-in user)
$ACCOUNT_ID = isset($_SESSION['user_id']) ? base64_decode($_SESSION['user_id']) : null;
if (!$ACCOUNT_ID) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Unauthorized"]);
    exit;
}

include __DIR__ . '/../connection/main_connection.php';
include __DIR__ . '/../functions/update_sql.php';
include __DIR__ . '/../functions/select_sql.php';

// Expect multipart form-data: url + file
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Method not allowed"]);
    exit;
}

$url = isset($_POST['url']) ? trim($_POST['url']) : '';
$submissionId = isset($_POST['submission_id']) ? intval($_POST['submission_id']) : 0;
if ($url === '' || !isset($_FILES['file'])) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Missing url or file"]);
    exit;
}

// Resolve local path if the URL points to our private uploads
$parsed = parse_url($url);
$projectRoot = dirname(__DIR__);
$privateDir = $projectRoot . DIRECTORY_SEPARATOR . 'pages' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . 'private' . DIRECTORY_SEPARATOR;

$isLocalPrivate = false;
$targetFsPath = null;
if ($parsed && isset($parsed['path'])) {
    // Normalize path and check it ends with /pages/src/media/private/<filename>
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

$affectedDb = 0;
$saved = false;
$err = null;

if ($isLocalPrivate && $targetFsPath) {
    // Replace file contents on disk
    $tmp = $_FILES['file']['tmp_name'] ?? '';
    $origName = $_FILES['file']['name'] ?? '';
    if ($tmp && is_uploaded_file($tmp)) {
        // Generate a new unique filename for the replacement and update DB file_path
        try {
            $safe_basename = preg_replace('/[^a-zA-Z0-9\._-]/', '_', basename($origName));
            if ($safe_basename === '.' || $safe_basename === '..') {
                throw new Exception('Invalid filename');
            }
            // Build unique filename similar to submit flow
            try {
                $unique = bin2hex(random_bytes(8));
            } catch (Throwable $e) {
                $unique = uniqid();
            }
            $ext = strtolower(pathinfo($safe_basename, PATHINFO_EXTENSION));
            $extPart = $ext ? ('.' . $ext) : '';
            $baseNoExt = $ext ? substr($safe_basename, 0, -strlen($extPart)) : $safe_basename;
            $newFilename = $unique . '_' . ($baseNoExt ?: 'file') . $extPart;
            $newFsPath = $privateDir . $newFilename;

            // Write new file bytes
            $bytes = file_get_contents($tmp);
            if ($bytes === false) {
                throw new Exception('Failed reading upload');
            }
            $res = @file_put_contents($newFsPath, $bytes);
            if ($res === false) {
                throw new Exception('Failed writing file');
            }

            // Build absolute URL using original URL’s scheme/host
            $scheme = isset($parsed['scheme']) ? $parsed['scheme'] : 'http';
            $host = isset($parsed['host']) ? $parsed['host'] : ($_SERVER['HTTP_HOST'] ?? 'localhost');
            $port = isset($parsed['port']) ? (':' . $parsed['port']) : '';
            $newUrl = $scheme . '://' . $host . $port . '/pages/src/media/private/' . $newFilename;

            // Update DB: original_filename and file_path
            $sql = "UPDATE submission_files SET original_filename = ?, file_path = ? WHERE file_path = ?";
            $upd = executeUpdate($conn, $sql, 'sss', [$origName, $newUrl, $url]);
            if ($upd['success']) {
                $affectedDb = intval($upd['affected_rows'] ?? 0);
            }

            // Remove old file to avoid orphaned storage
            @unlink($targetFsPath);

            $saved = true;

            // Optionally lock submission edits if submission_id is provided and belongs to user
            if ($submissionId > 0) {
                $check = executeSelect($conn, "SELECT id FROM submissions WHERE id = ? AND user_id = ?", 'ii', [$submissionId, $ACCOUNT_ID]);
                if ($check['success'] && count($check['data']) > 0) {
                    executeUpdate($conn, "UPDATE submissions SET can_update = 0 WHERE id = ?", 'i', [$submissionId]);
                }
            }
        } catch (Throwable $e) {
            $err = $e->getMessage();
        }
    } else {
        $err = 'Invalid upload';
    }
} else {
    // Not a local file we manage; attempt remote update if configured (optional)
    // For now, just return failure to avoid unsafe SSRF.
    $err = 'Unsupported file location';
}

if ($saved) {
    echo json_encode([
        'success' => true,
        'message' => 'File replaced',
        'db_updates' => [
            'submission_files_updated' => $affectedDb,
            'requirements_uploads_updated' => 0
        ]
    ]);
} else {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $err ?: 'Failed to replace file',
        'db_updates' => [
            'submission_files_updated' => 0,
            'requirements_uploads_updated' => 0
        ]
    ]);
}

exit;
