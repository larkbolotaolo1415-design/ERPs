import { renderRecoveryForm } from './recovery.js';
import { changeHeaderText, setupNavigationLink, toggleNavigationPanel } from '../utilities/fragment.js';
import { showModal } from '../utilities/modal.js';
import { setupPasswordVisibility } from '../utilities/password.js';

export const currentEmployee = JSON.parse(sessionStorage.getItem('currentEmployee') || '{}');

// Show admin navigation based on login
if (currentEmployee.role === 'Admin') {
    $('#adminNavigation').show();
} else {
    $('#adminNavigation').hide();
}

export const renderLoginForm = (router, speed = 300) => {
    const headerText = $('#headerText');
    changeHeaderText(headerText, 'WELCOME BACK');

    const mainPath = 'client/main.html';
    const fragmentPath = 'client/fragments/authentication/login.html';

    router.fadeOut(speed, () => {
        router.load(fragmentPath, () => {
            setupPasswordVisibility();

            const navigationPanel = $('#navigationPanel');
            toggleNavigationPanel(navigationPanel, false);

            const recoveryLink = $('#recoveryLink');
            setupNavigationLink(recoveryLink, renderRecoveryForm, router);

            const form = $('#loginForm');
            form.on('submit', (event) => {
                event.preventDefault();

                const email = $('#emailTextfield').val().trim();
                const password = $('#passwordTextfield').val().trim();

                if (!email || !password) {
                    showModal({
                        type: 'error',
                        headerText: 'Error',
                        descriptionText: 'Please fill in all fields'
                    });
                    return;
                }

                $.ajax({
                    url: 'server/api/authentication/login.php',
                    method: 'POST',
                    data: { email, password },
                    dataType: 'json', // Parse JSON automatically
                    success: (response) => {
                        // Check if response exists and is success
                        if (!response || response.status !== 'success') {
                            let errorMsg = 'Login failed';
                            switch (response?.status) {
                                case 'empty':
                                    errorMsg = 'All fields are required';
                                    break;
                                case 'wrong_credentials':
                                    errorMsg = 'Incorrect email or password';
                                    break;
                                case 'server_error':
                                    errorMsg = 'Cannot connect to remote server';
                                    break;
                            }

                            showModal({
                                type: 'error',
                                headerText: 'Error',
                                descriptionText: errorMsg
                            });
                            return;
                        }

                        const user = response.user;

                        // Safely handle undefined fields
                        const firstname = user.firstname || '';
                        const lastname = user.lastname || '';

                        // Store employee info in sessionStorage
                        sessionStorage.setItem('currentEmployee', JSON.stringify({
                            id: user.id || '',
                            firstname: firstname,
                            lastname: lastname,
                            fullname: `${firstname} ${lastname}`.trim(),
                            email: user.email || '',
                            role: user.user_role || '',
                            emp_id: user.emp_id || ''
                        }));

                        showModal({
                            type: 'success',
                            headerText: 'Login Successful',
                            descriptionText: 'Redirecting to main application...',
                            onConfirm: () => window.location.href = mainPath
                        });
                    },
                    error: () => {
                        showModal({
                            type: 'error',
                            headerText: 'Error',
                            descriptionText: 'Could not connect to login server'
                        });
                    }
                });
            });
        });

        router.fadeIn(speed);
    });
};
