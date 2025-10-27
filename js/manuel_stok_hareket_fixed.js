new Vue({
    el: '#app',
    data: {
        user_name: '', // Will be set from PHP via data attribute
        movements: [],
        total_movements: 0,
        alert: {
            message: '',
            type: 'success' // 'success', 'danger', 'warning', 'info'
        },
        movementModalVisible: false,
        transferModalVisible: false,
        sayimFazlasiModalVisible: false,
        isSubmitting: false,
        isEdit: false,
        issayimFazlasiEdit: false,
        isTransferAutoFill: true,
        movementForm: {
            hareket_id: '',
            stok_turu: '',
            kod: '',
            yon: '',
            hareket_turu: '',
            miktar: '',
            ilgili_belge_no: '',
            aciklama: '',
            depo: '',
            raf: '',
            tank_kodu: ''
        },
        transferForm: {
            stok_turu: '',
            kod: '',
            miktar: '',
            ilgili_belge_no: '',
            aciklama: 'Stok transferi',
            kaynak_depo: '',
            kaynak_raf: '',
            hedef_depo: '',
            hedef_raf: '',
            tank_kodu: '',
            hedef_tank_kodu: ''
        },
        sayimFazlasiForm: {
            hareket_id: '',
            stok_turu: '',
            kod: '',
            yon: 'giris', // Otomatik olarak giris
            hareket_turu: 'sayim_fazlasi', // Otomatik olarak Sayim Fazlasi
            miktar: '',
            ilgili_belge_no: '',
            aciklama: '',
            depo: '',
            raf: '',
            tank_kodu: ''
        },

        firesayimEksigiModalVisible: false,
        isFiresayimEksigiEdit: false,
        firesayimEksigiForm: {
            hareket_id: '',
            stok_turu: '',
            kod: '',
            yon: 'cikis', // Otomatik olarak cikis
            hareket_turu: '', // fire veya sayim_Eksigi
            miktar: '',
            ilgili_belge_no: '',
            aciklama: '',
            depo: '',
            raf: '',
            tank_kodu: ''
        },
        firesayimEksigiStockItems: [],
        firesayimEksigiMovementTypes: [],
        firesayimEksigiSubmitButtonText: 'Kaydet',
        stockItems: [],
        transferStockItems: [],
        sayimFazlasiStockItems: [],

        locations: [],
        tanks: [], // Tanks for essence transfers
        hedefRaflar: [], // Shelves for selected target depot
        movementTypes: [],
        submitButtonText: 'Kaydet',
        
        // Mal Kabul specific data
        malKabulModalVisible: false,
        malKabulForm: {
            hareket_id: '',
            stok_turu: 'malzeme', // Default to 'malzeme'
            kod: '',
            yon: 'giris', // Otomatik olarak giris
            hareket_turu: 'mal_kabul', // Otomatik olarak mal kabul
            miktar: '',
            ilgili_belge_no: '',
            aciklama: '',
            depo: '',
            raf: '',
            tank_kodu: '',
            tedarikci: ''
        },
        malKabulStockItems: [],
        malKabulSuppliers: []
    },
    computed: {
        movementFormTitle() {
            return this.isEdit ? 'Stok Hareketini Duzenle' : 'Yeni Stok Hareketi';
        },
        firesayimEksigiFormTitle() {
            return this.isFiresayimEksigiEdit ? 'Stok Hareketini Duzenle' : 'Fire / Sayim Eksigi';
        },
        uniqueDepolar() {
            // Return unique depot names for selection
            return this.locations.map(location => ({
                depo_ismi: location.depo_ismi
            }));
        }
    },
    mounted() {
        // Set user name from data attribute
        const appElement = document.getElementById('app');
        if (appElement && appElement.dataset.username) {
            this.user_name = appElement.dataset.username;
        }
        this.loadMovements();
        this.loadLocations();
        this.loadTanks(); // Load tanks for essence transfers
        this.updateMovementTypes();
    },
    methods: {
        loadMovements() {
            // API call to load movements - using the correct action
            axios.get('api_islemleri/stok_hareket_islemler.php?action=get_all_movements')
                .then(response => {
                    if (response.data.status === 'success') {
                        this.movements = response.data.data || [];
                        // Also load total count
                        this.loadTotalMovements();
                    } else {
                        this.showAlert(response.data.message || 'Hareketler yuklenirken bir hata olustu', 'danger');
                    }
                })
                .catch(error => {
                    this.showAlert('Hareketler yuklenirken bir hata olustu', 'danger');
                });
        },
        loadTotalMovements() {
            // API call to get total movements count
            axios.get('api_islemleri/stok_hareket_islemler.php?action=get_total_movements')
                .then(response => {
                    if (response.data.status === 'success') {
                        this.total_movements = response.data.data || 0;
                    } else {
                        this.showAlert(response.data.message || 'Toplam hareket sayisi alinirken bir hata olustu', 'danger');
                    }
                })
                .catch(error => {
                    this.showAlert('Toplam hareket sayisi alinirken bir hata olustu', 'danger');
                });
        },
        loadLocations() {
            // API call to load locations
            axios.get('api_islemleri/stok_hareket_islemler.php?action=get_locations')
                .then(response => {
                    if (response.data.status === 'success') {
                        this.locations = response.data.data || [];
                    } else {
                        this.showAlert(response.data.message || 'Lokasyonlar yuklenirken bir hata olustu', 'danger');
                    }
                })
                .catch(error => {
                    this.showAlert('Lokasyonlar yuklenirken bir hata olustu', 'danger');
                });
        },
        loadTanks() {
            // API call to load tanks for essence transfers
            axios.get('api_islemleri/tanklar_islemler.php?action=get_tanks')
                .then(response => {
                    if (response.data.status === 'success') {
                        this.tanks = response.data.data || [];
                    } else {
                        this.showAlert(response.data.message || 'Tanklar yuklenirken bir hata olustu', 'danger');
                    }
                })
                .catch(error => {
                    this.showAlert('Tanklar yuklenirken bir hata olustu', 'danger');
                });
        },
        loadStockItems() {
            if (!this.movementForm.stok_turu) {
                this.stockItems = [];
                // Clear location fields when no stock type is selected
                this.movementForm.kod = '';
                this.movementForm.depo = '';
                this.movementForm.raf = '';
                this.movementForm.tank_kodu = '';
                return;
            }
            
            // API call to load stock items based on type
            axios.get(`api_islemleri/stok_hareket_islemler.php?action=get_stock_items&type=${this.movementForm.stok_turu}`)
                .then(response => {
                    if (response.data.status === 'success') {
                        this.stockItems = response.data.data || [];
                        // Clear the selected item if it's not in the new list
                        if (this.movementForm.kod && !this.stockItems.some(item => item.kod === this.movementForm.kod)) {
                            this.movementForm.kod = '';
                            this.movementForm.depo = '';
                            this.movementForm.raf = '';
                            this.movementForm.tank_kodu = '';
                        }
                    } else {
                        this.showAlert(response.data.message || 'Stok urunleri yuklenirken bir hata olustu', 'danger');
                    }
                })
                .catch(error => {
                    this.showAlert('Stok urunleri yuklenirken bir hata olustu', 'danger');
                });
        },
        loadTransferStockItems() {
            if (!this.transferForm.stok_turu) {
                this.transferStockItems = [];
                // Clear location fields when no stock type is selected
                this.transferForm.kod = '';
                this.transferForm.kaynak_depo = '';
                this.transferForm.kaynak_raf = '';
                this.transferForm.tank_kodu = '';
                return;
            }
            
            // API call to load transfer stock items based on type
            axios.get(`api_islemleri/stok_hareket_islemler.php?action=get_stock_items&type=${this.transferForm.stok_turu}`)
                .then(response => {
                    if (response.data.status === 'success') {
                        this.transferStockItems = response.data.data || [];
                        // Clear the selected item if it's not in the new list
                        if (this.transferForm.kod && !this.transferStockItems.some(item => item.kod === this.transferForm.kod)) {
                            this.transferForm.kod = '';
                            this.transferForm.kaynak_depo = '';
                            this.transferForm.kaynak_raf = '';
                            this.transferForm.tank_kodu = '';
                        }
                    } else {
                        this.showAlert(response.data.message || 'Transfer urunleri yuklenirken bir hata olustu', 'danger');
                    }
                })
                .catch(error => {
                    this.showAlert('Transfer urunleri yuklenirken bir hata olustu', 'danger');
                });
        },
        loadsayimFazlasiStockItems() {
            if (!this.sayimFazlasiForm.stok_turu) {
                this.sayimFazlasiStockItems = [];
                // Clear location fields when no stock type is selected
                this.sayimFazlasiForm.kod = '';
                this.sayimFazlasiForm.depo = '';
                this.sayimFazlasiForm.raf = '';
                this.sayimFazlasiForm.tank_kodu = '';
                return;
            }
            
            // API call to load stock items based on type for Sayim Fazlasi
            axios.get(`api_islemleri/stok_hareket_islemler.php?action=get_stock_items&type=${this.sayimFazlasiForm.stok_turu}`)
                .then(response => {
                    if (response.data.status === 'success') {
                        this.sayimFazlasiStockItems = response.data.data || [];
                        // Clear the selected item if it's not in the new list
                        if (this.sayimFazlasiForm.kod && !this.sayimFazlasiStockItems.some(item => item.kod === this.sayimFazlasiForm.kod)) {
                            this.sayimFazlasiForm.kod = '';
                            this.sayimFazlasiForm.depo = '';
                            this.sayimFazlasiForm.raf = '';
                            this.sayimFazlasiForm.tank_kodu = '';
                        }
                    } else {
                        this.showAlert(response.data.message || 'Stok urunleri yuklenirken bir hata olustu', 'danger');
                    }
                })
                .catch(error => {
                    this.showAlert('Stok urunleri yuklenirken bir hata olustu', 'danger');
                });
        },
        getStockLocation() {
            if (!this.movementForm.stok_turu || !this.movementForm.kod) {
                // Clear location fields when no item is selected
                this.movementForm.depo = '';
                this.movementForm.raf = '';
                this.movementForm.tank_kodu = '';
                return;
            }
            
            // API call to get current location
            axios.get(`api_islemleri/stok_hareket_islemler.php?action=get_current_location&stock_type=${this.movementForm.stok_turu}&item_code=${this.movementForm.kod}`)
                .then(response => {
                    if (response.data.status === 'success') {
                        const location = response.data.data;
                        this.movementForm.depo = location.depo || '';
                        this.movementForm.raf = location.raf || '';
                        this.movementForm.tank_kodu = location.tank_kodu || '';
                    } else {
                        this.showAlert(response.data.message || 'Lokasyon bilgisi alinirken bir hata olustu', 'warning');
                    }
                })
                .catch(error => {
                    this.showAlert('Lokasyon bilgisi alinirken bir hata olustu', 'danger');
                });
        },
        getTransferStockLocation() {
            if (!this.transferForm.stok_turu || !this.transferForm.kod) {
                // Clear location fields when no item is selected
                this.transferForm.kaynak_depo = '';
                this.transferForm.kaynak_raf = '';
                this.transferForm.tank_kodu = '';
                this.transferForm.miktar = '';
                return;
            }
            
            // API call to get current location for transfer
            this.isTransferAutoFill = true;
            axios.get(`api_islemleri/stok_hareket_islemler.php?action=get_current_location&stock_type=${this.transferForm.stok_turu}&item_code=${this.transferForm.kod}`)
                .then(response => {
                    if (response.data.status === 'success') {
                        const location = response.data.data;
                        this.transferForm.kaynak_depo = location.depo || '';
                        this.transferForm.kaynak_raf = location.raf || '';
                        this.transferForm.tank_kodu = location.tank_kodu || '';
                        this.transferForm.miktar = location.stok_miktari || 0;
                        this.showAlert(`Kaynak konum ve miktar otomatik olarak dolduruldu: ${location.stok_miktari}`, 'info');
                    } else {
                        this.showAlert(response.data.message || 'Lokasyon bilgisi alinirken bir hata olustu', 'warning');
                        this.isTransferAutoFill = false;
                    }
                })
                .catch(error => {
                    this.showAlert('Lokasyon bilgisi alinirken bir hata olustu', 'danger');
                    this.isTransferAutoFill = false;
                });
        },
        getsayimFazlasiStockLocation() {
            if (!this.sayimFazlasiForm.stok_turu || !this.sayimFazlasiForm.kod) {
                // Clear location fields when no item is selected
                this.sayimFazlasiForm.depo = '';
                this.sayimFazlasiForm.raf = '';
                this.sayimFazlasiForm.tank_kodu = '';
                return;
            }
            
            // API call to get current location for Sayim Fazlasi
            axios.get(`api_islemleri/stok_hareket_islemler.php?action=get_current_location&stock_type=${this.sayimFazlasiForm.stok_turu}&item_code=${this.sayimFazlasiForm.kod}`)
                .then(response => {
                    if (response.data.status === 'success') {
                        const location = response.data.data;
                        this.sayimFazlasiForm.depo = location.depo || '';
                        this.sayimFazlasiForm.raf = location.raf || '';
                        this.sayimFazlasiForm.tank_kodu = location.tank_kodu || '';
                    } else {
                        this.showAlert(response.data.message || 'Lokasyon bilgisi alinirken bir hata olustu', 'warning');
                    }
                })
                .catch(error => {
                    this.showAlert('Lokasyon bilgisi alinirken bir hata olustu', 'danger');
                });
        },
        updateMovementTypes() {
            // Define movement types based on direction
            if (this.movementForm.yon === 'giris') {
                this.movementTypes = [
                    { value: 'sayim_fazlasi', label: 'Sayim Fazlasi' },
                    { value: 'stok_giris', label: 'Stok Giris' },
                    { value: 'uretim', label: 'Uretim' },
                    { value: 'uretim_giris', label: 'Uretim Giris' },
                    { value: 'iade_girisi', label: 'Iade Giris' },
                    { value: 'sayim_farki', label: 'Sayim Farki (Artis)' },
                    { value: 'stok_duzeltme', label: 'Stok Duzeltme (Artis)' },
                    { value: 'transfer', label: 'Transfer (Giris)' },
                    { value: 'numune_girisi', label: 'Numune Giris' }
                ];
            } else if (this.movementForm.yon === 'cikis') {
                this.movementTypes = [
                    { value: 'stok_cikis', label: 'Stok Cikisi' },
                    { value: 'uretimde_kullanim', label: 'Uretimde Kullanim' },
                    { value: 'fire', label: 'Fire' },
                    { value: 'numune_cikisi', label: 'Numune Cikisi' },
                    { value: 'tedarikciye_iade', label: 'Tedarikciye Iade' },
                    { value: 'montaj', label: 'Montaj' },
                    { value: 'sayim_farki', label: 'Sayim Farki (Azalis)' },
                    { value: 'stok_duzeltme', label: 'Stok Duzeltme (Azalis)' },
                    { value: 'transfer', label: 'Transfer (Cikis)' },
                    { value: 'fire_cikisi', label: 'Fire Cikisi' },
                    { value: 'sayim_Eksigi', label: 'Sayim Eksigi' }
                ];
            } else {
                this.movementTypes = [];
            }
            
            // Set default movement type if not already set
            if (!this.movementForm.hareket_turu) {
                if (this.movementForm.yon === 'giris') {
                    this.movementForm.hareket_turu = 'sayim_fazlasi';
                } else if (this.movementForm.yon === 'cikis') {
                    this.movementForm.hareket_turu = 'sayim_Eksigi';
                }
            }
        },
        opensayimFazlasiModal() {
            this.resetsayimFazlasiForm();
            this.issayimFazlasiEdit = false;
            this.sayimFazlasiModalVisible = true;
        },

        openMovementForm(yon = null, hareket_turu = null) {
            this.resetMovementForm();
            this.isEdit = false;
            this.submitButtonText = 'Kaydet';
            this.movementForm.yon = yon || '';
            this.movementForm.hareket_turu = hareket_turu || '';
            this.updateMovementTypes();
            this.movementModalVisible = true;
        },
        editMovement(movement) {
            // Check if this is a Sayim Fazlasi movement
            if (movement.hareket_turu === 'sayim_fazlasi') {
                // Set the data in the Sayim Fazlasi form
                this.sayimFazlasiForm = { ...movement };
                this.issayimFazlasiEdit = true;
                this.submitButtonText = 'Guncelle';
                
                // Load stock items for the stock type
                this.loadsayimFazlasiStockItems();
                
                this.sayimFazlasiModalVisible = true;
            }
            // Check if this is a fire or Sayim Eksigi movement (exit movements)
            else if ((movement.yon === 'cikis') && (movement.hareket_turu === 'fire' || movement.hareket_turu === 'sayim_Eksigi')) {
                // Set the data in the fire/Sayim Eksigi form
                this.firesayimEksigiForm = { ...movement };
                this.isFiresayimEksigiEdit = true;
                this.firesayimEksigiSubmitButtonText = 'Guncelle';
                
                // Load stock items for the stock type
                this.loadFiresayimEksigiStockItems();
                
                // Update movement types for this modal
                this.updateFiresayimEksigiTypes();
                
                this.firesayimEksigiModalVisible = true;
            }
            else {
                // For other movements, use the regular form
                this.movementForm = { ...movement };
                this.isEdit = true;
                this.submitButtonText = 'Guncelle';
                this.loadStockItems(); // Load stock items for this stock type
                this.updateMovementTypes(); // Update movement types based on direction
                this.movementModalVisible = true;
            }
        },
        saveMovement() {
            this.isSubmitting = true;
            
            // Prepare the form data
            const formData = new FormData();
            Object.keys(this.movementForm).forEach(key => {
                if (this.movementForm[key] !== null) {
                    formData.append(key, this.movementForm[key]);
                }
            });
            
            // Determine the action based on whether we're editing
            formData.append('action', this.isEdit ? 'update_movement' : 'add_movement');
            
            axios.post('api_islemleri/stok_hareket_islemler.php', formData)
                .then(response => {
                    if (response.data.status === 'success') {
                        this.showAlert(response.data.message, 'success');
                        this.closeMovementModal();
                        this.loadMovements(); // Reload movements
                    } else {
                        this.showAlert(response.data.message || 'Islem sirasinda bir hata olustu', 'danger');
                    }
                })
                .catch(error => {
                    this.showAlert('Islem sirasinda bir hata olustu', 'danger');
                })
                .finally(() => {
                    this.isSubmitting = false;
                });
        },
        deleteMovement(id) {
            Swal.fire({
                title: 'Emin misiniz?',
                text: 'Bu stok hareketini silmek istediginizden emin misiniz?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Evet',
                cancelButtonText: 'Iptal'
            }).then((result) => {
                if (!result.isConfirmed) {
                    return;
                }

                const formData = new FormData();
                formData.append('action', 'delete_movement');
                formData.append('hareket_id', id);

                axios.post('api_islemleri/stok_hareket_islemler.php', formData)
                    .then(response => {
                        if (response.data.status === 'success') {
                            this.showAlert(response.data.message, 'success');
                            this.loadMovements(); // Reload movements
                        } else {
                            this.showAlert(response.data.message || 'Silme sirasinda bir hata olustu', 'danger');
                        }
                    })
                    .catch(error => {
                        this.showAlert('Silme sirasinda bir hata olustu', 'danger');
                    });
            });
        },
        openTransferModal() {
            this.resetTransferForm();
            this.transferModalVisible = true;
            this.loadTransferStockItems(); // Load transfer stock items
        },
        saveTransfer() {
            this.isSubmitting = true;
            
            // Validate form
            if (!this.transferForm.stok_turu || !this.transferForm.kod || !this.transferForm.miktar || this.transferForm.miktar <= 0) {
                this.showAlert('Lutfen tum zorunlu alanlari doldurun.', 'danger');
                this.isSubmitting = false;
                return;
            }
            
            // For essence transfers, validate hedef tank selection
            if (this.transferForm.stok_turu === 'esans' && (!this.transferForm.hedef_tank_kodu)) {
                this.showAlert('Lutfen hedef tanki secin.', 'danger');
                this.isSubmitting = false;
                return;
            }
            
            // For material/product transfers, validate depot/raf
            if (this.transferForm.stok_turu !== 'esans' && (!this.transferForm.hedef_depo || !this.transferForm.hedef_raf)) {
                this.showAlert('Lutfen hedef konumu belirtin.', 'danger');
                this.isSubmitting = false;
                return;
            }

            const formData = new FormData();
            Object.keys(this.transferForm).forEach(key => {
                if (this.transferForm[key] !== null) {
                    formData.append(key, this.transferForm[key]);
                }
            });
            
            formData.append('action', 'transfer_stock');
            
            axios.post('api_islemleri/stok_hareket_islemler.php', formData)
                .then(response => {
                    if (response.data.status === 'success') {
                        this.showAlert(response.data.message, 'success');
                        this.closeTransferModal();
                        this.loadMovements(); // Reload movements
                    } else {
                        this.showAlert(response.data.message || 'Transfer sirasinda bir hata olustu', 'danger');
                    }
                })
                .catch(error => {
                    this.showAlert('Transfer sirasinda bir hata olustu', 'danger');
                })
                .finally(() => {
                    this.isSubmitting = false;
                });
        },
        savesayimFazlasi() {
            this.isSubmitting = true;
            
            // Prepare the form data
            const formData = new FormData();
            Object.keys(this.sayimFazlasiForm).forEach(key => {
                if (this.sayimFazlasiForm[key] !== null) {
                    formData.append(key, this.sayimFazlasiForm[key]);
                }
            });
            
            // Determine the action based on whether we're editing
            formData.append('action', this.issayimFazlasiEdit ? 'update_movement' : 'add_movement');
            
            axios.post('api_islemleri/stok_hareket_islemler.php', formData)
                .then(response => {
                    if (response.data.status === 'success') {
                        this.showAlert(response.data.message, 'success');
                        this.closesayimFazlasiModal();
                        this.loadMovements(); // Reload movements
                    } else {
                        this.showAlert(response.data.message || 'Islem sirasinda bir hata olustu', 'danger');
                    }
                })
                .catch(error => {
                    this.showAlert('Islem sirasinda bir hata olustu', 'danger');
                })
                .finally(() => {
                    this.isSubmitting = false;
                });
        },
        closeMovementModal() {
            this.movementModalVisible = false;
        },
        closeTransferModal() {
            this.transferModalVisible = false;
        },
        closesayimFazlasiModal() {
            this.sayimFazlasiModalVisible = false;
        },
        resetMovementForm() {
            this.movementForm = {
                hareket_id: '',
                stok_turu: '',
                kod: '',
                yon: '',
                hareket_turu: '',
                miktar: '',
                ilgili_belge_no: '',
                aciklama: '',
                depo: '',
                raf: '',
                tank_kodu: ''
            };
            this.stockItems = [];
        },
        resetTransferForm() {
            this.transferForm = {
                stok_turu: '',
                kod: '',
                miktar: '',
                ilgili_belge_no: '',
                aciklama: 'Stok transferi',
                kaynak_depo: '',
                kaynak_raf: '',
                hedef_depo: '',
                hedef_raf: '',
                tank_kodu: '',
                hedef_tank_kodu: ''
            };
            this.transferStockItems = [];
        },
        resetsayimFazlasiForm() {
            this.sayimFazlasiForm = {
                hareket_id: '',
                stok_turu: '',
                kod: '',
                yon: 'giris', // Otomatik olarak giris
                hareket_turu: 'sayim_fazlasi', // Otomatik olarak Sayim Fazlasi
                miktar: '',
                ilgili_belge_no: '',
                aciklama: '',
                depo: '',
                raf: '',
                tank_kodu: ''
            };
            this.sayimFazlasiStockItems = [];
        },
        openFiresayimEksigiModal() {
            this.resetFiresayimEksigiForm();
            this.isFiresayimEksigiEdit = false;
            this.firesayimEksigiSubmitButtonText = 'Kaydet';
            
            // Update movement types for this modal
            this.updateFiresayimEksigiTypes();
            
            this.firesayimEksigiModalVisible = true;
        },
        closeFiresayimEksigiModal() {
            this.firesayimEksigiModalVisible = false;
        },
        resetFiresayimEksigiForm() {
            this.firesayimEksigiForm = {
                hareket_id: '',
                stok_turu: '',
                kod: '',
                yon: 'cikis', // Otomatik olarak cikis
                hareket_turu: '', // fire veya sayim_Eksigi
                miktar: '',
                ilgili_belge_no: '',
                aciklama: '',
                depo: '',
                raf: '',
                tank_kodu: ''
            };
            this.firesayimEksigiStockItems = [];
        },
        loadFiresayimEksigiStockItems() {
            if (!this.firesayimEksigiForm.stok_turu) {
                this.firesayimEksigiStockItems = [];
                // Clear location fields when no stock type is selected
                this.firesayimEksigiForm.kod = '';
                this.firesayimEksigiForm.depo = '';
                this.firesayimEksigiForm.raf = '';
                this.firesayimEksigiForm.tank_kodu = '';
                return;
            }
            
            // API call to load stock items based on type for fire/Sayim Eksigi
            axios.get(`api_islemleri/stok_hareket_islemler.php?action=get_stock_items&type=${this.firesayimEksigiForm.stok_turu}`)
                .then(response => {
                    if (response.data.status === 'success') {
                        this.firesayimEksigiStockItems = response.data.data || [];
                        // Clear the selected item if it's not in the new list
                        if (this.firesayimEksigiForm.kod && !this.firesayimEksigiStockItems.some(item => item.kod === this.firesayimEksigiForm.kod)) {
                            this.firesayimEksigiForm.kod = '';
                            this.firesayimEksigiForm.depo = '';
                            this.firesayimEksigiForm.raf = '';
                            this.firesayimEksigiForm.tank_kodu = '';
                        }
                    } else {
                        this.showAlert(response.data.message || 'Stok urunleri yuklenirken bir hata olustu', 'danger');
                    }
                })
                .catch(error => {
                    this.showAlert('Stok urunleri yuklenirken bir hata olustu', 'danger');
                });
        },
        getFiresayimEksigiLocation() {
            if (!this.firesayimEksigiForm.stok_turu || !this.firesayimEksigiForm.kod) {
                // Clear location fields when no item is selected
                this.firesayimEksigiForm.depo = '';
                this.firesayimEksigiForm.raf = '';
                this.firesayimEksigiForm.tank_kodu = '';
                return;
            }
            
            // API call to get current location for fire/Sayim Eksigi
            axios.get(`api_islemleri/stok_hareket_islemler.php?action=get_current_location&stock_type=${this.firesayimEksigiForm.stok_turu}&item_code=${this.firesayimEksigiForm.kod}`)
                .then(response => {
                    if (response.data.status === 'success') {
                        const location = response.data.data;
                        this.firesayimEksigiForm.depo = location.depo || '';
                        this.firesayimEksigiForm.raf = location.raf || '';
                        this.firesayimEksigiForm.tank_kodu = location.tank_kodu || '';
                    } else {
                        this.showAlert(response.data.message || 'Lokasyon bilgisi alinirken bir hata olustu', 'warning');
                    }
                })
                .catch(error => {
                    this.showAlert('Lokasyon bilgisi alinirken bir hata olustu', 'danger');
                });
        },
        updateFiresayimEksigiTypes() {
            // For the fire/Sayim Eksigi modal, only allow fire and sayim_Eksigi
            this.firesayimEksigiMovementTypes = [
                { value: 'fire', label: 'Fire' },
                { value: 'sayim_Eksigi', label: 'Sayim Eksigi' }
            ];
            
            // If a movement type is not selected, default to 'fire'
            if (!this.firesayimEksigiForm.hareket_turu) {
                this.firesayimEksigiForm.hareket_turu = 'fire';
            }
        },
        saveFiresayimEksigi() {
            this.isSubmitting = true;
            
            // Prepare the form data
            const formData = new FormData();
            Object.keys(this.firesayimEksigiForm).forEach(key => {
                if (this.firesayimEksigiForm[key] !== null) {
                    formData.append(key, this.firesayimEksigiForm[key]);
                }
            });
            
            // Determine the action based on whether we're editing
            formData.append('action', this.isFiresayimEksigiEdit ? 'update_movement' : 'add_movement');
            
            axios.post('api_islemleri/stok_hareket_islemler.php', formData)
                .then(response => {
                    if (response.data.status === 'success') {
                        this.showAlert(response.data.message, 'success');
                        this.closeFiresayimEksigiModal();
                        this.loadMovements(); // Reload movements
                    } else {
                        this.showAlert(response.data.message || 'Islem sirasinda bir hata olustu', 'danger');
                    }
                })
                .catch(error => {
                    this.showAlert('Islem sirasinda bir hata olustu', 'danger');
                })
                .finally(() => {
                    this.isSubmitting = false;
                });
        },
        
        openMalKabulModal() {
            this.resetMalKabulForm();
            this.malKabulModalVisible = true;
            this.loadMalKabulStockItems(); // Load materials when modal opens
        },
        
        closeMalKabulModal() {
            this.malKabulModalVisible = false;
        },
        
        resetMalKabulForm() {
            this.malKabulForm = {
                hareket_id: '',
                stok_turu: 'malzeme', // Default to 'malzeme'
                kod: '',
                yon: 'giris', // Otomatik olarak giris
                hareket_turu: 'mal_kabul', // Otomatik olarak mal kabul
                miktar: '',
                ilgili_belge_no: '',
                aciklama: '',
                depo: '',
                raf: '',
                tank_kodu: '',
                tedarikci: ''
            };
            this.malKabulStockItems = [];
            this.malKabulSuppliers = [];
        },
        
        loadMalKabulStockItems() {
            // Since stok_turu is fixed to 'malzeme', we can call it directly.
            axios.get(`api_islemleri/stok_hareket_islemler.php?action=get_stock_items&type=malzeme`)
                .then(response => {
                    if (response.data.status === 'success') {
                        this.malKabulStockItems = response.data.data || [];
                        // Clear the selected item if it's not in the new list
                        if (this.malKabulForm.kod && !this.malKabulStockItems.some(item => item.kod === this.malKabulForm.kod)) {
                            this.malKabulForm.kod = '';
                            this.malKabulForm.depo = '';
                            this.malKabulForm.raf = '';
                            this.malKabulForm.tank_kodu = '';
                        }
                    } else {
                        this.showAlert(response.data.message || 'Stok urunleri yuklenirken bir hata olustu', 'danger');
                    }
                })
                .catch(error => {
                    this.showAlert('Stok urunleri yuklenirken bir hata olustu', 'danger');
                });
        },
        
        loadMalKabulSuppliers() {
            if (!this.malKabulForm.kod) {
                this.malKabulSuppliers = [];
                this.malKabulForm.tedarikci = '';
                return;
            }
            
            // Reset current selection when loading new suppliers
            this.malKabulForm.tedarikci = '';

            // API call to load suppliers based on selected material from cerceve_sozlesmeler table
            axios.get(`api_islemleri/stok_hareket_islemler.php?action=get_suppliers_for_material&material_code=${this.malKabulForm.kod}`)
                .then(response => {
                    if (response.data.status === 'success') {
                        this.malKabulSuppliers = response.data.data || [];
                        // Auto-selection logic removed to allow manual selection every time.
                        /* if (this.malKabulSuppliers.length === 1) {
                            this.malKabulForm.tedarikci = this.malKabulSuppliers[0].tedarikci_ismi;
                        } */
                    } else {
                        this.malKabulSuppliers = []; // Clear suppliers on error
                        this.showAlert(response.data.message || 'Tedarikciler yuklenirken bir hata olustu', 'danger');
                    }
                })
                .catch(error => {
                    this.showAlert('Tedarikciler yuklenirken bir hata olustu', 'danger');
                });
        },
        
        saveMalKabul() {
            this.isSubmitting = true;
            
            // Prepare the form data
            const formData = new FormData();
            Object.keys(this.malKabulForm).forEach(key => {
                if (this.malKabulForm[key] !== null) {
                    formData.append(key, this.malKabulForm[key]);
                }
            });
            
            formData.append('action', 'add_mal_kabul');
            
            axios.post('api_islemleri/stok_hareket_islemler.php', formData)
                .then(response => {
                    if (response.data.status === 'success') {
                        this.showAlert(response.data.message, 'success');
                        this.closeMalKabulModal();
                        this.loadMovements(); // Reload movements
                    } else {
                        this.showAlert(response.data.message || 'Islem sirasinda bir hata olustu', 'danger');
                    }
                })
                .catch(error => {
                    this.showAlert('Islem sirasinda bir hata olustu', 'danger');
                })
                .finally(() => {
                    this.isSubmitting = false;
                });
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
                title: icon === 'success' ? 'Basarili!' : 'Bilgi',
                text: message,
                icon: icon,
                confirmButtonText: 'Tamam'
            });
        },
        clearAlert() {
            this.alert.message = '';
            this.alert.type = 'success';
        },


        updateHedefRaflar() {
            // Update shelves based on selected target depot
            this.hedefRaflar = [];
            this.transferForm.hedef_raf = ''; // Clear selected shelf
            
            if (this.transferForm.hedef_depo) {
                // Find the selected depot and get its shelves
                const selectedDepo = this.locations.find(location => location.depo_ismi === this.transferForm.hedef_depo);
                if (selectedDepo) {
                    this.hedefRaflar = selectedDepo.raflar || [];
                }
            }
        },
        formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleString('tr-TR');
        },
        formatNumber(num) {
            return parseFloat(num).toFixed(2).replace('.', ',');
        }
    }
});

