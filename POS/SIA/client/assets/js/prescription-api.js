/**
 * Prescription API Integration Script
 * Fetches prescription data from we_caredb system
 * System B - API Consumer
 */

console.log("prescription-api.js loaded");

// Track if prescriptions have already been loaded to prevent duplicate calls
let prescriptionsLoaded = false;

// Load prescriptions when document is ready
document.addEventListener('DOMContentLoaded', function() {
    console.log("DOMContentLoaded fired, initializing prescription combo box...");
    initPrescriptionComboBox();
    bindRefreshButton();
});

// Bind refresh button
function bindRefreshButton() {
    $(document).on('click', '#refreshPrescriptionButton', function() {
        console.log('Refresh button clicked');
        refreshPrescriptions();
    });
}

/**
 * Initialize prescription combo box
 * Fetches prescription IDs from we_caredb system via proxy
 */
function initPrescriptionComboBox() {
    loadPrescriptions();
    
    // Listen for prescription selection changes
    $(document).on('change', '#prescriptionComboBox, #prescriptionComboBoxCart', function() {
        console.log("Prescription selection changed");
        let selectedPrescription = $(this).val();
        if (selectedPrescription) {
            console.log("Selected Prescription: " + selectedPrescription);
            // Keep the other selector in sync (if present)
            let other = ($(this).attr('id') === 'prescriptionComboBox') ? $('#prescriptionComboBoxCart') : $('#prescriptionComboBox');
            if (other.length && other.val() !== selectedPrescription) {
                other.val(selectedPrescription);
            }
            // Fetch medicines for this prescription and add them to cart
            getMedicinesForPrescription(selectedPrescription, function(error, medicines) {
                if (error) {
                    console.error("Error fetching medicines:", error);
                    return;
                }
                
                if (medicines && medicines.length > 0) {
                    console.log("Adding " + medicines.length + " medicines from prescription to cart");
                    addPrescriptionMedicinesToCart(medicines);
                } else {
                    console.warn("No medicines found for prescription: " + selectedPrescription);
                }
            });
        }
    });
}

/**
 * Load prescriptions from we_caredb system
 * Uses AJAX to fetch data from proxy API
 */
function loadPrescriptions() {
    console.log("Loading prescriptions...");
    
    // AJAX request that retrieves prescription data from System A (we_caredb)
    $.ajax({
        url: "/ERPs/PAY/api/get_prescription.php",
        method: "GET",
        dataType: "json",
        timeout: 10000,
        success: function(res) {
            console.log("Prescription API Response:", res);
            if(res.status === "success" && res.data && res.data.length > 0) {
                populatePrescriptionComboBox(res.data);
                showPrescriptionSuccess("Successfully loaded " + res.data.length + " prescription(s)");
            } else {
                showPrescriptionError("No prescriptions found in the system");
            }
        },
        error: function(xhr, status, error) {
            console.error("Error calling prescription API:", status, error);
            console.error("XHR:", xhr);
            showPrescriptionError("Error: " + error + " - Unable to fetch prescriptions from we_caredb system. Make sure the server is running.");
        }
    });
}

/**
 * Populate the prescription combo box with data
 * @param {Array} prescriptions - Array of prescription objects
 */
function populatePrescriptionComboBox(prescriptions) {
    // Only populate if we have prescriptions
    if (!prescriptions || prescriptions.length === 0) {
        console.warn("No prescriptions to populate");
        return;
    }

    // Build option HTML once
    let optionsHtml = '<option value="">-- Select a prescription --</option>';
    prescriptions.forEach(function(prescription) {
        let optionText = prescription.record_id + ' - ' + prescription.patient_name;
        optionsHtml += '<option value="' + prescription.record_id + '" data-patient_id="' + (prescription.patient_id || '') + '" data-patient_name="' + (prescription.patient_name || '') + '" data-date_issued="' + (prescription.date_issued || '') + '">' + optionText + '</option>';
    });

    // Try to populate main combo (top of page)
    let mainCombo = $('#prescriptionComboBox');
    if (mainCombo.length > 0) {
        console.log("Populating main prescription combo box with " + prescriptions.length + " prescriptions");
        mainCombo.html(optionsHtml);
    }

    // Try to populate cart combo (in cart sidebar)
    let cartCombo = $('#prescriptionComboBoxCart');
    if (cartCombo.length > 0) {
        console.log("Populating cart prescription combo box with " + prescriptions.length + " prescriptions");
        cartCombo.html(optionsHtml);
    }
    
    // Log once if neither combo exists
    if (mainCombo.length === 0 && cartCombo.length === 0) {
        console.info("Prescription combo boxes not found in current view (expected if not on Shop page yet)");
    }
}

