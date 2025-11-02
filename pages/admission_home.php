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

// --- UPDATED QUERY ---
$sql = "SELECT
            at.id, at.name,
            c.cycle_name
        FROM
            applicant_types at
        JOIN
            admission_cycles c ON at.admission_cycle_id = c.id
        WHERE
            at.is_active = 1 AND at.is_archived = 0
        ORDER BY
            c.cycle_name, at.name";

$result = $conn->query($sql);
$active_types = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $active_types[] = $row;
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="../pages/src/css/admission_dashboard.css">
    <link rel="stylesheet" href="../pages/src/css/global_styling.css">
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
                        <h2 class="bigger_text">CHOOSE YOUR APPLICATION TYPE</h2>
                        <p>ACADEMIC YEAR 2025-2026</p>
                    </div>
                </div>
            </div>

            <!-- Cards Menu -->
            <div class="cards_menu_container">

                <?php if (empty($active_types)): ?>
                    <div class="gradelvl_container_menu" style="display:flex;align-items:center;justify-content:center;min-height:220px;width:100%;grid-column:1 / -1;">
                        <div class="gradelvl_container_info" style="text-align:center;">
                            <p class="year_and_program_container" style="margin-bottom:8px;">Admission not available</p>
                            <p style="opacity:0.8;">No open admission cycles at the moment. Please check back later.</p>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($active_types as $type): ?>
                        <div class="gradelvl_container_menu">
                            <div class="gradelvl_container-picture">
                                <img src="<?php echo $ADMISSION_BANNER_URL; ?>" alt="<?php echo $ADMISSION_BANNER; ?>" />
                            </div>

                            <div class="gradelvl_container_info">
                                <p class="year_and_program_container">
                                    <?php echo htmlspecialchars($type['name']); ?>
                                </p>

                                <div class="button_area_forms">
                                    <button class="btn" aria-label="next"
                                        onclick="window.location.href='application?applicant_type_id=<?php echo $type['id']; ?>';">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                            viewBox="0 0 24 24" fill="none"
                                            stroke="white" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round"
                                            class="lucide lucide-chevron-right">
                                            <path d="m9 18 6-6-6-6" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <!-- <div class="gradelvl_container_menu">
                    <div class="gradelvl_container-picture">
                        <img src="../pages/src/media/graduated_cover.png" alt="Officer Picture" />
                    </div>
                    <div class="gradelvl_container_info">
                        <p class="year_and_program_container">Senior High Graduate</p>
                        <div class="button_area_forms">
                            <button class="btn" aria-label="next" onclick="window.location.href='application?type=SHSGraduate';">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                                    stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                    class="lucide lucide-chevron-right">
                                    <path d="m9 18 6-6-6-6" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="gradelvl_container_menu">
                    <div class="gradelvl_container-picture">
                        <img src="../pages/src/media/transferee_cover.png" alt="Officer Picture" />
                    </div>
                    <div class="gradelvl_container_info">
                        <p class="year_and_program_container">Transferee</p>
                        <div class="button_area_forms">
                            <button class="btn" aria-label="next" onclick="window.location.href='application?type=Transferee';">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                                    stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                    class="lucide lucide-chevron-right">
                                    <path d="m9 18 6-6-6-6" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>


                <div class="gradelvl_container_menu">
                    <div class="gradelvl_container-picture">
                        <img src="../pages/src/media/ongoing12_cover.png" alt="Officer Picture" />
                    </div>
                    <div class="gradelvl_container_info">
                        <p class="year_and_program_container">ALS Graduate</p>
                        <div class="button_area_forms">
                            <button class="btn" aria-label="next" onclick="window.location.href='application?type=ALSGraduate';">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                                    stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                    class="lucide lucide-chevron-right">
                                    <path d="m9 18 6-6-6-6" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div> -->

            </div><!-- end cards_menu_container -->
        </div><!-- end cards_container -->

    </div><!-- end body_content_container -->
    <?php include "includes/footer.php"; ?>
    <?php include "includes/modal.php"; ?>
    <?php include "includes/loader.php"; ?>
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