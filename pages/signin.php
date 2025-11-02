<?php
include "connection/main_connection.php";
include "functions/generalUploads.php";
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

        <div class="main-content">
            <div class="welcome-section">
                <h1>Welcome Back!</h1>
                <p>Access your admission account to track and manage your application.</p>
            </div>
            <div class="login-section">
                <a href="email-otp" class="return-section" title="Return to Home">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#FFFFFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-house-icon lucide-house">
                        <path d="M15 21v-8a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v8" />
                        <path d="M3 10a2 2 0 0 1 .709-1.528l7-6a2 2 0 0 1 2.582 0l7 6A2 2 0 0 1 21 10v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                    </svg>
                </a>

                <div class="login-header">
                    <img src="<?php echo $PLP_LOGO_URL; ?>" alt="<?php echo $PLP_LOGO; ?>" class="logo"/>
                    <div class="header-text">
                        <h2>Pamantasan ng Lungsod ng Pasig</h2>
                        <p>Admission | Sign in</p>
                    </div>
                </div>

                <div class="login-form">
                    <form id="loginForm">
                        <div class="input-group">
                            <div class="input-wrapper">
                                <img src="pages/src/media/mail.png" alt="Email" class="input-icon">
                                <input type="email" placeholder="Email Address" id="email-address" required>
                            </div>
                        </div>

                        <div class="input-group">
                            <div class="input-wrapper">
                                <img src="pages/src/media/key-round.png" alt="Password" class="input-icon" id="passwordIcon" onclick="showHidePassword('password', 'passwordIcon')">
                                <input type="password" placeholder="Password" id="password" required>
                            </div>
                        </div>

                        <div class="form-options">
                            <label class="checkbox-wrapper">
                                <input type="checkbox">
                                <span class="checkmark"></span>
                                Remember Me
                            </label>
                            <a href="forgot-password" class="forgot-password">Forgot Password?</a>
                        </div>

                        <button type="submit" class="login-button" id="login-button">LOGIN ACCOUNT</button>
                    </form>

                    <a href="login/email-otp" class="otp-button-link">
                        <button type="button" class="otp-button">LOGIN VIA OTP</button>
                    </a>

                    <p class="signup-text">
                        Don't have an account? <a href="register" class="signup-link">Sign up</a>
                    </p>
                </div>
            </div>

            <!-- Message Modal -->
            <?php include "includes/modal.php"; ?>
            <?php include "includes/loader.php"; ?>

        </div>
    </div>
    <script src="pages/src/js/showHidePass.js"></script>
    <script src="pages/src/js/activate_acc.js"></script>
    <script src="pages/src/js/resend_account_verification_link.js"></script>
    <script>
        const loginForm = document.getElementById('loginForm');
        const loginButton = document.getElementById('login-button');
        const signupLink = document.querySelector('.signup-link');

        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const email_address = document.getElementById('email-address').value;
            const password = document.getElementById('password').value;
            if (email_address && password) {
                loginButton.disabled = true;
                loginButton.style.cursor = 'not-allowed';
                loginButton.textContent = 'PLEASE WAIT...';

                signupLink.style.pointerEvents = 'none';
                signupLink.style.cursor = 'not-allowed';
                signupLink.style.opacity = '0.6';

                try {
                    const response = await fetch('api/login', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            email_address,
                            password
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
                                window.location.href = 'admission/home';
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

                    signupLink.style.pointerEvents = 'auto';
                    signupLink.style.cursor = 'pointer';
                    signupLink.style.opacity = '1';
                }
            } else {
                messageModalV1Show({
                    icon: `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-ban-icon lucide-ban"><path d="M4.929 4.929 19.07 19.071"/><circle cx="12" cy="12" r="10"/></svg>`,
                    iconBg: '#7d2e2e7a',
                    actionBtnBg: '#c42424ff',
                    showCancelBtn: false,
                    title: 'INVALID INPUT',
                    message: 'Whoops! It looks like you forgot to enter your email or password. Please fill in both fields to log in.',
                    cancelText: 'Cancel',
                    actionText: 'Okay, Try Again',
                    onConfirm: () => {
                        messageModalV1Dismiss();
                    }
                });
            }
        });

        // === Key icon changes to eye when focused or has value ===
        const passwordIcon = document.getElementById('passwordIcon');
        const passwordInput = document.getElementById('password');

        function updateIconForInput(input, iconEl) {
            const focusedOrFilled = document.activeElement === input || input.value.trim() !== '';
            iconEl.src = focusedOrFilled ? 'pages/src/media/eye.svg' : 'pages/src/media/key-round.png';
        }

        function attachIconBehavior(input, iconEl) {
            const handler = () => updateIconForInput(input, iconEl);
            input.addEventListener('focus', handler);
            input.addEventListener('blur', handler);
            input.addEventListener('input', handler);
            handler(); // initialize
        }

        attachIconBehavior(passwordInput, passwordIcon);
    </script>
</body>

</html>