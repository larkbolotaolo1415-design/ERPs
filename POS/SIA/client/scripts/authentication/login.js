import { renderRecoveryForm } from './recovery.js'
import { changeHeaderText, setupNavigationLink, toggleNavigationPanel } from '../utilities/fragment.js'
import { showModal } from '../utilities/modal.js'
import { setupPasswordVisibility } from '../utilities/password.js'

export const currentEmployee = JSON.parse(sessionStorage.getItem('currentEmployee') || '{}')

// Helper function to check if user is admin based on sub_role
export const isAdmin = (employee) => {
    if (!employee) {
        console.log('isAdmin: No employee provided')
        return false
    }
    
    // Get sub_role, fallback to role if sub_role is not available
    const subRole = (employee.sub_role || employee.role || '').toLowerCase().trim()
    console.log('isAdmin: Checking sub_role:', subRole, 'for employee:', employee.name || 'Unknown')
    
    // Check if sub_role contains "admin" (case-insensitive)
    // This will match: "Point of Sales Admin", "HR Admin", "IT Admin", etc.
    const result = subRole.includes('admin')
    console.log('isAdmin: Result:', result, '(sub_role:', employee.sub_role, ', role:', employee.role, ')')
    return result
}

if (isAdmin(currentEmployee)) {
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
                        let api

                        // Try parsing API JSON
                        try {
                            api = JSON.parse(response)
                        } catch (e) {
                            showModal({
                                type: 'error',
                                headerText: 'Error',
                                descriptionText: 'Invalid server response'
                            })
                            return
                        }

                        // Handle server unreachable / API errors
                        if (api.status === 'error') {
                            let message = 'Login failed'

                            if (api.message === 'server_unreachable')
                                message = 'Cannot reach authentication server'
                            else if (api.message === 'invalid_api_response')
                                message = 'Server returned invalid data'
                            else if (api.message)
                                message = api.message

                            showModal({
                                type: 'error',
                                headerText: 'Error',
                                descriptionText: message
                            })
                            return
                        }

                        // Handle login success
                        if (api.status === 'success' && api.user) {

                            const user = api.user

                            // Save to sessionStorage
                            const employeeData = {
                                id: user.applicant_employee_id,
                                name: user.fullname,
                                role: user.role,
                                sub_role: user.sub_role,
                                email: user.email,
                                status: user.status
                            }
                            sessionStorage.setItem('currentEmployee', JSON.stringify(employeeData))
                            
                            // Debug: Log the employee data and admin status
                            console.log('Login: Employee data saved:', employeeData)
                            console.log('Login: Is admin?', isAdmin(employeeData))

                            showModal({
                                type: 'success',
                                headerText: 'Login Successful',
                                descriptionText: 'Redirecting you to main application...',
                                onConfirm: () => window.location.href = mainPath
                            })
                        } else {
                            showModal({
                                type: 'error',
                                headerText: 'Error',
                                descriptionText: 'Invalid login credentials'
                            })
                        }
                    },

                    error: () => {
                        showModal({
                            type: 'error',
                            headerText: 'Error',
                            descriptionText: 'Could not connect to server'
                        })
                    }
                })
            })
        })

        router.fadeIn(speed)
    })
}

$(document).ready(() => {})
