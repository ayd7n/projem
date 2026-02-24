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
    computed: {
        kritikBilesenTurleri() {
            // Esans recetesinde ana bilesenler genelde "malzeme" turunde gelir.
            return ['malzeme', 'esans', 'kutu', 'takim', 'takm'];
        },
        maxProducibleQuantity() {
            if (!this.calculatedComponents || this.calculatedComponents.length === 0) {
                return 0;
            }

            let maxProducible = Infinity;
            const kritikTurler = this.kritikBilesenTurleri;

            for (const component of this.calculatedComponents) {
                const malzemeTuru = (component.malzeme_turu || '').toLowerCase();
                if (!kritikTurler.includes(malzemeTuru)) {
                    continue;
                }

                const bilesimOrani = parseFloat(component.bilesim_orani) || 0;
                const stokMiktari = parseFloat(component.stok_miktari) || 0;
                if (bilesimOrani > 0) {
                    const producibleFromThis = Math.max(0, Math.floor(stokMiktari / bilesimOrani));
                    maxProducible = Math.min(maxProducible, producibleFromThis);
                }
            }

            return maxProducible === Infinity ? 0 : maxProducible;
        },
        limitingComponent() {
            if (!this.calculatedComponents || this.calculatedComponents.length === 0) {
                return null;
            }

            let minProducible = Infinity;
            let limitingName = null;
            const kritikTurler = this.kritikBilesenTurleri;

            for (const component of this.calculatedComponents) {
                const malzemeTuru = (component.malzeme_turu || '').toLowerCase();
                if (!kritikTurler.includes(malzemeTuru)) {
                    continue;
                }

                const bilesimOrani = parseFloat(component.bilesim_orani) || 0;
                const stokMiktari = parseFloat(component.stok_miktari) || 0;
                if (bilesimOrani > 0) {
                    const producibleFromThis = Math.max(0, Math.floor(stokMiktari / bilesimOrani));
                    if (producibleFromThis < minProducible) {
                        minProducible = producibleFromThis;
                        limitingName = component.malzeme_ismi;
                    }
                }
            }

            return limitingName;
        }
    },
    methods: {
        getProducibleForComponent(component) {
            const bilesimOrani = parseFloat(component.bilesim_orani) || 0;
            const stokMiktari = parseFloat(component.stok_miktari) || 0;

            if (bilesimOrani > 0) {
                return Math.max(0, Math.floor(stokMiktari / bilesimOrani));
            }
            return 0;
        },
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
                    Swal.fire({
                        title: 'Hata!',
                        text: response.data.message,
                        icon: 'error',
                        confirmButtonText: 'Tamam'
                    });
                }
            } catch (error) {
                this.workOrders = []; // Ensure an empty array on error
                Swal.fire({
                    title: 'Hata!',
                    text: 'Esans iş emirleri alınırken bir hata oluştu.',
                    icon: 'error',
                    confirmButtonText: 'Tamam'
                });

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
                    Swal.fire({
                        title: 'Hata!',
                        text: response.data.message,
                        icon: 'error',
                        confirmButtonText: 'Tamam'
                    });
                }
            } catch (error) {
                Swal.fire({
                    title: 'Hata!',
                    text: 'Essanslar alınırken bir hata oluştu.',
                    icon: 'error',
                    confirmButtonText: 'Tamam'
                });
            }
        },

        async fetchTanks() {
            try {
                const response = await axios.get('api_islemleri/esans_is_emirleri_islemler.php?action=get_tanks');
                if (response.data.status === 'success') {
                    this.tanks = response.data.data || [];
                } else {
                    Swal.fire({
                        title: 'Hata!',
                        text: response.data.message,
                        icon: 'error',
                        confirmButtonText: 'Tamam'
                    });
                }
            } catch (error) {
                Swal.fire({
                    title: 'Hata!',
                    text: 'Tanklar alınırken bir hata oluştu.',
                    icon: 'error',
                    confirmButtonText: 'Tamam'
                });
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
                this.selectedWorkOrder.esans_ismi = essence.esans_ismi;
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
                    const calculated = response.data.data.map(component => {
                        return {
                            malzeme_kodu: component.bilesen_kodu,
                            malzeme_ismi: component.bilesen_ismi,
                            malzeme_turu: component.bilesenin_malzeme_turu,
                            miktar: parseFloat(component.gereken_miktar).toFixed(2),
                            birim: this.essenceUnit,
                            bilesim_orani: component.bilesen_miktari,
                            stok_miktari: parseFloat(component.stok_miktari).toFixed(2),
                            stok_yeterli: component.stok_yeterli
                        };
                    });

                    this.calculatedComponents = calculated;
                } else {
                    Swal.fire({
                        title: 'Hata!',
                        text: response.data.message,
                        icon: 'error',
                        confirmButtonText: 'Tamam'
                    });
                    this.calculatedComponents = [];
                }
            } catch (error) {
                Swal.fire({
                    title: 'Hata!',
                    text: 'Bileşenler hesaplanırken bir hata oluştu.',
                    icon: 'error',
                    confirmButtonText: 'Tamam'
                });

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
                    Swal.fire({
                        title: 'Başarılı!',
                        text: response.data.message,
                        icon: 'success',
                        confirmButtonText: 'Tamam'
                    });
                    this.closeModal();
                    await this.fetchWorkOrders(this.pagination.current_page); // Refresh the current page
                } else {
                    Swal.fire({
                        title: 'Hata!',
                        text: response.data.message,
                        icon: 'error',
                        confirmButtonText: 'Tamam'
                    });
                }
            } catch (error) {
                Swal.fire({
                    title: 'Hata!',
                    text: 'İş emri kaydedilirken bir hata oluştu.',
                    icon: 'error',
                    confirmButtonText: 'Tamam'
                });

            }
        },
        async deleteWorkOrder(id) {
            Swal.fire({
                title: 'Emin misiniz?',
                text: 'Bu iş emrini silmek istediğinizden emin misiniz?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Evet',
                cancelButtonText: 'İptal'
            }).then(async (result) => {
                if (!result.isConfirmed) {
                    return;
                }

                try {
                    const response = await axios.post('api_islemleri/esans_is_emirleri_islemler.php', {
                        action: 'delete_work_order',
                        id: id
                    });

                    if (response.data.status === 'success') {
                        Swal.fire({
                            title: 'Başarılı!',
                            text: response.data.message,
                            icon: 'success',
                            confirmButtonText: 'Tamam'
                        });
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
                        Swal.fire({
                            title: 'Hata!',
                            text: response.data.message,
                            icon: 'error',
                            confirmButtonText: 'Tamam'
                        });
                    }
                } catch (error) {
                    Swal.fire({
                        title: 'Hata!',
                        text: 'İş emri silinirken bir hata oluştu.',
                        icon: 'error',
                        confirmButtonText: 'Tamam'
                    });
                }
            });
        },
        closeModal() {
            this.showModal = false;
        },
        showAlert(message, type) {
            let icon = 'info';
            if (type === 'success') {
                icon = 'success';
            } else if (type === 'danger' || type === 'error') {
                icon = 'error';
            } else if (type === 'warning') {
                icon = 'warning';
            }
            
            Swal.fire({
                title: icon === 'success' ? 'Başarılı!' : 'Bilgi',
                text: message,
                icon: icon,
                confirmButtonText: 'Tamam'
            });
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

                    Swal.fire({
                        title: 'Emin misiniz?',
                        text: confirmationMessage,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Evet',
                        cancelButtonText: 'İptal'
                    }).then(async (result) => {
                        if (result.isConfirmed) {
                            // If confirmed, proceed to start the work order
                            const startResponse = await axios.post('api_islemleri/esans_is_emirleri_islemler.php', {
                                action: 'start_work_order',
                                id: id
                            });

                            if (startResponse.data.status === 'success') {
                                Swal.fire({
                                    title: 'Başarılı!',
                                    text: startResponse.data.message,
                                    icon: 'success',
                                    confirmButtonText: 'Tamam'
                                });
                                await this.fetchWorkOrders(this.pagination.current_page); // Refresh the current page
                            } else {
                                Swal.fire({
                                    title: 'Hata!',
                                    text: startResponse.data.message,
                                    icon: 'error',
                                    confirmButtonText: 'Tamam'
                                });
                            }
                        }
                    });
                } else {
                    Swal.fire({
                        title: 'Hata!',
                        text: 'Onay icin bilesen listesi alinamadi: ' + response.data.message,
                        icon: 'error',
                        confirmButtonText: 'Tamam'
                    });
                }
            } catch (error) {
                Swal.fire({
                    title: 'Hata!',
                    text: 'Onay icin bilesen listesi alinirken bir sunucu hatasi olustu.',
                    icon: 'error',
                    confirmButtonText: 'Tamam'
                });

            }
        },
        async revertWorkOrder(id) {
            Swal.fire({
                title: 'Emin misiniz?',
                text: 'Bu is emrini durdurup "Olusturuldu" durumuna geri almak istediginizden emin misiniz?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Evet',
                cancelButtonText: 'İptal'
            }).then(async (result) => {
                if (!result.isConfirmed) {
                    return;
                }

                try {
                    const response = await axios.post('api_islemleri/esans_is_emirleri_islemler.php', {
                        action: 'revert_work_order',
                        id: id
                    });

                    if (response.data.status === 'success') {
                        Swal.fire({
                            title: 'Başarılı!',
                            text: response.data.message,
                            icon: 'success',
                            confirmButtonText: 'Tamam'
                        });
                        await this.fetchWorkOrders(this.pagination.current_page); // Refresh the current page
                    } else {
                        Swal.fire({
                            title: 'Hata!',
                            text: response.data.message,
                            icon: 'error',
                            confirmButtonText: 'Tamam'
                        });
                    }
                } catch (error) {
                    Swal.fire({
                        title: 'Hata!',
                        text: 'Islem sirasinda bir hata olustu.',
                        icon: 'error',
                        confirmButtonText: 'Tamam'
                    });
                }
            });
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
                    Swal.fire({
                        title: 'Hata!',
                        text: response.data.message,
                        icon: 'error',
                        confirmButtonText: 'Tamam'
                    });
                }
            } catch (error) {
                Swal.fire({
                    title: 'Hata!',
                    text: 'Is emri detaylari alinirken bir hata olustu.',
                    icon: 'error',
                    confirmButtonText: 'Tamam'
                });
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
                    Swal.fire({
                        title: 'Hata!',
                        text: 'Hata: ' + response.data.message,
                        icon: 'error',
                        confirmButtonText: 'Tamam'
                    });
                }
            } catch (error) {
                Swal.fire({
                    title: 'Hata!',
                    text: 'Islem sirasinda sunucu taraflı bir hata olustu.',
                    icon: 'error',
                    confirmButtonText: 'Tamam'
                });
            }
        },
        async revertCompletion(id) {
            // Find the work order in our list to verify it exists
            const workOrder = this.workOrders.find(wo => wo.is_emri_numarasi == id);

            if (!id) {
                Swal.fire({
                    title: 'Hata!',
                    text: 'İş emri numarası gerekli.',
                    icon: 'error',
                    confirmButtonText: 'Tamam'
                });
                return;
            }

            // Check if ID is valid
            if (typeof id === 'undefined' || id === null || id === '') {
                Swal.fire({
                    title: 'Hata!',
                    text: 'Geçersiz iş emri numarası.',
                    icon: 'error',
                    confirmButtonText: 'Tamam'
                });
                return;
            }

            Swal.fire({
                title: 'Emin misiniz?',
                text: 'Bu is emrinin tamamlanma durumunu geri almak istediginizden emin misiniz? Bu islem ilgili stok hareketlerini tersine cevirecektir.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Evet',
                cancelButtonText: 'İptal'
            }).then(async (result) => {
                if (result.isConfirmed) {
                    try {
                        const response = await axios.post('api_islemleri/esans_is_emirleri_islemler.php', {
                            action: 'revert_completion',
                            id: id
                        });

                        if (response.data.status === 'success') {
                            Swal.fire({
                                title: 'Başarılı!',
                                text: response.data.message,
                                icon: 'success',
                                confirmButtonText: 'Tamam'
                            });
                            await this.fetchWorkOrders(this.pagination.current_page); // Refresh the current page
                        } else {
                            Swal.fire({
                                title: 'Hata!',
                                text: response.data.message,
                                icon: 'error',
                                confirmButtonText: 'Tamam'
                            });
                        }
                    } catch (error) {
                        Swal.fire({
                            title: 'Hata!',
                            text: 'Geri alma islemi sirasinda bir hata olustu.',
                            icon: 'error',
                            confirmButtonText: 'Tamam'
                        });

                    }
                }
            });
        },
        async showWorkOrderDetails(id) {
            try {
                const response = await axios.get(`api_islemleri/esans_is_emirleri_islemler.php?action=get_work_order_components&id=${id}`);

                if (response.data.status === 'success') {
                    this.workOrderComponents = response.data.data;
                    this.selectedWorkOrderId = id;
                    this.showDetailsModal = true;
                } else {
                    Swal.fire({
                        title: 'Hata!',
                        text: 'Malzeme detayları alınırken bir hata oluştu: ' + response.data.message,
                        icon: 'error',
                        confirmButtonText: 'Tamam'
                    });
                }
            } catch (error) {
                Swal.fire({
                    title: 'Hata!',
                    text: 'Malzeme detayları alınırken bir sunucu hatası oluştu.',
                    icon: 'error',
                    confirmButtonText: 'Tamam'
                });
            }
        },
        async printWorkOrder(id) {
            Swal.fire({
                title: 'Lütfen bekleyin...',
                text: 'PDF oluşturuluyor, lütfen bekleyin...',
                icon: 'info',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false
            });

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
                        Swal.close(); // Close the loading alert
                        Swal.fire({
                            title: 'Başarılı!',
                            text: 'PDF başarıyla oluşturuldu ve indirildi.',
                            icon: 'success',
                            confirmButtonText: 'Tamam'
                        });
                    });
                } else {
                    Swal.close(); // Close the loading alert
                    Swal.fire({
                        title: 'Hata!',
                        text: 'PDF oluşturmak için veriler alınırken bir hata oluştu.',
                        icon: 'error',
                        confirmButtonText: 'Tamam'
                    });
                }
            } catch (error) {
                Swal.close(); // Close the loading alert
                Swal.fire({
                    title: 'Hata!',
                    text: 'PDF oluşturulurken bir sunucu hatası oluştu.',
                    icon: 'error',
                    confirmButtonText: 'Tamam'
                });
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

    }
});

