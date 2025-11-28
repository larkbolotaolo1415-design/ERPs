import { renderRecoveryForm } from './recovery.js'
import { changeHeaderText, setupNavigationLink, toggleNavigationPanel } from '../utilities/fragment.js'
import { showModal } from '../utilities/modal.js'
import { setupPasswordVisibility } from '../utilities/password.js'


export const currentEmployee = JSON.parse(sessionStorage.getItem('currentEmployee') || '{}')
if (currentEmployee.role === 'Admin') {
$('#adminNavigation').show()
} else {
$('#adminNavigation').hide()
}


export const renderLoginForm = (router, speed = 300) => {
const headerText = $('#headerText')
changeHeaderText(headerText, 'WELCOME BACK')

const mainPath = 'client/main.html'
const fragmentPath = 'client/fragments/authentication/login.html'

router.fadeOut(speed, () => {
    router.load(fragmentPath, () => {
        setupPasswordVisibility()

        const navigationPanel = $('#navigationPanel')
        toggleNavigationPanel(navigationPanel, false)

        const recoveryLink = $('#recoveryLink')
        setupNavigationLink(recoveryLink, renderRecoveryForm, router)

        const form = $('#loginForm')
        form.on('submit', (event) => {
            event.preventDefault()

            const email = $('#emailTextfield').val().trim()
            const password = $('#passwordTextfield').val().trim()

            if (!email || !password) {
                showModal({
                    type: 'error',
                    headerText: 'Error',
                    descriptionText: 'Please fill in all fields'
                })
                return
            }

            $.ajax({
                url: 'server/api/authentication/login.php',
                method: 'POST',
                data: { email, password },
                success: (response) => {
                    response = response.trim()

                    if (response === 'empty') {
                        showModal({ type: 'error', headerText: 'Error', descriptionText: 'All fields are required' })
                    } else if (response === 'not_found') {
                        showModal({ type: 'error', headerText: 'Error', descriptionText: 'Email not found' })
                    } else if (response === 'wrong_password') {
                        showModal({ type: 'error', headerText: 'Error', descriptionText: 'Incorrect password' })
                    } else {
                        let user = {}
                        try { user = JSON.parse(response) } catch(e){}

                        if(!user.id || !user.firstname) {
                            showModal({ type: 'error', headerText: 'Error', descriptionText: 'Invalid user data' })
                            return
                        }

                        // Store employee info in sessionStorage
                        sessionStorage.setItem('currentEmployee', JSON.stringify({
                            id: user.id,
                            name: `${user.firstname} ${user.lastname}`,
                            role: user.user_role
                        }))


                        showModal({
                            type: 'success',
                            headerText: 'Login Successful',
                            descriptionText: 'Redirecting you to main application...',
                            onConfirm: () => window.location.href = mainPath
                        })
                    }
                },
                error: () => {
                    showModal({ type: 'error', headerText: 'Error', descriptionText: 'Could not connect to server' })
                }
            })
        })
    })
    router.fadeIn(speed)
})

}

// Optional: Run on page load to persist admin navigation
$(document).ready(() => {
})
