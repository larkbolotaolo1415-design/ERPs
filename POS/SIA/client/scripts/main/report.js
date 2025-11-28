import { activateSidebarNavigationLink } from '../utilities/fragment.js'

export const renderReport = (router, speed = 300) => {
    const path = 'client/fragments/main/report.html'

    const sidebarReportLink = $('#reportLink')
    activateSidebarNavigationLink(sidebarReportLink)

    router.fadeOut(speed, () => {
        router.load(path, () => {
            // ========== [ FRAGMENT INITIALIZERS ] ========== //
            initializeReportDownload()
            loadInvoices()
            // ========== [ ... ] ========== //
        })
        router.fadeIn(speed)
    })
}

/**
 * Load all invoices from database
 */
function loadInvoices() {
    fetch(`/sia/server/api/main/get_invoices.php`)
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success' && data.data.length > 0) {
                displayInvoiceList(data.data)
                // Load first invoice by default
                /* loadInvoiceDetails(data.data[0].invoice_id) */
            } else {
                document.getElementById('invoiceListTableBody').innerHTML = `
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 2rem;">No invoices found</td>
                    </tr>
                `
            }
        })
        .catch(err => {
            console.error('Error loading invoices:', err)
            document.getElementById('invoiceListTableBody').innerHTML = `
                <tr>
                    <td colspan="5" style="text-align: center; padding: 2rem;">Error loading invoices</td>
                </tr>
            `
        })
}

/**
 * Display invoice list in table
 */
function displayInvoiceList(invoices) {
    const tbody = document.getElementById('invoiceListTableBody')
    tbody.innerHTML = ''
    
    invoices.forEach(invoice => {
        const date = new Date(invoice.date_created).toLocaleDateString()
        
        const row = document.createElement('tr')
        row.className = 'invoice-row'
        row.dataset.invoiceId = invoice.invoice_id
        row.style.cursor = 'pointer'
        row.innerHTML = `
            <td>${invoice.invoice_id}</td>
            <td>${invoice.invoice_number}</td>
             <td>${invoice.patient_id}</td>
            <td>${invoice.walkin_id}</td>
            <td>${invoice.employee_id}</td>
            <td>${invoice.subtotal}</td>
            <td>${invoice.discount_type}</td>
            <td>${invoice.discount_amount}</td>
            <td>${invoice.tax_amount}</td>
            <td>${invoice.total_amount}</td>
            <td>${invoice.payment_method}</td>
            <td>${date}</td>
        `
        
            row.addEventListener('click', () => {
                // Highlight selected row
                document.querySelectorAll('.invoice-row').forEach(r => r.style.backgroundColor = '')
                row.style.backgroundColor = '#e8f4f8'
                // Open invoice in modal view
                openInvoiceModal(invoice.invoice_id)
            })
        tbody.appendChild(row)
    })
}

/**
 * Open invoice in centered modal view and render report
 */
function openInvoiceModal(invoiceId) {
    console.log('openInvoiceModal called with ID:', invoiceId)
    
    fetch(`/sia/server/api/main/get_invoice_details.php?invoice_id=${invoiceId}`)
        .then(res => res.json())
        .then(data => {
            console.log('Invoice data received:', data)
            if (data.status === 'success') {
                const invoice = data.invoice
                const items = data.items || []

                const container = document.getElementById('viewReportContainer')
                console.log('Container found:', !!container)
                
                if (!container) return
                container.innerHTML = ''
                container.appendChild(renderReportView(invoice, items))

                // Show view modal
                const viewModal = document.getElementById('viewModalContainer')
                console.log('Modal container found:', !!viewModal)
                
                if (viewModal) {
                    viewModal.classList.add('---show')
                    console.log('Modal shown with class ---show')
                }
            }
        })
        .catch(err => {
            console.error('Error opening invoice modal:', err)
        })
}

