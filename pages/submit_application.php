<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include required files for authentication
include "connection/main_connection.php";
include "functions/auth_checker.php";
include "functions/select_sql.php";
include "functions/config_msg.php";
include "functions/user_fullname.php";

// Get token from session (correct key is 'token', not 'authToken')
$sessionToken = $_SESSION['token'] ?? null;

if (!$sessionToken) {
    header("Location: ../login");
    exit;
}

// Extract the actual token from 'Bearer base64_encoded_token' format
if (strpos($sessionToken, 'Bearer ') === 0) {
    $encodedToken = substr($sessionToken, 7); // Remove 'Bearer ' prefix
    $token = base64_decode($encodedToken);
} else {
    // If token format is unexpected, redirect to login
    header("Location: ../login");
    exit;
}

// Validate token against database
$authResult = verifyAuthTokenfromDB($conn, $token);

if (!$authResult['success']) {
    // Token is invalid, expired, or not found - redirect to login
    session_destroy();
    header("Location: ../login");
    exit;
}

$user_id = base64_decode($_SESSION['user_id']);

// Helper: Upload a file to the external API and return its JSON result
function uploadToExternalApi($tmpPath, $mimeType, $originalName, $email, $password, $folder = 'requirements')
{
    include "connection/main_connection.php";
    // Target API URL (encode spaces to avoid issues)
    $apiUrl = $UPLOAD_REQUIREMENTS_IMAGES_API;

    // Title is filename
    $title = $originalName;

    // Optional base URL parameter (if API needs it). Using localhost base.
    $baseUrl = $UPLOAD_REQUIREMENTS_BASE_URL;

    // Create CURLFile for multipart upload
    $curlFile = new CURLFile($tmpPath, $mimeType ?: 'application/octet-stream', $originalName);

    $postFields = [
        'email'   => $email,
        'password' => $password,
        'title'   => $title,
        'url'     => $baseUrl,
        'folder'  => $folder,
        'file'    => $curlFile,
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    // Accept JSON
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: */*',
    ]);

    $response = curl_exec($ch);
    $curlErr  = curl_error($ch);
    $status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response === false) {
        return [
            'success' => false,
            'message' => 'cURL error: ' . $curlErr,
            'status'  => $status,
        ];
    }

    $decoded = json_decode($response, true);
    if (!is_array($decoded)) {
        return [
            'success' => false,
            'message' => 'Invalid JSON response from upload API',
            'raw'     => $response,
            'status'  => $status,
        ];
    }

    return $decoded;
}

// Check if the form was actually submitted via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // --- 1. Get Predefined User ID, Applicant Type ID, and create the main submission ---

    // ----------------------------------------

    // Check if applicant_type_id was sent
    if (!isset($_POST['applicant_type_id'])) {
        die("Error: Applicant Type ID is missing from the form submission.");
    }
    $applicant_type_id = (int)$_POST['applicant_type_id'];

    // Use prepared statements to prevent SQL injection
    $stmt = $conn->prepare("INSERT INTO submissions (user_id, applicant_type_id, submitted_at, status) VALUES (?, ?, NOW(), 'Pending')");
    if (!$stmt) {
        die("Database Error (Prepare submissions): " . $conn->error);
    }

    // Bind the predefined user_id and the applicant_type_id
    $stmt->bind_param("ii", $user_id, $applicant_type_id);

    if (!$stmt->execute()) {
        die("Database Error (Execute submissions): " . $stmt->error);
    }

    // Get the ID of the new submission we just created
    $new_submission_id = $conn->insert_id;
    $stmt->close();


    // --- 2. Handle all File Uploads (from $_FILES) ---

    // Prepare statement for inserting file records
    $stmt_file = $conn->prepare("INSERT INTO submission_files (submission_id, field_name, original_filename, file_path) VALUES (?, ?, ?, ?)");
    if (!$stmt_file) {
        die("Database Error (Prepare files): " . $conn->error);
    }

    foreach ($_FILES as $fieldName => $file) {
        // Check for *any* upload error first
        if (isset($file['error']) && $file['error'] !== UPLOAD_ERR_OK) {
            // Skip empty file fields silently
            if ($file['error'] === UPLOAD_ERR_NO_FILE) {
                continue;
            }
            // Report other upload errors
            echo "Upload Error for field '$fieldName': ";
            switch ($file['error']) {
                case UPLOAD_ERR_INI_SIZE:
                    echo "The uploaded file exceeds the upload_max_filesize directive in php.ini.";
                    break;
                case UPLOAD_ERR_FORM_SIZE:
                    echo "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.";
                    break;
                case UPLOAD_ERR_PARTIAL:
                    echo "The uploaded file was only partially uploaded.";
                    break;
                case UPLOAD_ERR_CANT_WRITE:
                    echo "Failed to write file to disk.";
                    break;
                case UPLOAD_ERR_EXTENSION:
                    echo "A PHP extension stopped the file upload.";
                    break;
                default:
                    echo "Unknown upload error. Code: " . $file['error'];
                    break;
            }
            echo "<br>";
            continue; // Skip processing this file
        }

        // Proceed if upload was OK and a file exists
        if (isset($file['tmp_name']) && is_uploaded_file($file['tmp_name'])) {
            $original_filename = $file['name'];
            // Prevent potential directory traversal (basic check)
            $safe_basename = preg_replace("/[^a-zA-Z0-9\._-]/", "_", basename($original_filename));
            if ($safe_basename === '.' || $safe_basename === '..') {
                echo "Error: Invalid filename '$original_filename'.<br>";
                continue;
            }

            // Gather session credentials
            $email_from_session = $_SESSION['email_address'] ?? '';
            $password_from_session = $_SESSION['password'] ?? '';

            if (!$email_from_session || !$password_from_session) {
                echo "Error: Missing email or password in session for file '$original_filename'.<br>";
                continue;
            }

            // Upload to external API
            $apiResult = uploadToExternalApi($file['tmp_name'], $file['type'] ?? 'application/octet-stream', $original_filename, $email_from_session, $password_from_session, 'requirements');

            if (isset($apiResult['success']) && $apiResult['success'] === true) {
                // Prefer full URL if provided; otherwise fall back to stored_as
                $returned_url = $apiResult['url'] ?? '';
                $stored_as    = $apiResult['stored_as'] ?? '';

                // Based on instruction: use API response (url + stored_as)
                // Store the full URL if available; otherwise store the stored_as path
                $file_path_to_store = $returned_url ? $returned_url : $stored_as;

                if (!$file_path_to_store) {
                    echo "Upload API did not return a usable path for '$original_filename'.<br>";
                    continue;
                }

                // Save to DB
                $stmt_file->bind_param("isss", $new_submission_id, $fieldName, $original_filename, $file_path_to_store);
                if (!$stmt_file->execute()) {
                    echo "Database Error saving file info for '$fieldName': " . $stmt_file->error . "<br>";
                }
            } else {
                $msg = is_array($apiResult) && isset($apiResult['message']) ? $apiResult['message'] : 'Unknown error from upload API';
                echo "Upload API error for '$original_filename': $msg<br>";
                if (is_array($apiResult) && isset($apiResult['status'])) {
                    echo "HTTP Status: " . $apiResult['status'] . "<br>";
                }
            }
        }
    }
    $stmt_file->close();


    // --- 3. Handle all Text/Select Data (from $_POST) ---

    // Prepare a statement for inserting text data
    $stmt_data = $conn->prepare("INSERT INTO submission_data (submission_id, field_name, field_value) VALUES (?, ?, ?)");
    if (!$stmt_data) {
        die("Database Error (Prepare data): " . $conn->error);
    }

    foreach ($_POST as $fieldName => $value) {
        // Skip fields that are not part of the dynamic form data
        if ($fieldName === 'applicant_type_id') {
            continue;
        }

        // Handle arrays (e.g., from checkboxes if added later)
        $field_value = is_array($value) ? implode(', ', $value) : $value;

        $stmt_data->bind_param("iss", $new_submission_id, $fieldName, $field_value);
        if (!$stmt_data->execute()) {
            echo "Database Error saving data for '$fieldName': " . $stmt_data->error . "<br>";
        }
    }
    $stmt_data->close();

    // --- Update admission_submission table for this user ---
    // Set can_apply = false, can_update = false, submitted_at = NOW(), updated_at = NOW()
    if ($user_id) {
        // Check if record exists
        $stmt_check = $conn->prepare("SELECT id FROM admission_submission WHERE user_id = ?");
        if ($stmt_check) {
            $stmt_check->bind_param("i", $user_id);
            if ($stmt_check->execute()) {
                $result_check = $stmt_check->get_result();
                if ($result_check && $result_check->num_rows > 0) {
                    // Update existing
                    $stmt_update = $conn->prepare("UPDATE admission_submission SET can_apply = 0, can_update = 0, submitted_at = NOW(), updated_at = NOW() WHERE user_id = ?");
                    if ($stmt_update) {
                        $stmt_update->bind_param("i", $user_id);
                        $stmt_update->execute();
                        $stmt_update->close();
                    }
                } else {
                    // Insert new
                    $stmt_insert = $conn->prepare("INSERT INTO admission_submission (user_id, can_apply, can_update, submitted_at, updated_at) VALUES (?, 0, 0, NOW(), NOW())");
                    if ($stmt_insert) {
                        $stmt_insert->bind_param("i", $user_id);
                        $stmt_insert->execute();
                        $stmt_insert->close();
                    }
                }
            }
            $stmt_check->close();
        }
    }

    // Close the main connection
    $conn->close();

    // --- 4. Redirect to a "Thank You" page ---
    // Check if any errors were echoed before redirecting
    // This is a simple check; more robust error handling could be added
    if (error_get_last() === null && !headers_sent()) {
        // IMPORTANT: Comment out or remove the die() at the top before enabling redirect
        header("Location: received-application");
        exit;
        //  echo "<hr><strong>Submission processed. Redirect to thank_you.php would happen here.</strong>"; // For debugging
    } else {
        echo "<hr><strong>Errors occurred during processing. Redirect prevented.</strong>";
    }
} else {
    // If not a POST request, just redirect home or show an error
    // header("Location: apply.php"); // Redirect to the selection page
    echo "Invalid request method.";
    exit;
}
