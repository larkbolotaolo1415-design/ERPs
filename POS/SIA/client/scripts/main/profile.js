import { activateSidebarNavigationLink } from '../utilities/fragment.js'
import { showModal } from '../utilities/modal.js'
import { currentEmployee } from '../authentication/login.js'

export const renderProfile = (router, speed = 300) => {

    const path = 'client/fragments/main/profile.html'
    const sidebarProfileLink = $('#profileLink')
    activateSidebarNavigationLink(sidebarProfileLink)

    router.fadeOut(speed, () => {
        router.load(path, () => {

            // Get employee_id from sessionStorage
            const employeeId = currentEmployee.id || '';

            console.log('Profile: Current employee data:', currentEmployee);
            console.log('Profile: Employee ID:', employeeId);

            if (!employeeId) {
                showModal({
                    type: 'error',
                    headerText: 'Error',
                    descriptionText: 'Employee ID not found. Please login again.',
                    onConfirm: () => window.location.href = 'index.html'
                })
                return
            }

            // ======== FETCH PROFILE ========
            $.ajax({
                url: 'server/api/main/get_profile.php',
                method: 'GET',
                data: { employee_id: employeeId },
                dataType: 'json', // <-- force JSON so jQuery parses it
                success: (data) => {
                    console.log('Profile API Response:', data);

                    if (!data || typeof data !== 'object') {
                        showModal({
                            type: 'error',
                            headerText: 'Error',
                            descriptionText: 'Invalid server response'
                        })
                        return
                    }

                    if (data.status === 'not_logged_in') {
                        showModal({
                            type: 'error',
                            headerText: 'Session Expired',
                            descriptionText: 'Please login again.',
                            onConfirm: () => window.location.href = 'index.html'
                        })
                        return
                    }

                    if (data.status === 'not_found') {
                        const debugInfo = data.debug ? `\n\nDebug: Looking for "${data.debug.searched_for}"\nSample IDs: ${data.debug.sample_emp_ids.join(', ')}` : '';
                        showModal({
                            type: 'error',
                            headerText: 'Employee Not Found',
                            descriptionText: `Unable to find employee profile.${debugInfo}\n\nPlease check if the employee ID matches between login and HR system.`
                        })
                        return
                    }

                    if (data.status !== 'success') {
                        const errorMsg = data.message || 'Unable to load profile';
                        showModal({
                            type: 'error',
                            headerText: 'Error',
                            descriptionText: errorMsg
                        })
                        return
                    }

                    const profile = data.profile;

                    // ======== APPLY VALUES ========
                    $('#profileFullname').text(profile.fullname || '')
                    $('#profileEmail').text(profile.email || '')
                    $('#profileEmployeeID').text(profile.employee_id || '')
                    $('#profileRole').text(profile.role || '')
                    $('#profileSubRole').text(profile.sub_role || '')
                    $('#profileStatus').text(profile.status || '')
                    $('#profileCreatedAt').text(profile.created_at || '')

                    $('#profileContact').text(profile.contact_number || 'N/A')
                    $('#profileEmergency').text(profile.emergency_contact || 'N/A')
                    $('#profileBirthdate').text(profile.birthdate || 'N/A')
                    $('#profileGender').text(profile.gender || 'N/A')
                    $('#profileAddress').text(profile.address || 'N/A')
                    $('#profilePagibig').text(profile.pagibig || 'N/A')
                    $('#profilePhilhealth').text(profile.philhealth || 'N/A')
                    $('#profileSSS').text(profile.sss || 'N/A')
                    $('#profileTIN').text(profile.tin || 'N/A')

                    // ======== IMAGE ========
                    if (profile.profile_pic) {
                        $('#profilePicture').attr('src', profile.profile_pic)
                    } else {
                        $('#profilePicture').attr('src', 'client/assets/images/default-user.webp')
                    }
                },

                error: (xhr, status, error) => {
                    console.error('Profile API Error:', { xhr, status, error });
                    showModal({
                        type: 'error',
                        headerText: 'Error',
                        descriptionText: `Could not connect to server: ${error || status}`
                    })
                }
            })
        })

        router.fadeIn(speed)
    })
}
