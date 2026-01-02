/**
 * Kasa Yönetimi JavaScript
 * Dashboard verilerini yükler ve kasa işlemlerini yönetir
 */

// Global değişken
let dashboardData = {};

$(document).ready(function () {
  // Başlangıç ayarları
  initializePage();
  loadDashboardData();
  loadKasaHareketleri();
  loadCekler();

  // Event listeners
  setupEventListeners();
});

// Sayfa ayarlarını yap
function initializePage() {
  // Tarih alanını ayarla
  const now = new Date();
  const tzOffset = now.getTimezoneOffset() * 60000;
  const localISOTime = new Date(Date.now() - tzOffset)
    .toISOString()
    .slice(0, 16);
  $("#kasaTarih").val(localISOTime);

  // Varsayılan filtre tarihleri (bu ay)
  const startOfMonth = new Date(now.getFullYear(), now.getMonth(), 1);
  $("#filterBaslangic").val(startOfMonth.toISOString().split("T")[0]);
  $("#filterBitis").val(now.toISOString().split("T")[0]);
}

// Event listener'ları kur
function setupEventListeners() {
  // Personel detay butonu
  $("#personelDetayBtn").click(function () {
    if (
      dashboardData &&
      dashboardData.bekleyen_odemeler &&
      dashboardData.bekleyen_odemeler.detaylar &&
      dashboardData.bekleyen_odemeler.detaylar.personel
    ) {
      const personelListesi = dashboardData.bekleyen_odemeler.detaylar.personel;
      let html = "";

      if (personelListesi.length > 0) {
        personelListesi.forEach((p) => {
          html += `
                      <tr>
                          <td>${p.ad_soyad}</td>
                          <td>${formatMoney(p.brut_ucret)} ₺</td>
                          <td>${formatMoney(p.avans)} ₺</td>
                          <td>${formatMoney(p.odenen)} ₺</td>
                          <td class="text-danger font-weight-bold">${formatMoney(
                            p.kalan_odeme
                          )} ₺</td>
                      </tr>
                  `;
        });
      } else {
        html =
          '<tr><td colspan="5" class="text-center">Ödenmemiş personel maaşı bulunamadı.</td></tr>';
      }

      $("#personelDetayTableBody").html(html);
      $("#personelMaasDetayModal").modal("show");
    }
  });

  // Sabit gider detay butonu
  $("#sabitGiderDetayBtn").click(function () {
    if (
      dashboardData &&
      dashboardData.bekleyen_odemeler &&
      dashboardData.bekleyen_odemeler.detaylar &&
      dashboardData.bekleyen_odemeler.detaylar.sabit_giderler
    ) {
      const giderListesi =
        dashboardData.bekleyen_odemeler.detaylar.sabit_giderler;
      let html = "";

      if (giderListesi.length > 0) {
        giderListesi.forEach((g) => {
          html += `
                      <tr>
                          <td>${g.odeme_gunu}</td>
                          <td>${g.odeme_adi}</td>
                          <td>${g.alici_firma}</td>
                          <td class="text-danger font-weight-bold">${formatMoney(
                            g.tutar
                          )} ₺</td>
                      </tr>
                  `;
        });
      } else {
        html =
          '<tr><td colspan="4" class="text-center">Ödenmemiş sabit gider bulunamadı.</td></tr>';
      }

      $("#sabitGiderDetayTableBody").html(html);
      $("#sabitGiderDetayModal").modal("show");
    }
  });

  // Kasa işlem butonu
  $("#kasaIslemBtn").click(function () {
    $("#kasaIslemTipi").val("kasa_ekle");
    $("#kasaIslemModal .modal-title").html(
      '<i class="fas fa-plus-circle"></i> Kasaya Para Ekle'
    );
    $("#kasaSubmitBtn")
      .removeClass("btn-danger")
      .addClass("btn-primary")
      .html('<i class="fas fa-save"></i> Kaydet');
    $("#kasaIslemModal").modal("show");
  });

  // Kasa ekle/çıkar butonları
  $(".kasa-ekle-btn").click(function () {
    const kasa = $(this).data("kasa");
    $("#kasaParaBirimi").val(kasa);
    $("#kasaIslemTipi").val("kasa_ekle");
    $("#kasaIslemModal .modal-title").html(
      '<i class="fas fa-plus-circle text-success"></i> ' +
        kasa +
        " Kasasına Ekle"
    );
    $("#kasaSubmitBtn").removeClass("btn-danger").addClass("btn-success");
    $("#kasaIslemModal").modal("show");
  });

  $(".kasa-cikar-btn").click(function () {
    const kasa = $(this).data("kasa");
    $("#kasaParaBirimi").val(kasa);
    $("#kasaIslemTipi").val("kasa_cikar");
    $("#kasaIslemModal .modal-title").html(
      '<i class="fas fa-minus-circle text-danger"></i> ' +
        kasa +
        " Kasasından Çıkar"
    );
    $("#kasaSubmitBtn").removeClass("btn-success").addClass("btn-danger");
    $("#kasaIslemModal").modal("show");
  });

  // Çek ekle butonu
  $("#cekEkleBtn").click(function () {
    $("#cekEkleForm")[0].reset();
    $("#cekVade").val(new Date().toISOString().split("T")[0]);
    $("#cekEkleModal").modal("show");
  });

  // Çek detay butonu
  $("#cekDetayBtn").click(function () {
    $('a[href="#cekler"]').tab("show");
  });

  // Kasa işlem formu
  $("#kasaIslemForm").submit(function (e) {
    e.preventDefault();
    submitKasaIslemi();
  });

  // Çek formu
  $("#cekEkleForm").submit(function (e) {
    e.preventDefault();
    submitCek();
  });

  // Filtreler
  $("#filterKasa, #filterIslemTipi, #filterBaslangic, #filterBitis").change(
    function () {
      loadKasaHareketleri(1);
    }
  );

  $("#filterCekTipi, #filterCekDurum").change(function () {
    loadCekler(1);
  });

  // Arama
  let hareketSearchTimeout;
  $("#hareketSearch").on("input", function () {
    clearTimeout(hareketSearchTimeout);
    hareketSearchTimeout = setTimeout(() => loadKasaHareketleri(1), 300);
  });

  let cekSearchTimeout;
  $("#cekSearch").on("input", function () {
    clearTimeout(cekSearchTimeout);
    cekSearchTimeout = setTimeout(() => loadCekler(1), 300);
  });

  // Temizle butonları
  $("#clearHareketSearch").click(function () {
    $("#hareketSearch").val("");
    loadKasaHareketleri(1);
  });

  $("#clearCekSearch").click(function () {
    $("#cekSearch").val("");
    loadCekler(1);
  });

  // Sayfa başına kayıt
  $("#hareketPerPage").change(function () {
    loadKasaHareketleri(1);
  });

  // Excel export
  $("#exportExcelBtn").click(exportToExcel);

  // Detay butonları
  $("#tedarikciDetayBtn").click(function () {
    loadTedarikciDetay();
    $("#tedarikciDetayModal").modal("show");
  });

  $("#musteriDetayBtn").click(function () {
    loadMusteriDetay();
    $("#musteriDetayModal").modal("show");
  });
}

