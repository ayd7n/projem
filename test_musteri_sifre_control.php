<!DOCTYPE html>
<html>
<head>
    <title>Test Musteriler Sifre Kontrol</title>
</head>
<body>
    <h2>Müşteri Form Testi</h2>
    <form id="testForm">
        <input type="hidden" id="musteri_id" value=""> <!-- Yeni müşteri için boş -->
        <label>Giriş Yetkisi:</label>
        <input type="checkbox" id="giris_yetkisi" onchange="checkForm()"> <br><br>
        
        <label>Şifre:</label>
        <input type="password" id="sifre" oninput="checkForm()"> <br><br>
        
        <div id="result"></div>
        <button type="button" onclick="simulateSaveCustomer()">SaveCustomer Çağır</button>
    </form>

    <script>
        function checkForm() {
            const girisYetkisi = document.getElementById('giris_yetkisi').checked;
            const sifre = document.getElementById('sifre').value;
            const musteriId = document.getElementById('musteri_id').value;
            
            let action = musteriId ? 'update_customer' : 'add_customer';
            
            let msg = `giris_yetkisi: ${girisYetkisi}, sifre: "${sifre}", musteri_id: "${musteriId}", action: ${action}<br>`;
            
            if (action === 'add_customer') {
                if (girisYetkisi && (!sifre || sifre.trim() === '')) {
                    msg += "Durum: Yeni müşteri için giriş yetkisi açık ama şifre boş - KAYIT ENGELLENECEK";
                } else {
                    msg += "Durum: Yeni müşteri için uygun - KAYIT İZİN VERİLECEK";
                }
            } else { // update
                msg += "Durum: Güncelleme işlemi - şifre boş olabilir";
            }
            
            document.getElementById('result').innerHTML = msg;
        }
        
        function simulateSaveCustomer() {
            const girisYetkisi = document.getElementById('giris_yetkisi').checked;
            const sifre = document.getElementById('sifre').value;
            const musteriId = document.getElementById('musteri_id').value;
            
            let action = musteriId ? 'update_customer' : 'add_customer';
            
            if (action === 'add_customer') {
                if (girisYetkisi && (!sifre || sifre.trim() === '')) {
                    alert('Yeni müşteri için sistemde giriş yetkisi verildiğinde şifre zorunludur.');
                    return false;
                }
            }
            
            alert('Form gönderilecek! action: ' + action);
            return true;
        }
        
        // Initialize
        checkForm();
    </script>
</body>
</html>