function renderReportView(invoice, items) {
    const el = document.createElement('div')
    el.className = 'report__view'
    el.innerHTML = `
        <div class="report__header">
            <h2>PHARMA PLUS</h2>
            <p>Pharmacy Management System</p>
            <hr>
        </div>

        <div class="report__section">
            <h3>SALES INVOICE REPORT</h3>
            <div class="report__info-grid">
                <div class="report__info-item"><strong>Invoice ID:</strong> <span id="rpt-invoice-id">${invoice.invoice_id}</span></div>
                <div class="report__info-item"><strong>Invoice Number:</strong> <span id="rpt-invoice-number">${invoice.invoice_number}</span></div>
                <div class="report__info-item"><strong>Date:</strong> <span id="rpt-invoice-date">${new Date(invoice.date_created).toLocaleDateString()}</span></div>
                <div class="report__info-item"><strong>Payment Method:</strong> <span id="rpt-payment-method">${invoice.payment_method || 'Cash'}</span></div>
            </div>
        </div>

        <div class="report__section">
            <h4>Patient Information</h4>
            <div class="report__info-grid">
                <div class="report__info-item"><strong>Patient ID:</strong> <span id="rpt-patient-id">${invoice.patient_id || '-'}</span></div>
                <div class="report__info-item"><strong>Employee ID:</strong> <span id="rpt-employee-id">${invoice.employee_id || '-'}</span></div>
            </div>
        </div>

        <div class="report__section">
            <h4>Order Details</h4>
            <table class="report__detail-table">
                <thead>
                    <tr>
                        <th>Medicine</th>
                        <th>Dosage</th>
                        <th>Quantity</th>
                        <th>Unit Price</th>
                        <th>Total</th>
                        <th>Refunded</th>
                    </tr>
                </thead>
                <tbody id="rpt-items-tbody">
                    ${items.length === 0 ? '<tr><td colspan="6" style="text-align:center">No items found</td></tr>' : items.map(it => {
                        const total = (parseFloat(it.price) * parseInt(it.quantity || 0)).toFixed(2)
                        // determine refund status (flexible field names)
                        const refundedDate = it.refunded_date || it.date_refunded || it.refunded_at || null
                        const isRefunded = (it.refunded === 1 || it.refunded === '1' || refundedDate)
                        const refundedDisplay = isRefunded ? `<span style="color:#dc2626;font-weight:600">Yes${refundedDate ? ' (' + new Date(refundedDate).toLocaleDateString() + ')' : ''}</span>` : `<span style="color:#6b7280">No</span>`
                        return `<tr><td>${it.medicine_name}</td><td>${it.dosage || '-'}</td><td>${it.quantity}</td><td>₱${parseFloat(it.price).toFixed(2)}</td><td>₱${total}</td><td>${refundedDisplay}</td></tr>`
                    }).join('')}
                </tbody>
            </table>
        </div>

        <div class="report__summary">
            <div class="report__summary-row"><span>Subtotal:</span><strong id="rpt-subtotal">₱${parseFloat(invoice.subtotal || 0).toFixed(2)}</strong></div>
            <div class="report__summary-row"><span>Discount:</span><strong id="rpt-discount">₱${parseFloat(invoice.discount_amount || 0).toFixed(2)}</strong></div>
            <div class="report__summary-row"><span>Tax Amount:</span><strong id="rpt-tax">₱${parseFloat(invoice.tax_amount || 0).toFixed(2)}</strong></div>
            <div class="report__summary-row --total"><span>Total Amount:</span><strong id="rpt-total">₱${parseFloat(invoice.total_amount || 0).toFixed(2)}</strong></div>
        </div>

        <div class="report__footer"><p>Thank you for your business!</p></div>
    `

    return el
}

function closeViewModal() {
    const viewModal = document.getElementById('viewModalContainer')
    if (viewModal) viewModal.classList.remove('---show')
}

/**
 * Toggle edit mode in view modal
 */
function toggleEditMode() {
    const viewReportPane = document.getElementById('viewReportPane')
    if (!viewReportPane) return

    const isEditMode = viewReportPane.classList.contains('edit-mode')

    if (!isEditMode) {
        // Enter edit mode
        enableEditMode()
    } else {
        // Exit edit mode
        disableEditMode()
    }
}

/**
 * Enable edit mode - make content editable
 */