// Dashboard verilerini yükle
function loadDashboardData() {
  $.ajax({
    url: "api_islemleri/kasa_yonetimi_islemler.php",
    data: { action: "get_dashboard_summary" },
    dataType: "json",
    success: function (response) {
      if (response.status === "success") {
        updateDashboard(response.data);
      } else {
        showAlert("danger", response.message || "Veriler yüklenemedi.");
      }
    },
    error: function () {
      showAlert("danger", "Sunucuya bağlanılamadı.");
    },
  });
}

// Dashboard'u güncelle
function updateDashboard(data) {
  // Veriyi global değişkene kaydet (Modallar için)
  dashboardData = data;

  // Stok değerleri
  $("#stokUrunler").text(formatMoney(data.stok_degerleri.urunler) + " ₺");
  $("#stokMalzemeler").text(formatMoney(data.stok_degerleri.malzemeler) + " ₺");
  $("#stokEsanslar").text(formatMoney(data.stok_degerleri.esanslar) + " ₺");
  $("#stokToplam").text(formatMoney(data.stok_degerleri.toplam) + " ₺");

  // Kasa bakiyeleri
  $("#kasaTL").text(formatMoney(data.kasalar.TL) + " ₺");
  $("#kasaUSD").text(formatMoney(data.kasalar.USD) + " $");
  $("#kasaEUR").text(formatMoney(data.kasalar.EUR) + " €");
  $("#kasaCek").text(data.cek_kasasi.adet + " adet");
  $("#cekTLKarsiligi").text(
    "(~" + formatMoney(data.cek_kasasi.toplam * (data.kurlar.TL || 1)) + " ₺)"
  );

  // Tedarikçi borçları
  $("#borcUSD").text(
    formatMoney(data.tedarikci_borclari.detay.USD || 0) + " $"
  );
  $("#borcEUR").text(
    formatMoney(data.tedarikci_borclari.detay.EUR || 0) + " €"
  );
  $("#borcTL").text(formatMoney(data.tedarikci_borclari.detay.TL || 0) + " ₺");
  $("#borcToplam").text(formatMoney(data.tedarikci_borclari.tl_toplam) + " ₺");

  // Bekleyen Ödemeler (Yeni)
  if (data.bekleyen_odemeler) {
    $("#bekleyenPersonel").text(
      formatMoney(data.bekleyen_odemeler.personel) + " ₺"
    );
    $("#bekleyenSabit").text(
      formatMoney(data.bekleyen_odemeler.sabit_giderler) + " ₺"
    );
  }

  // Müşteri alacakları
  if (data.musteri_alacaklari) {
    $("#alacakUSD").text(
      formatMoney(data.musteri_alacaklari.detay.USD || 0) + " $"
    );
    $("#alacakEUR").text(
      formatMoney(data.musteri_alacaklari.detay.EUR || 0) + " €"
    );
    $("#alacakTL").text(
      formatMoney(data.musteri_alacaklari.detay.TL || 0) + " ₺"
    );
    $("#alacakToplam").text(
      formatMoney(data.musteri_alacaklari.tl_toplam) + " ₺"
    );
  }
}

