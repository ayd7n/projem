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
        isSayimFazlasiEdit: false,
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
            yon: 'giris', // Otomatik olarak giriş
            hareket_turu: 'sayim_fazlasi', // Otomatik olarak sayım fazlası
            miktar: '',
            ilgili_belge_no: '',
            aciklama: '',
            depo: '',
            raf: '',
            tank_kodu: ''
        },

        fireSayimEksigiModalVisible: false,
        isFireSayimEksigiEdit: false,
        fireSayimEksigiForm: {
            hareket_id: '',
            stok_turu: '',
            kod: '',
            yon: 'cikis', // Otomatik olarak çıkış
            hareket_turu: '', // fire veya sayim_eksigi
            miktar: '',
            ilgili_belge_no: '',
            aciklama: '',
            depo: '',
            raf: '',
            tank_kodu: ''
        },
        fireSayimEksigiStockItems: [],
        fireSayimEksigiMovementTypes: [],
        fireSayimEksigiSubmitButtonText: 'Kaydet',
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
            yon: 'giris', // Otomatik olarak giriş
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
            return this.isEdit ? 'Stok Hareketini Düzenle' : 'Yeni Stok Hareketi';
        },
        fireSayimEksigiFormTitle() {
            return this.isFireSayimEksigiEdit ? 'Stok Hareketini Düzenle' : 'Fire / Sayım Eksigi';
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
                        this.showAlert(response.data.message || 'Hareketler yüklenirken bir hata oluştu', 'danger');
                    }
                })
                .catch(error => {
                    this.showAlert('Hareketler yüklenirken bir hata oluştu', 'danger');
                });
        },
        loadTotalMovements() {
            // API call to get total movements count
            axios.get('api_islemleri/stok_hareket_islemler.php?action=get_total_movements')
                .then(response => {
                    if (response.data.status === 'success') {
                        this.total_movements = response.data.data || 0;
                    } else {
                        this.showAlert(response.data.message || 'Toplam hareket sayısı alınırken bir hata oluştu', 'danger');
                    }
                })
                .catch(error => {
                    this.showAlert('Toplam hareket sayısı alınırken bir hata oluştu', 'danger');
                });
        },
        loadLocations() {
            // API call to load locations
            axios.get('api_islemleri/stok_hareket_islemler.php?action=get_locations')
                .then(response => {
                    if (response.data.status === 'success') {
                        this.locations = response.data.data || [];
                    } else {
                        this.showAlert(response.data.message || 'Lokasyonlar yüklenirken bir hata oluştu', 'danger');
                    }
                })
                .catch(error => {
                    this.showAlert('Lokasyonlar yüklenirken bir hata oluştu', 'danger');
                });
        },
        loadTanks() {
            // API call to load tanks for essence transfers
            axios.get('api_islemleri/tanklar_islemler.php?action=get_tanks')
                .then(response => {
                    if (response.data.status === 'success') {
                        this.tanks = response.data.data || [];
                    } else {
                        this.showAlert(response.data.message || 'Tanklar yüklenirken bir hata oluştu', 'danger');
                    }
                })
                .catch(error => {
                    this.showAlert('Tanklar yüklenirken bir hata oluştu', 'danger');
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
                        this.showAlert(response.data.message || 'Stok ürünleri yüklenirken bir hata oluştu', 'danger');
                    }
                })
                .catch(error => {
                    this.showAlert('Stok ürünleri yüklenirken bir hata oluştu', 'danger');
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
                        this.showAlert(response.data.message || 'Transfer ürünleri yüklenirken bir hata oluştu', 'danger');
                    }
                })
                .catch(error => {
                    this.showAlert('Transfer ürünleri yüklenirken bir hata oluştu', 'danger');
                });
        },
        loadSayimFazlasiStockItems() {
            if (!this.sayimFazlasiForm.stok_turu) {
                this.sayimFazlasiStockItems = [];
                // Clear location fields when no stock type is selected
                this.sayimFazlasiForm.kod = '';
                this.sayimFazlasiForm.depo = '';
                this.sayimFazlasiForm.raf = '';
                this.sayimFazlasiForm.tank_kodu = '';
                return;
            }
            
            // API call to load stock items based on type for sayım fazlası
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
                        this.showAlert(response.data.message || 'Stok ürünleri yüklenirken bir hata oluştu', 'danger');
                    }
                })
                .catch(error => {
                    this.showAlert('Stok ürünleri yüklenirken bir hata oluştu', 'danger');
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
                        this.showAlert(response.data.message || 'Lokasyon bilgisi alınırken bir hata oluştu', 'warning');
                    }
                })
                .catch(error => {
                    this.showAlert('Lokasyon bilgisi alınırken bir hata oluştu', 'danger');
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
                        this.showAlert(response.data.message || 'Lokasyon bilgisi alınırken bir hata oluştu', 'warning');
                        this.isTransferAutoFill = false;
                    }
                })
                .catch(error => {
                    this.showAlert('Lokasyon bilgisi alınırken bir hata oluştu', 'danger');
                    this.isTransferAutoFill = false;
                });
        },
        getSayimFazlasiStockLocation() {
            if (!this.sayimFazlasiForm.stok_turu || !this.sayimFazlasiForm.kod) {
                // Clear location fields when no item is selected
                this.sayimFazlasiForm.depo = '';
                this.sayimFazlasiForm.raf = '';
                this.sayimFazlasiForm.tank_kodu = '';
                return;
            }
            
            // API call to get current location for sayım fazlası
            axios.get(`api_islemleri/stok_hareket_islemler.php?action=get_current_location&stock_type=${this.sayimFazlasiForm.stok_turu}&item_code=${this.sayimFazlasiForm.kod}`)
                .then(response => {
                    if (response.data.status === 'success') {
                        const location = response.data.data;
                        this.sayimFazlasiForm.depo = location.depo || '';
                        this.sayimFazlasiForm.raf = location.raf || '';
                        this.sayimFazlasiForm.tank_kodu = location.tank_kodu || '';
                    } else {
                        this.showAlert(response.data.message || 'Lokasyon bilgisi alınırken bir hata oluştu', 'warning');
                    }
                })
                .catch(error => {
                    this.showAlert('Lokasyon bilgisi alınırken bir hata oluştu', 'danger');
                });
        },
        updateMovementTypes() {
            // Define movement types based on direction
            if (this.movementForm.yon === 'giris') {
                this.movementTypes = [
                    { value: 'sayim_fazlasi', label: 'Sayım Fazlası' },
                    { value: 'stok_giris', label: 'Stok Girişi' },
                    { value: 'uretim', label: 'Üretim' },
                    { value: 'uretim_giris', label: 'Üretim Girişi' },
                    { value: 'iade_girisi', label: 'İade Girişi' },
                    { value: 'sayim_farki', label: 'Sayım Farkı (Artış)' },
                    { value: 'stok_duzeltme', label: 'Stok Düzeltme (Artış)' },
                    { value: 'transfer', label: 'Transfer (Giriş)' },
                    { value: 'numune_girisi', label: 'Numune Girişi' }
                ];
            } else if (this.movementForm.yon === 'cikis') {
                this.movementTypes = [
                    { value: 'stok_cikis', label: 'Stok Çıkışı' },
                    { value: 'uretimde_kullanim', label: 'Üretimde Kullanım' },
                    { value: 'fire', label: 'Fire' },
                    { value: 'numune_cikisi', label: 'Numune Çıkışı' },
                    { value: 'tedarikciye_iade', label: 'Tedarikçiye İade' },
                    { value: 'montaj', label: 'Montaj' },
                    { value: 'sayim_farki', label: 'Sayım Farkı (Azalış)' },
                    { value: 'stok_duzeltme', label: 'Stok Düzeltme (Azalış)' },
                    { value: 'transfer', label: 'Transfer (Çıkış)' },
                    { value: 'fire_cikisi', label: 'Fire Çıkışı' },
                    { value: 'sayim_eksigi', label: 'Sayım Eksigi' }
                ];
            } else {
                this.movementTypes = [];
            }
            
            // Set default movement type if not already set
            if (!this.movementForm.hareket_turu) {
                if (this.movementForm.yon === 'giris') {
                    this.movementForm.hareket_turu = 'sayim_fazlasi';
                } else if (this.movementForm.yon === 'cikis') {
                    this.movementForm.hareket_turu = 'sayim_eksigi';
                }
            }
        },
        openSayimFazlasiModal() {
            this.resetSayimFazlasiForm();
            this.isSayimFazlasiEdit = false;
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
            // Check if this is a sayım fazlası movement
            if (movement.hareket_turu === 'sayim_fazlasi') {
                // Set the data in the sayım fazlası form
                this.sayimFazlasiForm = { ...movement };
                this.isSayimFazlasiEdit = true;
                this.submitButtonText = 'Güncelle';
                
                // Load stock items for the stock type
                this.loadSayimFazlasiStockItems();
                
                this.sayimFazlasiModalVisible = true;
            }
            // Check if this is a fire or sayım eksigi movement (exit movements)
            else if ((movement.yon === 'cikis') && (movement.hareket_turu === 'fire' || movement.hareket_turu === 'sayim_eksigi')) {
                // Set the data in the fire/sayım eksigi form
                this.fireSayimEksigiForm = { ...movement };
                this.isFireSayimEksigiEdit = true;
                this.fireSayimEksigiSubmitButtonText = 'Güncelle';
                
                // Load stock items for the stock type
                this.loadFireSayimEksigiStockItems();
                
                // Update movement types for this modal
                this.updateFireSayimEksigiTypes();
                
                this.fireSayimEksigiModalVisible = true;
            }
            else {
                // For other movements, use the regular form
                this.movementForm = { ...movement };
                this.isEdit = true;
                this.submitButtonText = 'Güncelle';
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
                        this.showAlert(response.data.message || 'İşlem sırasında bir hata oluştu', 'danger');
                    }
                })
                .catch(error => {
                    this.showAlert('İşlem sırasında bir hata oluştu', 'danger');
                })
                .finally(() => {
                    this.isSubmitting = false;
                });
        },
        deleteMovement(id) {
            Swal.fire({
                title: 'Emin misiniz?',
                text: 'Bu stok hareketini silmek istediğinizden emin misiniz?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Evet',
                cancelButtonText: 'İptal'
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
                            this.showAlert(response.data.message || 'Silme sırasında bir hata oluştu', 'danger');
                        }
                    })
                    .catch(error => {
                        this.showAlert('Silme sırasında bir hata oluştu', 'danger');
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
                this.showAlert('Lütfen tüm zorunlu alanları doldurun.', 'danger');
                this.isSubmitting = false;
                return;
            }
            
            // For essence transfers, validate hedef tank selection
            if (this.transferForm.stok_turu === 'esans' && (!this.transferForm.hedef_tank_kodu)) {
                this.showAlert('Lütfen hedef tankı seçin.', 'danger');
                this.isSubmitting = false;
                return;
            }
            
            // For material/product transfers, validate depot/raf
            if (this.transferForm.stok_turu !== 'esans' && (!this.transferForm.hedef_depo || !this.transferForm.hedef_raf)) {
                this.showAlert('Lütfen hedef konumu belirtin.', 'danger');
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
                        this.showAlert(response.data.message || 'Transfer sırasında bir hata oluştu', 'danger');
                    }
                })
                .catch(error => {
                    this.showAlert('Transfer sırasında bir hata oluştu', 'danger');
                })
                .finally(() => {
                    this.isSubmitting = false;
                });
        },
        saveSayimFazlasi() {
            this.isSubmitting = true;
            
            // Prepare the form data
            const formData = new FormData();
            Object.keys(this.sayimFazlasiForm).forEach(key => {
                if (this.sayimFazlasiForm[key] !== null) {
                    formData.append(key, this.sayimFazlasiForm[key]);
                }
            });
            
            // Determine the action based on whether we're editing
            formData.append('action', this.isSayimFazlasiEdit ? 'update_movement' : 'add_movement');
            
            axios.post('api_islemleri/stok_hareket_islemler.php', formData)
                .then(response => {
                    if (response.data.status === 'success') {
                        this.showAlert(response.data.message, 'success');
                        this.closeSayimFazlasiModal();
                        this.loadMovements(); // Reload movements
                    } else {
                        this.showAlert(response.data.message || 'İşlem sırasında bir hata oluştu', 'danger');
                    }
                })
                .catch(error => {
                    this.showAlert('İşlem sırasında bir hata oluştu', 'danger');
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
        closeSayimFazlasiModal() {
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
        resetSayimFazlasiForm() {
            this.sayimFazlasiForm = {
                hareket_id: '',
                stok_turu: '',
                kod: '',
                yon: 'giris', // Otomatik olarak giriş
                hareket_turu: 'sayim_fazlasi', // Otomatik olarak sayım fazlası
                miktar: '',
                ilgili_belge_no: '',
                aciklama: '',
                depo: '',
                raf: '',
                tank_kodu: ''
            };
            this.sayimFazlasiStockItems = [];
        },
        openFireSayimEksigiModal() {
            this.resetFireSayimEksigiForm();
            this.isFireSayimEksigiEdit = false;
            this.fireSayimEksigiSubmitButtonText = 'Kaydet';
            
            // Update movement types for this modal
            this.updateFireSayimEksigiTypes();
            
            this.fireSayimEksigiModalVisible = true;
        },
        closeFireSayimEksigiModal() {
            this.fireSayimEksigiModalVisible = false;
        },
        resetFireSayimEksigiForm() {
            this.fireSayimEksigiForm = {
                hareket_id: '',
                stok_turu: '',
                kod: '',
                yon: 'cikis', // Otomatik olarak çıkış
                hareket_turu: '', // fire veya sayim_eksigi
                miktar: '',
                ilgili_belge_no: '',
                aciklama: '',
                depo: '',
                raf: '',
                tank_kodu: ''
            };
            this.fireSayimEksigiStockItems = [];
        },
        loadFireSayimEksigiStockItems() {
            if (!this.fireSayimEksigiForm.stok_turu) {
                this.fireSayimEksigiStockItems = [];
                // Clear location fields when no stock type is selected
                this.fireSayimEksigiForm.kod = '';
                this.fireSayimEksigiForm.depo = '';
                this.fireSayimEksigiForm.raf = '';
                this.fireSayimEksigiForm.tank_kodu = '';
                return;
            }
            
            // API call to load stock items based on type for fire/sayım eksigi
            axios.get(`api_islemleri/stok_hareket_islemler.php?action=get_stock_items&type=${this.fireSayimEksigiForm.stok_turu}`)
                .then(response => {
                    if (response.data.status === 'success') {
                        this.fireSayimEksigiStockItems = response.data.data || [];
                        // Clear the selected item if it's not in the new list
                        if (this.fireSayimEksigiForm.kod && !this.fireSayimEksigiStockItems.some(item => item.kod === this.fireSayimEksigiForm.kod)) {
                            this.fireSayimEksigiForm.kod = '';
                            this.fireSayimEksigiForm.depo = '';
                            this.fireSayimEksigiForm.raf = '';
                            this.fireSayimEksigiForm.tank_kodu = '';
                        }
                    } else {
                        this.showAlert(response.data.message || 'Stok ürünleri yüklenirken bir hata oluştu', 'danger');
                    }
                })
                .catch(error => {
                    this.showAlert('Stok ürünleri yüklenirken bir hata oluştu', 'danger');
                });
        },
        getFireSayimEksigiLocation() {
            if (!this.fireSayimEksigiForm.stok_turu || !this.fireSayimEksigiForm.kod) {
                // Clear location fields when no item is selected
                this.fireSayimEksigiForm.depo = '';
                this.fireSayimEksigiForm.raf = '';
                this.fireSayimEksigiForm.tank_kodu = '';
                return;
            }
            
            // API call to get current location for fire/sayım eksigi
            axios.get(`api_islemleri/stok_hareket_islemler.php?action=get_current_location&stock_type=${this.fireSayimEksigiForm.stok_turu}&item_code=${this.fireSayimEksigiForm.kod}`)
                .then(response => {
                    if (response.data.status === 'success') {
                        const location = response.data.data;
                        this.fireSayimEksigiForm.depo = location.depo || '';
                        this.fireSayimEksigiForm.raf = location.raf || '';
                        this.fireSayimEksigiForm.tank_kodu = location.tank_kodu || '';
                    } else {
                        this.showAlert(response.data.message || 'Lokasyon bilgisi alınırken bir hata oluştu', 'warning');
                    }
                })
                .catch(error => {
                    this.showAlert('Lokasyon bilgisi alınırken bir hata oluştu', 'danger');
                });
        },
        updateFireSayimEksigiTypes() {
            // For the fire/sayım eksigi modal, only allow fire and sayim_eksigi
            this.fireSayimEksigiMovementTypes = [
                { value: 'fire', label: 'Fire' },
                { value: 'sayim_eksigi', label: 'Sayım Eksigi' }
            ];
            
            // If a movement type is not selected, default to 'fire'
            if (!this.fireSayimEksigiForm.hareket_turu) {
                this.fireSayimEksigiForm.hareket_turu = 'fire';
            }
        },
        saveFireSayimEksigi() {
            this.isSubmitting = true;
            
            // Prepare the form data
            const formData = new FormData();
            Object.keys(this.fireSayimEksigiForm).forEach(key => {
                if (this.fireSayimEksigiForm[key] !== null) {
                    formData.append(key, this.fireSayimEksigiForm[key]);
                }
            });
            
            // Determine the action based on whether we're editing
            formData.append('action', this.isFireSayimEksigiEdit ? 'update_movement' : 'add_movement');
            
            axios.post('api_islemleri/stok_hareket_islemler.php', formData)
                .then(response => {
                    if (response.data.status === 'success') {
                        this.showAlert(response.data.message, 'success');
                        this.closeFireSayimEksigiModal();
                        this.loadMovements(); // Reload movements
                    } else {
                        this.showAlert(response.data.message || 'İşlem sırasında bir hata oluştu', 'danger');
                    }
                })
                .catch(error => {
                    this.showAlert('İşlem sırasında bir hata oluştu', 'danger');
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
                yon: 'giris', // Otomatik olarak giriş
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
                        this.showAlert(response.data.message || 'Stok ürünleri yüklenirken bir hata oluştu', 'danger');
                    }
                })
                .catch(error => {
                    this.showAlert('Stok ürünleri yüklenirken bir hata oluştu', 'danger');
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
                        this.showAlert(response.data.message || 'Tedarikçiler yüklenirken bir hata oluştu', 'danger');
                    }
                })
                .catch(error => {
                    this.showAlert('Tedarikçiler yüklenirken bir hata oluştu', 'danger');
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
                        this.showAlert(response.data.message || 'İşlem sırasında bir hata oluştu', 'danger');
                    }
                })
                .catch(error => {
                    this.showAlert('İşlem sırasında bir hata oluştu', 'danger');
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
                title: icon === 'success' ? 'Başarılı!' : 'Bilgi',
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


