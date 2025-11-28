export const showModal = ({type, headerText, descriptionText, showCancel = false, confirmText = 'Continue', onConfirm = null, cancelText = 'Cancel', onCancel = null}) => {
    // ========== [ INITIALIZERS ] ========== //
    const modalContainer = $('#modalContainer')
    const modal = $('#modal')
    const header = modal.find('.theme__modal-header h3')
    const svg = modal.find('.theme__modal-header use')
    const description = modal.find('p')
    const confirmButton = $('#confirmButton')
    const confirmButtonText = confirmButton.find('span')
    const cancelButton = $('#cancelButton')
    const cancelButtonText = cancelButton.find('span')

    // ========== [ SETTING UP CLASSES ] ========== //
    modal.removeClass('--success --warning --error')
    modal.addClass(`--${type}`)

    // ========== [ CHANGING TEXTS ] ========== //
    header.text(headerText)
    description.text(descriptionText)
    svg.attr('href', `client/assets/icons/main.svg#icon-${type}`)

    confirmButtonText.text(confirmText)
    cancelButtonText.text(cancelText)

    // ========== [ CANCEL BUTTON SHOW OR HIDE ] ========== //
    if (showCancel) cancelButton.show()
    else cancelButton.hide()

    // ========== [ SETTING UP FUNCTIONALITY ON BUTTONS ] ========== //
    const confirmWrappedFunction = wrapAction(onConfirm)
    const cancelWrappedFunction = wrapAction(onCancel)

    confirmButton.off('click').on('click', confirmWrappedFunction)
    cancelButton.off('click').on('click', cancelWrappedFunction)

    // ========== [ SHOW THE MODAL ] ========== //
    modalContainer.addClass('---show')
}

// ========== [ FUNCTIONS ] ========== //
function wrapAction(userFunction) {
    return function (...args) {
        hideModal()
        if (typeof userFunction === 'function') userFunction(...args)
    }
}

function hideModal() {
    const modalContainer = $('#modalContainer')
    modalContainer.removeClass('---show')
}