// Kasa hareketlerini yükle
function loadKasaHareketleri(page = 1) {
  const params = {
    action: "get_kasa_hareketleri",
    page: page,
    per_page: $("#hareketPerPage").val() || 20,
    kasa_adi: $("#filterKasa").val(),
    islem_tipi: $("#filterIslemTipi").val(),
    baslangic_tarihi: $("#filterBaslangic").val(),
    bitis_tarihi: $("#filterBitis").val(),
    search: $("#hareketSearch").val(),
  };

  $.ajax({
    url: "api_islemleri/kasa_yonetimi_islemler.php",
    data: params,
    dataType: "json",
    success: function (response) {
      if (response.status === "success") {
        renderHareketlerTable(
          response.data,
          response.total,
          page,
          response.per_page,
          response.total_pages
        );
      } else {
        $("#hareketlerTableBody").html(
          '<tr><td colspan="9" class="text-center text-danger">Hata: ' +
            (response.message || "Bilinmiyor") +
            "</td></tr>"
        );
      }
    },
    error: function () {
      $("#hareketlerTableBody").html(
        '<tr><td colspan="9" class="text-center text-danger">Sunucu hatası</td></tr>'
      );
    },
  });
}

// Hareketler tablosunu render et
function renderHareketlerTable(data, total, page, perPage, totalPages) {
  if (data.length === 0) {
    $("#hareketlerTableBody").html(
      '<tr><td colspan="9" class="text-center text-muted">Kayıt bulunamadı</td></tr>'
    );
    $("#hareketTableInfo").text("0 kayıt");
    $("#hareketPagination").empty();
    return;
  }

  let html = "";
  data.forEach(function (row) {
    const islemBadge = getIslemTipiBadge(row.islem_tipi);
    const tarih = formatDate(row.tarih);
    const tutar = formatMoney(row.tutar) + " " + row.para_birimi;
    const tlKarsiligi = formatMoney(row.tl_karsiligi) + " ₺";
    const kaynak = row.kaynak_tablo ? row.kaynak_tablo.replace("_", " ") : "-";
    const canDelete =
      row.islem_tipi === "kasa_ekle" || row.islem_tipi === "kasa_cikar";

    html += `<tr>
            <td>${tarih}</td>
            <td>${islemBadge}</td>
            <td>${row.kasa_adi}</td>
            <td>${tutar}</td>
            <td>${tlKarsiligi}</td>
            <td>${kaynak}</td>
            <td>${row.aciklama || "-"}</td>
            <td>${row.kaydeden_personel || "-"}</td>
            <td>
                ${
                  canDelete
                    ? `<button class="btn btn-action btn-outline-danger" onclick="deleteHareket(${row.hareket_id})" title="Sil"><i class="fas fa-trash"></i></button>`
                    : "-"
                }
            </td>
        </tr>`;
  });

  $("#hareketlerTableBody").html(html);
  $("#hareketTableInfo").text(
    `${(page - 1) * perPage + 1}-${Math.min(
      page * perPage,
      total
    )} / ${total} kayıt`
  );

  renderPagination(
    "#hareketPagination",
    page,
    totalPages,
    "loadKasaHareketleri"
  );
}

