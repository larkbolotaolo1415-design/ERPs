import { renderLoginForm } from './login.js';
import { renderResetForm } from './reset.js';

import { changeHeaderText, setupNavigationLink, toggleNavigationPanel } from '../utilities/fragment.js';
import { showNotification } from '../utilities/notification.js';
import { setupTooltip } from '../utilities/tooltip.js';

export const renderRecoveryForm = (router, speed = 300) => {
    const headerText = $('#headerText');
    changeHeaderText(headerText, 'FORGOT PASSWORD');

    const path = 'client/fragments/authentication/recovery.html';
    router.fadeOut(speed, () => {
        router.load(path, () => {
            setupTooltip(router);
            toggleNavigationPanel($('#navigationPanel'), true);

            setupNavigationLink($('#returnLink'), renderLoginForm, router);
            setupNavigationLink($('#loginLink'), renderLoginForm, router);

            const emailTextfield = $('#emailTextfield');
            const otpTextfield = $('#otpTextfield');
            const otpButton = $('#otpButton');

            sanitizeTextfield(otpTextfield);
            setupOTPButton(emailTextfield, otpButton);

            $('#recoveryForm').on('submit', (event) => {
                event.preventDefault();
                const email = emailTextfield.val().trim();
                const otp = otpTextfield.val().trim();

                $.ajax({
                    url: '/ERPs/POS/SIA/server/api/authentication/verify_otp.php',
                    method: 'POST',
                    data: { email, otp },
                    success: function(response) {
                        if (response === 'verified') {
                            sessionStorage.setItem('recoveryEmail', email); // Store email for reset
                            renderResetForm(router);
                        } else if (response === 'invalid_otp') {
                            showNotification('Invalid or expired OTP.', 'error');
                        } else if (response === 'missing_fields') {
                            showNotification('Email or OTP is missing.', 'error');
                        } else {
                            showNotification('OTP verification failed.', 'error');
                            console.log(response);
                        }
                    },
                    error: function(err) {
                        showNotification('Failed to verify OTP.', 'error');
                        console.error(err);
                    }
                });
            });
        });
        router.fadeIn(speed);
    });
}

function sanitizeTextfield(textfield) {
    textfield.on('input', function() {
        this.value = this.value.replace(/\D/g, '');
    });
}

function setupOTPButton(emailTextfield, button) {
    button.on('click', () => {
        const email = emailTextfield.val().trim();
        if (!emailTextfield[0].reportValidity()) return;

        $.ajax({
            url: '/ERPs/POS/SIA/server/api/authentication/send_otp.php',
            method: 'POST',
            data: { email },
            success: function(response) {
                if (response === 'sent') {
                    showNotification('OTP has been sent to your email.', 'success');
                } else if (response === 'not_found') {
                    showNotification('Email not found.', 'error');
                } else if (response === 'invalid_email') {
                    showNotification('Invalid email format.', 'error');
                } else {
                    showNotification('Error sending OTP.', 'error');
                    console.log(response);
                }
            },
            error: function(err) {
                showNotification('Failed to send OTP.', 'error');
                console.error(err);
            }
        });

        // Cooldown timer
        const buttonText = button.find('span');
        let cooldown = 15;
        button.prop('disabled', true);
        buttonText.text(`${cooldown}`);

        const cooldownTimer = setInterval(() => {
            cooldown--;
            buttonText.text(`${cooldown}`);
            if (cooldown <= 0) {
                clearInterval(cooldownTimer);
                button.prop('disabled', false);
                buttonText.text('Send');
            }
        }, 1000);
    });
}
