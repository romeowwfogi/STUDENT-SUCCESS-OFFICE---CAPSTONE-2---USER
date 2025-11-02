<?php
// CHECK IF 'token' EXISTS IN THE URL QUERY PARAMETERS
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
    $backHome = false;
    $token = $_GET['token'] ?? '';

    $sql = "SELECT user_id, expires_at FROM tokenization 
        WHERE value = ? AND name = 'RESET_PASSWORD' AND is_used = 0";
    $types = "s";
    $params = [$token];
    $result = executeSelect($conn, $sql, $types, $params);

    if ($result['success'] && count($result['data']) > 0) {
        $row = $result['data'][0];
        $userId = $row['user_id'];
        $expiresAt = new DateTime($row['expires_at']);
        $now = new DateTime();

        $sqlUser = "SELECT email, acc_status FROM users WHERE id = ?";
        $typesUser = "i";
        $paramsUser = [$userId];
        $resultUser = executeSelect($conn, $sqlUser, $typesUser, $paramsUser);

        if ($resultUser['success'] && count($resultUser['data']) > 0) {
            $user = $resultUser['data'][0];
            $EMAIL_ADDRESS = decryptData($user['email']);

            if ($user['acc_status'] === 'ACCOUNT_BANNED_DELETED') {
                $ERROR = getConfigValue(
                    $conn,
                    'ACCOUNT_BANNED_DELETED',
                    "It looks like your account has been deactivated or suspended. If this is unexpected, please contact our support team to restore access."
                );
            }
        } else {
            $ERROR = getConfigValue(
                $conn,
                'RESET_PASSWORD_LINK_INVALID',
                "We couldn’t find an account with this reset password link. Please check your reset password link and try again."
            );
        }

        // 🔒 Check expiration
        if ($expiresAt < $now) {
            $isExpired = true;
            $ERROR = getConfigValue(
                $conn,
                'RESET_PASSWORD_LINK_EXPIRED',
                "This reset password link has expired for security reasons. Please request a new link to verify your account."
            );
        } else {
            // 🧩 Get user info

        }
    } else {
        $backHome = true;
        $ERROR = getConfigValue(
            $conn,
            'RESET_PASSWORD_LINK_INVALID',
            "We couldn’t find an account with this reset password link. Please check your reset password link and try again."
        );
    }
} else {
    // REDIRECT USER TO LOGIN PAGE
    header("Location: login");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="pages/src/css/signup.css">
</head>

<body>
    <div class="container">
        <!-- Background -->
        <div class="background"></div>

        <!-- Main content -->
        <div class="main-content">
            <!-- Welcome section -->
            <div class="welcome-section">
                <h1>Change Password</h1>
                <p>Change password now to apply for admission and track your application online.</p>
            </div>

            <!-- Signup form -->
            <div class="signup-section">
                <a href="email-otp" class="return-section" title="Return to Home">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#FFFFFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-house-icon lucide-house">
                        <path d="M15 21v-8a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v8" />
                        <path d="M3 10a2 2 0 0 1 .709-1.528l7-6a2 2 0 0 1 2.582 0l7 6A2 2 0 0 1 21 10v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                    </svg>
                </a>

                <div class="signup-header">
                    <img src="pages/src/media/plp_logo.png" alt="PLP Logo" class="logo">
                    <div class="header-text">
                        <h2>Pamantasan ng Lungsod ng Pasig</h2>
                        <p>Admission | Change Password</p>
                    </div>
                </div>

                <form class="signup-form" id="signupForm">
                    <div class="input-group">
                        <div class="input-wrapper">
                            <input type="email" id="email" placeholder="Email Address" value="<?php echo $EMAIL_ADDRESS; ?>" readonly>
                            <img src="pages/src/media/mail.png" alt="Email" class="input-icon">
                        </div>
                    </div>

                    <div class="input-group">
                        <div class="input-wrapper">
                            <input type="password" id="password" placeholder="Password" required>
                            <img src="pages/src/media/key-round.png" alt="Password" class="input-icon">
                        </div>
                        <div class="password-requirements">
                            <div class="requirement" id="req-length">
                                <span class="check-icon">✓</span>
                                <span class="text">8-16 Characters</span>
                            </div>
                            <div class="requirement" id="req-uppercase">
                                <span class="check-icon">✓</span>
                                <span class="text">At least one uppercase letter</span>
                            </div>
                            <div class="requirement" id="req-number">
                                <span class="check-icon">✓</span>
                                <span class="text">At least one number</span>
                            </div>
                            <div class="requirement" id="req-special">
                                <span class="check-icon">✓</span>
                                <span class="text">At least one special character</span>
                            </div>
                        </div>
                    </div>

                    <div class="input-group">
                        <div class="input-wrapper">
                            <input type="password" id="confirmPassword" placeholder="Confirm Password" required>
                            <img src="pages/src/media/key-round.png" alt="Confirm Password" class="input-icon">
                        </div>
                        <div class="password-match" id="password-match">
                            <span class="error-text">Password don't match</span>
                        </div>
                    </div>

                    <button type="submit" class="create-button" id="createButton">CHANGE PASSWORD</button>
                </form>
            </div>
        </div>

        <!-- Message Modal -->
        <?php include "includes/modal.php"; ?>
        <?php include "includes/loader.php"; ?>
    </div>

    <script>
        let ERROR = "<?php echo $ERROR; ?>";
        let isExpired = <?php echo $isExpired ? 'true' : 'false'; ?>;
        let backHome = <?php echo $backHome ? 'true' : 'false'; ?>;

        const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('confirmPassword');
        const createButton = document.getElementById('createButton');
        const passwordMatch = document.getElementById('password-match');
        const signupForm = document.getElementById('signupForm');
        const emailInput = document.getElementById('email');
        const signinLink = document.querySelector('.signin-link');
        const reqLength = document.getElementById('req-length');
        const reqUppercase = document.getElementById('req-uppercase');
        const reqNumber = document.getElementById('req-number');
        const reqSpecial = document.getElementById('req-special');

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

        // CHECK IF TOKEN IS EXPIRED
        if (isExpired) {
            messageModalV1Show({
                icon: `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-ban-icon lucide-ban"><path d="M4.929 4.929 19.07 19.071"/><circle cx="12" cy="12" r="10"/></svg>`,
                iconBg: '#7d2e2e7a',
                actionBtnBg: '#c42424ff',
                showCancelBtn: true,
                title: 'EXPIRED',
                message: ERROR,
                cancelText: 'Back to Home',
                actionText: 'Resend Reset Link',
                onConfirm: () => {
                    sendResetPassword();
                }
            });
        }

        if (backHome) {
            messageModalV1Show({
                icon: `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-ban-icon lucide-ban"><path d="M4.929 4.929 19.07 19.071"/><circle cx="12" cy="12" r="10"/></svg>`,
                iconBg: '#7d2e2e7a',
                actionBtnBg: '#c42424ff',
                showCancelBtn: false,
                title: 'INVALID',
                message: ERROR,
                cancelText: 'Back to Home',
                actionText: 'Back to Login',
                onConfirm: () => {
                    window.location.href = 'login';
                }
            });
        }

        async function sendResetPassword() {
            showLoader();
            try {
                const response = await fetch('api/reset-password', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        email: emailInput.value
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
                        actionText: 'Okay',
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
                        actionText: 'Resend Reset Link',
                        onConfirm: () => {
                            sendResetPassword()
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

        // === Password Validation ===
        function validatePassword() {
            const password = passwordInput.value;

            reqLength.classList.toggle('valid', password.length >= 8 && password.length <= 16);
            reqUppercase.classList.toggle('valid', /[A-Z]/.test(password));
            reqNumber.classList.toggle('valid', /[0-9]/.test(password));
            reqSpecial.classList.toggle('valid', /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password));
        }

        function validateConfirmPassword() {
            if (confirmPasswordInput.value && passwordInput.value !== confirmPasswordInput.value) {
                passwordMatch.style.display = 'block';
            } else {
                passwordMatch.style.display = 'none';
            }
        }

        passwordInput.addEventListener('input', validatePassword);
        confirmPasswordInput.addEventListener('input', validateConfirmPassword);

        function isFormValid() {
            const allRequirementsMet = document.querySelectorAll('.requirement.valid').length === 4;
            const passwordsMatch = passwordInput.value === confirmPasswordInput.value;
            const passwordsFilled = passwordInput.value && confirmPasswordInput.value;
            return allRequirementsMet && passwordsMatch && passwordsFilled;
        }

        // === Step 1: Show Terms modal on submit ===
        signupForm.addEventListener('submit', (e) => {
            e.preventDefault();
            if (isFormValid()) {
                handleRegister();
            } else {
                alert('Please ensure all requirements are met and passwords match.');
                messageModalV1Show({
                    icon: `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-ban-icon lucide-ban"><path d="M4.929 4.929 19.07 19.071"/><circle cx="12" cy="12" r="10"/></svg>`,
                    iconBg: '#7d2e2e7a',
                    actionBtnBg: '#c42424ff',
                    showCancelBtn: false,
                    title: 'Error',
                    message: error,
                    cancelText: 'Cancel',
                    actionText: 'Okay, Try Again',
                    onConfirm: () => {
                        messageModalV1Dismiss();
                    }
                });
            }
        });

        // === Step 3: Final Registration ===
        async function handleRegister() {
            const token = "<?php echo $_GET['token']; ?>";
            const password = passwordInput.value;

            createButton.disabled = true;
            createButton.style.cursor = 'not-allowed';
            createButton.textContent = 'PLEASE WAIT...';

            try {
                const response = await fetch('api/change-password', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        token,
                        password
                    })
                });

                const data = await response.json();
                if (data.status == "success") {
                    messageModalV1Show({
                        icon: `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-check-icon lucide-check"><path d="M20 6 9 17l-5-5"/></svg>`,
                        iconBg: '#2e7d327a',
                        actionBtnBg: '#2E7D32',
                        showCancelBtn: false,
                        title: 'CHANGE PASSWORD SUCCESS',
                        message: data.message,
                        cancelText: 'Cancel',
                        actionText: 'Okay, Back to Login',
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
                        title: 'CHANGE PASSWORD FAILED',
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
                    title: 'CHANGE PASSWORD ERROR',
                    message: error,
                    cancelText: 'Cancel',
                    actionText: 'Okay, Try Again',
                    onConfirm: () => {
                        messageModalV1Dismiss();
                    }
                });
            } finally {
                createButton.disabled = false;
                createButton.style.cursor = 'pointer';
                createButton.textContent = 'CHANGE PASSWORD';
            }
        }
    </script>

</body>

</html>