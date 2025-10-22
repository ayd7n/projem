// Vue.js application for essence work orders
document.addEventListener('DOMContentLoaded', function() {
    new Vue({
        el: '#app',
        data: {
            workOrders: [],
            essences: [],
            materials: [],
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
            alertMessage: '',
            alertType: 'success',
            kullaniciAdi: window.kullaniciBilgisi ? window.kullaniciBilgisi.kullaniciAdi : 'Kullanıcı'
        },
        methods: {
            async fetchWorkOrders() {
                try {
                    const response = await axios.get('api_islemleri/esans_is_emirleri_islemler.php?action=get_work_orders');
                    if (response.data.status === 'success') {
                        this.workOrders = response.data.data || [];
                    } else {
                        this.showAlert(response.data.message, 'danger');
                    }
                } catch (error) {
                    this.showAlert('Esans iş emirleri alınırken bir hata oluştu.', 'danger');
                    console.error('Error fetching work orders:', error);
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
            async fetchMaterials() {
                try {
                    const response = await axios.get('api_islemleri/esans_is_emirleri_islemler.php?action=get_materials');
                    if (response.data.status === 'success') {
                        this.materials = response.data.data || [];
                    } else {
                        this.showAlert(response.data.message, 'danger');
                    }
                } catch (error) {
                    this.showAlert('Malzemeler alınırken bir hata oluştu.', 'danger');
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
                                birim: this.selectedWorkOrder.birim
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
                this.selectedWorkOrder = {...workOrder};
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
                        await this.fetchWorkOrders(); // Refresh the list
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
                        await this.fetchWorkOrders(); // Refresh the list
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
            }
        },
        watch: {
            // Watch changes in planlanan_baslangic_tarihi or demlenme_suresi_gun to update planlanan_bitis_tarihi
            'selectedWorkOrder.planlanan_baslangic_tarihi': function(newVal) {
                if (newVal && this.selectedWorkOrder.demlenme_suresi_gun) {
                    let startDate = new Date(newVal);
                    let endDate = new Date(startDate);
                    endDate.setDate(endDate.getDate() + parseInt(this.selectedWorkOrder.demlenme_suresi_gun));
                    this.selectedWorkOrder.planlanan_bitis_tarihi = endDate.toISOString().split('T')[0];
                }
            },
            'selectedWorkOrder.demlenme_suresi_gun': function(newVal) {
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
            await this.fetchWorkOrders();
            await this.fetchEssences();
            await this.fetchMaterials();
            await this.fetchTanks();
        }
    });
});