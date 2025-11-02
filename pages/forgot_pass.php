<?php
include "connection/main_connection.php";
include "functions/generalUploads.php";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="pages/src/css/forgot_pass.css">
</head>

<body>
    <div class="container">
        <!-- Background -->
        <div class="background"></div>

        <!-- Main content -->
        <div class="main-content">
            <!-- Welcome section -->
            <div class="welcome-section">
                <h1>Forgot Password?</h1>
                <p>Don't worry! Enter your email address and we'll send you a verification code to reset your password.</p>
            </div>

            <!-- Forgot Password form -->
            <div class="forgot-section">
                <a href="login" class="return-section" title="Return to Home">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#FFFFFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-house-icon lucide-house">
                        <path d="M15 21v-8a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v8" />
                        <path d="M3 10a2 2 0 0 1 .709-1.528l7-6a2 2 0 0 1 2.582 0l7 6A2 2 0 0 1 21 10v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" />
                    </svg>
                </a>

                <div class="forgot-header">
                    <img src="<?php echo $PLP_LOGO_URL; ?>" alt="<?php echo $PLP_LOGO; ?>" class="logo" />
                    <div class="header-text">
                        <h2>Pamantasan ng Lungsod ng Pasig</h2>
                        <p>Admission | Password Recovery</p>
                    </div>
                </div>

                <div class="forgot-form">
                    <p class="form-description">Enter the email address linked to your admission account to proceed with password recovery.</p>
                    <form id="forgotForm">
                        <div class="input-group">
                            <div class="input-wrapper">
                                <img src="pages/src/media/mail.png" alt="Email" class="input-icon">
                                <input type="email" placeholder="Email Address" id="email-address" required>
                            </div>
                        </div>

                        <button type="submit" class="send-button">RESET PASSWORD</button>
                    </form>

                    <p class="back-to-login">
                        Remember your password? <a href="login" class="login-link">Sign in</a>
                    </p>
                </div>
            </div>
        </div>

        <!-- Message Modal -->
        <?php include "includes/modal.php"; ?>
        <?php include "includes/loader.php"; ?>
    </div>

    <script>
        const forgotForm = document.getElementById('forgotForm');
        const sendButton = document.querySelector('.send-button');
        forgotForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const email_address = document.getElementById('email-address').value;

            if (email_address) {
                sendButton.disabled = true;
                sendButton.style.cursor = 'not-allowed';
                sendButton.textContent = 'PLEASE WAIT...';

                try {
                    const response = await fetch('api/reset-password', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            email: email_address
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
                            title: 'RESET FAILED',
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
                        title: 'RESET ERROR',
                        message: error,
                        cancelText: 'Cancel',
                        actionText: 'Okay, Try Again',
                        onConfirm: () => {
                            messageModalV1Dismiss();
                        }
                    });
                } finally {
                    sendButton.disabled = false;
                    sendButton.style.cursor = 'pointer';
                    sendButton.textContent = 'RESET PASSWORD';
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
    </script>
</body>

</html>