<?php
include "connection/main_connection.php";
include "functions/generalUploads.php";
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
                <h1>Create Account</h1>
                <p>Register now to apply for admission and track your application online.</p>
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
                    <img src="<?php echo $PLP_LOGO_URL; ?>" alt="<?php echo $PLP_LOGO; ?>" class="logo" />
                    <div class="header-text">
                        <h2>Pamantasan ng Lungsod ng Pasig</h2>
                        <p>Admission | Sign up</p>
                    </div>
                </div>

                <form class="signup-form" id="signupForm">
                    <div class="input-group">
                        <div class="input-wrapper">
                            <input type="email" id="email" placeholder="Email Address" required>
                            <img src="pages/src/media/mail.png" alt="Email" class="input-icon">
                        </div>
                    </div>

                    <div class="input-group">
                        <div class="input-wrapper">
                            <input type="password" id="password" placeholder="Password" required>
                            <img src="pages/src/media/key-round.png" alt="Password" class="input-icon" id="passwordIcon" onclick="showHidePassword('password', 'passwordIcon')">
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
                            <img src="pages/src/media/key-round.png" alt="Confirm Password" class="input-icon" id="confirmPasswordIcon" onclick="showHidePassword('confirmPassword', 'confirmPasswordIcon')">
                        </div>
                        <div class="password-match" id="password-match">
                            <span class="error-text">Password don't match</span>
                        </div>
                    </div>

                    <button type="submit" class="create-button" id="createButton">CREATE ACCOUNT</button>

                    <p class="signin-text">
                        Already have an account? <a href="login" class="signin-link">Sign in</a>
                    </p>
                </form>
            </div>
        </div>

        <!-- Message Modal -->
        <?php include "includes/modal.php"; ?>

        <!-- Terms & Conditions Modal -->
        <div class="modal-overlay" id="termsModal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>TERMS & CONDITIONS</h2>
                    <p class="last-updated">Last Updated: September XX, XXXX</p>
                </div>

                <div class="modal-body">
                    <p class="intro-text">
                        By creating an account with Pamantasan ng Lungsod ng Pasig, you agree to the following terms and conditions:
                    </p>

                    <div class="terms-section">
                        <h3>1. Account Eligibility</h3>
                        <ul>
                            <li>You must provide accurate and truthful information when creating your account.</li>
                            <li>Accounts are personal and non-transferable.</li>
                        </ul>
                    </div>

                    <div class="terms-section">
                        <h3>2. Use of Account</h3>
                        <ul>
                            <li>Your account is intended solely for admission, enrollment, and other school-related transactions.</li>
                            <li>Unauthorized use, sharing, or misuse of accounts is strictly prohibited.</li>
                        </ul>
                    </div>

                    <div class="terms-section">
                        <h3>3. User Responsibility</h3>
                        <ul>
                            <li>You are responsible for maintaining the confidentiality of your account credentials.</li>
                            <li>Any actions taken through your account will be considered your responsibility.</li>
                        </ul>
                    </div>

                    <div class="terms-section">
                        <h3>4. Institution Rights</h3>
                        <ul>
                            <li>Pamantasan ng Lungsod ng Pasig reserves the right to suspend or terminate accounts that violate policies, use false information, or engage in unauthorized activities.</li>
                            <li>Account creation does not guarantee admission or enrollment.</li>
                        </ul>
                    </div>

                    <div class="terms-section">
                        <h3>5. Amendments</h3>
                        <ul>
                            <li>Pamantasan ng Lungsod ng Pasig may update these Terms & Conditions at any time. Continued use of the system constitutes agreement to the updated terms.</li>
                        </ul>
                    </div>

                    <p class="consent-text">
                        By clicking agree, you acknowledge that you have read and understood our Terms & Conditions and you consent to the processing of your personal data as described herein.
                    </p>
                </div>

                <div class="modal-footer">
                    <button type="button" class="disagree-button" onclick="closeModal()">DISAGREE</button>
                    <button type="button" class="agree-button" onclick="agreeToTerms()">AGREE</button>
                </div>
            </div>
        </div>

        <!-- Privacy Policy Modal -->
        <div class="modal-overlay" id="privacyModal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>PRIVACY POLICY</h2>
                    <p class="last-updated">Last Updated: September XX, XXXX</p>
                </div>

                <div class="modal-body">
                    <p class="intro-text">
                        Pamantasan ng Lungsod ng Pasig is committed to protecting your personal data in accordance with the Data Privacy Act of 2012 (RA 10173).
                    </p>

                    <div class="terms-section">
                        <h3>1. Information Collected</h3>
                        <ul>
                            <li>Personal details (name, contact information, birthdate, etc.)</li>
                            <li>Academic records or documents you submit for admission purposes</li>
                            <li>Login credentials (username, password)</li>
                        </ul>
                    </div>

                    <div class="terms-section">
                        <h3>2. Purpose of Data Collection</h3>
                        <ul>
                            <li>Account creation and management</li>
                            <li>Admission and enrollment processing</li>
                            <li>Communication regarding school services</li>
                            <li>Compliance with CHED, DepEd, TESDA, and other government regulations</li>
                        </ul>
                    </div>

                    <div class="terms-section">
                        <h3>3. Data Sharing & Disclosure</h3>
                        <ul>
                            <li>Your data will only be shared with authorized school personnel and government agencies as required by law.</li>
                            <li>We will not sell, rent, or disclose your personal data to unauthorized third parties.</li>
                        </ul>
                    </div>

                    <div class="terms-section">
                        <h3>4. Data Protection</h3>
                        <ul>
                            <li>We implement organizational, physical, and technical safeguards to protect your personal information.</li>
                            <li>Only authorized personnel have access to your records/activities.</li>
                            <li>Account creation does not guarantee admission or enrollment.</li>
                        </ul>
                    </div>

                    <div class="terms-section">
                        <h3>5. User Rights</h3>
                        <p style="margin-bottom: 8px; color: rgba(255, 255, 255, 0.9);">Under the Data Privacy Act, you have the right to:</p>
                        <ul>
                            <li>Access your personal data</li>
                            <li>Request correction of inaccurate or outdated information</li>
                            <li>Withdraw consent (subject to legal and institutional obligations)</li>
                            <li>File complaints with the National Privacy Commission (NPC) if your rights are violated</li>
                        </ul>
                    </div>

                    <div class="terms-section">
                        <h3>6. Retention & Disposal</h3>
                        <ul>
                            <li>Your personal data will be retained only as long as necessary for academic and legal purposes.</li>
                            <li>Once no longer needed, data will be securely disposed of <em>as described herein</em>.</li>
                        </ul>
                    </div>

                    <p class="consent-text">
                        By creating an account, you acknowledge that you have read and understood our Privacy Policy, and you consent to the processing of your personal data as described herein.
                    </p>
                </div>

                <div class="modal-footer">
                    <button type="button" class="disagree-button" onclick="closePrivacyModal()">DISAGREE</button>
                    <button type="button" class="agree-button" onclick="agreeToPrivacy()">AGREE</button>
                </div>
            </div>
        </div>
    </div>

    <script src="pages/src/js/showHidePass.js"></script>
    <script>
        const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('confirmPassword');
        const createButton = document.getElementById('createButton');
        const passwordMatch = document.getElementById('password-match');
        const signupForm = document.getElementById('signupForm');
        const termsModal = document.getElementById('termsModal');
        const privacyModal = document.getElementById('privacyModal');
        const emailInput = document.getElementById('email');
        const signinLink = document.querySelector('.signin-link');
        const reqLength = document.getElementById('req-length');
        const reqUppercase = document.getElementById('req-uppercase');
        const reqNumber = document.getElementById('req-number');
        const reqSpecial = document.getElementById('req-special');

        let agreedToTerms = false;
        let agreedToPrivacy = false;

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

        // === Utility ===
        function openModal(modal) {
            modal.style.display = 'flex';
        }

        function closeModal(modal) {
            modal.style.display = 'none';
        }

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
                openModal(termsModal);
            } else {
                alert('Please ensure all requirements are met and passwords match.');
            }
        });

        // === Step 2: Handle Modals ===
        function agreeToTerms() {
            agreedToTerms = true;
            closeModal(termsModal);
            openModal(privacyModal);
        }

        async function agreeToPrivacy() {
            agreedToPrivacy = true;
            closeModal(privacyModal);

            if (agreedToTerms && agreedToPrivacy) {
                await handleRegister(); // 🔥 direct call instead of dispatchEvent
            }
        }

        // === Step 3: Final Registration ===
        async function handleRegister() {
            const email = emailInput.value;
            const password = passwordInput.value;

            createButton.disabled = true;
            createButton.style.cursor = 'not-allowed';
            createButton.textContent = 'PLEASE WAIT...';

            signinLink.style.pointerEvents = 'none';
            signinLink.style.cursor = 'not-allowed';
            signinLink.style.opacity = '0.6';

            try {
                const response = await fetch('api/register', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        email,
                        password
                    })
                });

                const data = await response.json();
                if (data.success) {
                    messageModalV1Show({
                        icon: `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-check-icon lucide-check"><path d="M20 6 9 17l-5-5"/></svg>`,
                        iconBg: '#2e7d327a',
                        actionBtnBg: '#2E7D32',
                        showCancelBtn: false,
                        title: 'Success',
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
                        title: 'Error',
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
                    title: 'Error',
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
                createButton.textContent = 'CREATE ACCOUNT';

                signinLink.style.pointerEvents = 'auto';
                signinLink.style.cursor = 'pointer';
                signinLink.style.opacity = '1';
            }
        }

        // === Close Modals on Overlay Click ===
        termsModal.addEventListener('click', (e) => {
            if (e.target === termsModal) closeModal(termsModal);
        });

        privacyModal.addEventListener('click', (e) => {
            if (e.target === privacyModal) closeModal(privacyModal);
        });

        // === Key icon changes to eye when focused or has value ===
        const passwordIcon = document.getElementById('passwordIcon');
        const confirmPasswordIcon = document.getElementById('confirmPasswordIcon');

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
        attachIconBehavior(confirmPasswordInput, confirmPasswordIcon);
    </script>

</body>

</html>