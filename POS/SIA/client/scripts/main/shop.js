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

            // -------------------------
            // Elements
            // -------------------------
            const medicineTableBody = $('#medicinesTableBody')
            const searchField = $('#searchTextfield')
            const cartContainer = $('#cartContainer')
            const subtotalAmount = $('#subtotalAmount')
            const discountAmount = $('#discountAmount')
            const taxAmount = $('#taxAmount')
            const totalAmount = $('#totalAmount')
            const discountSelect = $('#discountSelect')
            const resetCartButton = $('#resetCartButton')
            const orderButton = $('#orderButton')
            const cartSidebar = $('#cartSidebar')

            const patientIdTextfield = $('#patientIdTextfield')
            const walkinNameTextfield = $('#walkinNameTextfield')
            const patientField = $('#patientField')
            const walkinField = $('#walkinField')

            let medicines = []
            let cart = []
            let patients = []
            let TAX_RATE = 0, PWD_DISCOUNT = 0, SENIOR_DISCOUNT = 0
            let configLoaded = false

            // -------------------------
            // Load configuration
            // -------------------------
            fetch("server/api/main/get_configuration.php")
                .then(res => res.json())
                .then(data => {
                    if (data.status === "success") {
                        TAX_RATE = parseFloat(data.data.tax_percentage)/100
                        PWD_DISCOUNT = parseFloat(data.data.pwd_discount)/100
                        SENIOR_DISCOUNT = parseFloat(data.data.senior_discount)/100
                        $('#taxAmount').prev('span').text(`Tax (${parseFloat(data.data.tax_percentage)}%)`)
                        configLoaded = true
                    }
                })

            // -------------------------
            // Load local medicines
            // -------------------------
            const loadMedicines = () => {
                fetch('server/api/main/get_medicines.php')
                    .then(res => res.json())
                    .then(data => {
                        if(data.status==="success"){
                            medicines = data.data
                            displayMedicines(medicines)
                        }
                    })
            }

            const displayMedicines = (list) => {
                medicineTableBody.html('')
                if(list.length===0){
                    medicineTableBody.append(`<tr><td class="table__empty" colspan="8">No medicines found.</td></tr>`)
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
            searchField.on('input', ()=>{
                const value = searchField.val().toLowerCase()
                const filtered = medicines.filter(m=>
                    m.medicine_name.toLowerCase().includes(value) ||
                    m.generic_name.toLowerCase().includes(value) ||
                    m.medicine_group.toLowerCase().includes(value)
                )
                displayMedicines(filtered)
            })

            // -------------------------
            // Load patients from remote API
            // -------------------------
            const loadPatients = async () => {
                try{
                    const res = await fetch('http://26.233.226.98/PMS/api/get_patients.php')
                    const data = await res.json()
                    if(data.status==='success'){
                        patients = data.data
                        let listHtml = ''
                        patients.forEach(p=>{
                            listHtml += `<option value="${p.patient_id}">${p.full_name}</option>`
                        })
                        $('#patientList').html(listHtml)
                    } else showNotification('Failed to load patients', 'error')
                } catch(err){
                    console.error(err)
                    showNotification('Error fetching patients', 'error')
                }
            }

            // -------------------------
            // Add patient prescriptions to cart
            // -------------------------
            patientIdTextfield.on('change', async ()=>{
                const patientId = patientIdTextfield.val().trim()
                if(!patientId) return

                const patient = patients.find(p=>p.patient_id.toString()===patientId)
                if(!patient){
                    showNotification('Patient not found', 'error')
                    return
                }

                try{
                    const presRes = await fetch(`http://26.233.226.98/PMS/api/get_prescription.php`)
                    const presData = await presRes.json()
                    if(presData.status!=='success') throw new Error('Failed to fetch prescriptions')

                    const patientRecords = presData.data.filter(r=>r.patient_id.toString()===patientId)
                    let prescriptionMedicines = []
                    for(let record of patientRecords){
                        if(record.prescriptions && record.prescriptions.length>0){
                            for(let med of record.prescriptions){
                                prescriptionMedicines.push({
                                    medicine_id: med.medicine_id,
                                    medicine_name: med.medicine_name,
                                    quantity: med.quantity,
                                    price: parseFloat(med.price)
                                })
                            }
                        }
                    }

                    if(prescriptionMedicines.length===0){
                        showNotification('No prescriptions found', 'info')
                        return
                    }

                    // Dispatch to cart
                    document.dispatchEvent(new CustomEvent('addPrescriptionMedicines', {detail:{medicines: prescriptionMedicines}}))

                } catch(err){
                    console.error(err)
                    showNotification('Failed to load prescriptions', 'error')
                }
            })

            // -------------------------
            // Event: Add prescription medicines to cart
            // -------------------------
            document.addEventListener('addPrescriptionMedicines', function(e){
                const prescriptionMedicines = e.detail.medicines
                if(!Array.isArray(prescriptionMedicines) || prescriptionMedicines.length===0) return

                cart = [] // clear cart first
                const normalize = s => (s||'').toString().trim()

                prescriptionMedicines.forEach(med=>{
                    let fullMedicine = medicines.find(m=>normalize(m.medicine_id)===normalize(med.medicine_id))
                    if(fullMedicine){
                        const stock = parseInt(fullMedicine.stock)||0
                        if(stock<=0) return
                        cart.push({
                            medicine_id: fullMedicine.medicine_id,
                            medicine_name: fullMedicine.medicine_name,
                            price: parseFloat(fullMedicine.price),
                            quantity: Math.min(stock, med.quantity)
                        })
                    } else {
                        cart.push({
                            medicine_id: med.medicine_id,
                            medicine_name: med.medicine_name || 'Unknown',
                            price: med.price||0,
                            quantity: med.quantity||1
                        })
                    }
                })
                renderCart()
                updateCartTotals()
                showNotification("Prescription medicines added to cart", "success")
            })

            // -------------------------
            // Render cart
            // -------------------------
            const renderCart = () => {
                cartContainer.html('')
                cart.forEach((item,index)=>{
                    cartContainer.append(`
                        <div class="cart-container__item cart-item" data-index="${index}">
                            <span>${item.medicine_name}</span>
                            <div class="cart-container__item-action">
                                <button class="theme__button --secondary cart-item__dec"><span>-</span></button>
                                <span>${item.quantity}</span>
                                <button class="theme__button --secondary cart-item__inc"><span>+</span></button>
                            </div>
                            <span>₱${(item.price*item.quantity).toFixed(2)}</span>
                            <button class="theme__button --critical cart-item__remove"><span>x</span></button>
                        </div>
                    `)
                })

                $('.cart-item__inc').on('click', function(){
                    const idx = $(this).closest('.cart-item').data('index')
                    const item = cart[idx]
                    const med = medicines.find(m=>m.medicine_id===item.medicine_id)
                    if(item.quantity+1>med.stock){
                        showModal({type:'error', headerText:'Stock Limit', descriptionText:`Only ${med.stock} available.`})
                        return
                    }
                    item.quantity+=1
                    renderCart()
                    updateCartTotals()
                })

                $('.cart-item__dec').on('click', function(){
                    const idx = $(this).closest('.cart-item').data('index')
                    if(cart[idx].quantity>1) cart[idx].quantity-=1
                    renderCart()
                    updateCartTotals()
                })

                $('.cart-item__remove').on('click', function(){
                    const idx = $(this).closest('.cart-item').data('index')
                    cart.splice(idx,1)
                    renderCart()
                    updateCartTotals()
                })

                updateCartTotals()
            }

            // -------------------------
            // Update cart totals
            // -------------------------
            const updateCartTotals = ()=>{
                if(!configLoaded) return
                let subtotal=0
                cart.forEach(item=>subtotal+=item.price*item.quantity)
                let discountAmountVal=0
                let discountPercent=0
                const selectedDiscount = discountSelect.val()
                if(selectedDiscount==='pwd'){
                    discountAmountVal = subtotal*PWD_DISCOUNT
                    discountPercent = PWD_DISCOUNT*100
                } else if(selectedDiscount==='senior'){
                    discountAmountVal = subtotal*SENIOR_DISCOUNT
                    discountPercent = SENIOR_DISCOUNT*100
                }
                $('#discountAmount').prev('span').text(`Discount${discountPercent>0?' ('+discountPercent+'%)':''}`)
                const tax = (subtotal-discountAmountVal)*TAX_RATE
                const grandTotal = subtotal-discountAmountVal+tax

                subtotalAmount.text(`₱${subtotal.toFixed(2)}`)
                discountAmount.text(`₱${discountAmountVal.toFixed(2)}`)
                taxAmount.text(`₱${tax.toFixed(2)}`)
                totalAmount.text(`₱${grandTotal.toFixed(2)}`)
            }

            // -------------------------
            // Patient/Walk-in toggle
            // -------------------------
            $('input[name="customerType"]').on('change', function(){
                const type=$(this).val()
                if(type==='patient'){
                    patientField.show()
                    walkinField.hide()
                } else {
                    patientField.hide()
                    walkinField.show()
                }
            })

            // -------------------------
            // Add medicine from table to cart
            // -------------------------
            $('table.shop__content-table tbody').on('click','tr:not(.table__empty)', function(){
                const rowIndex = $(this).index()
                const med = medicines[rowIndex]
                const existingIndex = cart.findIndex(item=>item.medicine_id===med.medicine_id)
                if(existingIndex>=0){
                    if(cart[existingIndex].quantity+1>med.stock){
                        showModal({type:'error', headerText:'Stock Limit', descriptionText:`Only ${med.stock} available.`})
                        return
                    }
                    cart[existingIndex].quantity+=1
                } else {
                    if(med.stock===0){
                        showModal({type:'error', headerText:'Out of Stock', descriptionText:`${med.medicine_name} out of stock.`})
                        return
                    }
                    cart.push({medicine_id:med.medicine_id, medicine_name:med.medicine_name, price:parseFloat(med.price), quantity:1})
                }
                renderCart()
            })

            // -------------------------
            // Initial load
            // -------------------------
            loadMedicines()
            loadPatients()

            // Cart toggle
            $('#cartButton').on('click',()=>cartSidebar.toggleClass('---show'))

        })
        router.fadeIn(speed)
    })
}