function enableEditMode() {
    const viewReportPane = document.getElementById('viewReportPane')
    const viewEditBtn = document.getElementById('viewEditBtn')
    const viewModalSide = document.getElementById('viewModalSide')
    
    if (!viewReportPane || !viewEditBtn || !viewModalSide) return

    // Add edit mode class
    viewReportPane.classList.add('edit-mode')

    // Make content editable
    const spans = viewReportPane.querySelectorAll('span[id^="rpt-"]')
    spans.forEach(span => {
        span.contentEditable = true
        span.style.cursor = 'text'
    })

    const cells = viewReportPane.querySelectorAll('#rpt-items-tbody td')
    cells.forEach(cell => {
        cell.contentEditable = true
        cell.style.cursor = 'text'
    })

    // Replace buttons with Save/Cancel
    viewModalSide.innerHTML = `
        <button id="saveChangesBtn" class="theme__button --primary" style="width: 100%; padding: 0.8rem 0.6rem; font-size: 0.8rem; display: flex; flex-direction: column; align-items: center; gap: 0.5rem; border-radius: 8px; transition: all 0.3s ease; border: none; cursor: pointer; font-weight: 600; background-color: #10b981; color: white;">
            <svg height="24" width="24" style="flex-shrink: 0;">
                <use href="client/assets/icons/main.svg#icon-check"></use>
            </svg>
            <span>SAVE</span>
        </button>
        
        <button id="cancelEditBtn" class="theme__button" style="width: 100%; padding: 0.8rem 0.6rem; font-size: 0.8rem; display: flex; flex-direction: column; align-items: center; gap: 0.5rem; border-radius: 8px; transition: all 0.3s ease; background-color: rgba(255, 255, 255, 0.2); color: white; border: none; cursor: pointer; font-weight: 600;">
            <svg height="24" width="24" style="flex-shrink: 0;">
                <use href="client/assets/icons/main.svg#icon-x"></use>
            </svg>
            <span>CANCEL</span>
        </button>
    `

    // Attach event listeners
    const saveBtn = document.getElementById('saveChangesBtn')
    const cancelBtn = document.getElementById('cancelEditBtn')

    if (saveBtn) {
        saveBtn.addEventListener('click', function(e) {
            e.preventDefault()
            e.stopPropagation()
            saveInlineChanges()
        })
    }

    if (cancelBtn) {
        cancelBtn.addEventListener('click', function(e) {
            e.preventDefault()
            e.stopPropagation()
            disableEditMode()
        })
    }

    // Change edit button color
    viewEditBtn.style.display = 'none'
}

/**
 * Disable edit mode - revert to view only
 */
function disableEditMode() {
    const viewReportPane = document.getElementById('viewReportPane')
    const viewEditBtn = document.getElementById('viewEditBtn')
    const viewDownloadBtn = document.getElementById('viewDownloadBtn')
    const viewModalSide = document.getElementById('viewModalSide')

    if (!viewReportPane || !viewModalSide) return

    // Remove edit mode class
    viewReportPane.classList.remove('edit-mode')

    // Make content not editable
    const spans = viewReportPane.querySelectorAll('span[id^="rpt-"]')
    spans.forEach(span => {
        span.contentEditable = false
        span.style.backgroundColor = ''
        span.style.border = ''
        span.style.padding = ''
        span.style.cursor = ''
    })

    const cells = viewReportPane.querySelectorAll('#rpt-items-tbody td')
    cells.forEach(cell => {
        cell.contentEditable = false
        cell.style.backgroundColor = ''
        cell.style.cursor = ''
    })

    // Restore original buttons
    viewModalSide.innerHTML = `
        <button id="viewDownloadBtn" class="theme__button --primary" style="width: 100%; padding: 0.8rem 0.6rem; font-size: 0.8rem; display: flex; flex-direction: column; align-items: center; gap: 0.5rem; border-radius: 8px; transition: all 0.3s ease; border: none; cursor: pointer; font-weight: 600; background-color: rgba(255, 255, 255, 0.2); color: white;">
            <svg height="24" width="24" style="flex-shrink: 0;">
                <use href="client/assets/icons/main.svg#icon-download"></use>
            </svg>
            <span>DOWNLOAD</span>
        </button>

        <button id="viewEditBtn" class="theme__button" style="width: 100%; padding: 0.8rem 0.6rem; font-size: 0.8rem; display: flex; flex-direction: column; align-items: center; gap: 0.5rem; border-radius: 8px; transition: all 0.3s ease; background-color: rgba(255, 255, 255, 0.2); color: white; border: none; cursor: pointer; font-weight: 600;">
            <svg height="24" width="24" style="flex-shrink: 0;">
                <use href="client/assets/icons/main.svg#icon-edit"></use>
            </svg>
            <span>EDIT</span>
        </button>
    `

    // Re-attach event listeners
    const downloadBtn = document.getElementById('viewDownloadBtn')
    const editBtn = document.getElementById('viewEditBtn')
    
    if (downloadBtn) {
        downloadBtn.addEventListener('click', function(e) {
            e.preventDefault()
            e.stopPropagation()
            downloadReportAsPDF()
        })
    }
    
    if (editBtn) {
        editBtn.addEventListener('click', function(e) {
            e.preventDefault()
            e.stopPropagation()
            toggleEditMode()
        })
    }
}

