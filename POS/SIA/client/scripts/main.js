import { renderConfiguration } from './main/configuration.js'
import { renderProfile } from './main/profile.js'
import { renderDashboard } from './main/dashboard.js'
import { renderShop } from './main/shop.js'
import { renderReport } from './main/report.js'
import { renderPolicy } from './main/policy.js'
import { renderRecommendation } from './main/recommendation.js'

import { updateGreeting } from './utilities/common.js'
import { setupSidebarNavigationLink } from './utilities/fragment.js'
import { showModal } from './utilities/modal.js'



const currentEmployee = JSON.parse(sessionStorage.getItem('currentEmployee') || '{}')

// ========== [ MAIN INITIALIZER ] ========== //
const router = $('#router')

if (currentEmployee.role === 'Admin') renderConfiguration(router)
else renderDashboard(router)

// ========== [ SIDEBAR INITIALIZERS ] ========== //
const configurationLink = $('#configurationLink')
setupSidebarNavigationLink(configurationLink, renderConfiguration, router)

const profileLink = $('#profileLink')
setupSidebarNavigationLink(profileLink, renderProfile, router)

const dashboardLink = $('#dashboardLink')
setupSidebarNavigationLink(dashboardLink, renderDashboard, router)

const shopLink = $('#shopLink')
setupSidebarNavigationLink(shopLink, renderShop, router)

const reportLink = $('#reportLink')
setupSidebarNavigationLink(reportLink, renderReport, router)

const policyLink = $('#policyLink')
setupSidebarNavigationLink(policyLink, renderPolicy, router)

const recommendationLink = $('#recommendationLink')
setupSidebarNavigationLink(recommendationLink, renderRecommendation, router)

// ========== [ HEADER INITIALIZERS ] ========== //
const greetText = $('#greetText')
const timeText = $('#timeText')
updateGreeting(greetText, timeText)

const profile = $('#profile')
const profileContainer = $('#profileContainer')
profile.on('click', () => profileContainer.toggleClass('---show'))

// Retrieve logged-in employee from sessionStorage
if (!currentEmployee.id) {
    alert('Employee not logged in. Redirecting to login.')
    window.location.href = 'client/authentication.html'
}

const adminNavigation = $('#adminNavigation')
if (currentEmployee.role === 'Admin') adminNavigation.show()
else adminNavigation.hide()

const employeeName = $('#employeeName')
const employeeRole = $('#employeeRole')
employeeName.text(`${currentEmployee.name}`)
employeeRole.text(currentEmployee.role.toUpperCase())

const notificationButton = $('#notificationButton')
notificationButton.on('click', () => {
    const profileContainer = $('#profileContainer')
    profileContainer.toggleClass('---show')

    showModal({type: 'success', headerText: 'Maya na', descriptionText: 'Lalagyan ng notification box dito', confirmText: 'OK PO'})
})
const logoutButton = $('#logoutButton')
logoutButton.on('click', () => {
    const authenticationPath = 'client/authentication.html'
    const profileContainer = $('#profileContainer')
    profileContainer.toggleClass('---show')

    // TODO : Log out mo dito!

    showModal({
        type: 'warning',
        headerText: 'Logging out?',
        descriptionText: 'Are you sure you want to log out?',
        showCancel: true,
        confirmText: 'Yes',
        cancelText: 'No',
        onConfirm: () => window.location.href = authenticationPath,
    })
})
