<?php
include "connection/main_connection.php";
include "functions/generalUploads.php";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../pages/src/css/signin_otp.css">
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

            <!-- Verification form -->
            <div class="login-section">
                <a href="email-otp" class="return-section" title="Return to Home">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#FFFFFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-house-icon lucide-house">
                        <path d="M15 21v-8a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v8" />
                        <path d="M3 10a2 2 0 0 1 .709-1.528l7-6a2 2 0 0 1 2.582 0l7 6A2 2 0 0 1 21 10v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                    </svg>
                </a>

                <div class="login-header">
                    <img src="<?php echo $PLP_LOGO_URL; ?>" alt="<?php echo $PLP_LOGO; ?>" class="logo" />
                    <div class="header-text">
                        <h2>Pamantasan ng Lungsod ng Pasig</h2>
                        <p>Admission | Sign in</p>
                    </div>
                </div>

                <div class="login-form">
                    <form id="verificationForm">
                        <div class="input-group">
                            <div class="input-wrapper">
                                <input type="email" placeholder="Email Address" id="email-address" required>
                                <img src="../pages/src/media/mail.png" alt="Email" class="input-icon">
                            </div>
                        </div>

                        <div class="form-options">
                            <label class="checkbox-wrapper">
                                <input type="checkbox">
                                <span class="checkmark"></span>
                                Remember Me
                            </label>
                            <a href="../forgot-password" class="forgot-password">Forgot Password?</a>
                        </div>

                        <button type="submit" class="verification-button">SEND VERIFICATION CODE</button>
                    </form>

                    <a href="../login" class="password-button-link">
                        <button type="button" class="password-button">LOGIN VIA PASSWORD</button>
                    </a>

                    <p class="signup-text">
                        Don't have an account? <a href="../register" class="signup-link">Sign up</a>
                    </p>
                </div>
            </div>
        </div>

        <!-- Message Modal -->
        <?php include "includes/modal.php"; ?>
        <?php include "includes/loader.php"; ?>
    </div>

    <script>
        const loginForm = document.getElementById('verificationForm');
        const loginButton = document.querySelector('.verification-button');
        const signupLink = document.querySelector('.signup-link');

        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const email_address = document.getElementById('email-address').value;
            if (email_address) {
                loginButton.disabled = true;
                loginButton.style.cursor = 'not-allowed';
                loginButton.textContent = 'PLEASE WAIT...';

                signupLink.style.pointerEvents = 'none';
                signupLink.style.cursor = 'not-allowed';
                signupLink.style.opacity = '0.6';

                try {
                    const response = await fetch('../api/login-send-otp', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            email_address
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
                                window.location.href = 'verify-login';
                            }
                        });
                    } else if (data.status == "error-resend") {
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
                                handleResendVerificationToken();
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
                    message: 'Whoops! It looks like you forgot to enter your email. Please fill in fields to log in.',
                    cancelText: 'Cancel',
                    actionText: 'Okay, Try Again',
                    onConfirm: () => {
                        messageModalV1Dismiss();
                    }
                });
            }
        });

        async function handleResendVerificationToken() {
            const email = document.getElementById('email-address').value;

            showLoader();

            try {
                const response = await fetch('../api/resend-login-verification', {
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
                        title: 'SENDING VERIFICATION SUCCESS',
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
                        title: 'SENDING VERIFICATION FAILED',
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
                    title: 'SENDING VERIFICATION ERROR',
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