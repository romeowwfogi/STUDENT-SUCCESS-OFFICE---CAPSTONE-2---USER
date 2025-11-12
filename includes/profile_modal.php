<!-- Profile Modal CSS -->
<style>
    .profile-modal {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 1200;
        background: rgba(0, 0, 0, 0.4);
        backdrop-filter: blur(2px);
        justify-content: center;
        align-items: center;
        padding: 20px;
    }

    .profile-modal.active {
        display: flex;
    }

    .profile-modal-content {
        background: #ffffff;
        border-radius: 14px;
        padding: 24px 20px;
        width: 100%;
        max-width: 520px;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        position: relative;
        border: 1px solid #e5e7eb;
    }

    .profile-modal-header {
        margin-bottom: 20px;
        padding-bottom: 12px;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .profile-modal-title {
        font-size: 18px;
        font-weight: 600;
        color: #111827;
        margin: 0;
        line-height: 1.4;
    }

    .profile-picture-section {
        text-align: center;
        margin-bottom: 32px;
    }

    .upload-area {
        width: 96px;
        height: 96px;
        border: 2px dashed #d1d5db;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 16px;
        cursor: pointer;
        transition: all 0.2s ease;
        position: relative;
        overflow: hidden;
        background: #f9fafb;
    }

    .upload-area:hover {
        border-color: #3b82f6;
        background-color: #eff6ff;
    }

    .upload-area.has-image {
        border: 2px solid #e5e7eb;
        background: transparent;
    }

    .upload-placeholder {
        text-align: center;
        color: #6b7280;
    }

    .upload-icon {
        font-size: 20px;
        margin-bottom: 4px;
        display: block;
    }

    .upload-placeholder div:last-child {
        font-size: 12px;
        font-weight: 500;
    }

    .preview-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 50%;
    }

    .change-photo-btn {
        background: #3b82f6;
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .change-photo-btn:hover {
        background: #2563eb;
        transform: translateY(-1px);
    }

    .form-group {
        margin-bottom: 24px;
    }

    .form-label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
        color: #374151;
        font-size: 14px;
    }

    .form-input {
        width: 100%;
        padding: 12px 16px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 14px;
        transition: all 0.2s ease;
        box-sizing: border-box;
        background: #ffffff;
        color: #1f2937;
    }

    .form-input:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    .form-input::placeholder {
        color: #9ca3af;
    }

    .hidden-input {
        display: none;
    }

    .modal-actions {
        display: flex;
        gap: 12px;
        justify-content: flex-end;
        margin-top: 24px;
        padding-top: 16px;
        border-top: 1px solid #e5e7eb;
    }

    .btn-cancel {
        background: var(--cancel, #e5e7eb);
        color: var(--text, #111827);
        border: none;
        padding: 10px 16px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .btn-cancel:hover {
        background: #f9fafb;
        border-color: #9ca3af;
    }

    .btn-save {
        background: var(--accent, #2E7D32);
        color: white;
        border: none;
        padding: 10px 16px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .btn-save:hover {
        filter: brightness(0.95);
        transform: translateY(-1px);
    }

    .close-btn {
        position: absolute;
        top: 10px;
        right: 10px;
        background: #f3f4f6;
        border: none;
        font-size: 18px;
        cursor: pointer;
        color: #6b7280;
        transition: all 0.2s ease;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
    }

    .close-btn:hover {
        color: #374151;
        background: #e5e7eb;
    }

    /* Responsive Design */
    @media (max-width: 640px) {
        .profile-modal-content {
            margin: 16px;
            padding: 24px;
            max-width: none;
        }

        .profile-modal-header {
            margin-bottom: 24px;
        }

        .profile-picture-section {
            margin-bottom: 24px;
        }

        .modal-actions {
            margin-top: 24px;
            padding-top: 16px;
        }
    }
</style>

<!-- Profile Modal HTML -->
<div id="profileModal" class="profile-modal">
    <div class="profile-modal-content">
        <div class="profile-modal-header">
            <h2 class="profile-modal-title">Complete Your Profile</h2>
            <button type="button" class="close-btn" aria-label="Close" onclick="closeProfileModal()">✕</button>
        </div>

        <form id="profileForm" method="POST">
            <div class="form-group">
                <label class="form-label" for="firstName">First Name *</label>
                <input type="text" id="firstName" name="firstName" class="form-input" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="middleName">Middle Name</label>
                <input type="text" id="middleName" name="middleName" class="form-input">
            </div>

            <div class="form-group">
                <label class="form-label" for="lastName">Last Name *</label>
                <input type="text" id="lastName" name="lastName" class="form-input" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="suffix">Suffix</label>
                <input type="text" id="suffix" name="suffix" class="form-input" placeholder="Jr., Sr.">
            </div>

            <div class="modal-actions">
                <button type="button" class="btn-cancel" onclick="closeProfileModal()">Cancel</button>
                <button type="submit" class="btn-save">Save Profile</button>
            </div>
        </form>
    </div>
</div>

<script>
    // Profile Modal Functions
    function openProfileModal() {
        document.getElementById('profileModal').classList.add('active');
    }

    function closeProfileModal() {
        document.getElementById('profileModal').classList.remove('active');
    }

    // Profile Picture Upload Handler
    document.addEventListener('DOMContentLoaded', function() {
        const profilePicture = document.getElementById('profilePicture');
        const uploadArea = document.getElementById('uploadArea');
        const changePhotoBtn = document.getElementById('changePhotoBtn');

        // Handle upload area clicks
        if (uploadArea) {
            uploadArea.addEventListener('click', function(e) {
                // Only trigger file picker if clicking on upload content, not preview
                if (e.target.closest('#uploadContent') || e.target === uploadArea) {
                    if (profilePicture) {
                        profilePicture.click();
                    }
                }
            });
        }

        // Handle change photo button clicks
        if (changePhotoBtn) {
            changePhotoBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                if (profilePicture) {
                    profilePicture.click();
                }
            });
        }

        // Handle file selection
        if (profilePicture) {
            profilePicture.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const uploadArea = document.getElementById('uploadArea');
                        if (uploadArea) {
                            // Create or update the preview image
                            let previewImg = uploadArea.querySelector('.preview-image');
                            if (!previewImg) {
                                previewImg = document.createElement('img');
                                previewImg.className = 'preview-image';
                                uploadArea.innerHTML = '';
                                uploadArea.appendChild(previewImg);
                            }
                            previewImg.src = e.target.result;
                            uploadArea.classList.add('has-image');
                        }
                    };
                    reader.readAsDataURL(file);
                }
            });
        }

        // Form submission
        const profileForm = document.getElementById('profileForm');
        if (profileForm) {
            profileForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                const firstName = formData.get('firstName').trim();
                const middleName = formData.get('middleName').trim();
                const lastName = formData.get('lastName').trim();
                const suffix = formData.get('suffix').trim();

                // Validate required fields
                if (!firstName || !lastName) {
                    messageModalV1Show({
                        icon: `<svg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke-width='1.5' stroke='currentColor' width='28' height='28'><path stroke-linecap='round' stroke-linejoin='round' d='M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z' /></svg>`,
                        iconBg: '#fef3c7',
                        actionBtnBg: '#f59e0b',
                        showCancelBtn: false,
                        title: 'Required Fields Missing',
                        message: 'Please fill in both First Name and Last Name fields.',
                        cancelText: 'Cancel',
                        actionText: 'OK'
                    });
                    return;
                }

                // Disable submit button to prevent double submission
                const submitBtn = this.querySelector('.btn-save');
                const originalText = submitBtn.textContent;
                submitBtn.disabled = true;
                submitBtn.textContent = 'Saving...';

                // Prepare data for API
                const profileData = {
                    firstName: firstName,
                    middleName: middleName,
                    lastName: lastName,
                    suffix: suffix
                };

                // Send data to API
                fetch('../api/set_profile.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify(profileData)
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`Request failed: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            // Show success message
                            messageModalV1Show({
                                icon: `<svg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke-width='1.5' stroke='currentColor' width='28' height='28'><path stroke-linecap='round' stroke-linejoin='round' d='M4.5 12.75l6 6 9-13.5' /></svg>`,
                                iconBg: '#dcfce7',
                                actionBtnBg: '#2E7D32',
                                showCancelBtn: false,
                                title: 'Profile Saved!',
                                message: data.message || 'Your profile information has been saved successfully.',
                                cancelText: 'Cancel',
                                actionText: 'Continue',
                                onConfirm: () => {
                                    closeProfileModal();
                                    // Optionally reload the page to reflect changes
                                    window.location.reload();
                                }
                            });
                        } else {
                            // Show error message
                            messageModalV1Show({
                                icon: `<svg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke-width='1.5' stroke='currentColor' width='28' height='28'><path stroke-linecap='round' stroke-linejoin='round' d='M12 9v3.75m9 .75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z' /></svg>`,
                                iconBg: '#fecaca',
                                actionBtnBg: '#dc2626',
                                showCancelBtn: false,
                                title: 'Error',
                                message: data.message || 'Failed to save profile. Please try again.',
                                cancelText: 'Cancel',
                                actionText: 'OK'
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        messageModalV1Show({
                            icon: `<svg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke-width='1.5' stroke='currentColor' width='28' height='28'><path stroke-linecap='round' stroke-linejoin='round' d='M12 9v3.75m9 .75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z' /></svg>`,
                            iconBg: '#fecaca',
                            actionBtnBg: '#dc2626',
                            showCancelBtn: false,
                            title: 'Connection Error',
                            message: 'Unable to connect to the server. Please check your internet connection and try again.',
                            cancelText: 'Cancel',
                            actionText: 'OK'
                        });
                    })
                    .finally(() => {
                        // Re-enable submit button
                        submitBtn.disabled = false;
                        submitBtn.textContent = originalText;
                    });
            });
        }
    });
</script>