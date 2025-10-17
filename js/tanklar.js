// tanklar.js dosyası - Vue uygulaması
document.addEventListener('DOMContentLoaded', function() {
    new Vue({
        el: '#app',
        data: {
            user_name: window.session_kullanici_adi || 'Kullanıcı',
            tanks: [],
            total_tanks: 0,
            alert: {
                message: '',
                type: 'success' // 'success', 'danger', 'warning', 'info'
            },
            tankModalVisible: false,
            isSubmitting: false,
            isEdit: false,
            tankForm: {
                tank_id: '',
                tank_kodu: '',
                tank_ismi: '',
                kapasite: '',
                not_bilgisi: ''
            },
            submitButtonText: 'Kaydet'
        },
        computed: {
            tankModalTitle() {
                return this.isEdit ? 'Tankı Düzenle' : 'Yeni Tank Ekle';
            }
        },
        mounted() {
            this.loadTanks();
            this.loadTotalTanks();
        },
        methods: {
            loadTanks() {
                axios.get('api_islemleri/tanklar_islemler.php?action=get_tanks')
                    .then(response => {
                        if (response.data.status === 'success') {
                            this.tanks = response.data.data || [];
                        } else {
                            this.showAlert(response.data.message || 'Tanklar yüklenirken bir hata oluştu. Hata Kodu: T001', 'danger');
                        }
                    })
                    .catch(error => {
                        console.error('Tank listesi yüklenirken hata oluştu:', error);
                        this.showAlert('Tanklar yüklenirken bir hata oluştu. Lütfen daha sonra tekrar deneyin. Hata Kodu: T002', 'danger');
                    });
            },
            loadTotalTanks() {
                axios.get('api_islemleri/tanklar_islemler.php?action=get_total_tanks')
                    .then(response => {
                        if (response.data.status === 'success') {
                            this.total_tanks = response.data.data || 0;
                        } else {
                            this.showAlert(response.data.message || 'Toplam tank sayısı alınırken bir hata oluştu. Hata Kodu: T003', 'danger');
                        }
                    })
                    .catch(error => {
                        console.error('Toplam tank sayısı alınırken hata oluştu:', error);
                        this.showAlert('Toplam tank sayısı alınırken bir hata oluştu. Lütfen daha sonra tekrar deneyin. Hata Kodu: T004', 'danger');
                    });
            },
            openTankModal() {
                this.resetTankForm();
                this.isEdit = false;
                this.submitButtonText = 'Ekle';
                this.tankModalVisible = true;
            },
            editTank(tank) {
                this.tankForm = { ...tank };
                this.isEdit = true;
                this.submitButtonText = 'Güncelle';
                this.tankModalVisible = true;
            },
            saveTank() {
                this.isSubmitting = true;
                
                // Prepare the form data
                const formData = new FormData();
                Object.keys(this.tankForm).forEach(key => {
                    if (this.tankForm[key] !== null) {
                        formData.append(key, this.tankForm[key]);
                    }
                });
                
                // Determine the action based on whether we're editing
                formData.append('action', this.isEdit ? 'update_tank' : 'add_tank');
                
                axios.post('api_islemleri/tanklar_islemler.php', formData)
                    .then(response => {
                        if (response.data.status === 'success') {
                            this.showAlert(response.data.message, 'success');
                            this.closeTankModal();
                            this.loadTanks(); // Reload tanks
                            this.loadTotalTanks(); // Reload total count
                        } else {
                            this.showAlert(response.data.message || 'Tank işlemi sırasında bir hata oluştu. Lütfen bilgileri kontrol edin. Hata Kodu: T005', 'danger');
                        }
                    })
                    .catch(error => {
                        console.error('Tank işlemi sırasında hata oluştu:', error);
                        // Hata detaylarını kontrol et
                        if (error.response) {
                            // Sunucudan hata yanıtı geldi
                            if (error.response.status === 403) {
                                this.showAlert('Bu işlemi yapma yetkiniz yok. Lütfen uygulamadan çıkış yapıp tekrar giriş yapın. Hata Kodu: T006', 'danger');
                            } else if (error.response.status === 401) {
                                this.showAlert('Oturumunuz sona ermiş olabilir. Lütfen sayfayı yenileyin veya tekrar giriş yapın. Hata Kodu: T007', 'danger');
                            } else {
                                this.showAlert(`Tank işlemi sırasında bir hata oluştu. Hata: ${error.response.data?.message || error.response.statusText || 'Bilinmeyen hata'}. Hata Kodu: T008`, 'danger');
                            }
                        } else if (error.request) {
                            // İstek yapıldı ancak yanıt alınamadı
                            this.showAlert('Sunucuya bağlanılamadı. Lütfen internet bağlantınızı kontrol edin veya daha sonra tekrar deneyin. Hata Kodu: T009', 'danger');
                        } else {
                            // İstek yapılmadan önce bir hata oluştu
                            this.showAlert('İstek hazırlanırken bir hata oluştu. Lütfen daha sonra tekrar deneyin. Hata Kodu: T010', 'danger');
                        }
                    })
                    .finally(() => {
                        this.isSubmitting = false;
                    });
            },
            deleteTank(id) {
                if (!confirm('Bu tankı silmek istediğinizden emin misiniz?')) {
                    return;
                }
                
                const formData = new FormData();
                formData.append('action', 'delete_tank');
                formData.append('tank_id', id);
                
                axios.post('api_islemleri/tanklar_islemler.php', formData)
                    .then(response => {
                        if (response.data.status === 'success') {
                            this.showAlert(response.data.message, 'success');
                            this.loadTanks(); // Reload tanks
                            this.loadTotalTanks(); // Reload total count
                        } else {
                            this.showAlert(response.data.message || 'Tank silme işlemi sırasında bir hata oluştu. Hata Kodu: T011', 'danger');
                        }
                    })
                    .catch(error => {
                        console.error('Tank silme işlemi sırasında hata oluştu:', error);
                        if (error.response) {
                            if (error.response.status === 403) {
                                this.showAlert('Bu işlemi yapma yetkiniz yok. Lütfen uygulamadan çıkış yapıp tekrar giriş yapın. Hata Kodu: T012', 'danger');
                            } else if (error.response.status === 401) {
                                this.showAlert('Oturumunuz sona ermiş olabilir. Lütfen sayfayı yenileyin veya tekrar giriş yapın. Hata Kodu: T013', 'danger');
                            } else {
                                this.showAlert(`Tank silme işlemi sırasında bir hata oluştu. Hata: ${error.response.data?.message || error.response.statusText || 'Bilinmeyen hata'}. Hata Kodu: T014`, 'danger');
                            }
                        } else if (error.request) {
                            this.showAlert('Sunucuya bağlanılamadı. Lütfen internet bağlantınızı kontrol edin veya daha sonra tekrar deneyin. Hata Kodu: T015', 'danger');
                        } else {
                            this.showAlert('İstek hazırlanırken bir hata oluştu. Lütfen daha sonra tekrar deneyin. Hata Kodu: T016', 'danger');
                        }
                    });
            },
            closeTankModal() {
                this.tankModalVisible = false;
            },
            resetTankForm() {
                this.tankForm = {
                    tank_id: '',
                    tank_kodu: '',
                    tank_ismi: '',
                    kapasite: '',
                    not_bilgisi: ''
                };
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
            }
        }
    });
});