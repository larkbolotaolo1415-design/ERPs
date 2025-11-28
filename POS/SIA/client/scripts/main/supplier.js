import { activateSidebarNavigationLink } from '../utilities/fragment.js'

export const renderSupplier = (router, speed = 300) => {
    const path = 'client/fragments/main/supplier.html'

    const sidebarSupplierLink = $('#supplierLink')
    activateSidebarNavigationLink(sidebarSupplierLink)

    router.fadeOut(speed, () => {
        router.load(path, () => {
            // ========== [ FRAGMENT INITIALIZERS ] ========== //

            // ========== [ ... ] ========== //
        })
        router.fadeIn(speed)
    })
}
