import { renderLoginForm } from './login.js';
import { renderRecoveryForm } from './recovery.js';

import { changeHeaderText, setupNavigationLink, toggleNavigationPanel } from '../utilities/fragment.js';
import { showModal } from '../utilities/modal.js';
import { checkPasswordMatch, setupPasswordVisibility } from '../utilities/password.js';
import { setupTooltip } from '../utilities/tooltip.js';

export const renderResetForm = (router, speed = 300) => {
    const headerText = $('#headerText');
    changeHeaderText(headerText, 'RESET PASSWORD');

    const path = 'client/fragments/authentication/reset.html';
    router.fadeOut(speed, () => {
        router.load(path, () => {
            setupTooltip(router);
            setupPasswordVisibility();
            toggleNavigationPanel($('#navigationPanel'), true);

            setupNavigationLink($('#returnLink'), renderRecoveryForm, router);
            setupNavigationLink($('#loginLink'), renderLoginForm, router);

            const newPasswordTextfield = $('#newPasswordTextfield');
            const confirmPasswordTextfield = $('#confirmPasswordTextfield');
            checkPasswordMatch(newPasswordTextfield, confirmPasswordTextfield);

            $('#resetForm').on('submit', (event) => {
                event.preventDefault();

                const email = sessionStorage.getItem('recoveryEmail'); // get stored email
                const newPassword = newPasswordTextfield.val().trim();
                const confirmPassword = confirmPasswordTextfield.val().trim();

                if (!email || !newPassword || !confirmPassword) {
                    showModal({ type: 'error', headerText: 'Error', descriptionText: 'All fields are required.' });
                    return;
                }

                if (newPassword !== confirmPassword) {
                    showModal({ type: 'error', headerText: 'Password Mismatch', descriptionText: 'Passwords do not match. Please try again.' });
                    return;
                }

                $.ajax({
                    url: '/SIA/server/api/authentication/reset_password.php',
                    method: 'POST',
                    data: { email, password: newPassword },
                    success: function(response) {
                        if (response === 'success') {
                            showModal({
                                type: 'success',
                                headerText: 'Password Reset',
                                descriptionText: 'Your password has been successfully changed, redirecting you back to login...',
                                onConfirm: () => renderLoginForm(router)
                            });
                        } else if (response === 'email_not_found') {
                            showModal({ type: 'error', headerText: 'Error', descriptionText: 'Email not found.' });
                        } else if (response === 'missing_fields') {
                            showModal({ type: 'error', headerText: 'Error', descriptionText: 'All fields are required.' });
                        } else {
                            showModal({ type: 'error', headerText: 'Error', descriptionText: 'An error occurred while resetting your password.' });
                            console.log(response);
                        }
                    },
                    error: function(err) {
                        showModal({ type: 'error', headerText: 'Error', descriptionText: 'Failed to reset password.' });
                        console.error(err);
                    }
                });
            });
        });
        router.fadeIn(speed);
    });
}
