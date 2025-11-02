<?php
if (isset($_GET['token']) && !empty($_GET['token'])) {
    include "connection/main_connection.php";
    include "functions/en-de_crypt.php";
    include "functions/config_msg.php";
    include "functions/select_sql.php";
    include "functions/update_sql.php";


    $token = $_GET['token'];

    $ERROR = "";
    $EMAIL_ADDRESS = "";
    $isExpired = false;
    $isAlreadyVerified = false;
    $isSuccess = false;

    $token = $_GET['token'] ?? '';

    $sql = "SELECT user_id, expires_at FROM tokenization 
        WHERE value = ? AND name = 'ACTIVATE_ACCOUNT'";
    $types = "s";
    $params = [$token];
    $result = executeSelect($conn, $sql, $types, $params);

    if ($result['success'] && count($result['data']) > 0) {
        $row = $result['data'][0];
        $userId = $row['user_id'];
        $expiresAt = new DateTime($row['expires_at']);
        $now = new DateTime();

        // 🔒 Check expiration
        if ($expiresAt < $now) {
            $isExpired = true;
            $ERROR = getConfigValue(
                $conn,
                'EXPIRED_VERIFY_LINK',
                "This verification link has expired for security reasons. Please request a new link to verify your account."
            );
        } else {
            // 🧩 Get user info
            $sqlUser = "SELECT email, acc_status FROM users WHERE id = ?";
            $typesUser = "i";
            $paramsUser = [$userId];
            $resultUser = executeSelect($conn, $sqlUser, $typesUser, $paramsUser);

            if ($resultUser['success'] && count($resultUser['data']) > 0) {
                $user = $resultUser['data'][0];
                $EMAIL_ADDRESS = decryptData($user['email']);

                // 🧾 Check if already verified
                if ($user['acc_status'] === 'active') {
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

                        // ✅ Update tokenization is_used = 1
                        $sqlToken = "UPDATE tokenization SET is_used = 1 WHERE value = ?";
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
                $ERROR = getConfigValue(
                    $conn,
                    'USER_NOT_FOUND',
                    "We couldn’t find an account with this verification link. Please check your information and try again."
                );
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

        // CHECK IF ERROR EXISTS
        if (ERROR) {
            messageModalV1Show({
                icon: `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-ban-icon lucide-ban"><path d="M4.929 4.929 19.07 19.071"/><circle cx="12" cy="12" r="10"/></svg>`,
                iconBg: '#7d2e2e7a',
                actionBtnBg: '#c42424ff',
                showCancelBtn: false,
                title: 'ERROR',
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
                actionText: 'Resend Reactivation Link',
                onConfirm: () => {
                    handleAccountReactivation();
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
                message: 'Congratulations! Your account is now active. Welcome aboard, and you can start exploring your account.',
                cancelText: 'Cancel',
                actionText: 'Back to Login',
                onConfirm: () => {
                    window.location.href = 'login';
                }
            });
        }

        async function handleAccountReactivation() {
            const email = document.getElementById('email-address').value;

            showLoader();

            try {
                const response = await fetch('api/activate-account', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        email: email
                    })
                });

                const data = await response.json();
                if (data.success) {
                    messageModalV1Show({
                        icon: `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-check-icon lucide-check"><path d="M20 6 9 17l-5-5"/></svg>`,
                        iconBg: '#2e7d327a',
                        actionBtnBg: '#2E7D32',
                        showCancelBtn: false,
                        title: 'SUCCESS',
                        message: data.message,
                        cancelText: 'Cancel',
                        actionText: 'Okay',
                        onConfirm: () => {
                            messageModalV1Dismiss();
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
                        cancelText: 'Cancel',
                        actionText: 'Okay, Try Again',
                        onConfirm: () => {
                            messageModalV1Dismiss();
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