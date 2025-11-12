<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Dependencies
include __DIR__ . "/../connection/main_connection.php";
include __DIR__ . "/../functions/auth_checker.php";
include __DIR__ . "/../functions/select_sql.php";
include __DIR__ . "/../functions/config_msg.php";
include __DIR__ . "/../functions/user_fullname.php";

// Session token
$sessionToken = $_SESSION['token'] ?? null;
if (!$sessionToken) {
    header("Location: ../login");
    exit;
}

// Extract raw token from 'Bearer <base64>'
if (strpos($sessionToken, 'Bearer ') === 0) {
    $encodedToken = substr($sessionToken, 7);
    $token = base64_decode($encodedToken);
} else {
    header("Location: ../login");
    exit;
}

// Validate token against DB
$authResult = verifyAuthTokenfromDB($conn, $token);
if (!$authResult['success']) {
    session_destroy();
    header("Location: ../login");
    exit;
}

$user_id = isset($_SESSION['user_id']) ? intval(base64_decode($_SESSION['user_id'])) : 0;
if ($user_id <= 0) {
    header("Location: ../login");
    exit;
}

// Build base URL for absolute file paths
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submissionSuccess = true;

    // Load default status and remarks from statuses (design-aligned messaging)
    $defaultStatusName = 'Pending';
    $defaultRemarks = 'Thank you for your submission. Your application is still being processed, and additional time is required to complete the review.';
    $uiAccentColor = '#147ad6';
    try {
        $statusRes = executeSelect($conn, "SELECT name, remarks, hex_color FROM statuses WHERE is_default = 1 LIMIT 1");
        if ($statusRes['success'] && count($statusRes['data']) > 0) {
            $row = $statusRes['data'][0];
            $defaultStatusName = $row['name'] ?: $defaultStatusName;
            $defaultRemarks    = $row['remarks'] ?: $defaultRemarks;
            $uiAccentColor     = $row['hex_color'] ?: $uiAccentColor;
        }
    } catch (Throwable $e) {
        // Fallbacks already set above
    }

    // Applicant type is required
    if (!isset($_POST['applicant_type_id'])) {
        die("Error: Applicant Type ID is missing from the form submission.");
    }
    $applicant_type_id = (int)$_POST['applicant_type_id'];

    // Create or update submission record (prevent duplicate by unique keys) with default status & remarks
    $stmt = $conn->prepare(
        "INSERT INTO submissions (user_id, applicant_type_id, submitted_at, status, can_update, remarks)
         VALUES (?, ?, NOW(), ?, 0, ?)
         ON DUPLICATE KEY UPDATE
           applicant_type_id = VALUES(applicant_type_id),
           submitted_at = VALUES(submitted_at),
           status = VALUES(status),
           can_update = 0,
           remarks = VALUES(remarks)"
    );
    if (!$stmt) {
        die("Database Error (Prepare submissions upsert): " . $conn->error);
    }
    $stmt->bind_param("iiss", $user_id, $applicant_type_id, $defaultStatusName, $defaultRemarks);
    if (!$stmt->execute()) {
        die("Database Error (Execute submissions upsert): " . $stmt->error);
    }
    // Retrieve submission id for this user/type
    $stmt_id = $conn->prepare("SELECT id FROM submissions WHERE user_id = ? AND applicant_type_id = ? LIMIT 1");
    if (!$stmt_id) {
        die("Database Error (Prepare submissions id): " . $conn->error);
    }
    $stmt_id->bind_param("ii", $user_id, $applicant_type_id);
    if (!$stmt_id->execute()) {
        die("Database Error (Execute submissions id): " . $stmt_id->error);
    }
    $result_id = $stmt_id->get_result();
    if ($result_id && $row = $result_id->fetch_assoc()) {
        $new_submission_id = (int)$row['id'];
    } else {
        die("Database Error: Unable to fetch submission id.");
    }
    $stmt_id->close();
    $stmt->close();

    // Prepare for file records
    $stmt_file = $conn->prepare("INSERT INTO submission_files (submission_id, field_name, original_filename, file_path) VALUES (?, ?, ?, ?)");
    if (!$stmt_file) {
        die("Database Error (Prepare files): " . $conn->error);
    }
    // De-duplication: remove existing file entries for the same field before insert
    $stmt_file_del = $conn->prepare("DELETE FROM submission_files WHERE submission_id = ? AND field_name = ?");
    if (!$stmt_file_del) {
        die("Database Error (Prepare files delete): " . $conn->error);
    }

    // Ensure private media directory exists: pages/src/media/private
    $projectRoot = dirname(__DIR__);
    $privateDir  = $projectRoot . DIRECTORY_SEPARATOR . 'pages' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR . 'private' . DIRECTORY_SEPARATOR;
    if (!is_dir($privateDir)) {
        if (!mkdir($privateDir, 0777, true) && !is_dir($privateDir)) {
            $submissionSuccess = false;
            echo "Error: Unable to create uploads directory.<br>";
        }
    }

    // Handle files
    foreach ($_FILES as $fieldName => $file) {
        if (isset($file['error']) && $file['error'] !== UPLOAD_ERR_OK) {
            if ($file['error'] !== UPLOAD_ERR_NO_FILE) {
                $submissionSuccess = false;
                echo "Upload Error for field '$fieldName' (code: " . intval($file['error']) . ")<br>";
            }
            continue;
        }

        if (isset($file['tmp_name']) && is_uploaded_file($file['tmp_name'])) {
            $original_filename = $file['name'];
            $safe_basename = preg_replace("/[^a-zA-Z0-9\._-]/", "_", basename($original_filename));
            if ($safe_basename === '.' || $safe_basename === '..') {
                $submissionSuccess = false;
                echo "Error: Invalid filename '$original_filename'.<br>";
                continue;
            }

            // Unique filename: <generateduniqueid>_<originalname>
            try {
                $unique = bin2hex(random_bytes(8));
            } catch (Exception $e) {
                $unique = uniqid();
            }
            $ext = strtolower(pathinfo($safe_basename, PATHINFO_EXTENSION));
            $extPart = $ext ? ('.' . $ext) : '';
            // Rebuild original name without double extension
            $baseNoExt = $ext ? substr($safe_basename, 0, -strlen($extPart)) : $safe_basename;
            $targetFilename = $unique . '_' . ($baseNoExt ?: 'file') . $extPart;
            $targetPath     = $privateDir . $targetFilename;

            if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
                $submissionSuccess = false;
                echo "Error: Failed to save file locally for '$original_filename'.<br>";
                continue;
            }

            // Store absolute URL in DB: current url + /pages/src/media/private/<generateduniqueid>_<originalname>
            $file_path_to_store = $scheme . '://' . $host . '/pages/src/media/private/' . $targetFilename;

            // Ensure no duplicate per submission + field
            $stmt_file_del->bind_param("is", $new_submission_id, $fieldName);
            if (!$stmt_file_del->execute()) {
                $submissionSuccess = false;
                echo "Database Error deleting old file for '$fieldName': " . $stmt_file_del->error . "<br>";
            }

            $stmt_file->bind_param("isss", $new_submission_id, $fieldName, $original_filename, $file_path_to_store);
            if (!$stmt_file->execute()) {
                $submissionSuccess = false;
                echo "Database Error saving file info for '$fieldName': " . $stmt_file->error . "<br>";
            }
        }
    }
    $stmt_file->close();
    $stmt_file_del->close();

    // Handle text/select fields
    $stmt_data = $conn->prepare("INSERT INTO submission_data (submission_id, field_name, field_value) VALUES (?, ?, ?)");
    if (!$stmt_data) {
        $submissionSuccess = false;
        die("Database Error (Prepare data): " . $conn->error);
    }
    // De-duplication: remove existing data entries for the same field before insert
    $stmt_data_del = $conn->prepare("DELETE FROM submission_data WHERE submission_id = ? AND field_name = ?");
    if (!$stmt_data_del) {
        $submissionSuccess = false;
        die("Database Error (Prepare data delete): " . $conn->error);
    }
    foreach ($_POST as $fieldName => $value) {
        if ($fieldName === 'applicant_type_id') continue;
        $field_value = is_array($value) ? implode(', ', $value) : $value;
        $stmt_data_del->bind_param("is", $new_submission_id, $fieldName);
        if (!$stmt_data_del->execute()) {
            $submissionSuccess = false;
            echo "Database Error deleting old data for '$fieldName': " . $stmt_data_del->error . "<br>";
        }
        $stmt_data->bind_param("iss", $new_submission_id, $fieldName, $field_value);
        if (!$stmt_data->execute()) {
            $submissionSuccess = false;
            echo "Database Error saving data for '$fieldName': " . $stmt_data->error . "<br>";
        }
    }
    $stmt_data->close();
    $stmt_data_del->close();

    // Update or insert admission_controller.can_apply = 0 for this user
    $stmt_ctrl_upd = $conn->prepare("UPDATE admission_controller SET can_apply = 0 WHERE user_id = ?");
    if ($stmt_ctrl_upd) {
        $stmt_ctrl_upd->bind_param("i", $user_id);
        $stmt_ctrl_upd->execute();
        $affected = $stmt_ctrl_upd->affected_rows;
        $stmt_ctrl_upd->close();

        if ($affected === 0) {
            $stmt_ctrl_ins = $conn->prepare("INSERT INTO admission_controller (user_id, can_apply) VALUES (?, 0)");
            if ($stmt_ctrl_ins) {
                $stmt_ctrl_ins->bind_param("i", $user_id);
                $stmt_ctrl_ins->execute();
                $stmt_ctrl_ins->close();
            }
        }
    }

    // Close DB
    $conn->close();

    // Show message (no redirect)
    if (!headers_sent()) {
        header('Content-Type: text/html; charset=UTF-8');
    }
    echo '<!doctype html><html><head><meta charset="utf-8"><title>Submission Status</title>';
    echo '<style>
      :root{--accent:' . htmlspecialchars($uiAccentColor) . ';}
      body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif;padding:24px;color:#222;background:#fafafa}
      .box{max-width:780px;margin:0 auto;border:1px solid #e5e7eb;border-radius:12px;padding:24px;background:#fff;box-shadow:0 1px 3px rgba(0,0,0,.06)}
      .title{display:flex;align-items:center;gap:10px;margin:0 0 8px}
      .status-pill{display:inline-flex;align-items:center;gap:8px;background:rgba(20,122,214,.08);border:1px solid #e5e7eb;padding:6px 10px;border-radius:999px;color:#111}
      .dot{width:10px;height:10px;border-radius:50%;background:var(--accent);display:inline-block}
      .desc{margin:8px 0 0;color:#3a3a3a;white-space:pre-line}
      .actions{margin-top:18px}
      .btn{display:inline-block;padding:10px 14px;border-radius:8px;text-decoration:none}
      .btn-primary{background:var(--accent);color:#fff}
      .btn-secondary{background:#f2f4f7;color:#111;border:1px solid #d0d5dd}
    </style></head><body>';
    echo '<div class="box">';
    if ($submissionSuccess) {
        echo '<div class="title"><h2 style="color:var(--accent);margin:0">Application Submitted</h2>';
        echo '<span class="status-pill"><span class="dot"></span>' . htmlspecialchars($defaultStatusName) . '</span></div>';
        echo '<p class="desc">' . htmlspecialchars($defaultRemarks) . '</p>';
    } else {
        echo '<div class="title"><h2 style="color:#b00020;margin:0">Submission completed with errors</h2></div>';
        echo '<p class="desc">Please review the messages shown above. You can go back and try again.</p>';
    }
    echo '<div class="actions">';
    echo '<a class="btn btn-primary" href="home">Back to Home</a>';
    echo '</div></div></body></html>';
    exit;
}

// Not a POST
echo "Invalid request method.";
exit;
