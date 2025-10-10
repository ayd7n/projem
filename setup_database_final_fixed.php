<?php
// Create database connection for setup
$connection = new mysqli('localhost', 'root', '');

// Check connection
if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

// Set charset to UTF-8
$connection->set_charset("utf8");

// Create database if it doesn't exist
$create_db = "CREATE DATABASE IF NOT EXISTS parfum_erp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
if ($connection->query($create_db) === TRUE) {
    echo "Database created successfully or already exists\n";
} else {
    echo "Error creating database: " . $connection->error . "\n";
}

// Select the database
$connection->select_db('parfum_erp');

// SQL to create tables
$sql_queries = [
    // 1. Sistem Kullanıcıları Tablosu
    "CREATE TABLE IF NOT EXISTS sistem_kullanicilari (
        kullanici_id INT AUTO_INCREMENT PRIMARY KEY,
        taraf VARCHAR(50) NOT NULL,
        id INT NOT NULL,
        kullanici_adi VARCHAR(100) NOT NULL UNIQUE,
        telefon VARCHAR(20),
        sifre VARCHAR(255) NOT NULL,
        rol VARCHAR(50) NOT NULL,
        aktif BOOLEAN DEFAULT TRUE
    ) ENGINE=InnoDB;",

    // 2. Personeller Tablosu
    "CREATE TABLE IF NOT EXISTS personeller (
        personel_id INT AUTO_INCREMENT PRIMARY KEY,
        ad_soyad VARCHAR(255) NOT NULL,
        tc_kimlik_no VARCHAR(11) NOT NULL UNIQUE,
        dogum_tarihi DATE,
        ise_giris_tarihi DATE,
        pozisyon VARCHAR(100),
        departman VARCHAR(100),
        e_posta VARCHAR(255),
        telefon VARCHAR(20),
        adres TEXT,
        notlar TEXT
    ) ENGINE=InnoDB;",

    // 3. Gider Yönetimi Tablosu
    "CREATE TABLE IF NOT EXISTS gider_yonetimi (
        gider_id INT AUTO_INCREMENT PRIMARY KEY,
        tarih DATE NOT NULL,
        tutar DECIMAL(10, 2) NOT NULL,
        kategori VARCHAR(100) NOT NULL,
        aciklama TEXT,
        kaydeden_personel_id INT,
        kaydeden_personel_ismi VARCHAR(255),
        fatura_no VARCHAR(100),
        odeme_tipi VARCHAR(50)
    ) ENGINE=InnoDB;",

    // 4. Tedarikçiler Tablosu
    "CREATE TABLE IF NOT EXISTS tedarikciler (
        tedarikci_id INT AUTO_INCREMENT PRIMARY KEY,
        tedarikci_adi VARCHAR(255) NOT NULL,
        vergi_no_tc VARCHAR(20),
        adres TEXT,
        telefon VARCHAR(20),
        e_posta VARCHAR(255),
        yetkili_kisi VARCHAR(255),
        aciklama_notlar TEXT
    ) ENGINE=InnoDB;",

    // 5. Lokasyonlar Tablosu
    "CREATE TABLE IF NOT EXISTS lokasyonlar (
        lokasyon_id INT AUTO_INCREMENT PRIMARY KEY,
        depo_ismi VARCHAR(255) NOT NULL,
        raf VARCHAR(100) NOT NULL
    ) ENGINE=InnoDB;",

    // 6. Esanslar Tablosu
    "CREATE TABLE IF NOT EXISTS esanslar (
        esans_id INT AUTO_INCREMENT PRIMARY KEY,
        esans_kodu VARCHAR(50) NOT NULL UNIQUE,
        esans_ismi VARCHAR(255) NOT NULL,
        not_bilgisi TEXT,
        stok_miktari DECIMAL(10, 2) DEFAULT 0,
        birim VARCHAR(50) DEFAULT 'adet',
        demlenme_suresi_gun DECIMAL(5, 2) DEFAULT 0
    ) ENGINE=InnoDB;",

    // 7. Müşteriler Tablosu
    "CREATE TABLE IF NOT EXISTS musteriler (
        musteri_id INT AUTO_INCREMENT PRIMARY KEY,
        musteri_adi VARCHAR(255) NOT NULL,
        vergi_no_tc VARCHAR(20),
        adres TEXT,
        telefon VARCHAR(20),
        e_posta VARCHAR(255),
        sistem_sifresi VARCHAR(255),
        aciklama_notlar TEXT
    ) ENGINE=InnoDB;",

    // 8. Malzemeler Tablosu
    "CREATE TABLE IF NOT EXISTS malzemeler (
        malzeme_kodu INT AUTO_INCREMENT PRIMARY KEY,
        malzeme_ismi VARCHAR(255) NOT NULL,
        malzeme_turu VARCHAR(100) NOT NULL,
        malzeme_foto_ismi VARCHAR(255),
        not_bilgisi TEXT,
        stok_miktari DECIMAL(10, 2) DEFAULT 0,
        birim VARCHAR(50) DEFAULT 'adet',
        termin_suresi INT DEFAULT 0,
        depo VARCHAR(255),
        raf VARCHAR(100),
        kritik_stok_seviyesi INT DEFAULT 0
    ) ENGINE=InnoDB;",

    // 9. Ürün Ağacı Tablosu
    "CREATE TABLE IF NOT EXISTS urun_agaci (
        urun_agaci_id INT AUTO_INCREMENT PRIMARY KEY,
        urun_kodu INT NOT NULL,
        urun_ismi VARCHAR(255) NOT NULL,
        bilesenin_malzeme_turu VARCHAR(100) NOT NULL,
        bilesen_kodu VARCHAR(50) NOT NULL,
        bilesen_ismi VARCHAR(255) NOT NULL,
        bilesen_miktari DECIMAL(10, 2) NOT NULL
    ) ENGINE=InnoDB;",

    // 10. Tanklar Tablosu
    "CREATE TABLE IF NOT EXISTS tanklar (
        tank_id INT AUTO_INCREMENT PRIMARY KEY,
        tank_kodu VARCHAR(50) NOT NULL UNIQUE,
        tank_ismi VARCHAR(255) NOT NULL,
        not_bilgisi TEXT,
        kapasite DECIMAL(10, 2) NOT NULL
    ) ENGINE=InnoDB;",

    // 11. Esans İş Emirleri Tablosu
    "CREATE TABLE IF NOT EXISTS esans_is_emirleri (
        is_emri_numarasi INT AUTO_INCREMENT PRIMARY KEY,
        olusturulma_tarihi DATE DEFAULT (CURRENT_DATE),
        olusturan VARCHAR(255) NOT NULL,
        esans_kodu VARCHAR(50) NOT NULL,
        esans_ismi VARCHAR(255) NOT NULL,
        tank_kodu VARCHAR(50) NOT NULL,
        tank_ismi VARCHAR(255) NOT NULL,
        planlanan_miktar DECIMAL(10, 2) NOT NULL,
        birim VARCHAR(50),
        planlanan_baslangic_tarihi DATE,
        demlenme_suresi_gun INT,
        planlanan_bitis_tarihi DATE,
        aciklama TEXT,
        durum VARCHAR(50) DEFAULT 'olusturuldu',
        tamamlanan_miktar DECIMAL(10, 2) DEFAULT 0,
        eksik_miktar_toplami DECIMAL(10, 2) DEFAULT 0
    ) ENGINE=InnoDB;",

    // 12. Esans İş Emirleri Eksik Miktar Kayıtları
    "CREATE TABLE IF NOT EXISTS esans_is_emirleri_eksik_miktar_kayitlari (
        kayit_id INT AUTO_INCREMENT PRIMARY KEY,
        is_emri_numarasi INT NOT NULL,
        eksik_miktar DECIMAL(10, 2) NOT NULL,
        eksik_kalma_nedeni VARCHAR(50) NOT NULL,
        malzeme_kodu INT,
        malzeme_ismi VARCHAR(255),
        malzeme_turu VARCHAR(100)
    ) ENGINE=InnoDB;",

    // 13. Ürünler Tablosu
    "CREATE TABLE IF NOT EXISTS urunler (
        urun_kodu INT AUTO_INCREMENT PRIMARY KEY,
        urun_ismi VARCHAR(255) NOT NULL,
        urun_foto_ismi VARCHAR(255),
        not_bilgisi TEXT,
        stok_miktari INT DEFAULT 0,
        birim VARCHAR(50) DEFAULT 'adet',
        satis_fiyati DECIMAL(10, 2) NOT NULL,
        kritik_stok_seviyesi INT DEFAULT 0,
        depo VARCHAR(255),
        raf VARCHAR(100)
    ) ENGINE=InnoDB;",

    // 14. Esans İş Emri Malzeme Listesi Tablosu
    "CREATE TABLE IF NOT EXISTS esans_is_emri_malzeme_listesi (
        is_emri_numarasi INT NOT NULL,
        malzeme_kodu INT NOT NULL,
        malzeme_ismi VARCHAR(255) NOT NULL,
        malzeme_turu VARCHAR(100) NOT NULL,
        miktar DECIMAL(10, 2) NOT NULL,
        birim VARCHAR(50) NOT NULL
    ) ENGINE=InnoDB;",

    // 15. Montaj İş Emirleri Tablosu
    "CREATE TABLE IF NOT EXISTS montaj_is_emirleri (
        is_emri_numarasi INT AUTO_INCREMENT PRIMARY KEY,
        olusturulma_tarihi DATE DEFAULT (CURRENT_DATE),
        olusturan VARCHAR(255) NOT NULL,
        urun_kodu VARCHAR(50) NOT NULL,
        urun_ismi VARCHAR(255) NOT NULL,
        planlanan_miktar DECIMAL(10, 2) NOT NULL,
        birim VARCHAR(50),
        planlanan_baslangic_tarihi DATE,
        planlanan_bitis_tarihi DATE,
        aciklama TEXT,
        durum VARCHAR(50) DEFAULT 'olusturuldu',
        tamamlanan_miktar DECIMAL(10, 2) DEFAULT 0,
        eksik_miktar_toplami DECIMAL(10, 2) DEFAULT 0,
        is_merkezi_id INT
    ) ENGINE=InnoDB;",

    // 16. Montaj İş Emirleri Eksik Miktar Kayıtları Tablosu
    "CREATE TABLE IF NOT EXISTS montaj_is_emirleri_eksik_miktar_kayitlari (
        is_emri_numarasi INT NOT NULL,
        eksik_miktar DECIMAL(10, 2) NOT NULL,
        eksik_kalma_nedeni VARCHAR(50) NOT NULL,
        malzeme_kodu INT NOT NULL,
        malzeme_ismi VARCHAR(255) NOT NULL,
        malzeme_turu VARCHAR(100) NOT NULL
    ) ENGINE=InnoDB;",

    // 17. Montaj İş Emri Malzeme Listesi Tablosu
    "CREATE TABLE IF NOT EXISTS montaj_is_emri_malzeme_listesi (
        is_emri_numarasi INT NOT NULL,
        malzeme_kodu INT NOT NULL,
        malzeme_ismi VARCHAR(255) NOT NULL,
        malzeme_turu VARCHAR(100) NOT NULL,
        miktar DECIMAL(10, 2) NOT NULL,
        birim VARCHAR(50) NOT NULL
    ) ENGINE=InnoDB;",

    // 18. Stok Hareket Kayıtları Tablosu
    "CREATE TABLE IF NOT EXISTS stok_hareket_kayitlari (
        hareket_id INT AUTO_INCREMENT PRIMARY KEY,
        tarih DATETIME DEFAULT CURRENT_TIMESTAMP,
        stok_turu VARCHAR(50) NOT NULL,
        kod VARCHAR(50) NOT NULL,
        isim VARCHAR(255) NOT NULL,
        birim VARCHAR(50) NOT NULL,
        miktar DECIMAL(10, 2) NOT NULL,
        yon VARCHAR(10) NOT NULL,
        hareket_turu VARCHAR(50) NOT NULL,
        depo VARCHAR(255),
        raf VARCHAR(100),
        tank_kodu VARCHAR(50),
        ilgili_belge_no VARCHAR(100),
        is_emri_numarasi INT,
        musteri_id INT,
        aciklama TEXT NOT NULL,
        kaydeden_personel_id INT,
        kaydeden_personel_adi VARCHAR(255)
    ) ENGINE=InnoDB;",

    // 19. Çerçeve Sözleşmeler Tablosu
    "CREATE TABLE IF NOT EXISTS cerceve_sozlesmeler (
        sozlesme_id INT AUTO_INCREMENT PRIMARY KEY,
        tedarikci_id INT NOT NULL,
        tedarikci_adi VARCHAR(255) NOT NULL,
        malzeme_kodu INT NOT NULL,
        malzeme_ismi VARCHAR(255) NOT NULL,
        birim_fiyat DECIMAL(10, 2) NOT NULL,
        para_birimi VARCHAR(10) NOT NULL,
        sozlesme_turu VARCHAR(50) NOT NULL,
        toplam_anlasilan_miktar DECIMAL(10, 2) NOT NULL,
        kalan_anlasilan_miktar DECIMAL(10, 2) NOT NULL,
        baslangic_tarihi DATE,
        bitis_tarihi DATE,
        pesin_odeme_yapildi_mi BOOLEAN DEFAULT FALSE,
        toplam_pesin_odeme_tutari DECIMAL(10, 2) DEFAULT 0,
        durum VARCHAR(20) DEFAULT 'aktif',
        olusturan VARCHAR(255) NOT NULL,
        olusturulma_tarihi DATETIME DEFAULT CURRENT_TIMESTAMP,
        aciklama TEXT
    ) ENGINE=InnoDB;",

    // 20. Giriş Kalite Kontrolü Tablosu
    "CREATE TABLE IF NOT EXISTS giris_kalite_kontrolu (
        kontrol_id INT AUTO_INCREMENT PRIMARY KEY,
        tarih DATETIME DEFAULT CURRENT_TIMESTAMP,
        tedarikci_id INT NOT NULL,
        tedarikci_adi VARCHAR(255) NOT NULL,
        malzeme_kodu INT NOT NULL,
        malzeme_ismi VARCHAR(255) NOT NULL,
        birim VARCHAR(50),
        reddedilen_miktar DECIMAL(10, 2) NOT NULL,
        red_nedeni VARCHAR(50) NOT NULL,
        kontrol_eden_personel_id INT,
        kontrol_eden_personel_adi VARCHAR(255),
        ilgili_belge_no VARCHAR(100),
        aciklama TEXT
    ) ENGINE=InnoDB;",

    // 21. Siparişler Tablosu
    "CREATE TABLE IF NOT EXISTS siparisler (
        siparis_id INT AUTO_INCREMENT PRIMARY KEY,
        musteri_id INT NOT NULL,
        musteri_adi VARCHAR(255) NOT NULL,
        tarih DATETIME DEFAULT CURRENT_TIMESTAMP,
        durum VARCHAR(20) DEFAULT 'beklemede',
        toplam_adet INT DEFAULT 0,
        olusturan_musteri VARCHAR(255),
        onaylayan_personel_id INT,
        onaylayan_personel_adi VARCHAR(255),
        onay_tarihi DATETIME,
        aciklama TEXT
    ) ENGINE=InnoDB;",

    // 22. Sipariş Kalemleri Tablosu
    "CREATE TABLE IF NOT EXISTS siparis_kalemleri (
        siparis_id INT NOT NULL,
        urun_kodu INT NOT NULL,
        urun_ismi VARCHAR(255) NOT NULL,
        adet INT NOT NULL,
        birim VARCHAR(50),
        birim_fiyat DECIMAL(10, 2),
        toplam_tutar DECIMAL(10, 2)
    ) ENGINE=InnoDB;",

    // 23. Müşteri Davranış Logu Tablosu
    "CREATE TABLE IF NOT EXISTS musteri_davranis_logu (
        log_id INT AUTO_INCREMENT PRIMARY KEY,
        musteri_id INT NOT NULL,
        musteri_adi VARCHAR(255) NOT NULL,
        giris_tarihi DATETIME DEFAULT CURRENT_TIMESTAMP,
        cikis_tarihi DATETIME,
        siparis_olusturdu_mu BOOLEAN DEFAULT FALSE,
        oturum_suresi_dakika DECIMAL(10, 2)
    ) ENGINE=InnoDB;",

    // 25. Müşteri Geri Bildirimleri Tablosu
    "CREATE TABLE IF NOT EXISTS musteri_geri_bildirimleri (
        geri_bildirim_id INT AUTO_INCREMENT PRIMARY KEY,
        musteri_id INT NOT NULL,
        musteri_adi VARCHAR(255) NOT NULL,
        siparis_id INT,
        urun_kodu INT,
        urun_ismi VARCHAR(255),
        puanlama INT,
        baslik VARCHAR(255),
        aciklama TEXT,
        geri_bildirim_turu VARCHAR(20),
        gizlilik_durumu BOOLEAN DEFAULT FALSE,
        cevaplandi_mi BOOLEAN DEFAULT FALSE,
        cevap_metni TEXT,
        olusturma_tarihi DATETIME DEFAULT CURRENT_TIMESTAMP,
        guncelleme_tarihi DATETIME
    ) ENGINE=InnoDB;",

    // 26. İş Merkezleri Tablosu
    "CREATE TABLE IF NOT EXISTS is_merkezleri (
        is_merkezi_id INT AUTO_INCREMENT PRIMARY KEY,
        isim VARCHAR(255) NOT NULL,
        aciklama TEXT
    ) ENGINE=InnoDB;",

    // 25. Yetki Tabloları Tablosu
    "CREATE TABLE IF NOT EXISTS yetki_tablolari (
        yetki_id INT AUTO_INCREMENT PRIMARY KEY,
        personel_id INT NOT NULL,
        erisim_sayfasi VARCHAR(255) NOT NULL,
        olusturma_tarihi DATETIME DEFAULT CURRENT_TIMESTAMP,
        olusturan_personel_id INT NOT NULL
    ) ENGINE=InnoDB;",

    // Create Views - FIXED VERSION
    "CREATE OR REPLACE VIEW vw_malzemeler_detayli AS 
    SELECT 
        m.malzeme_kodu,
        m.malzeme_ismi,
        m.malzeme_turu,
        m.stok_miktari,
        m.birim,
        m.depo,
        m.raf,
        m.kritik_stok_seviyesi,
        COALESCE(ama.ortalama_aylik_tuketim, 0) AS ortalama_aylik_tuketim,
        COALESCE(st.son_tedarikci_adi, '') AS son_tedarikci_adi
    FROM malzemeler m
    LEFT JOIN (
        SELECT 
            kod AS malzeme_kodu,
            AVG(miktar) AS ortalama_aylik_tuketim
        FROM stok_hareket_kayitlari
        WHERE hareket_turu IN ('uretimde_kullanim', 'cikis') 
            AND tarih >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
            AND stok_turu = 'malzeme'
        GROUP BY kod
    ) ama ON m.malzeme_kodu = ama.malzeme_kodu
    LEFT JOIN (
        SELECT 
            kod AS malzeme_kodu,
            tedarikci_adi AS son_tedarikci_adi
        FROM giris_kalite_kontrolu gk
        WHERE (kod, tarih) IN (
            SELECT kod, MAX(tarih) 
            FROM giris_kalite_kontrolu 
            WHERE stok_turu = 'malzeme'
            GROUP BY kod
        )
    ) st ON m.malzeme_kodu = st.malzeme_kodu;",

    "CREATE OR REPLACE VIEW vw_urunler_master AS 
    SELECT 
        u.urun_kodu,
        u.urun_ismi,
        u.urun_foto_ismi,
        u.depo,
        u.raf,
        u.not_bilgisi,
        u.stok_miktari,
        u.birim,
        u.satis_fiyati,
        u.kritik_stok_seviyesi,
        0 AS birim_maliyet, -- Placeholder, calculated separately
        0 AS fiili_maliyet, -- Placeholder, calculated separately
        0 AS kar_orani, -- Placeholder, calculated separately 
        u.stok_miktari * u.satis_fiyati AS stok_degeri,
        COALESCE(eie.planlanan_miktar, 0) AS esans_is_emri_miktari,
        COALESCE(mie.planlanan_miktar, 0) AS montaj_is_emri_miktari,
        COALESCE(tbs.adet, 0) AS teslimat_bekleyen_musteri_siparisi_miktari,
        tshk.tarih AS son_teslimat_tarihi
    FROM urunler u
    LEFT JOIN (
        SELECT 
            urun_kodu,
            SUM(planlanan_miktar) AS planlanan_miktar
        FROM esans_is_emirleri 
        WHERE durum IN ('olusturuldu', 'basladi')
        GROUP BY urun_kodu
    ) eie ON u.urun_kodu = eie.urun_kodu
    LEFT JOIN (
        SELECT 
            urun_kodu,
            SUM(planlanan_miktar) AS planlanan_miktar
        FROM montaj_is_emirleri 
        WHERE durum IN ('olusturuldu', 'basladi')
        GROUP BY urun_kodu
    ) mie ON u.urun_kodu = mie.urun_kodu
    LEFT JOIN (
        SELECT 
            urun_kodu,
            SUM(adet) AS adet
        FROM siparis_kalemleri sk
        JOIN siparisler s ON sk.siparis_id = s.siparis_id
        WHERE s.durum = 'onaylandi'
        GROUP BY urun_kodu
    ) tbs ON u.urun_kodu = tbs.urun_kodu
    LEFT JOIN (
        SELECT 
            kod AS urun_kodu,
            MAX(tarih) AS tarih
        FROM stok_hareket_kayitlari
        WHERE hareket_turu = 'cikis' AND stok_turu = 'urun'
        GROUP BY kod
    ) tshk ON u.urun_kodu = tshk.urun_kodu;"
];

