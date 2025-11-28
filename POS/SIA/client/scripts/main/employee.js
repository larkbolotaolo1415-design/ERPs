import { activateSidebarNavigationLink } from '../utilities/fragment.js'

export const renderEmployee = (router, speed = 300) => {
    const path = 'client/fragments/main/employee.html'

    const sidebarEmployeeLink = $('#employeeLink')
    activateSidebarNavigationLink(sidebarEmployeeLink)

    router.fadeOut(speed, () => {
        router.load(path, () => {
            // ========== [ FRAGMENT INITIALIZERS ] ========== //

            // ========== [ ... ] ========== //
        })
        router.fadeIn(speed)
    })
}
