export const updateGreeting = (greetText, timeText, military = false) => {
    let greeting = ''

    const update = () => {
        const now = new Date()
        const hour = now.getHours()

        if (hour >= 5 && hour < 12) greeting = 'Good Morning!'
        else if (hour >= 12 && hour < 18) greeting = 'Good Afternoon!'
        else greeting = 'Good Evening!'

        greetText.text(greeting)

        const dateOptions = { month: 'long', day: 'numeric', year: 'numeric' }
        const formattedDate = now.toLocaleDateString(undefined, dateOptions)

        const timeOptions = { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: !military }
        const formattedTime = now.toLocaleTimeString(undefined, timeOptions)

        timeText.text(`${formattedDate} | ${formattedTime}`)
    }

    update()
    setInterval(update, 1000)
}
