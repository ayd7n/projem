/**
 * Şirket Kasası - Final JS
 */
const SirketKasasi = {
    init: function() {
        this.bindEvents();
        this.loadAll();
    },

    bindEvents: function() {
        const self = this;
        
        // Modal açılış tarihi
        $('#kasaIslemBtn').on('click', function() {
            const now = new Date();
            const tzoffset = now.getTimezoneOffset() * 60000; 
            const localISOTime = (new Date(now - tzoffset)).toISOString().slice(0, 16);
            $('#kasa_tarih').val(localISOTime);
            $('#kasaIslemModal').modal('show');
        });

        // Form Gönder (Ekle)
        $('#kasaIslemForm').on('submit', function(e) {
            e.preventDefault();
            self.add();
        });

        // SİLME BUTONU (DELEGATION)
        $(document).on('click', '.btn-delete-cash', function() {
            const id = $(this).attr('data-id');
            self.remove(id);
        });

        // Arama ve Sayfalama
        $('#incomeSearchInput, #expenseSearchInput, #cashSearchInput').on('keyup', function() {
            const type = $(this).attr('id').replace('SearchInput', '');
            self.loadTable(type, 1);
        });
    },

    loadAll: function() {
        this.loadStats();
        this.loadTable('income', 1);
        this.loadTable('expense', 1);
        this.loadTable('cash', 1);
    },

    loadStats: function() {
        $.get('api_islemleri/sirket_kasasi_islemler.php', { action: 'get_statistics' }, function(res) {
            if (res.status === 'success') {
                if (res.kasa_bakiyeleri) {
                    Object.keys(res.kasa_bakiyeleri).forEach(pb => {
                        $(`.stat-card[data-currency="${pb}"] .stat-value`).text(SirketKasasi.format(res.kasa_bakiyeleri[pb]) + ' ' + pb);
                    });
                }
                if (res.monthly_income_by_currency) {
                    Object.keys(res.monthly_income_by_currency).forEach(pb => {
                        $(`.stat-card[data-income-currency="${pb}"] .stat-value`).text(SirketKasasi.format(res.monthly_income_by_currency[pb]) + ' ' + pb);
                    });
                }
                $('#overallTotal').text(SirketKasasi.format(res.monthly_expenses) + ' TL');
            }
        });
    },

    loadTable: function(type, page) {
        const perPage = $(`#${type}PerPageSelect`).val() || 10;
        const search = $(`#${type}SearchInput`).val() || '';
        const container = $(`#${type === 'cash' ? 'cash' : type + 's'}TableBody`);

        $.get('api_islemleri/sirket_kasasi_islemler.php', {
            action: `get_${type === 'cash' ? 'cash_transactions' : type + 's'}`,
            page: page,
            per_page: perPage,
            search: search
        }, function(res) {
            if (res.status === 'success') {
                SirketKasasi.render(type, res.data, container);
                SirketKasasi.pagination(type, res.page, Math.ceil(res.total / res.per_page));
            }
        });
    },

    render: function(type, rows, container) {
        if (!rows || rows.length === 0) {
            container.html('<tr><td colspan="10" class="text-center p-4">Kayıt bulunamadı.</td></tr>');
            return;
        }
        let html = '';
        rows.forEach(r => {
            if (type === 'income') {
                html += `<tr><td>${r.tarih}</td><td>${r.kategori}</td><td>${this.format(r.tutar)}</td><td>${r.para_birimi}</td><td>${r.odeme_tipi}</td><td>${r.musteri_adi || '-'}</td><td>${r.aciklama}</td><td>${r.kaydeden_personel_ismi}</td></tr>`;
            } else if (type === 'expense') {
                html += `<tr><td>${r.tarih}</td><td>${r.kategori}</td><td>${this.format(r.tutar)}</td><td>${r.odeme_tipi}</td><td>${r.fatura_no || '-'}</td><td>${r.aciklama}</td><td>${r.odeme_yapilan_firma}</td><td>${r.kaydeden_personel_ismi}</td></tr>`;
            } else {
                html += `<tr>
                    <td>${r.tarih}</td>
                    <td><span class="badge ${r.islem_tipi.includes('ekle') ? 'badge-success' : 'badge-danger'}">${r.islem_tipi}</span></td>
                    <td>${this.format(r.tutar)}</td><td>${r.para_birimi}</td><td>${r.kaynak_tablo || 'Manuel'}</td><td>${r.aciklama}</td><td>${r.kaydeden_personel}</td>
                    <td class="text-center"><button class="btn btn-danger btn-sm btn-delete-cash" data-id="${r.id}"><i class="fas fa-trash"></i></button></td>
                </tr>`;
            }
        });
        container.html(html);
    },

    pagination: function(type, cur, tot) {
        const cont = $(`#${type === 'cash' ? 'cash' : type + 's'}Pagination`);
        if (tot <= 1) { cont.empty(); return; }
        let h = '';
        for (let i = 1; i <= tot; i++) h += `<li class="page-item ${i===cur?'active':''}"><a class="page-link" href="javascript:SirketKasasi.loadTable('${type}',${i})">${i}</a></li>`;
        cont.html(h);
    },

    add: function() {
        const data = $('#kasaIslemForm').serialize() + '&action=add_cash_transaction';
        $.post('api_islemleri/sirket_kasasi_islemler.php', data, function(res) {
            if (res.status === 'success') {
                $('#kasaIslemModal').modal('hide');
                $('#kasaIslemForm')[0].reset();
                Swal.fire('Başarılı', res.message, 'success');
                SirketKasasi.loadAll();
            } else {
                Swal.fire('Hata', res.message, 'error');
            }
        });
    },

    remove: function(id) {
        Swal.fire({
            title: 'Emin misiniz?',
            text: "Bu işlem kasa bakiyesini tersine etkiler!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Evet, Sil'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('api_islemleri/sirket_kasasi_islemler.php', { action: 'delete_cash_transaction', id: id }, function(res) {
                    if (res.status === 'success') {
                        Swal.fire('Silindi', res.message, 'success');
                        SirketKasasi.loadAll();
                    } else {
                        Swal.fire('Hata', res.message, 'error');
                    }
                });
            }
        });
    },

    format: (v) => parseFloat(v).toLocaleString('tr-TR', { minimumFractionDigits: 2 })
};

$(document).ready(() => SirketKasasi.init());
