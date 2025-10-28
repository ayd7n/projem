// tanklar.js dosyası - Vue uygulaması
document.addEventListener('DOMContentLoaded', function() {
    new Vue({
        el: '#app',
        data: {
            user_name: window.session_kullanici_adi || 'Kullanıcı',
            allTanks: [], // Store all tanks once loaded
            filtered_tanks: [], // Filtered tanks based on search term
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
            submitButtonText: 'Kaydet',
            // Pagination and search properties
            search: '',
            currentPage: 1,
            totalPages: 1,
            limit: 10,
            isLoading: true // Track loading state
        },
        computed: {
            tankModalTitle() {
                return this.isEdit ? 'Tankı Düzenle' : 'Yeni Tank Ekle';
            },
            // Get paginated tanks based on current page and limit
            paginatedTanks() {
                const start = (this.currentPage - 1) * this.limit;
                const end = start + this.limit;
                return this.filtered_tanks.slice(start, end);
            },
            paginationInfo() {
                if (this.totalPages <= 0 || this.filtered_tanks.length <= 0) {
                    return 'Gösterilecek kayıt yok';
                }
                const startRecord = (this.currentPage - 1) * this.limit + 1;
                const endRecord = Math.min(this.currentPage * this.limit, this.filtered_tanks.length);
                return `${startRecord}-${endRecord} arası gösteriliyor, toplam ${this.filtered_tanks.length} kayıttan`;
            },
            pageNumbers() {
                const pages = [];
                const startPage = Math.max(1, this.currentPage - 2);
                const endPage = Math.min(this.totalPages, this.currentPage + 2);

                for (let i = startPage; i <= endPage; i++) {
                    pages.push(i);
                }
                return pages;
            }
        },
        mounted() {
            this.loadTanks();
            this.loadTotalTanks();
        },
        methods: {
            // Load all tanks initially (for client-side filtering)
            loadTanks() {
                this.isLoading = true;
                const url = 'api_islemleri/tanklar_islemler.php?action=get_tanks'; // Load all tanks at once
                axios.get(url)
                    .then(response => {
                        if (response.data.status === 'success') {
                            this.allTanks = response.data.data || [];
                            this.total_tanks = this.allTanks.length; // Set total from actual data
                            this.applyFilters(); // Apply initial filters
                        } else {
                            this.showAlert(response.data.message || 'Tanklar yüklenirken bir hata oluştu. Hata Kodu: T001', 'danger');
                        }
                    })
                    .catch(error => {
                        this.showAlert('Tanklar yüklenirken bir hata oluştu. Lütfen daha sonra tekrar deneyin. Hata Kodu: T002', 'danger');
                    })
                    .finally(() => {
                        this.isLoading = false;
                    });
            },
            loadTotalTanks() {
                // No need to call API since we have the total from allTanks
                // The total will be updated when tanks are loaded
            },
            // Apply filters (search) and update pagination
            applyFilters() {
                // Filter tanks based on search term
                let filtered = this.allTanks;
                
                if (this.search && this.search.trim() !== '') {
                    const searchTerm = this.search.toLowerCase().trim();
                    filtered = this.allTanks.filter(tank => 
                        tank.tank_kodu.toLowerCase().includes(searchTerm) ||
                        tank.tank_ismi.toLowerCase().includes(searchTerm) ||
                        tank.kapasite.toString().includes(searchTerm) ||
                        (tank.not_bilgisi && tank.not_bilgisi.toLowerCase().includes(searchTerm))
                    );
                }
                
                this.filtered_tanks = filtered;
                
                // Reset to first page when filters change
                this.currentPage = 1;
                
                // Update total pages based on filtered results
                this.totalPages = Math.ceil(this.filtered_tanks.length / this.limit);
                
                // Ensure current page is valid
                if (this.currentPage > this.totalPages && this.totalPages > 0) {
                    this.currentPage = this.totalPages;
                } else if (this.totalPages === 0) {
                    this.currentPage = 1;
                }
            },
            
            // Handle search input changes
            handleSearchInput() {
                this.applyFilters();
            },
            
            // Handle limit change (items per page)
            handleLimitChange() {
                // Update total pages based on new limit
                this.totalPages = Math.ceil(this.filtered_tanks.length / this.limit);
                
                // Ensure current page is valid with new limit
                if (this.currentPage > this.totalPages && this.totalPages > 0) {
                    this.currentPage = this.totalPages;
                } else if (this.totalPages === 0) {
                    this.currentPage = 1;
                }
                
                // Apply filters to update pagination
                this.applyFilters();
            },
            
            // Change to a specific page
            changePage(page) {
                // Validate page number
                if (page >= 1 && page <= this.totalPages) {
                    this.currentPage = page;
                }
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
                            // Reload all tanks to update client-side data
                            this.loadTanks();
                        } else {
                            this.showAlert(response.data.message || 'Tank işlemi sırasında bir hata oluştu. Lütfen bilgileri kontrol edin. Hata Kodu: T005', 'danger');
                        }
                    })
                    .catch(error => {
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
                Swal.fire({
                    title: 'Emin misiniz?',
                    text: 'Bu tankı silmek istediğinizden emin misiniz?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Evet',
                    cancelButtonText: 'İptal'
                }).then((result) => {
                    if (!result.isConfirmed) {
                        return;
                    }

                    const formData = new FormData();
                    formData.append('action', 'delete_tank');
                    formData.append('tank_id', id);

                    axios.post('api_islemleri/tanklar_islemler.php', formData)
                        .then(response => {
                            if (response.data.status === 'success') {
                                this.showAlert(response.data.message, 'success');
                                // Reload all tanks to update client-side data
                                this.loadTanks();
                            } else {
                                this.showAlert(response.data.message || 'Tank silme işlemi sırasında bir hata oluştu. Hata Kodu: T011', 'danger');
                            }
                        })
                        .catch(error => {
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
            }
        }
    });
});
