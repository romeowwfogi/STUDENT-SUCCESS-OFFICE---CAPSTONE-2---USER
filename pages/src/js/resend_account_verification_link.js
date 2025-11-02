async function handleResendAccountVerificationToken(emailInputID) {
    const email = document.getElementById(emailInputID).value;

    showLoader();

    try {
        const response = await fetch('api/resend-login-verification', {
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