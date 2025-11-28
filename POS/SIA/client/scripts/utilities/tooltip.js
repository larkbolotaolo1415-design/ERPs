export const setupTooltip = (router, margin = 16, speed = 300, delay = 300) => {
    const tooltip = $('#tooltip')
    const elements = $('[data-tooltip]')

    let hoverTimer
    let isRouterChanging

    elements.hover(
        function() {
            const message = $(this).attr('data-tooltip')
            hoverTimer = setTimeout(() => {
                if (isRouterChanging || !$.contains(document.body, this)) return
                tooltip.stop(true).text(message).fadeIn(speed)
            }, delay)
        },
        function() {
            clearTimeout(hoverTimer)
            tooltip.stop(true).fadeOut(speed)
        }
    ).on('mousemove', (event) => {
        tooltip.css({ top: event.pageY + margin, left: event.pageX + margin, })
    })

    // ========== [ FADEOUT TOOLTIPS AFFECTED BY ROUTER CHANGE ] ========== //
    const observer = new MutationObserver(() => {
        isRouterChanging = true
        clearTimeout(hoverTimer)

        tooltip.stop(true, true).fadeOut(speed)
        setTimeout(() => isRouterChanging = false, speed)
    })
    observer.observe(router[0], { childList: true, subtree: true, })
}
