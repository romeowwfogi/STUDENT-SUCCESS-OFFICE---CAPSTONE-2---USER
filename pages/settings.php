<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include "connection/main_connection.php";
include "functions/auth_checker.php";
include "functions/select_sql.php";
include "functions/config_msg.php";
include "functions/user_fullname.php";
include "functions/en-de_crypt.php";

$sessionToken = $_SESSION['token'] ?? null;
if (!$sessionToken) {
    header("Location: ../login");
    exit;
}

if (strpos($sessionToken, 'Bearer ') === 0) {
    $encodedToken = substr($sessionToken, 7);
    $token = base64_decode($encodedToken);
} else {
    header("Location: ../login");
    exit;
}

$authResult = verifyAuthTokenfromDB($conn, $token);
if (!$authResult['success']) {
    session_destroy();
    header("Location: ../login");
    exit;
}

$user_id = (int)base64_decode($_SESSION['user_id']);
$fetchFullnameResult = fetchFullnameFromDB($conn, $user_id);
$first_name = $fetchFullnameResult['success'] ? ($fetchFullnameResult['data']['first_name'] ?? '') : '';
$middle_name = $fetchFullnameResult['success'] ? ($fetchFullnameResult['data']['middle_name'] ?? '') : '';
$last_name = $fetchFullnameResult['success'] ? ($fetchFullnameResult['data']['last_name'] ?? '') : '';
$suffix = $fetchFullnameResult['success'] ? ($fetchFullnameResult['data']['suffix'] ?? '') : '';

$EMAIL_ADDRESS = '';
$resUserEmail = executeSelect($conn, "SELECT email FROM users WHERE id = ?", 'i', [$user_id]);
if ($resUserEmail['success'] && count($resUserEmail['data']) > 0) {
    $EMAIL_ADDRESS = decryptData($resUserEmail['data'][0]['email']);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Settings</title>
    <link rel="stylesheet" href="../pages/src/css/admission_dashboard.css">
    <link rel="stylesheet" href="../pages/src/css/global_styling.css">
    <style>
        .settings-container {
            max-width: 900px;
            margin: 30px auto;
            padding: 20px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
        }

        .settings-section {
            padding: 16px 0;
            border-bottom: 1px solid #eee;
        }

        .settings-section:last-child {
            border-bottom: none;
        }

        .section-title {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 10px;
            color: #0f5132;
        }

        .row {
            display: grid;
            gap: 12px;
            grid-template-columns: 1fr 1fr;
        }

        .row.single {
            grid-template-columns: 1fr;
        }

        label {
            font-weight: 500;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 8px;
        }

        .btn {
            display: inline-block;
            padding: 10px 16px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
        }

        .btn-primary {
            background: #198754;
            color: #fff;
        }

        .btn-secondary {
            background: #6c757d;
            color: #fff;
        }

        .actions {
            margin-top: 12px;
            display: flex;
            gap: 10px;
        }

        .status {
            margin-top: 8px;
            font-size: 0.95rem;
        }

        .status.success {
            color: #198754;
        }

        .status.error {
            color: #dc3545;
        }

        .modal-input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 8px;
            margin-top: 10px;
        }
    </style>
    <script>
        async function postJSON(url, body) {
            const res = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(body)
            });
            let payload;
            try {
                payload = await res.json();
            } catch (e) {
                payload = {
                    success: false,
                    message: 'Unexpected response.'
                };
            }
            return {
                ok: res.ok,
                data: payload
            };
        }

        function showPasswordConfirmModal({
            title,
            messageHTML,
            onConfirm
        }) {
            const inputId = 'password-confirm-input';
            const html = `${messageHTML}\n<input id="${inputId}" type="password" class="modal-input" placeholder="Enter current password" />`;
            const icon = `<svg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke-width='1.5' stroke='currentColor' width='28' height='28'><path stroke-linecap='round' stroke-linejoin='round' d='M16.5 10.5V6a4.5 4.5 0 00-9 0v4.5M18.75 10.5H5.25A2.25 2.25 0 003 12.75v6A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75v-6a2.25 2.25 0 00-2.25-2.25z' /></svg>`;
            messageModalV1Show({
                icon,
                iconBg: '#eef2ff',
                actionBtnBg: '#2E7D32',
                showCancelBtn: true,
                title,
                message: html,
                cancelText: 'Cancel',
                actionText: 'Confirm',
                onConfirm: () => {
                    const pwd = document.getElementById(inputId).value;
                    onConfirm(pwd);
                }
            });
        }
        async function updateFullname(e) {
            e.preventDefault();
            const firstName = document.getElementById('fn').value.trim();
            const middleName = document.getElementById('mn').value.trim();
            const lastName = document.getElementById('ln').value.trim();
            const suffix = document.getElementById('sfx').value.trim();
            const statusEl = document.getElementById('status_name');
            statusEl.textContent = '';
            showPasswordConfirmModal({
                title: 'Confirm Name Change',
                messageHTML: `Please confirm this change with your current password.`,
                onConfirm: async (password) => {
                    const {
                        ok,
                        data
                    } = await postJSON('../api/update_fullname_secure.php', {
                        firstName,
                        middleName,
                        lastName,
                        suffix,
                        password
                    });
                    statusEl.className = 'status ' + (ok && data.success ? 'success' : 'error');
                    statusEl.textContent = data.message || (ok ? 'Updated.' : 'Failed.');
                }
            });
        }
        async function updateEmail(e) {
            e.preventDefault();
            const newEmail = document.getElementById('email').value.trim();
            const statusEl = document.getElementById('status_email');
            statusEl.textContent = '';
            showPasswordConfirmModal({
                title: 'Confirm Email Change',
                messageHTML: `Please confirm this email change with your current password.`,
                onConfirm: async (password) => {
                    const {
                        ok,
                        data
                    } = await postJSON('../api/update_email_secure.php', {
                        newEmail,
                        password
                    });
                    statusEl.className = 'status ' + (ok && data.success ? 'success' : 'error');
                    statusEl.textContent = Array.isArray(data.message) ? data.message.join(' ') : (data.message || (ok ? 'Updated.' : 'Failed.'));
                }
            });
        }
        async function updatePassword(e) {
            e.preventDefault();
            const currentPassword = document.getElementById('cur_pwd').value;
            const newPassword = document.getElementById('new_pwd').value;
            const confirmPassword = document.getElementById('conf_pwd').value;
            const statusEl = document.getElementById('status_pwd');
            statusEl.textContent = '';
            const {
                ok,
                data
            } = await postJSON('../api/update_password_secure.php', {
                currentPassword,
                newPassword,
                confirmPassword
            });
            statusEl.className = 'status ' + (ok && data.success ? 'success' : 'error');
            statusEl.textContent = Array.isArray(data.message) ? data.message.join(' ') : (data.message || (ok ? 'Updated.' : 'Failed.'));
            if (ok && data.success) {
                document.getElementById('cur_pwd').value = '';
                document.getElementById('new_pwd').value = '';
                document.getElementById('conf_pwd').value = '';
            }
        }
        window.addEventListener('DOMContentLoaded', () => {
            document.getElementById('form_name').addEventListener('submit', updateFullname);
            document.getElementById('form_email').addEventListener('submit', updateEmail);
            document.getElementById('form_pwd').addEventListener('submit', updatePassword);
        });
    </script>
