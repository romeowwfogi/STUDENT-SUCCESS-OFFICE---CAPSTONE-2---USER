<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$sessionToken = $_SESSION['token'] ?? null;
if (!$sessionToken) {
    header("Location: ../login");
    exit;
}

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

include "connection/main_connection.php";
include "functions/select_sql.php";
include "functions/insert_sql.php";
include "functions/update_sql.php";
include "functions/en-de_crypt.php";
include "functions/send_email.php";
include "functions/greetings.php";
include "functions/config_msg.php";
include "functions/expiration_config.php";

// === DEBUG SETTINGS ===
date_default_timezone_set('Asia/Manila'); // Set your timezone

ini_set('display_errors', 0);              // Hide errors from browser (production-safe)
ini_set('log_errors', 1);                  // Enable error logging
ini_set('error_log', __DIR__ . '/php-error.log'); // Log errors to a file in this directory
error_reporting(E_ALL);                    // Report all errors

error_log("🚀 Error logging test triggered at " . date('Y-m-d H:i:s'));

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Oops! We couldn’t connect to the server. Please try again shortly."
    ]);
    exit;
}

$MESSAGE = getConfigValue(
    $conn,
    'REQUEST_METHOD_POST',
    "To interact with this endpoint, be sure to send a POST request — other methods aren’t supported."
);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(response_code: 405);
    echo json_encode(["success" => false, "message" => $MESSAGE]);
    exit;
}

// 📨 Read JSON input
$input = json_decode(file_get_contents("php://input"), true);

// Application Type
$applicationType = trim($input['applicationType'] ?? '');
$valid_types = ['OngoingG12', 'SHSGraduate', 'Transferee', 'ALSGraduate'];

if (
    empty($applicationType) ||
    !in_array($APPLICANT_TYPE, $valid_types, true)
) {
    $MESSAGE = getConfigValue(
        $conn,
        'INVALID_APPLICATION_TYPE',
        "The provided application type is invalid or not supported."
    );
    echo json_encode(["success" => false, "message" => $MESSAGE]);
    exit;
}

// Personal Information
$firstName = trim($input['firstName'] ?? '');
$middleName = trim($input['middleName'] ?? '');
$lastName = trim($input['lastName'] ?? '');
$suffix = trim($input['suffix'] ?? '');
$gender = trim($input['gender'] ?? '');
$birthday = trim($input['birthday'] ?? '');
$contactNumber = trim($input['contactNumber'] ?? '');

// Address Information
$houseNumber = trim($input['houseNumber'] ?? '');
$streetName = trim($input['streetName'] ?? '');
$subdivision = trim($input['subdivision'] ?? '');
$city = trim($input['city'] ?? '');
$barangay = trim($input['barangay'] ?? '');

// Document Files
$front_id_file = trim($input['front_id_file'] ?? '');
$back_id_file = trim($input['back_id_file'] ?? '');
$guardian_front_id_file = trim($input['guardian_front_id_file'] ?? '');
$guardian_back_id_file = trim($input['guardian_back_id_file'] ?? '');
$affidavit_file = trim($input['affidavit_file'] ?? '');
$guardianDeclaration = trim($input['guardianDeclaration'] ?? '');
$birth_certificate_file = trim($input['birth_certificate_file'] ?? '');
$form_document_file = trim($input['form_document_file'] ?? '');
$passport_pictures_file = trim($input['passport_pictures_file'] ?? '');

// Academic Information
$lrn = trim($input['lrn'] ?? '');
$lastSchoolAttended = trim($input['lastSchoolAttended'] ?? '');
$typeOfSchool = trim($input['type_of_school'] ?? '');

if (
    !str_contains($applicationType, 'Transferee')
) {
    $strandTrack = trim($input['strand_track'] ?? '');

    // Academic Averages
    $avg_fil = trim($input['avg_fil'] ?? '');
    $avg_eng = trim($input['avg_eng'] ?? '');
    $avg_math = trim($input['avg_math'] ?? '');
    $avg_scie = trim($input['avg_scie'] ?? '');
    $overall_avg = trim($input['overall_avg'] ?? '');
} else {
    $previousYear = trim($input['prev_year'] ?? '');
    $previousCourse = trim($input['prev_course'] ?? '');
    $tor_file = trim($input['tor_file'] ?? '');
}

