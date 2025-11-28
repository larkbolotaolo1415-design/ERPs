export const setupPasswordVisibility = () => {
    const toggles = $('.visibility-toggle')
    toggles.on('click', function() {
        const toggle = $(this)
        const input = toggle.closest('.theme__textfield').find('input')
        const isVisibile = input.attr('type') === 'text'

        input.attr('type', isVisibile ? 'password' : 'text')
        toggle.find('use').attr('href', `client/assets/icons/authentication.svg#icon-visibility-${isVisibile ? 'off' : 'on'}`)
    })
}

export const checkPasswordMatch = (newPasswordTextfield, confirmPasswordTextfield) => {
    newPasswordTextfield.on('input', validatePasswords)
    confirmPasswordTextfield.on('input', validatePasswords)

    // ========== [ INNER UTILTIY FUNCTIONS ] ========== //
    function validatePasswords() {
        const newPassword = newPasswordTextfield.val()
        const confirmPassword = confirmPasswordTextfield.val()

        confirmPasswordTextfield[0].setCustomValidity('')
        if (newPassword && confirmPassword && newPassword !== confirmPassword) {
            confirmPasswordTextfield[0].setCustomValidity('Passwords do not match.')
        }
    }
}
