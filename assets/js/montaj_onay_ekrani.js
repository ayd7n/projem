new Vue({
  el: "#approvalApp",
  data: {
    loading: false,
    workOrders: [],
    selectedIds: [],
    selectAll: false,
    showApproveModal: false,
    selectedWorkOrder: {},
    approveForm: {
      tamamlanan_miktar: 0,
      note: "",
    },
    pagination: {
      current_page: 1,
      per_page: 25,
      total: 0,
      total_pages: 1,
    },
  },
  computed: {
    calculatedMissing() {
      const planned = parseFloat(this.selectedWorkOrder.planlanan_miktar) || 0;
      const completed = parseFloat(this.approveForm.tamamlanan_miktar) || 0;
      return Math.max(0, planned - completed).toFixed(2);
    },
  },
  methods: {
    formatNumber(value) {
      const n = parseFloat(value);
      if (!Number.isFinite(n)) {
        return "0.00";
      }
      return n.toFixed(2);
    },
    syncSelectAllState() {
      if (this.workOrders.length === 0) {
        this.selectAll = false;
        return;
      }
      const selectedSet = new Set(this.selectedIds.map(String));
      this.selectAll = this.workOrders.every((item) =>
        selectedSet.has(String(item.is_emri_numarasi))
      );
    },
    toggleSelectAll() {
      if (this.selectAll) {
        this.selectedIds = this.workOrders.map((x) => x.is_emri_numarasi);
        return;
      }
      this.selectedIds = [];
    },
    normalizeBatchResult(responseData) {
      const rows = Array.isArray(responseData.results) ? responseData.results : [];
      const successCount = rows.filter((x) => x.status === "success").length;
      const errorCount = rows.length - successCount;
      const errorMessages = rows
        .filter((x) => x.status !== "success")
        .map((x) => `${x.is_emri_numarasi || "-"}: ${x.message || "Bilinmeyen hata"}`);

      return {
        successCount,
        errorCount,
        errorMessages,
      };
    },
    async fetchPendingWorkOrders(page = 1) {
      this.loading = true;
      try {
        const response = await axios.get(
          `api_islemleri/montaj_is_emirleri_islemler.php?action=get_work_orders&durum=onay_bekliyor&page=${page}&limit=${this.pagination.per_page}`
        );

        if (response.data.status !== "success") {
          throw new Error(response.data.message || "Onay bekleyen kayitlar alinamadi.");
        }

        this.workOrders = response.data.data || [];
        this.pagination = response.data.pagination || this.pagination;

        const currentIds = new Set(this.workOrders.map((x) => String(x.is_emri_numarasi)));
        this.selectedIds = this.selectedIds.filter((id) => currentIds.has(String(id)));
        this.syncSelectAllState();
      } catch (error) {
        Swal.fire({
          title: "Hata",
          text:
            (error.response && error.response.data && error.response.data.message) ||
            error.message ||
            "Listeleme sirasinda hata olustu.",
          icon: "error",
        });
      } finally {
        this.loading = false;
      }
    },
    openApproveModal(workOrder) {
      this.selectedWorkOrder = { ...workOrder };
      this.approveForm = {
        tamamlanan_miktar: parseFloat(workOrder.tamamlanan_miktar) || 0,
        note: "",
      };
      this.showApproveModal = true;
    },
    async approveSingle() {
      const id = this.selectedWorkOrder.is_emri_numarasi;
      const amount = parseFloat(this.approveForm.tamamlanan_miktar);

      if (!id) {
        return;
      }
      if (!Number.isFinite(amount) || amount < 0) {
        Swal.fire({
          title: "Gecersiz Miktar",
          text: "Tamamlanan miktar 0 veya daha buyuk olmalidir.",
          icon: "warning",
        });
        return;
      }

      try {
        const response = await axios.post(
          "api_islemleri/montaj_is_emirleri_islemler.php",
          {
            action: "approve_work_order",
            is_emri_numarasi: id,
            tamamlanan_miktar: amount,
            onay_notu: this.approveForm.note || "",
          }
        );

        if (response.data.status !== "success") {
          throw new Error(response.data.message || "Onay islemi basarisiz.");
        }

        this.showApproveModal = false;
        Swal.fire({
          title: "Basarili",
          text: response.data.message || "Is emri onaylandi.",
          icon: "success",
        });
        await this.fetchPendingWorkOrders(this.pagination.current_page);
      } catch (error) {
        Swal.fire({
          title: "Hata",
          text:
            (error.response && error.response.data && error.response.data.message) ||
            error.message ||
            "Onay islemi sirasinda hata olustu.",
          icon: "error",
        });
      }
    },
    async rejectSingle(id) {
      const result = await Swal.fire({
        title: "Is Emrini Reddet",
        text: `Is emri #${id} kaydini reddetmek istiyor musunuz?`,
        input: "textarea",
        inputLabel: "Red Notu (opsiyonel)",
        inputPlaceholder: "Reddetme nedenini yazabilirsiniz...",
        showCancelButton: true,
        confirmButtonText: "Reddet",
        cancelButtonText: "Iptal",
        confirmButtonColor: "#dc3545",
      });

      if (!result.isConfirmed) {
        return;
      }

      try {
        const response = await axios.post(
          "api_islemleri/montaj_is_emirleri_islemler.php",
          {
            action: "reject_work_order",
            is_emri_numarasi: id,
            red_notu: result.value || "",
          }
        );

        if (response.data.status !== "success") {
          throw new Error(response.data.message || "Red islemi basarisiz.");
        }

        Swal.fire({
          title: "Basarili",
          text: response.data.message || "Is emri reddedildi.",
          icon: "success",
        });
        await this.fetchPendingWorkOrders(this.pagination.current_page);
      } catch (error) {
        Swal.fire({
          title: "Hata",
          text:
            (error.response && error.response.data && error.response.data.message) ||
            error.message ||
            "Red islemi sirasinda hata olustu.",
          icon: "error",
        });
      }
    },
    async bulkApprove() {
      if (this.selectedIds.length === 0) {
        return;
      }

      const result = await Swal.fire({
        title: "Toplu Onay",
        text: `${this.selectedIds.length} kayit onaylanacak. Devam edilsin mi?`,
        input: "textarea",
        inputLabel: "Toplu Onay Notu (opsiyonel)",
        inputPlaceholder: "Tum secili kayitlar icin not...",
        showCancelButton: true,
        confirmButtonText: "Toplu Onayla",
        cancelButtonText: "Iptal",
        confirmButtonColor: "#28a745",
      });

      if (!result.isConfirmed) {
        return;
      }

      try {
        const response = await axios.post(
          "api_islemleri/montaj_is_emirleri_islemler.php",
          {
            action: "bulk_approve_work_orders",
            is_emri_numaralari: this.selectedIds,
            onay_notu: result.value || "",
          }
        );

        const batch = this.normalizeBatchResult(response.data || {});
        const icon =
          batch.errorCount === 0 ? "success" : batch.successCount > 0 ? "warning" : "error";

        await Swal.fire({
          title: "Toplu Onay Sonucu",
          html: `
            <div><strong>Basarili:</strong> ${batch.successCount}</div>
            <div><strong>Basarisiz:</strong> ${batch.errorCount}</div>
            ${
              batch.errorMessages.length
                ? `<hr><div style="text-align:left;max-height:180px;overflow:auto;">${batch.errorMessages
                    .map((x) => `<div>- ${x}</div>`)
                    .join("")}</div>`
                : ""
            }
          `,
          icon,
        });

        this.selectedIds = [];
        this.selectAll = false;
        await this.fetchPendingWorkOrders(this.pagination.current_page);
      } catch (error) {
        Swal.fire({
          title: "Hata",
          text:
            (error.response && error.response.data && error.response.data.message) ||
            error.message ||
            "Toplu onay sirasinda hata olustu.",
          icon: "error",
        });
      }
    },
    async bulkReject() {
      if (this.selectedIds.length === 0) {
        return;
      }

      const result = await Swal.fire({
        title: "Toplu Red",
        text: `${this.selectedIds.length} kayit reddedilecek. Devam edilsin mi?`,
        input: "textarea",
        inputLabel: "Toplu Red Notu (opsiyonel)",
        inputPlaceholder: "Tum secili kayitlar icin not...",
        showCancelButton: true,
        confirmButtonText: "Toplu Reddet",
        cancelButtonText: "Iptal",
        confirmButtonColor: "#dc3545",
      });

      if (!result.isConfirmed) {
        return;
      }

      try {
        const response = await axios.post(
          "api_islemleri/montaj_is_emirleri_islemler.php",
          {
            action: "bulk_reject_work_orders",
            is_emri_numaralari: this.selectedIds,
            red_notu: result.value || "",
          }
        );

        const batch = this.normalizeBatchResult(response.data || {});
        const icon =
          batch.errorCount === 0 ? "success" : batch.successCount > 0 ? "warning" : "error";

        await Swal.fire({
          title: "Toplu Red Sonucu",
          html: `
            <div><strong>Basarili:</strong> ${batch.successCount}</div>
            <div><strong>Basarisiz:</strong> ${batch.errorCount}</div>
            ${
              batch.errorMessages.length
                ? `<hr><div style="text-align:left;max-height:180px;overflow:auto;">${batch.errorMessages
                    .map((x) => `<div>- ${x}</div>`)
                    .join("")}</div>`
                : ""
            }
          `,
          icon,
        });

        this.selectedIds = [];
        this.selectAll = false;
        await this.fetchPendingWorkOrders(this.pagination.current_page);
      } catch (error) {
        Swal.fire({
          title: "Hata",
          text:
            (error.response && error.response.data && error.response.data.message) ||
            error.message ||
            "Toplu red sirasinda hata olustu.",
          icon: "error",
        });
      }
    },
  },
  watch: {
    selectedIds() {
      this.syncSelectAllState();
    },
  },
  mounted() {
    this.fetchPendingWorkOrders(1);
  },
});
