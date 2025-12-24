const app = Vue.createApp({
  data() {
    return {
      productData: null,
      loading: true,
      error: null,
      urunKodu: window.urunKodu,
      frameContracts: [], // Store available frame contracts
      contractsLoaded: false, // Track if contracts have been loaded
      kurlar: { dolar: 1, euro: 1 }, // Exchange rates
      // No image viewer needed since we're using Lightbox2
    };
  },
  computed: {
    activeOrders() {
      if (
        !this.productData ||
        !this.productData.orders ||
        !this.productData.orders.data
      ) {
        return [];
      }
      // Filter out orders with status "iptal_edildi"
      return this.productData.orders.data.filter(
        (order) => order.siparis_durum !== "iptal_edildi"
      );
    },
    recentMovements() {
      if (
        !this.productData ||
        !this.productData.movements ||
        !this.productData.movements.data
      ) {
        return [];
      }
      // Return only the last 10 movements
      return this.productData.movements.data.slice(-10);
    },
    producibleQuantity() {
      if (
        !this.productData ||
        !this.productData.bom_components ||
        this.productData.bom_components.length === 0
      ) {
        return 0;
      }

      // Calculate maximum producible quantity based on available components
      let maxProducible = Infinity;

      for (const component of this.productData.bom_components) {
        const requiredAmount = parseFloat(component.bilesen_miktari) || 0;
        const availableAmount = parseFloat(component.bilesen_stok) || 0;

        if (requiredAmount > 0) {
          const producibleFromThisComponent = Math.floor(
            availableAmount / requiredAmount
          );
          maxProducible = Math.min(maxProducible, producibleFromThisComponent);
        }
      }

      return maxProducible === Infinity ? 0 : maxProducible;
    },
    stockGap() {
      if (!this.productData) return null;

      const currentStock =
        parseFloat(this.productData.product.stok_miktari) || 0;
      const criticalStock =
        parseFloat(this.productData.product.kritik_stok_seviyesi) || 0;
      const gap = criticalStock - currentStock;

      if (gap <= 0) {
        return {
          hasGap: false,
          gap: 0,
          gapPercentage: 0,
          producibleForGap: 0,
          canCoverGap: true,
          gapDetails: [],
        };
      }

      // Calculate if we can produce enough to cover the gap
      const producibleForGap = Math.min(this.producibleQuantity, gap);
      const canCoverGap = producibleForGap >= gap;

      // Calculate what's needed to produce the missing amount
      let gapDetails = [];
      if (!canCoverGap && this.productData.bom_components) {
        const missingToProduce = gap - producibleForGap;

        for (const component of this.productData.bom_components) {
          const requiredAmount = parseFloat(component.bilesen_miktari) || 0;
          const neededForGap = missingToProduce * requiredAmount;
          const availableAmount = parseFloat(component.bilesen_stok) || 0;
          const shortfall = Math.max(0, neededForGap - availableAmount);

          if (shortfall > 0) {
            gapDetails.push({
              name: component.bilesen_ismi,
              code: component.bilesen_kodu,
              type: component.bilesen_turu,
              needed: neededForGap,
              available: availableAmount,
              shortfall: shortfall,
              unit: component.bilesen_birim,
            });
          }
        }
      }

      return {
        hasGap: true,
        gap: gap,
        gapPercentage:
          currentStock > 0 ? Math.round((gap / currentStock) * 100) : 0,
        producibleForGap: producibleForGap,
        canCoverGap: canCoverGap,
        gapDetails: gapDetails,
      };
    },
  },
  mounted() {
    this.loadProductCard();
    this.loadKurlar();
    // Remove v-cloak attribute after Vue has mounted to show the content
    this.$nextTick(() => {
      const appElement = document.getElementById("app");
      if (appElement && appElement.hasAttribute("v-cloak")) {
        appElement.removeAttribute("v-cloak");
      }
    });
  },
  methods: {
    loadProductCard() {
      this.loading = true;
      this.error = null;

      // Create a timeout promise to handle potential network delays
      const timeoutPromise = new Promise((_, reject) => {
        setTimeout(() => {
          reject(
            new Error(
              "İstek zaman aşımına uğradı. Lütfen daha sonra tekrar deneyin."
            )
          );
        }, 10000); // 10 second timeout
      });

      Promise.race([
        fetch(
          `api_islemleri/urun_karti_islemler.php?action=get_product_card&urun_kodu=${this.urunKodu}`
        ),
        timeoutPromise,
      ])
        .then((response) => {
          if (response.status === 404) {
            throw new Error(
              "API endpoint bulunamadı. Lütfen geliştirici ile iletişime geçin."
            );
          }
          return response.json();
        })
        .then((response) => {
          if (response.status === "success") {
            this.productData = response.data;
            // Load frame contracts after product data is loaded
            this.loadFrameContracts();
          } else {
            this.error =
              response.message || "Ürün bilgileri yüklenirken hata oluştu.";
          }
          this.loading = false;
        })
        .catch((error) => {
          if (error.message.includes("zaman aşımı")) {
            this.error = error.message;
          } else if (error.message.includes("endpoint bulunamadı")) {
            this.error = error.message;
          } else {
            this.error = "Bir ağ hatası oluştu: " + error.message;
          }
          this.loading = false;
        });
    },
    formatCurrency(value, currency = "TRY") {
      if (value === null || value === undefined) return "0,00 ₺";
      const num = parseFloat(value);
      const currencySymbols = { TRY: "₺", USD: "$", EUR: "€" };
      const symbol = currencySymbols[currency] || "₺";
      return (
        num.toLocaleString("tr-TR", {
          minimumFractionDigits: 2,
          maximumFractionDigits: 2,
        }) +
        " " +
        symbol
      );
    },
    formatPriceWithCurrency(product) {
      const price = parseFloat(product.satis_fiyati) || 0;
      const currency = product.satis_fiyati_para_birimi || "TRY";
      return this.formatCurrency(price, currency);
    },
    formatTeorikMaliyet(product) {
      const teorikMaliyet = parseFloat(product.teorik_maliyet) || 0;
      const currency = product.satis_fiyati_para_birimi || "TRY";
      let convertedCost = teorikMaliyet;
      if (currency === "USD" && this.kurlar.dolar > 0) {
        convertedCost = teorikMaliyet / this.kurlar.dolar;
      } else if (currency === "EUR" && this.kurlar.euro > 0) {
        convertedCost = teorikMaliyet / this.kurlar.euro;
      }
      return this.formatCurrency(convertedCost, currency);
    },
    loadKurlar() {
      fetch("api_islemleri/ayarlar_islemler.php?action=get_settings")
        .then((response) => response.json())
        .then((response) => {
          if (response.status === "success") {
            this.kurlar.dolar = parseFloat(response.data.dolar_kuru) || 1;
            this.kurlar.euro = parseFloat(response.data.euro_kuru) || 1;
          }
        });
    },
    formatNumber(value) {
      if (value === null || value === undefined) return "0";
      const num = parseFloat(value);
      if (Number.isInteger(num)) {
        return num.toString();
      }
      return num.toFixed(2);
    },
    formatDate(dateString) {
      if (!dateString) return "-";
      const date = new Date(dateString);
      return date.toLocaleDateString("tr-TR", {
        year: "numeric",
        month: "2-digit",
        day: "2-digit",
        hour: "2-digit",
        minute: "2-digit",
      });
    },
    getStatusBadgeClass(status) {
      const statusMap = {
        onaylandi: "badge badge-success",
        hazirlaniyor: "badge badge-warning",
        tamamlandi: "badge badge-info",
        iptal: "badge badge-danger",
      };
      return statusMap[status] || "badge badge-secondary";
    },
    calculateActiveOrdersTotal() {
      if (!this.activeOrders || this.activeOrders.length === 0) {
        return 0;
      }
      return this.activeOrders.reduce((total, order) => {
        return total + (parseFloat(order.adet) || 0);
      }, 0);
    },
    canProduceEnough(component) {
      if (!component || !this.productData) return false;
      const requiredAmount = parseFloat(component.bilesen_miktari) || 0;
      const availableAmount = parseFloat(component.bilesen_stok) || 0;
      const producibleFromThisComponent =
        requiredAmount > 0 ? Math.floor(availableAmount / requiredAmount) : 0;
      return producibleFromThisComponent >= this.producibleQuantity;
    },
    loadFrameContracts() {
      // Load valid frame contracts directly
      fetch(
        "api_islemleri/cerceve_sozlesmeler_islemler.php?action=get_valid_contracts"
      )
        .then((response) => {
          if (!response.ok) {
            throw new Error("HTTP error " + response.status);
          }
          return response.json();
        })
        .then((data) => {
          if (data && data.status === "success") {
            // Use the contracts directly as they are already valid
            this.frameContracts = data.data.filter(
              (contract) => parseFloat(contract.kalan_miktar) > 0
            );
          } else {
            console.error(
              "Frame contracts could not be loaded:",
              data?.message || "Unknown error"
            );
            // Even if there are issues, ensure the property is initialized
            this.frameContracts = [];
          }
          // Mark contracts as loaded regardless of success or failure
          this.contractsLoaded = true;
        })
        .catch((error) => {
          console.error("Error loading frame contracts:", error);
          // Initialize as an empty array in case of error
          this.frameContracts = [];
          // Still mark as loaded even if there was an error
          this.contractsLoaded = true;
        });
    },
    getAvailableContract(componentCode) {
      // Find the best available contract for a component code (malzeme_kodu or esans_kodu)
      if (!this.frameContracts || this.frameContracts.length === 0) return null;

      // Filter contracts that match the component code
      const matchingContracts = this.frameContracts.filter(
        (contract) =>
          contract.malzeme_kodu === componentCode &&
          contract.gecerli_mi == 1 && // Using == to handle string comparison
          parseFloat(contract.kalan_miktar) > 0
      );

      if (matchingContracts.length === 0) return null;

      // Return the contract with highest priority (lowest oncelik number)
      return matchingContracts.reduce((prev, current) =>
        prev.oncelik < current.oncelik ? prev : current
      );
    },
    getContractForComponent(componentCode) {
      // Find the best available contract for a specific component code
      return this.getAvailableContract(componentCode);
    },
    getRelevantContracts() {
      // Get all frame contracts that are relevant to this product's components
      if (
        !this.frameContracts ||
        !this.productData ||
        !this.productData.bom_components
      ) {
        return [];
      }

      // Get all component codes used in this product
      const componentCodes = this.productData.bom_components.map(
        (comp) => comp.bilesen_kodu
      );

      // Find contracts that match any of these component codes
      return this.frameContracts.filter(
        (contract) =>
          componentCodes.includes(contract.malzeme_kodu) &&
          contract.gecerli_mi == 1 // Using == to handle string comparison
      );
    },
    getAvailableContractsCount() {
      // Count how many valid contracts are available for this product
      return this.getRelevantContracts().length;
    },
    getShortfallForComponent(componentCode) {
      if (
        !this.stockGap ||
        !this.stockGap.hasGap ||
        !this.stockGap.gapDetails
      ) {
        return 0;
      }
      const detail = this.stockGap.gapDetails.find(
        (d) => d.code === componentCode
      );
      return detail ? detail.shortfall : 0;
    },
    // Image viewer methods removed since we're using Lightbox2
  },
});

app.mount("#app");

// Initialize Fancybox after Vue has rendered
document.addEventListener("DOMContentLoaded", function () {
  // Fancybox will automatically initialize for elements with data-fancybox attribute
  // But we can also configure it if needed
  if (typeof Fancybox !== "undefined") {
    Fancybox.bind("[data-fancybox]", {
      // Optional customizations
      infinite: true, // Loop through gallery
      Carousel: {
        infinite: true,
      },
    });
  }
});