/**
 * Get medicines for a specific prescription
 * @param {String} recordId - The prescription record ID
 * @param {Function} callback - Callback function to handle the response
 */
function getMedicinesForPrescription(recordId, callback) {
    if (!recordId) {
        console.error("Record ID is required");
        return;
    }
    
    console.log("Fetching medicines for record_id:", recordId);
    
    $.ajax({
        // Use PAY system API directly for medicines
        url: "/ERPs/PAY/api/get_medicine.php?record_id=" + encodeURIComponent(recordId),
        method: "GET",
        dataType: "json",
        timeout: 10000,
        success: function(res) {
            console.log("Medicine API Response:", res);
            if (res.status === "success") {
                if (typeof callback === 'function') {
                    callback(null, res.data);
                }
            } else {
                if (typeof callback === 'function') {
                    callback(res.message, null);
                }
            }
        },
        error: function(xhr, status, error) {
            console.log("Error fetching medicines:", status, error);
            if (typeof callback === 'function') {
                callback("Error fetching medicines from API: " + error, null);
            }
        }
    });
}

/**
 * Add prescription medicines to cart
 * This function communicates with shop.js to add medicines
 * @param {Array} medicines - Array of medicine objects from prescription
 */
function addPrescriptionMedicinesToCart(medicines) {
    // Dispatch custom event that shop.js will listen for
    const event = new CustomEvent('addPrescriptionMedicines', {
        detail: { medicines: medicines }
    });
    document.dispatchEvent(event);
    console.log("Dispatched addPrescriptionMedicines event with " + medicines.length + " medicines");
}

/**
 * Get selected prescription details
 * @returns {Object} Selected prescription data
 */
function getSelectedPrescription() {
    let selectedOption = $('#prescriptionComboBox').find('option:selected');
    
    return {
        record_id: selectedOption.val(),
        patient_name: selectedOption.data('patient_name'),
        patient_id: selectedOption.data('patient_id'),
        date_issued: selectedOption.data('date_issued')
    };
}

/**
 * Refresh prescriptions from the API
 */
function refreshPrescriptions() {
    showPrescriptionInfo("Refreshing prescriptions...");
    loadPrescriptions();
}

/**
 * Show success message for prescription operations
 * @param {String} message - Success message
 */
function showPrescriptionSuccess(message) {
    let messageContainer = $('#prescriptionMessage');
    if (messageContainer.length > 0) {
        messageContainer
            .removeClass('error')
            .addClass('success')
            .text(message)
            .show();
    } else {
        console.log("Success: " + message);
    }
}

/**
 * Show error message for prescription operations
 * @param {String} message - Error message
 */
function showPrescriptionError(message) {
    let messageContainer = $('#prescriptionMessage');
    if (messageContainer.length > 0) {
        messageContainer
            .removeClass('success')
            .addClass('error')
            .text(message)
            .show();
    } else {
        console.error("Error: " + message);
    }
}

/**
 * Show info message for prescription operations
 * @param {String} message - Info message
 */
function showPrescriptionInfo(message) {
    let messageContainer = $('#prescriptionMessage');
    if (messageContainer.length > 0) {
        messageContainer
            .removeClass('error success')
            .addClass('info')
            .text(message)
            .show();
    } else {
        console.log("Info: " + message);
    }
}