// Execute each SQL query
foreach ($sql_queries as $sql) {
    if ($connection->query($sql) === TRUE) {
        echo "Query executed successfully: " . substr($sql, 0, 50) . "...\n";
    } else {
        echo "Error executing query: " . $connection->error . "\n";
        echo "Query: " . $sql . "\n";
    }
}

// Insert default admin user
$hashed_password = password_hash('admin123', PASSWORD_DEFAULT);
$default_user_sql = "INSERT IGNORE INTO sistem_kullanicilari (taraf, id, kullanici_adi, telefon, sifre, rol, aktif) 
                     VALUES ('personel', 1, 'admin', '05551234567', '$hashed_password', 'admin', TRUE)";

if ($connection->query($default_user_sql) === TRUE) {
    echo "Default admin user created successfully\n";
} else {
    echo "Error creating default admin user: " . $connection->error . "\n";
}

// Insert default employee
$default_employee_sql = "INSERT IGNORE INTO personeller (ad_soyad, tc_kimlik_no, telefon, e_posta) 
                         VALUES ('Admin User', '12345678900', '05551234567', 'admin@parfum.com')";

if ($connection->query($default_employee_sql) === TRUE) {
    echo "Default employee created successfully\n";
} else {
    echo "Error creating default employee: " . $connection->error . "\n";
}

$connection->close();

echo "\nDatabase setup completed successfully!";
?>