/**
 * Save inline changes made in view modal
 */
function saveInlineChanges() {
    const viewReportPane = document.getElementById('viewReportPane')
    if (!viewReportPane) return

    try {
        // Collect invoice data from editable fields
        const invoiceData = {
            invoice_id: document.getElementById('rpt-invoice-id')?.textContent || '',
            invoice_number: document.getElementById('rpt-invoice-number')?.textContent || '',
            patient_id: document.getElementById('rpt-patient-id')?.textContent || '',
            employee_id: document.getElementById('rpt-employee-id')?.textContent || '',
            subtotal: document.getElementById('rpt-subtotal')?.textContent?.replace('₱', '').trim() || '0',
            discount_amount: document.getElementById('rpt-discount')?.textContent?.replace('₱', '').trim() || '0',
            tax_amount: document.getElementById('rpt-tax')?.textContent?.replace('₱', '').trim() || '0',
            total_amount: document.getElementById('rpt-total')?.textContent?.replace('₱', '').trim() || '0',
            payment_method: document.getElementById('rpt-payment-method')?.textContent || 'Cash'
        }

        // Collect items from table
        const itemsBody = document.getElementById('rpt-items-tbody')
        const items = []
        if (itemsBody) {
            const rows = itemsBody.querySelectorAll('tr')
            rows.forEach(row => {
                const cells = row.querySelectorAll('td')
                if (cells.length >= 5) {
                    items.push({
                        medicine_name: cells[0]?.textContent?.trim() || '',
                        dosage: cells[1]?.textContent?.trim() || '',
                        quantity: parseInt(cells[2]?.textContent?.trim() || '0'),
                        price: parseFloat(cells[3]?.textContent?.replace('₱', '').trim() || '0')
                    })
                }
            })
        }

        // Send to server
        console.log('Sending changes to server...', invoiceData)

        fetch('/sia/server/api/main/update_invoice.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                invoice: invoiceData,
                items: items
            })
        })
        .then(res => res.json())
        .then(data => {
            console.log('Server response:', data)
            if (data.status === 'success') {
                alert('Changes saved successfully!')
                disableEditMode()
                // Reload invoice list
                setTimeout(() => {
                    loadInvoices()
                    closeViewModal()
                }, 500)
            } else {
                alert('Error saving changes: ' + (data.message || 'Unknown error'))
            }
        })
        .catch(err => {
            console.error('Error saving changes:', err)
            alert('Error saving changes: ' + err.message)
        })

    } catch (error) {
        console.error('Error collecting changes:', error)
        alert('Error collecting changes: ' + error.message)
    }
}

/**
 * Initialize download button functionality
 */
function initializeReportDownload() {
    // Edit button - opens modal
    const editBtn = document.getElementById('editBtn')
    if (editBtn) {
        console.log('Edit button found')
        editBtn.addEventListener('click', function(e) {
            e.preventDefault()
            e.stopPropagation()
            openEditModal()
        })
    }
    
    // Close modal button
    const closeEditModalBtn = document.getElementById('closeEditModalBtn')
    if (closeEditModalBtn) {
        closeEditModalBtn.addEventListener('click', function(e) {
            e.preventDefault()
            closeEditModal()
        })
    }
    
    // Cancel button
    const cancelEditModalBtn = document.getElementById('cancelEditModalBtn')
    if (cancelEditModalBtn) {
        cancelEditModalBtn.addEventListener('click', function(e) {
            e.preventDefault()
            closeEditModal()
        })
    }
    
    // Save button
    const saveEditModalBtn = document.getElementById('saveEditModalBtn')
    if (saveEditModalBtn) {
        saveEditModalBtn.addEventListener('click', function(e) {
            e.preventDefault()
            saveEditModalChanges()
        })
    }
    
    // Click outside modal to close
    const editModalContainer = document.getElementById('editModalContainer')
    if (editModalContainer) {
        editModalContainer.addEventListener('click', function(e) {
            if (e.target === editModalContainer) {
                closeEditModal()
            }
        })
    }

    // View modal controls (viewer)
    const viewDownloadBtn = document.getElementById('viewDownloadBtn')
    if (viewDownloadBtn) {
        viewDownloadBtn.addEventListener('click', function(e) {
            e.preventDefault()
            e.stopPropagation()
            downloadReportAsPDF()
        })
    }

    const viewEditBtn = document.getElementById('viewEditBtn')
    if (viewEditBtn) {
        viewEditBtn.addEventListener('click', function(e) {
            e.preventDefault()
            e.stopPropagation()
            toggleEditMode()
        })
    }

    const closeViewModalBtn = document.getElementById('closeViewModalBtn')
    if (closeViewModalBtn) {
        closeViewModalBtn.addEventListener('click', function(e) {
            e.preventDefault()
            e.stopPropagation()
            closeViewModal()
        })
    }

    const viewModalContainer = document.getElementById('viewModalContainer')
    if (viewModalContainer) {
        viewModalContainer.addEventListener('click', function(e) {
            if (e.target === viewModalContainer) {
                closeViewModal()
            }
        })
    }
}

