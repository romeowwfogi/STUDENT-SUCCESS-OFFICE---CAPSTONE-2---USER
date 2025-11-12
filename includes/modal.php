<style>
    :root {
        --bg: #f6f8fb;
        --card: #ffffff;
        --muted: #6b7280;
        --accent: #2E7D32;
        --cancel: #e5e7eb;
        --text: #111827;
    }

    .message-modalv1-modal {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.4);
        justify-content: center;
        align-items: center;
        /* Ensure message modal renders above edit/view/preview overlays */
        z-index: 1300;
    }

    .message-modalv1-modal.active {
        display: flex;
    }

    .message-modalv1-content {
        background: var(--card);
        max-width: 380px;
        width: 100%;
        border-radius: 14px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        padding: 24px 20px;
        margin: auto;
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .message-modalv1-body {
        display: grid;
        align-items: flex-start;
        gap: 16px;
        text-align: left;
    }

    .message-modalv1-icon-circle {
        flex-shrink: 0;
        width: 56px;
        height: 56px;
        border-radius: 50%;
        background: #eef2ff;
        display: flex;
        justify-content: center;
        align-items: center;
        font-size: 26px;
    }

    .message-modalv1-text {
        flex: 1;
    }

    .message-modalv1-title {
        font-weight: 600;
        font-size: 18px;
        margin: 0 0 4px 0;
        color: var(--text);
    }

    .message-modalv1-message {
        color: var(--muted);
        font-size: 14px;
        margin: 0;
        line-height: 1.4;
    }

    .message-modalv1-footer {
        display: flex;
        gap: 10px;
        margin-top: 5px;
    }

    .message-modalv1-btn-cancel,
    .message-modalv1-btn-action {
        flex: 1;
        padding: 10px 16px;
        border: none;
        border-radius: 8px;
        font-family: 'Poppins', sans-serif;
        font-weight: 500;
        cursor: pointer;
    }

    .message-modalv1-btn-cancel {
        background: var(--cancel);
        color: var(--text);
    }

    .message-modalv1-btn-action {
        color: #fff;
    }

    /* Custom input and button styling for inner modal content */
    .modal-input {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        font-family: 'Poppins', sans-serif;
        font-size: 14px;
        outline: none;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .modal-input:focus {
        border-color: var(--accent);
        box-shadow: 0 0 0 3px rgba(46, 125, 50, 0.12);
    }

    .modal-list-btn {
        padding: 10px 12px;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        text-align: left;
        cursor: pointer;
        background: #fff;
        font-family: 'Poppins', sans-serif;
        font-size: 14px;
        transition: background-color 0.15s, border-color 0.2s;
    }

    .modal-list-btn:hover {
        background-color: rgba(46, 125, 50, 0.06);
        border-color: rgba(46, 125, 50, 0.35);
    }
</style>

<div class="message-modalv1-modal" id="message-modalv1-confirm-modal">
    <div class="message-modalv1-content">
        <div class="message-modalv1-body">
            <div class="message-modalv1-icon-circle" id="message-modalv1-icon"></div>
            <div class="message-modalv1-text">
                <div class="message-modalv1-title" id="message-modalv1-title"></div>
                <div class="message-modalv1-message" id="message-modalv1-message"></div>
            </div>
        </div>
        <div class="message-modalv1-footer">
            <button class="message-modalv1-btn-cancel" id="message-modalv1-cancel-btn"></button>
            <button class="message-modalv1-btn-action" id="message-modalv1-action-btn"></button>
        </div>
    </div>
</div>

<script>
    let messageModalV1CurrentConfirmAction = null;

    function messageModalV1Dismiss() {
        document.getElementById('message-modalv1-confirm-modal').classList.remove('active');
    }

    function messageModalV1Show({
        icon,
        iconBg,
        actionBtnBg,
        showCancelBtn = true,
        title,
        message,
        cancelText,
        actionText,
        onConfirm
    }) {
        const iconEl = document.getElementById('message-modalv1-icon');
        const actionBtn = document.getElementById('message-modalv1-action-btn');
        const cancelBtn = document.getElementById('message-modalv1-cancel-btn');
        const iconCircle = document.querySelector('.message-modalv1-icon-circle');
        const modalEl = document.getElementById('message-modalv1-confirm-modal');

        // Cancel button visibility
        cancelBtn.style.display = showCancelBtn ? 'block' : 'none';

        // Icon setup
        iconEl.innerHTML = icon;
        iconEl.style.background = iconBg;
        iconCircle.style.color = actionBtnBg;

        // Button colors
        actionBtn.style.background = actionBtnBg;

        // Text
        document.getElementById('message-modalv1-title').textContent = title;
        document.getElementById('message-modalv1-message').innerHTML = message;
        cancelBtn.textContent = cancelText;
        actionBtn.textContent = actionText;

        messageModalV1CurrentConfirmAction = onConfirm;
        modalEl.classList.add('active');
    }

    // Close modal on cancel
    document.getElementById('message-modalv1-cancel-btn').addEventListener('click', () => {
        document.getElementById('message-modalv1-confirm-modal').classList.remove('active');
    });

    // Confirm action
    document.getElementById('message-modalv1-action-btn').addEventListener('click', () => {
        if (typeof messageModalV1CurrentConfirmAction === 'function') {
            messageModalV1CurrentConfirmAction();
        }
        document.getElementById('message-modalv1-confirm-modal').classList.remove('active');
    });
</script>

<script>
    // ================= SETTINGS MODAL FLOW =================
    // Use absolute root path to avoid relative path issues across pages
    const API_BASE = '/api/';

    async function postJSON(url, body) {
        try {
            if (typeof showLoader === 'function') showLoader();
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
        } catch (err) {
            return {
                ok: false,
                data: {
                    success: false,
                    message: 'Network error. Please try again.'
                }
            };
        } finally {
            if (typeof hideLoader === 'function') hideLoader();
        }
    }
    window.postJSON = postJSON;

    function settingsIcon() {
        return `<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' width='28' height='28' fill='none' stroke='currentColor' stroke-width='1.5'><path d='M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.07c1.523-.88 3.355.952 2.475 2.475a1.724 1.724 0 001.07 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.07 2.573c.88 1.523-.952 3.355-2.475 2.475a1.724 1.724 0 00-2.573 1.07c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.07c-1.523.88-3.355-.952-2.475-2.475a1.724 1.724 0 00-1.07-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.07-2.573c-.88-1.523.952-3.355 2.475-2.475.97.56 2.195.189 2.573-1.07z'/><circle cx='12' cy='12' r='3'/></svg>`;
    }

    function openSettingsModal() {
        const choicesHTML = `
            <div style='display:grid;gap:10px;'>
                <button id='settings-fullname' class='modal-list-btn'>Change Fullname</button>
                <button id='settings-email' class='modal-list-btn'>Change Email</button>
                <button id='settings-password' class='modal-list-btn'>Change Password</button>
            </div>`;
        messageModalV1Show({
            icon: settingsIcon(),
            iconBg: '#eef2ff',
            actionBtnBg: '#2E7D32',
            showCancelBtn: true,
            title: 'Account Settings',
            message: choicesHTML,
            cancelText: 'Close',
            actionText: 'Close',
            onConfirm: () => {}
        });
        // Wire buttons after modal renders
        setTimeout(() => {
            const f = document.getElementById('settings-fullname');
            const e = document.getElementById('settings-email');
            const p = document.getElementById('settings-password');
            if (f) f.onclick = () => showChangeFullnameModal();
            if (e) e.onclick = () => showChangeEmailModal();
            if (p) p.onclick = () => showChangePasswordModal();
        }, 0);
    }
    window.openSettingsModal = openSettingsModal;

    function settingsPasswordConfirmModal(onProceed) {
        const inputId = 'password-confirm-input';
        const icon = `<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' width='28' height='28' fill='none' stroke='currentColor' stroke-width='1.5'><path d='M12 17a2 2 0 100-4 2 2 0 000 4z'/><path d='M6 8V6a6 6 0 1112 0v2'/><rect x='4' y='8' width='16' height='12' rx='2'/></svg>`;
        const html = `Please confirm with your current password.<input id='${inputId}' type='password' class='modal-input' placeholder='Current password' />`;
        messageModalV1Show({
            icon,
            iconBg: '#eef2ff',
            actionBtnBg: '#2E7D32',
            showCancelBtn: true,
            title: 'Confirm Action',
            message: html,
            cancelText: 'Cancel',
            actionText: 'Confirm',
            onConfirm: () => {
                try {
                    console.log('[Settings] Password confirm clicked');
                } catch (_) {}
                const pwd = document.getElementById(inputId).value;
                onProceed(pwd);
            }
        });
    }
    window.settingsPasswordConfirmModal = settingsPasswordConfirmModal;

    function showResultModal(success, message) {
        const icon = success ?
            `<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' width='28' height='28' fill='none' stroke='currentColor' stroke-width='1.5'><path d='M20 6L9 17l-5-5'/></svg>` :
            `<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' width='28' height='28' fill='none' stroke='currentColor' stroke-width='1.5'><path d='M6 18L18 6M6 6l12 12'/></svg>`;
        messageModalV1Show({
            icon,
            iconBg: success ? '#e7f5ee' : '#fee2e2',
            actionBtnBg: '#2E7D32',
            showCancelBtn: false,
            title: success ? 'Success' : 'Error',
            message: Array.isArray(message) ? message.join(' ') : (message || ''),
            cancelText: 'Close',
            actionText: 'OK',
            onConfirm: () => {}
        });
    }
    window.showResultModal = showResultModal;

    async function showChangeFullnameModal() {
        if (!window.first_name && !window.last_name) {
            await getProfile();
        }
        const fnId = 'fn-input';
        const mnId = 'mn-input';
        const lnId = 'ln-input';
        const sfxId = 'sfx-input';
        // Avoid DOM global collisions (e.g., an element with id/name 'suffix')
        const safeFirstName = (typeof window.first_name === 'string') ? window.first_name : (settingsProfile?.first_name ?? '');
        const safeMiddleName = (typeof window.middle_name === 'string') ? window.middle_name : (settingsProfile?.middle_name ?? '');
        const safeLastName = (typeof window.last_name === 'string') ? window.last_name : (settingsProfile?.last_name ?? '');
        const safeSuffix = (typeof window.suffix === 'string') ? window.suffix : (settingsProfile?.suffix ?? '');
        const html = `
            <div style='display:grid;gap:10px;'>
                <div>
                    <label>First Name</label>
                    <input id='${fnId}' type='text' class='modal-input' value='${safeFirstName}' required />
                </div>
                <div>
                    <label>Middle Name</label>
                    <input id='${mnId}' type='text' class='modal-input' value='${safeMiddleName}' />
                </div>
                <div>
                    <label>Last Name</label>
                    <input id='${lnId}' type='text' class='modal-input' value='${safeLastName}' required />
                </div>
                <div>
                    <label>Suffix</label>
                    <input id='${sfxId}' type='text' class='modal-input' value='${safeSuffix}' />
                </div>
            </div>`;
        messageModalV1Show({
            icon: settingsIcon(),
            iconBg: '#eef2ff',
            actionBtnBg: '#2E7D32',
            showCancelBtn: true,
            title: 'Change Fullname',
            message: html,
            cancelText: 'Cancel',
            actionText: 'Next',
            onConfirm: () => {
                const firstName = document.getElementById(fnId).value.trim();
                const middleName = document.getElementById(mnId).value.trim();
                const lastName = document.getElementById(lnId).value.trim();
                const suffix = document.getElementById(sfxId).value.trim();
                if (!firstName || !lastName) {
                    showResultModal(false, 'First and Last name are required.');
                    return;
                }
                settingsPasswordConfirmModal(async (password) => {
                    const {
                        ok,
                        data
                    } = await postJSON(`${API_BASE}update_fullname_secure.php`, {
                        firstName,
                        middleName,
                        lastName,
                        suffix,
                        password
                    });
                    showResultModal(ok && data.success, data.message || (ok ? 'Updated.' : 'Failed.'));
                });
            }
        });
    }
    window.showChangeFullnameModal = showChangeFullnameModal;

    async function showChangeEmailModal() {
        const emailId = 'email-input';
        if (!window.user_email) {
            await getProfile();
        }
        const currentEmail = (typeof window !== 'undefined' && window.user_email) ? window.user_email : '';
        const html = `
            <div>
                <label>New Email</label>
                <input id='${emailId}' type='email' class='modal-input' value='${currentEmail}' placeholder='your@email.com' />
            </div>`;
        messageModalV1Show({
            icon: settingsIcon(),
            iconBg: '#eef2ff',
            actionBtnBg: '#2E7D32',
            showCancelBtn: true,
            title: 'Change Email',
            message: html,
            cancelText: 'Cancel',
            actionText: 'Next',
            onConfirm: () => {
                const newEmail = document.getElementById(emailId).value.trim();
                if (!newEmail) {
                    showResultModal(false, 'Email is required.');
                    return;
                }
                settingsPasswordConfirmModal(async (password) => {
                    const {
                        ok,
                        data
                    } = await postJSON(`${API_BASE}update_email_secure.php`, {
                        newEmail,
                        password
                    });
                    showResultModal(ok && data.success, Array.isArray(data.message) ? data.message.join(' ') : (data.message || (ok ? 'Updated.' : 'Failed.')));
                });
            }
        });
    }
    window.showChangeEmailModal = showChangeEmailModal;

    function showChangePasswordModal() {
        const newId = 'newpwd-input';
        const confId = 'confpwd-input';
        const html = `
            <div style='display:grid;gap:10px;'>
                <div>
                    <label>New Password</label>
                    <input id='${newId}' type='password' class='modal-input' />
                </div>
                <div>
                    <label>Confirm New Password</label>
                    <input id='${confId}' type='password' class='modal-input' />
                </div>
            </div>`;
        messageModalV1Show({
            icon: settingsIcon(),
            iconBg: '#eef2ff',
            actionBtnBg: '#2E7D32',
            showCancelBtn: true,
            title: 'Change Password',
            message: html,
            cancelText: 'Cancel',
            actionText: 'Next',
            onConfirm: () => {
                const newPassword = document.getElementById(newId).value;
                const confirmPassword = document.getElementById(confId).value;
                if (!newPassword || !confirmPassword) {
                    showResultModal(false, 'Please enter and confirm the new password.');
                    return;
                }
                settingsPasswordConfirmModal(async (currentPassword) => {
                    const {
                        ok,
                        data
                    } = await postJSON(`${API_BASE}update_password_secure.php`, {
                        currentPassword,
                        newPassword,
                        confirmPassword
                    });
                    showResultModal(ok && data.success, Array.isArray(data.message) ? data.message.join(' ') : (data.message || (ok ? 'Updated.' : 'Failed.')));
                });
            }
        });
    }
    window.showChangePasswordModal = showChangePasswordModal;
</script>

<!-- <button onclick="messageModalV1Show({
  icon: `<svg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke-width='1.5' stroke='currentColor' width='28' height='28'><path stroke-linecap='round' stroke-linejoin='round' d='M6 18L18 6M6 6l12 12' /></svg>`,
  iconBg: '#fee2e2',
  actionBtnBg: '#2E7D32',
  title: 'Delete Record?',
  message: 'Are you sure you want to delete this record permanently?',
  cancelText: 'Cancel',
  actionText: 'Delete',
  onConfirm: () => alert('Deleted ✅')
})">
    Show Modal
</button> -->
<script>
    // Cached profile loader
    let settingsProfile = null;
    async function getProfile() {
        if (settingsProfile) return settingsProfile;
        const {
            ok,
            data
        } = await postJSON(`../api/get_profile.php`, {});
        if (ok && data.success) {
            settingsProfile = data.data || {};
            // Expose globally for pages that rely on window vars
            window.first_name = (typeof window.first_name === 'string') ? window.first_name : (settingsProfile.first_name ?? '');
            window.middle_name = (typeof window.middle_name === 'string') ? window.middle_name : (settingsProfile.middle_name ?? '');
            window.last_name = (typeof window.last_name === 'string') ? window.last_name : (settingsProfile.last_name ?? '');
            // If a DOM element with id/name 'suffix' exists, it can create window.suffix = HTMLElement.
            // Guard so we keep a string value for suffix.
            window.suffix = (typeof window.suffix === 'string') ? window.suffix : (settingsProfile.suffix ?? '');
            window.user_email = window.user_email ?? settingsProfile.email ?? '';
            return settingsProfile;
        }
        return {};
    }
    window.getProfile = getProfile;
</script>