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
                birim: 'ml',
                demlenme_suresi_gun: 0,
                not_bilgisi: ''
            },
            modalAcik: false,
            modalModu: 'ekle', // 'ekle' veya 'duzenle'
            alertMessage: '',
            alertType: 'success', // 'success' veya 'danger'
            kullaniciAdi: window.kullaniciBilgisi ? window.kullaniciBilgisi.kullaniciAdi : 'Kullanıcı'
        },
        computed: {
            toplamEsans() {
                return this.esansListesi.length;
            },
            modalBaslik() {
                return this.modalModu === 'ekle' ? 'Yeni Esans Ekle' : 'Esansı Düzenle';
            },
            submitButonMetni() {
                return this.modalModu === 'ekle' ? 'Ekle' : 'Güncelle';
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
                        console.error('Tanklar yüklenirken hata oluştu:', error);
                        self.gosterUyari('Tanklar yüklenirken bir hata oluştu.', 'danger');
                    });
            },
            
            esanslariYukle() {
                const self = this;
                axios.get('api_islemleri/esanslar_islemler.php?action=get_all_essences')
                    .then(function(response) {
                        if(response.data.status === 'success') {
                            self.esansListesi = response.data.data || [];
                        } else {
                            self.gosterUyari(response.data.message, 'danger');
                        }
                    })
                    .catch(function(error) {
                        console.error('Esanslar yüklenirken hata oluştu:', error);
                        self.gosterUyari('Esanslar yüklenirken bir hata oluştu.', 'danger');
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
                    stok_miktari: 0,
                    birim: 'ml',
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
                            self.esanslariYukle(); // Listeyi yeniden yükle
                        } else {
                            self.gosterUyari(response.data.message, 'danger');
                        }
                    })
                    .catch(function(error) {
                        console.error('Esans kaydedilirken hata oluştu:', error);
                        self.gosterUyari('Esans kaydedilirken bir hata oluştu.', 'danger');
                    });
            },
            
            silEsans(esansId) {
                if(confirm('Bu esansı silmek istediğinizden emin misiniz?')) {
                    const self = this;
                    axios.post('api_islemleri/esanslar_islemler.php', {
                        action: 'delete_essence',
                        esans_id: esansId
                    })
                    .then(function(response) {
                        if(response.data.status === 'success') {
                            self.gosterUyari(response.data.message, 'success');
                            self.esanslariYukle(); // Listeyi yeniden yükle
                        } else {
                            self.gosterUyari(response.data.message, 'danger');
                        }
                    })
                    .catch(function(error) {
                        console.error('Esans silinirken hata oluştu:', error);
                        self.gosterUyari('Esans silinirken bir hata oluştu.', 'danger');
                    });
                }
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
            }
        }
    });
});