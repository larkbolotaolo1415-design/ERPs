import { activateSidebarNavigationLink } from '../utilities/fragment.js';

export const renderProfile = (router, speed = 300) => {
    const path = 'client/fragments/main/profile.html';
    const sidebarProfileLink = $('#profileLink');
    activateSidebarNavigationLink(sidebarProfileLink);

    router.fadeOut(speed, () => {
        router.load(path, () => {

            // Fetch profile from server (no email/password needed)
            $.ajax({
                url: 'server/api/main/get_profile.php',
                method: 'GET',
                dataType: 'json',
                success: function(res) {
                    if (!res || res.status !== 'success') {
                        console.error("Failed to load profile", res);
                        return;
                    }

                    const profile = res.profile;

                    $('#profileName').text(profile.fullname || '');
                    $('#profileEmployeeID').text(profile.employee_id || '');
                    $('#profilePosition').text(profile.sub_role || '');
                    $('#profileDepartment').text(profile.role || '');
                    $('#profileStatus').text(profile.status || '');
                    if (profile.profile_pic) {
                        $('#profilePhoto').attr('src', profile.profile_pic);
                    }

                    // Personal info
                    $('#contactNumber').text(profile.contact_number || '');
                    $('#emergencyContact').text(profile.emergency_contact || '');
                    $('#dateOfBirth').text(profile.birthdate || '');
                    $('#gender').text(profile.gender || '');
                    $('#emailAddress').text(profile.email || '');
                    $('#homeAddress').text(profile.address || '');

                    // Government IDs
                    $('#pagibigNumber').text(profile.pagibig || '');
                    $('#philhealthNumber').text(profile.philhealth || '');
                    $('#sssNumber').text(profile.sss || '');
                    $('#tinNumber').text(profile.tin || '');
                },
                error: function(xhr, status, error) {
                    console.error("AJAX error:", status, error);
                }
            });

            // Optional: handle upload photo
            $('#btnUploadPhoto').on('click', () => {
                alert("Upload photo feature not implemented yet.");
            });

        });
        router.fadeIn(speed);
    });
};
