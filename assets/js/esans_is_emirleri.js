app = new Vue({
    el: '#app',
    data: {
        workOrders: [],
        essences: [],
        tanks: [],
        calculatedComponents: [],
        essenceUnit: '', // Will store the unit of selected essence
        selectedWorkOrder: {
            is_emri_numarasi: null,
            olusturulma_tarihi: '',
            olusturan: '',
            esans_kodu: '',
            esans_ismi: '',
            tank_kodu: '',
            tank_ismi: '',
            planlanan_miktar: 0,
            birim: '',
            planlanan_baslangic_tarihi: '',
            demlenme_suresi_gun: 0,
            planlanan_bitis_tarihi: '',
            aciklama: '',
            durum: 'olusturuldu',
            tamamlanan_miktar: 0,
            eksik_miktar_toplami: 0
        },
        modalTitle: 'Yeni Esans İş Emri Oluştur',
        submitButtonText: 'Oluştur',
        showModal: false,
        showCompleteModal: false,
        showDetailsModal: false,
        showInfoModal: false,
        selectedWorkOrderId: null,
        workOrderComponents: [],
        alertMessage: '',
        alertType: 'success',
        kullaniciAdi: window.kullaniciBilgisi ? window.kullaniciBilgisi.kullaniciAdi : 'Kullanıcı',
        // Pagination properties
        pagination: {
            current_page: 1,
            per_page: 25,
            total: 0,
            total_pages: 1
        },
        // Loading state
        loading: true,
        showContent: false
    },
    methods: {
        async fetchWorkOrders(page = 1) {
            this.loading = true; // Show loading state
            this.showContent = false; // Hide content initially
            
            try {
                const response = await axios.get(`api_islemleri/esans_is_emirleri_islemler.php?action=get_work_orders&page=${page}&limit=${this.pagination.per_page}`);
                if (response.data.status === 'success') {
                    // Ensure we have valid data before assigning
                    const fetchedData = response.data.data || [];
                    // Verify each work order has required properties
                    this.workOrders = fetchedData.map(wo => ({
                        ...wo,
                        durum: wo.durum || 'olusturuldu', // Default to 'olusturuldu' if not defined
                        is_emri_numarasi: wo.is_emri_numarasi || null
                    }));
                    console.log('Work orders fetched:', this.workOrders);
                    // Log each work order to check if is_emri_numarasi exists
                    this.workOrders.forEach((wo, index) => {
                        console.log(`Work order ${index}:`, wo);
                        console.log(`  is_emri_numarasi:`, wo.is_emri_numarasi);
                        console.log(`  durum:`, wo.durum);
                    });
                    
                    // Update pagination data
                    if (response.data.pagination) {
                        this.pagination = {
                            current_page: response.data.pagination.current_page,
                            per_page: response.data.pagination.per_page,
                            total: response.data.pagination.total,
                            total_pages: response.data.pagination.total_pages
                        };
                    }
                } else {
                    this.workOrders = []; // Ensure an empty array on error
                    this.showAlert(response.data.message, 'danger');
                }
            } catch (error) {
                this.workOrders = []; // Ensure an empty array on error
                this.showAlert('Esans iş emirleri alınırken bir hata oluştu.', 'danger');
                console.error('Error fetching work orders:', error);
            } finally {
                this.loading = false; // Hide loading state
                
                // Wait 1.5 seconds before showing content
                setTimeout(() => {
                    this.showContent = true; // Show content after delay
                }, 1500);
            }
        },
        async fetchEssences() {
            try {
                const response = await axios.get('api_islemleri/esans_is_emirleri_islemler.php?action=get_essences');
                if (response.data.status === 'success') {
                    this.essences = response.data.data || [];
                } else {
                    this.showAlert(response.data.message, 'danger');
                }
            } catch (error) {
                this.showAlert('Essanslar alınırken bir hata oluştu.', 'danger');
            }
        },

        async fetchTanks() {
            try {
                const response = await axios.get('api_islemleri/esans_is_emirleri_islemler.php?action=get_tanks');
                if (response.data.status === 'success') {
                    this.tanks = response.data.data || [];
                } else {
                    this.showAlert(response.data.message, 'danger');
                }
            } catch (error) {
                this.showAlert('Tanklar alınırken bir hata oluştu.', 'danger');
            }
        },
        async updateEssenceDetails() {
            if (!this.selectedWorkOrder.esans_kodu) {
                this.essenceUnit = '';
                this.selectedWorkOrder.birim = '';
                this.selectedWorkOrder.demlenme_suresi_gun = 0;
                this.calculatedComponents = [];
                return;
            }

            // Find the essence details
            const essence = this.essences.find(e => e.esans_kodu === this.selectedWorkOrder.esans_kodu);
            if (essence) {
                this.essenceUnit = essence.birim;
                this.selectedWorkOrder.birim = essence.birim;
                this.selectedWorkOrder.demlenme_suresi_gun = essence.demlenme_suresi_gun || 0;
            }

            // Calculate components based on selected essence
            await this.calculateComponents();
        },
        async calculateComponents() {
            if (!this.selectedWorkOrder.esans_kodu || !this.selectedWorkOrder.planlanan_miktar) {
                this.calculatedComponents = [];
                return;
            }

            try {
                const response = await axios.post('api_islemleri/esans_is_emirleri_islemler.php?action=calculate_components', {
                    essence_code: this.selectedWorkOrder.esans_kodu,
                    quantity: this.selectedWorkOrder.planlanan_miktar
                });

                if (response.data.status === 'success') {
                    // Calculate the required amounts based on the formula
                    const calculated = response.data.data.map(component => {
                        // Calculate: original_component_amount * user_quantity
                        const calculatedAmount = parseFloat(component.bilesen_miktari) * parseFloat(this.selectedWorkOrder.planlanan_miktar);
                        return {
                            malzeme_kodu: component.bilesen_kodu,
                            malzeme_ismi: component.bilesen_ismi,
                            malzeme_turu: component.bilesenin_malzeme_turu,
                            miktar: calculatedAmount.toFixed(2),
                            birim: this.essenceUnit // Use the essence unit instead of work order unit
                        };
                    });

                    this.calculatedComponents = calculated;
                } else {
                    this.showAlert(response.data.message, 'danger');
                    this.calculatedComponents = [];
                }
            } catch (error) {
                this.showAlert('Bileşenler hesaplanırken bir hata oluştu.', 'danger');
                console.error('Error calculating components:', error);
                this.calculatedComponents = [];
            }
        },
        updateTankName() {
            if (!this.selectedWorkOrder.tank_kodu) {
                this.selectedWorkOrder.tank_ismi = '';
                return;
            }

            const tank = this.tanks.find(t => t.tank_kodu === this.selectedWorkOrder.tank_kodu);
            if (tank) {
                this.selectedWorkOrder.tank_ismi = tank.tank_ismi;
            }
        },
        openAddModal() {
            this.selectedWorkOrder = {
                is_emri_numarasi: null,
                olusturulma_tarihi: new Date().toISOString().split('T')[0],
                olusturan: this.kullaniciAdi,
                esans_kodu: '',
                esans_ismi: '',
                tank_kodu: '',
                tank_ismi: '',
                planlanan_miktar: 0,
                birim: '',
                planlanan_baslangic_tarihi: new Date().toISOString().split('T')[0],
                demlenme_suresi_gun: 0,
                planlanan_bitis_tarihi: '',
                aciklama: '',
                durum: 'olusturuldu',
                tamamlanan_miktar: 0,
                eksik_miktar_toplami: 0
            };
            this.essenceUnit = '';
            this.calculatedComponents = [];
            this.modalTitle = 'Yeni Esans İş Emri Oluştur';
            this.submitButtonText = 'Oluştur';
            this.showModal = true;
        },
        async openEditModal(id) {
            const workOrder = this.workOrders.find(item => item.is_emri_numarasi == id);
            this.selectedWorkOrder = { ...workOrder };
            this.essenceUnit = workOrder.birim;

            // Calculate components for existing work order
            this.calculatedComponents = [];
            try {
                // We'll fetch the calculated components from the esans_is_emri_malzeme_listesi table
                const response = await axios.get(`api_islemleri/esans_is_emirleri_islemler.php?action=get_components&work_order_id=${id}`);
                if (response.data.status === 'success') {
                    this.calculatedComponents = response.data.data || [];
                }
            } catch (error) {
                console.error('Error loading components for edit:', error);
            }

            this.modalTitle = 'Esans İş Emri Düzenle';
            this.submitButtonText = 'Güncelle';
            this.showModal = true;
        },
        async saveWorkOrder() {
            try {
                // Calculate planlanan_bitis_tarihi based on planlanan_baslangic_tarihi and demlenme_suresi_gun
                let startDate = new Date(this.selectedWorkOrder.planlanan_baslangic_tarihi);
                let endDate = new Date(startDate);
                endDate.setDate(endDate.getDate() + parseInt(this.selectedWorkOrder.demlenme_suresi_gun));
                this.selectedWorkOrder.planlanan_bitis_tarihi = endDate.toISOString().split('T')[0];

                // Determine action based on whether we're creating or updating
                const isUpdate = !!this.selectedWorkOrder.is_emri_numarasi;
                const action = isUpdate ? 'update_work_order' : 'create_work_order';

                const response = await axios.post('api_islemleri/esans_is_emirleri_islemler.php', {
                    action: action,
                    work_order: this.selectedWorkOrder,
                    components: this.calculatedComponents
                });

                if (response.data.status === 'success') {
                    this.showAlert(response.data.message, 'success');
                    this.closeModal();
                    await this.fetchWorkOrders(this.pagination.current_page); // Refresh the current page
                } else {
                    this.showAlert(response.data.message, 'danger');
                }
            } catch (error) {
                this.showAlert('İş emri kaydedilirken bir hata oluştu.', 'danger');
                console.error('Error saving work order:', error);
            }
        },
        async deleteWorkOrder(id) {
            if (!confirm('Bu iş emrini silmek istediğinizden emin misiniz?')) {
                return;
            }

            try {
                const response = await axios.post('api_islemleri/esans_is_emirleri_islemler.php', {
                    action: 'delete_work_order',
                    id: id
                });

                if (response.data.status === 'success') {
                    this.showAlert(response.data.message, 'success');
                    // After deletion, check if we need to adjust the page
                    if (this.workOrders.length === 1 && this.pagination.current_page > 1) {
                        // If we're on a page with only one item and it's not the first page,
                        // we should go to the previous page
                        await this.goToPreviousPage();
                    } else {
                        // Otherwise, refresh the current page
                        await this.fetchWorkOrders(this.pagination.current_page);
                    }
                } else {
                    this.showAlert(response.data.message, 'danger');
                }
            } catch (error) {
                this.showAlert('İş emri silinirken bir hata oluştu.', 'danger');
            }
        },
        closeModal() {
            this.showModal = false;
        },
        showAlert(message, type) {
            this.alertMessage = message;
            this.alertType = type;

            // Auto-hide the alert after 5 seconds
            setTimeout(() => {
                this.closeAlert();
            }, 5000);
        },
        closeAlert() {
            this.alertMessage = '';
        },
        updateEndDate() {
            if (this.selectedWorkOrder.planlanan_baslangic_tarihi && this.selectedWorkOrder.demlenme_suresi_gun) {
                let startDate = new Date(this.selectedWorkOrder.planlanan_baslangic_tarihi);
                let endDate = new Date(startDate);
                endDate.setDate(endDate.getDate() + parseInt(this.selectedWorkOrder.demlenme_suresi_gun));
                this.selectedWorkOrder.planlanan_bitis_tarihi = endDate.toISOString().split('T')[0];
            }
        },

        async startWorkOrder(id) {
            // First, get the list of components to show in the confirmation dialog
            try {
                const response = await axios.get(`api_islemleri/esans_is_emirleri_islemler.php?action=get_work_order_components&id=${id}`);

                if (response.data.status === 'success') {
                    const components = response.data.data;
                    let confirmationMessage = 'Bu is emrini baslatmak istediginizden emin misiniz?\n\n';

                    if (components && components.length > 0) {
                        confirmationMessage += 'Asagidaki malzemeler stoktan dusulecektir:\n';
                        components.forEach(component => {
                            confirmationMessage += ` - ${component.malzeme_ismi}: ${parseFloat(component.miktar).toFixed(2)} ${component.birim}\n`;
                        });
                    } else {
                        confirmationMessage += 'Bu is emri icin stoktan dusulecek malzeme bulunmuyor.';
                    }

                    if (confirm(confirmationMessage)) {
                        // If confirmed, proceed to start the work order
                        const startResponse = await axios.post('api_islemleri/esans_is_emirleri_islemler.php', {
                            action: 'start_work_order',
                            id: id
                        });

                        if (startResponse.data.status === 'success') {
                            this.showAlert(startResponse.data.message, 'success');
                            await this.fetchWorkOrders(this.pagination.current_page); // Refresh the current page
                        } else {
                            this.showAlert(startResponse.data.message, 'danger');
                        }
                    }
                } else {
                    this.showAlert('Onay icin bilesen listesi alinamadi: ' + response.data.message, 'danger');
                }
            } catch (error) {
                this.showAlert('Onay icin bilesen listesi alinirken bir sunucu hatasi olustu.', 'danger');
                console.error('Error getting components:', error);
            }
        },
        async revertWorkOrder(id) {
            if (!confirm('Bu is emrini durdurup "Olusturuldu" durumuna geri almak istediginizden emin misiniz?')) {
                return;
            }

            try {
                const response = await axios.post('api_islemleri/esans_is_emirleri_islemler.php', {
                    action: 'revert_work_order',
                    id: id
                });

                if (response.data.status === 'success') {
                    this.showAlert(response.data.message, 'success');
                    await this.fetchWorkOrders(this.pagination.current_page); // Refresh the current page
                } else {
                    this.showAlert(response.data.message, 'danger');
                }
            } catch (error) {
                this.showAlert('Islem sirasinda bir hata olustu.', 'danger');
            }
        },
        async openCompleteModal(id) {
            try {
                const response = await axios.get(`api_islemleri/esans_is_emirleri_islemler.php?action=get_work_order&id=${id}`);

                if (response.data.status === 'success') {
                    const workOrder = response.data.data;
                    this.selectedWorkOrder = { ...workOrder };
                    
                    // Calculate missing amount when modal opens
                    this.calculateMissingAmount();
                    
                    this.showCompleteModal = true;
                } else {
                    this.showAlert(response.data.message, 'danger');
                }
            } catch (error) {
                this.showAlert('Is emri detaylari alinirken bir hata olustu.', 'danger');
            }
        },
        async completeWorkOrder() {
            try {
                // Ensure missing amount is calculated before sending
                this.calculateMissingAmount();
                
                const response = await axios.post('api_islemleri/esans_is_emirleri_islemler.php', {
                    action: 'complete_work_order',
                    is_emri_numarasi: this.selectedWorkOrder.is_emri_numarasi,
                    tamamlanan_miktar: this.selectedWorkOrder.tamamlanan_miktar,
                    eksik_miktar_toplami: this.selectedWorkOrder.eksik_miktar_toplami,
                    aciklama: this.selectedWorkOrder.aciklama
                });

                if (response.data.status === 'success') {
                    this.showAlert(response.data.message, 'success');
                    this.showCompleteModal = false;
                    await this.fetchWorkOrders(this.pagination.current_page); // Refresh the current page
                } else {
                    alert('Hata: ' + response.data.message);
                }
            } catch (error) {
                alert('Islem sirasinda sunucu taraflı bir hata olustu.');
            }
        },
        async revertCompletion(id) {
            console.log('Revert completion called with ID:', id);
            console.log('Type of ID:', typeof id);
            console.log('Work orders:', this.workOrders);

            // Find the work order in our list to verify it exists
            const workOrder = this.workOrders.find(wo => wo.is_emri_numarasi == id);
            console.log('Found work order:', workOrder);

            if (!id) {
                this.showAlert('İş emri numarası gerekli.', 'danger');
                return;
            }

            // Check if ID is valid
            if (typeof id === 'undefined' || id === null || id === '') {
                this.showAlert('Geçersiz iş emri numarası.', 'danger');
                return;
            }

            if (confirm('Bu is emrinin tamamlanma durumunu geri almak istediginizden emin misiniz? Bu islem ilgili stok hareketlerini tersine cevirecektir.')) {
                try {
                    const response = await axios.post('api_islemleri/esans_is_emirleri_islemler.php', {
                        action: 'revert_completion',
                        id: id
                    });

                    if (response.data.status === 'success') {
                        this.showAlert(response.data.message, 'success');
                        await this.fetchWorkOrders(this.pagination.current_page); // Refresh the current page
                    } else {
                        this.showAlert(response.data.message, 'danger');
                    }
                } catch (error) {
                    this.showAlert('Geri alma islemi sirasinda bir hata olustu.', 'danger');
                    console.error('Revert completion error:', error);
                }
            }
        },
        async showWorkOrderDetails(id) {
            try {
                const response = await axios.get(`api_islemleri/esans_is_emirleri_islemler.php?action=get_work_order_components&id=${id}`);

                if (response.data.status === 'success') {
                    this.workOrderComponents = response.data.data;
                    this.selectedWorkOrderId = id;
                    this.showDetailsModal = true;
                } else {
                    this.showAlert('Malzeme detayları alınırken bir hata oluştu: ' + response.data.message, 'danger');
                }
            } catch (error) {
                this.showAlert('Malzeme detayları alınırken bir sunucu hatası oluştu.', 'danger');
            }
        },
        async printWorkOrder(id) {
            this.showAlert('PDF oluşturuluyor, lütfen bekleyin...', 'info');

            try {
                // Fetch both work order details and components in parallel
                const [workOrderResponse, componentsResponse] = await Promise.all([
                    axios.get(`api_islemleri/esans_is_emirleri_islemler.php?action=get_work_order&id=${id}`),
                    axios.get(`api_islemleri/esans_is_emirleri_islemler.php?action=get_work_order_components&id=${id}`)
                ]);

                if (workOrderResponse.data.status === 'success' && componentsResponse.data.status === 'success') {
                    const workOrder = workOrderResponse.data.data;
                    const components = componentsResponse.data.data;

                    const printHtml = this.buildPrintableHtml(workOrder, components);

                    const element = document.createElement('div');
                    element.innerHTML = printHtml;

                    const opt = {
                        margin: 0.2,
                        filename: `is_emri_${workOrder.is_emri_numarasi}.pdf`,
                        image: { type: 'jpeg', quality: 0.98 },
                        html2canvas: { scale: 2, useCORS: true },
                        jsPDF: { unit: 'in', format: 'a4', orientation: 'landscape' }
                    };

                    html2pdf().from(element).set(opt).save().then(() => {
                        this.showAlert('PDF başarıyla oluşturuldu ve indirildi.', 'success');
                    });
                } else {
                    this.showAlert('PDF oluşturmak için veriler alınırken bir hata oluştu.', 'danger');
                }
            } catch (error) {
                this.showAlert('PDF oluşturulurken bir sunucu hatası oluştu.', 'danger');
            }
        },
        buildPrintableHtml(wo, components) {
            // Map for user-friendly labels
            const fieldLabels = {
                'is_emri_numarasi': 'İş Emri No',
                'olusturulma_tarihi': 'Oluşturulma Tarihi',
                'olusturan': 'Oluşturan',
                'esans_kodu': 'Esans Kodu',
                'esans_ismi': 'Esans İsmi',
                'tank_kodu': 'Tank Kodu',
                'tank_ismi': 'Tank İsmi',
                'planlanan_miktar': 'Planlanan Miktar',
                'tamamlanan_miktar': 'Tamamlanan Miktar',
                'eksik_miktar_toplami': 'Eksik Miktar Toplamı',
                'birim': 'Birim',
                'planlanan_baslangic_tarihi': 'Planlanan Başlangıç',
                'planlanan_bitis_tarihi': 'Planlanan Bitiş',
                'gerceklesen_baslangic_tarihi': 'Gerçekleşen Başlangıç',
                'gerceklesen_bitis_tarihi': 'Gerçekleşen Bitiş',
                'demlenme_suresi_gun': 'Demlenme Süresi (Gün)',
                'durum': 'Durum',
                'aciklama': 'Açıklama'
            };

            let allRows = [];

            for (const key in wo) {
                if (wo.hasOwnProperty(key) && wo[key] !== null && wo[key] !== '' && fieldLabels[key]) {
                    let value = wo[key];

                    if (key === 'durum') {
                        const statusMap = { olusturuldu: 'Oluşturuldu', uretimde: 'Üretimde', tamamlandi: 'Tamamlandı', iptal: 'İptal' };
                        value = statusMap[value] || value;
                    }

                    const row = `
                    <tr>
                        <td style="padding: 4px; border-bottom: 1px solid #eee; background-color: #f9f9f9; width: 40%;"><strong>${fieldLabels[key]}:</strong></td>
                        <td style="padding: 4px; border-bottom: 1px solid #eee;">${value}</td>
                    </tr>`;
                    allRows.push(row);
                }
            }

            const itemsPerColumn = Math.ceil(allRows.length / 3);
            const leftColumn = allRows.slice(0, itemsPerColumn).join('');
            const middleColumn = allRows.slice(itemsPerColumn, 2 * itemsPerColumn).join('');
            const rightColumn = allRows.slice(2 * itemsPerColumn).join('');

            const generalInfoHtml = `
            <div style="display: flex; justify-content: space-between; width: 100%;">
                <div style="width: 32%;">
                    <table style="width: 100%; border-collapse: collapse;">${leftColumn}</table>
                </div>
                <div style="width: 32%;">
                    <table style="width: 100%; border-collapse: collapse;">${middleColumn}</table>
                </div>
                <div style="width: 32%;">
                    <table style="width: 100%; border-collapse: collapse;">${rightColumn}</table>
                </div>
            </div>`;

            let componentsHtml = '';
            if (components.length > 0) {
                components.forEach(c => {
                    componentsHtml += `
                    <tr>
                        <td style="padding: 4px; border: 1px solid #ddd;">${c.malzeme_kodu}</td>
                        <td style="padding: 4px; border: 1px solid #ddd;">${c.malzeme_ismi}</td>
                        <td style="padding: 4px; border: 1px solid #ddd;">${c.malzeme_turu}</td>
                        <td style="padding: 4px; border: 1px solid #ddd;">${parseFloat(c.miktar).toFixed(2)}</td>
                    </tr>`;
                });
            } else {
                componentsHtml = '<tr><td colspan="4" style="padding: 4px; border: 1px solid #ddd; text-align: center;">Bu iş emri için bileşen bulunmuyor.</td></tr>';
            }

            const today = new Date().toLocaleDateString('tr-TR');

            return `
            <div style="font-family: Arial, sans-serif; padding: 10px; font-size: 10px;">
                <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #4a0e63; padding-bottom: 5px; margin-bottom: 5px;">
                    <h1 style="font-size: 22px; color: #4a0e63; margin: 0;">İş Emri Raporu</h1>
                    <div style="text-align: right;">
                        <div style="font-size: 12px;"><strong>İş Emri No:</strong> ${wo.is_emri_numarasi}</div>
                        <div style="font-size: 10px; color: #666;"><strong>Rapor Tarihi:</strong> ${today}</div>
                    </div>
                </div>

                <div style="margin-top: 15px;">
                    <h3 style="font-size: 14px; color: #4a0e63; border-bottom: 1px solid #eee; padding-bottom: 3px; margin-bottom: 10px;">Genel Bilgiler</h3>
                    ${generalInfoHtml}
                </div>
                
                <div style="margin-top: 15px;">
                    <h3 style="font-size: 14px; color: #4a0e63; border-bottom: 1px solid #eee; padding-bottom: 3px; margin-bottom: 10px;">Gerekli Bileşenler</h3>
                    <table style="width: 100%; border-collapse: collapse; font-size: 10px;">
                        <thead style="background-color: #4a0e63; color: white;">
                            <tr>
                                <th style="padding: 5px; border: 1px solid #4a0e63; text-align: left;">Malzeme Kodu</th>
                                <th style="padding: 5px; border: 1px solid #4a0e63; text-align: left;">Malzeme İsmi</th>
                                <th style="padding: 5px; border: 1px solid #4a0e63; text-align: left;">Malzeme Türü</th>
                                <th style="padding: 5px; border: 1px solid #4a0e63; text-align: left;">Gerekli Miktar</th>
                            </tr>
                        </thead>
                        <tbody>${componentsHtml}</tbody>
                    </table>
                </div>

                <div style="position: absolute; bottom: 10px; left: 10px; right: 10px; text-align: center; font-size: 8px; color: #aaa;">
                    Bu rapor IDO Kozmetik ERP sistemi tarafından oluşturulmuştur.
                </div>
            </div>
        `;
        },
        
        async goToPage(page) {
            if (page < 1 || page > this.pagination.total_pages) {
                return; // Invalid page number
            }
            await this.fetchWorkOrders(page);
        },
        
        async goToPreviousPage() {
            if (this.pagination.current_page > 1) {
                await this.goToPage(this.pagination.current_page - 1);
            }
        },
        
        async goToNextPage() {
            if (this.pagination.current_page < this.pagination.total_pages) {
                await this.goToPage(this.pagination.current_page + 1);
            }
        },
        
        async goToFirstPage() {
            await this.goToPage(1);
        },
        
        async goToLastPage() {
            await this.goToPage(this.pagination.total_pages);
        },
        
        async changePerPage() {
            // Reset to first page when changing items per page
            await this.fetchWorkOrders(1);
        },
        
        calculateMissingAmount() {
            // Calculate missing amount as planned amount minus completed amount
            // Ensure it doesn't go below 0
            if (this.selectedWorkOrder && 
                this.selectedWorkOrder.planlanan_miktar !== undefined && 
                this.selectedWorkOrder.tamamlanan_miktar !== undefined) {
                
                const planned = parseFloat(this.selectedWorkOrder.planlanan_miktar) || 0;
                const completed = parseFloat(this.selectedWorkOrder.tamamlanan_miktar) || 0;
                
                // Calculate missing amount: planned - completed, but minimum 0
                const missing = Math.max(0, planned - completed);
                
                this.selectedWorkOrder.eksik_miktar_toplami = missing;
            }
        }
    },
    watch: {
        // Watch changes in planlanan_baslangic_tarihi or demlenme_suresi_gun to update planlanan_bitis_tarihi
        'selectedWorkOrder.planlanan_baslangic_tarihi': function (newVal) {
            if (newVal && this.selectedWorkOrder.demlenme_suresi_gun) {
                let startDate = new Date(newVal);
                let endDate = new Date(startDate);
                endDate.setDate(endDate.getDate() + parseInt(this.selectedWorkOrder.demlenme_suresi_gun));
                this.selectedWorkOrder.planlanan_bitis_tarihi = endDate.toISOString().split('T')[0];
            }
        },
        'selectedWorkOrder.demlenme_suresi_gun': function (newVal) {
            if (this.selectedWorkOrder.planlanan_baslangic_tarihi && newVal) {
                let startDate = new Date(this.selectedWorkOrder.planlanan_baslangic_tarihi);
                let endDate = new Date(startDate);
                endDate.setDate(endDate.getDate() + parseInt(newVal));
                this.selectedWorkOrder.planlanan_bitis_tarihi = endDate.toISOString().split('T')[0];
            }
        }
    },
    async mounted() {
        // Fetch initial data when component is mounted
        await this.fetchWorkOrders(1); // Fetch first page
        await this.fetchEssences();
        await this.fetchTanks();
        console.log('Vue app initialized');

        // Debug: Check if work orders are correctly loaded
        setTimeout(() => {
            console.log('Mounted - Work orders count:', this.workOrders.length);
            console.log('Pagination info:', this.pagination);
            if (this.workOrders.length > 0) {
                console.log('First work order sample:', this.workOrders[0]);
            }
        }, 2000);
    }
});

