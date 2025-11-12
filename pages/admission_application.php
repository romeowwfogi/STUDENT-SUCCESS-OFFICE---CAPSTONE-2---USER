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
include "functions/generalUploads.php";

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
$isRestrictedApply = false;
// Gate: check if user can apply via admission_controller; default allow if missing
try {
    $gateResult = executeSelect($conn, "SELECT can_apply FROM admission_controller WHERE user_id = ? LIMIT 1", "i", [$user_id]);
    if ($gateResult['success'] && count($gateResult['data']) > 0) {
        $isRestrictedApply = ((int)($gateResult['data'][0]['can_apply'] ?? 1) === 0);
    }
} catch (Throwable $e) {
    $isRestrictedApply = false;
}
$fetchFullnameResult = fetchFullnameFromDB($conn, $user_id);
$isProfileSet = false;
$first_name = null;
$middle_name = null;
$last_name = null;
$suffix = null;
if ($fetchFullnameResult['success'] === true) {
    $isProfileSet = true;
    $first_name = $fetchFullnameResult['data']['first_name'];
    $middle_name = $fetchFullnameResult['data']['middle_name'];
    $last_name = $fetchFullnameResult['data']['last_name'];
    $suffix = $fetchFullnameResult['data']['suffix'];
}

if (!isset($_GET['applicant_type_id'])) {
    // If no ID, redirect them to the selection page
    header("Location: home");
    exit;
}
$applicant_type_id = (int)$_GET['applicant_type_id'];

// If user is restricted from applying, return them to home (prevents direct URL bypass)
if ($isRestrictedApply) {
    header("Location: admission_home.php?locked=1");
    exit;
}

$formSteps = [];
$currentApplicantTypeId = $applicant_type_id; // Will be used in the hidden form field
$isSpecView = (isset($_GET['view']) && strtolower($_GET['view']) === 'spec');

// --- UPDATED SQL QUERY ---
// Loads form based on applicant_type_id and checks if it's active
$sql = "SELECT
            s.title as step_title,
            s.description as step_description,
            at.id as applicant_type_id,
            f.id as field_id, f.name, f.label, f.input_type, f.placeholder_text, f.is_required,
            f.notes as field_notes, f.allowed_file_types, f.max_file_size_mb,
            f.visible_when_field_id, f.visible_when_value,
            o.id as option_id, o.option_label, o.option_value
        FROM
            applicant_types at
        JOIN
            form_steps s ON at.id = s.applicant_type_id
        LEFT JOIN
            form_fields f ON s.id = f.step_id
        LEFT JOIN
            form_field_options o ON f.id = o.field_id
        WHERE
            at.id = ? AND at.is_active = 1 AND s.is_archived = 0
        ORDER BY
            s.step_order, f.field_order, o.option_order";

$result = null;
try {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $applicant_type_id);
    $stmt->execute();
    $result = $stmt->get_result();
} catch (Throwable $e) {
    // Fallback query without extended columns in case schema doesn't have them
    $sqlFallback = "SELECT
            s.title as step_title,
            at.id as applicant_type_id,
            f.id as field_id, f.name, f.label, f.input_type, f.placeholder_text, f.is_required,
            o.id as option_id, o.option_label, o.option_value
        FROM
            applicant_types at
        JOIN
            form_steps s ON at.id = s.applicant_type_id
        LEFT JOIN
            form_fields f ON s.id = f.step_id
        LEFT JOIN
            form_field_options o ON f.id = o.field_id
        WHERE
            at.id = ? AND at.is_active = 1
        ORDER BY
            s.step_order";
    $stmt = $conn->prepare($sqlFallback);
    $stmt->bind_param("i", $applicant_type_id);
    $stmt->execute();
    $result = $stmt->get_result();
}

// --- This logic builds the nested array ---
// --- This logic builds the nested array ---
$allFields = [];
$fieldsIndexById = [];
$fieldsNameById = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $stepTitle = $row['step_title'];
        if (!isset($formSteps[$stepTitle])) {
            $formSteps[$stepTitle] = [
                'description' => $row['step_description'] ?? '',
                'fields' => []
            ];
        }

        $field_id = $row['field_id'];
        if ($field_id === null) {
            continue;
        }

        if (!isset($allFields[$field_id])) {
            $allFields[$field_id] = [
                'id' => $field_id,
                'name' => $row['name'],
                'label' => $row['label'],
                'input_type' => $row['input_type'],
                'placeholder_text' => $row['placeholder_text'] ?? '',
                'required' => (bool)$row['is_required'],
                'notes' => $row['field_notes'] ?? '',
                'allowed_file_types' => $row['allowed_file_types'] ?? '',
                'max_file_size_mb' => $row['max_file_size_mb'] ?? null,
                'visible_when_field_id' => $row['visible_when_field_id'] ?? null,
                'visible_when_value' => $row['visible_when_value'] ?? '',
                'trigger_field_name' => '',
                'trigger_value' => '',
                'options' => []
            ];
            $formSteps[$stepTitle]['fields'][] = &$allFields[$field_id];
            $fieldsIndexById[$field_id] = &$allFields[$field_id];
            $fieldsNameById[$field_id] = $row['name'];
        }

        if ($row['option_id'] !== null) {
            $allFields[$field_id]['options'][] = [
                'label' => $row['option_label'],
                'value' => $row['option_value']
            ];
        }
    }
}
// Resolve trigger field names based on visible_when_field_id
foreach ($fieldsIndexById as $fid => &$fieldRef) {
    $ctrlId = $fieldRef['visible_when_field_id'];
    if (!empty($ctrlId) && isset($fieldsNameById[$ctrlId])) {
        $fieldRef['trigger_field_name'] = $fieldsNameById[$ctrlId];
        $fieldRef['trigger_value'] = (string)$fieldRef['visible_when_value'];
    }
}
unset($allFields, $fieldsIndexById, $fieldsNameById);