// Çekleri yükle
function loadCekler(page = 1) {
  const params = {
    action: "get_cekler",
    page: page,
    per_page: 20,
    cek_tipi: $("#filterCekTipi").val(),
    cek_durumu: $("#filterCekDurum").val(),
    search: $("#cekSearch").val(),
  };

  $.ajax({
    url: "api_islemleri/kasa_yonetimi_islemler.php",
    data: params,
    dataType: "json",
    success: function (response) {
      if (response.status === "success") {
        renderCeklerTable(
          response.data,
          response.total,
          page,
          response.per_page
        );
      } else {
        $("#ceklerTableBody").html(
          '<tr><td colspan="8" class="text-center text-danger">Hata</td></tr>'
        );
      }
    },
    error: function () {
      $("#ceklerTableBody").html(
        '<tr><td colspan="8" class="text-center text-danger">Sunucu hatası</td></tr>'
      );
    },
  });
}

// Çekler tablosunu render et
function renderCeklerTable(data, total, page, perPage) {
  if (data.length === 0) {
    $("#ceklerTableBody").html(
      '<tr><td colspan="8" class="text-center text-muted">Çek bulunamadı</td></tr>'
    );
    $("#cekTableInfo").text("0 kayıt");
    $("#cekPagination").empty();
    return;
  }

  let html = "";
  data.forEach(function (row) {
    const tutar = formatMoney(row.cek_tutari) + " " + row.cek_para_birimi;
    const vade = formatDate(row.vade_tarihi);
    const tip =
      row.cek_tipi === "alacak"
        ? '<span class="badge badge-success">Alacak</span>'
        : '<span class="badge badge-warning">Verilen</span>';
    const durum = getCekDurumBadge(row.cek_durumu);

    html += `<tr>
            <td><strong>${row.cek_no}</strong></td>
            <td>${row.cek_sahibi}</td>
            <td>${row.cek_banka_adi || "-"}</td>
            <td>${tutar}</td>
            <td>${vade}</td>
            <td>${tip}</td>
            <td>${durum}</td>
            <td>
                <div class="dropdown">
                    <button class="btn btn-action btn-outline-secondary dropdown-toggle" data-toggle="dropdown">
                        <i class="fas fa-ellipsis-v"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-right">
                        <a class="dropdown-item" href="#" onclick="updateCekDurum(${
                          row.cek_id
                        }, 'tahsilde')"><i class="fas fa-paper-plane"></i> Tahsile Gönder</a>
                        <a class="dropdown-item" href="#" onclick="updateCekDurum(${
                          row.cek_id
                        }, 'geri_odendi')"><i class="fas fa-check"></i> Tahsil Edildi</a>
                        <a class="dropdown-item" href="#" onclick="updateCekDurum(${
                          row.cek_id
                        }, 'iptal')"><i class="fas fa-ban"></i> İptal Et</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item text-danger" href="#" onclick="deleteCek(${
                          row.cek_id
                        })"><i class="fas fa-trash"></i> Sil</a>
                    </div>
                </div>
            </td>
        </tr>`;
  });

  $("#ceklerTableBody").html(html);
  $("#cekTableInfo").text(total + " kayıt");

  const totalPages = Math.ceil(total / perPage);
  renderPagination("#cekPagination", page, totalPages, "loadCekler");
}

