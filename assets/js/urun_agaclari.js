// Define the Vue app for product and essence trees
new Vue({
    el: '#app',
    data: {
        productTrees: [],
        products: [],
        materials: [],
        essences: [],
        totalProductTrees: 0,
        essenceTrees: [],
        totalEssenceTrees: 0,
        selectedProductTree: {
            urun_agaci_id: null,
            urun_kodu: '',
            urun_ismi: '',
            bilesen_kodu: '',
            bilesen_ismi: '',
            bilesenin_malzeme_turu: '',
            bilesen_miktari: 0
        },
        selectedEssenceTree: {
            urun_agaci_id: null,
            urun_kodu: '',
            urun_ismi: '',
            bilesen_kodu: '',
            bilesen_ismi: '',
            bilesenin_malzeme_turu: 'urun',
            bilesen_miktari: 0
        },
        modalTitle: 'Yeni Ürün Ağacı Ekle',
        submitButtonText: 'Ekle',
        showModal: false,
        showEssenceModal: false,
        alertMessage: '',
        alertType: 'success',
        activeTab: 'product', // Default to product trees tab
        kullaniciAdi: window.kullaniciBilgisi ? window.kullaniciBilgisi.kullaniciAdi : 'Kullanıcı',
        productTreeSearchTerm: '', // For product tree search
        essenceTreeSearchTerm: '', // For essence tree search
        // Pagination for product trees
        productTreesCurrentPage: 1,
        productTreesPerPage: 10,
        productTreesTotal: 0,
        productTreesTotalPages: 0,
        // Pagination for essence trees
        essenceTreesCurrentPage: 1,
        essenceTreesPerPage: 10,
        essenceTreesTotal: 0,
        essenceTreesTotalPages: 0
    },
    mounted() {
        this.fetchAllData();
    },
    methods: {
        async fetchAllData() {
            await Promise.all([
                this.fetchProductTreesPaginated(1),
                this.fetchEssenceTreesPaginated(1),
                this.fetchProducts(),
                this.fetchMaterials(),
                this.fetchEssences()
            ]);
        },
        updateTotalProductTrees() {
            const uniqueProducts = new Set(this.productTrees.map(item => item.urun_kodu));
            this.totalProductTrees = uniqueProducts.size;
        },
        updateTotalEssenceTrees() {
            const uniqueEssences = new Set(this.essenceTrees.map(item => item.urun_kodu));
            this.totalEssenceTrees = uniqueEssences.size;
        },
        updateTotalProductTrees() {
            const uniqueProducts = new Set(this.productTrees.map(item => item.urun_kodu));
            this.totalProductTrees = uniqueProducts.size;
        },
        updateTotalEssenceTrees() {
            const uniqueEssences = new Set(this.essenceTrees.map(item => item.urun_kodu));
            this.totalEssenceTrees = uniqueEssences.size;
        },
        async fetchProducts() {
            try {
                const response = await axios.get('api_islemleri/urun_agaclari_islemler.php?action=get_products');
                if (response.data.status === 'success') {
                    this.products = response.data.data || [];
                } else {
                    this.showAlert(response.data.message, 'danger');
                }
            } catch (error) {
                this.showAlert('Ürünler alınırken bir hata oluştu.', 'danger');
            }
        },
        async fetchMaterials() {
            try {
                const response = await axios.get('api_islemleri/urun_agaclari_islemler.php?action=get_materials');
                if (response.data.status === 'success') {
                    this.materials = response.data.data || [];
                } else {
                    this.showAlert(response.data.message, 'danger');
                }
            } catch (error) {
                this.showAlert('Malzemeler alınırken bir hata oluştu.', 'danger');
            }
        },
        async fetchEssences() {
            try {
                const response = await axios.get('api_islemleri/urun_agaclari_islemler.php?action=get_essences');
                if (response.data.status === 'success') {
                    this.essences = response.data.data || [];
                } else {
                    this.showAlert(response.data.message, 'danger');
                }
            } catch (error) {
                this.showAlert('Esanslar alınırken bir hata oluştu.', 'danger');
            }
        },
        async saveProductTree() {
            try {
                const productTreeData = {
                    action: this.selectedProductTree.urun_agaci_id ? 'update_product_tree' : 'add_product_tree',
                    ...this.selectedProductTree,
                    agac_turu: 'urun'
                };
                
                let response;
                if (this.selectedProductTree.urun_agaci_id) {
                    response = await axios.post('api_islemleri/urun_agaclari_islemler.php', productTreeData);
                } else {
                    response = await axios.post('api_islemleri/urun_agaclari_islemler.php', productTreeData);
                }
                
                if (response.data.status === 'success') {
                    this.showAlert(response.data.message, 'success');
                    // Refresh current page after save
                    await this.searchProductTreesPaginated(this.productTreesCurrentPage);
                    this.closeModal();
                } else {
                    this.showAlert(response.data.message, 'danger');
                }
            } catch (error) {
                this.showAlert('Ürün ağacı kaydedilirken hata oluştu: ' + (error.response?.data?.message || error.message), 'danger');
            }
        },
        async saveEssenceTree() {
            try {
                // Convert essence code to essence ID for database storage
                const essence = this.essences.find(e => e.esans_kodu === this.selectedEssenceTree.urun_kodu);
                let essenceId = this.selectedEssenceTree.urun_kodu; // fallback to original value
                
                if (essence && essence.esans_id) {
                    essenceId = essence.esans_id;
                }
                
                const essenceTreeData = {
                    action: this.selectedEssenceTree.urun_agaci_id ? 'update_product_tree' : 'add_product_tree',
                    ...this.selectedEssenceTree,
                    urun_kodu: essenceId, // Use the essence ID for storage
                    agac_turu: 'esans',
                    bilesenin_malzeme_turu: 'malzeme'  // Esans ağacında bileşen türü her zaman malzeme olur
                };
                
                let response;
                if (this.selectedEssenceTree.urun_agaci_id) {
                    response = await axios.post('api_islemleri/urun_agaclari_islemler.php', essenceTreeData);
                } else {
                    response = await axios.post('api_islemleri/urun_agaclari_islemler.php', essenceTreeData);
                }
                
                if (response.data.status === 'success') {
                    this.showAlert(response.data.message, 'success');
                    // Refresh current page after save
                    await this.searchEssenceTreesPaginated(this.essenceTreesCurrentPage);
                    this.closeEssenceModal();
                } else {
                    this.showAlert(response.data.message, 'danger');
                }
            } catch (error) {
                this.showAlert('Esans ağacı kaydedilirken hata oluştu: ' + (error.response?.data?.message || error.message), 'danger');
            }
        },
        async deleteProductTree(id) {
            Swal.fire({
                title: 'Emin misiniz?',
                text: 'Bu ürün ağacını silmek istediğinizden emin misiniz?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Evet',
                cancelButtonText: 'İptal'
            }).then(async (result) => {
                if (result.isConfirmed) {
                    try {
                        const response = await axios.post('api_islemleri/urun_agaclari_islemler.php', {
                            action: 'delete_product_tree',
                            urun_agaci_id: id
                        });

                        if (response.data.status === 'success') {
                            this.showAlert(response.data.message, 'success');
                            // Refresh current page after delete
                            await this.searchProductTreesPaginated(this.productTreesCurrentPage);
                        } else {
                            this.showAlert(response.data.message, 'danger');
                        }
                    } catch (error) {
                        this.showAlert('Silme işlemi sırasında bir hata oluştu: ' + (error.response?.data?.message || error.message), 'danger');
                    }
                }
            });
        },
        async deleteEssenceTree(id) {
            Swal.fire({
                title: 'Emin misiniz?',
                text: 'Bu esans ağacını silmek istediğinizden emin misiniz?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Evet',
                cancelButtonText: 'İptal'
            }).then(async (result) => {
                if (result.isConfirmed) {
                    try {
                        const response = await axios.post('api_islemleri/urun_agaclari_islemler.php', {
                            action: 'delete_product_tree',
                            urun_agaci_id: id
                        });

                        if (response.data.status === 'success') {
                            this.showAlert(response.data.message, 'success');
                            // Refresh current page after delete
                            await this.searchEssenceTreesPaginated(this.essenceTreesCurrentPage);
                        } else {
                            this.showAlert(response.data.message, 'danger');
                        }
                    } catch (error) {
                        this.showAlert('Silme işlemi sırasında bir hata oluştu: ' + (error.response?.data?.message || error.message), 'danger');
                    }
                }
            });
        },
        openAddModal() {
            this.modalTitle = 'Yeni Ürün Ağacı Ekle';
            this.submitButtonText = 'Ekle';
            this.selectedProductTree = {
                urun_agaci_id: null,
                urun_kodu: '',
                urun_ismi: '',
                bilesen_kodu: '',
                bilesen_ismi: '',
                bilesenin_malzeme_turu: '',
                bilesen_miktari: 0
            };
            this.showModal = true;
        },
        openEditModal(id) {
            const productTree = this.productTrees.find(item => item.urun_agaci_id == id);
            this.selectedProductTree = {...productTree};
            this.modalTitle = 'Ürün Ağacı Düzenle';
            this.submitButtonText = 'Güncelle';
            this.showModal = true;
        },
        openEssenceAddModal() {
            this.modalTitle = 'Yeni Esans Ağacı Ekle';
            this.submitButtonText = 'Ekle';
            this.selectedEssenceTree = {
                urun_agaci_id: null,
                urun_kodu: '',
                urun_ismi: '',
                bilesen_kodu: '',
                bilesen_ismi: '',
                bilesenin_malzeme_turu: 'malzeme',  // Esans trees can only have materials as components
                bilesen_miktari: 0
            };
            this.showEssenceModal = true;
        },
        openEssenceEditModal(id) {
            const essenceTree = this.essenceTrees.find(item => item.urun_agaci_id == id);
            if (essenceTree) {
                // Need to convert essence ID back to essence code for dropdown selection
                const essence = this.essences.find(e => e.esans_id == essenceTree.urun_kodu);
                const essenceCode = essence ? essence.esans_kodu : essenceTree.urun_kodu; // fallback to ID if no match found
                
                this.selectedEssenceTree = {
                    ...essenceTree,
                    urun_kodu: essenceCode  // Use essence code for dropdown selection
                };
                
                this.modalTitle = 'Esans Ağacı Düzenle';
                this.submitButtonText = 'Güncelle';
                this.showEssenceModal = true;
            }
        },

        closeModal() {
            this.showModal = false;
        },
        closeEssenceModal() {
            this.showEssenceModal = false;
            this.resetSelectedEssenceTree();
        },
        resetSelectedEssenceTree() {
            this.selectedEssenceTree = {
                urun_agaci_id: null,
                urun_kodu: '',
                urun_ismi: '',
                bilesen_kodu: '',
                bilesen_ismi: '',
                bilesenin_malzeme_turu: 'urun',
                bilesen_miktari: 0
            };
        },
        showAlert(message, type) {
            this.alertMessage = message;
            this.alertType = type;
            setTimeout(() => {
                this.alertMessage = '';
            }, 5000);
        },
        updateProductName() {
            const product = this.products.find(p => p.urun_kodu === this.selectedProductTree.urun_kodu);
            this.selectedProductTree.urun_ismi = product ? product.urun_ismi : '';
        },
        updateBilesenInfo() {
            // Check if it's an essence
            let bilesen = this.essences.find(e => e.esans_kodu === this.selectedProductTree.bilesen_kodu);
            if (bilesen) {
                this.selectedProductTree.bilesen_ismi = bilesen.esans_ismi;
                this.selectedProductTree.bilesenin_malzeme_turu = 'esans';
                return;
            }
            // Check if it's a material
            bilesen = this.materials.find(m => m.malzeme_kodu === this.selectedProductTree.bilesen_kodu);
            if (bilesen) {
                this.selectedProductTree.bilesen_ismi = bilesen.malzeme_ismi;
                this.selectedProductTree.bilesenin_malzeme_turu = bilesen.malzeme_turu;
                return;
            }
        },
        async addProductTree() {
            try {
                const productTreeData = {
                    action: 'add_product_tree',
                    ...this.selectedProductTree,
                    agac_turu: 'urun'
                };
                
                const response = await axios.post('api_islemleri/urun_agaclari_islemler.php', productTreeData);
                
                if (response.data.status === 'success') {
                    this.showAlert(response.data.message, 'success');
                    await this.fetchProductTrees();
                    this.closeModal();
                } else {
                    this.showAlert(response.data.message, 'danger');
                }
            } catch (error) {
                this.showAlert('Ürün ağacı eklenirken hata oluştu: ' + (error.response?.data?.message || error.message), 'danger');
            }
        },
        async updateProductTree() {
            try {
                const productTreeData = {
                    action: 'update_product_tree',
                    ...this.selectedProductTree,
                    agac_turu: 'urun'
                };
                
                const response = await axios.post('api_islemleri/urun_agaclari_islemler.php', productTreeData);
                
                if (response.data.status === 'success') {
                    this.showAlert(response.data.message, 'success');
                    await this.fetchProductTrees();
                    this.closeModal();
                } else {
                    this.showAlert(response.data.message, 'danger');
                }
            } catch (error) {
                this.showAlert('Ürün ağacı güncellenirken hata oluştu: ' + (error.response?.data?.message || error.message), 'danger');
            }
        },
        updateEssenceName() {
            const essence = this.essences.find(e => e.esans_kodu === this.selectedEssenceTree.urun_kodu);
            if (essence) {
                this.selectedEssenceTree.urun_ismi = essence.esans_ismi;
            }
        },
        updateEssenceBilesenInfo() {
            // Esans ağacında sadece malzeme seçilebilir
            const material = this.materials.find(m => m.malzeme_kodu == this.selectedEssenceTree.bilesen_kodu);
            if (material) {
                this.selectedEssenceTree.bilesen_ismi = material.malzeme_ismi;
                // Component type will be set to 'malzeme' in saveEssenceTree method
            }
        },
        updateTotalProductTrees() {
            const uniqueProducts = new Set(this.productTrees.map(item => item.urun_kodu));
            this.totalProductTrees = uniqueProducts.size;
        },
        updateTotalEssenceTrees() {
            const uniqueEssences = new Set(this.essenceTrees.map(item => item.urun_kodu));
            this.totalEssenceTrees = uniqueEssences.size;
        },
        closeAlert() {
            this.alertMessage = '';
        },
        switchTab(tabName) {
            this.activeTab = tabName;
            // Bootstrap's tab switching
            $('#myTab a[href="#' + tabName + '"]').tab('show');
        },
        async fetchProductTreesPaginated(page = 1) {
            try {
                const response = await axios.get('api_islemleri/urun_agaclari_islemler.php', {
                    params: {
                        action: 'get_product_trees_paginated',
                        page: page,
                        limit: this.productTreesPerPage
                    }
                });
                
                if (response.data.status === 'success') {
                    this.productTrees = response.data.data || [];
                    if (response.data.pagination) {
                        this.productTreesCurrentPage = response.data.pagination.current_page;
                        this.productTreesTotal = response.data.pagination.total;
                        this.productTreesTotalPages = response.data.pagination.total_pages;
                    }
                } else {
                    this.showAlert(response.data.message, 'danger');
                }
            } catch (error) {
                this.showAlert('Ürün ağaçları alınırken bir hata oluştu: ' + (error.response?.data?.message || error.message), 'danger');
            }
        },
        async fetchEssenceTreesPaginated(page = 1) {
            try {
                const response = await axios.get('api_islemleri/urun_agaclari_islemler.php', {
                    params: {
                        action: 'get_essence_trees_paginated',
                        page: page,
                        limit: this.essenceTreesPerPage
                    }
                });
                
                if (response.data.status === 'success') {
                    this.essenceTrees = response.data.data || [];
                    if (response.data.pagination) {
                        this.essenceTreesCurrentPage = response.data.pagination.current_page;
                        this.essenceTreesTotal = response.data.pagination.total;
                        this.essenceTreesTotalPages = response.data.pagination.total_pages;
                    }
                } else {
                    this.showAlert(response.data.message, 'danger');
                }
            } catch (error) {
                this.showAlert('Esans ağaçları alınırken bir hata oluştu: ' + (error.response?.data?.message || error.message), 'danger');
            }
        },
        async searchProductTreesPaginated(page = 1) {
            if (this.productTreeSearchTerm.trim() === '') {
                // If search term is empty, fetch paginated data
                await this.fetchProductTreesPaginated(page);
            } else {
                // Perform search with pagination
                try {
                    const response = await axios.get('api_islemleri/urun_agaclari_islemler.php', {
                        params: {
                            action: 'search_product_trees_paginated',
                            searchTerm: this.productTreeSearchTerm,
                            page: page,
                            limit: this.productTreesPerPage
                        }
                    });
                    
                    if (response.data.status === 'success') {
                        this.productTrees = response.data.data || [];
                        if (response.data.pagination) {
                            this.productTreesCurrentPage = response.data.pagination.current_page;
                            this.productTreesTotal = response.data.pagination.total;
                            this.productTreesTotalPages = response.data.pagination.total_pages;
                        }
                    } else {
                        this.showAlert(response.data.message, 'danger');
                    }
                } catch (error) {
                    this.showAlert('Arama sırasında bir hata oluştu: ' + (error.response?.data?.message || error.message), 'danger');
                }
            }
        },
        async searchEssenceTreesPaginated(page = 1) {
            if (this.essenceTreeSearchTerm.trim() === '') {
                // If search term is empty, fetch paginated data
                await this.fetchEssenceTreesPaginated(page);
            } else {
                // Perform search with pagination
                try {
                    const response = await axios.get('api_islemleri/urun_agaclari_islemler.php', {
                        params: {
                            action: 'search_essence_trees_paginated',
                            searchTerm: this.essenceTreeSearchTerm,
                            page: page,
                            limit: this.essenceTreesPerPage
                        }
                    });
                    
                    if (response.data.status === 'success') {
                        this.essenceTrees = response.data.data || [];
                        if (response.data.pagination) {
                            this.essenceTreesCurrentPage = response.data.pagination.current_page;
                            this.essenceTreesTotal = response.data.pagination.total;
                            this.essenceTreesTotalPages = response.data.pagination.total_pages;
                        }
                    } else {
                        this.showAlert(response.data.message, 'danger');
                    }
                } catch (error) {
                    this.showAlert('Arama sırasında bir hata oluştu: ' + (error.response?.data?.message || error.message), 'danger');
                }
            }
        },
        async searchProductTrees() {
            // Reset to first page when performing a new search
            this.productTreesCurrentPage = 1;
            await this.searchProductTreesPaginated(1);
        },
        async searchEssenceTrees() {
            // Reset to first page when performing a new search
            this.essenceTreesCurrentPage = 1;
            await this.searchEssenceTreesPaginated(1);
        },
        changeProductTreesPage(page) {
            if (page >= 1 && page <= this.productTreesTotalPages) {
                this.productTreesCurrentPage = page;
                if (this.productTreeSearchTerm.trim() === '') {
                    this.fetchProductTreesPaginated(page);
                } else {
                    this.searchProductTreesPaginated(page);
                }
            }
        },
        changeEssenceTreesPage(page) {
            if (page >= 1 && page <= this.essenceTreesTotalPages) {
                this.essenceTreesCurrentPage = page;
                if (this.essenceTreeSearchTerm.trim() === '') {
                    this.fetchEssenceTreesPaginated(page);
                } else {
                    this.searchEssenceTreesPaginated(page);
                }
            }
        },
        changeProductTreesPerPage() {
            // Reset to first page when changing items per page
            this.productTreesCurrentPage = 1;
            if (this.productTreeSearchTerm.trim() === '') {
                this.fetchProductTreesPaginated(1);
            } else {
                this.searchProductTreesPaginated(1);
            }
        },
        changeEssenceTreesPerPage() {
            // Reset to first page when changing items per page
            this.essenceTreesCurrentPage = 1;
            if (this.essenceTreeSearchTerm.trim() === '') {
                this.fetchEssenceTreesPaginated(1);
            } else {
                this.searchEssenceTreesPaginated(1);
            }
        }
    }
});
