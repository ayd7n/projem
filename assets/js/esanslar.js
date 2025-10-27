document.addEventListener('DOMContentLoaded', function() {
    new Vue({
        el: '#app',
        data: {
            esansListesi: [],
            tanklarListesi: [],
            seciliEsans: {
                esans_id: null,
                esans_kodu: '',
                esans_ismi: '',
                tank_kodu: '',
                tank_ismi: '',
                stok_miktari: 0,
                birim: 'lt',
                demlenme_suresi_gun: 0,
                not_bilgisi: ''
            },
            modalAcik: false,
            modalModu: 'ekle', // 'ekle' veya 'duzenle'
            alertMessage: '',
            alertType: 'success', // 'success' veya 'danger'
            kullaniciAdi: window.kullaniciBilgisi ? window.kullaniciBilgisi.kullaniciAdi : 'Kullanıcı',
            // Pagination and search properties
            search: '',
            currentPage: 1,
            totalPages: 1,
            totalEssences: 0,
            limit: 10,
            loading: false
        },
        computed: {
            toplamEsans() {
                return this.totalEssences;
            },
            modalBaslik() {
                return this.modalModu === 'ekle' ? 'Yeni Esans Ekle' : 'Esansı Düzenle';
            },
            submitButonMetni() {
                return this.modalModu === 'ekle' ? 'Ekle' : 'Güncelle';
            },
            paginationInfo() {
                if (this.totalPages <= 0 || this.totalEssences <= 0) {
                    return 'Gösterilecek kayıt yok';
                }
                const startRecord = (this.currentPage - 1) * this.limit + 1;
                const endRecord = Math.min(this.currentPage * this.limit, this.totalEssences);
                return `${startRecord}-${endRecord} arası gösteriliyor, toplam ${this.totalEssences} kayıttan`;
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
            this.tanklariYukle();
            this.esanslariYukle();
        },
        methods: {
            tanklariYukle() {
                const self = this;
                axios.get('api_islemleri/tanklar_islemler.php?action=get_tanks')
                    .then(function(response) {
                        if(response.data.status === 'success') {
                            self.tanklarListesi = response.data.data || [];
                        } else {
                            self.gosterUyari(response.data.message, 'danger');
                        }
                    })
                    .catch(function(error) {
                        self.gosterUyari('Tanklar yüklenirken bir hata oluştu.', 'danger');
                    });
            },
            
            esanslariYukle(page = 1) {
                const self = this;
                self.loading = true;
                this.currentPage = page;
                const url = `api_islemleri/get_essences_ajax.php?page=${this.currentPage}&limit=${this.limit}&search=${encodeURIComponent(this.search)}`;
                
                axios.get(url)
                    .then(function(response) {
                        if(response.data.status === 'success') {
                            self.esansListesi = response.data.data || [];
                            self.totalPages = response.data.pagination.total_pages;
                            self.totalEssences = response.data.pagination.total_essences;
                        } else {
                            self.gosterUyari(response.data.message, 'danger');
                        }
                        self.loading = false;
                    })
                    .catch(function(error) {
                        self.gosterUyari('Esanslar yüklenirken bir hata oluştu.', 'danger');
                        self.loading = false;
                    });
            },
            
            tankSecildi() {
                const selectedTank = this.tanklarListesi.find(tank => tank.tank_kodu === this.seciliEsans.tank_kodu);
                if (selectedTank) {
                    this.seciliEsans.tank_ismi = selectedTank.tank_ismi;
                } else {
                    this.seciliEsans.tank_ismi = '';
                }
            },
            
            acYeniEsansModal() {
                this.modalModu = 'ekle';
                this.sifirlaSeciliEsans();
                this.seciliEsans.birim = 'lt';  // Yeni esans için özellikle litre yap
                this.modalAcik = true;
            },
            
            acDuzenleModal(esans) {
                this.modalModu = 'duzenle';
                this.seciliEsans = { ...esans };
                this.modalAcik = true;
            },
            
            sifirlaSeciliEsans() {
                this.seciliEsans = {
                    esans_id: null,
                    esans_kodu: '',
                    esans_ismi: '',
                    tank_kodu: '',
                    tank_ismi: '',
                    stok_miktari: 0,
                    birim: 'lt',
                    demlenme_suresi_gun: 0,
                    not_bilgisi: ''
                };
            },
            
            kaydetEsans() {
                const formData = {
                    action: this.modalModu === 'ekle' ? 'add_essence' : 'update_essence',
                    ...this.seciliEsans
                };

                const self = this;
                axios.post('api_islemleri/esanslar_islemler.php', formData, {
                    headers: {
                        'Content-Type': 'application/json'
                    }
                })
                    .then(function(response) {
                        if(response.data.status === 'success') {
                            self.gosterUyari(response.data.message, 'success');
                            self.kapatModal();
                            self.esanslariYukle(self.currentPage); // Listeyi yeniden yükle, aynı sayfada kal
                        } else {
                            self.gosterUyari(response.data.message, 'danger');
                        }
                    })
                    .catch(function(error) {
                        self.gosterUyari('Esans kaydedilirken bir hata oluştu.', 'danger');
                    });
            },
            
            silEsans(esansId) {
                Swal.fire({
                    title: 'Emin misiniz?',
                    text: 'Bu esansı silmek istediğinizden emin misiniz?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Evet',
                    cancelButtonText: 'İptal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const self = this;
                        axios.post('api_islemleri/esanslar_islemler.php', {
                            action: 'delete_essence',
                            esans_id: esansId
                        })
                        .then(function(response) {
                            if(response.data.status === 'success') {
                                self.gosterUyari(response.data.message, 'success');
                                // After deletion, check if current page has no records and adjust page if needed
                                if(self.esansListesi.length === 1 && self.currentPage > 1) {
                                    self.esanslariYukle(self.currentPage - 1);
                                } else {
                                    self.esanslariYukle(self.currentPage); // Reload current page
                                }
                            } else {
                                self.gosterUyari(response.data.message, 'danger');
                            }
                        })
                        .catch(function(error) {
                            self.gosterUyari('Esans silinirken bir hata oluştu.', 'danger');
                        });
                    }
                });
            },
            
            kapatModal() {
                this.modalAcik = false;
                this.sifirlaSeciliEsans();
            },
            
            gosterUyari(mesaj, tip) {
                this.alertMessage = mesaj;
                this.alertType = tip;
                
                // Otomatik kapatma
                setTimeout(() => {
                    this.alertMessage = '';
                }, 5000);
            },
            
            closeAlert() {
                this.alertMessage = '';
            },
            
            performSearch() {
                // Reset to first page when searching
                // Using setTimeout to debounce the search
                if(this.searchTimeout) {
                    clearTimeout(this.searchTimeout);
                }
                this.searchTimeout = setTimeout(() => {
                    this.esanslariYukle(1);
                }, 500);
            }
        }
    });
});