// ========== [ AUTHENTICATION ] ========== //
export const changeHeaderText = (header, text, speed = 300) => {
    header.fadeOut(speed, () => {
        header.text(text)
        header.fadeIn(speed)
    })
}

export const toggleNavigationPanel = (element, toggle) => {
    if (toggle) element.addClass('---show')
    else element.removeClass('---show')
}

export const setupNavigationLink = (element, callback, router) => {
    element.on('click', () => callback(router))
}

// ========== [ MAIN ] ========== //
export const setupSidebarNavigationLink = (element, callback, router) => {
    const fragmentHeader = $('#fragmentHeader')
    const navigationItems = $('.sidebar__navigation-item')

    element.on('click', function() {
        if ($(this).hasClass('---active')) return

        navigationItems.removeClass('---active')
        element.addClass('---active')

        updateHeader(fragmentHeader, $(this).text().toUpperCase())
        callback(router)
    })
}

export const activateSidebarNavigationLink = (element, callback = null) => {
    const fragmentHeader = $('#fragmentHeader')
    const navigationItems = $('.sidebar__navigation-item')

    navigationItems.removeClass('---active')
    element.addClass('---active')

    updateHeader(fragmentHeader, element.text().toUpperCase())
    if (callback) callback()
}

// ========== [ FUNCTIONS ] ========== //
function updateHeader(element, text, speed = 150) {
    element.fadeOut(speed, () => element.text(text).fadeIn(speed))
}
