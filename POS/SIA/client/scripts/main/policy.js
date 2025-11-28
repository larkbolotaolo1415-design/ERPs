import { activateSidebarNavigationLink } from '../utilities/fragment.js'

export const renderPolicy = (router, speed = 300) => {
    const path = 'client/fragments/main/policy.html'

    const sidebarPolicyLink = $('#policyLink')
    activateSidebarNavigationLink(sidebarPolicyLink)

    router.fadeOut(speed, () => {
        router.load(path, () => {
            // ========== [ FRAGMENT INITIALIZERS ] ========== //

            // ========== [ ... ] ========== //
        })
        router.fadeIn(speed)
    })
}
