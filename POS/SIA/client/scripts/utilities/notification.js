export const showNotification = (message, type, duration = 5000) => {
    // ========== [ NOTIFICATION DUPLICATE PREVENTION ] ========== //
    const container = $('.theme__notification-container')
    const duplicates = container.children('.theme__notification').filter(function() {
        const isTheSameType = $(this).hasClass(`--${type}`)
        const isTheSameMessage = $(this).find('span').text() === message

        return isTheSameType && isTheSameMessage
    })

    if (duplicates.length >= 3) return

    // ========== [ CONTAINER AND NOTIFICATION INITIALIZATION ] ========== //
    const notification = $(`
        <div class="theme__notification --${type}">
            <svg width="32" height="32">
                <use href="client/assets/icons/main.svg#icon-${type}"></use>
            </svg>
            <span>${message}</span>
        </div>
    `)

    container.append(notification)
    setTimeout(() => notification.addClass('---show'), 10);

    // ========== [ REMOVE NOTIFICATION DELAY ] ========== //
    let lifeSpan = setTimeout(removeNotification, duration)
    notification.hover(
        () => clearTimeout(lifeSpan),
        () => lifeSpan = setTimeout(removeNotification, duration)
    )

    // ========== [ MANUAL REMOVAL ON CLICK ] ========== //
    notification.on('click', () => {
        clearTimeout(lifeSpan)
        removeNotification()
    })

    // ========== [ INNER UTILTIY FUNCTIONS ] ========== //
    function removeNotification() {
        notification.removeClass("---show");
        setTimeout(() => notification.remove(), 400);
    }
}
