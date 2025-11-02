<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$email = $_SESSION['login-otp-email'] ?? null;

if (!$email) {
    header("Location: email-otp");
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../pages/src/css/signin_verify_otp.css">
</head>

<body>
    <div class="container">
        <!-- Background -->
        <div class="background"></div>

        <!-- Main content -->
        <div class="main-content">
            <!-- Welcome section -->
            <div class="welcome-section">
                <h1>Welcome Back!</h1>
                <p>Access your admission account to track and manage your application.</p>
            </div>

            <!-- OTP form -->
            <div class="login-section">
                <a href="email-otp" class="return-section" title="Back to Email Login">
                    <svg xmlns="http://www.w3.org/2000/svg"
                        width="20"
                        height="20"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="#FFFFFF"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        class="lucide lucide-chevron-left-icon lucide-chevron-left">
                        <path d="m15 18-6-6 6-6" />
                    </svg>
                </a>
                <div class="login-header">
                    <img src="../pages/src/media/plp_logo.png" alt="PLP Logo" class="logo">
                    <div class="header-text">
                        <h2>Pamantasan ng Lungsod ng Pasig</h2>
                        <p>Admission | Sign in</p>
                    </div>
                </div>

                <form class="otp-form" id="otpForm">
                    <p class="otp-instruction">
                        Check your email for the OTP and enter it below to proceed.
                    </p>
                    <input type="hidden" value="<?php echo $_SESSION['login-otp-email'] ?>" id="email-address">
                    <div class="otp-container">
                        <input type="text" class="otp-input" id="otp-1" maxlength="1" pattern="[0-9]" required>
                        <input type="text" class="otp-input" id="otp-2" maxlength="1" pattern="[0-9]" required>
                        <input type="text" class="otp-input" id="otp-3" maxlength="1" pattern="[0-9]" required>
                        <input type="text" class="otp-input" id="otp-4" maxlength="1" pattern="[0-9]" required>
                        <input type="text" class="otp-input" id="otp-5" maxlength="1" pattern="[0-9]" required>
                        <input type="text" class="otp-input" id="otp-6" maxlength="1" pattern="[0-9]" required>
                    </div>

                    <div class="resend-container">
                        <p class="resend-text">Resend in <span id="countdown">5:00</span></p>
                    </div>

                    <button type="submit" class="verify-button">LOGIN ACCOUNT</button>
                </form>
            </div>
        </div>

        <!-- Message Modal -->
        <?php include "./includes/modal.php"; ?>
        <?php include "./includes/loader.php"; ?>
    </div>

    <script src="../pages/src/js/activate_acc.js"></script>
    <script src="../pages/src/js/resend_account_verification_link.js"></script>
    <script>
        // OTP input handling
        const otpInputs = document.querySelectorAll('.otp-input');

        otpInputs.forEach((input, index) => {
            input.addEventListener('input', (e) => {
                if (e.target.value.length === 1 && index < otpInputs.length - 1) {
                    otpInputs[index + 1].focus();
                }
            });

            input.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && e.target.value === '' && index > 0) {
                    otpInputs[index - 1].focus();
                }
            });
        });

        const otpForm = document.getElementById('otpForm');
        const loginButton = document.querySelector('.verify-button');
        otpForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const email_address = document.getElementById('email-address').value;
            const inputs = [
                document.getElementById('otp-1'),
                document.getElementById('otp-2'),
                document.getElementById('otp-3'),
                document.getElementById('otp-4'),
                document.getElementById('otp-5'),
                document.getElementById('otp-6')
            ];

            for (let i = 0; i < inputs.length; i++) {
                const value = inputs[i].value.trim();

                if (value === '') {
                    messageModalV1Show({
                        icon: `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-ban-icon lucide-ban"><path d="M4.929 4.929 19.07 19.071"/><circle cx="12" cy="12" r="10"/></svg>`,
                        iconBg: '#7d2e2e7a',
                        actionBtnBg: '#c42424ff',
                        showCancelBtn: false,
                        title: 'LOGIN FAILED',
                        message: 'The OTP you entered is incorrect. Please double-check and try again.',
                        cancelText: 'Cancel',
                        actionText: 'Okay, Try Again',
                        onConfirm: () => {
                            messageModalV1Dismiss();
                        }
                    });
                    inputs[i].focus();
                    return;
                }
            }

            if (email_address) {
                const otp_code = inputs.map(input => input.value).join('');
                loginButton.disabled = true;
                loginButton.style.cursor = 'not-allowed';
                loginButton.textContent = 'PLEASE WAIT...';

                try {
                    const response = await fetch('../api/login-verify-otp', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            email_address,
                            otp_code
                        })
                    });

                    const data = await response.json();
                    if (data.status == "success") {
                        if (data.token) {
                            sessionStorage.setItem('token', data.token);
                        }
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
                                window.location.href = 'verify-login';
                            }
                        });
                    } else if (data.status == "expired" || data.status == "used") {
                        messageModalV1Show({
                            icon: `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-ban-icon lucide-ban"><path d="M4.929 4.929 19.07 19.071"/><circle cx="12" cy="12" r="10"/></svg>`,
                            iconBg: '#7d2e2e7a',
                            actionBtnBg: '#c42424ff',
                            showCancelBtn: true,
                            title: 'INVALID OTP',
                            message: data.message,
                            cancelText: 'Cancel',
                            actionText: 'Resend OTP Code',
                            onConfirm: () => {
                                handleResendVerification();
                            }
                        });
                    } else if (data.status == "deactivated") {
                        messageModalV1Show({
                            icon: `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-ban-icon lucide-ban"><path d="M4.929 4.929 19.07 19.071"/><circle cx="12" cy="12" r="10"/></svg>`,
                            iconBg: '#7d2e2e7a',
                            actionBtnBg: '#c42424ff',
                            showCancelBtn: true,
                            title: 'ACCOUNT DEACTIVATED',
                            message: data.message,
                            cancelText: 'Cancel',
                            actionText: 'Reactivate Account',
                            onConfirm: () => {
                                handleAccountReactivation('email-address');
                            }
                        });
                    } else if (data.status == "not_verified") {
                        messageModalV1Show({
                            icon: `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-ban-icon lucide-ban"><path d="M4.929 4.929 19.07 19.071"/><circle cx="12" cy="12" r="10"/></svg>`,
                            iconBg: '#7d2e2e7a',
                            actionBtnBg: '#c42424ff',
                            showCancelBtn: true,
                            title: 'ACCOUNT NOT VERIFIED',
                            message: data.message,
                            cancelText: 'Cancel',
                            actionText: 'Resend Verification',
                            onConfirm: () => {
                                handleResendAccountVerificationToken('email-address');
                            }
                        });
                    } else {
                        messageModalV1Show({
                            icon: `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-ban-icon lucide-ban"><path d="M4.929 4.929 19.07 19.071"/><circle cx="12" cy="12" r="10"/></svg>`,
                            iconBg: '#7d2e2e7a',
                            actionBtnBg: '#c42424ff',
                            showCancelBtn: false,
                            title: 'LOGIN FAILED',
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
                        title: 'LOGIN ERROR',
                        message: error,
                        cancelText: 'Cancel',
                        actionText: 'Okay, Try Again',
                        onConfirm: () => {
                            messageModalV1Dismiss();
                        }
                    });
                } finally {
                    loginButton.disabled = false;
                    loginButton.style.cursor = 'pointer';
                    loginButton.textContent = 'LOGIN ACCOUNT';
                }
            } else {
                messageModalV1Show({
                    icon: `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-ban-icon lucide-ban"><path d="M4.929 4.929 19.07 19.071"/><circle cx="12" cy="12" r="10"/></svg>`,
                    iconBg: '#7d2e2e7a',
                    actionBtnBg: '#c42424ff',
                    showCancelBtn: false,
                    title: 'INVALID INPUT',
                    message: 'Whoops! It looks like you forgot to enter your email. Please fill in fields to log in.',
                    cancelText: 'Cancel',
                    actionText: 'Okay, Try Again',
                    onConfirm: () => {
                        messageModalV1Dismiss();
                    }
                });
            }
        });

        let timeLeft = 300;
        let timer = null;

        function startCountdown() {
            if (timer) clearInterval(timer);
            timer = setInterval(() => {
                if (timeLeft > 0) {
                    const minutes = Math.floor(timeLeft / 60);
                    const seconds = timeLeft % 60;
                    countdownElement.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
                    timeLeft--;
                } else {
                    clearInterval(timer);
                    showResendLink();
                }
            }, 1000);
        }

        async function handleResendVerification() {
            const email = document.getElementById('email-address').value;

            showLoader();

            try {
                const response = await fetch('../api/login-send-otp', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        email_address: email
                    })
                });

                const data = await response.json();
                if (data.status == "success") {
                    messageModalV1Show({
                        icon: `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-check-icon lucide-check"><path d="M20 6 9 17l-5-5"/></svg>`,
                        iconBg: '#2e7d327a',
                        actionBtnBg: '#2E7D32',
                        showCancelBtn: false,
                        title: 'Success',
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
                        title: 'LOGIN FAILED',
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
                    title: 'LOGIN ERROR',
                    message: error,
                    cancelText: 'Cancel',
                    actionText: 'Okay, Try Again',
                    onConfirm: () => {
                        messageModalV1Dismiss();
                    }
                });
            } finally {
                // Hide loader & re-enable resend link
                hideLoader();
            }
        }

        async function showResendLink() {
            resendTextElement.innerHTML = `Didn't receive code? <a href="#" id="resend-link">Resend now</a>`;
            const resendLink = document.getElementById('resend-link');

            resendLink.addEventListener('click', async (e) => {
                e.preventDefault();
                handleResendVerification();
                timeLeft = 300;
                resendTextElement.innerHTML = `Resend in <span id="countdown"></span>`;
                countdownElement = document.getElementById('countdown');
                startCountdown();
            });
        }

        // Start countdown on page load
        startCountdown();
    </script>
</body>

</html>