// Kasa işlemi gönder
function submitKasaIslemi() {
  const formData = {
    action: "add_kasa_islemi",
    islem_tipi: $("#kasaIslemTipi").val(),
    kasa_adi: $("#kasaParaBirimi").val(),
    tutar: $("#kasaTutar").val(),
    tarih: $("#kasaTarih").val(),
    aciklama: $("#kasaAciklama").val(),
  };

  $.ajax({
    url: "api_islemleri/kasa_yonetimi_islemler.php",
    method: "POST",
    data: formData,
    dataType: "json",
    success: function (response) {
      if (response.status === "success") {
        $("#kasaIslemModal").modal("hide");
        $("#kasaIslemForm")[0].reset();
        showAlert("success", response.message);
        loadDashboardData();
        loadKasaHareketleri();
      } else {
        Swal.fire("Hata!", response.message, "error");
      }
    },
    error: function () {
      Swal.fire("Hata!", "Sunucu hatası", "error");
    },
  });
}

// Çek ekle
function submitCek() {
  const formData = $("#cekEkleForm").serialize() + "&action=add_cek";

  $.ajax({
    url: "api_islemleri/kasa_yonetimi_islemler.php",
    method: "POST",
    data: formData,
    dataType: "json",
    success: function (response) {
      if (response.status === "success") {
        $("#cekEkleModal").modal("hide");
        $("#cekEkleForm")[0].reset();
        showAlert("success", response.message);
        loadDashboardData();
        loadCekler();
      } else {
        Swal.fire("Hata!", response.message, "error");
      }
    },
    error: function () {
      Swal.fire("Hata!", "Sunucu hatası", "error");
    },
  });
}

// Hareket sil
function deleteHareket(hareketId) {
  Swal.fire({
    title: "Emin misiniz?",
    text: "Bu kasa işlemi geri alınacak ve silinecek!",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#d33",
    cancelButtonColor: "#3085d6",
    confirmButtonText: "Evet, Sil!",
    cancelButtonText: "İptal",
  }).then((result) => {
    if (result.isConfirmed) {
      $.ajax({
        url: "api_islemleri/kasa_yonetimi_islemler.php",
        method: "POST",
        data: { action: "delete_kasa_islemi", hareket_id: hareketId },
        dataType: "json",
        success: function (response) {
          if (response.status === "success") {
            showAlert("success", response.message);
            loadDashboardData();
            loadKasaHareketleri();
          } else {
            Swal.fire("Hata!", response.message, "error");
          }
        },
        error: function () {
          Swal.fire("Hata!", "Sunucu hatası", "error");
        },
      });
    }
  });
}

// Çek durumu güncelle
function updateCekDurum(cekId, yeniDurum) {
  $.ajax({
    url: "api_islemleri/kasa_yonetimi_islemler.php",
    method: "POST",
    data: { action: "update_cek_durumu", cek_id: cekId, yeni_durum: yeniDurum },
    dataType: "json",
    success: function (response) {
      if (response.status === "success") {
        showAlert("success", response.message);
        loadDashboardData();
        loadCekler();
      } else {
        Swal.fire("Hata!", response.message, "error");
      }
    },
    error: function () {
      Swal.fire("Hata!", "Sunucu hatası", "error");
    },
  });
}

// Çek sil
function deleteCek(cekId) {
  Swal.fire({
    title: "Emin misiniz?",
    text: "Bu çek kaydı silinecek!",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#d33",
    cancelButtonColor: "#3085d6",
    confirmButtonText: "Evet, Sil!",
    cancelButtonText: "İptal",
  }).then((result) => {
    if (result.isConfirmed) {
      $.ajax({
        url: "api_islemleri/kasa_yonetimi_islemler.php",
        method: "POST",
        data: { action: "delete_cek", cek_id: cekId },
        dataType: "json",
        success: function (response) {
          if (response.status === "success") {
            showAlert("success", response.message);
            loadDashboardData();
            loadCekler();
          } else {
            Swal.fire("Hata!", response.message, "error");
          }
        },
        error: function () {
          Swal.fire("Hata!", "Sunucu hatası", "error");
        },
      });
    }
  });
}

