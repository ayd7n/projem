<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ürün Bileşen Detayları</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-5">
    <div class="max-w-6xl mx-auto bg-white p-5 rounded-lg shadow-lg">
        <h1 class="text-3xl font-bold text-center text-gray-800 mb-8">Ürün Bileşen Detayları</h1>

        <div class="text-center mb-8">
            <label for="urun_kodu" class="block text-lg font-medium text-gray-700 mb-2">Ürün Kodu:</label>
            <input type="number" id="urun_kodu" placeholder="Ürün kodunu girin" min="1" class="px-4 py-2 text-lg border border-gray-300 rounded w-52">
            <button onclick="getUrunBilesenleri()" class="px-5 py-2 text-lg bg-blue-600 text-white rounded cursor-pointer ml-2 hover:bg-blue-700">Getir</button>
        </div>

        <div id="result" class="mt-5"></div>
    </div>

    <script>
        async function getUrunBilesenleri() {
            const urunKodu = document.getElementById('urun_kodu').value;

            if (!urunKodu) {
                alert('Lütfen bir ürün kodu girin!');
                return;
            }

            const resultDiv = document.getElementById('result');
            resultDiv.innerHTML = '<div class="text-center p-5">Yükleniyor...</div>';

            try {
                const response = await fetch(`urun_siparis_durumu_detay.php?urun_kodu=${urunKodu}`);
                const data = await response.json();

                if (data.error) {
                    resultDiv.innerHTML = `<div class="text-red-600 font-bold">${data.error}</div>`;
                    return;
                }

                displayResult(data);
            } catch (error) {
                resultDiv.innerHTML = `<div class="text-red-600 font-bold">Hata oluştu: ${error.message}</div>`;
            }
        }
        
        function displayResult(data) {
            const resultDiv = document.getElementById('result');

            let html = `
                <div class="bg-green-50 p-4 rounded mb-5">
                    <h3 class="text-xl font-semibold mb-3">Ürün Bilgileri</h3>
                    <p><strong>Ürün Kodu:</strong> ${data.urun_kodu}</p>
                    <p><strong>Ürün İsmi:</strong> ${data.urun_ismi}</p>
                    <p><strong>Stok Miktarı:</strong> ${data.stok_miktari} ${data.birim}</p>
                    <p><strong>Kritik Stok Seviyesi:</strong> ${data.kritik_stok_seviyesi} ${data.birim}</p>
                    <p><strong>Satış Fiyatı:</strong> ${data.satis_fiyati} ₺</p>
                </div>

                <div class="bg-yellow-50 p-4 rounded mb-5">
                    <h3 class="text-xl font-semibold mb-3">Sipariş ve Üretim Bilgileri</h3>
                    <p><strong>Onay Bekleyen Sipariş Miktarı:</strong> ${data.onay_bekleyen_siparis_miktari}</p>
                    <p><strong>Onaylanan Sipariş Miktarı:</strong> ${data.onaylanan_siparis_miktari}</p>
                    <p><strong>Toplam Açık Siparişler:</strong> ${data.toplam_acik_siparisler}</p>
                    <p><strong>Montaj Üretimindeki Miktar:</strong> ${data.montaj_uretimindeki_miktar}</p>
                    <p><strong>Eldeki Hazır Bileşenlerle Üretilebilecek Max Miktar:</strong> ${data.eldeki_hazir_bilesenlerle_uretilebilecek_max_miktar}</p>
                </div>

                <div class="bg-green-100 p-4 rounded mb-5">
                    <h3 class="text-xl font-semibold mb-3">Analiz Bilgileri</h3>
                    <p><strong>Mevcut Eldeki Stok:</strong> ${data.mevcut_eldeki_stok} ${data.birim}</p>
                    <p><strong>İhtiyaç Miktarı:</strong> ${data.ihtiyaç_miktari} ${data.birim}</p>
                    <p><strong>Üretilmesi Gereken Miktar:</strong> ${data.uretilmesi_gereken_miktar} ${data.birim}</p>

                    <h4 class="text-lg font-medium mt-4 mb-2">Hesaplama Aşamaları:</h4>
                    <div class="ml-5">
                        ${Object.values(data.analiz_detaylari).map(adim => `
                            <div class="mb-2 p-2 bg-gray-100 rounded">
                                <strong>${adim.adim}:</strong><br>
                                <em>${adim.aciklama}</em><br>
                                <code>${adim.formul} = ${adim.sonuc}</code>
                            </div>
                        `).join('')}
                    </div>
                </div>

                <h3 class="text-2xl font-semibold mt-6 mb-4">Bileşenler ve Miktarlar</h3>
            `;

            data.bilesenler_ve_miktarlar.forEach(bilesen => {
                html += `
                    <div class="border border-gray-300 p-4 mb-2 rounded bg-gray-50">
                        <p><strong>Bileşen İsmi:</strong> ${bilesen.bilesen_ismi}</p>
                        <p><strong>Bileşen Türü:</strong> ${bilesen.bilesenin_malzeme_turu}</p>
                        <p><strong>Bileşen Miktarı:</strong> ${bilesen.bilesen_miktari} ${data.birim}</p>
                        <p><strong>Stok Miktarı:</strong> ${bilesen.stok_miktari}</p>
                `;

                if (bilesen.bilesenin_malzeme_turu !== 'esans') {
                    html += `<p><strong>Sipariş Verilen Miktar:</strong> ${bilesen.siparis_verilen_miktar}</p>`;
                } else {
                    html += `<p><strong>Üretimdeki Miktar:</strong> ${bilesen.uretimdeki_miktar}</p>`;

                    if (bilesen.esans_bilesenleri && bilesen.esans_bilesenleri.length > 0) {
                        html += `<div class="ml-5 border-l-2 border-blue-600 pl-4"><h4 class="text-lg font-medium mb-2">Esans Bileşenleri:</h4>`;

                        bilesen.esans_bilesenleri.forEach(esans_bilesen => {
                            html += `
                                <div class="mb-2 p-3 bg-blue-50 rounded">
                                    <p><strong>Malzeme İsmi:</strong> ${esans_bilesen.malzeme_ismi}</p>
                                    <p><strong>Malzeme Miktarı:</strong> ${esans_bilesen.malzeme_miktari}</p>
                                    <p><strong>Stok Miktarı:</strong> ${esans_bilesen.stok_miktari}</p>
                                    <p><strong>Sipariş Verilen Miktar:</strong> ${esans_bilesen.siparis_verilen_miktar}</p>
                                </div>
                            `;
                        });

                        html += `</div>`;
                    } else {
                        html += `<p class="italic text-gray-600">Esansın alt bileşeni bulunmamaktadır.</p>`;
                    }
                }

                html += `</div>`;
            });

            resultDiv.innerHTML = html;
        }
        
        // Enter tuşu ile arama yapmak için
        document.getElementById('urun_kodu').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                getUrunBilesenleri();
            }
        });
    </script>
</body>
</html>