$stmt->close();

// --- ADD THIS BLOCK TO ENSURE A SUMMARY STEP ---
if (!empty($formSteps)) {
    // Get the title of the last step fetched from the DB
    $lastStepTitle = array_key_last($formSteps);

    // Check if the last step title *does not* contain "Summary"
    if (stripos($lastStepTitle, 'Summary') === false) {
        // If no summary step exists as the last one, add it manually
        $formSteps['Step ' . (count($formSteps) + 1) . ': Summary'] = [
            'description' => '',
            'fields' => []
        ]; // Add an empty step named Summary
    }
}
// --- END OF ADDED BLOCK ---

$conn->close(); // Connection is closed *after* the check

function e($string)
{
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Success Office - Admission</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="./src/css/global_styling.css" rel="stylesheet">

    <style>
        :root {
            --primary-color: #136515;

            --success-color: #28a745;

            --light-gray: #f4f7f6;
            --gray: #ccc;
            --dark-gray: #666;
            --text-color: #333;
            --border-radius: 8px;
            --danger-color: #dc3545;
        }


        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--light-gray);
            color: var(--text-color);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
            box-sizing: border-box;
            position: relative;
            overflow-x: hidden;
        }

        /* Particles Background Canvas */
        .bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1;
            pointer-events: none;
        }

        #canvas {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: block;
        }

        .form-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: var(--border-radius);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 700px;
            overflow: hidden;
            position: relative;
            z-index: 10;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            body {
                padding: 10px;
            }

            .form-container {
                max-width: 100%;
                margin: 0;
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 5px;
            }

            .form-container {
                border-radius: 0;
                min-height: 100vh;
                display: flex;
                flex-direction: column;
            }
        }

        /* ----- Step Indicator ----- */
        .step-indicator {
            display: flex;
            justify-content: space-between;
            padding: 30px;
            background: #fdfdfd;
            border-bottom: 1px solid #eee;
        }

        .step-wrapper {
            display: flex;
            flex-grow: 1;
        }

        .step-wrapper:last-child {
            flex-grow: 0;
        }

        .step-wrapper:last-child .step-line {
            display: none;
        }

        .step-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            min-width: 120px;
        }

        .step-dot {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: var(--light-gray);
            border: 3px solid var(--gray);
            color: var(--gray);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s ease;
            z-index: 2;
        }

        .step-labels {
            margin-top: 10px;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .step-labels span:first-child {
            font-size: 11px;
            font-weight: 500;
            color: var(--dark-gray);
            text-transform: uppercase;
        }

        .step-labels span:last-child {
            font-size: 14px;
            font-weight: 600;
            color: var(--text-color);
        }

        .step-badge {
            font-size: 11px;
            font-weight: 600;
            padding: 3px 10px;
            border-radius: 12px;
            margin-top: 8px;
            text-transform: uppercase;
        }

        .step-line {
            flex-grow: 1;
            height: 4px;
            background: var(--gray);
            margin: 15px 0 0 -10px;
            z-index: 1;
            transition: background 0.4s ease;
        }

        .step-wrapper.completed .step-dot {
            background: var(--success-color);
            border-color: var(--success-color);
            color: #fff;
        }

        .step-wrapper.completed .step-line {
            background: var(--success-color);
        }

        .step-wrapper.completed .step-badge {
            background: #eaf6ec;
            color: var(--success-color);
        }

        .step-wrapper.active .step-dot {
            border-color: var(--primary-color);
            color: var(--primary-color);
            background: #fff;
            transform: scale(1.1);
        }

        .step-wrapper.active .step-labels span:last-child {
            color: var(--primary-color);
        }

        .step-wrapper.active .step-line {
            background: linear-gradient(to right, var(--primary-color) 30%, var(--gray) 30%);
            background-size: 100%;
        }

        .step-wrapper.active .step-badge {
            background: #e6f2ff;
            color: var(--primary-color);
        }

        .step-wrapper.pending .step-badge {
            background: #f0f0f0;
            color: var(--dark-gray);
        }

        /* ----- Responsive Stepper ----- */
        @media (max-width: 768px) {
            .step-indicator {
                padding: 16px;
                gap: 12px;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                scrollbar-width: thin;
            }

            .step-wrapper {
                flex: 0 0 auto;
            }

            .step-item {
                min-width: 100px;
            }

            .step-dot {
                width: 28px;
                height: 28px;
                border-width: 2px;
                font-size: 14px;
            }

            .step-labels span:first-child {
                font-size: 10px;
            }

            .step-labels span:last-child {
                font-size: 12px;
                max-width: 110px;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            .step-line {
                height: 3px;
                margin: 12px 0 0 -8px;
            }
        }

        @media (max-width: 480px) {
            .step-indicator {
                padding: 12px;
                gap: 10px;
            }

            .step-item {
                min-width: 88px;
            }

            .step-dot {
                width: 24px;
                height: 24px;
                border-width: 2px;
                font-size: 12px;
            }

            .step-labels span:first-child {
                display: none;
            }

            .step-labels span:last-child {
                font-size: 11px;
                max-width: 90px;
            }

            .step-badge {
                display: none;
            }

            .step-line {
                height: 3px;
                margin: 10px 0 0 -6px;
            }
        }

        /* ----- Form ----- */
        #multiStepForm {
            padding: 30px;
        }

        @media (max-width: 768px) {
            #multiStepForm {
                padding: 20px;
            }
        }

        @media (max-width: 480px) {
            #multiStepForm {
                padding: 15px;
                flex: 1;
            }
        }

        .step {
            display: none;
        }

        .step.active {
            display: block;
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .step h2 {
            margin-top: 0;
            color: var(--primary-color);
        }

        .step-description {
            margin: 8px 0 16px;
            color: var(--dark-gray);
            font-size: 14px;
        }

        .field-note {
            display: block;
            margin-top: 6px;
            color: var(--dark-gray);
            font-size: 12px;
        }

        .spec-row {
            font-size: 14px;
            color: var(--dark-gray);
            margin: 4px 0;
        }

        .spec-option {
            font-size: 13px;
            color: var(--dark-gray);
            margin-left: 12px;
        }

        .muted {
            color: #7a7a7a;
        }


        /* ----- Floating Label Fields ----- */
        .field-group {
            position: relative;
            margin-bottom: 25px;
        }

        @media (max-width: 480px) {
            .field-group {
                margin-bottom: 20px;
            }
        }


        /* Inputs with floating styles (exclude radio/checkbox) */
        .field-group input:not([type="radio"]):not([type="checkbox"]),
        .field-group select {
            width: 100%;
            padding: 15px 12px;
            border: 1px solid var(--gray);
            border-radius: var(--border-radius);
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
            font-size: 16px;
            transition: border-color 0.3s ease;
            background-color: #fff;
        }

        .field-group select {
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23666' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 12px center;
            background-size: 1em;
            padding-right: 40px;
        }

        /* Limit floating label to direct child labels only */
        .field-group:not(.field-group-file)>label {
            position: absolute;
            top: 13px;
            left: 13px;
            font-weight: 400;
            color: var(--dark-gray);
            font-size: 16px;
            pointer-events: none;
            transition: all 0.2s ease;
        }

        .field-group label .required-asterisk {
            color: var(--danger-color);
            margin-left: 3px;
        }

        .field-group input:focus~label,
        .field-group input:not(:placeholder-shown)~label,
        .field-group select:focus~label,
        .field-group select:valid~label {
            top: -10px;
            left: 10px;
            font-size: 13px;
            color: var(--primary-color);
            font-weight: 500;
            background: #fff;
            padding: 0 4px;
        }

        .field-group input:focus,
        .field-group select:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.3);
        }

        /* File Input Fix */
        .field-group-file input[type="file"] {
            padding: 12px;
            font-family: 'Poppins', sans-serif;
            color: var(--dark-gray);
        }

        .field-group-file label {
            position: static;
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            font-size: 16px;
            color: var(--text-color);
            pointer-events: auto;
        }

        /* Select Input: use static label like file */
        .field-group-select label {
            position: static;
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            font-size: 16px;
            color: var(--text-color);
            pointer-events: auto;
        }

        /* Radio/Checkbox groups */
        .option-group {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            align-items: flex-start;
        }

        .option-group legend,
        .group-label {
            display: block;
            width: 100%;
            margin-bottom: 8px;
            font-weight: 500;
            font-size: 16px;
            color: var(--text-color);
            line-height: 1.4;
            word-break: break-word;
        }

        .option-inline {
            display: inline-flex;
            align-items: flex-start;
            gap: 8px;
            margin: 4px 8px 4px 0;
            position: static;
            pointer-events: auto;
            max-width: 100%;
        }

        .option-inline span {
            white-space: normal;
            line-height: 1.4;
        }

        /* ----- Button Navigation ----- */
        .button-group {
            margin-top: 30px;
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            flex-wrap: wrap;
        }

        @media (max-width: 480px) {
            .button-group {
                gap: 10px;
                margin-top: 20px;
            }
        }

        .btn {
            position: relative;
            padding: 12px 25px;
            border: 0;
            border-radius: var(--border-radius);
            font-family: 'Poppins', sans-serif;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.12s ease, box-shadow 0.12s ease, filter 0.2s ease;
            box-shadow: 0 6px 0 rgba(0, 0, 0, 0.15), 0 12px 24px rgba(0, 0, 0, 0.08);
            will-change: transform, box-shadow;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
            min-height: 50px;
        }

        .btn:hover {
            filter: brightness(1.03);
            transform: translateY(-1px);
            box-shadow: 0 7px 0 rgba(0, 0, 0, 0.16), 0 14px 28px rgba(0, 0, 0, 0.10);
        }

        .btn:active {
            transform: translateY(2px);
            box-shadow: 0 3px 0 rgba(0, 0, 0, 0.14), 0 6px 12px rgba(0, 0, 0, 0.08);
        }

        .btn-primary {
            color: #fff;
            background: linear-gradient(180deg, #34c759 0%, #2fa94a 60%, #238f3e 100%);
            border: 1px solid #1f7a33;
            text-shadow: 0 1px 0 rgba(0, 0, 0, 0.15);
        }

        .btn-primary:hover {
            filter: brightness(1.06);
        }

        .btn-primary:active {
            background: linear-gradient(180deg, #2fa94a 0%, #238f3e 100%);
        }

        .btn-primary:focus-visible {
            outline: none;
            box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.25), 0 6px 0 rgba(0, 0, 0, 0.15), 0 12px 24px rgba(0, 0, 0, 0.08);
        }

        .btn-secondary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 12px 25px;
            min-height: 50px;
            width: auto;
            height: auto;
            border-radius: 10px;
            background: linear-gradient(180deg, #f0f3f5 0%, #e2e6ea 60%, #d5dadd 100%);
            color: #2f3336;
            border: 1px solid #c4c9cc;
            box-shadow: 0 6px 0 rgba(0, 0, 0, 0.12), 0 12px 24px rgba(0, 0, 0, 0.07);
            text-shadow: 0 1px 0 rgba(255, 255, 255, 0.4);
        }

        .btn-secondary:hover {
            filter: brightness(1.04);
        }

        .btn-secondary:active {
            background: linear-gradient(180deg, #e8ecef 0%, #d5dadd 100%);
        }

        /* Back button: more compact neutral style */
        .btn-back {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 12px 20px;
            min-height: 50px;
            width: auto;
            height: auto;
            border-radius: var(--border-radius);
            background: linear-gradient(180deg, #f7f8f9 0%, #e9edf0 65%, #dfe3e6 100%);
            color: #2f3336;
            border: 1px solid #cbd0d3;
            box-shadow: 0 5px 0 rgba(0, 0, 0, 0.12), 0 10px 20px rgba(0, 0, 0, 0.07);
            text-shadow: 0 1px 0 rgba(255, 255, 255, 0.55);
        }

        .btn-back:hover {
            filter: brightness(1.04);
        }

        .btn-back:active {
            background: linear-gradient(180deg, #eaeef1 0%, #dfe3e6 100%);
        }

        /* Back icon (top-left) smaller and lighter than secondary */
        .btn-back-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 44px;
            height: 44px;
            padding: 8px;
            border-radius: 10px;
            background: linear-gradient(180deg, #f7f8f9 0%, #e9edf0 65%, #dfe3e6 100%);
            color: #2f3336;
            border: 1px solid #cbd0d3;
            box-shadow: 0 5px 0 rgba(0, 0, 0, 0.12), 0 10px 20px rgba(0, 0, 0, 0.07);
        }

        .btn-back-icon:hover {
            filter: brightness(1.04);
        }

        .btn-back-icon:active {
            background: linear-gradient(180deg, #eaeef1 0%, #dfe3e6 100%);
        }

        .btn.hidden {
            display: none;
        }

        .top-actions {
            display: flex;
            justify-content: flex-start;
        }

        .btn .icon {
            width: 18px;
            height: 18px;
            margin-right: 8px;
            vertical-align: middle;
        }

        /* ----- Summary & Error ----- */
        #summary-content p {
            font-size: 16px;
            line-height: 1.6;
            border-bottom: 1px dashed var(--gray);
            padding-bottom: 10px;
        }

        #summary-content p strong {
            color: var(--text-color);
            margin-right: 8px;
            min-width: 150px;
            display: inline-block;
        }

        .summary-preview-image,
        .summary-preview-video {
            display: block;
            margin-top: 10px;
            max-width: 90%;
            height: auto;
            border-radius: var(--border-radius);
            border: 1px solid var(--gray);
        }

        .summary-preview-video {
            max-width: 300px;
        }

        .error-message {
            text-align: center;
            padding: 40px;
            background: rgba(255, 248, 248, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid #f5c6cb;
            color: #721c24;
            border-radius: var(--border-radius);
        }

        /* Additional Responsive Improvements */
        @media (max-width: 768px) {
            .step h2 {
                font-size: 1.5rem;
                margin-bottom: 20px;
            }

            .btn {
                padding: 14px 20px;
                font-size: 14px;
            }

            .error-message {
                padding: 30px 20px;
            }
        }

        @media (max-width: 480px) {
            .step h2 {
                font-size: 1.3rem;
                margin-bottom: 15px;
            }

            .btn {
                padding: 12px 18px;
                font-size: 14px;
                min-width: 100px;
            }

            .field-group input,
            .field-group select {
                font-size: 16px;
                /* Prevents zoom on iOS */
            }

            .error-message {
                padding: 25px 15px;
            }

            #summary-content p {
                font-size: 14px;
                line-height: 1.5;
            }
        }

        /* Particles background: place behind all page content */
        .bg {
            position: fixed;
            inset: 0;
            /* top/right/bottom/left: 0 */
            z-index: -1;
            /* sit behind everything */
            pointer-events: none;
            user-select: none;
        }

        /* Make canvas cover the bg container */
        .bg>#canvas,
        #canvas {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            display: block;
            pointer-events: none;
            user-select: none;
        }

        /* Ensure main content sits above background */
        .form-container {
            position: relative;
            z-index: 1;
        }
    </style>
</head>

<body>
    <!-- Particles Background -->
    <div class="bg">
        <canvas id="canvas"></canvas>
    </div>

    <div class="form-container">

        <?php if ($isRestrictedApply): ?>
            <div class="error-message">
                <h2>Application Restricted</h2>
                <p>Your account is currently not allowed to submit a new application. If you believe this is an error, please contact support.</p>
                <div class="error-actions">
                    <a href="home" class="btn btn-primary" style="margin-top: 20px;">Back to Home</a>
                </div>
            </div>
        <?php elseif (empty($formSteps)): ?>
            <div class="error-message">
                <h2>Application Not Found</h2>
                <p>This application may be closed or the link is incorrect. Please select an open application from the main page.</p>
                <div class="error-actions">
                    <a href="home" class="btn btn-primary" style="margin-top: 20px;">Back to Home</a>
                </div>
            </div>
        <?php else: ?>

            <div class="top-actions">
                <a href="javascript:void(0);" class="btn-back-icon" aria-label="Go Back" onclick="history.back()">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-left-icon lucide-chevron-left">
                        <path d="m15 18-6-6 6-6" />
                    </svg>
                </a>
            </div>

            <div class="step-indicator">
                <?php
                $stepCount = count($formSteps);
                $i = 1;
                foreach ($formSteps as $stepTitle => $stepData):
                    $titleParts = explode(':', $stepTitle, 2);
                    $stepLabel = trim($titleParts[0] ?? "STEP $i");
                    $stepName = trim($titleParts[1] ?? $stepTitle);
                ?>
                    <div class="step-wrapper">
                        <div class="step-item">
                            <div class="step-dot"><?php echo $i; ?></div>
                            <div class="step-labels">
                                <span><?php echo e(strtoupper($stepLabel)); ?></span>
                                <span><?php echo e($stepName); ?></span>
                            </div>
                            <div class="step-badge"></div>
                        </div>

                        <?php if ($i < $stepCount): ?>
                            <div class="step-line"></div>
                        <?php endif; ?>
                    </div>
                <?php
                    $i++;
                endforeach;
                ?>
            </div>

            <form id="multiStepForm" action="submit-application" method="post" enctype="multipart/form-data">

                <input type="hidden" name="applicant_type_id" value="<?php echo e($currentApplicantTypeId); ?>">

                <?php
                $stepIndex = 1;
                foreach ($formSteps as $stepTitle => $stepData):

                    // --- This is the fix for the heading ---
                    $heading = '';
                    if (stripos($stepTitle, 'Summary') !== false) {
                        $heading = "Summary";
                    } else {
                        $titleParts = explode(':', $stepTitle, 2);
                        $heading = trim($titleParts[1] ?? $stepTitle);
                    }
                    // ---------------------

                ?>
                    <div class="step <?php echo ($stepIndex === 1) ? 'active' : ''; ?>" data-step-content="<?php echo $stepIndex; ?>">

                        <h2><?php echo e($heading); ?></h2>
                        <?php if (!empty($stepData['description'])): ?>
                            <p class="step-description"><?php echo nl2br(e($stepData['description'])); ?></p>
                        <?php endif; ?>

                        <?php if (stripos($stepTitle, 'Summary') !== false): ?>
                            <p>Please review your information before submitting:</p>
                            <div id="summary-content">
                            </div>
                        <?php else: ?>

                            <?php foreach ($stepData['fields'] as $field): ?>
                                <div class="field-group <?php echo ($field['input_type'] === 'file') ? 'field-group-file' : (($field['input_type'] === 'select') ? 'field-group-select' : ''); ?>"
                                    <?php if (!empty($field['trigger_field_name'])): ?>
                                    data-trigger-field="<?php echo e($field['trigger_field_name']); ?>"
                                    data-trigger-value="<?php echo e($field['trigger_value']); ?>"
                                    style="display:none;"
                                    <?php endif; ?>>

                                    <?php if ($isSpecView): ?>
                                        <div class="spec-row"><strong>Label:</strong> <?php echo e($field['label']); ?></div>
                                        <div class="spec-row"><strong>Input type:</strong> <?php echo e($field['input_type']); ?></div>
                                        <div class="spec-row"><strong>Notes:</strong> <?php echo !empty($field['notes']) ? nl2br(e($field['notes'])) : '-'; ?></div>
                                        <?php if (in_array($field['input_type'], ['select', 'radio', 'checkbox']) && !empty($field['options'])): ?>
                                            <div class="spec-row"><strong>Options:</strong></div>
                                            <?php foreach ($field['options'] as $option): ?>
                                                <div class="spec-option">• <?php echo e($option['label']); ?> <span class="muted">(<?php echo e($option['value']); ?>)</span></div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                        <?php if ($field['input_type'] === 'file'): ?>
                                            <div class="spec-row"><strong>Allowed file types:</strong> <?php echo !empty($field['allowed_file_types']) ? e($field['allowed_file_types']) : '-'; ?></div>
                                            <div class="spec-row"><strong>Max file size (MB):</strong> <?php echo !empty($field['max_file_size_mb']) ? e($field['max_file_size_mb']) : '-'; ?></div>
                                        <?php endif; ?>
                                        <?php if (!empty($field['trigger_field_name'])): ?>
                                            <div class="spec-row"><strong>Visible when:</strong> <?php echo e($field['trigger_field_name']); ?> = "<?php echo e($field['trigger_value']); ?>"</div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <?php if ($field['input_type'] === 'select'): ?>
                                            <label for="<?php echo e($field['name']); ?>" class="group-label">
                                                <?php echo e($field['label']); ?>
                                                <?php if ($field['required']): ?>
                                                    <span class="required-asterisk">*</span>
                                                <?php endif; ?>
                                            </label>
                                            <select
                                                id="<?php echo e($field['name']); ?>"
                                                name="<?php echo e($field['name']); ?>"
                                                <?php echo $field['required'] ? 'required' : ''; ?>>
                                                <option value="" disabled selected></option>

                                                <?php foreach ($field['options'] as $option): ?>
                                                    <option value="<?php echo e($option['value']); ?>">
                                                        <?php echo e($option['label']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <?php if (!empty($field['notes'])): ?>
                                                <small class="field-note"><?php echo nl2br(e($field['notes'])); ?></small>
                                            <?php endif; ?>

                                        <?php elseif ($field['input_type'] === 'file'): ?>
                                            <label for="<?php echo e($field['name']); ?>">
                                                <?php echo e($field['label']); ?>
                                                <?php if ($field['required']): ?>
                                                    <span class="required-asterisk">*</span>
                                                <?php endif; ?>
                                            </label>
                                            <input
                                                type="file"
                                                id="<?php echo e($field['name']); ?>"
                                                name="<?php echo e($field['name']); ?>"
                                                <?php echo $field['required'] ? 'required' : ''; ?>
                                                <?php if (!empty($field['allowed_file_types'])): ?>accept="<?php echo e($field['allowed_file_types']); ?>" <?php endif; ?>
                                                <?php if (!empty($field['max_file_size_mb'])): ?>data-max-size-mb="<?php echo e($field['max_file_size_mb']); ?>" <?php endif; ?>>
                                            <?php if (!empty($field['allowed_file_types']) || !empty($field['max_file_size_mb'])): ?>
                                                <small class="field-note">Allowed: <?php echo e($field['allowed_file_types'] ?: ''); ?><?php if (!empty($field['allowed_file_types']) && !empty($field['max_file_size_mb'])): ?> • <?php endif; ?><?php if (!empty($field['max_file_size_mb'])): ?>Max: <?php echo e($field['max_file_size_mb']); ?>MB<?php endif; ?></small>
                                            <?php endif; ?>

                                        <?php elseif ($field['input_type'] === 'radio'): ?>
                                            <fieldset class="option-group" role="radiogroup" aria-label="<?php echo e($field['label']); ?>">
                                                <legend class="group-label">
                                                    <?php echo e($field['label']); ?>
                                                    <?php if ($field['required']): ?>
                                                        <span class="required-asterisk">*</span>
                                                    <?php endif; ?>
                                                </legend>
                                                <?php foreach ($field['options'] as $idx => $option):
                                                    $optId = $field['name'] . '_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', (string)$option['value']);
                                                ?>
                                                    <label class="option-inline" for="<?php echo e($optId); ?>">
                                                        <input type="radio"
                                                            id="<?php echo e($optId); ?>"
                                                            name="<?php echo e($field['name']); ?>"
                                                            value="<?php echo e($option['value']); ?>"
                                                            <?php echo $field['required'] && $idx === 0 ? 'required' : ''; ?>>
                                                        <span><?php echo e($option['label']); ?></span>
                                                    </label>
                                                <?php endforeach; ?>
                                                <?php if (!empty($field['notes'])): ?>
                                                    <small class="field-note"><?php echo nl2br(e($field['notes'])); ?></small>
                                                <?php endif; ?>
                                            </fieldset>

                                        <?php elseif ($field['input_type'] === 'checkbox'): ?>
                                            <?php if (!empty($field['options'])): ?>
                                                <fieldset class="option-group" aria-label="<?php echo e($field['label']); ?>">
                                                    <legend class="group-label">
                                                        <?php echo e($field['label']); ?>
                                                        <?php if ($field['required']): ?>
                                                            <span class="required-asterisk">*</span>
                                                        <?php endif; ?>
                                                    </legend>
                                                    <?php foreach ($field['options'] as $option):
                                                        $optId = $field['name'] . '_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', (string)$option['value']);
                                                    ?>
                                                        <label class="option-inline" for="<?php echo e($optId); ?>">
                                                            <input type="checkbox"
                                                                id="<?php echo e($optId); ?>"
                                                                name="<?php echo e($field['name']); ?>[]"
                                                                value="<?php echo e($option['value']); ?>">
                                                            <span><?php echo e($option['label']); ?></span>
                                                        </label>
                                                    <?php endforeach; ?>
                                                    <?php if (!empty($field['notes'])): ?>
                                                        <small class="field-note"><?php echo nl2br(e($field['notes'])); ?></small>
                                                    <?php endif; ?>
                                                </fieldset>
                                            <?php else: ?>
                                                <label class="option-inline" for="<?php echo e($field['name']); ?>">
                                                    <input type="checkbox"
                                                        id="<?php echo e($field['name']); ?>"
                                                        name="<?php echo e($field['name']); ?>"
                                                        value="1"
                                                        <?php echo $field['required'] ? 'required' : ''; ?>>
                                                    <span><?php echo e($field['label']); ?></span>
                                                </label>
                                                <?php if (!empty($field['notes'])): ?>
                                                    <small class="field-note"><?php echo nl2br(e($field['notes'])); ?></small>
                                                <?php endif; ?>
                                            <?php endif; ?>

                                        <?php else: ?>
                                            <input
                                                type="<?php echo e($field['input_type']); ?>"
                                                id="<?php echo e($field['name']); ?>"
                                                name="<?php echo e($field['name']); ?>"
                                                placeholder=" "
                                                <?php echo $field['required'] ? 'required' : ''; ?>>
                                        <?php endif; ?>

                                        <?php if (!in_array($field['input_type'], ['file', 'radio', 'checkbox', 'select'])): ?>
                                            <label for="<?php echo e($field['name']); ?>">
                                                <?php echo e($field['label']); ?>
                                                <?php if ($field['required']): ?>
                                                    <span class="required-asterisk">*</span>
                                                <?php endif; ?>
                                            </label>
                                            <?php if (!empty($field['notes'])): ?>
                                                <small class="field-note"><?php echo nl2br(e($field['notes'])); ?></small>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    <?php endif; // end spec view 
                                    ?>

                                </div>
                            <?php endforeach; ?>

                        <?php endif; ?>

                    </div>
                <?php
                    $stepIndex++;
                endforeach;
                ?>

                <div class="button-group">
                    <button type="button" class="btn btn-back hidden" id="prevBtn">Previous</button>
                    <button type="button" class="btn btn-primary" id="nextBtn">Next</button>
                    <button type="submit" class="btn btn-primary hidden" id="submitBtn">Submit Application</button>
                </div>
            </form>
        <?php endif; ?>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('multiStepForm');
            if (!form) return;

            const steps = Array.from(document.querySelectorAll('.step'));
            const stepWrappers = Array.from(document.querySelectorAll('.step-wrapper'));
            const nextBtn = document.getElementById('nextBtn');
            const prevBtn = document.getElementById('prevBtn');
            const submitBtn = document.getElementById('submitBtn');

            let currentStep = 0;

            function showStep(stepIndex) {
                steps.forEach((step, index) => {
                    step.classList.toggle('active', index === stepIndex);
                });

                stepWrappers.forEach((wrapper, index) => {
                    const badge = wrapper.querySelector('.step-badge');
                    wrapper.classList.remove('active', 'completed', 'pending');
                    if (badge) badge.className = 'step-badge';

                    if (index < stepIndex) {
                        wrapper.classList.add('completed');
                        badge.textContent = 'Completed';
                    } else if (index === stepIndex) {
                        wrapper.classList.add('active');
                        badge.textContent = 'In Progress';
                    } else {
                        wrapper.classList.add('pending');
                        badge.textContent = 'Pending';
                    }
                });

                prevBtn.classList.toggle('hidden', stepIndex === 0);
                nextBtn.classList.toggle('hidden', stepIndex === steps.length - 1);
                submitBtn.classList.toggle('hidden', stepIndex !== steps.length - 1);

                if (stepIndex === steps.length - 1) {
                    generateSummary();
                }

                currentStep = stepIndex;
            }

            function validateStep(stepIndex) {
                function isVisible(el) {
                    if (!el) return false;
                    // offsetParent is null when el or an ancestor has display:none
                    if (el.offsetParent === null) return false;
                    const style = window.getComputedStyle(el);
                    return style.visibility !== 'hidden' && style.display !== 'none';
                }

                const allRequired = Array.from(steps[stepIndex].querySelectorAll('input[required], select[required]'));
                // Validate only required fields that are enabled and visible (skip hidden by triggers)
                const fieldsToValidate = allRequired.filter(f => !f.disabled && isVisible(f));

                let isValid = true;
                for (const field of fieldsToValidate) {
                    if (!field.checkValidity()) {
                        try {
                            field.reportValidity();
                        } catch (e) {}
                        isValid = false;
                        break;
                    }
                }
                return isValid;
            }

            function generateSummary() {
                const summaryContent = document.getElementById('summary-content');
                summaryContent.innerHTML = '';

                const fields = form.querySelectorAll('input[name], select[name]');

                fields.forEach(field => {
                    if (field.type === 'hidden' || field.name === '') return;

                    const label = document.querySelector(`label[for="${field.id}"]`);
                    let labelText = label ? label.innerText.replace('*', '').trim() : field.name;

                    const p = document.createElement('p');
                    p.innerHTML = `<strong>${labelText}:</strong> `;

                    let valueNode = null;

                    if (field.tagName === 'SELECT') {
                        let value = 'No selection';
                        if (field.selectedIndex > 0 && field.options[field.selectedIndex]) {
                            value = field.options[field.selectedIndex].text;
                        }
                        valueNode = document.createTextNode(e(value));

                    } else if (field.type === 'file') {
                        if (field.files.length > 0) {
                            const file = field.files[0];
                            const fileType = file.type;
                            const fileURL = URL.createObjectURL(file);

                            if (fileType.startsWith('image/')) {
                                valueNode = document.createElement('img');
                                valueNode.src = fileURL;
                                valueNode.className = 'summary-preview-image';
                                valueNode.alt = 'Uploaded image preview';
                            } else if (fileType.startsWith('video/')) {
                                valueNode = document.createElement('video');
                                valueNode.src = fileURL;
                                valueNode.controls = true;
                                valueNode.className = 'summary-preview-video';
                            } else {
                                valueNode = document.createTextNode(e(file.name));
                            }
                        } else {
                            valueNode = document.createTextNode('No file selected');
                        }
                    } else {
                        valueNode = document.createTextNode(e(field.value));
                    }

                    p.appendChild(valueNode);
                    summaryContent.appendChild(p);
                });
            }

            function e(str) {
                if (!str) return '';
                return str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
            }

            nextBtn.addEventListener('click', () => {
                if (validateStep(currentStep)) {
                    showStep(currentStep + 1);
                }
            });

            prevBtn.addEventListener('click', () => {
                showStep(currentStep - 1);
            });

            form.addEventListener('submit', (e) => {
                for (let i = 0; i < steps.length - 1; i++) {
                    if (!validateStep(i)) {
                        e.preventDefault();
                        showStep(i);
                        return;
                    }
                }
                console.log('Form submitted!');
            });

            // This checks if the form container exists before trying to show a step
            // This prevents an error if the "Admissions Closed" message is shown instead
            if (steps.length > 0) {
                showStep(0);
            }
        });
    </script>
    <script src="./src/js/geometric _bg.js"></script>
    <script>
        // Conditional display for trigger-based fields
        function applyTriggers() {
            var containers = document.querySelectorAll('.field-group[data-trigger-field]');
            containers.forEach(function(container) {
                var fieldName = container.getAttribute('data-trigger-field');
                var triggerValue = container.getAttribute('data-trigger-value');
                var control = document.getElementById(fieldName);
                var controlsByName = document.querySelectorAll('[name="' + fieldName + '"]');
                var hasAnyControl = control || controlsByName.length > 0;
                if (!hasAnyControl) return;

                var evaluate = function() {
                    var match = false;
                    // If there are radios/checkboxes with the same name, evaluate the group
                    if (controlsByName.length > 1 && controlsByName[0].type === 'radio') {
                        var selected = document.querySelector('input[name="' + fieldName + '"]:checked');
                        match = selected ? (selected.value === triggerValue) : false;
                    } else if (controlsByName.length > 1 && controlsByName[0].type === 'checkbox') {
                        var tv = (triggerValue || '').trim();
                        var anyChecked = Array.from(controlsByName).some(function(cb) {
                            return cb.checked;
                        });
                        if (tv) {
                            match = Array.from(controlsByName).some(function(cb) {
                                return cb.checked && (cb.value === tv);
                            });
                        } else {
                            match = anyChecked; // show when any is checked if no specific value
                        }
                    } else {
                        // Single control (select, text, single checkbox, or radio)
                        var c = control || controlsByName[0];
                        if (!c) {
                            container.style.display = 'none';
                            return;
                        }
                        if (c.type === 'checkbox') {
                            var truthy = (triggerValue || 'true').toLowerCase();
                            match = c.checked ? truthy !== 'false' : truthy === 'false';
                        } else if (c.type === 'radio') {
                            var selected2 = document.querySelector('input[name="' + fieldName + '"]:checked');
                            match = selected2 ? (selected2.value === triggerValue) : false;
                        } else {
                            match = (c.value === triggerValue);
                        }
                    }
                    container.style.display = match ? '' : 'none';
                };

                // Attach listeners to all relevant controls so hiding works when unselecting/changing
                if (controlsByName.length > 0) {
                    controlsByName.forEach(function(el) {
                        el.addEventListener('change', evaluate);
                    });
                }
                if (control) {
                    control.addEventListener('change', evaluate);
                }

                evaluate();
            });
        }

        // File validation against accepted types and max size
        function validateFileInput(input) {
            try {
                input.setCustomValidity('');
            } catch (e) {}
            var accept = input.getAttribute('accept') || '';
            var maxMbAttr = input.getAttribute('data-max-size-mb');
            var maxMb = maxMbAttr ? parseFloat(maxMbAttr) : NaN;
            if (!input.files || input.files.length === 0) return true;
            var allowed = accept ? accept.split(',').map(function(s) {
                return s.trim().toLowerCase();
            }) : [];
            for (var i = 0; i < input.files.length; i++) {
                var file = input.files[i];
                if (allowed.length) {
                    var ext = '.' + (file.name.split('.').pop() || '').toLowerCase();
                    if (allowed.indexOf(ext) === -1) {
                        var msg = 'File type not allowed: ' + ext + (allowed.length ? ('\nAllowed: ' + allowed.join(', ')) : '');
                        try {
                            input.setCustomValidity(msg);
                            input.reportValidity();
                        } catch (e) {
                            alert(msg);
                        }
                        return false;
                    }
                }
                if (!isNaN(maxMb) && file.size > (maxMb * 1024 * 1024)) {
                    var msg2 = 'File exceeds max size of ' + maxMb + ' MB';
                    try {
                        input.setCustomValidity(msg2);
                        input.reportValidity();
                    } catch (e) {
                        alert(msg2);
                    }
                    return false;
                }
            }
            return true;
        }

        function validateAllFiles() {
            var ok = true;
            document.querySelectorAll('input[type="file"]').forEach(function(input) {
                if (!validateFileInput(input)) ok = false;
            });
            return ok;
        }

        document.addEventListener('DOMContentLoaded', function() {
            applyTriggers();
            document.querySelectorAll('input[type="file"]').forEach(function(input) {
                input.addEventListener('change', function() {
                    validateFileInput(input);
                });
            });
            var form = document.getElementById('multiStepForm');
            if (form) {
                form.addEventListener('submit', function(e) {
                    if (!validateAllFiles()) {
                        e.preventDefault();
                    }
                });
            }
        });
    </script>
</body>

</html>