// Excel'e aktar
function exportToExcel() {
  const params = new URLSearchParams({
    action: "get_kasa_hareketleri",
    per_page: 10000,
    kasa_adi: $("#filterKasa").val(),
    islem_tipi: $("#filterIslemTipi").val(),
    baslangic_tarihi: $("#filterBaslangic").val(),
    bitis_tarihi: $("#filterBitis").val(),
    search: $("#hareketSearch").val(),
  });

  $.ajax({
    url: "api_islemleri/kasa_yonetimi_islemler.php?" + params.toString(),
    dataType: "json",
    success: function (response) {
      if (response.status === "success" && response.data.length > 0) {
        let csv =
          "Tarih;İşlem Tipi;Kasa;Tutar;Para Birimi;TL Karşılığı;Kaynak;Açıklama;Personel\n";
        response.data.forEach(function (row) {
          csv += `${row.tarih};${row.islem_tipi};${row.kasa_adi};${row.tutar};${
            row.para_birimi
          };${row.tl_karsiligi};${row.kaynak_tablo || ""};${
            row.aciklama || ""
          };${row.kaydeden_personel || ""}\n`;
        });

        const blob = new Blob(["\ufeff" + csv], {
          type: "text/csv;charset=utf-8;",
        });
        const link = document.createElement("a");
        link.href = URL.createObjectURL(blob);
        link.download =
          "kasa_hareketleri_" + new Date().toISOString().split("T")[0] + ".csv";
        link.click();
      } else {
        Swal.fire("Uyarı", "Dışa aktarılacak veri yok.", "warning");
      }
    },
  });
}

// Yardımcı fonksiyonlar
function formatMoney(value) {
  return parseFloat(value || 0).toLocaleString("tr-TR", {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  });
}

function formatDate(dateStr) {
  if (!dateStr) return "-";
  const d = new Date(dateStr);
  return (
    d.toLocaleDateString("tr-TR") +
    " " +
    d.toLocaleTimeString("tr-TR", { hour: "2-digit", minute: "2-digit" })
  );
}

function getIslemTipiBadge(tip) {
  const badges = {
    kasa_ekle: '<span class="badge-islem badge-gelir">Kasa Ekle</span>',
    kasa_cikar: '<span class="badge-islem badge-gider">Kasa Çıkar</span>',
    gelir_girisi: '<span class="badge-islem badge-gelir">Gelir</span>',
    gider_cikisi: '<span class="badge-islem badge-gider">Gider</span>',
    cek_alma: '<span class="badge-islem badge-cek">Çek Alındı</span>',
    cek_odeme: '<span class="badge-islem badge-cek">Çek Verildi</span>',
    cek_kullanimi: '<span class="badge-islem badge-cek">Çek Kullanıldı</span>',
    cek_tahsildi: '<span class="badge-islem badge-gelir">Çek Tahsil</span>',
    transfer_giris:
      '<span class="badge-islem badge-transfer">Transfer Giriş</span>',
    transfer_cikis:
      '<span class="badge-islem badge-transfer">Transfer Çıkış</span>',
  };
  return badges[tip] || `<span class="badge-islem">${tip}</span>`;
}

function getCekDurumBadge(durum) {
  const badges = {
    alindi: '<span class="cek-durum cek-durum-alindi">Alındı</span>',
    tahsilde: '<span class="cek-durum cek-durum-tahsilde">Tahsilde</span>',
    kullanildi:
      '<span class="cek-durum cek-durum-kullanildi">Kullanıldı</span>',
    iptal: '<span class="cek-durum cek-durum-iptal">İptal</span>',
    geri_odendi:
      '<span class="cek-durum cek-durum-alindi">Tahsil Edildi</span>',
    teminat_verildi:
      '<span class="cek-durum cek-durum-tahsilde">Teminat</span>',
  };
  return badges[durum] || `<span class="cek-durum">${durum}</span>`;
}

