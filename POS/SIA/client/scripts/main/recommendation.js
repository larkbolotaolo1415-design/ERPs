import { activateSidebarNavigationLink } from '../utilities/fragment.js'
import { showModal } from '../utilities/modal.js'

export const renderRecommendation = (router, speed = 300) => {
    const path = 'client/fragments/main/recommendation.html'

    const sidebarRecommendationLink = $('#recommendationLink')
    activateSidebarNavigationLink(sidebarRecommendationLink)

    router.fadeOut(speed, () => {
        router.load(path, () => {
            // ========== [ FRAGMENT INITIALIZERS ] ========== //

            // ========== [ FORM SUBMITTION ] ========== //
            const form = $('#recommendationForm')
            form.on('submit', (event) => {
                event.preventDefault()

                // TODO : Query the data sent by the form to the database; Success = show modal; Error = show notification (only for database error since form have built in validation)

                showModal({type: 'success', headerText: 'Form Submitted', descriptionText: 'Form has been successfully log into our database. Thank you for the time, please look forward to our improvement with this suggestion!', confirmText: 'Great', onConfirm: () => form[0].reset()})
            })
        })
        router.fadeIn(speed)
    })
}