/**
 * Open edit modal with editable report
 */
function openEditModal() {
    const reportView = document.querySelector('.report__view')
    if (!reportView) {
        alert('No report to edit')
        return
    }
    
    // Clone the report view
    const editableReport = reportView.cloneNode(true)
    const editableReportContainer = document.getElementById('editableReportContainer')
    
    // Clear and populate container
    editableReportContainer.innerHTML = ''
    editableReportContainer.appendChild(editableReport)
    
    // Make content editable
    makeReportEditable(editableReportContainer)
    
    // Show modal with dark overlay
    const editModalContainer = document.getElementById('editModalContainer')
    editModalContainer.classList.add('---show')
}

/**
 * Make report content editable
 */
function makeReportEditable(container) {
    // Make span elements editable
    const spans = container.querySelectorAll('span[id^="rpt-"]')
    spans.forEach(span => {
        span.contentEditable = true
        span.style.backgroundColor = '#fff9e6'
        span.style.border = '1px solid #ffd700'
        span.style.padding = '2px 4px'
        span.style.borderRadius = '2px'
        span.style.cursor = 'text'
        span.style.minWidth = '2rem'
        span.style.display = 'inline-block'
    })
    
    // Make table cells in items table editable
    const itemsTable = container.querySelector('#rpt-items-tbody')
    if (itemsTable) {
        const cells = itemsTable.querySelectorAll('td')
        cells.forEach(cell => {
            cell.contentEditable = true
            cell.style.backgroundColor = '#fff9e6'
            cell.style.cursor = 'text'
        })
    }
}

/**
 * Close edit modal
 */
function closeEditModal() {
    const editModalContainer = document.getElementById('editModalContainer')
    editModalContainer.classList.remove('---show')
}

/**
 * Save changes from edit modal
 */
function saveEditModalChanges() {
    const editableReportContainer = document.getElementById('editableReportContainer')
    const originalReportView = document.querySelector('.report__view')
    
    const editedReport = editableReportContainer.querySelector('.report__view')
    
    if (!editedReport || !originalReportView) {
        alert('Error saving changes')
        return
    }
    
    // Update all span values
    const editedSpans = editedReport.querySelectorAll('span[id^="rpt-"]')
    editedSpans.forEach(editedSpan => {
        const originalSpan = originalReportView.querySelector(`#${editedSpan.id}`)
        if (originalSpan) {
            originalSpan.textContent = editedSpan.textContent
        }
    })
    
    // Update items table
    const editedItemsTable = editedReport.querySelector('#rpt-items-tbody')
    const originalItemsTable = originalReportView.querySelector('#rpt-items-tbody')
    if (editedItemsTable && originalItemsTable) {
        originalItemsTable.innerHTML = editedItemsTable.innerHTML
    }
    
    // Close modal
    closeEditModal()
    alert('Changes saved successfully!')
}

/**
 * Download report view as PDF using print dialog
 */
