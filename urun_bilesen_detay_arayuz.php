<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MRP Analiz Raporu - Profesyonel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .metric-card {
            transition: all 0.3s;
        }

        .metric-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .timeline-bar {
            height: 24px;
            border-radius: 4px;
            position: relative;
        }

        .critical {
            background: linear-gradient(90deg, #ef4444 0%, #dc2626 100%);
        }

        .normal {
            background: linear-gradient(90deg, #3b82f6 0%, #2563eb 100%);
        }

        .completed {
            background: linear-gradient(90deg, #10b981 0%, #059669 100%);
        }

        @media print {
            .no-print {
                display: none;
            }

            body {
                background: white;
            }
        }
    </style>
</head>

<body class="bg-gray-50">
    <!-- Header -->
    <div class="bg-gradient-to-r from-blue-900 to-blue-700 text-white p-4 no-print">
        <div class="max-w-7xl mx-auto">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">Material Requirements Planning (MRP)</h1>
                    <p class="text-sm text-blue-100">Üretim Planlama ve Malzeme İhtiyaç Analizi</p>
                </div>
                <div class="flex gap-2">
                    <input type="text" id="urun_kodu" placeholder="Ürün Kodu"
                        class="px-3 py-2 rounded text-gray-900 text-sm">
                    <button onclick="analizYap()"
                        class="bg-white text-blue-900 px-4 py-2 rounded font-bold hover:bg-blue-50">
                        Analiz Yap
                    </button>
                    <button onclick="window.print()" class="bg-blue-800 text-white px-4 py-2 rounded hover:bg-blue-900">
                        📄 Yazdır
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Content -->
    <div id="sonuc" class="max-w-7xl mx-auto p-4"></div>

    <script>
        function fmtNum(num) {
            return parseFloat(num).toLocaleString('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        async function analizYap() {
            const urunKodu = document.getElementById('urun_kodu').value;
            if (!urunKodu) { alert('Ürün kodu girin!'); return; }

            const sonucDiv = document.getElementById('sonuc');
            sonucDiv.innerHTML = '<div class="text-center p-20"><div class="text-6xl">⏳</div><div class="mt-4 text-xl">Analiz yapılıyor...</div></div>';

            try {
                const response = await fetch(`urun_siparis_durumu_detay.php?urun_kodu=${urunKodu}`);
                const data = await response.json();
                if (data.error) {
                    sonucDiv.innerHTML = `<div class="bg-red-100 border-l-4 border-red-500 p-4"><strong>Hata:</strong> ${data.error}</div>`;
                    return;
                }
                raporOlustur(data);
            } catch (error) {
                sonucDiv.innerHTML = `<div class="bg-red-100 border-l-4 border-red-500 p-4"><strong>Hata:</strong> ${error.message}</div>`;
            }
        }

        function raporOlustur(data) {
            const tarih = new Date().toLocaleDateString('tr-TR', { year: 'numeric', month: 'long', day: 'numeric' });

            // KPI Hesaplamaları
            const stockTurnover = data.onaylanan_siparis_miktari / (data.stok_miktari || 1);
            const fillRate = ((data.stok_miktari / (data.stok_miktari + data.uretilmesi_gereken_miktar)) * 100).toFixed(1);
            const capacityUtil = ((data.eldeki_hazir_bilesenlerle_uretilebilecek_max_miktar / data.uretilmesi_gereken_miktar) * 100).toFixed(1);
            const stockoutRisk = data.stok_miktari < data.kritik_stok_seviyesi ? 'YÜKSEK' : 'DÜŞÜK';

            let html = `
                <!-- Executive Summary -->
                <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900">${data.urun_ismi}</h2>
                            <p class="text-sm text-gray-600">Ürün Kodu: ${data.urun_kodu} | Rapor Tarihi: ${tarih}</p>
                        </div>
                        <div class="text-right">
                            <div class="text-sm text-gray-600">MRP Analiz Durumu</div>
                            <div class="text-2xl font-bold ${data.uretilmesi_gereken_miktar > 0 ? 'text-orange-600' : 'text-green-600'}">
                                ${data.uretilmesi_gereken_miktar > 0 ? 'AKSİYON GEREKLİ' : 'NORMAL'}
                            </div>
                        </div>
                    </div>

                    <!-- KPI Dashboard -->
                    <div class="grid grid-cols-4 gap-4">
                        <div class="metric-card bg-blue-50 border-l-4 border-blue-600 p-4 rounded">
                            <div class="text-xs text-gray-600 uppercase">Stok Devir Hızı</div>
                            <div class="text-2xl font-bold text-blue-900">${stockTurnover.toFixed(2)}x</div>
                            <div class="text-xs text-gray-500">Sipariş/Stok Oranı</div>
                        </div>
                        <div class="metric-card bg-green-50 border-l-4 border-green-600 p-4 rounded">
                            <div class="text-xs text-gray-600 uppercase">Doluluk Oranı</div>
                            <div class="text-2xl font-bold text-green-900">${fillRate}%</div>
                            <div class="text-xs text-gray-500">Mevcut/Toplam İhtiyaç</div>
                        </div>
                        <div class="metric-card bg-purple-50 border-l-4 border-purple-600 p-4 rounded">
                            <div class="text-xs text-gray-600 uppercase">Kapasite Kullanımı</div>
                            <div class="text-2xl font-bold text-purple-900">${capacityUtil}%</div>
                            <div class="text-xs text-gray-500">Mevcut Malzeme Kapasitesi</div>
                        </div>
                        <div class="metric-card bg-${stockoutRisk === 'YÜKSEK' ? 'red' : 'gray'}-50 border-l-4 border-${stockoutRisk === 'YÜKSEK' ? 'red' : 'gray'}-600 p-4 rounded">
                            <div class="text-xs text-gray-600 uppercase">Stoksuzluk Riski</div>
                            <div class="text-2xl font-bold text-${stockoutRisk === 'YÜKSEK' ? 'red' : 'gray'}-900">${stockoutRisk}</div>
                            <div class="text-xs text-gray-500">Kritik Seviye: ${fmtNum(data.kritik_stok_seviyesi)}</div>
                        </div>
                    </div>
                </div>

                <!-- MPS (Master Production Schedule) -->
                <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                    <h3 class="text-xl font-bold mb-4 border-b-2 border-gray-200 pb-2">
                        📋 Ana Üretim Planı (MPS)
                    </h3>
                    <div class="grid grid-cols-3 gap-6">
                        <div>
                            <div class="text-sm font-semibold text-gray-700 mb-2">TALEP ANALİZİ</div>
                            <table class="w-full text-sm">
                                <tr class="border-b">
                                    <td class="py-2">Onaylı Siparişler</td>
                                    <td class="text-right font-bold">${fmtNum(data.onaylanan_siparis_miktari)}</td>
                                </tr>
                                <tr class="border-b">
                                    <td class="py-2">Bekleyen Siparişler</td>
                                    <td class="text-right font-bold">${fmtNum(data.onay_bekleyen_siparis_miktari)}</td>
                                </tr>
                                <tr class="border-b">
                                    <td class="py-2">Güvenlik Stoğu</td>
                                    <td class="text-right font-bold">${fmtNum(data.kritik_stok_seviyesi)}</td>
                                </tr>
                                <tr class="bg-blue-50 font-bold">
                                    <td class="py-2">Brüt İhtiyaç</td>
                                    <td class="text-right">${fmtNum(parseFloat(data.onaylanan_siparis_miktari) + parseFloat(data.onay_bekleyen_siparis_miktari) + parseFloat(data.kritik_stok_seviyesi))}</td>
                                </tr>
                            </table>
                        </div>
                        <div>
                            <div class="text-sm font-semibold text-gray-700 mb-2">MEVCUT KAYNAKLAR</div>
                            <table class="w-full text-sm">
                                <tr class="border-b">
                                    <td class="py-2">Depodaki Stok</td>
                                    <td class="text-right font-bold">${fmtNum(data.stok_miktari)}</td>
                                </tr>
                                <tr class="border-b">
                                    <td class="py-2">Üretimdeki (WIP)</td>
                                    <td class="text-right font-bold">${fmtNum(data.montaj_uretimindeki_miktar)}</td>
                                </tr>
                                <tr class="border-b">
                                    <td class="py-2">Malzeme Kapasitesi</td>
                                    <td class="text-right font-bold">${fmtNum(data.eldeki_hazir_bilesenlerle_uretilebilecek_max_miktar)}</td>
                                </tr>
                                <tr class="bg-green-50 font-bold">
                                    <td class="py-2">Toplam Mevcut</td>
                                    <td class="text-right">${fmtNum(parseFloat(data.stok_miktari) + parseFloat(data.montaj_uretimindeki_miktar))}</td>
                                </tr>
                            </table>
                        </div>
                        <div>
                            <div class="text-sm font-semibold text-gray-700 mb-2">NET İHTİYAÇ</div>
                            <table class="w-full text-sm">
                                <tr class="border-b">
                                    <td class="py-2">Planlı Üretim</td>
                                    <td class="text-right font-bold text-orange-600">${fmtNum(data.uretilmesi_gereken_miktar)}</td>
                                </tr>
                                <tr class="border-b">
                                    <td class="py-2">Hemen Başlanabilir</td>
                                    <td class="text-right font-bold text-green-600">${fmtNum(Math.min(data.uretilmesi_gereken_miktar, data.eldeki_hazir_bilesenlerle_uretilebilecek_max_miktar))}</td>
                                </tr>
                                <tr class="border-b">
                                    <td class="py-2">Malzeme Bekleyen</td>
                                    <td class="text-right font-bold text-red-600">${fmtNum(Math.max(0, data.uretilmesi_gereken_miktar - data.eldeki_hazir_bilesenlerle_uretilebilecek_max_miktar))}</td>
                                </tr>
                                <tr class="bg-orange-50 font-bold">
                                    <td class="py-2">Darboğaz</td>
                                    <td class="text-right text-red-700">${data.kisitlayan_bilesen}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- BOM Explosion & Capacity Analysis -->
                <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                    <h3 class="text-xl font-bold mb-4 border-b-2 border-gray-200 pb-2">
                        🔧 BOM Patlatma ve Kapasite Analizi
                    </h3>
            `;

            if (data.kapasite_hesaplama_detaylari && data.kapasite_hesaplama_detaylari.length > 0) {
                const minKapasite = Math.min(...data.kapasite_hesaplama_detaylari.map(b => b.max_urun));

                html += `<div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="text-left p-2 border">Bileşen</th>
                                <th class="text-center p-2 border">Tip</th>
                                <th class="text-right p-2 border">Birim İhtiyaç</th>
                                <th class="text-right p-2 border">Mevcut Stok</th>
                                <th class="text-right p-2 border">Üretilebilir</th>
                                <th class="text-right p-2 border">Toplam Kaynak</th>
                                <th class="text-right p-2 border">Max Kapasite</th>
                                <th class="text-center p-2 border">Durum</th>
                            </tr>
                        </thead>
                        <tbody>`;

                data.kapasite_hesaplama_detaylari.forEach(b => {
                    const isDarboğaz = b.max_urun === minKapasite;
                    const rowClass = isDarboğaz ? 'bg-red-50 font-bold' : '';
                    const statusBadge = isDarboğaz ?
                        '<span class="bg-red-600 text-white px-2 py-1 rounded text-xs">DARBOĞAZ</span>' :
                        '<span class="bg-green-600 text-white px-2 py-1 rounded text-xs">YETERLİ</span>';

                    html += `
                        <tr class="${rowClass} border-b hover:bg-gray-50">
                            <td class="p-2">${b.bilesen_ismi}</td>
                            <td class="text-center p-2">
                                <span class="px-2 py-1 rounded text-xs ${b.bilesen_turu === 'esans' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800'}">
                                    ${b.bilesen_turu}
                                </span>
                            </td>
                            <td class="text-right p-2">${fmtNum(b.birim_recete)}</td>
                            <td class="text-right p-2">${fmtNum(b.direkt_stok)}</td>
                            <td class="text-right p-2">${fmtNum(b.uretilebilir_esans || 0)}</td>
                            <td class="text-right p-2 font-semibold">${fmtNum(b.toplam_kaynak)}</td>
                            <td class="text-right p-2 text-lg font-bold ${isDarboğaz ? 'text-red-600' : 'text-green-600'}">
                                ${fmtNum(b.max_urun)}
                            </td>
                            <td class="text-center p-2">${statusBadge}</td>
                        </tr>`;

                    // Alt bileşenler varsa
                    if (b.alt_hesaplamalar && b.alt_hesaplamalar.length > 0) {
                        b.alt_hesaplamalar.forEach(alt => {
                            html += `
                                <tr class="bg-yellow-50 text-xs">
                                    <td class="p-2 pl-8">↳ ${alt.malzeme}</td>
                                    <td class="text-center p-2">hammadde</td>
                                    <td class="text-right p-2">${fmtNum(alt.recete)}</td>
                                    <td class="text-right p-2">${fmtNum(alt.stok)}</td>
                                    <td class="text-right p-2">-</td>
                                    <td class="text-right p-2">-</td>
                                    <td class="text-right p-2">${fmtNum(alt.kapasite)}</td>
                                    <td class="text-center p-2">-</td>
                                </tr>`;
                        });
                    }
                });

                html += `</tbody></table></div>`;
            }

            html += `</div>`;

            // Action Plan
            html += `
                <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                    <h3 class="text-xl font-bold mb-4 border-b-2 border-gray-200 pb-2">
                        ⚡ Aksiyon Planı ve Öncelikler
                    </h3>
                    <div class="space-y-3">
            `;

            let priorityNum = 1;
            const hemenUretilecek = Math.min(data.uretilmesi_gereken_miktar, Math.floor(data.eldeki_hazir_bilesenlerle_uretilebilecek_max_miktar));

            // Esansları ayır - hemen üretilebilecek ve bekleyen
            let hemenEsanslar = [];
            let bekleyenEsanslar = [];
            if (data.kaynak_planlamasi && data.kaynak_planlamasi.uretilmesi_gereken_esanslar) {
                data.kaynak_planlamasi.uretilmesi_gereken_esanslar.forEach(e => {
                    const uretimdeki = e.uretimdeki_miktar || 0;
                    const uretilebilir = e.uretilebilir_miktar || 0;
                    const depodaki = e.depodaki_miktar || 0;
                    const toplamMevcut = depodaki + uretimdeki + uretilebilir;

                    // Hemen üretilebilecek miktar: sadece yeterli hammadde varsa
                    const hemenMiktar = Math.min(e.uretim_emri_miktari, e.hemen_uretilebilecek_miktar || 0);

                    // Yeni düzeltme: e.hemen_uretilebilecek_miktar varsa onu kullan, yoksa eski mantık
                    const hemenUretim = e.hemen_uretilebilecek_miktar !== undefined ?
                        Math.min(e.uretim_emri_miktari, e.hemen_uretilebilecek_miktar) :
                        Math.min(e.uretim_emri_miktari, uretilebilir);

                    if (hemenUretim > 0) {
                        hemenEsanslar.push({
                            ...e,
                            hemen_miktar: hemenUretim,
                            uretimdeki: uretimdeki,
                            depodaki: depodaki,
                            uretilebilir: uretilebilir,
                            toplam_mevcut: toplamMevcut
                        });
                    }

                    // Kalan miktar (hammadde gelince)
                    const toplam_ihtiyac = e.uretim_emri_miktari;
                    const kalan = Math.max(0, toplam_ihtiyac - (depodaki + uretimdeki + uretilebilir));

                    if (kalan > 0) {
                        bekleyenEsanslar.push({
                            ...e,
                            bekleyen_miktar: kalan,
                            uretimdeki: uretimdeki
                        });
                    }
                });
            }

            // Ürünleri ayır
            let hemenUrunler = [];
            let bekleyenUrunler = [];
            if (data.kaynak_planlamasi && data.kaynak_planlamasi.uretilmesi_gereken_urunler) {
                data.kaynak_planlamasi.uretilmesi_gereken_urunler.forEach(u => {
                    // Yeni düzeltme: hemen üretilebilecek miktar varsa onu kullan
                    const hemenUretim = u.hemen_uretilebilecek_miktar !== undefined ?
                        Math.min(u.uretim_emri_miktari, u.hemen_uretilebilecek_miktar) :
                        Math.min(u.uretim_emri_miktari, (u.mevcut_kaynak || 0));

                    if (hemenUretim > 0) {
                        hemenUrunler.push({
                            ...u,
                            hemen_miktar: hemenUretim
                        });
                    }

                    const kalan = Math.max(0, u.uretim_emri_miktari - (u.hemen_uretilebilecek_miktar || 0));
                    if (kalan > 0) {
                        bekleyenUrunler.push({ ...u, bekleyen_miktar: kalan });
                    }
                });
            }

            // Priority 1: Hemen yapılabilir esanslar
            if (hemenEsanslar.length > 0) {
                html += `
                    <div class="border-l-4 border-purple-600 bg-purple-50 p-4 rounded">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-3">
                                <span class="bg-purple-600 text-white w-8 h-8 rounded-full flex items-center justify-center font-bold">P${priorityNum++}</span>
                                <div>
                                    <div class="font-bold text-purple-900">Esans Üretim Emirleri (Kritik - Hemen Başla)</div>
                                    <div class="text-xs text-gray-600">Lead Time: 2-3 gün | Kapasite: Mevcut</div>
                                </div>
                            </div>
                            <span class="bg-red-600 text-white px-3 py-1 rounded text-sm font-bold">KRİTİK YOL</span>
                        </div>
                        <div class="ml-11 space-y-2">`;
                hemenEsanslar.forEach(e => {
                    html += `
                        <div class="bg-white border border-purple-200 rounded p-2 flex justify-between items-center">
                            <div>
                                <span class="font-semibold">${e.esans_ismi}</span>
                                <span class="text-xs text-gray-500 ml-2">WO#${Math.floor(Math.random() * 10000)}</span>
                            </div>
                            <div class="text-right">
                                <div class="font-bold text-purple-700">${fmtNum(e.hemen_miktar)}</div>
                                <div class="text-xs text-gray-500">Hammaddeler Hazır</div>
                            </div>
                        </div>`;
                });
                html += `</div></div>`;
            }

            // Priority 2: Hemen yapılabilir ürün üretimleri
            if (hemenUrunler.length > 0) {
                html += `
                    <div class="border-l-4 border-blue-600 bg-blue-50 p-4 rounded">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-3">
                                <span class="bg-blue-600 text-white w-8 h-8 rounded-full flex items-center justify-center font-bold">P${priorityNum++}</span>
                                <div>
                                    <div class="font-bold text-blue-900">Ürün Üretim İş Emirleri (Malzemeler Hazır)</div>
                                    <div class="text-xs text-gray-600">Lead Time: 3-5 gün | Kapasite: Mevcut</div>
                                </div>
                            </div>
                            <span class="bg-blue-600 text-white px-3 py-1 rounded text-sm font-bold">HAZIR</span>
                        </div>
                        <div class="ml-11 space-y-2">`;
                hemenUrunler.forEach(u => {
                    html += `
                        <div class="bg-white border border-blue-200 rounded p-2 flex justify-between items-center">
                            <div>
                                <span class="font-semibold">${u.urun_ismi}</span>
                                <span class="text-xs text-gray-500 ml-2">PO#${Math.floor(Math.random() * 10000)}</span>
                            </div>
                            <div class="text-right">
                                <div class="font-bold text-blue-700">${fmtNum(u.hemen_miktar)}</div>
                                <div class="text-xs text-gray-500">Bileşenler: Hazır</div>
                            </div>
                        </div>`;
                });
                html += `</div></div>`;
            }

            // Priority 3: Montaj
            if (hemenUretilecek > 0) {
                html += `
                    <div class="border-l-4 border-green-600 bg-green-50 p-4 rounded">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-3">
                                <span class="bg-green-600 text-white w-8 h-8 rounded-full flex items-center justify-center font-bold">P${priorityNum++}</span>
                                <div>
                                    <div class="font-bold text-green-900">Montaj Üretim Emri</div>
                                    <div class="text-xs text-gray-600">Lead Time: 1 gün | Kapasite: ${fmtNum(hemenUretilecek)} adet</div>
                                </div>
                            </div>
                            <span class="bg-green-600 text-white px-3 py-1 rounded text-sm font-bold">HAZIR</span>
                        </div>
                        <div class="ml-11">
                            <div class="bg-white border border-green-200 rounded p-2 flex justify-between items-center">
                                <div>
                                    <span class="font-semibold">${data.urun_ismi}</span>
                                    <span class="text-xs text-gray-500 ml-2">MO#${Math.floor(Math.random() * 10000)}</span>
                                </div>
                                <div class="text-right">
                                    <div class="font-bold text-green-700">${fmtNum(hemenUretilecek)} ${data.birim}</div>
                                    <div class="text-xs text-gray-500">Tüm malzemeler mevcut</div>
                                </div>
                            </div>
                        </div>
                    </div>`;
            }

            // Priority 4: Malzeme satın alma
            if (data.kaynak_planlamasi && data.kaynak_planlamasi.satin_alinmasi_gereken_malzemeler &&
                data.kaynak_planlamasi.satin_alinmasi_gereken_malzemeler.length > 0) {
                html += `
                    <div class="border-l-4 border-blue-600 bg-blue-50 p-4 rounded">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-3">
                                <span class="bg-blue-600 text-white w-8 h-8 rounded-full flex items-center justify-center font-bold">P${priorityNum++}</span>
                                <div>
                                    <div class="font-bold text-blue-900">Satın Alma Talepleri</div>
                                    <div class="text-xs text-gray-600">Lead Time: 5-7 gün | Tedarikçi: Çoklu</div>
                                </div>
                            </div>
                            <span class="bg-orange-600 text-white px-3 py-1 rounded text-sm font-bold">BEKLEMEDE</span>
                        </div>
                        <div class="ml-11 space-y-2">`;
                data.kaynak_planlamasi.satin_alinmasi_gereken_malzemeler.forEach(m => {
                    html += `
                        <div class="bg-white border border-blue-200 rounded p-2 flex justify-between items-center">
                            <div>
                                <span class="font-semibold">${m.malzeme_ismi}</span>
                                <span class="text-xs text-gray-500 ml-2">PR#${Math.floor(Math.random() * 10000)}</span>
                            </div>
                            <div class="text-right">
                                <div class="font-bold text-blue-700">${fmtNum(m.satin_alma_miktari)}</div>
                                <div class="text-xs text-gray-500">Eksik: ${fmtNum(m.toplam_gereken - m.mevcut_kaynak)}</div>
                            </div>
                        </div>`;
                });
                html += `</div></div>`;
            }

            // Priority 5: Bekleyen ürün üretimleri
            if (bekleyenUrunler.length > 0) {
                html += `
                    <div class="border-l-4 border-yellow-600 bg-yellow-50 p-4 rounded">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-3">
                                <span class="bg-yellow-600 text-white w-8 h-8 rounded-full flex items-center justify-center font-bold">P${priorityNum++}</span>
                                <div>
                                    <div class="font-bold text-yellow-900">Ürün Üretimi (Malzeme Gelince)</div>
                                    <div class="text-xs text-gray-600">Bağımlılık: Satın alma tamamlanması</div>
                                </div>
                            </div>
                            <span class="bg-gray-600 text-white px-3 py-1 rounded text-sm font-bold">PLANLANDI</span>
                        </div>
                        <div class="ml-11 space-y-2">`;
                bekleyenUrunler.forEach(u => {
                    html += `
                        <div class="bg-white border border-yellow-200 rounded p-2 flex justify-between items-center">
                            <div>
                                <span class="font-semibold">${u.urun_ismi}</span>
                                <span class="text-xs text-gray-500 ml-2">Beklemede</span>
                            </div>
                            <div class="text-right">
                                <div class="font-bold text-yellow-700">${fmtNum(u.bekleyen_miktar)}</div>
                                <div class="text-xs text-gray-500">Bileşenler: Yolda</div>
                            </div>
                        </div>`;
                });
                html += `</div></div>`;
            }

            // Priority 6: Bekleyen esanslar
            if (bekleyenEsanslar.length > 0) {
                html += `
                    <div class="border-l-4 border-yellow-600 bg-yellow-50 p-4 rounded">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-3">
                                <span class="bg-yellow-600 text-white w-8 h-8 rounded-full flex items-center justify-center font-bold">P${priorityNum++}</span>
                                <div>
                                    <div class="font-bold text-yellow-900">Esans Üretim (Malzeme Gelince)</div>
                                    <div class="text-xs text-gray-600">Bağımlılık: Satın alma tamamlanması</div>
                                </div>
                            </div>
                            <span class="bg-gray-600 text-white px-3 py-1 rounded text-sm font-bold">PLANLANDI</span>
                        </div>
                        <div class="ml-11 space-y-2">`;
                bekleyenEsanslar.forEach(e => {
                    html += `
                        <div class="bg-white border border-yellow-200 rounded p-2 flex justify-between items-center">
                            <div>
                                <span class="font-semibold">${e.esans_ismi}</span>
                                <span class="text-xs text-gray-500 ml-2">Beklemede</span>
                            </div>
                            <div class="text-right">
                                <div class="font-bold text-yellow-700">${fmtNum(e.bekleyen_miktar)}</div>
                                <div class="text-xs text-gray-500">Hammaddeler: Yolda</div>
                            </div>
                        </div>`;
                });
                html += `</div></div>`;
            }

            html += `</div></div>`;

            // Risk & Recommendations
            html += `
                <div class="grid grid-cols-2 gap-6 mb-6">
                    <div class="bg-white rounded-lg shadow-lg p-6">
                        <h3 class="text-lg font-bold mb-3 border-b-2 border-gray-200 pb-2">⚠️ Risk Analizi</h3>
                        <div class="space-y-2 text-sm">
                            ${data.stok_miktari < data.kritik_stok_seviyesi ?
                    '<div class="flex items-start gap-2"><span class="text-red-600">●</span><div><strong>Yüksek Risk:</strong> Stok kritik seviyenin altında</div></div>' :
                    '<div class="flex items-start gap-2"><span class="text-green-600">●</span><div><strong>Düşük Risk:</strong> Stok seviyeleri normal</div></div>'}
                            ${data.uretilmesi_gereken_miktar > data.eldeki_hazir_bilesenlerle_uretilebilecek_max_miktar ?
                    '<div class="flex items-start gap-2"><span class="text-orange-600">●</span><div><strong>Orta Risk:</strong> Malzeme eksikliği var</div></div>' :
                    '<div class="flex items-start gap-2"><span class="text-green-600">●</span><div><strong>Düşük Risk:</strong> Malzemeler yeterli</div></div>'}
                            ${data.onay_bekleyen_siparis_miktari > 0 ?
                    '<div class="flex items-start gap-2"><span class="text-yellow-600">●</span><div><strong>Belirsizlik:</strong> Onay bekleyen siparişler mevcut</div></div>' : ''}
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow-lg p-6">
                        <h3 class="text-lg font-bold mb-3 border-b-2 border-gray-200 pb-2">💡 Öneriler</h3>
                        <div class="space-y-2 text-sm">
                            <div class="flex items-start gap-2"><span class="text-blue-600">▸</span><div>Kritik yol üzerindeki esans üretimlerine öncelik verin</div></div>
                            <div class="flex items-start gap-2"><span class="text-blue-600">▸</span><div>Darboğaz bileşen (${data.kisitlayan_bilesen}) için güvenlik stoğu artırın</div></div>
                            <div class="flex items-start gap-2"><span class="text-blue-600">▸</span><div>Lead time'ları kısaltmak için tedarikçilerle görüşün</div></div>
                            ${capacityUtil < 80 ? '<div class="flex items-start gap-2"><span class="text-blue-600">▸</span><div>Kapasite kullanımı düşük, ek siparişler alınabilir</div></div>' : ''}
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="bg-gray-100 rounded-lg p-4 text-xs text-gray-600 text-center">
                    <p>Bu rapor otomatik olarak MRP sistemi tarafından oluşturulmuştur.</p>
                    <p class="mt-1">Rapor Tarihi: ${tarih} | Versiyon: 2.0 | Hazırlayan: MRP Modülü</p>
                </div>
            `;

            document.getElementById('sonuc').innerHTML = html;
        }

        // Enter tuşu ile arama
        document.getElementById('urun_kodu').addEventListener('keypress', function (e) {
            if (e.key === 'Enter') analizYap();
        });
    </script>
</body>

</html>