function renderPagination(selector, currentPage, totalPages, callbackFn) {
  if (totalPages <= 1) {
    $(selector).empty();
    return;
  }

  let html = "";
  html += `<li class="page-item ${currentPage === 1 ? "disabled" : ""}">
        <a class="page-link" href="#" onclick="${callbackFn}(${
    currentPage - 1
  }); return false;">‹</a>
    </li>`;

  for (
    let i = Math.max(1, currentPage - 2);
    i <= Math.min(totalPages, currentPage + 2);
    i++
  ) {
    html += `<li class="page-item ${i === currentPage ? "active" : ""}">
            <a class="page-link" href="#" onclick="${callbackFn}(${i}); return false;">${i}</a>
        </li>`;
  }

  html += `<li class="page-item ${
    currentPage === totalPages ? "disabled" : ""
  }">
        <a class="page-link" href="#" onclick="${callbackFn}(${
    currentPage + 1
  }); return false;">›</a>
    </li>`;

  $(selector).html(html);
}

function showAlert(type, message) {
  const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    `;
  $("#alert-placeholder").html(alertHtml);
  setTimeout(() => $("#alert-placeholder .alert").fadeOut(), 4000);
}

// Tedarikçi borçları detay yükle
function loadTedarikciDetay() {
  $.ajax({
    url: "api_islemleri/kasa_yonetimi_islemler.php",
    data: { action: "get_tedarikci_odemeleri" },
    dataType: "json",
    success: function (response) {
      if (response.status === "success" && response.data.detaylar.length > 0) {
        let html = "";
        response.data.detaylar.forEach(function (row) {
          const tutar =
            formatMoney(row.odenmemis_tutar) + " " + row.para_birimi;
          html += `<tr>
            <td><strong>${row.tedarikci_adi}</strong></td>
            <td>${formatMoney(row.odenmemis_miktar)}</td>
            <td>${formatMoney(row.birim_fiyat)} ${row.para_birimi}</td>
            <td class="text-danger font-weight-bold">${tutar}</td>
          </tr>`;
        });
        // Toplam satırı
        html += `<tr style="background: #f9fafb;">
          <td colspan="3" class="text-right"><strong>TL Karşılığı Toplam:</strong></td>
          <td class="text-danger font-weight-bold">${formatMoney(
            response.data.tl_toplam
          )} ₺</td>
        </tr>`;
        $("#tedarikciDetayTableBody").html(html);
      } else {
        $("#tedarikciDetayTableBody").html(
          '<tr><td colspan="4" class="text-center text-muted p-4">Ödeme bekleyen kayıt yok</td></tr>'
        );
      }
    },
    error: function () {
      $("#tedarikciDetayTableBody").html(
        '<tr><td colspan="4" class="text-center text-danger p-4">Veri yüklenemedi</td></tr>'
      );
    },
  });
}

// Müşteri alacakları detay yükle
function loadMusteriDetay() {
  $.ajax({
    url: "api_islemleri/kasa_yonetimi_islemler.php",
    data: { action: "get_musteri_alacaklari" },
    dataType: "json",
    success: function (response) {
      if (
        response.status === "success" &&
        response.data.liste &&
        response.data.liste.length > 0
      ) {
        let html = "";
        response.data.liste.forEach(function (row) {
          const pb = row.para_birimi || "TL";
          const tarih = row.tarih
            ? new Date(row.tarih).toLocaleDateString("tr-TR")
            : "-";
          html += `<tr>
            <td><strong>#${row.siparis_id}</strong></td>
            <td>${row.musteri_adi || "-"}</td>
            <td>${tarih}</td>
            <td>${formatMoney(row.siparis_tutari)} ${pb}</td>
            <td>${formatMoney(row.odenen_tutar)} ${pb}</td>
            <td class="text-success font-weight-bold">${formatMoney(
              row.kalan
            )} ${pb}</td>
          </tr>`;
        });
        // Toplam satırı
        html += `<tr style="background: #f9fafb;">
          <td colspan="5" class="text-right"><strong>TL Karşılığı Toplam:</strong></td>
          <td class="text-success font-weight-bold">${formatMoney(
            response.data.tl_toplam
          )} ₺</td>
        </tr>`;
        $("#musteriDetayTableBody").html(html);
      } else {
        $("#musteriDetayTableBody").html(
          '<tr><td colspan="6" class="text-center text-muted p-4">Alacak bekleyen kayıt yok</td></tr>'
        );
      }
    },
    error: function () {
      $("#musteriDetayTableBody").html(
        '<tr><td colspan="6" class="text-center text-danger p-4">Veri yüklenemedi</td></tr>'
      );
    },
  });
}
