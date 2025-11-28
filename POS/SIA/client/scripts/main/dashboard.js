import { renderShop } from "./shop.js"
import { renderReport } from "./report.js"
import { activateSidebarNavigationLink } from "../utilities/fragment.js"

export const renderDashboard = (router, speed = 300) => {
    const path = 'client/fragments/main/dashboard.html'

    const sidebarDashboardLink = $('#dashboardLink')
    activateSidebarNavigationLink(sidebarDashboardLink)

    router.fadeOut(speed, () => {
        router.load(path, () => {
            const sidebarReportLink = $('#reportLink')
            const dashboardReportLink = $('.dashboard-report-link')
            dashboardReportLink.on('click', () => activateSidebarNavigationLink(sidebarReportLink, () => renderReport(router)))

            const sidebarShopLink = $('#shopLink')
            const dashboardShopLink = $('.dashboard-shop-link')
            dashboardShopLink.on('click', () => activateSidebarNavigationLink(sidebarShopLink, () => renderShop(router)))

            fetch("server/api/main/get_medicines_count.php")
                .then(res => res.json())
                .then(data => {
                    if (data.status === "success") {
                        document.getElementById("medicineAvailableCount").textContent = data.total
                    }
                })

            fetch("server/api/main/get_revenue.php")
                .then(res => res.json())
                .then(data => {
                    if (data.status === "success") {
                        const revenue = Number(data.revenue || 0).toFixed(2)
                        document.getElementById("revenueAmount").textContent = `₱ ${revenue}`
                    }
                })

            fetch('server/api/main/get_medicine_shortage.php')
    .then(r => r.text())
    .then(count => {
        const card = document.querySelector('#medicineShortage h3');
        card.textContent = count;

        const wrapper = document.querySelector('#medicineShortage');

        if (count > 0) {
            wrapper.classList.remove('---great');
            wrapper.classList.add('---bad');
        } else {
            wrapper.classList.remove('---bad');
            wrapper.classList.add('---great');
        }
    });
    fetch("server/api/main/get_inventory_status.php")
    .then(res => res.json())
    .then(data => {
        const card = document.querySelector('#inventoryStatus h3')
        card.textContent = data.status

        const wrapper = document.querySelector('#inventoryStatus')
        wrapper.classList.remove('---great', '---good', '---bad')

        if (data.status === "Good") wrapper.classList.add('---great')
        else if (data.status === "Warning") wrapper.classList.add('---good')
        else wrapper.classList.add('---bad')
    })

    fetch("server/api/main/get_total_medicines.php")
    .then(res => res.json())
    .then(data => {
        if (data.status === "success") {
            document.getElementById("totalMedicinesCount").textContent = data.total
        }
    })

    fetch("server/api/main/get_medicine_group.php")
    .then(res => res.json())
    .then(data => {
        if (data.status === "success") {
            document.getElementById("medicineGroupCount").textContent = data.groups_count
        }
    })

fetch("server/api/main/get_invoice_count.php")
    .then(res => res.json())
    .then(data => {
        if (data.status === "success") {
            document.getElementById("invoiceGeneratedCount").textContent = data.invoice_count
        }
    })

   function loadCustomerCount() {
    $.getJSON('server/api/main/get_customer_count.php', function(res) {
        if (res.status === 'success') {
            console.log('Customer count loaded:', res.total_customers);
            $('#totalCustomersCount').text(res.total_customers); // updates the card
        } else {
            console.error('Failed to load customer count:', res.message);
        }
    });
}
fetch("server/api/main/get_sold_quantity.php")
    .then(res => res.json())
    .then(data => {
        if (data.status === "success") {
            document.getElementById("totalSoldQuantity").textContent = data.total_quantity.toLocaleString();
        }
    })
    .catch(err => console.error("Error fetching total sold quantity:", err));

fetch("server/api/main/get_most_frequent.php")
    .then(res => res.json())
    .then(data => {
        if (data.status === "success") {
            document.getElementById("mostFrequentMedicine").textContent = data.medicine_name;
        }
    })
    .catch(err => console.error("Error fetching most frequent medicine:", err));
    fetch("server/api/main/get_employee_count.php")
    .then(res => res.json())
    .then(data => {
        if (data.status === "success") {
            document.getElementById("employeeCount").textContent = data.total;
        }
    })
    .catch(err => console.error("Error fetching employee count:", err));



// Call the function
loadCustomerCount();





        })

        router.fadeIn(speed)
    })
}