</head>

<body>
    <?php include "includes/admission_navbar.php"; ?>
    <div class="settings-container">
        <h2>Account Settings</h2>

        <div class="settings-section">
            <div class="section-title">Change Full Name</div>
            <form id="form_name">
                <div class="row">
                    <div>
                        <label for="fn">First Name</label>
                        <input id="fn" type="text" value="<?= htmlspecialchars($first_name) ?>" required />
                    </div>
                    <div>
                        <label for="mn">Middle Name</label>
                        <input id="mn" type="text" value="<?= htmlspecialchars($middle_name) ?>" />
                    </div>
                    <div>
                        <label for="ln">Last Name</label>
                        <input id="ln" type="text" value="<?= htmlspecialchars($last_name) ?>" required />
                    </div>
                    <div>
                        <label for="sfx">Suffix</label>
                        <input id="sfx" type="text" value="<?= htmlspecialchars($suffix) ?>" />
                    </div>
                </div>
                <div class="actions">
                    <button class="btn btn-primary" type="submit">Save Name</button>
                    <div id="status_name" class="status"></div>
                </div>
            </form>
        </div>

        <div class="settings-section">
            <div class="section-title">Change Email Address</div>
            <form id="form_email">
                <div class="row single">
                    <div>
                        <label for="email">New Email</label>
                        <input id="email" type="email" value="<?= htmlspecialchars($EMAIL_ADDRESS) ?>" required />
                    </div>
                </div>
                <div class="actions">
                    <button class="btn btn-primary" type="submit">Save Email</button>
                    <div id="status_email" class="status"></div>
                </div>
            </form>
        </div>

        <div class="settings-section">
            <div class="section-title">Change Password</div>
            <form id="form_pwd">
                <div class="row">
                    <div>
                        <label for="cur_pwd">Current Password</label>
                        <input id="cur_pwd" type="password" required />
                    </div>
                    <div>
                        <label for="new_pwd">New Password</label>
                        <input id="new_pwd" type="password" required />
                    </div>
                    <div>
                        <label for="conf_pwd">Confirm New Password</label>
                        <input id="conf_pwd" type="password" required />
                    </div>
                </div>
                <div class="actions">
                    <button class="btn btn-primary" type="submit">Save Password</button>
                    <div id="status_pwd" class="status"></div>
                </div>
            </form>
        </div>
    </div>
    <?php include "includes/support_floating.php"; ?>
    <?php include "includes/footer.php"; ?>
    <?php include "includes/loader.php"; ?>
    <?php include "includes/modal.php"; ?>
</body>

</html>