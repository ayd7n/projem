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
            hedef_raf: ''
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
        stockItems: [],
        transferStockItems: [],
        sayimFazlasiStockItems: [],
        locations: [],
        movementTypes: [],
        submitButtonText: 'Kaydet'
    },
    computed: {
        movementFormTitle() {
            return this.isEdit ? 'Stok Hareketini Düzenle' : 'Yeni Stok Hareketi';
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
        loadStockItems() {
            if (!this.movementForm.stok_turu) {
                this.stockItems = [];
                return;
            }
            
            // API call to load stock items based on type
            axios.get(`api_islemleri/stok_hareket_islemler.php?action=get_stock_items&type=${this.movementForm.stok_turu}`)
                .then(response => {
                    if (response.data.status === 'success') {
                        this.stockItems = response.data.data || [];
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
                return;
            }
            
            // API call to load transfer stock items based on type
            axios.get(`api_islemleri/stok_hareket_islemler.php?action=get_stock_items&type=${this.transferForm.stok_turu}`)
                .then(response => {
                    if (response.data.status === 'success') {
                        this.transferStockItems = response.data.data || [];
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
                return;
            }
            
            // API call to load stock items based on type for sayım fazlası
            axios.get(`api_islemleri/stok_hareket_islemler.php?action=get_stock_items&type=${this.sayimFazlasiForm.stok_turu}`)
                .then(response => {
                    if (response.data.status === 'success') {
                        this.sayimFazlasiStockItems = response.data.data || [];
                    } else {
                        this.showAlert(response.data.message || 'Stok ürünleri yüklenirken bir hata oluştu', 'danger');
                    }
                })
                .catch(error => {
                    this.showAlert('Stok ürünleri yüklenirken bir hata oluştu', 'danger');
                });
        },
        getStockLocation() {
            if (!this.movementForm.stok_turu || !this.movementForm.kod) return;
            
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
            if (!this.transferForm.stok_turu || !this.transferForm.kod) return;
            
            // API call to get current location for transfer
            this.isTransferAutoFill = true;
            axios.get(`api_islemleri/stok_hareket_islemler.php?action=get_current_location&stock_type=${this.transferForm.stok_turu}&item_code=${this.transferForm.kod}`)
                .then(response => {
                    if (response.data.status === 'success') {
                        const location = response.data.data;
                        this.transferForm.kaynak_depo = location.depo || '';
                        this.transferForm.kaynak_raf = location.raf || '';
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
            if (!this.sayimFazlasiForm.stok_turu || !this.sayimFazlasiForm.kod) return;
            
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
                    { value: 'mal_kabul', label: 'Mal Kabul' },
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
            } else {
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
            if (!confirm('Bu stok hareketini silmek istediğinizden emin misiniz?')) {
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
        },
        openTransferModal() {
            this.resetTransferForm();
            this.transferModalVisible = true;
            this.loadTransferStockItems(); // Load transfer stock items
        },
        saveTransfer() {
            this.isSubmitting = true;
            
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
                hedef_raf: ''
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
        showAlert(message, type) {
            this.alert.message = message;
            this.alert.type = type;
            
            // Auto-hide success alerts after 5 seconds
            if (type === 'success') {
                setTimeout(() => {
                    this.clearAlert();
                }, 5000);
            }
        },
        clearAlert() {
            this.alert.message = '';
            this.alert.type = 'success';
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