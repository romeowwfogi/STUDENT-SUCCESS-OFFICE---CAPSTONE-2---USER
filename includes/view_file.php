<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Example access control — only logged-in users
$sessionToken = $_SESSION['token'] ?? null;

if (!$sessionToken) {
    header('HTTP/1.1 403 Forbidden');
    exit('Access denied: not logged in.');
}


$baseDir = 'pags/src/media/private/admission/'; // allowed folder
$fileName = basename($_GET['file'] ?? ''); // strips dangerous paths
$imagePath = realpath($baseDir . $fileName);

// ✅ Check 1: file exists
if (!$imagePath || !file_exists($imagePath)) {
    header('HTTP/1.1 404 Not Found');
    exit('File not found.');
}

// ✅ Check 2: ensure file is inside allowed directory
if (strpos($imagePath, realpath($baseDir)) !== 0) {
    header('HTTP/1.1 403 Forbidden');
    exit('Access denied.');
}

// ✅ Serve image safely
$mime = mime_content_type($imagePath);
header("Content-Type: $mime");
header('Content-Length: ' . filesize($imagePath));
readfile($imagePath);
exit;