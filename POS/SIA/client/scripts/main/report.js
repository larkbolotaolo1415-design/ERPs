import { activateSidebarNavigationLink } from '../utilities/fragment.js'

export const renderReport = (router, speed = 300) => {
    const path = 'client/fragments/main/report.html'

    const sidebarReportLink = $('#reportLink')
    activateSidebarNavigationLink(sidebarReportLink)

    router.fadeOut(speed, () => {
        router.load(path, () => {
            // ========== [ FRAGMENT INITIALIZERS ] ========== //
                initializeReportDownload()
                initializeReportTabs()
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
 * Initialize report tab switching (Sales / Invoice)
 */
function initializeReportTabs() {
    const salesTab = document.getElementById('salesTab')
    const invoiceTab = document.getElementById('invoiceTab')

    if (salesTab) {
        // Attach to tab container div
        salesTab.addEventListener('click', function (e) {
            e.preventDefault()
            e.stopPropagation()
            // toggle active classes
            document.querySelectorAll('.configuration__tab-item').forEach(i => i.classList.remove('---active'))
            salesTab.classList.add('---active')
            showSalesTable()
        })
        
        // Also attach to span inside
        const salesSpan = salesTab.querySelector('span')
        if (salesSpan) {
            salesSpan.addEventListener('click', function (e) {
                e.preventDefault()
                e.stopPropagation()
                document.querySelectorAll('.configuration__tab-item').forEach(i => i.classList.remove('---active'))
                salesTab.classList.add('---active')
                showSalesTable()
            })
        }
    }

    if (invoiceTab) {
        // Attach to tab container div
        invoiceTab.addEventListener('click', function (e) {
            e.preventDefault()
            e.stopPropagation()
            document.querySelectorAll('.configuration__tab-item').forEach(i => i.classList.remove('---active'))
            invoiceTab.classList.add('---active')
            showInvoiceTable()
        })
        
        // Also attach to span inside
        const invoiceSpan = invoiceTab.querySelector('span')
        if (invoiceSpan) {
            invoiceSpan.addEventListener('click', function (e) {
                e.preventDefault()
                e.stopPropagation()
                document.querySelectorAll('.configuration__tab-item').forEach(i => i.classList.remove('---active'))
                invoiceTab.classList.add('---active')
                showInvoiceTable()
            })
        }
    }
}

function showSalesTable() {
    const salesWrapper = document.getElementById('salesTableWrapper')
    const invoiceWrapper = document.getElementById('invoiceTableWrapper')
    
    if (salesWrapper) salesWrapper.style.display = 'block'
    if (invoiceWrapper) invoiceWrapper.style.display = 'none'
    
    loadSalesReport()
    // Build accordion charts after aggregation
    loadSalesByGeneric().then(aggregates => buildSalesAccordion(aggregates)).catch(err => console.error(err))
}

function showInvoiceTable() {
    const salesWrapper = document.getElementById('salesTableWrapper')
    const invoiceWrapper = document.getElementById('invoiceTableWrapper')
    
    if (salesWrapper) salesWrapper.style.display = 'none'
    if (invoiceWrapper) invoiceWrapper.style.display = 'block'
}

/**
 * Load sales report using invoices and invoice items (items count and totals)
 */
function loadSalesReport() {
    fetch(`/sia/server/api/main/get_invoices.php`)
        .then(res => res.json())
        .then(data => {
            const tbody = document.getElementById('salesListTableBody')
            if (!tbody) return

            if (data.status === 'success' && data.data.length > 0) {
                const invoices = data.data

                // For each invoice, fetch details to get item counts (parallel requests)
                const detailPromises = invoices.map(inv => {
                    return fetch(`/sia/server/api/main/get_invoice_details.php?invoice_id=${inv.invoice_id}`)
                        .then(r => r.json())
                        .catch(err => {
                            console.error('Error fetching details for invoice', inv.invoice_id, err)
                            return null
                        })
                        .then(detail => ({ inv, detail }))
                })

                Promise.all(detailPromises).then(results => {
                    tbody.innerHTML = ''
                    
                    results.forEach(({ inv, detail }) => {
                        // Get items array from detail response
                        let itemsCount = 0
                        if (detail && detail.items && Array.isArray(detail.items)) {
                            itemsCount = detail.items.length
                        }
                        
                        const date = inv.date_created ? new Date(inv.date_created).toLocaleDateString() : '-'
                        const row = document.createElement('tr')
                        row.className = 'sales-row'
                        row.dataset.invoiceId = inv.invoice_id
                        row.style.cursor = 'pointer'
                        row.innerHTML = `
                            <td>${inv.invoice_id}</td>
                            <td>${inv.invoice_number || '-'}</td>
                            <td>${inv.patient_id || '-'}</td>
                            <td>${date}</td>
                            <td style="text-align:center">${itemsCount}</td>
                            <td>₱${parseFloat(inv.total_amount || 0).toFixed(2)}</td>
                            <td>${inv.payment_method || 'Cash'}</td>
                        `
                        row.addEventListener('click', () => {
                            document.querySelectorAll('.sales-row').forEach(r => r.style.backgroundColor = '')
                            row.style.backgroundColor = '#e8f4f8'
                            // Open invoice view modal instead of chart modal
                            openInvoiceModal(inv.invoice_id)
                        })
                        tbody.appendChild(row)
                    })
                })
            } else {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 2rem;">No sales found</td>
                    </tr>
                `
            }
        })
        .catch(err => {
            console.error('Error loading sales report:', err)
            const tbody = document.getElementById('salesListTableBody')
            if (tbody) tbody.innerHTML = `
                <tr>
                    <td colspan="7" style="text-align: center; padding: 2rem;">Error loading sales</td>
                </tr>
            `
        })
}

/** Build accordion items and render charts from aggregated data */
function buildSalesAccordion(aggregates) {
    try {
        const topNSelect = document.getElementById('topNSelect')
        const chartTypeSelect = document.getElementById('chartTypeSelect')
        const customSelect = document.getElementById('customGenericSelect')
        const addGenericBtn = document.getElementById('addGenericBtn')

        const topN = parseInt(topNSelect?.value || '10')
        const chartType = chartTypeSelect?.value || 'bar'

        // Prepare data
        const arr = aggregates.slice() // copy
        const byQtyDesc = arr.slice().sort((a,b) => b.quantity - a.quantity)
        const byQtyAsc = arr.slice().sort((a,b) => a.quantity - b.quantity)
        const byPrice = arr.slice().filter(d => d.quantity > 0).map(d => ({...d, avg: d.sales / d.quantity || 0}))
        byPrice.sort((a,b) => b.avg - a.avg)

        // Top sellers
        const top = byQtyDesc.slice(0, topN)
        renderChartToCanvas('chart_top', top.map(d=>d.name), top.map(d=>d.quantity), chartType, 'Quantity Sold')

        // Least sellers
        const least = byQtyAsc.slice(0, topN)
        renderChartToCanvas('chart_least', least.map(d=>d.name), least.map(d=>d.quantity), chartType, 'Quantity Sold')

        // Most expensive
        const expensive = byPrice.slice(0, topN)
        renderChartToCanvas('chart_expensive', expensive.map(d=>d.name), expensive.map(d=>parseFloat((d.avg||0).toFixed(2))), chartType, 'Avg Price (₱)')

        // Populate custom select
        if (customSelect) {
            customSelect.innerHTML = ''
            arr.forEach(item => {
                const opt = document.createElement('option')
                opt.value = item.name
                opt.textContent = `${item.name} — ${item.quantity} pcs`;
                customSelect.appendChild(opt)
            })
        }

        // Custom chart: allow adding selected generics for comparison
        const customSelected = []
        function renderCustom() {
            const sel = customSelected.slice()
            if (sel.length === 0) {
                // show placeholder
                renderChartToCanvas('chart_custom', ['No selection'], [0], 'bar', 'No Data')
                return
            }
            const items = sel.map(name => {
                const found = arr.find(a => a.name === name)
                return { name, qty: found ? found.quantity : 0 }
            })
            renderChartToCanvas('chart_custom', items.map(i=>i.name), items.map(i=>i.qty), chartType, 'Quantity')
        }

        if (addGenericBtn) {
            addGenericBtn.onclick = (e) => {
                const val = customSelect?.value
                if (!val) return
                if (!customSelected.includes(val)) customSelected.push(val)
                renderCustom()
            }
        }

        // wire controls to re-render
        if (topNSelect) topNSelect.onchange = () => buildSalesAccordion(aggregates)
        if (chartTypeSelect) chartTypeSelect.onchange = () => buildSalesAccordion(aggregates)

        // accordion open/close wiring
        document.querySelectorAll('.accordion__header').forEach(h => {
            const item = h.parentElement
            h.onclick = () => {
                const isOpen = item.classList.contains('open')
                document.querySelectorAll('.accordion__item').forEach(it => it.classList.remove('open'))
                if (!isOpen) item.classList.add('open')
            }
        })

    } catch (err) {
        console.error('Error building accordion charts', err)
    }
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

// Chart instances (to destroy when re-rendering)
let _aggregateChart = null
let _charts = {}

/** Wait until Chart.js is available, then call callback. */
function ensureChartReady(cb, attempts = 0) {
    if (typeof Chart !== 'undefined') return cb()
    if (attempts > 20) {
        console.error('Chart.js not available')
        return
    }
    setTimeout(() => ensureChartReady(cb, attempts + 1), 100)
}

/**
 * Load all invoices and invoice items, aggregate sales by generic medicine name,
 * and return a Promise that resolves to the aggregated array.
 */
function loadSalesByGeneric(topN = 50) {
    return new Promise((resolve, reject) => {
        fetch(`/sia/server/api/main/get_invoices.php`)
            .then(res => res.json())
            .then(data => {
                if (!data || data.status !== 'success' || !Array.isArray(data.data)) {
                    console.warn('No invoices to aggregate')
                    resolve([])
                    return
                }

                const invoices = data.data
                // Fetch details for each invoice in parallel
                const detailPromises = invoices.map(inv =>
                    fetch(`/sia/server/api/main/get_invoice_details.php?invoice_id=${inv.invoice_id}`)
                        .then(r => r.json())
                        .catch(err => { console.error('detail fetch error', err); return null })
                        .then(detail => ({ inv, detail }))
                )

                Promise.all(detailPromises).then(results => {
                    const map = new Map()

                    results.forEach(({ inv, detail }) => {
                        if (!detail || !Array.isArray(detail.items)) return
                        const invoiceDate = inv.date_created ? new Date(inv.date_created) : null
                        detail.items.forEach(it => {
                            const key = (it.generic_name || it.medicine_name || it.medicine_id || 'Unknown').toString().trim().toLowerCase()
                            if (!map.has(key)) map.set(key, {
                                name: (it.generic_name || it.medicine_name || it.medicine_id || 'Unknown').toString(),
                                quantity: 0,
                                sales: 0,
                                last_sold: invoiceDate
                            })

                            const entry = map.get(key)
                            const qty = parseInt(it.quantity || 0) || 0
                            const price = parseFloat(it.price || 0) || 0
                            entry.quantity += qty
                            entry.sales += price * qty
                            if (invoiceDate && (!entry.last_sold || invoiceDate > entry.last_sold)) entry.last_sold = invoiceDate
                        })
                    })

                    const arr = Array.from(map.values())
                    // Sort by quantity desc by default
                    arr.sort((a, b) => b.quantity - a.quantity)
                    resolve(arr)
                })
            })
            .catch(err => {
                console.error('Error aggregating sales:', err)
                resolve([])
            })
    })
}

/** Render a chart into a canvas id with animation */
function renderChartToCanvas(canvasId, labels, dataset, chartType = 'bar', labelText = '') {
    ensureChartReady(() => {
        try {
            const ctxEl = document.getElementById(canvasId)
            if (!ctxEl) {
                console.warn('Canvas not found:', canvasId)
                return
            }
            
            // destroy existing
            if (_charts[canvasId]) { try { _charts[canvasId].destroy() } catch(e){}; _charts[canvasId] = null }

            // Ensure canvas has proper dimensions
            ctxEl.width = ctxEl.offsetWidth
            ctxEl.height = 320

            const config = {
                type: chartType,
                data: {
                    labels: labels,
                    datasets: [{
                        label: labelText,
                        data: dataset,
                        backgroundColor: labels.map((_, i) => `rgba(${54 + (i*20)%200}, ${162 - (i*10)%120}, ${235 - (i*15)%200}, 0.75)`),
                        borderColor: 'rgba(33,37,41,0.9)',
                        borderWidth: 1,
                        tension: 0.3,
                        fill: false
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: { duration: 900, easing: 'easeOutQuart' },
                    scales: { 
                        y: { beginAtZero: true },
                        x: { ticks: { autoSkip: false, maxRotation: 45, minRotation: 0 } }
                    },
                    plugins: { 
                        legend: { display: false },
                        tooltip: { mode: 'index', intersect: false }
                    }
                }
            }

            // Small delay to ensure DOM is ready
            setTimeout(() => {
                try {
                    _charts[canvasId] = new Chart(ctxEl.getContext('2d'), config)
                } catch (e) {
                    console.error('Chart creation error:', canvasId, e)
                }
            }, 50)
        } catch (err) {
            console.error('Error rendering chart', canvasId, err)
        }
    })
}

/* Chart modal per-row removed — aggregated chart now drives Sales view. */

/* per-row chart rendering removed; retained aggregated chart via `renderAggregateChart`. */

/* per-row chart PDF export removed. */

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

    console.log('Starting report download as PDF...')
    
    // Clone the report view
    const reportClone = reportView.cloneNode(true)
    
    // Create minimal HTML for printing
    const printHTML = `
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice Report</title>
    <style>
        :root {
            --color-brand: #0066cc;
            --color-text: #1a1a1a;
            --color-text-muted: #666666;
            --color-surface: #f5f5f5;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background: white;
            color: var(--color-text);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            font-size: 14px;
            line-height: 1.6;
        }
        
        .report__view {
            width: 210mm;
            height: 297mm;
            margin: 0 auto;
            padding: 20mm;
            background: white;
            box-shadow: none;
            border-radius: 0;
        }
        
        .report__header {
            text-align: center;
            margin-bottom: 2rem;
            border-bottom: 2px solid var(--color-brand);
            padding-bottom: 1.5rem;
        }
        
        .report__header h2 {
            margin: 0 0 0.5rem 0;
            font-size: 24px;
            color: var(--color-brand);
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        
        .report__header p {
            margin: 0.5rem 0 0 0;
            font-size: 12px;
            color: var(--color-text-muted);
        }
        
        .report__section {
            margin-bottom: 2rem;
        }
        
        .report__section h3,
        .report__section h4 {
            margin: 0 0 1rem 0;
            font-size: 13px;
            color: var(--color-brand);
            text-transform: uppercase;
            border-bottom: 2px solid var(--color-brand);
            padding-bottom: 0.75rem;
            letter-spacing: 1px;
        }
        
        .report__info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }
        
        .report__info-item {
            font-size: 13px;
            line-height: 1.6;
            padding: 0.5rem 0;
        }
        
        .report__info-item strong {
            color: var(--color-text);
            font-weight: 600;
        }
        
        .report__detail-table {
            width: 100%;
            border-collapse: collapse;
            margin: 1.5rem 0;
            font-size: 12px;
        }
        
        .report__detail-table thead {
            background-color: var(--color-brand);
            color: white;
        }
        
        .report__detail-table th {
            padding: 10px;
            text-align: left;
            font-weight: 600;
            border: 1px solid var(--color-brand);
        }
        
        .report__detail-table td {
            padding: 10px;
            border: 1px solid #e0e0e0;
            text-align: left;
        }
        
        .report__detail-table tbody tr:nth-child(even) {
            background-color: #fafafa;
        }
        
        .report__summary {
            margin: 2rem 0;
            padding: 1.5rem;
            border: 2px solid var(--color-brand);
            border-radius: 4px;
            background-color: var(--color-surface);
        }
        
        .report__summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
            font-size: 13px;
            padding: 0.5rem 0;
        }
        
        .report__summary-row strong {
            font-weight: 600;
            color: var(--color-text);
        }
        
        .report__summary-row.--total {
            border-top: 2px solid var(--color-brand);
            padding-top: 1rem;
            font-size: 14px;
            font-weight: bold;
            color: var(--color-brand);
        }
        
        .report__footer {
            text-align: center;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid #e0e0e0;
            font-size: 12px;
            color: var(--color-text-muted);
        }
        
        @page {
            size: A4;
            margin: 10mm;
        }
        
        @media print {
            body {
                margin: 0;
                padding: 0;
            }
            
            .report__view {
                width: 100%;
                height: auto;
                margin: 0;
                padding: 20mm;
                page-break-after: always;
            }
            
            .report__section {
                page-break-inside: avoid;
            }
            
            .report__detail-table {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    ${reportClone.outerHTML}
</body>
</html>
    `
    
    // Open new window for printing
    const printWindow = window.open('', '_blank', 'width=1000,height=800')
    printWindow.document.write(printHTML)
    printWindow.document.close()
    
    // Trigger print after content is loaded
    printWindow.onload = () => {
        setTimeout(() => {
            printWindow.print()
        }, 250)
    }
    
    console.log('PDF print dialog opened')
}
