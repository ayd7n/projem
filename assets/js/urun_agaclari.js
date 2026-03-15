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
            bilesenin_malzeme_turu: 'malzeme',
            bilesen_miktari: 0
        },
        modalTitle: 'Yeni Urun Agaci Ekle',
        submitButtonText: 'Ekle',
        showModal: false,
        showEssenceModal: false,
        alertMessage: '',
        alertType: 'success',
        activeTab: 'product',
        kullaniciAdi: window.kullaniciBilgisi ? window.kullaniciBilgisi.kullaniciAdi : 'Kullanici',
        productTreeSearchTerm: '',
        essenceTreeSearchTerm: '',
        productTreesCurrentPage: 1,
        productTreesPerPage: 10,
        productTreesTotal: 0,
        productTreesTotalPages: 0,
        essenceTreesCurrentPage: 1,
        essenceTreesPerPage: 10,
        essenceTreesTotal: 0,
        essenceTreesTotalPages: 0,
        coverageQuickOptions: [1, 2, 3, 4, 6, 8, 10, 12, 24],
        productRatioWizard: {
            inputMode: 'coverage',
            coverageCount: '',
            directCount: '',
            advancedInput: '',
            calculatedAmount: null,
            error: '',
            isValid: false,
            showAdvanced: false,
            approximateCoverage: false
        },
        essenceRatioWizard: {
            inputMode: 'coverage',
            coverageCount: '',
            directCount: '',
            advancedInput: '',
            calculatedAmount: null,
            error: '',
            isValid: false,
            showAdvanced: false,
            approximateCoverage: false
        }
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

        getWizardState(treeType) {
            return treeType === 'product' ? this.productRatioWizard : this.essenceRatioWizard;
        },
        getSelectedTree(treeType) {
            return treeType === 'product' ? this.selectedProductTree : this.selectedEssenceTree;
        },
        formatFourDecimal(value) {
            if (value === null || value === undefined || Number.isNaN(Number(value))) {
                return '0.0000';
            }
            return this.roundTo4(value).toFixed(4);
        },
        roundTo4(value) {
            return Math.round((Number(value) + Number.EPSILON) * 10000) / 10000;
        },
        parsePositiveInteger(value) {
            const raw = String(value ?? '').trim();
            if (raw === '' || !/^\d+$/.test(raw)) {
                return null;
            }
            const parsed = Number(raw);
            if (!Number.isInteger(parsed) || parsed <= 0) {
                return null;
            }
            return parsed;
        },
        computeFromCoverage(adet) {
            const parsedCoverage = this.parsePositiveInteger(adet);
            if (!parsedCoverage) {
                return null;
            }
            return this.roundTo4(1 / parsedCoverage);
        },
        parseAdvancedInput(value) {
            const input = String(value ?? '').trim();
            if (input === '') {
                return null;
            }

            const normalized = input.replace(',', '.');
            let parsedValue = null;

            if (normalized.includes('/')) {
                const parts = normalized.split('/');
                if (parts.length !== 2) {
                    return null;
                }

                const pay = Number(parts[0].trim());
                const payda = Number(parts[1].trim());
                if (!Number.isFinite(pay) || !Number.isFinite(payda) || pay <= 0 || payda <= 0) {
                    return null;
                }
                parsedValue = pay / payda;
            } else {
                parsedValue = Number(normalized);
            }

            if (!Number.isFinite(parsedValue) || parsedValue <= 0) {
                return null;
            }

            return this.roundTo4(parsedValue);
        },
        applyWizardResult(treeType, amount, options = {}) {
            const wizard = this.getWizardState(treeType);
            const selectedTree = this.getSelectedTree(treeType);

            wizard.calculatedAmount = amount;
            wizard.isValid = amount !== null;
            wizard.error = amount === null ? (options.error || "Lütfen geçerli bir değer girin. Örn: 1 (1'e 1) veya 6 (1/6).") : '';
            wizard.approximateCoverage = !!options.approximateCoverage;

            if (amount !== null) {
                selectedTree.bilesen_miktari = amount;
            } else {
                selectedTree.bilesen_miktari = 0;
            }
        },
        recalcRatioFromCoverage(treeType) {
            const wizard = this.getWizardState(treeType);
            const amount = this.computeFromCoverage(wizard.coverageCount);

            if (amount === null) {
                this.applyWizardResult(treeType, null, { error: "Lütfen geçerli bir sayı girin. Örn: 1, 2, 6" });
                return;
            }

            wizard.advancedInput = this.formatFourDecimal(amount);
            this.applyWizardResult(treeType, amount);
        },
        recalcDirect(treeType) {
            const wizard = this.getWizardState(treeType);
            const raw = String(wizard.directCount ?? '').trim().replace(',', '.');
            const parsed = Number(raw);

            if (!raw || !Number.isFinite(parsed) || parsed <= 0) {
                this.applyWizardResult(treeType, null, { error: "Lütfen geçerli bir sayı girin. Örn: 1, 2, 3" });
                return;
            }

            const rounded = this.roundTo4(parsed);
            wizard.advancedInput = this.formatFourDecimal(rounded);
            this.applyWizardResult(treeType, rounded);
        },
        recalcRatioFromAdvanced(treeType) {
            const wizard = this.getWizardState(treeType);
            const amount = this.parseAdvancedInput(wizard.advancedInput);

            if (amount === null) {
                this.applyWizardResult(treeType, null, { error: "Örn: 1/6, 0,25 veya 0.25. 1'e 1 için 1 yazabilirsiniz." });
                return;
            }

            wizard.coverageCount = '';
            wizard.approximateCoverage = false;
            this.applyWizardResult(treeType, amount);
        },
        setCoverageQuick(treeType, coverage) {
            const wizard = this.getWizardState(treeType);
            wizard.coverageCount = coverage;
            this.recalcRatioFromCoverage(treeType);
        },
        toggleAdvancedRatio(treeType) {
            const wizard = this.getWizardState(treeType);
            wizard.showAdvanced = !wizard.showAdvanced;
        },
        resetRatioWizard(treeType) {
            const wizard = this.getWizardState(treeType);
            wizard.inputMode = 'coverage';
            wizard.coverageCount = '';
            wizard.directCount = '';
            wizard.advancedInput = '';
            wizard.calculatedAmount = null;
            wizard.error = '';
            wizard.isValid = false;
            wizard.showAdvanced = false;
            wizard.approximateCoverage = false;
        },
        initializeRatioWizardFromAmount(treeType, amount) {
            const wizard = this.getWizardState(treeType);
            this.resetRatioWizard(treeType);

            const numericAmount = Number(amount);
            if (!Number.isFinite(numericAmount) || numericAmount <= 0) {
                return;
            }

            const roundedAmount = this.roundTo4(numericAmount);

            if (roundedAmount >= 1 && Number.isInteger(roundedAmount)) {
                wizard.inputMode = 'direct';
                wizard.directCount = roundedAmount;
            } else {
                wizard.inputMode = 'coverage';
                const approximatedCoverage = this.parsePositiveInteger(Math.round(1 / roundedAmount));
                let isApproximate = false;

                if (approximatedCoverage) {
                    wizard.coverageCount = approximatedCoverage;
                    const reconstructed = this.roundTo4(1 / approximatedCoverage);
                    isApproximate = Math.abs(reconstructed - roundedAmount) > 0.0001;
                }
                wizard.approximateCoverage = isApproximate;
            }

            wizard.advancedInput = this.formatFourDecimal(roundedAmount);
            this.applyWizardResult(treeType, roundedAmount, { approximateCoverage: wizard.approximateCoverage });
        },
        getCoverageValueForPayload(treeType) {
            const wizard = this.getWizardState(treeType);
            return this.parsePositiveInteger(wizard.coverageCount);
        },
        prepareRatioForSave(treeType) {
            const wizard = this.getWizardState(treeType);
            const selectedTree = this.getSelectedTree(treeType);

            if (!wizard.isValid || wizard.calculatedAmount === null) {
                wizard.error = wizard.error || "Bileşen miktarı geçersiz. Örn: 1 (1'e 1) veya 6 (1/6).";
                return false;
            }

            selectedTree.bilesen_miktari = this.roundTo4(wizard.calculatedAmount);
            return true;
        },
        getCoverageDisplay(treeType) {
            const wizard = this.getWizardState(treeType);
            return wizard.coverageCount ? wizard.coverageCount : '?';
        },
        getComponentUnitLabel(treeType) {
            const defaultUnit = 'birim';

            if (treeType === 'product') {
                const componentCode = this.selectedProductTree.bilesen_kodu;
                if (!componentCode) {
                    return defaultUnit;
                }

                const componentType = String(this.selectedProductTree.bilesenin_malzeme_turu || '').toLowerCase();
                if (componentType === 'esans') {
                    const essence = this.essences.find(e => String(e.esans_kodu) === String(componentCode));
                    return (essence && essence.birim && String(essence.birim).trim()) || defaultUnit;
                }

                const material = this.materials.find(m => String(m.malzeme_kodu) === String(componentCode));
                return (material && material.birim && String(material.birim).trim()) || defaultUnit;
            }

            const essenceComponentCode = this.selectedEssenceTree.bilesen_kodu;
            if (!essenceComponentCode) {
                return defaultUnit;
            }

            const essenceMaterial = this.materials.find(m => String(m.malzeme_kodu) === String(essenceComponentCode));
            return (essenceMaterial && essenceMaterial.birim && String(essenceMaterial.birim).trim()) || defaultUnit;
        },
        isProductFormReady() {
            return !!(
                this.selectedProductTree.urun_kodu &&
                this.selectedProductTree.bilesen_kodu &&
                this.productRatioWizard.isValid
            );
        },
        isEssenceFormReady() {
            return !!(
                this.selectedEssenceTree.urun_kodu &&
                this.selectedEssenceTree.bilesen_kodu &&
                this.essenceRatioWizard.isValid
            );
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
                this.showAlert('Urunler alinirken bir hata olustu.', 'danger');
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
                this.showAlert('Malzemeler alinirken bir hata olustu.', 'danger');
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
                this.showAlert('Esanslar alinirken bir hata olustu.', 'danger');
            }
        },

        async saveProductTree() {
            try {
                if (!this.prepareRatioForSave('product')) {
                    this.showAlert(this.productRatioWizard.error || 'Bilesen miktari gecersiz.', 'danger');
                    return;
                }

                const productTreeData = {
                    action: this.selectedProductTree.urun_agaci_id ? 'update_product_tree' : 'add_product_tree',
                    ...this.selectedProductTree,
                    agac_turu: 'urun',
                    bilesen_miktari: this.roundTo4(this.selectedProductTree.bilesen_miktari),
                    kapsadigi_urun_adedi: this.getCoverageValueForPayload('product')
                };

                const response = await axios.post('api_islemleri/urun_agaclari_islemler.php', productTreeData);

                if (response.data.status === 'success') {
                    this.showAlert(response.data.message, 'success');
                    await this.searchProductTreesPaginated(this.productTreesCurrentPage);
                    this.closeModal();
                } else {
                    this.showAlert(response.data.message, 'danger');
                }
            } catch (error) {
                this.showAlert('Urun agaci kaydedilirken hata olustu: ' + (error.response?.data?.message || error.message), 'danger');
            }
        },
        async saveEssenceTree() {
            try {
                if (!this.prepareRatioForSave('essence')) {
                    this.showAlert(this.essenceRatioWizard.error || 'Bilesen miktari gecersiz.', 'danger');
                    return;
                }

                const essence = this.essences.find(e => e.esans_kodu === this.selectedEssenceTree.urun_kodu);
                let essenceId = this.selectedEssenceTree.urun_kodu;

                if (essence && essence.esans_id) {
                    essenceId = essence.esans_id;
                }

                const essenceTreeData = {
                    action: this.selectedEssenceTree.urun_agaci_id ? 'update_product_tree' : 'add_product_tree',
                    ...this.selectedEssenceTree,
                    urun_kodu: essenceId,
                    agac_turu: 'esans',
                    bilesenin_malzeme_turu: 'malzeme',
                    bilesen_miktari: this.roundTo4(this.selectedEssenceTree.bilesen_miktari),
                    kapsadigi_urun_adedi: this.getCoverageValueForPayload('essence')
                };

                const response = await axios.post('api_islemleri/urun_agaclari_islemler.php', essenceTreeData);

                if (response.data.status === 'success') {
                    this.showAlert(response.data.message, 'success');
                    await this.searchEssenceTreesPaginated(this.essenceTreesCurrentPage);
                    this.closeEssenceModal();
                } else {
                    this.showAlert(response.data.message, 'danger');
                }
            } catch (error) {
                this.showAlert('Esans agaci kaydedilirken hata olustu: ' + (error.response?.data?.message || error.message), 'danger');
            }
        },

        async deleteProductTree(id) {
            Swal.fire({
                title: 'Emin misiniz?',
                text: 'Bu urun agacini silmek istediginizden emin misiniz?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Evet',
                cancelButtonText: 'Iptal'
            }).then(async (result) => {
                if (result.isConfirmed) {
                    try {
                        const response = await axios.post('api_islemleri/urun_agaclari_islemler.php', {
                            action: 'delete_product_tree',
                            urun_agaci_id: id
                        });

                        if (response.data.status === 'success') {
                            this.showAlert(response.data.message, 'success');
                            await this.searchProductTreesPaginated(this.productTreesCurrentPage);
                        } else {
                            this.showAlert(response.data.message, 'danger');
                        }
                    } catch (error) {
                        this.showAlert('Silme islemi sirasinda bir hata olustu: ' + (error.response?.data?.message || error.message), 'danger');
                    }
                }
            });
        },
        async deleteEssenceTree(id) {
            Swal.fire({
                title: 'Emin misiniz?',
                text: 'Bu esans agacini silmek istediginizden emin misiniz?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Evet',
                cancelButtonText: 'Iptal'
            }).then(async (result) => {
                if (result.isConfirmed) {
                    try {
                        const response = await axios.post('api_islemleri/urun_agaclari_islemler.php', {
                            action: 'delete_product_tree',
                            urun_agaci_id: id
                        });

                        if (response.data.status === 'success') {
                            this.showAlert(response.data.message, 'success');
                            await this.searchEssenceTreesPaginated(this.essenceTreesCurrentPage);
                        } else {
                            this.showAlert(response.data.message, 'danger');
                        }
                    } catch (error) {
                        this.showAlert('Silme islemi sirasinda bir hata olustu: ' + (error.response?.data?.message || error.message), 'danger');
                    }
                }
            });
        },

        openAddModal() {
            this.modalTitle = 'Yeni Urun Agaci Ekle';
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
            this.resetRatioWizard('product');
            this.showModal = true;
        },
        openEditModal(id) {
            const productTree = this.productTrees.find(item => item.urun_agaci_id == id);
            if (!productTree) {
                return;
            }

            this.selectedProductTree = {
                ...productTree,
                bilesen_miktari: this.roundTo4(productTree.bilesen_miktari || 0)
            };
            this.initializeRatioWizardFromAmount('product', this.selectedProductTree.bilesen_miktari);
            this.modalTitle = 'Urun Agaci Duzenle';
            this.submitButtonText = 'Guncelle';
            this.showModal = true;
        },
        openEssenceAddModal() {
            this.modalTitle = 'Yeni Esans Agaci Ekle';
            this.submitButtonText = 'Ekle';
            this.selectedEssenceTree = {
                urun_agaci_id: null,
                urun_kodu: '',
                urun_ismi: '',
                bilesen_kodu: '',
                bilesen_ismi: '',
                bilesenin_malzeme_turu: 'malzeme',
                bilesen_miktari: 0
            };
            this.resetRatioWizard('essence');
            this.showEssenceModal = true;
        },
        openEssenceEditModal(id) {
            const essenceTree = this.essenceTrees.find(item => item.urun_agaci_id == id);
            if (!essenceTree) {
                return;
            }

            const essence = this.essences.find(e => e.esans_id == essenceTree.urun_kodu);
            const essenceCode = essence ? essence.esans_kodu : essenceTree.urun_kodu;

            this.selectedEssenceTree = {
                ...essenceTree,
                urun_kodu: essenceCode,
                bilesen_miktari: this.roundTo4(essenceTree.bilesen_miktari || 0)
            };

            this.initializeRatioWizardFromAmount('essence', this.selectedEssenceTree.bilesen_miktari);
            this.modalTitle = 'Esans Agaci Duzenle';
            this.submitButtonText = 'Guncelle';
            this.showEssenceModal = true;
        },

        closeModal() {
            this.showModal = false;
            this.resetRatioWizard('product');
        },
        closeEssenceModal() {
            this.showEssenceModal = false;
            this.resetRatioWizard('essence');
            this.resetSelectedEssenceTree();
        },
        resetSelectedEssenceTree() {
            this.selectedEssenceTree = {
                urun_agaci_id: null,
                urun_kodu: '',
                urun_ismi: '',
                bilesen_kodu: '',
                bilesen_ismi: '',
                bilesenin_malzeme_turu: 'malzeme',
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
        closeAlert() {
            this.alertMessage = '';
        },

        updateProductName() {
            const product = this.products.find(p => p.urun_kodu === this.selectedProductTree.urun_kodu);
            this.selectedProductTree.urun_ismi = product ? product.urun_ismi : '';
        },
        updateBilesenInfo() {
            let bilesen = this.essences.find(e => e.esans_kodu === this.selectedProductTree.bilesen_kodu);
            if (bilesen) {
                this.selectedProductTree.bilesen_ismi = bilesen.esans_ismi;
                this.selectedProductTree.bilesenin_malzeme_turu = 'esans';
                return;
            }

            bilesen = this.materials.find(m => m.malzeme_kodu === this.selectedProductTree.bilesen_kodu);
            if (bilesen) {
                this.selectedProductTree.bilesen_ismi = bilesen.malzeme_ismi;
                this.selectedProductTree.bilesenin_malzeme_turu = bilesen.malzeme_turu;
            }
        },
        updateEssenceName() {
            const essence = this.essences.find(e => e.esans_kodu === this.selectedEssenceTree.urun_kodu);
            if (essence) {
                this.selectedEssenceTree.urun_ismi = essence.esans_ismi;
            }
        },
        updateEssenceBilesenInfo() {
            const material = this.materials.find(m => m.malzeme_kodu == this.selectedEssenceTree.bilesen_kodu);
            if (material) {
                this.selectedEssenceTree.bilesen_ismi = material.malzeme_ismi;
            }
        },

        switchTab(tabName) {
            this.activeTab = tabName;
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
                this.showAlert('Urun agaclari alinirken bir hata olustu: ' + (error.response?.data?.message || error.message), 'danger');
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
                this.showAlert('Esans agaclari alinirken bir hata olustu: ' + (error.response?.data?.message || error.message), 'danger');
            }
        },

        async searchProductTreesPaginated(page = 1) {
            if (this.productTreeSearchTerm.trim() === '') {
                await this.fetchProductTreesPaginated(page);
                return;
            }

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
                this.showAlert('Arama sirasinda bir hata olustu: ' + (error.response?.data?.message || error.message), 'danger');
            }
        },
        async searchEssenceTreesPaginated(page = 1) {
            if (this.essenceTreeSearchTerm.trim() === '') {
                await this.fetchEssenceTreesPaginated(page);
                return;
            }

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
                this.showAlert('Arama sirasinda bir hata olustu: ' + (error.response?.data?.message || error.message), 'danger');
            }
        },

        async searchProductTrees() {
            this.productTreesCurrentPage = 1;
            await this.searchProductTreesPaginated(1);
        },
        async searchEssenceTrees() {
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
            this.productTreesCurrentPage = 1;
            if (this.productTreeSearchTerm.trim() === '') {
                this.fetchProductTreesPaginated(1);
            } else {
                this.searchProductTreesPaginated(1);
            }
        },
        changeEssenceTreesPerPage() {
            this.essenceTreesCurrentPage = 1;
            if (this.essenceTreeSearchTerm.trim() === '') {
                this.fetchEssenceTreesPaginated(1);
            } else {
                this.searchEssenceTreesPaginated(1);
            }
        }
    }
});