function downloadReportAsPDF() {
    const reportView = document.querySelector('.report__view')
    
    if (!reportView) {
        console.error('Report view not found')
        alert('Unable to find report content')
        return
    }

    console.log('Starting report download...')

    // Get ALL CSS styles from stylesheets (includes report.css)
    let styles = ''
    try {
        Array.from(document.styleSheets).forEach(sheet => {
            try {
                Array.from(sheet.cssRules).forEach(rule => {
                    styles += rule.cssText + '\n'
                })
            } catch (e) {
                // Skip CORS-blocked stylesheets
            }
        })
    } catch (e) {
        console.warn('Some stylesheets could not be accessed')
    }
    
    // Save the original body HTML
    const originalBody = document.body.innerHTML
    const originalHead = document.head.innerHTML
    
    // Clone the report view - deep clone to get all content
    const reportClone = reportView.cloneNode(true)
    
    // Log to verify content is being captured
    console.log('Report content cloned:', reportClone.innerHTML.length, 'characters')
    console.log('Invoice sections found:', reportClone.querySelectorAll('.report__section').length)
    
    // Create a wrapper div for the cloned content
    const wrapper = document.createElement('div')
    wrapper.appendChild(reportClone)
    
    // Replace body content with only the report
    document.body.innerHTML = wrapper.innerHTML
    
    // Add ALL collected styles to the document head
    const styleTag = document.createElement('style')
    styleTag.textContent = `
        ${styles}
        
        /* Minimal print-specific adjustments only */
        * {
            box-sizing: border-box;
        }
        
        body {
            margin: 0;
            padding: 0;
            background: white;
        }
        
        .report__view {
            width: 100%;
            height: auto;
            margin: 0;
            padding: 40px;
            box-shadow: none;
            border-radius: 0;
            overflow: visible;
            background: white;
        }
        
        /* Ensure all sections are visible */
        .report__header {
            display: block;
            visibility: visible;
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 2px solid var(--color-brand);
        }
        
        .report__section {
            display: block;
            visibility: visible;
            margin-bottom: 2rem;
        }
        
        .report__section h3,
        .report__section h4 {
            display: block;
            visibility: visible;
            margin: 0 0 1.5rem 0;
            font-size: 1.1rem;
            color: var(--color-brand);
            text-transform: uppercase;
            border-bottom: 2px solid var(--color-brand);
            padding-bottom: 1rem;
        }
        
        .report__info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-bottom: 2rem;
        }
        
        .report__info-item {
            display: block;
            font-size: 0.95rem;
            line-height: 1.6;
        }
        
        .report__detail-table {
            display: table;
            width: 100%;
            margin: 2rem 0;
            border-collapse: collapse;
        }
        
        .report__detail-table thead {
            display: table-header-group;
            background-color: var(--color-brand);
        }
        
        .report__detail-table tbody {
            display: table-row-group;
        }
        
        .report__detail-table tr {
            display: table-row;
        }
        
        .report__detail-table th,
        .report__detail-table td {
            display: table-cell;
            padding: 1rem;
            border: 1px solid #e0e0e0;
        }
        
        .report__summary {
            display: block;
            margin: 2rem 0;
            padding: 1.5rem;
            border: 2px solid var(--color-brand);
            border-radius: 4px;
            background-color: #f5f5f5;
        }
        
        .report__summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
            font-size: 1rem;
        }
        
        .report__summary-row.--total {
            border-top: 2px solid var(--color-brand);
            padding-top: 1rem;
            font-weight: bold;
            color: var(--color-brand);
        }
        
        .report__footer {
            display: block;
            text-align: center;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid #e0e0e0;
        }
        
        @page {
            size: A4;
            margin: 10mm;
        }
        
        @media print {
            body {
                margin: 0;
                padding: 0;
                background: white;
            }
            
            .report__view {
                width: 100%;
                height: auto;
                margin: 0;
                padding: 20px;
                box-shadow: none;
                border-radius: 0;
                page-break-inside: avoid;
            }
            
            .report__section {
                page-break-inside: avoid;
            }
            
            .report__detail-table {
                page-break-inside: avoid;
            }
        }
    `
    document.head.appendChild(styleTag)
    
    // Trigger print dialog with longer delay to ensure rendering
    setTimeout(function() {
        console.log('Triggering print dialog')
        window.print()
        
        // Restore original page after print dialog closes
        setTimeout(function() {
            console.log('Restoring original page')
            document.body.innerHTML = originalBody
            document.head.innerHTML = originalHead
            
            // Re-initialize the report download button after restoration
            initializeReportDownload()
        }, 1500)
    }, 800)
    
    console.log('Print dialog should appear now')
}
