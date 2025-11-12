<?php
if (isset($_GET['token']) && !empty($_GET['token'])) {
    include "connection/main_connection.php";
    include "functions/en-de_crypt.php";
    include "functions/config_msg.php";
    include "functions/select_sql.php";
    include "functions/update_sql.php";

    // Ensure consistent timezone for accurate expiration comparisons
    date_default_timezone_set('Asia/Manila');


    // Get token from query param (fallback to empty string)
    $token = $_GET['token'] ?? '';

    $ERROR = "";
    $EMAIL_ADDRESS = "";
    $isExpired = false;
    $isAlreadyVerified = false;
    $isSuccess = false;

    // Fetch token + user info in one query
    $sql = "SELECT t.user_id, t.expires_at, t.is_used, u.email, u.acc_status
            FROM tokenization t
            INNER JOIN users u ON u.id = t.user_id
            WHERE t.value = ? AND t.name = 'VERIFY_ACCOUNT'";
    $types = "s";
    $params = [$token];
    $result = executeSelect($conn, $sql, $types, $params);

    if ($result['success'] && count($result['data']) > 0) {
        $row = $result['data'][0];
        $userId = $row['user_id'];
        $expiresAt = new DateTime($row['expires_at']);
        $now = new DateTime();

        // Extract user info from joined row
        $EMAIL_ADDRESS = decryptData($row['email']);
        $accStatus = $row['acc_status'];
        $tokenIsUsed = (int)($row['is_used'] ?? 0);

        // Validate expiry and usage BEFORE any activation
        if ($expiresAt < $now) {
            $isExpired = true;
            $ERROR = getConfigValue(
                $conn,
                'EXPIRED_VERIFY_LINK',
                "This verification link has expired for security reasons. Please request a new link to verify your account."
            );
        } elseif ($tokenIsUsed === 1) {
            $isAlreadyVerified = true;
            $ERROR = getConfigValue(
                $conn,
                'ALREADY_USED_VERIFY_LINK',
                "This verification link has already been used. Please request a new verification link."
            );
        } elseif ($accStatus === 'active') {
            // Already verified
            $isAlreadyVerified = true;
            $ERROR = getConfigValue(
                $conn,
                'ALREADY_VERIFIED',
                "It looks like your account has already been verified. If you’re having trouble signing in, try resetting your password or contacting support."
            );
        } else {
            // ✅ Update to active
            $conn->begin_transaction();

            try {
                // ✅ Update user account status
                $sqlUpdate = "UPDATE users SET acc_status = 'active' WHERE id = ?";
                $typesUpdate = "i";
                $paramsUpdate = [$userId];
                $resultUpdate = executeUpdate($conn, $sqlUpdate, $typesUpdate, $paramsUpdate);

                if (!$resultUpdate['success']) {
                    throw new Exception("Unable to update user verification status.");
                }

                // ✅ Mark token as used (only verify-account tokens and only if currently unused)
                $sqlToken = "UPDATE tokenization SET is_used = 1 
                              WHERE value = ? AND name = 'VERIFY_ACCOUNT' AND is_used = 0";
                $typesToken = "s";
                $paramsToken = [$token];
                $resultToken = executeUpdate($conn, $sqlToken, $typesToken, $paramsToken);

                if (!$resultToken['success']) {
                    throw new Exception("Unable to mark token as used.");
                }

                // ✅ Both succeeded — commit
                $conn->commit();
                $isSuccess = true;
            } catch (Exception $e) {
                // ❌ Roll back everything on failure
                $conn->rollback();
                $isSuccess = false;

                $ERROR = "The system was unable to update your verification status due to a temporary issue. Please refresh the page or try again later.";
            }
        }
    } else {
        header("Location: login.php");
        exit;
    }
} else {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="pages/src/css/signin.css">
</head>

<body>
    <div class="container">
        <div class="background"></div>
        <?php include "includes/modal.php"; ?>
        <?php include "includes/loader.php"; ?>
    </div>
    <script>
        let ERROR = "<?php echo $ERROR; ?>";
        let isExpired = <?php echo $isExpired ? 'true' : 'false'; ?>;
        let isSuccess = <?php echo $isSuccess ? 'true' : 'false'; ?>;
        let isAlreadyVerified = <?php echo $isAlreadyVerified ? 'true' : 'false'; ?>;
        let email = "<?php echo $EMAIL_ADDRESS; ?>";

        if (ERROR) {
            messageModalV1Show({
                icon: `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-ban-icon lucide-ban"><path d="M4.929 4.929 19.07 19.071"/><circle cx="12" cy="12" r="10"/></svg>`,
                iconBg: '#7d2e2e7a',
                actionBtnBg: '#c42424ff',
                showCancelBtn: false,
                title: 'CHANGE PASSWORD ERROR',
                message: ERROR,
                cancelText: 'Cancel',
                actionText: 'Okay, Try Again',
                onConfirm: () => {
                    messageModalV1Dismiss();
                }
            });
        }

        if (isExpired) {
            messageModalV1Show({
                icon: `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-ban-icon lucide-ban"><path d="M4.929 4.929 19.07 19.071"/><circle cx="12" cy="12" r="10"/></svg>`,
                iconBg: '#7d2e2e7a',
                actionBtnBg: '#c42424ff',
                showCancelBtn: true,
                title: 'EXPIRED',
                message: ERROR,
                cancelText: 'Back to Home',
                actionText: 'Resend Verify Link',
                onConfirm: () => {
                    sendVerifyAccount();
                }
            });
        }

        if (isAlreadyVerified) {
            messageModalV1Show({
                icon: `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-ban-icon lucide-ban"><path d="M4.929 4.929 19.07 19.071"/><circle cx="12" cy="12" r="10"/></svg>`,
                iconBg: '#7d2e2e7a',
                actionBtnBg: '#c42424ff',
                showCancelBtn: false,
                title: 'ALREADY VERIFIED',
                message: ERROR,
                cancelText: 'Back to Home',
                actionText: 'Back to Login',
                onConfirm: () => {
                    window.location.href = 'login';
                }
            });
        }

        if (isSuccess) {
            messageModalV1Show({
                icon: `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-check-icon lucide-check"><path d="M20 6 9 17l-5-5"/></svg>`,
                iconBg: '#2e7d327a',
                actionBtnBg: '#2E7D32',
                showCancelBtn: false,
                title: 'SUCCESS',
                message: 'Your account has been successfully verified. You can now log in and start using our platform.',
                cancelText: 'Cancel',
                actionText: 'Back to Login',
                onConfirm: () => {
                    window.location.href = 'login';
                }
            });
        }

        async function sendVerifyAccount() {
            showLoader();
            try {
                const response = await fetch('api/resend-verify-link', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        email
                    })
                });

                const data = await response.json();
                if (data.status == "success") {
                    messageModalV1Show({
                        icon: `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-check-icon lucide-check"><path d="M20 6 9 17l-5-5"/></svg>`,
                        iconBg: '#2e7d327a',
                        actionBtnBg: '#2E7D32',
                        showCancelBtn: false,
                        title: 'SUCCESS',
                        message: data.message,
                        cancelText: 'Cancel',
                        actionText: 'Back to Login',
                        onConfirm: () => {
                            window.location.href = 'login';
                        }
                    });
                } else {
                    messageModalV1Show({
                        icon: `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-ban-icon lucide-ban"><path d="M4.929 4.929 19.07 19.071"/><circle cx="12" cy="12" r="10"/></svg>`,
                        iconBg: '#7d2e2e7a',
                        actionBtnBg: '#c42424ff',
                        showCancelBtn: false,
                        title: 'FAILED',
                        message: data.message,
                        cancelText: 'Back to Home',
                        actionText: 'Resend Verify Link',
                        onConfirm: () => {
                            sendVerifyAccount()
                        }
                    });
                }
            } catch (error) {
                messageModalV1Show({
                    icon: `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-ban-icon lucide-ban"><path d="M4.929 4.929 19.07 19.071"/><circle cx="12" cy="12" r="10"/></svg>`,
                    iconBg: '#7d2e2e7a',
                    actionBtnBg: '#c42424ff',
                    showCancelBtn: false,
                    title: 'ERROR',
                    message: error,
                    cancelText: 'Cancel',
                    actionText: 'Okay, Try Again',
                    onConfirm: () => {
                        messageModalV1Dismiss();
                    }
                });
            } finally {
                hideLoader();
            }
        }
    </script>
</body>

</html>