// Program Choices
$first_choice_program = trim($input['first_choice_program'] ?? '');
$second_choice_program = trim($input['second_choice_program'] ?? '');

// Legacy field mappings for backward compatibility
$dob = $birthday; // Map birthday to legacy dob field
$house_unit_building_num = $houseNumber; // Map houseNumber to legacy field
$street_name = $streetName; // Already matches
$subdivision_village = $subdivision; // Map subdivision to legacy field
$city_municipality = $city; // Map city to legacy field
$ApplicantFrontID = $front_id_file; // Map to legacy field names
$ApplicantBackID = $back_id_file;
$ParentGuardianFrontID = $guardian_front_id_file;
$ParentGuardianBackID = $guardian_back_id_file;
$AffidavitofGuardianship = $affidavit_file;
$PSA = $birth_certificate_file;


// 🔍 Validate required fields
$required_fields = [
    'firstName' => $firstName,
    'lastName' => $lastName,
    'gender' => $gender,
    'birthday' => $birthday,
    'contactNumber' => $contactNumber,
    'houseNumber' => $houseNumber,
    'streetName' => $streetName,
    'city' => $city,
    'barangay' => $barangay
];

// Add email for non-transferee applications
if (
    !str_contains($applicationType, 'Transferee')
) {
    $required_fields['email'] = $email;
} else {
}

// Add TOR, previous year and course for transferee applications
if (
    str_contains($applicationType, 'Transferee')
) {
    $required_fields['tor_file'] = $tor_file;
    $required_fields['prev_year'] = $previousYear;
    $required_fields['prev_course'] = $previousCourse;
} else {
}

// Add Notarized Affidavit of Guardianship for parent/guardian applications
if (
    !empty($guardianDeclaration) && $guardianDeclaration === 'true'
) {
    $required_fields['affidavit_file'] = $affidavit_file;
} else {
}

// Add grades for SHS applications
if (
    str_contains($applicationType, 'SHSGraduate') || str_contains($applicationType, 'OngoingG12')
) {
    $required_fields['avg_fil'] = $avg_fil;
    $required_fields['avg_eng'] = $avg_eng;
    $required_fields['avg_math'] = $avg_math;
    $required_fields['avg_scie'] = $avg_scie;
    $required_fields['overall_avg'] = $overall_avg;
} else {
}

$missing_fields = [];
foreach ($required_fields as $field_name => $field_value) {
    if (empty($field_value)) {
        $missing_fields[] = $field_name;
    }
}

if (!empty($missing_fields)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Missing required fields: ' . implode(', ', $missing_fields)
    ]);
    exit;
}

// Get user ID from session
$ACCOUNT_ID = base64_decode($_SESSION['user_id'] ?? '');
if (!$ACCOUNT_ID) {
    http_response_code(401);
    echo json_encode([
        "success" => false,
        "message" => "User not authenticated"
    ]);
    exit;
}

// Gate: check if user is allowed to apply
$gateSql = "SELECT can_apply FROM admission_submission WHERE user_id = ?";
$gateTypes = "i";
$gateParams = [$ACCOUNT_ID];
$gateResult = executeSelect($conn, $gateSql, $gateTypes, $gateParams);
if ($gateResult['success'] && count($gateResult['data']) > 0) {
    $canApply = (int)($gateResult['data'][0]['can_apply'] ?? 1) === 1;
    if (!$canApply) {
        http_response_code(403);
        echo json_encode([
            "success" => false,
            "message" => "You cannot apply at this time."
        ]);
        exit;
    }
}

