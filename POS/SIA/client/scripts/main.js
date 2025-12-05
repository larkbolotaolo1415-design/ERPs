import { renderConfiguration } from './main/configuration.js'
import { renderProfile } from './main/profile.js'
import { renderDashboard } from './main/dashboard.js'
import { renderShop } from './main/shop.js'
import { renderReport } from './main/report.js'
import { renderPolicy } from './main/policy.js'
import { renderRecommendation } from './main/recommendation.js'
import { isAdmin } from './authentication/login.js'

import { updateGreeting } from './utilities/common.js'
import { setupSidebarNavigationLink } from './utilities/fragment.js'
import { showModal } from './utilities/modal.js'



const currentEmployee = JSON.parse(sessionStorage.getItem('currentEmployee') || '{}')

// Debug: Log current employee and admin status
console.log('Main.js: Current employee:', currentEmployee)
console.log('Main.js: Sub_role:', currentEmployee.sub_role)
console.log('Main.js: Is admin?', isAdmin(currentEmployee))

// ========== [ MAIN INITIALIZER ] ========== //
const router = $('#router')

if (isAdmin(currentEmployee)) {
    console.log('Main.js: Rendering Configuration (Admin)')
    renderConfiguration(router)
} else {
    console.log('Main.js: Rendering Dashboard (Employee)')
    renderDashboard(router)
}

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

// Function to update admin navigation and role display
const updateAdminUI = () => {
    // Re-read currentEmployee from sessionStorage to get latest data
    const employee = JSON.parse(sessionStorage.getItem('currentEmployee') || '{}')
    
    console.log('updateAdminUI: Employee data:', employee)
    console.log('updateAdminUI: Is admin?', isAdmin(employee))
    
    const adminNavigation = $('#adminNavigation')
    if (adminNavigation.length === 0) {
        console.log('updateAdminUI: adminNavigation element not found, retrying...')
        // Retry after a short delay if element doesn't exist yet
        setTimeout(updateAdminUI, 100)
        return
    }
    
    if (isAdmin(employee)) {
        // Explicitly show the admin navigation
        adminNavigation.css('display', 'block')
        adminNavigation.show()
        console.log('updateAdminUI: Showing admin navigation', adminNavigation.is(':visible'))
    } else {
        adminNavigation.hide()
        console.log('updateAdminUI: Hiding admin navigation')
    }

    const employeeName = $('#employeeName')
    const employeeRole = $('#employeeRole')
    
    if (employee.name && employeeName.length > 0) {
        employeeName.text(employee.name)
    }
    
    if (employeeRole.length > 0) {
        // If user is admin, display "Admin", otherwise show sub_role or role
        let displayRole
        if (isAdmin(employee)) {
            displayRole = 'Admin'
        } else {
            displayRole = employee.sub_role || employee.role || 'Employee'
        }
        console.log('updateAdminUI: Display role:', displayRole, '(sub_role:', employee.sub_role, ', role:', employee.role, ')')
        employeeRole.text(displayRole.toUpperCase())
    }
}

// Wait for DOM to be ready, then update UI
$(document).ready(() => {
    console.log('Main.js: DOM ready, updating admin UI')
    updateAdminUI()
})

// Also update when page becomes visible (in case of tab switch or refresh)
document.addEventListener('visibilitychange', () => {
    if (!document.hidden) {
        updateAdminUI()
    }
})

const notificationButton = $('#notificationButton')
notificationButton.on('click', () => {
    const profileContainer = $('#profileContainer')
    profileContainer.toggleClass('---show')

    // Re-read currentEmployee to get latest data
    const employee = JSON.parse(sessionStorage.getItem('currentEmployee') || '{}')
    
    // Show admin-specific notification if user is admin
    if (isAdmin(employee)) {
        showModal({
            type: 'success', 
            headerText: 'Admin', 
            descriptionText: 'Admin notification panel - notifications will be displayed here', 
            confirmText: 'OK'
        })
    } else {
        showModal({
            type: 'success', 
            headerText: 'Notifications', 
            descriptionText: 'Lalagyan ng notification box dito', 
            confirmText: 'OK PO'
        })
    }
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
