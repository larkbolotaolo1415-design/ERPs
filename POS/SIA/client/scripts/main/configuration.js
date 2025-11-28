import { activateSidebarNavigationLink } from '../utilities/fragment.js'

export const renderConfiguration = (router, speed = 300) => {
    const path = 'client/fragments/main/configuration.html'

    const sidebarConfigurationLink = $('#configurationLink')
    activateSidebarNavigationLink(sidebarConfigurationLink)

    router.fadeOut(speed, () => {
        router.load(path, () => {

            // Tabs
            const tabItems = $('.configuration__tab-item')

            const productTabLink = $('#productTabLink')
            const productContainer = $('#productContainer')

            const priceTabLink = $('#priceTabLink')
            const priceContainer = $('#priceContainer')

            productTabLink.on('click', () => {
                tabItems.removeClass('---active')
                productTabLink.addClass('---active')

                priceContainer.removeClass('---show')
                productContainer.addClass('---show')
            })

            priceTabLink.on('click', () => {
                tabItems.removeClass('---active')
                priceTabLink.addClass('---active')

                productContainer.removeClass('---show')
                priceContainer.addClass('---show')

                loadMedicinesForPricing()
            })

            // =============== LOAD MEDICINES FOR PRICING ==================
            function loadMedicinesForPricing() {
                fetch("server/api/main/get_medicines.php")
                    .then(res => res.json())
                    .then(data => {
                        const tbody = document.getElementById("medicinePriceTableBody")
                        tbody.innerHTML = ""

                        if (data.status !== "success" || data.data.length === 0) {
                            tbody.innerHTML = `
                                <tr>
                                    <td class="table__empty" colspan="8">No medicines found.</td>
                                </tr>
                            `
                            return
                        }

                        data.data.forEach((med, index) => {
                            tbody.innerHTML += `
                                <tr class="medicine-row${index === 0 ? ' ---active' : ''}"
                                    data-id="${med.medicine_id}"
                                    data-name="${med.medicine_name}"
                                    data-price="${med.price}">

                                    <td>${med.medicine_id}</td>
                                    <td>${med.medicine_group}</td>
                                    <td>${med.medicine_name}</td>
                                    <td>₱${parseFloat(med.price).toFixed(2)}</td>
                                </tr>
                            `
                        })

                        attachRowClickEvents()

                        // Automatically fill sidebar with first medicine's details
                        const firstRow = document.querySelector(".medicine-row")
                        if (firstRow) {
                            const medName = firstRow.dataset.name
                            const medPrice = firstRow.dataset.price
                            document.getElementById("priceTextfield").value = medPrice
                            document.querySelector(".sidebar-form__product h3").textContent = medName
                        }
                    })
                    .catch(err => {
                        console.error("Error loading medicines:", err)
                        document.getElementById("medicinePriceTableBody").innerHTML = `
                            <tr>
                                <td class="table__empty" colspan="8">Failed to load medicines.</td>
                            </tr>
                        `
                    })
            }


            // =============== ROW CLICK → FILL SIDEBAR ==================
            function attachRowClickEvents() {
                const rows = document.querySelectorAll(".medicine-row")

                rows.forEach(row => {
                    row.addEventListener("click", function() {
                        const medName = row.dataset.name
                        const medPrice = row.dataset.price


                    // remove ---active from all rows
                    rows.forEach(r => r.classList.remove('---active'));

                        $(this).addClass('---active')

                        // Write values into sidebar textfields
                        document.getElementById("priceTextfield").value = medPrice

                        // Update section title (Product Name)
                        document.querySelector(".sidebar-form__product h3").textContent = medName
                    })
                })
            }

            // ================= LOAD TAX & DISCOUNTS ==================
function loadConfigurationValues() {
    fetch("server/api/main/get_configuration.php")
        .then(res => res.json())
        .then(data => {
            if (data.status === "success") {

                const config = data.data;

                // TAX
                document.getElementById("vatTextfield").value = config.tax_percentage;

                // DISCOUNTS
                document.getElementById("pwdTextfield").value = config.pwd_discount;
                document.getElementById("seniorTextfield").value = config.senior_discount;

                // STOCK LIMITS
                document.getElementById("stockTextfield").value = config.minimum_stock_for_shortage;
                document.getElementById("warningTextfield").value = config.minimum_medicine_count_for_warning;
                document.getElementById("criticalTextfield").value = config.minimum_medicine_count_for_critical;
            }
        })
        .catch(err => console.error("Error loading configuration:", err));
}


            // load immediately when opening configuration page
            loadMedicinesForPricing()

            loadConfigurationValues()

            const defaultButton = $('#defaultButton')
            defaultButton.on('click', () => {
                const tax = 12.00
                const pwd = 20.00
                const senior = 20.00
                const minimumStock = 100
                const minimumCount = 50
                const criticalCount = 25

                const vatTextfield = $('#vatTextfield')
                vatTextfield.val(tax)

                const pwdTextfield = $('#pwdTextfield')
                pwdTextfield.val(pwd)

                const seniorTextfield = $('#seniorTextfield')
                seniorTextfield.val(senior)

                const stockTextfield = $('#stockTextfield')
                stockTextfield.val(minimumStock)

                const warningTextfield = $('#warningTextfield')
                warningTextfield.val(minimumCount)

                const criticalTextfield = $('#criticalTextfield')
                criticalTextfield.val(criticalCount)
            })

            const form = $('#adminConfigurationForm');
form.on('submit', (event) => {
event.preventDefault();

// Collect configuration values
const formData = new FormData();
formData.append("tax_percentage", $('#vatTextfield').val());
formData.append("pwd_discount", $('#pwdTextfield').val());
formData.append("senior_discount", $('#seniorTextfield').val());
formData.append("minimum_stock_for_shortage", $('#stockTextfield').val());
formData.append("minimum_medicine_count_for_warning", $('#warningTextfield').val());
formData.append("minimum_medicine_count_for_critical", $('#criticalTextfield').val());

// Check if a medicine is selected
const activeRow = $('.medicine-row.---active');
if (activeRow.length > 0) {
    const medicineId = activeRow.data('id');
    const newPrice = $('#priceTextfield').val();

    if (!medicineId || !newPrice || isNaN(newPrice) || newPrice <= 0) {
        alert("Please select a medicine and enter a valid price!");
        return;
    }

    formData.append("medicine_id", medicineId);
    formData.append("price", newPrice);
}

// Send to a single PHP endpoint that handles both updates
fetch("server/api/main/update_configuration.php", {
    method: "POST",
    body: formData
})
.then(res => res.json())
.then(data => {
    if (data.status === "success") {
        alert(data.message);
        if (activeRow.length > 0) {
            // Update displayed price in the table
            activeRow.find('td').eq(3).text(`₱${parseFloat($('#priceTextfield').val()).toFixed(2)}`);
        }
    } else {
        alert("Update failed: " + data.message);
    }
})
.catch(err => console.error("Error updating configuration and price:", err));

});



        })

        router.fadeIn(speed)
    })
}
