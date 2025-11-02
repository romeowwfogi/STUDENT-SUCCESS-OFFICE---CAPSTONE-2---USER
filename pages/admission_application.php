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
// Gate: check if user can apply. If a record exists and can_apply is false, restrict.
$gateResult = executeSelect($conn, "SELECT can_apply FROM admission_submission WHERE user_id = ?", "i", [$user_id]);
if ($gateResult['success'] && count($gateResult['data']) > 0) {
    $isRestrictedApply = ((int)($gateResult['data'][0]['can_apply'] ?? 1) === 0);
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

$formSteps = [];
$currentApplicantTypeId = $applicant_type_id; // Will be used in the hidden form field

// --- UPDATED SQL QUERY ---
// Loads form based on applicant_type_id and checks if it's active
$sql = "SELECT
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
            at.id = ? AND at.is_active = 1 AND at.is_archived = 0 -- <<< ADD THIS CHECK
        ORDER BY
            s.step_order, f.field_order, o.option_order";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $applicant_type_id);
$stmt->execute();
$result = $stmt->get_result();

// --- This logic builds the nested array ---
// --- This logic builds the nested array ---
$allFields = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $stepTitle = $row['step_title'];
        if (!isset($formSteps[$stepTitle])) {
            $formSteps[$stepTitle] = [];
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
                'options' => []
            ];
            $formSteps[$stepTitle][] = &$allFields[$field_id];
        }

        if ($row['option_id'] !== null) {
            $allFields[$field_id]['options'][] = [
                'label' => $row['option_label'],
                'value' => $row['option_value']
            ];
        }
    }
}
unset($allFields);

$stmt->close();

// --- ADD THIS BLOCK TO ENSURE A SUMMARY STEP ---
if (!empty($formSteps)) {
    // Get the title of the last step fetched from the DB
    $lastStepTitle = array_key_last($formSteps);

    // Check if the last step title *does not* contain "Summary"
    if (stripos($lastStepTitle, 'Summary') === false) {
        // If no summary step exists as the last one, add it manually
        $formSteps['Step ' . (count($formSteps) + 1) . ': Summary'] = []; // Add an empty step named Summary
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

    <style>
        :root {
            --primary-color: #007bff;
            /* Blue */
            --success-color: #28a745;
            /* Green */
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
            display: grid;
            place-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
            box-sizing: border-box;
        }

        .form-container {
            background: #fff;
            border-radius: var(--border-radius);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
            width: 100%;
            max-width: 700px;
            overflow: hidden;
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

        /* ----- Form ----- */
        #multiStepForm {
            padding: 30px;
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


        /* ----- Floating Label Fields ----- */
        .field-group {
            position: relative;
            margin-bottom: 25px;
        }

        .field-group input,
        .field-group select {
            width: 100%;
            padding: 18px 12px 6px 12px;
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

        .field-group:not(.field-group-file) label {
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

        /* ----- Button Navigation ----- */
        .button-group {
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
        }

        .btn {
            padding: 12px 25px;
            border: none;
            border-radius: var(--border-radius);
            font-family: 'Poppins', sans-serif;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: var(--primary-color);
            color: #fff;
        }

        .btn-primary:hover {
            background: #0056b3;
        }

        .btn-secondary {
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 10px;
            padding: 10px;
            width: 50px;
            height: 50px;
            border-radius: 10px;
        }

        .btn.hidden {
            display: none;
        }

        .top-actions {
            display: flex;
            justify-content: flex-start;
            margin-bottom: 20px;
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
            background: #fff8f8;
            border: 1px solid #f5c6cb;
            color: #721c24;
            border-radius: var(--border-radius);
        }
    </style>
</head>

<body>

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
                <a href="javascript:void(0);" class="btn-secondary" aria-label="Go Back" onclick="history.back()">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon lucide lucide-chevron-left" aria-hidden="true">
                        <path d="m15 18-6-6 6-6" />
                    </svg>
                </a>
            </div>

            <div class="step-indicator">
                <?php
                $stepCount = count($formSteps);
                $i = 1;
                foreach ($formSteps as $stepTitle => $fields):
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
                foreach ($formSteps as $stepTitle => $fields):

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

                        <?php if (stripos($stepTitle, 'Summary') !== false): ?>
                            <p>Please review your information before submitting:</p>
                            <div id="summary-content">
                            </div>
                        <?php else: ?>

                            <?php foreach ($fields as $field): ?>
                                <div class="field-group <?php echo ($field['input_type'] === 'file') ? 'field-group-file' : ''; ?>">

                                    <?php if ($field['input_type'] === 'select'): ?>
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
                                            <?php echo $field['required'] ? 'required' : ''; ?>>

                                    <?php else: ?>
                                        <input
                                            type="<?php echo e($field['input_type']); ?>"
                                            id="<?php echo e($field['name']); ?>"
                                            name="<?php echo e($field['name']); ?>"
                                            placeholder=" "
                                            <?php echo $field['required'] ? 'required' : ''; ?>>
                                    <?php endif; ?>

                                    <?php if ($field['input_type'] !== 'file'): ?>
                                        <label for="<?php echo e($field['name']); ?>">
                                            <?php echo e($field['label']); ?>
                                            <?php if ($field['required']): ?>
                                                <span class="required-asterisk">*</span>
                                            <?php endif; ?>
                                        </label>
                                    <?php endif; ?>

                                </div>
                            <?php endforeach; ?>

                        <?php endif; ?>

                    </div>
                <?php
                    $stepIndex++;
                endforeach;
                ?>

                <div class="button-group">
                    <button type="button" class="btn btn-secondary hidden" id="prevBtn">Previous</button>
                    <button type="button" class="btn btn-primary" id="nextBtn">Next</button>
                    <button type="submit" class="btn btn-primary hidden" id="submitBtn">Submit Application</button>
                </div>
            </form>
        <?php endif; ?>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let APPLICANT_BACKGROUND_URL = "<?php echo $APPLICANT_BACKGROUND_URL; ?>";
            let BODY_SELECTOR = document.querySelector('body');
            BODY_SELECTOR.style.backgroundImage = `url(${APPLICANT_BACKGROUND_URL})`;
            BODY_SELECTOR.style.backgroundSize = "cover";
            BODY_SELECTOR.style.backgroundPosition = "center";
            BODY_SELECTOR.style.backgroundRepeat = "no-repeat";

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
                const currentStepFields = steps[stepIndex].querySelectorAll('input[required], select[required]');
                let isValid = true;

                for (const field of currentStepFields) {
                    if (!field.checkValidity()) {
                        field.reportValidity();
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
</body>

</html>