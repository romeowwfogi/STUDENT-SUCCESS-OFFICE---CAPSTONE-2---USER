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
include "functions/en-de_crypt.php";

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
$fetchFullnameResult = fetchFullnameFromDB($conn, $user_id);
$isProfileSet = false;
$first_name = null;
$middle_name = null;
$last_name = null;
$suffix = null;
$EMAIL_ADDRESS = '';
if ($fetchFullnameResult['success'] === true) {
    $isProfileSet = true;
    $first_name = $fetchFullnameResult['data']['first_name'];
    $middle_name = $fetchFullnameResult['data']['middle_name'];
    $last_name = $fetchFullnameResult['data']['last_name'];
    $suffix = $fetchFullnameResult['data']['suffix'];
}

// Fetch decrypted email for current user
$sqlUserEmail = "SELECT email FROM users WHERE id = ?";
$typesUserEmail = "i";
$paramsUserEmail = [$user_id];
$resUserEmail = executeSelect($conn, $sqlUserEmail, $typesUserEmail, $paramsUserEmail);
if ($resUserEmail['success'] && count($resUserEmail['data']) > 0) {
    $EMAIL_ADDRESS = decryptData($resUserEmail['data'][0]['email']);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Application - Student Success Office</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="../pages/src/css/admission_dashboard.css">
    <link rel="stylesheet" href="../pages/src/css/global_styling.css">
    <style>
        /* =========================================================
           🌿 MY APPLICATION TABLE STYLES
        ========================================================= */

        .table_section {
            max-width: 1400px;
            margin: 0 auto;
            width: 100%;
        }

        .page_header {
            margin-bottom: var(--space-lg);
        }

        .page_title {
            font-size: var(--font-size-lg);
            font-weight: 600;
            color: var(--color-gray-dark);
            margin-bottom: var(--space-md);
        }

        .table_controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: var(--space-md);
            gap: var(--space-md);
            flex-wrap: wrap;
        }

        .controls_left {
            display: flex;
            align-items: center;
            gap: var(--space-sm);
        }

        .controls_right {
            display: flex;
            align-items: center;
            gap: var(--space-sm);
        }

        .btn {
            padding: var(--space-xs) var(--space-md);
            border-radius: var(--radius-sm);
            border: none;
            font-size: var(--font-size-sm);
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition-base);
            display: flex;
            align-items: center;
            gap: var(--space-xs);
        }

        .btn_primary {
            background: linear-gradient(145deg, #16aa19, #136515);
            color: var(--color-white);
            border: none;
            border-radius: var(--radius-md);
            padding: clamp(0.5rem, 1.5vw, 0.75rem) clamp(1rem, 2.5vw, 1.5rem);
            box-shadow: rgba(0, 0, 0, 0.35) 0px 5px 15px;
            transform: translateY(0);
        }

        .btn_primary:hover {
            background: linear-gradient(145deg, #136515, #136515);
            transform: translateY(-1px);
            filter: drop-shadow(-2px 9px 5px #000000);
        }

        .btn_primary:active {
            box-shadow: inset 2px 2px 5px rgba(0, 0, 0, 0.5);
            transform: translateY(1px);
        }

        .btn_primary:focus-visible {
            outline: 3px solid var(--ring-color);
            outline-offset: 2px;
        }

        .btn_secondary {
            background-color: var(--color-white);
            color: var(--color-gray-dark);
            border: 1px solid var(--color-gray-light);
        }

        .btn_secondary:hover {
            background-color: var(--color-gray-50);
        }

        .btn_outline {
            background-color: transparent;
            color: #dc2626;
            border: 1px solid #dc2626;
        }

        .btn_outline:hover {
            background-color: #dc2626;
            color: var(--color-white);
        }

        .btn_refresh {
            background-color: var(--color-white);
            color: var(--color-gray);
            border: 1px solid var(--color-gray-light);
            padding: var(--space-xs);
            border-radius: var(--radius-sm);
        }

        .btn_refresh:hover {
            background-color: var(--color-gray-50);
        }

        .table_container {
            background-color: var(--color-white);
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-sm);
            overflow: hidden;
        }

        .data_table {
            width: 100%;
            border-collapse: collapse;
        }

        .table_header {
            background-color: var(--color-gray-50);
            border-bottom: 1px solid var(--color-gray-light);
        }

        .table_header th {
            padding: var(--space-md);
            text-align: left;
            font-weight: 600;
            color: var(--color-gray-dark);
            font-size: var(--font-size-sm);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .table_header th:first-child {
            width: 60px;
        }

        .sort_header {
            display: flex;
            align-items: center;
            gap: var(--space-xs);
            cursor: pointer;
        }

        .sort_icon {
            width: 16px;
            height: 16px;
            color: var(--color-gray);
        }

        .table_body tr {
            border-bottom: 1px solid var(--color-gray-light);
        }

        .table_body tr:hover {
            background-color: var(--color-gray-50);
        }

        .table_body td {
            padding: var(--space-md);
            font-size: var(--font-size-sm);
            color: var(--color-gray-dark);
        }

        .checkbox_cell {
            width: 40px;
        }

        .checkbox {
            width: 16px;
            height: 16px;
            cursor: pointer;
        }

        .status_badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .status_active {
            background-color: #dcfce7;
            color: #166534;
        }

        .status_inactive {
            background-color: #fef2f2;
            color: #dc2626;
        }

        .actions_cell {
            display: flex;
            align-items: center;
            gap: var(--space-xs);
        }

        .action_btn {
            padding: var(--space-xs);
            border: none;
            border-radius: var(--radius-sm);
            cursor: pointer;
            transition: var(--transition-base);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .action_view {
            background-color: #ede9fe;
            color: #6c757d;
        }

        .action_view:hover {
            background-color: #ddd6fe;
        }

        .action_edit {
            background-color: #dbeafe;
            color: #16aa19;
        }

        .action_edit:hover {
            background-color: #bfdbfe;
        }

        .action_disable {
            background-color: #fed7aa;
            color: #ea580c;
        }

        .action_disable:hover {
            background-color: #fdba74;
        }

        .action_delete {
            background-color: #fecaca;
            color: #dc2626;
        }

        .action_delete:hover {
            background-color: #fca5a5;
        }

        .icon {
            width: 16px;
            height: 16px;
        }

        @media (max-width: 768px) {
            .table_controls {
                flex-direction: column;
                align-items: stretch;
            }

            .controls_left,
            .controls_right {
                justify-content: center;
            }

            .table_container {
                overflow-x: auto;
            }

            .data_table {
                min-width: 600px;
            }
        }

        /* =========================================================
           🔍 SEARCH BAR STYLES
        ========================================================= */
        .search_input_wrapper {
            position: relative;
            width: 100%;
        }

        .search_icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            width: 20px;
            height: 20px;
            color: var(--color-gray-medium);
            pointer-events: auto;
            cursor: pointer;
            z-index: 2;
        }

        .search_input {
            width: 500px;
            padding: 12px 16px 12px 44px;
            border: 1px solid var(--color-gray-light);
            border-radius: var(--border-radius-md);
            font-size: var(--font-size-sm);
            color: var(--color-gray-dark);
            background-color: var(--color-white);
            transition: all 0.2s ease;
        }

        .search_input:focus {
            outline: none;
            border-color: var(--color-primary);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .search_input::placeholder {
            color: var(--color-gray-medium);
        }

        @media (max-width: 768px) {
            .search_input_wrapper {
                max-width: 100%;
            }
        }

        /* =========================================================
           🔧 FILTER MODAL STYLES
        ========================================================= */

        .filter_modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
        }

        .filter_modal.show {
            display: flex;
            align-items: center;
            justify-content: center;
            animation: fadeIn 0.3s ease;
        }

        .filter_modal_content {
            background-color: var(--color-white);
            border-radius: var(--border-radius-lg);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            width: 90%;
            max-width: 500px;
            max-height: 90vh;
            overflow-y: auto;
            animation: slideIn 0.3s ease;
        }

        .filter_modal_header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: var(--space-lg);
            border-bottom: 1px solid var(--color-gray-light);
        }

        .filter_modal_header h3 {
            margin: 0;
            font-size: var(--font-size-lg);
            font-weight: 600;
            color: var(--color-gray-dark);
        }

        .filter_close_btn {
            background: none;
            border: none;
            cursor: pointer;
            padding: 8px;
            border-radius: var(--border-radius-md);
            color: var(--color-gray-medium);
            transition: all 0.2s ease;
        }

        .filter_close_btn:hover {
            background-color: var(--color-gray-light);
            color: var(--color-gray-dark);
        }

        .filter_close_btn svg {
            width: 20px;
            height: 20px;
        }

        .filter_modal_body {
            padding: var(--space-lg);
        }

        .filter_section {
            margin-bottom: var(--space-lg);
        }

        .filter_section:last-child {
            margin-bottom: 0;
        }

        .filter_label {
            display: block;
            margin-bottom: var(--space-sm);
            font-weight: 500;
            color: var(--color-gray-dark);
            font-size: var(--font-size-sm);
        }

        .filter_select {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid var(--color-gray-light);
            border-radius: var(--border-radius-md);
            font-size: var(--font-size-sm);
            color: var(--color-gray-dark);
            background-color: var(--color-white);
            transition: all 0.2s ease;
        }

        .filter_select:focus {
            outline: none;
            border-color: var(--color-primary);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .filter_modal_footer {
            display: flex;
            justify-content: flex-end;
            gap: var(--space-md);
            padding: var(--space-lg);
            border-top: 1px solid var(--color-gray-light);
            background-color: var(--color-gray-lightest);
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-20px) scale(0.95);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        /* =========================================================
           📄 PAGINATION STYLES
        ========================================================= */
        .pagination_container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: var(--space-md);
            background-color: var(--color-white);
            border-top: 1px solid var(--color-gray-light);
            border-radius: 0 0 var(--radius-md) var(--radius-md);
        }

        .pagination_info {
            display: flex;
            align-items: center;
            gap: var(--space-lg);
        }

        .entries_per_page {
            display: flex;
            align-items: center;
            gap: var(--space-xs);
            font-size: var(--font-size-sm);
            color: var(--color-gray-dark);
        }

        .entries_select {
            padding: var(--space-xs) var(--space-sm);
            border: 1px solid var(--color-gray-light);
            border-radius: var(--radius-sm);
            font-size: var(--font-size-sm);
            background-color: var(--color-white);
            cursor: pointer;
        }

        .entries_counter {
            font-size: var(--font-size-sm);
            color: var(--color-gray-dark);
        }

        .pagination_controls {
            display: flex;
            align-items: center;
            gap: var(--space-sm);
        }

        .pagination_btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: var(--space-xs);
            min-width: 84px;
            height: 34px;
            padding: 0 var(--space-sm);
            border: 1px solid #18a558;
            border-radius: 8px;
            background-color: #ffffff;
            color: #18a558;
            font-size: var(--font-size-xs);
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .pagination_btn:hover:not(:disabled) {
            background-color: #eafaf2;
            border-color: #136515;
            color: #136515;
        }

        .pagination_btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .page_numbers {
            display: flex;
            gap: var(--space-xs);
            flex-wrap: wrap;
        }

        .page_number {
            width: 32px;
            height: 32px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid rgba(24, 165, 88, 0.5);
            border-radius: 8px;
            background-color: #f0fdf4;
            color: #136515;
            font-size: var(--font-size-sm);
            cursor: pointer;
            transition: all 0.2s ease;
            text-align: center;
        }

        .page_number:hover {
            background-color: #eafaf2;
            border-color: #18a558;
            color: #136515;
        }

        .page_number.active {
            background-color: #18a558;
            color: #ffffff;
            border-color: #18a558;
            box-shadow: 0 2px 6px rgba(24, 165, 88, 0.25);
        }

        @media (max-width: 768px) {
            .pagination_container {
                flex-direction: column;
                gap: var(--space-md);
            }

            .pagination_info {
                flex-direction: column;
                gap: var(--space-sm);
                text-align: center;
            }

            .filter_modal_content {
                width: 95%;
                margin: var(--space-md);
            }

            .filter_modal_footer {
                flex-direction: column;
            }

            .filter_modal_footer .btn {
                width: 100%;
            }
        }

        /* =========================================================
           📄 EMPTY STATE STYLES
        ========================================================= */
        .empty_state {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 300px;
            padding: var(--space-xl);
            background-color: var(--color-white);
            border-radius: var(--radius-md);
        }

        .empty_state_content {
            text-align: center;
            max-width: 400px;
        }

        .empty_state_icon {
            width: 64px;
            height: 64px;
            margin: 0 auto var(--space-md);
            color: var(--color-gray-medium);
            opacity: 0.6;
        }

        .empty_state_title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--color-text-primary);
            margin-bottom: var(--space-sm);
        }

        .empty_state_message {
            color: var(--color-text-secondary);
            margin-bottom: var(--space-lg);
            line-height: 1.5;
        }

        /* Responsive adjustments for empty state */
        @media (max-width: 768px) {
            .empty_state {
                min-height: 250px;
                padding: var(--space-lg);
            }

            .empty_state_icon {
                width: 48px;
                height: 48px;
            }

            .empty_state_title {
                font-size: 1.25rem;
            }
        }

        .save-changes-btn {
            display: flex;
            justify-content: end;
        }

        /* =========================================================
            Select Dropdown Enhancements (Green Theme + Mobile)
        ========================================================= */
        .entries_select {
            width: auto;
            min-width: 84px;
            height: 34px;
            padding: 4px 28px 4px 10px;
            border: 1px solid #18a558;
            border-radius: 8px;
            font-size: var(--font-size-sm);
            color: var(--color-gray-dark);
            background-color: var(--color-white);
            background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='%2318a558'><path fill-rule='evenodd' d='M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.08 1.04l-4.25 4.25a.75.75 0 01-1.06 0L5.21 8.27a.75.75 0 01.02-1.06z' clip-rule='evenodd'/></svg>");
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 16px 16px;
            appearance: none;
            cursor: pointer;
            transition: border-color 0.2s ease, box-shadow 0.2s ease, color 0.2s ease;
        }

        .entries_select:hover {
            border-color: #136515;
        }

        .entries_select:focus {
            outline: none;
            border-color: #136515;
            box-shadow: 0 0 0 3px rgba(24, 165, 88, 0.15);
        }

        .entries_select:disabled {
            background-color: var(--color-gray-lightest);
            color: var(--color-gray-medium);
            border-color: var(--color-gray-light);
            cursor: not-allowed;
        }

        .filter_select {
            width: 100%;
            padding: 10px 40px 10px 14px;
            border: 1px solid #18a558;
            border-radius: var(--border-radius-md);
            font-size: var(--font-size-sm);
            color: var(--color-gray-dark);
            background-color: var(--color-white);
            background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='%2318a558'><path fill-rule='evenodd' d='M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.08 1.04l-4.25 4.25a.75.75 0 01-1.06 0L5.21 8.27a.75.75 0 01.02-1.06z' clip-rule='evenodd'/></svg>");
            background-repeat: no-repeat;
            background-position: right 12px center;
            background-size: 16px 16px;
            appearance: none;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .filter_select:hover {
            border-color: #136515;
        }

        .filter_select:focus {
            outline: none;
            border-color: #136515;
            box-shadow: 0 0 0 3px rgba(24, 165, 88, 0.15);
        }

        @media (max-width: 480px) {
            .entries_per_page {
                flex-direction: column;
                align-items: stretch;
                gap: var(--space-xs);
            }

            .entries_select {
                width: 100%;
                min-width: 0;
                background-position: right 12px center;
            }
        }

        /* =========================================================
            Select + Pagination Refinements (Green, Responsive, No layout change)
        ========================================================= */
        .entries_select {
            width: auto;
            min-width: 84px;
            height: 34px;
            padding: 4px 28px 4px 10px;
            border: 1px solid #18a558;
            border-radius: 10px;
            font-size: var(--font-size-sm);
            color: var(--color-gray-dark);
            background-color: var(--color-white);
            background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='%2318a558'><path fill-rule='evenodd' d='M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.08 1.04l-4.25 4.25a.75.75 0 01-1.06 0L5.21 8.27a.75.75 0 01.02-1.06z' clip-rule='evenodd'/></svg>");
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 16px 16px;
            appearance: none;
            cursor: pointer;
            transition: border-color 0.2s ease, box-shadow 0.2s ease, color 0.2s ease;
        }

        .entries_select:hover {
            border-color: #136515;
        }

        .entries_select:focus {
            outline: none;
            border-color: #136515;
            box-shadow: 0 0 0 2px rgba(24, 165, 88, .15);
        }

        .entries_select:disabled {
            background-color: var(--color-gray-lightest);
            color: var(--color-gray-medium);
            border-color: var(--color-gray-light);
            cursor: not-allowed;
        }

        .filter_select {
            width: 100%;
            padding: 10px 40px 10px 14px;
            border: 1px solid #18a558;
            border-radius: var(--border-radius-md);
            font-size: var(--font-size-sm);
            color: var(--color-gray-dark);
            background-color: var(--color-white);
            background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='%2318a558'><path fill-rule='evenodd' d='M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.08 1.04l-4.25 4.25a.75.75 0 01-1.06 0L5.21 8.27a.75.75 0 01.02-1.06z' clip-rule='evenodd'/></svg>");
            background-repeat: no-repeat;
            background-position: right 12px center;
            background-size: 16px 16px;
            appearance: none;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .filter_select:hover {
            border-color: #136515;
        }

        .filter_select:focus {
            outline: none;
            border-color: #136515;
            box-shadow: 0 0 0 2px rgba(24, 165, 88, .15);
        }

        /* Keep row layout; just allow wrap on small screens */
        .pagination_container {
            flex-wrap: wrap;
            column-gap: var(--space-sm);
            row-gap: var(--space-sm);
        }

        .pagination_controls {
            flex-wrap: wrap;
        }

        @media (max-width: 768px) {
            .pagination_container {
                flex-direction: row !important;
                flex-wrap: wrap;
                gap: var(--space-sm);
            }

            .pagination_info {
                flex-direction: row !important;
                text-align: left !important;
                gap: var(--space-sm);
                flex-wrap: wrap;
            }

            .pagination_controls {
                flex-wrap: wrap;
            }
        }

        @media (max-width: 480px) {
            .entries_per_page {
                flex-direction: row;
                align-items: center;
                gap: var(--space-xs);
            }

            .entries_select {
                width: 100%;
                min-width: 0;
                background-position: right 12px center;
            }
        }
    </style>
</head>

<body>
    <!-- ================= MAIN CONTENT ================= -->
    <div class="body_content_container">
        <?php include "includes/admission_navbar.php"; ?>
        <!-- Banner -->
        <div class="cards_container">
            <div class="banner_container">
                <div class="banner_tile">
                    <div class="text_area_container">
                        <h2 class="bigger_text">MY APPLICATION</h2>
                        <p>Track and manage your application submissions</p>
                    </div>
                </div>
            </div>

            <!-- Table Section -->
            <div class="table_section">

                <div class="table_controls">
                    <div class="controls_left">
                        <!-- Search Bar -->
                        <div class="search_container">
                            <div class="search_input_wrapper">
                                <svg class="search_icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" role="button" tabindex="0" aria-label="Search">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                                <input type="text" class="search_input" placeholder="Search by No., Type, Academic Year..." id="searchInput">
                            </div>
                        </div>
                    </div>

                    <!-- 
                    <div class="controls_right">
                        <button class="btn btn_secondary">
                            <svg class="icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                            </svg>
                            Export
                        </button>
                    </div> -->
                </div>

                <div class="table_container">
                    <table class="data_table">
                        <thead class="table_header">
                            <tr>

                                <th>
                                    <div class="sort_header" data-sort-key="no" aria-sort="none" role="button" tabindex="0" aria-label="Sort by NO">
                                        NO
                                        <svg class="sort_icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                                        </svg>
                                    </div>
                                </th>
                                <th>
                                    <div class="sort_header" data-sort-key="type" aria-sort="none" role="button" tabindex="0" aria-label="Sort by TYPE">
                                        TYPE
                                        <svg class="sort_icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                                        </svg>
                                    </div>
                                </th>
                                <th>
                                    <div class="sort_header" data-sort-key="academicYear" aria-sort="none" role="button" tabindex="0" aria-label="Sort by ACADEMIC YEAR">
                                        ACADEMIC YEAR
                                        <svg class="sort_icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                                        </svg>
                                    </div>
                                </th>
                                <th>
                                    <div class="sort_header" data-sort-key="status" aria-sort="none" role="button" tabindex="0" aria-label="Sort by STATUS">
                                        STATUS
                                        <svg class="sort_icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                                        </svg>
                                    </div>
                                </th>
                                <th>
                                    <div class="sort_header" data-sort-key="remarks" aria-sort="none" role="button" tabindex="0" aria-label="Sort by REMARKS">
                                        REMARKS
                                        <svg class="sort_icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                                        </svg>
                                    </div>
                                </th>
                                <th>ACTIONS</th>
                            </tr>
                        </thead>
                        <tbody class="table_body">
                        </tbody>
                    </table>

                    <!-- Empty State -->
                    <div class="empty_state" id="emptyState" style="display: none;">
                        <div class="empty_state_content">
                            <svg class="empty_state_icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <h3 class="empty_state_title">No Records Found</h3>
                            <p class="empty_state_message">No applications match your current search or filter criteria.</p>
                        </div>
                    </div>
                </div>

                <!-- Pagination Controls -->
                <div class="pagination_container">
                    <div class="pagination_info">
                        <div class="entries_per_page">
                            <label for="entriesPerPage">Show:</label>
                            <select id="entriesPerPage" class="entries_select">
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                            <span>entries</span>
                        </div>
                        <div class="entries_counter">
                            <span id="entriesCounter">Showing 1-10 of 50 entries</span>
                        </div>
                    </div>

                    <div class="pagination_controls">
                        <button id="prevPage" class="pagination_btn" disabled>
                            <svg class="icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                            Previous
                        </button>

                        <div class="page_numbers" id="pageNumbers">
                            <!-- Page numbers will be generated dynamically -->
                        </div>

                        <button id="nextPage" class="pagination_btn">
                            Next
                            <svg class="icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div><!-- end table_section -->
        </div><!-- end cards_container -->
    </div><!-- end body_content_container -->

    <?php include "includes/footer.php"; ?>

    <?php include "includes/modal.php"; ?>
    <?php include "includes/loader.php"; ?>

    <!-- Responsive Table Overrides & Mobile Reflow -->
    <style>
        :root {
            --primary-green: #18a558;
            --hover-green: #136515;
        }

        /* Replace blue tones with green across table & pagination */
        .search_input:focus {
            border-color: var(--primary-green);
            box-shadow: 0 0 0 3px rgba(24, 165, 88, 0.15);
        }

        .filter_select:focus {
            border-color: var(--primary-green);
            box-shadow: 0 0 0 3px rgba(24, 165, 88, 0.15);
        }

        .action_view {
            background-color: #eafaf2;
            color: var(--hover-green);
        }

        .action_view:hover {
            background-color: #dcfce7;
        }

        .action_edit {
            background-color: #dcfce7;
            color: var(--hover-green);
        }

        .action_edit:hover {
            background-color: #bbf7d0;
        }

        .pagination_btn {
            border-color: var(--primary-green);
            color: var(--primary-green);
        }

        .pagination_btn:hover:not(:disabled) {
            border-color: var(--hover-green);
            color: var(--hover-green);
        }

        .page_number {
            color: var(--hover-green);
        }

        .page_number:hover {
            border-color: var(--primary-green);
            color: var(--hover-green);
        }

        .page_number.active {
            background-color: var(--primary-green);
            border-color: var(--primary-green);
        }

        /* Mobile-first Table Reflow (≤480px) */
        @media (max-width: 480px) {
            .table_container {
                overflow: visible;
            }

            .data_table {
                width: 100%;
                border-collapse: separate;
                border-spacing: 0;
                min-width: 100%;
            }

            .data_table .table_header {
                position: absolute;
                left: -9999px;
                top: -9999px;
                height: 0;
                width: 0;
                overflow: hidden;
            }

            .table_body {
                display: grid;
                gap: var(--space-md);
            }

            .table_body tr {
                display: grid;
                grid-template-columns: 1fr;
                background-color: var(--color-white);
                border: 1px solid var(--color-gray-light);
                border-radius: var(--radius-md);
                padding: var(--space-md);
                box-shadow: var(--shadow-sm);
            }

            .table_body td {
                display: grid;
                grid-template-columns: minmax(120px, 40%) 1fr;
                gap: 8px;
                padding: 10px 0;
                border: none;
            }

            .table_body td::before {
                content: attr(data-label);
                font-weight: 600;
                color: var(--color-gray-dark);
            }

            .table_body tr td:nth-child(1)::before {
                content: "No";
            }

            .table_body tr td:nth-child(2)::before {
                content: "Type";
            }

            .table_body tr td:nth-child(3)::before {
                content: "Academic Year";
            }

            .table_body tr td:nth-child(4)::before {
                content: "Status";
            }

            .table_body tr td:nth-child(5)::before {
                content: "Remarks";
            }

            .table_body tr td:nth-child(6)::before {
                content: "Actions";
            }

            .actions_cell {
                display: flex;
                flex-wrap: wrap;
                gap: 8px;
            }

            .search_input {
                width: 100%;
            }

            .pagination_container {
                flex-direction: column;
                align-items: stretch;
                gap: var(--space-md);
            }

            .pagination_info {
                justify-content: space-between;
            }

            .pagination_controls {
                justify-content: space-between;
                flex-wrap: wrap;
            }
        }
    </style>

    <!-- Remarks line-break styling -->
    <style>
        /* Preserve newlines from database in Remarks (table + modal) */
        .table_body tr td:nth-child(5) {
            white-space: pre-line;
        }

        .kv_item .kv_value {
            white-space: pre-line;
        }
    </style>

    <!-- View Submission Modal -->
    <style>
        .view-modal {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.4);
            justify-content: center;
            align-items: center;
            z-index: 1100;
        }

        .view-modal.active {
            display: flex;
        }

        .view-modal-content {
            background: #fff;
            width: 90%;
            max-width: 900px;
            max-height: 85vh;
            border-radius: 14px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .view-modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 20px;
            border-bottom: 1px solid #e5e7eb;
        }

        .view-modal-title {
            font-size: 18px;
            font-weight: 600;
            margin: 0;
        }

        .view-modal-close {
            background: #f3f4f6;
            border: none;
            border-radius: 8px;
            width: 34px;
            height: 34px;
            cursor: pointer;
            font-size: 18px;
        }

        .view-modal-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            padding: 12px 20px;
            border-bottom: 1px solid #e5e7eb;
            background: #fafafa;
        }

        .meta_chip {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 10px;
            background: #f3f4f6;
            border-radius: 999px;
            font-size: 13px;
            color: #374151;
        }

        .status_chip {
            padding: 6px 10px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 500;
        }

        .view-modal-body {
            padding: 18px;
            overflow: auto;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .section_card {
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 14px;
        }

        .section_title {
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .kv_list {
            display: grid;
            gap: 10px;
        }

        .kv_item {
            display: grid;
            grid-template-columns: 160px 1fr;
            gap: 8px;
            align-items: start;
        }

        .kv_key {
            font-size: 13px;
            color: #6b7280;
        }

        .kv_value {
            font-size: 13px;
            color: #111827;
            word-wrap: break-word;
        }

        .files_list {
            display: grid;
            gap: 10px;
        }

        .file_item {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .file_name {
            font-size: 13px;
            color: #111827;
        }

        .file_actions a {
            font-size: 13px;
            text-decoration: none;
        }

        .loading_state {
            padding: 20px;
            text-align: center;
            color: #6b7280;
        }

        @media (max-width: 768px) {
            .view-modal-body {
                flex-direction: column;
            }

            .kv_item {
                grid-template-columns: 1fr;
            }
        }

        /* =========================================================
            Select Dropdown Enhancements (Green Theme + Mobile)
        ========================================================= */
        .entries_select {
            width: auto;
            min-width: 84px;
            height: 34px;
            padding: 4px 28px 4px 10px;
            border: 1px solid #18a558;
            border-radius: 8px;
            font-size: var(--font-size-sm);
            color: var(--color-gray-dark);
            background-color: var(--color-white);
            background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='%2318a558'><path fill-rule='evenodd' d='M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.08 1.04l-4.25 4.25a.75.75 0 01-1.06 0L5.21 8.27a.75.75 0 01.02-1.06z' clip-rule='evenodd'/></svg>");
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 16px 16px;
            appearance: none;
            cursor: pointer;
            transition: border-color 0.2s ease, box-shadow 0.2s ease, color 0.2s ease;
        }

        .entries_select:hover {
            border-color: #136515;
        }

        .entries_select:focus {
            outline: none;
            border-color: #136515;
            box-shadow: 0 0 0 3px rgba(24, 165, 88, 0.15);
        }

        .entries_select:disabled {
            background-color: var(--color-gray-lightest);
            color: var(--color-gray-medium);
            border-color: var(--color-gray-light);
            cursor: not-allowed;
        }

        .filter_select {
            width: 100%;
            padding: 10px 40px 10px 14px;
            border: 1px solid #18a558;
            border-radius: var(--border-radius-md);
            font-size: var(--font-size-sm);
            color: var(--color-gray-dark);
            background-color: var(--color-white);
            background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='%2318a558'><path fill-rule='evenodd' d='M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.08 1.04l-4.25 4.25a.75.75 0 01-1.06 0L5.21 8.27a.75.75 0 01.02-1.06z' clip-rule='evenodd'/></svg>");
            background-repeat: no-repeat;
            background-position: right 12px center;
            background-size: 16px 16px;
            appearance: none;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .filter_select:hover {
            border-color: #136515;
        }

        .filter_select:focus {
            outline: none;
            border-color: #136515;
            box-shadow: 0 0 0 3px rgba(24, 165, 88, 0.15);
        }

        @media (max-width: 480px) {
            .entries_per_page {
                flex-direction: column;
                align-items: stretch;
                gap: var(--space-xs);
            }

            .entries_select {
                width: 100%;
                min-width: 0;
                background-position: right 12px center;
            }
        }

        /* =========================================================
            Select + Pagination Refinements (Green, Responsive, No layout change)
        ========================================================= */
        .entries_select {
            width: auto;
            min-width: 84px;
            height: 34px;
            padding: 4px 28px 4px 10px;
            border: 1px solid #18a558;
            border-radius: 10px;
            font-size: var(--font-size-sm);
            color: var(--color-gray-dark);
            background-color: var(--color-white);
            background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='%2318a558'><path fill-rule='evenodd' d='M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.08 1.04l-4.25 4.25a.75.75 0 01-1.06 0L5.21 8.27a.75.75 0 01.02-1.06z' clip-rule='evenodd'/></svg>");
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 16px 16px;
            appearance: none;
            cursor: pointer;
            transition: border-color 0.2s ease, box-shadow 0.2s ease, color 0.2s ease;
        }

        .entries_select:hover {
            border-color: #136515;
        }

        .entries_select:focus {
            outline: none;
            border-color: #136515;
            box-shadow: 0 0 0 2px rgba(24, 165, 88, .15);
        }

        .entries_select:disabled {
            background-color: var(--color-gray-lightest);
            color: var(--color-gray-medium);
            border-color: var(--color-gray-light);
            cursor: not-allowed;
        }

        .filter_select {
            width: 100%;
            padding: 10px 40px 10px 14px;
            border: 1px solid #18a558;
            border-radius: var(--border-radius-md);
            font-size: var(--font-size-sm);
            color: var(--color-gray-dark);
            background-color: var(--color-white);
            background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='%2318a558'><path fill-rule='evenodd' d='M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.08 1.04l-4.25 4.25a.75.75 0 01-1.06 0L5.21 8.27a.75.75 0 01.02-1.06z' clip-rule='evenodd'/></svg>");
            background-repeat: no-repeat;
            background-position: right 12px center;
            background-size: 16px 16px;
            appearance: none;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .filter_select:hover {
            border-color: #136515;
        }

        .filter_select:focus {
            outline: none;
            border-color: #136515;
            box-shadow: 0 0 0 2px rgba(24, 165, 88, .15);
        }

        /* Keep row layout; just allow wrap on small screens */
        .pagination_container {
            flex-wrap: wrap;
            column-gap: var(--space-sm);
            row-gap: var(--space-sm);
        }

        .pagination_controls {
            flex-wrap: wrap;
        }

        @media (max-width: 768px) {
            .pagination_container {
                flex-direction: row !important;
                flex-wrap: wrap;
                gap: var(--space-sm);
            }

            .pagination_info {
                flex-direction: row !important;
                text-align: left !important;
                gap: var(--space-sm);
                flex-wrap: wrap;
            }

            .pagination_controls {
                flex-wrap: wrap;
            }
        }

        @media (max-width: 480px) {
            .entries_per_page {
                flex-direction: row;
                align-items: center;
                gap: var(--space-xs);
            }

            .entries_select {
                width: 100%;
                min-width: 0;
                background-position: right 12px center;
            }
        }
    </style>

    <!-- File Preview Modal Styles -->
    <style>
        .preview-modal {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
            z-index: 1200;
        }

        .preview-modal.active {
            display: flex;
        }

        .preview-modal-content {
            background: #fff;
            width: 92%;
            max-width: 1000px;
            max-height: 85vh;
            border-radius: 14px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .preview-modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 18px;
            border-bottom: 1px solid #e5e7eb;
            background: #fafafa;
        }

        .preview-modal-title {
            font-size: 16px;
            font-weight: 600;
            margin: 0;
        }

        .preview-modal-close {
            background: #f3f4f6;
            border: none;
            border-radius: 8px;
            width: 32px;
            height: 32px;
            cursor: pointer;
            font-size: 18px;
        }

        .preview-modal-body {
            padding: 0;
            display: block;
            min-height: 60vh;
            background: #fff;
            overflow: hidden;
        }

        /* Ensure embedded previews fill the modal neatly */
        .preview-modal-body iframe,
        .preview-modal-body object {
            width: 100%;
            height: 80vh;
            border: 0;
            display: block;
        }

        .preview-modal-body img {
            max-width: 100%;
            max-height: 80vh;
            display: block;
            margin: 0 auto;
        }

        .preview-loading {
            color: #6b7280;
        }

        .preview-error {
            color: #dc2626;
        }

        /* Readable text preview */
        .preview-text {
            max-height: 80vh;
            overflow: auto;
            padding: 16px;
            margin: 0;
            line-height: 1.45;
            background: #fafafa;
            border-left: 4px solid #e5e7eb;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
            white-space: pre-wrap;
            word-break: break-word;
        }

        @media (max-width: 768px) {
            .preview-modal-content {
                width: 95%;
            }
        }

        /* =========================================================
            Select Dropdown Enhancements (Green Theme + Mobile)
        ========================================================= */
        .entries_select {
            width: auto;
            min-width: 84px;
            height: 34px;
            padding: 4px 28px 4px 10px;
            border: 1px solid #18a558;
            border-radius: 8px;
            font-size: var(--font-size-sm);
            color: var(--color-gray-dark);
            background-color: var(--color-white);
            background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='%2318a558'><path fill-rule='evenodd' d='M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.08 1.04l-4.25 4.25a.75.75 0 01-1.06 0L5.21 8.27a.75.75 0 01.02-1.06z' clip-rule='evenodd'/></svg>");
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 16px 16px;
            appearance: none;
            cursor: pointer;
            transition: border-color 0.2s ease, box-shadow 0.2s ease, color 0.2s ease;
        }

        .entries_select:hover {
            border-color: #136515;
        }

        .entries_select:focus {
            outline: none;
            border-color: #136515;
            box-shadow: 0 0 0 3px rgba(24, 165, 88, 0.15);
        }

        .entries_select:disabled {
            background-color: var(--color-gray-lightest);
            color: var(--color-gray-medium);
            border-color: var(--color-gray-light);
            cursor: not-allowed;
        }

        .filter_select {
            width: 100%;
            padding: 10px 40px 10px 14px;
            border: 1px solid #18a558;
            border-radius: var(--border-radius-md);
            font-size: var(--font-size-sm);
            color: var(--color-gray-dark);
            background-color: var(--color-white);
            background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='%2318a558'><path fill-rule='evenodd' d='M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.08 1.04l-4.25 4.25a.75.75 0 01-1.06 0L5.21 8.27a.75.75 0 01.02-1.06z' clip-rule='evenodd'/></svg>");
            background-repeat: no-repeat;
            background-position: right 12px center;
            background-size: 16px 16px;
            appearance: none;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .filter_select:hover {
            border-color: #136515;
        }

        .filter_select:focus {
            outline: none;
            border-color: #136515;
            box-shadow: 0 0 0 3px rgba(24, 165, 88, 0.15);
        }

        @media (max-width: 480px) {
            .entries_per_page {
                flex-direction: column;
                align-items: stretch;
                gap: var(--space-xs);
            }

            .entries_select {
                width: 100%;
                min-width: 0;
                background-position: right 12px center;
            }
        }

        /* =========================================================
            Select + Pagination Refinements (Green, Responsive, No layout change)
        ========================================================= */
        .entries_select {
            width: auto;
            min-width: 84px;
            height: 34px;
            padding: 4px 28px 4px 10px;
            border: 1px solid #18a558;
            border-radius: 10px;
            font-size: var(--font-size-sm);
            color: var(--color-gray-dark);
            background-color: var(--color-white);
            background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='%2318a558'><path fill-rule='evenodd' d='M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.08 1.04l-4.25 4.25a.75.75 0 01-1.06 0L5.21 8.27a.75.75 0 01.02-1.06z' clip-rule='evenodd'/></svg>");
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 16px 16px;
            appearance: none;
            cursor: pointer;
            transition: border-color 0.2s ease, box-shadow 0.2s ease, color 0.2s ease;
        }

        .entries_select:hover {
            border-color: #136515;
        }

        .entries_select:focus {
            outline: none;
            border-color: #136515;
            box-shadow: 0 0 0 2px rgba(24, 165, 88, .15);
        }

        .entries_select:disabled {
            background-color: var(--color-gray-lightest);
            color: var(--color-gray-medium);
            border-color: var(--color-gray-light);
            cursor: not-allowed;
        }

        .filter_select {
            width: 100%;
            padding: 10px 40px 10px 14px;
            border: 1px solid #18a558;
            border-radius: var(--border-radius-md);
            font-size: var(--font-size-sm);
            color: var(--color-gray-dark);
            background-color: var(--color-white);
            background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='%2318a558'><path fill-rule='evenodd' d='M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.08 1.04l-4.25 4.25a.75.75 0 01-1.06 0L5.21 8.27a.75.75 0 01.02-1.06z' clip-rule='evenodd'/></svg>");
            background-repeat: no-repeat;
            background-position: right 12px center;
            background-size: 16px 16px;
            appearance: none;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .filter_select:hover {
            border-color: #136515;
        }

        .filter_select:focus {
            outline: none;
            border-color: #136515;
            box-shadow: 0 0 0 2px rgba(24, 165, 88, .15);
        }

        /* Keep row layout; just allow wrap on small screens */
        .pagination_container {
            flex-wrap: wrap;
            column-gap: var(--space-sm);
            row-gap: var(--space-sm);
        }

        .pagination_controls {
            flex-wrap: wrap;
        }

        @media (max-width: 768px) {
            .pagination_container {
                flex-direction: row !important;
                flex-wrap: wrap;
                gap: var(--space-sm);
            }

            .pagination_info {
                flex-direction: row !important;
                text-align: left !important;
                gap: var(--space-sm);
                flex-wrap: wrap;
            }

            .pagination_controls {
                flex-wrap: wrap;
            }
        }

        @media (max-width: 480px) {
            .entries_per_page {
                flex-direction: row;
                align-items: center;
                gap: var(--space-xs);
            }

            .entries_select {
                width: 100%;
                min-width: 0;
                background-position: right 12px center;
            }
        }
    </style>

    <div id="viewSubmissionModal" class="view-modal" aria-hidden="true">
        <div class="view-modal-content" role="dialog" aria-modal="true" aria-labelledby="viewModalTitle">
            <div class="view-modal-header">
                <h3 id="viewModalTitle" class="view-modal-title">Application Details</h3>
                <button id="viewModalClose" class="view-modal-close" title="Close">×</button>
            </div>
            <div id="viewModalMeta" class="view-modal-meta">
                <!-- Summary chips injected here -->
            </div>
            <div id="viewModalBody" class="view-modal-body">
                <div class="section_card">
                    <div class="section_title">Application Details</div>
                    <div id="viewModalApplicationStatus" class="kv_list"></div>
                </div>
                <div class="section_card">
                    <div class="section_title">Submitted Data</div>
                    <div id="viewModalData" class="kv_list"></div>
                </div>
                <div class="section_card">
                    <div class="section_title">Uploaded Files</div>
                    <div id="viewModalFiles" class="files_list"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- File Preview Modal Markup -->
    <div id="filePreviewModal" class="preview-modal" aria-hidden="true">
        <div class="preview-modal-content" role="dialog" aria-modal="true" aria-labelledby="previewModalTitle">
            <div class="preview-modal-header">
                <h3 id="previewModalTitle" class="preview-modal-title">File Preview</h3>
                <button id="filePreviewClose" class="preview-modal-close" title="Close">×</button>
            </div>
            <div id="previewModalBody" class="preview-modal-body">
                <span class="preview-loading">Loading preview...</span>
            </div>
        </div>
    </div>

    <!-- Edit Submission Modal Styles -->
    <style>
        .modal_overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.4);
            justify-content: center;
            align-items: center;
            z-index: 1150;
        }

        .modal_overlay.active {
            display: flex;
        }

        .modal_container {
            background: #fff;
            width: 92%;
            max-width: 1000px;
            max-height: 85vh;
            border-radius: 14px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .modal_header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 18px;
            border-bottom: 1px solid #e5e7eb;
            background: #fafafa;
        }

        .modal_title {
            font-size: 18px;
            font-weight: 600;
            margin: 0;
        }

        .modal_close {
            background: #f3f4f6;
            border: none;
            border-radius: 8px;
            width: 34px;
            height: 34px;
            cursor: pointer;
            font-size: 18px;
        }

        .modal_meta_chips {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            padding: 12px 18px;
            border-bottom: 1px solid #e5e7eb;
            background: #fafafa;
        }

        .status_chip {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 10px;
            background: #f3f4f6;
            border-radius: 999px;
            font-size: 13px;
            color: #374151;
        }

        .modal_body {
            display: flex;
            flex-direction: column;
            gap: 20px;
            padding: 18px;
            /* Make the edit modal content scrollable within the container */
            flex: 1;
            overflow: auto;
            min-height: 0;
        }

        .section_card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 16px;
        }

        .section_title {
            font-weight: 600;
            margin-bottom: 10px;
        }

        .kv_list {}

        .kv_item {
            display: grid;
            grid-template-columns: 200px 1fr;
            gap: 12px;
            padding: 6px 0;
            align-items: center;
        }

        .kv_key {
            font-weight: 500;
            color: #374151;
            text-transform: capitalize;
        }

        .kv_value_input,
        .file_replace_input {
            width: 100%;
            padding: var(--space-xs) 10px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            font-size: 14px;
        }

        .file_item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #f3f4f6;
        }

        .file_name {
            color: #374151;
        }

        .file_actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        @media (max-width: 768px) {
            .modal_body {
                flex-direction: column;
            }

            .kv_item {
                grid-template-columns: 1fr;
            }
        }

        /* =========================================================
            Select Dropdown Enhancements (Green Theme + Mobile)
        ========================================================= */
        .entries_select {
            width: auto;
            min-width: 84px;
            height: 34px;
            padding: 4px 28px 4px 10px;
            border: 1px solid #18a558;
            border-radius: 8px;
            font-size: var(--font-size-sm);
            color: var(--color-gray-dark);
            background-color: var(--color-white);
            background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='%2318a558'><path fill-rule='evenodd' d='M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.08 1.04l-4.25 4.25a.75.75 0 01-1.06 0L5.21 8.27a.75.75 0 01.02-1.06z' clip-rule='evenodd'/></svg>");
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 16px 16px;
            appearance: none;
            cursor: pointer;
            transition: border-color 0.2s ease, box-shadow 0.2s ease, color 0.2s ease;
        }

        .entries_select:hover {
            border-color: #136515;
        }

        .entries_select:focus {
            outline: none;
            border-color: #136515;
            box-shadow: 0 0 0 3px rgba(24, 165, 88, 0.15);
        }

        .entries_select:disabled {
            background-color: var(--color-gray-lightest);
            color: var(--color-gray-medium);
            border-color: var(--color-gray-light);
            cursor: not-allowed;
        }

        .filter_select {
            width: 100%;
            padding: 10px 40px 10px 14px;
            border: 1px solid #18a558;
            border-radius: var(--border-radius-md);
            font-size: var(--font-size-sm);
            color: var(--color-gray-dark);
            background-color: var(--color-white);
            background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='%2318a558'><path fill-rule='evenodd' d='M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.08 1.04l-4.25 4.25a.75.75 0 01-1.06 0L5.21 8.27a.75.75 0 01.02-1.06z' clip-rule='evenodd'/></svg>");
            background-repeat: no-repeat;
            background-position: right 12px center;
            background-size: 16px 16px;
            appearance: none;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .filter_select:hover {
            border-color: #136515;
        }

        .filter_select:focus {
            outline: none;
            border-color: #136515;
            box-shadow: 0 0 0 3px rgba(24, 165, 88, 0.15);
        }

        @media (max-width: 480px) {
            .entries_per_page {
                flex-direction: column;
                align-items: stretch;
                gap: var(--space-xs);
            }

            .entries_select {
                width: 100%;
                min-width: 0;
                background-position: right 12px center;
            }
        }

        /* =========================================================
            Select + Pagination Refinements (Green, Responsive, No layout change)
        ========================================================= */
        .entries_select {
            width: auto;
            min-width: 84px;
            height: 34px;
            padding: 4px 28px 4px 10px;
            border: 1px solid #18a558;
            border-radius: 10px;
            font-size: var(--font-size-sm);
            color: var(--color-gray-dark);
            background-color: var(--color-white);
            background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='%2318a558'><path fill-rule='evenodd' d='M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.08 1.04l-4.25 4.25a.75.75 0 01-1.06 0L5.21 8.27a.75.75 0 01.02-1.06z' clip-rule='evenodd'/></svg>");
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 16px 16px;
            appearance: none;
            cursor: pointer;
            transition: border-color 0.2s ease, box-shadow 0.2s ease, color 0.2s ease;
        }

        .entries_select:hover {
            border-color: #136515;
        }

        .entries_select:focus {
            outline: none;
            border-color: #136515;
            box-shadow: 0 0 0 2px rgba(24, 165, 88, .15);
        }

        .entries_select:disabled {
            background-color: var(--color-gray-lightest);
            color: var(--color-gray-medium);
            border-color: var(--color-gray-light);
            cursor: not-allowed;
        }

        .filter_select {
            width: 100%;
            padding: 10px 40px 10px 14px;
            border: 1px solid #18a558;
            border-radius: var(--border-radius-md);
            font-size: var(--font-size-sm);
            color: var(--color-gray-dark);
            background-color: var(--color-white);
            background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='%2318a558'><path fill-rule='evenodd' d='M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.08 1.04l-4.25 4.25a.75.75 0 01-1.06 0L5.21 8.27a.75.75 0 01.02-1.06z' clip-rule='evenodd'/></svg>");
            background-repeat: no-repeat;
            background-position: right 12px center;
            background-size: 16px 16px;
            appearance: none;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .filter_select:hover {
            border-color: #136515;
        }

        .filter_select:focus {
            outline: none;
            border-color: #136515;
            box-shadow: 0 0 0 2px rgba(24, 165, 88, .15);
        }

        /* Keep row layout; just allow wrap on small screens */
        .pagination_container {
            flex-wrap: wrap;
            column-gap: var(--space-sm);
            row-gap: var(--space-sm);
        }

        .pagination_controls {
            flex-wrap: wrap;
        }

        @media (max-width: 768px) {
            .pagination_container {
                flex-direction: row !important;
                flex-wrap: wrap;
                gap: var(--space-sm);
            }

            .pagination_info {
                flex-direction: row !important;
                text-align: left !important;
                gap: var(--space-sm);
                flex-wrap: wrap;
            }

            .pagination_controls {
                flex-wrap: wrap;
            }
        }

        @media (max-width: 480px) {
            .entries_per_page {
                flex-direction: row;
                align-items: center;
                gap: var(--space-xs);
            }

            .entries_select {
                width: 100%;
                min-width: 0;
                background-position: right 12px center;
            }
        }
    </style>

    <!-- Edit Submission Modal -->
    <div id="editSubmissionModal" class="modal_overlay" aria-hidden="true">
        <div class="modal_container" role="dialog" aria-modal="true" aria-labelledby="editModalTitle">
            <div class="modal_header">
                <h3 id="editModalTitle" class="modal_title">Edit Submission</h3>
                <button id="editModalClose" class="modal_close" title="Close">×</button>
            </div>
            <div id="editModalMeta" class="modal_meta_chips"></div>

            <div class="modal_body">
                <div class="section_card">
                    <div class="section_title">Edit Data Fields</div>
                    <form id="editSubmissionForm">
                        <div id="editModalDataFields" class="kv_list"></div>
                    </form>
                </div>
                <div class="section_card">
                    <div class="section_title">Replace Uploaded Files</div>
                    <div id="editModalFiles" class="files_list"></div>
                </div>
                <div class="form_actions save-changes-btn">
                    <button type="submit" class="btn btn_primary" id="saveDataBtn" form="editSubmissionForm">Save Changes</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Select-all checkbox removed; no bulk selection behavior in this table.

        // Sort functionality (basic implementation)
        document.querySelectorAll('.sort_header').forEach(header => {
            header.addEventListener('click', function() {
                // Add sorting logic here
                console.log('Sort by:', this.textContent.trim());
            });
        });

        // Action button handlers
        document.querySelectorAll('.action_btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const action = this.title;
                console.log('Action:', action);
                // Add specific action logic here
            });
        });

        // Search functionality - this will be replaced by the pagination search function



        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            const filterModalEl = document.querySelector('.filter_modal');
            if (e.key === 'Escape' && filterModalEl && filterModalEl.classList.contains('show') && typeof closeModal === 'function') {
                closeModal();
            }
        });



        // =========================================================
        // 📄 PAGINATION AND DATA MANAGEMENT
        // =========================================================

        // Pagination and data variables
        let currentPage = 1;
        let entriesPerPage = 10;
        let filteredData = [];
        let allData = [];

        // Sorting state
        let sortKey = null; // one of: 'no', 'type', 'academicYear', 'status', 'remarks'
        let sortDirection = 'asc'; // 'asc' or 'desc'

        // Load applications from API
        async function loadApplications() {
            if (typeof showLoader === 'function') showLoader();
            try {
                const res = await fetch('../api/get_my_applications.php', {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json'
                    }
                });
                const payload = await res.json();

                if (!payload.success) {
                    console.error('Failed to fetch applications:', payload.message);
                    allData = [];
                } else {
                    const rows = payload.data || [];
                    allData = rows.map((row, idx) => ({
                        no: idx + 1,
                        submissionId: row.submission_id,
                        type: row.type || 'N/A',
                        academicYear: row.academic_year || 'N/A',
                        status: row.status || 'N/A',
                        remarks: row.remarks || '',
                        statusColor: row.status_color || '#64748b',
                        can_update: (row.can_update === 1 || row.can_update === true)
                    }));
                }

                filteredData = [...allData];
                currentPage = 1;
                renderTable();
                updatePaginationControls();
            } catch (err) {
                console.error('Error loading applications:', err);
                allData = [];
                filteredData = [];
                renderTable();
                updatePaginationControls();
            } finally {
                if (typeof hideLoader === 'function') hideLoader();
            }
        }

        // Generate table row HTML aligned to headers
        function generateTableRow(application, index) {
            const editDisabled = !application.can_update;
            const editTitle = editDisabled ? 'Updates disabled' : 'Edit';
            // Keep button clickable for modal messaging; do not use native disabled
            const editDisabledAttr = editDisabled ? 'aria-disabled="true" data-disabled="true"' : '';
            const editStyle = editDisabled ? 'style="opacity:0.6; cursor:not-allowed;"' : '';
            const badgeBg = `${application.statusColor}20`;
            return `
                <tr>
                    <td>${application.no}</td>
                    <td>${application.type}</td>
                    <td>${application.academicYear}</td>
                    <td><span class="status_badge" style="background-color: ${badgeBg}; color: ${application.statusColor}">${application.status}</span></td>
                    <td>${application.remarks}</td>
                    <td>
                        <div class="actions_cell">
                            <button class="action_btn action_view" title="View" data-submission-id="${application.submissionId}">
                                <svg class="icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                            <button class="action_btn action_edit" ${editDisabledAttr} ${editStyle} title="${editTitle}" data-submission-id="${application.submissionId}">
                                <svg class="icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        }

        // Update entries counter
        function updateEntriesCounter() {
            const totalEntries = filteredData.length;
            const startEntry = totalEntries === 0 ? 0 : (currentPage - 1) * entriesPerPage + 1;
            const endEntry = Math.min(currentPage * entriesPerPage, totalEntries);

            document.getElementById('entriesCounter').textContent =
                `Showing ${startEntry}-${endEntry} of ${totalEntries} entries`;
        }

        // Generate page numbers
        function generatePageNumbers() {
            const totalPages = Math.ceil(filteredData.length / entriesPerPage);
            const pageNumbersContainer = document.getElementById('pageNumbers');
            pageNumbersContainer.innerHTML = '';

            for (let i = 1; i <= totalPages; i++) {
                const pageBtn = document.createElement('button');
                pageBtn.className = `page_number ${i === currentPage ? 'active' : ''}`;
                pageBtn.textContent = i;
                pageBtn.addEventListener('click', () => goToPage(i));
                pageNumbersContainer.appendChild(pageBtn);
            }
        }

        // Go to specific page
        function goToPage(page) {
            const totalPages = Math.ceil(filteredData.length / entriesPerPage);
            if (page < 1 || page > totalPages) return;

            currentPage = page;
            renderTable();
            updatePaginationControls();
        }

        // Update pagination controls
        function updatePaginationControls() {
            const totalPages = Math.ceil(filteredData.length / entriesPerPage);

            document.getElementById('prevPage').disabled = currentPage === 1;
            document.getElementById('nextPage').disabled = currentPage === totalPages || totalPages === 0;

            generatePageNumbers();
            updateEntriesCounter();
        }

        // Render table with current page data
        function renderTable() {
            const tableBody = document.querySelector('.table_body');
            const emptyState = document.getElementById('emptyState');
            const tableContainer = document.querySelector('.table_container table');
            const paginationContainer = document.querySelector('.pagination_container');

            const startIndex = (currentPage - 1) * entriesPerPage;
            const endIndex = startIndex + entriesPerPage;
            const pageData = filteredData.slice(startIndex, endIndex);

            // Check if there are any filtered results
            if (filteredData.length === 0) {
                // Show empty state, hide table and pagination
                emptyState.style.display = 'flex';
                tableContainer.style.display = 'none';
                paginationContainer.style.display = 'none';
            } else {
                // Show table and pagination, hide empty state
                emptyState.style.display = 'none';
                tableContainer.style.display = 'table';
                paginationContainer.style.display = 'flex';

                // Render rows
                tableBody.innerHTML = pageData.map(generateTableRow).join('');
            }
        }

        // Sorting helpers
        function compareValues(a, b, key) {
            const va = a && a[key] !== undefined && a[key] !== null ? a[key] : '';
            const vb = b && b[key] !== undefined && b[key] !== null ? b[key] : '';
            if (key === 'no') {
                const na = Number(va) || 0;
                const nb = Number(vb) || 0;
                return na - nb;
            }
            return String(va).toLowerCase().localeCompare(String(vb).toLowerCase());
        }

        function applySort() {
            if (!sortKey) return;
            const dir = sortDirection === 'desc' ? -1 : 1;
            filteredData.sort((a, b) => compareValues(a, b, sortKey) * dir);
        }

        function updateSortIndicators() {
            const headers = document.querySelectorAll('.table_header .sort_header');
            headers.forEach(h => {
                const key = h.getAttribute('data-sort-key');
                if (key && key === sortKey) {
                    h.setAttribute('aria-sort', sortDirection === 'asc' ? 'ascending' : 'descending');
                } else {
                    h.setAttribute('aria-sort', 'none');
                }
            });
        }

        function setSort(key) {
            if (!key) return;
            if (sortKey === key) {
                sortDirection = sortDirection === 'asc' ? 'desc' : 'asc';
            } else {
                sortKey = key;
                sortDirection = 'asc';
            }
            applySort();
            updateSortIndicators();
            currentPage = 1;
            renderTable();
            updatePaginationControls();
        }

        function initializeSorting() {
            const headers = document.querySelectorAll('.table_header .sort_header');
            headers.forEach(header => {
                const key = header.getAttribute('data-sort-key');
                if (!key) return;
                header.addEventListener('click', () => setSort(key));
                header.addEventListener('keydown', (e) => {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        setSort(key);
                    }
                });
                // Ensure interactive accessibility even if attributes change
                header.setAttribute('tabindex', header.getAttribute('tabindex') || '0');
                header.setAttribute('role', header.getAttribute('role') || 'button');
                header.setAttribute('aria-label', header.getAttribute('aria-label') || `Sort by ${key}`);
            });
            updateSortIndicators();
        }

        // === View Modal Logic ===
        const viewModalEl = document.getElementById('viewSubmissionModal');
        const viewModalCloseBtn = document.getElementById('viewModalClose');
        const viewModalMeta = document.getElementById('viewModalMeta');
        const viewModalBody = document.getElementById('viewModalBody');
        const viewModalData = document.getElementById('viewModalData');
        const viewModalFiles = document.getElementById('viewModalFiles');
        const viewModalTitle = document.getElementById('viewModalTitle');
        const viewModalAppStatus = document.getElementById('viewModalApplicationStatus');

        function closeViewModal() {
            viewModalEl.classList.remove('active');
            viewModalEl.setAttribute('aria-hidden', 'true');
        }

        viewModalCloseBtn.addEventListener('click', closeViewModal);
        viewModalEl.addEventListener('click', (e) => {
            if (e.target === viewModalEl) {
                closeViewModal();
            }
        });

        async function openViewModal(submissionId) {
            // Show modal and reset content
            viewModalEl.classList.add('active');
            viewModalEl.setAttribute('aria-hidden', 'false');
            viewModalTitle.textContent = 'Application Details';
            viewModalMeta.innerHTML = '';
            viewModalData.innerHTML = '';
            viewModalFiles.innerHTML = '';
            viewModalAppStatus.innerHTML = '';

            if (typeof showLoader === 'function') showLoader();
            try {
                const res = await fetch(`../api/get_submission_details.php?submission_id=${submissionId}`, {
                    headers: {
                        'Accept': 'application/json'
                    }
                });
                const payload = await res.json();

                if (!payload.success) {
                    viewModalData.innerHTML = `<div class="kv_item"><div class="kv_key">Error</div><div class="kv_value">${payload.message || 'Unable to load details.'}</div></div>`;
                    return;
                }

                const {
                    submission,
                    data_fields,
                    files
                } = payload.data || {};
                if (submission) {
                    // Header title
                    viewModalTitle.textContent = `${submission.type || 'Application'} • ${submission.academic_year || ''}`.trim();

                    // Meta chips
                    const statusColor = submission.status_color || '#64748b';
                    const badgeBg = `${statusColor}20`;
                    const chips = [];
                    chips.push(`<span class="meta_chip">Academic Year: ${submission.academic_year || 'N/A'}</span>`);
                    chips.push(`<span class="meta_chip">Type: ${submission.type || 'N/A'}</span>`);
                    if (submission.submitted_at) chips.push(`<span class="meta_chip">Submitted: ${submission.submitted_at}</span>`);
                    viewModalMeta.innerHTML = chips.join('');

                    // Application Status section (Status and Remarks)
                    const statusText = submission.status || 'N/A';
                    const remarksText = submission.remarks || 'None';
                    const applicantNumText = submission.applicant_number || 'N/A';
                    viewModalAppStatus.innerHTML = `
                        <div class="kv_item"><div class="kv_key">Exam Permit #</div><div class="kv_value">${applicantNumText}</div></div>
                        <div class="kv_item"><div class="kv_key">Status</div><div class="kv_value"><span class="status_chip" style="background:${badgeBg}; color:${statusColor}">${submission.status || 'N/A'}</span></div></div>
                        <div class="kv_item"><div class="kv_key">Remarks</div><div class="kv_value">${remarksText}</div></div>
                    `;
                }

                // Data fields
                if (Array.isArray(data_fields) && data_fields.length > 0) {
                    viewModalData.innerHTML = data_fields.map(df => {
                        const key = (df.field_name || '').replace(/_/g, ' ');
                        const val = df.field_value || '';
                        return `<div class="kv_item"><div class="kv_key">${key}</div><div class="kv_value">${val}</div></div>`;
                    }).join('');
                } else {
                    viewModalData.innerHTML = '<div class="kv_item"><div class="kv_key">No data</div><div class="kv_value">No submitted fields found.</div></div>';
                }

                // Files
                if (Array.isArray(files) && files.length > 0) {
                    viewModalFiles.innerHTML = files.map(f => {
                        const name = f.original_filename || f.field_name || 'File';
                        const path = f.file_path || '';
                        // Make link relative to pages dir if path is root-relative
                        const href = path ? (path.startsWith('http') ? path : `../${path}`) : '#';
                        const safeHref = href;
                        const lowerName = (name || '').toLowerCase();
                        const lowerPath = (path || '').toLowerCase();
                        const extMatch = lowerName.match(/\.([a-z0-9]+)$/) || lowerPath.match(/\.([a-z0-9]+)(?:[?#]|$)/);
                        const ext = extMatch ? extMatch[1] : '';
                        const previewable = [
                            // Images
                            'jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg', 'ico',
                            // Documents
                            'pdf', 'txt', 'md', 'csv',
                            // Web/code
                            'html', 'htm', 'css', 'js', 'xml', 'json',
                            // Audio/video (browser-dependent)
                            'mp3', 'wav', 'ogg', 'mp4', 'webm', 'ogv'
                        ].includes(ext);
                        const actionHtml = previewable ?
                            `<button class="btn btn_primary" data-preview-url="${safeHref}" data-preview-name="${name}">Preview</button>` :
                            `<a class="btn btn_primary" href="${safeHref}" download>Download</a>`;
                        return `<div class="file_item">
                                    <div class="file_name">${name}</div>
                                    <div class="file_actions">
                                        ${actionHtml}
                                    </div>
                                </div>`;
                    }).join('');

                    // Attach preview handlers
                    viewModalFiles.querySelectorAll('button[data-preview-url]').forEach(btn => {
                        btn.addEventListener('click', () => {
                            const url = btn.getAttribute('data-preview-url');
                            const name = btn.getAttribute('data-preview-name') || 'File';
                            openPreviewModal(url, name);
                        });
                    });
                } else {
                    viewModalFiles.innerHTML = '<div class="file_item"><div class="file_name">No files</div><div class="file_actions"></div></div>';
                }
            } catch (err) {
                viewModalData.innerHTML = `<div class="kv_item"><div class="kv_key">Error</div><div class="kv_value">${err.message || 'Unexpected error.'}</div></div>`;
            } finally {
                if (typeof hideLoader === 'function') hideLoader();
            }
        }


        // Initialize pagination
        function initializePagination() {
            // Entries per page selector
            document.getElementById('entriesPerPage').addEventListener('change', function() {
                entriesPerPage = parseInt(this.value);
                currentPage = 1;
                renderTable();
                updatePaginationControls();
            });

            // Previous page button
            document.getElementById('prevPage').addEventListener('click', () => {
                if (currentPage > 1) {
                    goToPage(currentPage - 1);
                }
            });

            // Next page button
            document.getElementById('nextPage').addEventListener('click', () => {
                const totalPages = Math.ceil(filteredData.length / entriesPerPage);
                if (currentPage < totalPages) {
                    goToPage(currentPage + 1);
                }
            });

            // Initial render
            renderTable();
            updatePaginationControls();
        }

        // Combined filtering function that handles both search and filters
        function applyAllFilters() {
            const searchInputEl = document.getElementById('searchInput');
            const roleFilterEl = document.getElementById('roleFilter');
            const statusFilterEl = document.getElementById('statusFilter');
            const emailFilterEl = document.getElementById('emailFilter');

            const searchTerm = ((searchInputEl && searchInputEl.value) || '').toLowerCase();
            const roleFilter = ((roleFilterEl && roleFilterEl.value) || '').toLowerCase();
            const statusFilter = ((statusFilterEl && statusFilterEl.value) || '').toLowerCase();
            const emailFilter = ((emailFilterEl && emailFilterEl.value) || '').toLowerCase();

            filteredData = allData.filter(app => {
                // Apply search filter across NO, TYPE, ACADEMIC YEAR
                const matchesSearch = searchTerm === '' ||
                    app.type.toLowerCase().includes(searchTerm) ||
                    app.academicYear.toLowerCase().includes(searchTerm) ||
                    app.no.toString().includes(searchTerm);

                // No secondary filters applied for now to avoid mismatches
                const matchesRole = true;
                const matchesStatus = true;
                const matchesEmail = true;

                return matchesSearch && matchesRole && matchesStatus && matchesEmail;
            });

            // Apply sorting after filtering
            applySort();

            currentPage = 1;
            renderTable();
            updatePaginationControls();

            // Show/hide clear filter button based on active filters
            const hasActiveFilters = roleFilter || statusFilter || emailFilter;
            const clearFilterBtn = document.getElementById('clearFilterBtn');
            if (clearFilterBtn) {
                clearFilterBtn.style.display = hasActiveFilters ? 'flex' : 'none';
            }
        }

        // Update search functionality to work with pagination
        function updateSearchFunctionality() {
            const searchInput = document.getElementById('searchInput');
            const searchIcon = document.querySelector('.search_icon');

            // Live search on typing
            searchInput.addEventListener('input', function() {
                applyAllFilters();
            });

            // Trigger search on Enter key
            searchInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    applyAllFilters();
                }
            });

            // Click on icon triggers search and focuses input
            if (searchIcon) {
                searchIcon.addEventListener('click', function() {
                    searchInput.focus();
                    applyAllFilters();
                });
                // Keyboard accessibility for icon
                searchIcon.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        searchInput.focus();
                        applyAllFilters();
                    }
                });
            }
        }

        // Initialize everything when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            initializePagination();
            updateSearchFunctionality();
            initializeSorting();
            loadApplications();

            // Delegate click events for View buttons once at startup
            const tableBody = document.querySelector('.table_body');
            tableBody.addEventListener('click', (evt) => {
                const viewBtn = evt.target.closest('.action_view');
                if (viewBtn) {
                    const sid = viewBtn.getAttribute('data-submission-id');
                    if (sid) {
                        openViewModal(parseInt(sid, 10));
                    }
                }
            });

            // Delegate click events for Edit buttons
            tableBody.addEventListener('click', (evt) => {
                const editBtn = evt.target.closest('.action_edit');
                if (editBtn) {
                    const isDisabled = editBtn.getAttribute('aria-disabled') === 'true' || editBtn.getAttribute('data-disabled') === 'true';
                    if (isDisabled) {
                        // Show modal message when edits are locked
                        if (typeof messageModalV1Show === 'function') {
                            messageModalV1Show({
                                icon: `<svg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke-width='1.5' stroke='currentColor' width='28' height='28'><path stroke-linecap='round' stroke-linejoin='round' d='M11.25 9V5.25m0 0L9 7.5m2.25-2.25l2.25 2.25M12 9v6m0 0H6m6 0h6' /></svg>`,
                                iconBg: '#f3f4f6',
                                actionBtnBg: '#2563eb',
                                showCancelBtn: false,
                                title: 'Editing Locked',
                                message: 'You cannot edit this submission at this time.',
                                cancelText: 'Cancel',
                                actionText: 'OK',
                                onConfirm: () => {}
                            });
                        } else {
                            alert('Editing is locked for this submission.');
                        }
                        return;
                    }
                    const sid = editBtn.getAttribute('data-submission-id');
                    if (sid) {
                        openEditModal(parseInt(sid, 10));
                    }
                }
            });

            // Add event listener for clear all filters button in empty state
            const clearAllFiltersBtn = document.getElementById('clearAllFiltersBtn');
            if (clearAllFiltersBtn) {
                clearAllFiltersBtn.addEventListener('click', function() {
                    // Clear all filter inputs if present
                    const roleEl = document.getElementById('roleFilter');
                    const statusEl = document.getElementById('statusFilter');
                    const emailEl = document.getElementById('emailFilter');
                    if (roleEl) roleEl.value = '';
                    if (statusEl) statusEl.value = '';
                    if (emailEl) emailEl.value = '';

                    // Clear search input
                    const searchEl = document.getElementById('searchInput');
                    if (searchEl) searchEl.value = '';

                    // Apply filters (which will reset to show all data)
                    applyAllFilters();
                });
            }
        });

        // === Edit Modal Logic ===
        const editModalEl = document.getElementById('editSubmissionModal');
        const editModalCloseBtn = document.getElementById('editModalClose');
        const editModalMeta = document.getElementById('editModalMeta');
        const editModalTitle = document.getElementById('editModalTitle');
        const editModalDataFields = document.getElementById('editModalDataFields');
        const editModalFiles = document.getElementById('editModalFiles');
        const editSubmissionForm = document.getElementById('editSubmissionForm');
        let currentEditSubmissionId = null;
        let originalDataMap = {};
        let originalFilesByUrl = {};
        let pendingFileReplacements = [];

        function closeEditModal() {
            editModalEl.classList.remove('active');
            editModalEl.setAttribute('aria-hidden', 'true');
            currentEditSubmissionId = null;
            originalDataMap = {};
            originalFilesByUrl = {};
            pendingFileReplacements = [];
        }

        editModalCloseBtn.addEventListener('click', closeEditModal);
        editModalEl.addEventListener('click', (e) => {
            if (e.target === editModalEl) {
                closeEditModal();
            }
        });

        async function openEditModal(submissionId) {
            currentEditSubmissionId = submissionId;
            // Show modal and reset content
            editModalEl.classList.add('active');
            editModalEl.setAttribute('aria-hidden', 'false');
            editModalTitle.textContent = 'Edit Submission';
            editModalMeta.innerHTML = '';
            editModalDataFields.innerHTML = '';
            editModalFiles.innerHTML = '';
            pendingFileReplacements = [];

            if (typeof showLoader === 'function') showLoader();
            try {
                const res = await fetch(`../api/get_submission_details.php?submission_id=${submissionId}`, {
                    headers: {
                        'Accept': 'application/json'
                    }
                });
                const payload = await res.json();
                if (!payload.success) {
                    editModalDataFields.innerHTML = `<div class="kv_item"><div class="kv_key">Error</div><div class="kv_value">${payload.message || 'Unable to load details.'}</div></div>`;
                    return;
                }

                const {
                    submission,
                    data_fields,
                    files
                } = payload.data || {};
                if (submission) {
                    // Header title and meta chips
                    editModalTitle.textContent = `${submission.type || 'Application'} • ${submission.academic_year || ''}`.trim();
                    const statusColor = submission.status_color || '#64748b';
                    const badgeBg = `${statusColor}20`;
                    const chips = [];
                    chips.push(`<span class="meta_chip">Academic Year: ${submission.academic_year || 'N/A'}</span>`);
                    chips.push(`<span class="meta_chip">Type: ${submission.type || 'N/A'}</span>`);
                    chips.push(`<span class="status_chip" style="background:${badgeBg}; color:${statusColor}">${submission.status || 'N/A'}</span>`);
                    editModalMeta.innerHTML = chips.join('');
                }

                // Build inputs for data_fields
                originalDataMap = {};
                if (Array.isArray(data_fields) && data_fields.length > 0) {
                    editModalDataFields.innerHTML = data_fields.map(df => {
                        const keyRaw = df.field_name || '';
                        const keyLabel = keyRaw.replace(/_/g, ' ');
                        const val = df.field_value || '';
                        originalDataMap[keyRaw] = val;
                        return `<div class="kv_item">
                                    <label class="kv_key" for="field_${keyRaw}">${keyLabel}</label>
                                    <input id="field_${keyRaw}" name="${keyRaw}" class="kv_value_input" type="text" value="${val}">
                                </div>`;
                    }).join('');
                } else {
                    editModalDataFields.innerHTML = '<div class="kv_item"><div class="kv_key">No data</div><div class="kv_value">No submitted fields found.</div></div>';
                }

                // Build file replace UI
                if (Array.isArray(files) && files.length > 0) {
                    editModalFiles.innerHTML = files.map((f, idx) => {
                        const name = f.original_filename || f.field_name || `File ${idx+1}`;
                        const path = f.file_path || '';
                        const safePath = path ? (path.startsWith('http') ? path : `../${path}`) : '';
                        if (path) {
                            originalFilesByUrl[path] = {
                                name: name,
                                fieldName: f.field_name || ''
                            };
                        }
                        const lowerName = (name || '').toLowerCase();
                        const lowerPath = (path || '').toLowerCase();
                        const extMatch = lowerName.match(/\.([a-z0-9]+)$/) || lowerPath.match(/\.([a-z0-9]+)(?:[?#]|$)/);
                        const ext = extMatch ? extMatch[1] : '';
                        const previewable = [
                            'jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg', 'ico',
                            'pdf', 'txt', 'md', 'csv',
                            'html', 'htm', 'css', 'js', 'xml', 'json',
                            'mp3', 'wav', 'ogg', 'mp4', 'webm', 'ogv'
                        ].includes(ext);
                        const actionHtml = previewable ?
                            `<button type="button" class="btn btn_secondary preview_existing" data-preview-url="${safePath}" ${safePath ? '' : 'disabled'}>Preview</button>` :
                            `<a class="btn btn_secondary" href="${safePath}" download ${safePath ? '' : 'disabled'}>Download</a>`;
                        return `<div class="file_item">
                                    <div class="file_name">${name}</div>
                                    <div class="file_actions">
                                        <input type="file" class="file_replace_input" data-file-url="${path}" data-field-name="${f.field_name || ''}" accept="*/*">
                                        ${actionHtml}
                                    </div>
                                </div>`;
                    }).join('');

                    // Preview existing
                    editModalFiles.querySelectorAll('.preview_existing').forEach(btn => {
                        btn.addEventListener('click', () => {
                            const url = btn.getAttribute('data-preview-url');
                            if (url && url !== '#') openPreviewModal(url, 'Current File');
                        });
                    });

                    // Replace handler: queue for processing on Save
                    editModalFiles.querySelectorAll('.file_replace_input').forEach(input => {
                        input.addEventListener('change', () => {
                            const file = input.files && input.files[0];
                            const url = input.getAttribute('data-file-url');
                            const fieldName = input.getAttribute('data-field-name') || '';
                            if (!file || !url) return;
                            // Remove any existing queued replacement for this url, then queue new one
                            pendingFileReplacements = pendingFileReplacements.filter(r => r.url !== url);
                            pendingFileReplacements.push({
                                url,
                                file,
                                fieldName
                            });

                            // UI hint: mark as pending and show tag
                            const parent = input.closest('.file_item');
                            if (parent) {
                                parent.classList.add('pending');
                                const actions = parent.querySelector('.file_actions');
                                let tag = parent.querySelector('.pending_tag');
                            }
                        });
                    });
                } else {
                    editModalFiles.innerHTML = '<div class="file_item"><div class="file_name">No files</div><div class="file_actions"></div></div>';
                }
            } catch (err) {
                editModalDataFields.innerHTML = `<div class="kv_item"><div class="kv_key">Error</div><div class="kv_value">${err.message || 'Unexpected error.'}</div></div>`;
            } finally {
                if (typeof hideLoader === 'function') hideLoader();
            }
        }

        // Save data changes
        editSubmissionForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            if (!currentEditSubmissionId) return;
            const formData = new FormData(editSubmissionForm);
            const changes = [];
            for (const [key, value] of formData.entries()) {
                if (!(key in originalDataMap) || originalDataMap[key] !== value) {
                    changes.push({
                        field_name: key,
                        field_value: value
                    });
                }
            }
            if (changes.length === 0 && (!pendingFileReplacements || pendingFileReplacements.length === 0)) {
                // Inform user with modal (no changes)
                if (typeof messageModalV1Show === 'function') {
                    messageModalV1Show({
                        icon: `<svg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke-width='1.5' stroke='currentColor' width='28' height='28'><path stroke-linecap='round' stroke-linejoin='round' d='M11.25 9V5.25m0 0L9 7.5m2.25-2.25l2.25 2.25M12 9v6m0 0H6m6 0h6' /></svg>`,
                        iconBg: '#f3f4f6',
                        actionBtnBg: '#2563eb',
                        showCancelBtn: false,
                        title: 'No Changes',
                        message: 'You haven\'t modified any fields or files.',
                        cancelText: 'Cancel',
                        actionText: 'OK',
                        onConfirm: () => {}
                    });
                } else {
                    alert('No changes to save.');
                }
                return;
            }

            const saveBtn = document.getElementById('saveDataBtn');
            const originalBtnText = saveBtn ? saveBtn.textContent : '';
            try {
                // Show loader and disable UI while saving
                if (typeof showLoader === 'function') showLoader();
                if (saveBtn) {
                    saveBtn.disabled = true;
                    saveBtn.textContent = 'Saving...';
                }
                // Perform API requests for data fields and file replacements
                const ops = [];

                // Update submission data fields
                if (changes.length > 0) {
                    ops.push(
                        fetch('../api/update_submission_data.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                submission_id: currentEditSubmissionId,
                                fields: changes
                            })
                        }).then(r => r.json()).catch(err => ({
                            success: false,
                            message: err && err.message ? err.message : 'Network error'
                        }))
                    );
                }

                // Replace uploaded files via proxy
                const REQUIREMENTS_BASE = '<?php echo addslashes($UPLOAD_REQUIREMENTS_BASE_URL ?? ""); ?>';
                const fileResultsMap = new Map();
                (pendingFileReplacements || []).forEach(rep => {
                    const form = new FormData();
                    // Build full URL for proxy if original is relative
                    let urlForProxy = rep.url || '';
                    if (urlForProxy && !/^https?:\/\//i.test(urlForProxy)) {
                        const base = REQUIREMENTS_BASE || '';
                        if (base) {
                            urlForProxy = base.replace(/\/+$/, '') + '/' + urlForProxy.replace(/^\/+/, '');
                        }
                    }
                    form.append('url', urlForProxy);
                    form.append('file', rep.file);
                    // Pass submission_id so backend can lock edits (can_update = 0)
                    if (typeof currentEditSubmissionId !== 'undefined' && currentEditSubmissionId) {
                        form.append('submission_id', String(currentEditSubmissionId));
                    }
                    ops.push(
                        fetch('../api/update_requirement_proxy.php', {
                            method: 'POST',
                            body: form
                        }).then(r => r.json()).then(json => {
                            fileResultsMap.set(rep.url, json);
                            return json;
                        }).catch(err => ({
                            success: false,
                            message: err && err.message ? err.message : 'Network error'
                        }))
                    );
                });

                const results = await Promise.all(ops);
                const dataRes = results.find(r => r && (r.updated !== undefined || r.message === 'Data updated')) || null;
                const fileResList = results.filter(r => r && r.db_updates);
                const fieldsUpdated = dataRes && typeof dataRes.updated === 'number' ? dataRes.updated : 0;
                const dbRowsChanged = fileResList.reduce((acc, r) => acc + ((r.db_updates && (r.db_updates.requirements_uploads_updated || 0)) + (r.db_updates && (r.db_updates.submission_files_updated || 0))), 0);

                // Determine successes and compose detailed messages
                const hasFieldChanges = (changes && changes.length > 0);
                const hasFileChanges = (pendingFileReplacements && pendingFileReplacements.length > 0);
                const fieldsSucceeded = hasFieldChanges ? (dataRes && dataRes.success === true) : true;
                const filesSucceededCount = (pendingFileReplacements || []).reduce((count, rep) => {
                    const res = fileResultsMap.get(rep.url);
                    return count + (res && res.success === true ? 1 : 0);
                }, 0);
                const filesTotal = (pendingFileReplacements || []).length;
                const filesSucceeded = hasFileChanges ? (filesSucceededCount === filesTotal) : true;
                const overallFailure = !(fieldsSucceeded && filesSucceeded);
                const title = overallFailure ? 'Update Failed' : 'Update Success';

                // Build structured changes
                const fieldChanges = (changes || []).map(c => ({
                    name: c.field_name,
                    old: (originalDataMap && originalDataMap[c.field_name]) || '',
                    neu: c.field_value
                }));
                const fileChanges = (pendingFileReplacements || []).map(rep => ({
                    fieldName: rep.fieldName,
                    old: (originalFilesByUrl[rep.url] && originalFilesByUrl[rep.url].name) || (rep.url || '').split('/').pop(),
                    neu: (rep.file && rep.file.name) || ''
                }));

                let primaryMsg = '';
                const verb = overallFailure ? 'Failed to update' : 'Successfully updated';
                if (hasFieldChanges && hasFileChanges) {
                    const f0 = fieldChanges[0] || {
                        old: '',
                        neu: ''
                    };
                    const fl0 = fileChanges[0] || {
                        old: '',
                        neu: ''
                    };
                    primaryMsg = `${verb} the ${f0.old} - ${fl0.old} to ${f0.neu} - ${fl0.neu}`;
                } else if (hasFieldChanges && !hasFileChanges) {
                    const f0 = fieldChanges[0] || {
                        old: '',
                        neu: ''
                    };
                    primaryMsg = `${verb} the ${f0.old} to ${f0.neu}`;
                } else if (!hasFieldChanges && hasFileChanges) {
                    const fl0 = fileChanges[0] || {
                        old: '',
                        neu: ''
                    };
                    primaryMsg = `${verb} the ${fl0.old} to ${fl0.neu}`;
                } else {
                    primaryMsg = overallFailure ? 'Failed to update.' : 'No changes applied.';
                }

                // Additional lines for multiple changes
                const extraLines = [];
                if (fieldChanges.length > 1) {
                    for (let i = 1; i < fieldChanges.length; i++) {
                        const f = fieldChanges[i];
                        extraLines.push(`Field: ${f.old} → ${f.neu}`);
                    }
                }
                if (fileChanges.length > 1) {
                    for (let i = 1; i < fileChanges.length; i++) {
                        const fl = fileChanges[i];
                        extraLines.push(`File: ${fl.old} → ${fl.neu}`);
                    }
                }
                const msg = [primaryMsg, ...extraLines].filter(Boolean).join('<br>');

                if (typeof messageModalV1Show === 'function') {
                    messageModalV1Show({
                        icon: `<svg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke-width='1.5' stroke='currentColor' width='28' height='28'><path stroke-linecap='round' stroke-linejoin='round' d='M4.5 12.75l6 6 9-13.5' /></svg>`,
                        iconBg: overallFailure ? '#fef3c7' : '#dcfce7',
                        actionBtnBg: overallFailure ? '#f59e0b' : '#16a34a',
                        showCancelBtn: false,
                        title,
                        message: msg,
                        cancelText: 'Cancel',
                        actionText: 'OK',
                        onConfirm: () => {
                            pendingFileReplacements = [];
                            closeEditModal();
                            try {
                                window.location.reload();
                            } catch (_) {}
                        }
                    });
                } else {
                    alert(msg);
                    pendingFileReplacements = [];
                    closeEditModal();
                    try {
                        window.location.reload();
                    } catch (_) {}
                }
            } catch (err) {
                // Exception modal
                if (typeof messageModalV1Show === 'function') {
                    messageModalV1Show({
                        icon: `<svg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke-width='1.5' stroke='currentColor' width='28' height='28'><path stroke-linecap='round' stroke-linejoin='round' d='M6 18L18 6M6 6l12 12' /></svg>`,
                        iconBg: '#fee2e2',
                        actionBtnBg: '#dc2626',
                        showCancelBtn: false,
                        title: 'Unexpected Error',
                        message: 'An error occurred: ' + (err && err.message ? err.message : 'Unknown'),
                        cancelText: 'Cancel',
                        actionText: 'Close',
                        onConfirm: () => {}
                    });
                } else {
                    alert('Unexpected error: ' + err.message);
                }
            } finally {
                // Restore UI and hide loader
                if (saveBtn) {
                    saveBtn.disabled = false;
                    saveBtn.textContent = originalBtnText || 'Save Changes';
                }
                if (typeof hideLoader === 'function') hideLoader();
            }
        });

        // Preview modal logic for files
        const previewModalEl = document.getElementById('filePreviewModal');
        const previewModalCloseBtn = document.getElementById('filePreviewClose');
        const previewModalBody = document.getElementById('previewModalBody');
        const previewModalTitle = document.getElementById('previewModalTitle');

        function closePreviewModal() {
            if (previewModalBody && previewModalBody._blobUrl) {
                try {
                    URL.revokeObjectURL(previewModalBody._blobUrl);
                } catch (_) {}
                previewModalBody._blobUrl = null;
            }
            previewModalEl.classList.remove('active');
            previewModalEl.setAttribute('aria-hidden', 'true');
            previewModalBody.innerHTML = '';
        }

        previewModalCloseBtn.addEventListener('click', closePreviewModal);
        previewModalEl.addEventListener('click', (e) => {
            if (e.target === previewModalEl) {
                closePreviewModal();
            }
        });
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && previewModalEl.classList.contains('active')) {
                closePreviewModal();
            }
        });

        function openPreviewModal(fileUrl, name) {
            // Open modal
            previewModalEl.classList.add('active');
            previewModalEl.setAttribute('aria-hidden', 'false');
            previewModalTitle.textContent = `File Preview • ${name}`;
            previewModalBody.innerHTML = '<span class="preview-loading">Loading preview...</span>';

            // Ensure absolute URL for proxy
            let absoluteUrl = fileUrl;
            try {
                absoluteUrl = new URL(fileUrl, window.location.href).href;
            } catch (_) {}

            const proxyUrl = `../api/preview_requirement_proxy.php?url=${encodeURIComponent(absoluteUrl)}`;
            fetch(proxyUrl)
                .then(async (res) => {
                    if (!res.ok) {
                        throw new Error(`Unable to preview file. (${res.status})`);
                    }

                    const ct = (res.headers.get('Content-Type') || '').toLowerCase();
                    const blob = await res.blob();
                    previewModalBody.innerHTML = '';

                    // Derive extension for extra hints
                    let ext = '';
                    try {
                        const urlLower = (fileUrl || '').toLowerCase();
                        const nameLower = (name || '').toLowerCase();
                        const m = nameLower.match(/\.([a-z0-9]+)$/) || urlLower.match(/\.([a-z0-9]+)(?:[?#]|$)/);
                        ext = m ? m[1] : '';
                    } catch (_) {}

                    // Helper to attach text preview
                    const renderText = async () => {
                        const text = await blob.text();
                        const pre = document.createElement('pre');
                        pre.className = 'preview-text';
                        pre.textContent = text;
                        previewModalBody.appendChild(pre);
                    };

                    // Images (including svg/ico)
                    if (ct.startsWith('image/') || ext === 'svg' || ext === 'ico') {
                        const blobUrl = URL.createObjectURL(blob);
                        previewModalBody._blobUrl = blobUrl;
                        const img = document.createElement('img');
                        img.src = blobUrl;
                        img.alt = name || 'preview';
                        previewModalBody.appendChild(img);
                        return;
                    }

                    // PDF
                    if (ct.includes('pdf') || ext === 'pdf') {
                        const blobUrl = URL.createObjectURL(blob);
                        previewModalBody._blobUrl = blobUrl;
                        const object = document.createElement('object');
                        object.data = blobUrl;
                        object.type = 'application/pdf';
                        previewModalBody.appendChild(object);
                        return;
                    }

                    // Audio
                    if (ct.startsWith('audio/') || ['mp3', 'wav', 'ogg'].includes(ext)) {
                        const blobUrl = URL.createObjectURL(blob);
                        previewModalBody._blobUrl = blobUrl;
                        const audio = document.createElement('audio');
                        audio.controls = true;
                        audio.src = blobUrl;
                        audio.style.width = '100%';
                        previewModalBody.appendChild(audio);
                        return;
                    }

                    // Video
                    if (ct.startsWith('video/') || ['mp4', 'webm', 'ogv', 'ogg'].includes(ext)) {
                        const blobUrl = URL.createObjectURL(blob);
                        previewModalBody._blobUrl = blobUrl;
                        const video = document.createElement('video');
                        video.controls = true;
                        video.src = blobUrl;
                        video.style.width = '100%';
                        video.style.maxHeight = '80vh';
                        previewModalBody.appendChild(video);
                        return;
                    }

                    // Plain text, JSON, XML, code, CSV, Markdown
                    if (ct.startsWith('text/') || ct.includes('json') || ct.includes('xml') || ['json', 'xml', 'md', 'csv', 'txt', 'css', 'js'].includes(ext)) {
                        await renderText();
                        return;
                    }

                    // Default: HTML and unknown types → iframe
                    const blobUrl = URL.createObjectURL(blob);
                    previewModalBody._blobUrl = blobUrl;
                    const iframe = document.createElement('iframe');
                    iframe.src = blobUrl;
                    iframe.setAttribute('title', name || 'File preview');
                    previewModalBody.appendChild(iframe);
                })
                .catch(err => {
                    previewModalBody.innerHTML = `<span class="preview-error">${err.message || 'Failed to load preview.'}</span>`;
                    // Offer direct download when preview fails
                    const dl = document.createElement('a');
                    dl.href = fileUrl;
                    dl.download = name || 'download';
                    dl.className = 'btn btn_primary';
                    dl.style.marginTop = '12px';
                    dl.textContent = 'Download file';
                    previewModalBody.appendChild(dl);
                });
        }

        // Add notification animations to head
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideInRight {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
            @keyframes slideOutRight {
                from { transform: translateX(0); opacity: 1; }
                to { transform: translateX(100%); opacity: 0; }
            }
        `;
        document.head.appendChild(style);
    </script>

    <?php include "includes/profile_modal.php"; ?>
    <?php include "includes/support_floating.php"; ?>
    <script>
        let isProfileSet = <?php echo $isProfileSet ? 'true' : 'false'; ?>;
        let first_name = "<?php echo $first_name; ?>";
        let middle_name = "<?php echo $middle_name; ?>";
        let last_name = "<?php echo $last_name; ?>";
        let suffix = "<?php echo $suffix; ?>";
    </script>
    <script src="../pages/src/js/profile_info.js"></script>
</body>

</html>