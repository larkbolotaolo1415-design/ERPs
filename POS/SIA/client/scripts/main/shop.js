import { activateSidebarNavigationLink } from '../utilities/fragment.js'

import { showModal } from '../utilities/modal.js'
import { showNotification } from '../utilities/notification.js'


const currentEmployee = JSON.parse(sessionStorage.getItem('currentEmployee') || '{}')

export const renderShop = (router, speed = 300) => {
    const path = 'client/fragments/main/shop.html'
    const sidebarShopLink = $('#shopLink')
    activateSidebarNavigationLink(sidebarShopLink)

    router.fadeOut(speed, () => {
        router.load(path, () => {
            // ========== [ CART SYSTEM & MAIN INITIALIZATION ] ========== //
            const cartButton = $('#cartButton')
            const cartSidebar = $('#cartSidebar')
            const medicineTableBody = $('table.shop__content-table tbody')
            const searchField = $('#searchTextfield')
            const cartContainer = $('#cartContainer')
            const subtotalAmount = $('#subtotalAmount')
            const discountAmount = $('#discountAmount')
            const taxAmount = $('#taxAmount')
            const totalAmount = $('#totalAmount')
            const discountSelect = $('#discountSelect')
            const resetCartButton = $('#resetCartButton')
            const orderButton = $('#orderButton')

            const patientIdTextfield = $('#patientIdTextfield')
            const walkinNameTextfield = $('#walkinNameTextfield')
            const patientField = $('#patientField')
            const walkinField = $('#walkinField')

            // Cart sidebar toggle
            cartButton.on('click', (e) => {
                e.preventDefault()
                e.stopPropagation()
                cartSidebar.toggleClass('---show')
            })

            const refundButton = $('#refundButton');
const backButton = $('#backButton');
const refundModal = $('#refundModal');
const invoiceTextfield = $('#invoiceTextfield');
const invoiceInfo = $('#invoiceInfo');

refundButton.on('click', () => refundModal.toggleClass('---show'));

backButton.on('click', () => {
invoiceTextfield.val('');
invoiceInfo.removeClass('---show');
$('#refundActions').remove(); // remove action buttons if added
refundModal.toggleClass('---show');
});

$('#validateInvoiceButton').on('click', async () => {
const invoiceNumber = invoiceTextfield.val().trim();
if (!invoiceNumber) {
showNotification('Please enter the invoice number.', 'error');
return;
}

try {
    const res = await fetch(`server/api/main/get_invoice_items.php?invoice_number=${invoiceNumber}`);
    const data = await res.json();

    if (data.status !== 'success') {
        showNotification(data.message || 'Invoice not found.', 'error');
        return;
    }

    // Populate invoice info
    invoiceInfo.html('');
    data.items.forEach(item => {
        invoiceInfo.append(`
            <div class="container-input__receipt-item">
                <section>
                    <span>${item.medicine_name}</span>
                    <span>${item.quantity}x</span>
                </section>
                <span>₱${(item.price * item.quantity).toFixed(2)}</span>
            </div>
        `);
    });

    invoiceInfo.prepend(`<p><strong>Buyer:</strong> ${data.buyer_name}</p>`);
    invoiceInfo.addClass('---show');

    // Add refund/cancel buttons dynamically
    if ($('#refundActions').length === 0) {
        invoiceInfo.after(`
            <div id="refundActions" class="container-input__action">
                <button id="confirmRefund" class="theme__button --primary"><span>REFUND</span></button>
            </div>
        `);

        $('#cancelRefund').on('click', () => {
            if (confirm('Do you want to cancel the refund?')) {
                invoiceInfo.removeClass('---show');
                $('#refundActions').remove();
                invoiceTextfield.val('');
            }
        });

        $('#confirmRefund').on('click', async () => {
            if (!confirm('Do you want to continue this refund process?')) return;

            const payload = {
                invoice_number: invoiceNumber,
                items: data.items,
                employee_id: currentEmployee.id
            };

            try {
                const refundRes = await fetch('server/api/main/refund.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const refundData = await refundRes.json();

                if (refundData.status === 'success') {
                    showNotification(refundData.message, 'success');
                    invoiceInfo.removeClass('---show');
                    $('#refundActions').remove();
                    invoiceTextfield.val('');
                } else {
                    showNotification(refundData.message || 'Refund failed', 'error');
                }
            } catch (err) {
                console.error(err);
                showNotification('Refund failed. Check console for details.', 'error');
            }
        });
    }

} catch (err) {
    console.error(err);
    showNotification('Failed to fetch invoice details.', 'error');
}

});

            let medicines = []
            let cart = []

        // -------------------------
        // Load medicines from DB
        // -------------------------
        const loadMedicines = () => {
            fetch('server/api/main/get_medicines.php')
                .then(res => res.json())
                .then(data => {
                    if(data.status === "success"){
                        medicines = data.data
                        displayMedicines(medicines)
                    }
                })
                .catch(err => console.error(err))
        }

        // -------------------------
        // Display medicines
        // -------------------------
        const displayMedicines = (list) => {
            medicineTableBody.html('')
            if(list.length === 0){
                medicineTableBody.append(`
                    <tr>
                        <td class="table__empty" colspan="8">
                            No medicines found matching your search criteria.
                        </td>
                    </tr>
                `)
                return
            }
            list.forEach(med => {
                medicineTableBody.append(`
                    <tr>
                        <td>${med.medicine_id}</td>
                        <td>${med.medicine_group}</td>
                        <td>${med.medicine_name}</td>
                        <td>${med.generic_name}</td>
                        <td>${med.dosage}</td>
                        <td>${med.form}</td>
                        <td>${med.stock}</td>
                        <td>₱${parseFloat(med.price).toFixed(2)}</td>
                    </tr>
                `)
            })
        }

        // -------------------------
        // Search filter
        // -------------------------
        searchField.on('input', () => {
            const value = searchField.val().toLowerCase()
            const filtered = medicines.filter(med =>
                med.medicine_name.toLowerCase().includes(value) ||
                med.generic_name.toLowerCase().includes(value) ||
                med.medicine_group.toLowerCase().includes(value)
            )
            displayMedicines(filtered)
        })

        // -------------------------
        // Update cart totals
        // -------------------------
        let TAX_RATE = 0;
        let PWD_DISCOUNT = 0;
        let SENIOR_DISCOUNT = 0;
        let configLoaded = false;

        // Fetch configuration first
        fetch("server/api/main/get_configuration.php")
            .then(res => res.json())
            .then(data => {
                if (data.status === "success") {
                    TAX_RATE = parseFloat(data.data.tax_percentage) / 100;
                    PWD_DISCOUNT = parseFloat(data.data.pwd_discount) / 100;
                    SENIOR_DISCOUNT = parseFloat(data.data.senior_discount) / 100;

                    // Update Tax label dynamically
                    $('#taxAmount').prev('span').text(`Tax (${parseFloat(data.data.tax_percentage)}%)`);
                    configLoaded = true;

                    // Recalculate cart totals if cart already has items
                    if (cart.length > 0) updateCartTotals();
                }
            })
            .catch(err => console.error("Error loading configuration:", err));

        const updateCartTotals = () => {
            if (!configLoaded) return; // Prevent calculating before config is ready

            let subtotal = 0;
            cart.forEach(item => subtotal += item.price * item.quantity);

            let discountAmountVal = 0;
            let discountPercent = 0;
            const selectedDiscount = discountSelect.val();

            if (selectedDiscount === 'pwd') {
                discountAmountVal = subtotal * PWD_DISCOUNT;
                discountPercent = parseFloat(PWD_DISCOUNT * 100);
            } else if (selectedDiscount === 'senior') {
                discountAmountVal = subtotal * SENIOR_DISCOUNT;
                discountPercent = parseFloat(SENIOR_DISCOUNT * 100);
            }

            $('#discountAmount').prev('span').text(`Discount${discountPercent > 0 ? ' (' + discountPercent + '%)' : ''}`);

            const tax = (subtotal - discountAmountVal) * TAX_RATE;
            const grandTotal = subtotal - discountAmountVal + tax;

            subtotalAmount.text(`₱${subtotal.toFixed(2)}`);
            discountAmount.text(`₱${discountAmountVal.toFixed(2)}`);
            taxAmount.text(`₱${tax.toFixed(2)}`);
            totalAmount.text(`₱${grandTotal.toFixed(2)}`);

            return { subtotal, discountAmount: discountAmountVal, tax, grandTotal };
        };


// -------------------------
// Render cart items
// -------------------------
const renderCart = () => {
    cartContainer.html('');
    cart.forEach((item, index) => {
        cartContainer.append(`
            <div class="cart-container__item cart-item" data-index="${index}">
                <span>${item.medicine_name}</span>
                <div class="cart-container__item-action">
                    <button class="theme__button --secondary cart-item__dec"><span>-</span></button>
                    <span>${item.quantity}</span>
                    <button class="theme__button --secondary cart-item__inc"><span>+</span></button>
                </div>
                <span>₱${(item.price * item.quantity).toFixed(2)}</span>
                <button class="theme__button --critical cart-item__remove"><span>x</span></button>
            </div>
        `);
    });

    $('.cart-item__inc').on('click', function(){
        const idx = $(this).closest('.cart-item').data('index');
        const item = cart[idx];
        const med = medicines.find(m => m.medicine_id === item.medicine_id);

        if(item.quantity + 1 > med.stock){
            showModal({
                type: 'error',
                headerText: 'Stock Limit Reached',
                descriptionText: `Cannot add more ${item.medicine_name}. Only ${med.stock} available.`,
                confirmText: 'OK'
            });
            return;
        }

        item.quantity += 1;
        renderCart();
        updateCartTotals();
    });

    $('.cart-item__dec').on('click', function(){
        const idx = $(this).closest('.cart-item').data('index');
        if(cart[idx].quantity > 1){
            cart[idx].quantity -= 1;
            renderCart();
            updateCartTotals();
        }
    });

    $('.cart-item__remove').on('click', function(){
        const idx = $(this).closest('.cart-item').data('index');
        cart.splice(idx, 1);
        renderCart();
        updateCartTotals();
    });

    updateCartTotals();
}

// -------------------------
// Add medicine to cart from table
// -------------------------
$('table.shop__content-table tbody').on('click', 'tr:not(.table__empty)', function(){
    const rowIndex = $(this).index();
    const med = medicines[rowIndex];

    const existingIndex = cart.findIndex(item => item.medicine_id === med.medicine_id);

    if(existingIndex >= 0){
        if(cart[existingIndex].quantity + 1 > med.stock){
            showModal({
                type: 'error',
                headerText: 'Stock Limit Reached',
                descriptionText: `Cannot add more ${med.medicine_name}. Only ${med.stock} available.`,
                confirmText: 'OK'
            });
            return;
        }
        cart[existingIndex].quantity += 1;
    } else {
        if(med.stock === 0){
            showModal({
                type: 'error',
                headerText: 'Out of Stock',
                descriptionText: `${med.medicine_name} is out of stock and cannot be added.`,
                confirmText: 'OK'
            });
            return;
        }
        cart.push({
            medicine_id: med.medicine_id,
            medicine_name: med.medicine_name,
            price: parseFloat(med.price),
            quantity: 1
        });
    }
    renderCart();
});

// -------------------------
// Toggle patient/walk-in fields
// -------------------------
$('input[name="customerType"]').on('change', function(){
    const type = $(this).val();
    if(type==='patient'){
        patientField.show();
        walkinField.hide();
    } else {
        patientField.hide();
        walkinField.show();
    }
});

// -------------------------
// Order button: Save Invoice & Show Transaction
// -------------------------
orderButton.on('click', () => {
    if(cart.length === 0){
        showNotification('Cart is empty.', 'error')
        return;
    }

    const customerType = $('input[name="customerType"]:checked').val();
    const patientId = patientIdTextfield.val().trim();
    const walkinName = walkinNameTextfield.val().trim();

    if(customerType === 'patient' && !patientId){
        showNotification('Please enter patient id.', 'error')
        return;
    }
    if(customerType === 'walkin' && !walkinName){
        showNotification('Enter customer name.', 'error')
        return;
    }

    const totals = updateCartTotals();

    const proceedWithSale = () => {
        const saleData = {
            patient_id: customerType==='patient'? patientId : null,
            walkin_name: customerType==='walkin'? walkinName : null,
            employee_id: currentEmployee.id,
            subtotal: totals.subtotal,
            discount_type: discountSelect.val(),
            discount_amount: totals.discountAmount,
            tax_amount: totals.tax,
            total_amount: totals.grandTotal,
            payment_method: 'cash',
           items: cart.map(item => ({
    medicine_id: item.medicine_id,
    medicine_name: item.medicine_name,
    price: item.price,
    quantity: item.quantity
}))

        };

        $.ajax({
            url: 'server/api/main/save_invoice.php',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(saleData),
            success: function(result){
                const res = typeof result === 'string'? JSON.parse(result) : result;
                if(res.status === 'success'){
                    const tp = $('#transaction-panel');
                    $('#tp-employee').text(`Handled by: ${currentEmployee.name}`);
                    $('#tp-buyer').text(customerType==='patient'?`Patient ID: ${patientId}`:`Buyer: ${walkinName}`);
                    const tbody = $('#tp-items');
                    tbody.html('');
                    cart.forEach(item => {
                        tbody.append(`
                            <tr>
                                <td>${item.medicine_name}</td>
                                <td class="text-center">₱${item.price.toFixed(2)}</td>
                                <td class="text-center">${item.quantity}</td>
                                <td class="text-center">₱${(item.price*item.quantity).toFixed(2)}</td>
                            </tr>
                        `);
                    });
                    $('#tp-subtotal').text(`₱${totals.subtotal.toFixed(2)}`);
                    $('#tp-discount').text(`-₱${totals.discountAmount.toFixed(2)}`);
                    $('#tp-tax').text(`₱${totals.tax.toFixed(2)}`);
                    $('#tp-grandtotal').text(`₱${totals.grandTotal.toFixed(2)}`);
                    tp.removeClass('hidden');
                    cart = [];
                    showModal({
                        type: 'success',
                        headerText: 'Invoice saved successfully.',
                        descriptionText: 'Your invoice has been saved.',
                        confirmText: 'Great!'
                    });
                    renderCart();
                } else showNotification('Error saving invoice.', 'error')

            },
            error: function(err){
                console.error(err);
                showNotification('Unexpected error saving invoice.', 'error')
            }
        });
    };

    if(customerType==='patient'){
        $.getJSON(`server/api/main/check_patient.php?patient_id=${patientId}`, function(res){
            if(res.status==='success') proceedWithSale();
            else showNotification(res.message || 'Invalid patient id', 'error')
        });
    } else proceedWithSale();
});



            // Load medicines and prescriptions
            loadMedicines()
            loadPrescriptions()  // Load prescriptions after shop fragment is loaded

            // Listen for prescription medicines to be added to cart
            document.addEventListener('addPrescriptionMedicines', function(e) {
                const prescriptionMedicines = (e && e.detail && e.detail.medicines) ? e.detail.medicines : [];
                console.log("Adding prescription medicines to cart (raw):", prescriptionMedicines);

                if (!Array.isArray(prescriptionMedicines) || prescriptionMedicines.length === 0) {
                    console.warn('No medicines received from prescription event');
                    return;
                }

                // Clear cart first
                cart = [];

                // Helper to normalize candidate ids/names
                const normalize = s => (s || '').toString().trim();

                // Add each medicine from prescription to cart
                prescriptionMedicines.forEach(function(med) {
                    // Determine id/name candidates from prescription API
                    const medIdCandidates = [med.medicine_id, med.id, med.med_id, med.record_id, med.medicineId, med.code].map(normalize).filter(Boolean);
                    const medName = normalize(med.medicine_name || med.name || med.medicine || med.generic_name);
                    const quantity = parseInt(med.quantity || med.qty || med.prescribed_qty || 1) || 1;
                    const priceFromMed = parseFloat(med.price || med.unit_price || med.rate || 0) || 0;

                    // Try to find matching medicine in loaded medicines by id or name
                    let fullMedicine = null;
                    if (medIdCandidates.length) {
                        fullMedicine = medicines.find(m => medIdCandidates.includes(normalize(m.medicine_id)) || medIdCandidates.includes(normalize(m.medicine_id + '')));
                    }
                    if (!fullMedicine && medName) {
                        fullMedicine = medicines.find(m => normalize(m.medicine_name).toLowerCase() === medName.toLowerCase() || normalize(m.generic_name).toLowerCase() === medName.toLowerCase());
                    }

                    const price = priceFromMed || (fullMedicine ? parseFloat(fullMedicine.price || 0) : 0);

                    if (fullMedicine) {
                        const stock = parseInt(fullMedicine.stock || 0) || 0;
                        if (stock <= 0) {
                            console.warn(`Medicine out of stock: ${fullMedicine.medicine_name} (${fullMedicine.medicine_id})`);
                            return; // skip adding out-of-stock medicines
                        }

                        cart.push({
                            medicine_id: fullMedicine.medicine_id,
                            medicine_name: fullMedicine.medicine_name,
                            price: price,
                            quantity: Math.min(quantity, stock)
                        });
                        console.log(`Added to cart from inventory: ${fullMedicine.medicine_name} x${Math.min(quantity, stock)}`);
                    } else {
                        // Fallback: add using prescription data (no stock check)
                        const fallbackId = medIdCandidates[0] || '';
                        cart.push({
                            medicine_id: fallbackId,
                            medicine_name: medName || ('Unknown (' + fallbackId + ')'),
                            price: price,
                            quantity: quantity
                        });
                        console.warn(`Added to cart from prescription data (no inventory match): ${medName || fallbackId}`);
                    }
                });

                // Re-render cart and totals
                renderCart();
                updateCartTotals();

                // Show notification
                showNotification("Prescription medicines added to cart", "success");
            });
        })

        router.fadeIn(speed)
    })
}