$conn->begin_transaction();
try {
    // Insert into applicant_general
    $sql1 = "INSERT INTO applicant_general (user_id, application_type, lrn_number, sex, contact_number, birthday, type_of_school, last_school_attended) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $types1 = "isssssss";
    $params1 = [$ACCOUNT_ID, $applicationType, $lrn, $gender, $contactNumber, $birthday, $schoolType, $lastSchoolAttended];
    $result1 = executeInsert($conn, $sql1, $types1, $params1);

    if (!$result1['success']) {
        throw new Exception($result1['message']);
    }

    $applicant_id = $result1['insert_id'];

    // Insert into applicant_address
    $sql2 = "INSERT INTO applicant_address (user_id, house_unit_building_num, street_name, subdivision_village, city_municipality, barangay) 
                 VALUES (?, ?, ?, ?, ?, ?)";
    $types2 = "isssss";
    $params2 = [$ACCOUNT_ID, $houseNumber, $streetName, $subdivision, $city, $barangay];
    $result2 = executeInsert($conn, $sql2, $types2, $params2);

    if (!$result2['success']) {
        throw new Exception($result2['message']);
    }

    // Insert into applicant_documents
    $sql3 = "INSERT INTO applicant_documents (applicant_id, form_138, form_137, psa_birth_certificate, id_applicant_front, id_applicant_back, id_parents_front, id_parents_back, passport_picture_1, passport_picture_2, affidavit_guardianship) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $types3 = "issssssssss";
    $params3 = [$applicant_id, $form138, $form137, $psaBirthCertificate, $idApplicantFront, $idApplicantBack, $idParentsFront, $idParentsBack, $passportPicture1, $passportPicture2, $affidavitOfGuardianship];
    $result3 = executeInsert($conn, $sql3, $types3, $params3);

    if (!$result3['success']) {
        throw new Exception($result3['message']);
    }

    // Conditional inserts based on application type
    if (strpos($applicationType, 'SHS Graduate') !== false || strpos($applicationType, 'Ongoing G12') !== false) {
        // Insert into shs_information
        $sql4 = "INSERT INTO shs_information (applicant_id, strand, program_reference, first_choice, second_choice) 
                     VALUES (?, ?, ?, ?, ?)";
        $types4 = "issss";
        $params4 = [$applicant_id, $strand, $programReference, $firstChoiceProgram, $secondChoiceProgram];
        $result4 = executeInsert($conn, $sql4, $types4, $params4);

        if (!$result4['success']) {
            throw new Exception($result4['message']);
        }

        // Insert into academic_records
        $sql5 = "INSERT INTO academic_records (applicant_id, general_average, avg_filipino, avg_english, avg_mathematics, avg_science, overall_general_average) 
                     VALUES (?, ?, ?, ?, ?, ?, ?)";
        $types5 = "idddddd";
        $params5 = [$applicant_id, $generalAverage, $avgFil, $avgEng, $avgMath, $avgSci, $overallAverage];
        $result5 = executeInsert($conn, $sql5, $types5, $params5);

        if (!$result5['success']) {
            throw new Exception($result5['message']);
        }
    } else {
        // Insert into transferee_information for other application types
        $sql4 = "INSERT INTO transferee_information (applicant_id, previous_course_attended) 
                     VALUES (?, ?)";
        $types4 = "is";
        $params4 = [$applicant_id, $previousCourse];
        $result4 = executeInsert($conn, $sql4, $types4, $params4);

    if (!$result4['success']) {
            throw new Exception($result4['message']);
        }
    }

    // === Update admission_submission (can_apply=false, can_update=false, timestamps NOW) ===
    // Check if a record exists for this user
    $sqlCheck = "SELECT id FROM admission_submission WHERE user_id = ?";
    $typesCheck = "i";
    $paramsCheck = [$ACCOUNT_ID];
    $checkResult = executeSelect($conn, $sqlCheck, $typesCheck, $paramsCheck);

    if ($checkResult['success'] && count($checkResult['data']) > 0) {
        // Update existing row
        $sqlUpdate = "UPDATE admission_submission SET can_apply = ?, can_update = ?, submitted_at = NOW(), updated_at = NOW() WHERE user_id = ?";
        $typesUpdate = "iii";
        $paramsUpdate = [0, 0, $ACCOUNT_ID];
        $updateRes = executeUpdate($conn, $sqlUpdate, $typesUpdate, $paramsUpdate);
        if (!$updateRes['success']) {
            throw new Exception($updateRes['message']);
        }
    } else {
        // Insert new row
        $sqlInsert = "INSERT INTO admission_submission (user_id, can_apply, can_update, submitted_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())";
        $typesInsert = "iii";
        $paramsInsert = [$ACCOUNT_ID, 0, 0];
        $insertRes = executeInsert($conn, $sqlInsert, $typesInsert, $paramsInsert);
        if (!$insertRes['success']) {
            throw new Exception($insertRes['message']);
        }
    }

    $conn->commit();
    http_response_code(201);
    echo json_encode([
        "success" => true,
        "message" => "Application submitted successfully",
        "applicant_id" => $applicant_id
    ]);
} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Failed to submit application: " . $e->getMessage()
    ]);
}
