<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Kullanım Rehberi - IDO KOZMETIK</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #4a0e63;
            --secondary: #7c2a99;
            --accent: #d4af37;
            --bg-color: #f8f9fa;
            --text-color: #2d3436;
            --sidebar-width: 280px;
        }

        body {
            font-family: 'Ubuntu', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
            margin: 0;
            padding-top: 70px;
            /* Navbar height */
        }

        /* Top Navbar */
        .top-bar-wrapper {
            background: linear-gradient(45deg, var(--primary), var(--secondary));
            height: 70px;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            padding: 0 2rem;
        }

        .brand {
            color: var(--accent);
            font-size: 1.5rem;
            font-weight: 700;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .nav-right {
            margin-left: auto;
        }

        .btn-home {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            padding: 8px 20px;
            border-radius: 20px;
            text-decoration: none;
            transition: all 0.3s;
            font-weight: 500;
        }

        .btn-home:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }

        /* Layout */
        .container {
            display: flex;
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
            gap: 2rem;
        }

        @media (max-width: 768px) {
            .container {
                padding: 0;
            }
        }

        /* Sidebar Navigation */
        .sidebar {
            width: var(--sidebar-width);
            flex-shrink: 0;
            position: sticky;
            top: 90px;
            height: calc(100vh - 110px);
            overflow-y: auto;
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        .nav-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .nav-item {
            margin-bottom: 0.5rem;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            color: #636e72;
            text-decoration: none;
            border-radius: 10px;
            transition: all 0.2s;
            font-weight: 500;
        }

        .nav-link i {
            width: 25px;
            color: var(--secondary);
        }

        .nav-link:hover,
        .nav-link.active {
            background: rgba(124, 42, 153, 0.1);
            color: var(--primary);
        }

        .nav-link.active {
            font-weight: 700;
        }

        /* Main Content */
        .content {
            flex: 1;
            min-width: 0;
            /* Prevent overflow */
        }

        .section-card {
            background: white;
            border-radius: 15px;
            padding: 2.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            scroll-margin-top: 100px;
        }

        .section-title {
            color: var(--primary);
            border-bottom: 2px solid rgba(124, 42, 153, 0.1);
            padding-bottom: 1rem;
            margin-bottom: 1.5rem;
            font-size: 1.8rem;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        h3 {
            color: var(--secondary);
            margin-top: 2rem;
            margin-bottom: 1rem;
            font-size: 1.3rem;
        }

        h4 {
            color: #2d3436;
            margin-top: 1.5rem;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        p {
            line-height: 1.7;
            color: #636e72;
            margin-bottom: 1rem;
        }

        ul,
        ol {
            color: #636e72;
            line-height: 1.7;
            padding-left: 1.5rem;
        }

        li {
            margin-bottom: 0.5rem;
        }

        /* Info Boxes */
        .info-box {
            background: rgba(74, 14, 99, 0.05);
            border-left: 4px solid var(--primary);
            padding: 1.5rem;
            border-radius: 0 10px 10px 0;
            margin: 1.5rem 0;
        }

        .warning-box {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 1.5rem;
            border-radius: 0 10px 10px 0;
            margin: 1.5rem 0;
            color: #856404;
        }

        /* Tables */
        .table-responsive {
            overflow-x: auto;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin: 1rem 0;
        }

        .table th,
        .table td {
            padding: 12px;
            border: 1px solid #e0e0e0;
            text-align: left;
        }

        .table th {
            background: #f8f9fa;
            color: var(--primary);
            font-weight: 600;
        }

        /* Badges */
        .badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .bg-success {
            background-color: #d4edda;
            color: #155724;
        }

        .bg-warning {
            background-color: #fff3cd;
            color: #856404;
        }

        .bg-danger {
            background-color: #f8d7da;
            color: #721c24;
        }

        .bg-info {
            background-color: #d1ecf1;
            color: #0c5460;
        }

        .bg-secondary {
            background-color: #e2e3e5;
            color: #383d41;
        }

        /* FAQ Accordion */
        .faq-item {
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            margin-bottom: 1rem;
            overflow: hidden;
        }

        .faq-question {
            padding: 1.2rem;
            background: #f8f9fa;
            cursor: pointer;
            font-weight: 600;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: background 0.2s;
        }

        .faq-question:hover {
            background: #e9ecef;
        }

        .faq-answer {
            padding: 0 1.2rem;
            max-height: 0;
            overflow: hidden;
            transition: all 0.3s ease-out;
            background: white;
        }

        .faq-answer.open {
            padding: 1.2rem;
            max-height: 500px;
            border-top: 1px solid #e0e0e0;
        }

        /* Steps */
        .step-list {
            counter-reset: step;
            list-style: none;
            padding: 0;
        }

        .step-item {
            position: relative;
            padding-left: 50px;
            margin-bottom: 1.5rem;
        }

        .step-item::before {
            counter-increment: step;
            content: counter(step);
            position: absolute;
            left: 0;
            top: 0;
            width: 35px;
            height: 35px;
            background: var(--primary);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
        }

        .step-title {
            font-weight: 700;
            color: var(--text-color);
            display: block;
            margin-bottom: 0.3rem;
        }

        @media (max-width: 992px) {
            .container {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                height: auto;
                position: static;
                margin-bottom: 1rem;
            }
        }
    </style>
</head>

<body>

    <header class="top-bar-wrapper">
        <a href="navigation.php" class="brand">
            <i class="fas fa-spa"></i> IDO KOZMETIK
        </a>
        <div class="nav-right">
            <a href="navigation.php" class="btn-home">
                <i class="fas fa-home"></i> Ana Sayfa
            </a>
        </div>
    </header>

    <div class="container">
        <aside class="sidebar">
            <ul class="nav-menu">
                <li class="nav-item"><a href="#giris" class="nav-link active"><i class="fas fa-star"></i> Genel
                        Bakış</a></li>
                <li class="nav-item"><a href="#yonetim" class="nav-link"><i class="fas fa-user-shield"></i> Sistem
                        Yönetimi</a></li>
                <li class="nav-item"><a href="#crm" class="nav-link"><i class="fas fa-users"></i> İlişkiler (CRM)</a>
                </li>
                <li class="nav-item"><a href="#musteri-paneli" class="nav-link"><i class="fas fa-user-tag"></i> Müşteri
                        Paneli</a></li>
                <li class="nav-item"><a href="#stok" class="nav-link"><i class="fas fa-boxes"></i> Stok & Envanter</a>
                </li>
                <li class="nav-item"><a href="#lokasyon" class="nav-link"><i class="fas fa-map-marker-alt"></i>
                        Lokasyonlar</a></li>
                <li class="nav-item"><a href="#urun-agaci" class="nav-link"><i class="fas fa-project-diagram"></i> Ürün
                        Ağaçları</a></li>
                <li class="nav-item"><a href="#satis" class="nav-link"><i class="fas fa-shopping-cart"></i> Satış &
                        Sipariş</a></li>
                <li class="nav-item"><a href="#uretim" class="nav-link"><i class="fas fa-industry"></i> Üretim
                        İşleyişi</a></li>
                <li class="nav-item"><a href="#is-merkezleri" class="nav-link"><i class="fas fa-industry"></i> İş
                        Merkezleri</a></li>
                <li class="nav-item"><a href="#stok-operasyon" class="nav-link"><i class="fas fa-exchange-alt"></i>
                        Manuel Stok Operasyonları</a></li>
                <li class="nav-item"><a href="#stok-hareketleri" class="nav-link"><i class="fas fa-exchange-alt"></i>
                        Stok Hareketleri</a></li>
                <li class="nav-item"><a href="#finans" class="nav-link"><i class="fas fa-wallet"></i> Finans &
                        Maliyet</a></li>
                <li class="nav-item"><a href="#gider" class="nav-link"><i class="fas fa-money-bill-wave"></i> Gider
                        Yönetimi</a></li>
                <li class="nav-item"><a href="#loglar" class="nav-link"><i class="fas fa-clipboard-list"></i> Sistem Günlükleri</a></li>
                <li class="nav-item"><a href="#cerceve-sozlesmeler" class="nav-link"><i
                            class="fas fa-file-contract"></i> Çerçeve Sözleşmeler</a></li>
                <li class="nav-item"><a href="#raporlama" class="nav-link"><i class="fas fa-chart-bar"></i>
                        Raporlama</a></li>
                <li class="nav-item"><a href="#raporlar" class="nav-link"><i class="fas fa-chart-pie"></i> Raporlar</a>
                </li>
                <li class="nav-item"><a href="#siparis-takibi" class="nav-link"><i class="fas fa-clipboard-list"></i>
                        Sipariş Takibi</a></li>
                <li class="nav-item"><a href="#malzeme-siparisleri" class="nav-link"><i class="fas fa-box-open"></i>
                        Malzeme Siparişleri</a></li>
                <li class="nav-item"><a href="#urun-akisi" class="nav-link"><i class="fas fa-project-diagram"></i> Ürün
                        Akışı</a></li>
                <li class="nav-item"><a href="#api" class="nav-link"><i class="fas fa-code"></i> API ve Entegrasyonlar</a></li>
                <li class="nav-item"><a href="#komut-satiri" class="nav-link"><i class="fas fa-terminal"></i> Komut Satırı Erişimi</a></li>
                <li class="nav-item"><a href="#yedekleme" class="nav-link"><i class="fas fa-database"></i> Yedekleme ve Kurtarma</a></li>
                <li class="nav-item"><a href="#sss" class="nav-link"><i class="fas fa-question-circle"></i> SSS</a></li>
            </ul>
        </aside>

        <main class="content">
            <!-- Giriş -->
            <section id="giris" class="section-card">
                <h2 class="section-title"><i class="fas fa-star"></i> Sisteme Genel Bakış</h2>
                <p>IDO KOZMETIK Kurumsal Kaynak Planlama (ERP) sistemi, hammadde tedariğinden nihai ürün satışına kadar
                    olan tüm süreci entegre bir şekilde yönetmenizi sağlar.</p>

                <div class="info-box">
                    <strong><i class="fas fa-info-circle"></i> Temel Prensip:</strong>
                    Sistem "Entegre Stok Yönetimi" prensibiyle çalışır. Bir üretim emri verildiğinde hammaddeler stoktan
                    otomatik düşer, üretim bittiğinde ise nihai ürün stoğa otomatik eklenir. Manuel müdahale minimuma
                    indirilmiştir.
                </div>
            </section>

            <!-- Sistem Yönetimi -->
            <section id="yonetim" class="section-card">
                <h2 class="section-title"><i class="fas fa-user-shield"></i> Sistem Yönetimi</h2>
                <p>Bu bölüm, sistemin genel ayarlarının, kullanıcı yetkilerinin ve veri güvenliğinin yönetildiği
                    alandır.</p>

                <h3>1. Personel ve Yetki Yönetimi</h3>
                <p>Sisteme erişecek her kullanıcı için bir personel kaydı oluşturulmalıdır.</p>
                <ul>
                    <li><strong>Personel Ekleme:</strong> Ad, Soyad, Pozisyon, Departman ve iletişim bilgileri girilir.
                    </li>
                    <li><strong>Şifre İşlemleri:</strong> Personel eklerken veya düzenlerken "Şifre" alanından
                        personelin sisteme giriş şifresi belirlenir. Boş bırakılırsa mevcut şifre değişmez.</li>
                    <li><strong>Yetkilendirme:</strong> Her personelin erişebileceği sayfalar ve yapabileceği işlemler
                        (Ekleme, Silme, Düzenleme) detaylı olarak "Yetki Yönetimi" ekranından ayarlanır.</li>
                    <li><strong>Admin Kullanıcısı:</strong> Sistemde silinemeyen ve tüm yetkilere sahip özel bir "Admin"
                        hesabı bulunur.</li>
                </ul>

                <h3>2. Sistem Ayarları</h3>
                <ul>
                    <li><strong>Bakım Modu:</strong> Sistemde güncelleme yapılırken "Bakım Modu" açılabilir. Bu modda
                        sadece yöneticiler sisteme giriş yapabilir.</li>
                    <li><strong>Döviz Kurları:</strong> Maliyet hesaplamalarında kullanılmak üzere USD ve EUR kurları
                        buradan güncellenir. Kurlar manuel girilebilir veya "Otomatik Çek" butonu ile güncel piyasa
                        verileri alınabilir.</li>
                    <li><strong>Yedekleme:</strong> Veri kaybını önlemek için veritabanı yedekleri alınabilir. Yedekler
                        bilgisayara indirilebilir veya sistemden geri yüklenebilir.</li>
                </ul>

                <h3>3. Sistem Gereksinimleri ve Kurulum</h3>
                <p>Sistemin düzgün çalışması için aşağıdaki PHP eklentileri gereklidir:</p>
                <ul>
                    <li><strong>php-curl:</strong> Telegram bildirimleri için</li>
                    <li><strong>php-mbstring:</strong> Excel dışa aktarımı için</li>
                    <li><strong>php-mysql:</strong> Veritabanı bağlantısı için</li>
                </ul>
                <p>Bu eklentileri kurmak için aşağıdaki komutları kullanabilirsiniz:</p>
                <pre><code>sudo apt-get update
sudo apt-get install -y php-curl php-mbstring</code></pre>
                <p>Kurulumdan sonra PHP servisini yeniden başlatmanız gerekir:</p>
                <pre><code>sudo systemctl restart php8.1-fpm</code></pre>
            </section>

            <!-- CRM -->
            <section id="crm" class="section-card">
                <h2 class="section-title"><i class="fas fa-users"></i> İlişkiler ve Sözleşme Yönetimi</h2>

                <h3>1. Müşteriler</h3>
                <p>Ürün satışı yapılan firmalar veya şahıslardır. Sipariş oluşturabilmek için önce müşteri kaydı
                    yapılmalıdır.</p>

                <h3>2. Tedarikçiler</h3>
                <p>Hammadde veya ambalaj malzemesi satın alınan firmalardır. "Mal Kabul" işlemi yapabilmek için
                    tedarikçi ile aktif bir sözleşme (veya sistemde tanım) olması gerekir.</p>

                <h3>3. Çerçeve Sözleşmeler</h3>
                <p>Tedarikçilerle yapılan malzeme alım anlaşmalarının yönetildiği alandır.</p>
                <ul>
                    <li><strong>Sözleşme Tanımlama:</strong> Hangi tedarikçiden, hangi malzemenin, hangi fiyattan ve ne
                        kadar (limit) alınacağı belirlenir.</li>
                    <li><strong>Takip:</strong> "Ödenen Miktar" ve "Mal Kabul Miktarı" takip edilerek sözleşmenin
                        doluluk oranı izlenir.</li>
                    <li><strong>Geçerlilik:</strong> Sözleşmelerin başlangıç ve bitiş tarihleri vardır. Süresi dolan
                        veya limiti biten sözleşmeler otomatik olarak pasif duruma düşer.</li>
                </ul>

                <h3>4. Kullanıcı Kimlik Doğrulama ve Oturum Yönetimi</h3>
                <p>Sistemde iki farklı kullanıcı türü vardır:</p>
                <ul>
                    <li><strong>Personel:</strong> Şirket personeli, tam yetkili kullanıcılar</li>
                    <li><strong>Müşteri:</strong> Harici kullanıcılar, sınırlı erişim</li>
                </ul>

                <h4>Giriş Süreci:</h4>
                <p>Kullanıcılar e-posta adresi veya telefon numarası ile şifrelerini kullanarak sisteme giriş yapabilir.</p>
                <ul>
                    <li><strong>Personel Girişi:</strong> admin@parfum.com ve admin2@parfum.com hesapları özel yönetici ayrıcalıklarına sahiptir</li>
                    <li><strong>Müşteri Girişi:</strong> Giriş yetkisi olan müşteriler sistemdeki ürünleri görebilir ve sipariş verebilir</li>
                </ul>

                <div class="info-box">
                    <strong><i class="fas fa-shield-alt"></i> Güvenlik Notu:</strong> Şifreler veritabanında hashlenmiş (şifrelenmiş) olarak saklanır. Sistemde oturum açan kullanıcılar için oturum yönetimi yapılır.
                </div>
            </section>

            <!-- Müşteri Paneli ve Yetkilendirme -->
            <section id="musteri-paneli" class="section-card">
                <h2 class="section-title"><i class="fas fa-user-tag"></i> Müşteri Paneli ve Yetkilendirme</h2>
                <p>Sistem, sadece şirket personeli için değil, müşterileriniz için de özel bir sipariş paneli sunar.
                    Müşterileriniz kendi hesaplarına giriş yaparak sipariş verebilirler.</p>

                <h3>1. Müşteriye Giriş Yetkisi Verme</h3>
                <p>Bir müşterinin sisteme girebilmesi için şu adımları izleyin:</p>
                <ol>
                    <li>"Müşteriler" sayfasına gidin.</li>
                    <li>Yeni müşteri eklerken veya mevcut bir müşteriyi düzenlerken <strong>"Sisteme Giriş
                            Yetkisi"</strong> kutucuğunu işaretleyin.</li>
                    <li>Açılan <strong>"Şifre"</strong> alanına müşterinin kullanacağı şifreyi girin.</li>
                    <li>Kaydedin.</li>
                </ol>
                <div class="info-box">
                    <strong><i class="fas fa-key"></i> Giriş Bilgileri:</strong><br>
                    Müşteri, sisteme giriş yaparken <strong>Kullanıcı Adı</strong> olarak kayıtlı <u>E-posta
                        Adresini</u> veya <u>Telefon Numarasını</u>, <strong>Şifre</strong> olarak ise sizin
                    belirlediğiniz şifreyi kullanacaktır.
                </div>

                <h3>2. Müşteri Paneli Özellikleri</h3>
                <p>Giriş yapan bir müşteri şunları yapabilir:</p>
                <ul>
                    <li><strong>Ürün Kataloğu:</strong> Stokta bulunan ürünleri görüntüleyebilir ve arama yapabilir.
                    </li>
                    <li><strong>Sepet İşlemleri:</strong> Ürünleri sepete ekleyip sipariş oluşturabilir.</li>
                    <li><strong>Sipariş Takibi:</strong> Geçmiş siparişlerinin durumunu (Beklemede, Onaylandı,
                        Tamamlandı) takip edebilir.</li>
                    <li><strong>Profil:</strong> Kendi şifresini değiştirebilir.</li>
                </ul>

                <h3>3. Müşteri Paneli API İşlemleri</h3>
                <p>Müşteri paneli, arka planda birkaç farklı API endpoint'ini kullanır:</p>
                <ul>
                    <li><strong>Cart Operations:</strong> Sepet işlemleri (ürün ekleme, kaldırma, içerik alma)</li>
                    <li><strong>Order Operations:</strong> Sipariş oluşturma işlemleri</li>
                    <li><strong>Search Products:</strong> Ürün arama ve filtreleme</li>
                    <li><strong>Get Cart Contents:</strong> Sepetteki ürünleri listeleme</li>
                </ul>
                <p>Bu API'ler JSON formatında çalışır ve sadece yetkili müşteriler erişebilir. Tüm işlemler müşteri oturumu üzerinden doğrulanır.</p>
            </section>

            <!-- Stok -->
            <section id="stok" class="section-card">
                <h2 class="section-title"><i class="fas fa-boxes"></i> Stok & Envanter Yönetimi</h2>
                <p>Sistemde üç ana stok kalemi bulunur:</p>

                <div class="row">
                    <div class="col-md-4">
                        <h4><i class="fas fa-vial"></i> Malzemeler</h4>
                        <p>Üretimde kullanılan girdilerdir. (Örn: Alkol, Esans Yağı, Şişe, Kapak, Etiket).</p>
                    </div>
                    <div class="col-md-4">
                        <h4><i class="fas fa-flask"></i> Esanslar</h4>
                        <p>Malzemelerin karışımıyla elde edilen yarı mamullerdir. Tanklarda saklanır.</p>
                    </div>
                    <div class="col-md-4">
                        <h4><i class="fas fa-spray-can"></i> Ürünler</h4>
                        <p>Esansın şişelenip paketlenmiş, satışa hazır halidir.</p>
                    </div>
                </div>
            </section>

            <!-- Lokasyonlar -->
            <section id="lokasyon" class="section-card">
                <h2 class="section-title"><i class="fas fa-map-marker-alt"></i> Lokasyonlar ve Depolama Yönetimi</h2>
                <p>Sistemdeki tüm stok kalemleri bir lokasyonda (Depo, Raf veya Tank) bulunmak zorundadır. Bu sayede
                    stokların nerede bulundukları ve ihtiyaç duyulduğunda kolayca bulunabilmesi sağlanır.</p>

                <h3>1. Lokasyon Tanımlama</h3>
                <p>Sistemde farklı tipte lokasyonlar tanımlanabilir:</p>
                <ul>
                    <li><strong>Depolar:</strong> Genel depolama alanlarıdır.</li>
                    <li><strong>Raflar:</strong> Depolar içinde daha küçük birimlerdir.</li>
                    <li><strong>Tanklar:</strong> Esans gibi sıvı ürünlerin saklandığı özel lokasyonlardır.</li>
                </ul>

                <h3>2. Lokasyon Takibi</h3>
                <div class="warning-box">
                    <strong><i class="fas fa-map-marker-alt"></i> Lokasyon Takibi:</strong>
                    Her stok kalemi bir lokasyonda (Depo, Raf veya Tank) bulunmalıdır. Transfer işlemleri ile ürünlerin
                    yerini değiştirebilirsiniz.
                </div>
                <p>Stok takibi yapılırken her ürünün bulunduğu fiziksel konum bilgisi önemlidir. Bu sayede:</p>
                <ul>
                    <li>Stok sayımı sırasında ürünler kolayca bulunabilir</li>
                    <li>Sevkiyat için doğru lokasyondan stok düşümü yapılabilir</li>
                    <li>Transfer işlemleri ile ürünleri farklı lokasyonlara taşıyabilirsiniz</li>
                </ul>

                <h3>3. Transfer İşlemleri</h3>
                <p>Farklı lokasyonlar arasında stok taşımak için "Manuel Stok Operasyonları" bölümünden transfer işlemi
                    yapılmalıdır:</p>
                <ul>
                    <li><strong>Kaynak Lokasyon:</strong> Stokların alınacağı başlangıç noktası</li>
                    <li><strong>Hedef Lokasyon:</strong> Stokların taşınacağı yeni konum</li>
                    <li><strong>Transfer Miktarı:</strong> Taşınacak stok adedi</li>
                </ul>
            </section>

            <!-- Ürün Ağaçları -->
            <section id="urun-agaci" class="section-card">
                <h2 class="section-title"><i class="fas fa-project-diagram"></i> Ürün Ağaçları (Reçeteler)</h2>
                <p>Bir ürünün veya esansın üretilmesi için gereken "Formül"dür. Maliyet hesaplaması ve stok düşüşleri bu
                    reçeteye göre yapılır.</p>

                <h3>Temel Kavramlar</h3>
                <ul>
                    <li><strong>Esans Reçetesi:</strong> Sadece hammaddelerden (Yağlar, Alkol vb.) oluşur. Çıktısı
                        "Esans"tır.</li>
                    <li><strong>Ürün Reçetesi:</strong> Mutlaka <u>1 adet Esans</u> ve <u>Ambalaj Malzemelerinden</u>
                        (Şişe, Kapak, Kutu) oluşur. Çıktısı "Nihai Ürün"dür.</li>
                </ul>

                <div class="info-box">
                    <strong><i class="fas fa-lightbulb"></i> İpucu:</strong>
                    Üretim emri vermeden önce ilgili ürünün reçetesinin tanımlı olduğundan emin olun. Reçetesiz üretim
                    yapılamaz.
                </div>
            </section>

            <!-- Satış & Sipariş -->
            <section id="satis" class="section-card">
                <h2 class="section-title"><i class="fas fa-shopping-cart"></i> Satış ve Sipariş Yönetimi</h2>
                <p>Müşterilerden gelen taleplerin yönetildiği modüldür. Bir siparişin yaşam döngüsü şöyledir:</p>

                <div class="step-list">
                    <div class="step-item">
                        <span class="step-title">1. Beklemede (Oluşturma)</span>
                        Sipariş girilir. Henüz stoktan düşüş yapılmaz. Yönetici onayı beklenir.
                    </div>
                    <div class="step-item">
                        <span class="step-title">2. Onaylandı</span>
                        Yönetici siparişi onaylar. Üretim veya hazırlık süreci başlar. Stok hala düşmemiştir.
                    </div>
                    <div class="step-item">
                        <span class="step-title">3. Tamamlandı (Sevkiyat)</span>
                        Ürünler müşteriye sevk edildiğinde durum "Tamamlandı"ya çekilir.
                        <br><span class="badge bg-danger">Stok Etkisi:</span> Siparişteki ürün adedi stoktan
                        <strong>otomatik olarak düşer</strong>.
                    </div>
                    <div class="step-item">
                        <span class="step-title">4. İptal / İade</span>
                        Tamamlanmış bir sipariş iptal edilirse, düşülen stoklar otomatik olarak depoya geri eklenir.
                    </div>
                </div>
            </section>

            <!-- Üretim İşleyişi -->
            <section id="uretim" class="section-card">
                <h2 class="section-title"><i class="fas fa-industry"></i> Üretim İşleyişi</h2>
                <p>Üretim süreci iki ana aşamadan oluşur: Esans Üretimi ve Montaj/Dolum.</p>

                <h3>1. Tank Yönetimi</h3>
                <p>Esansların üretildiği ve saklandığı tankların tanımlandığı bölümdür.</p>
                <ul>
                    <li><strong>Tank Tanımlama:</strong> Her tankın bir kodu (Örn: TANK001), ismi ve litre cinsinden
                        kapasitesi vardır.</li>
                    <li><strong>Kullanım:</strong> Esans iş emirleri oluşturulurken, üretilecek esansın hangi tankta
                        demleneceği seçilir.</li>
                </ul>

                <h3>2. Esans Üretimi (İş Emirleri)</h3>
                <p>Hammaddelerin karıştırılarak esans elde edilmesi sürecidir.</p>
                <ol>
                    <li><strong>Planlama:</strong> Üretilecek Esans ve Miktar seçilir.</li>
                    <li><strong>Tank Seçimi:</strong> Esansın demleneceği boş tank seçilir.</li>
                    <li><strong>Başlatma (Üretimde):</strong> Hammaddeler (Yağlar vb.) stoktan düşer.</li>
                    <li><strong>Tamamlama:</strong> Üretilen sıvı esans, seçilen tankın stoğuna eklenir.</li>
                </ol>

                <h3>3. Aşama: Montaj (Dolum) Üretimi</h3>
                <p>Hazır esansın şişelenip nihai ürüne dönüştürülmesidir.</p>
                <ol>
                    <li><strong>Planlama:</strong> Üretilecek Parfüm ve Adet seçilir.</li>
                    <li><strong>Kaynak Seçimi:</strong> Esansın çekileceği Tank ve üretimin yapılacağı İş Merkezi
                        seçilir.</li>
                    <li><strong>Başlatma (Üretimde):</strong> Esans (Tanktan) ve Ambalaj Malzemeleri (Depodan) stoktan
                        düşer.</li>
                    <li><strong>Tamamlama:</strong> Nihai ürün (Parfüm) ana stoğa eklenir.</li>
                </ol>

                <h4 class="mt-4">İş Emri Durum Tablosu</h4>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Durum</th>
                                <th>Anlamı</th>
                                <th>Stok Hareketi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><span class="badge bg-secondary">Oluşturuldu</span></td>
                                <td>Planlama aşaması.</td>
                                <td>Hareket Yok</td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-warning text-dark">Üretimde</span></td>
                                <td>Üretim başladı.</td>
                                <td><span class="text-danger"><i class="fas fa-arrow-down"></i> Girdiler Stoktan
                                        Düşer</span></td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-success">Tamamlandı</span></td>
                                <td>Üretim bitti.</td>
                                <td><span class="text-success"><i class="fas fa-arrow-up"></i> Çıktı Ürünü Stoğa
                                        Girer</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- İş Merkezleri -->
            <section id="is-merkezleri" class="section-card">
                <h2 class="section-title"><i class="fas fa-industry"></i> İş Merkezleri</h2>
                <p>İş merkezleri, montaj ve dolum gibi işlemlerin yapıldığı üretim hatlarını veya iş istasyonlarını
                    temsil eder.</p>

                <h3>İş Merkezi Tanımlama</h3>
                <p>Yeni bir iş merkezi oluşturmak için:</p>
                <ol>
                    <li>"Altyapı" menüsünden "İş Merkezleri" sayfasına gidin.</li>
                    <li>"Yeni İş Merkezi Ekle" butonuna tıklayın.</li>
                    <li>İş merkezi ismini ve açıklamasını girin.</li>
                    <li>Kaydedin.</li>
                </ol>

                <h3>Montaj İş Emirlerinde Kullanımı</h3>
                <p>Montaj (dolum) iş emirlerinde, hangi iş merkezinde çalışılacağı belirtilir. Bu bilgi:</p>
                <ul>
                    <li>Üretim planlaması için önemlidir.</li>
                    <li>İş yükü dağılımını takip etmede yardımcı olur.</li>
                    <li>Raporlamalarda filtreleme imkanı sunar.</li>
                </ul>
            </section>

            <!-- Manuel Stok -->
            <section id="stok-operasyon" class="section-card">
                <h2 class="section-title"><i class="fas fa-exchange-alt"></i> Manuel Stok Operasyonları</h2>
                <p>Otomatik süreçler (Üretim/Satış) dışındaki stok hareketleri buradan yapılır.</p>

                <ul>
                    <li><strong>Mal Kabul:</strong> Tedarikçiden gelen malların stoğa girişidir. Fatura/İrsaliye no
                        girilmelidir.</li>
                    <li><strong>Transfer:</strong> Ürünlerin depolar veya raflar arası yer değişimidir.</li>
                    <li><strong>Sayım Fazlası:</strong> Fiziksel sayımda sistemden fazla ürün çıkarsa stoğu artırmak
                        için kullanılır.</li>
                    <li><strong>Fire / Sayım Eksiği:</strong> Kırılan, bozulan veya kaybolan ürünleri stoktan düşmek
                        için kullanılır. Gider olarak kaydedilir.</li>
                </ul>
            </section>

            <!-- Stok Hareketleri -->
            <section id="stok-hareketleri" class="section-card">
                <h2 class="section-title"><i class="fas fa-exchange-alt"></i> Stok Hareketleri</h2>
                <p>Tüm stok girişi ve çıkışlarının detaylı olarak takip edildiği bölüm.</p>

                <h3>Manuel Stok Hareketleri</h3>
                <p>"Operasyonlar" menüsünden "Stok Hareketleri" sayfası:</p>
                <ul>
                    <li><strong>Sayım Fazlası:</strong> Fiziksel sayım sonrası sistemden fazla ürün varsa</li>
                    <li><strong>Fire / Sayım Eksiği:</strong> Kırık, bozuk ürün veya sayım eksikliği için</li>
                    <li><strong>Transfer:</strong> Ürünlerin depolar/raflar arası yer değiştirmesi</li>
                    <li><strong>Mal Kabul:</strong> Tedarikçiden gelen malların stoğa girişi</li>
                </ul>

                <div class="info-box">
                    <strong><i class="fas fa-info-circle"></i> Otomatik Hareketler:</strong> Üretim ve satış süreçleri
                    sırasında stok değişiklikleri otomatik olarak kaydedilir.
                </div>
            </section>

            <!-- Finans -->
            <section id="finans" class="section-card">
                <h2 class="section-title"><i class="fas fa-wallet"></i> Finans ve Maliyet Yönetimi</h2>

                <h3>Maliyet Hesaplama Mantığı</h3>
                <p>Sistem, ürün maliyetlerini hesaplarken <strong>Son Satın Alma Fiyatı</strong> yöntemini kullanır.</p>
                <div class="info-box">
                    <strong>Formül:</strong><br>
                    Bir ürünün maliyeti = (Bileşen 1 Miktarı x Son Alış Fiyatı) + (Bileşen 2 Miktarı x Son Alış Fiyatı)
                    + ...
                </div>
                <p>Eğer alış fiyatları döviz (USD/EUR) cinsinden ise, "Ayarlar" menüsündeki güncel kurlar kullanılarak
                    TL'ye çevrilir.</p>
            </section>

            <!-- Gider Yönetimi -->
            <section id="gider" class="section-card">
                <h2 class="section-title"><i class="fas fa-money-bill-wave"></i> Gider Yönetimi</h2>
                <p>Şirketin sabit ve değişken giderlerini kaydetmek ve analiz etmek için kullanılır.</p>

                <h3>Gider Ekleme</h3>
                <p>Yeni bir gider kaydı oluşturmak için:</p>
                <ol>
                    <li>"Finans" menüsünden "Gider Yönetimi" sayfasına gidin.</li>
                    <li>"Yeni Gider Ekle" butonuna tıklayın.</li>
                    <li>Tarih, kategori, tutar, ödeme tipi ve açıklama girin.</li>
                    <li>Gerekiyorsa fatura numarası ekleyin.</li>
                    <li>Kaydedin.</li>
                </ol>

                <h3>Kategoriler</h3>
                <p>Giderler şu kategorilerde sınıflandırılabilir:</p>
                <ul>
                    <li><strong>Personel Gideri:</strong> Maaş, prim, sosyal aidat gibi</li>
                    <li><strong>Malzeme Gideri:</strong> Üretim dışı malzeme alımları</li>
                    <li><strong>İşletme Gideri:</strong> Elektrik, su, İnternet, kira gibi</li>
                    <li><strong>Taşıt Gideri:</strong> Şirket taşıtları ile ilgili giderler</li>
                    <li><strong>Diğer:</strong> Sınıflandırılamayan giderler</li>
                </ul>

                <div class="info-box">
                    <strong><i class="fas fa-info-circle"></i> Raporlama:</strong> Kayıtlı giderler "Raporlar"
                    menüsünden aylık ve kategorik olarak analiz edilebilir.
                </div>
            </section>

            <!-- Sistem Günlükleri ve İzleme -->
            <section id="loglar" class="section-card">
                <h2 class="section-title"><i class="fas fa-clipboard-list"></i> Sistem Günlükleri ve İzleme</h2>
                <p>Sistem, tüm kullanıcı etkileşimlerini ve kritik işlemleri log olarak kaydeder. Bu loglar sistem güvenliği ve izleme açısından önemlidir.</p>

                <h3>1. Loglama Sistemi</h3>
                <p>Sistemde aşağıdaki işlemler otomatik olarak loglanır:</p>
                <ul>
                    <li>Kullanıcı giriş ve çıkışları</li>
                    <li>Veri ekleme, düzenleme ve silme işlemleri</li>
                    <li>Sipariş oluşturma ve durum değişiklikleri</li>
                    <li>Üretim başlatma ve tamamlama işlemleri</li>
                    <li>Yedekleme ve kurtarma işlemleri</li>
                    <li>Sistem ayarlarında yapılan değişiklikler</li>
                </ul>

                <h3>2. Log Takibi</h3>
                <p>Tüm loglar <code>log_tablosu</code> adlı veritabanı tablosunda saklanır. Loglar şu bilgileri içerir:</p>
                <ul>
                    <li><strong>Kullanıcı Adı:</strong> İşlemi yapan kullanıcı</li>
                    <li><strong>Log Metni:</strong> Gerçekleşen işlem açıklaması</li>
                    <li><strong>İşlem Türü:</strong> CREATE, UPDATE, DELETE, LOGIN vb.</li>
                    <li><strong>Tarih:</strong> İşlemin gerçekleştiği tarih ve saat</li>
                </ul>

                <h3>3. Telegram Bildirimleri</h3>
                <p>Kritik işlemler sistem ayarlarında tanımlı Telegram bot aracılığıyla bildirilir:</p>
                <ul>
                    <li>Yeni müşteri siparişi oluşturulması</li>
                    <li>Otomatik günlük yedekleme</li>
                    <li>Sistem erişimleri</li>
                    <li>Önemli sistem değişiklikleri</li>
                </ul>
                <p>Bildirimler ayarlar sayfasından yapılandırılabilir.</p>
            </section>

            <!-- Çerçeve Sözleşmeler -->
            <section id="cerceve-sozlesmeler" class="section-card">
                <h2 class="section-title"><i class="fas fa-file-contract"></i> Çerçeve Sözleşmeler</h2>
                <p>Tedarikçilerle yapılan malzeme alım anlaşmalarının detaylarını içerir. Limit ve ödeme planlamaları
                    yapılır.</p>

                <h3>Sözleşme Tanımlama</h3>
                <p>Yeni bir çerçeve sözleşme oluşturmak için:</p>
                <ol>
                    <li>"Finans" menüsünden "Sözleşmeler" sayfasına gidin.</li>
                    <li>"Yeni Sözleşme Ekle" butonuna tıklayın.</li>
                    <li>Tedarikçi, malzeme, birim fiyat, para birimi seçin.</li>
                    <li>Toplam limit miktarı ve başlangıç/bitiş tarihlerini girin.</li>
                    <li>Öncelik seviyesini belirleyin (1=en yüksek, 5=en düşük).</li>
                    <li>Kaydedin.</li>
                </ol>

                <h3>Sözleşme Kullanımı</h3>
                <p>Bir sözleşme:</p>
                <ul>
                    <li>Belirtilen tarih aralığında</li>
                    <li>Belirtilen limit miktarı dahilinde</li>
                    <li>Belirtilen öncelik sırasına göre</li>
                    <li>Mal kabul işlemlerinde otomatik olarak dikkate alınır.</li>
                </ul>

                <div class="warning-box">
                    <strong><i class="fas fa-exclamation-triangle"></i> Limit ve Geçerlilik:</strong> Sözleşme limiti
                    dolunca veya bitiş tarihi geçmişse otomatik olarak pasif duruma geçer.
                </div>
            </section>

            <!-- Raporlama -->
            <section id="raporlama" class="section-card">
                <h2 class="section-title"><i class="fas fa-chart-pie"></i> Raporlama</h2>
                <p>Sistemdeki tüm verilerin analiz edildiği ve görselleştirildiği bölüm.</p>

                <h3>Mevcut Raporlar</h3>
                <ul>
                    <li><strong>Gider Raporları:</strong> Aylık ve kategorik gider analizleri</li>
                    <li><strong>Kritik Stok Raporları:</strong> Kritik seviye altındaki ürün ve malzemeler</li>
                    <li><strong>Eksik Bileşen Raporu:</strong> Üretimi yapılamayan ürünler (bileşen eksikliği)</li>
                    <li><strong>Ağaçları Olmayan Esanslar:</strong> Üretim reçetesi olmayan esanslar</li>
                </ul>

                <h3>Grafiksel Analiz</h3>
                <p>Raporlar sayfasında:</p>
                <ul>
                    <li>Aylık gider trendleri</li>
                    <li>Kategori bazlı dağılımlar</li>
                    <li>Anormal artışlar (diğer aylarla kıyasla)</li>
                </ul>
            </section>

            <!-- Raporlar -->
            <section id="raporlar" class="section-card">
                <h2 class="section-title"><i class="fas fa-chart-pie"></i> Raporlar</h2>
                <p>Karar destek mekanizması olarak sunulan raporlar şunlardır:</p>

                <ul>
                    <li><strong>Gider Raporları:</strong> Aylık harcamaları ve kategori bazlı dağılımları gösterir.
                        Anormal artışları (%2 üzeri sapma) otomatik tespit eder.</li>
                    <li><strong>Kritik Stok Raporu:</strong> Stoğu belirlenen seviyenin altına düşen ürün ve malzemeleri
                        listeler. Satın alma planlaması için kritiktir.</li>
                    <li><strong>Eksik Bileşen Analizi:</strong> Reçetesi tanımlanmamış veya eksik tanımlanmış ürünleri
                        tespit eder.</li>
                    <li><strong>Ağaçsız Esanslar:</strong> Üretim reçetesi olmayan esansları listeler.</li>
                </ul>
            </section>

            <!-- Sipariş Takibi -->
            <section id="siparis-takibi" class="section-card">
                <h2 class="section-title"><i class="fas fa-clipboard-list"></i> Sipariş Takibi (Müşteri Paneli)</h2>
                <p>Müşterilerin kendi panelinden oluşturduğu siparişlerin takip edildiği alan.</p>

                <h3>Sipariş Durumları</h3>
                <ul>
                    <li><strong>Beklemede:</strong> Yeni oluşturulan sipariş</li>
                    <li><strong>Onaylandı:</strong> Hazırlık süreci başladı</li>
                    <li><strong>Tamamlandı:</strong> Stok düşüşü yapıldı</li>
                    <li><strong>İptal Edildi:</strong> Geriye doğru stok eklendi</li>
                </ul>

                <h3>Sipariş İşlemleri</h3>
                <p>Bir sipariş için şu işlemler yapılabilir:</p>
                <ul>
                    <li>Durum değiştirme (onaylama, tamamlama, iptal etme)</li>
                    <li>Sipariş kalemleri görüntüleme</li>
                    <li>İlgili personel bilgisi takibi</li>
                    <li>Stok etkisi izleme</li>
                </ul>
            </section>

            <!-- Malzeme Siparişleri -->
            <section id="malzeme-siparisleri" class="section-card">
                <h2 class="section-title"><i class="fas fa-box-open"></i> Malzeme Siparişleri</h2>
                <p>Tedarikçilere verilen hammadde ve ambalaj malzemesi siparişlerinin takip edildiği bölümdür. Sistemde
                    malzeme ihtiyaçları tespit edildikçe bu alanda tedarikçilere sipariş verilebilir.</p>

                <h3>1. Malzeme Siparişi Oluşturma</h3>
                <p>Yeni bir malzeme siparişi oluşturmak için:</p>
                <ol>
                    <li>"Operasyonlar" menüsünden "Malzeme Siparişleri" sayfasına gidin.</li>
                    <li>"Yeni Sipariş Ekle" butonuna tıklayın.</li>
                    <li>Malzeme seçin (Sistemde tanımlı olan hammaddeler, ambalaj malzemeleri vb.).</li>
                    <li>Eşleşen tedarikçiyi seçin (Malzemeyle ilişkili olan veya uygun fiyat teklifi veren tedarikçi).
                    </li>
                    <li>Gerekli miktarı girin (Ondalıklı girişe izin verilir).</li>
                    <li>Teslim edileceği bildirilen tarihi seçin (Planlama için önemlidir).</li>
                    <li>İsteğe bağlı açıklama ekleyin.</li>
                    <li>"Kaydet" butonuna tıklayın.</li>
                </ol>

                <h3>2. Sipariş Durumları</h3>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Durum</th>
                                <th>Anlamı</th>
                                <th>İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><span class="badge bg-info">Oluşturuldu</span></td>
                                <td>Sipariş planlama aşamasında</td>
                                <td>Sipariş verilmeden önceki durumu</td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-warning text-dark">Sipariş Verildi</span></td>
                                <td>Tedarikçiye sipariş iletildi</td>
                                <td>Siparişin tedarikçiye iletildiği aşamadır</td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-success">Teslim Edildi</span></td>
                                <td>Tedarikçiden malzeme teslim alındı</td>
                                <td>Malzeme physically tedarikçiden teslim alındığında bu duruma getirilir</td>
                            </tr>
                            <tr>
                                <td><span class="badge bg-danger">İptal Edildi</span></td>
                                <td>Sipariş iptal edildi</td>
                                <td>Gerekli durumlarda sipariş iptal edilebilir</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <h3>3. Sipariş Takibi ve Yönetimi</h3>
                <p>Malzeme siparişlerinin takibi şu şekilde yapılır:</p>
                <ul>
                    <li><strong>Arama ve Filtreleme:</strong> Malzeme adı, tedarikçi, sipariş numarası gibi kriterlere
                        göre arama yapılabilir.</li>
                    <li><strong>Durum Güncelleme:</strong> Siparişlerin durumu ilerledikçe güncellenmelidir (Sipariş
                        Verildi → Teslim Edildi).</li>
                    <li><strong>Listeleme ve Sayfalandırma:</strong> Sayfa başına 10, 25, 50 veya 100 kayıtlık
                        görüntüleme seçeneği mevcuttur.</li>
                </ul>

                <h3>4. Malzeme ve Tedarikçi Seçimi</h3>
                <p>Sipariş oluştururken:</p>
                <ul>
                    <li>Sistemde daha önceden tanımlanmış malzemeler listelenir.</li>
                    <li>Bir malzeme seçildiğinde, o malzeme ile sözleşme (çerçeve sözleşme) olan tedarikçiler otomatik
                        olarak listelenir.</li>
                    <li>Bu sayede uygun tedarikçi seçimini kolaylaştırır.</li>
                </ul>

                <h3>5. Teslimat Takibi</h3>
                <p>Malzeme siparişleri oluştururken teslim tarihi belirlenir. Bu tarih planlama açısından önemlidir:</p>
                <ul>
                    <li>Teslim tarihi, üretim planlarını etkileyebilir.</li>
                    <li>Geç teslimat risklerini minimize etmek için tedarikçiyle uyumlu planlama yapılmalıdır.</li>
                    <li>Teslim alınan mallar, "Mal Kabul" işlemi ile stoklara dahil edilmelidir.</li>
                </ul>

                <div class="info-box">
                    <strong><i class="fas fa-info-circle"></i> Entegrasyon:</strong> Malzeme siparişlerinden teslim
                    alınan ürünler "Manuel Stok Operasyonları" sayfasındaki "Mal Kabul" işlemi ile sisteme stoğa dahil
                    edilmelidir. Bu işlem, sipariş durumu "Teslim Edildi" yapıldığında otomatik olarak gerçekleşmez.
                </div>

                <div class="warning-box">
                    <strong><i class="fas fa-exclamation-triangle"></i> Önemli:</strong> Sipariş verilen malzemelerin
                    üretimde kullanılabilmesi için mutlaka "Mal Kabul" işlemiyle sisteme alınması gerekir. Aksi takdirde
                    üretim planları aksamış olur.
                </div>
            </section>

            <!-- Ürün Akışı -->
            <section id="urun-akisi" class="section-card">
                <h2 class="section-title"><i class="fas fa-project-diagram"></i> Ürün Oluşumundan Satışa Kadar Olan
                    Süreç</h2>
                <p>Sistemde bir ürünün yaratılışından satışına kadar geçen tüm süreci anlatır. Bu süreç malzeme/esans
                    tanımlama, ürün tanımlama, ürün/esans ağacı oluşturma, sözleşme oluşturma, üretim planlama ve satış
                    süreçlerini kapsar.</p>

                <h3>1. Lokasyon, Tedarikçi Tanımı, Malzeme Tanımı ve Çerçeve Sözleşme Oluşturma</h3>
                <p>Ürün ve esans üretiminde kullanılan temel girdi maddelerinin tanımlandığı aşamadır:</p>
                <ul>
                    <li><strong>Lokasyonlar</strong> sayfasında depolar ve raflar tanımlanır (Depo: Parfüm Üretim Depo
                        1, Raf: A01, B02 vb.).</li>
                    <li><strong>Tedarikçiler</strong> sayfasında tedarikçi bilgileri tanımlanır (firma adı, iletişim
                        bilgileri).</li>
                    <li><strong>Malzemeler</strong> sayfasında hammaddeler, ambalaj malzemeleri (şişe, kapak, etiket,
                        pompa vb.) tanımlanır.</li>
                    <li>Her malzeme için tür (şişe, kutu, etiket, iç ambalaj, pompa, diğer), birim (kg, lt, adet), depo
                        ve raf bilgileri girilir (depolar ve raflar önceden tanımlanmış olmalıdır).</li>
                    <li>Tedarikçi ve malzeme kayıtları oluşturulduktan sonra, <strong>Çerçeve Sözleşmeler</strong>
                        sayfasında sözleşme oluşturulur.</li>
                    <li>Çerçeve sözleşme oluşturulurken: Hangi tedarikçiden hangi malzeme ne kadar ve hangi fiyattan
                        alınacak bilgileri girilir.</li>
                    <li>Sözleşmelerde başlangıç ve bitiş tarihi belirlenir, öncelik seviyesi atanır (1=en yüksek, 5=en
                        düşük).</li>
                    <li>Sözleşme tanımlanan limit miktarı dolduğunda sözleşme otomatik olarak pasif hale gelir.</li>
                </ul>

                <h3>2. Tank, Esans Tanımı ve Esans Ağacı Oluşturma</h3>
                <p>Parfüm üretiminde kullanılan esansların tanımlandığı ve reçetelerinin çıkarıldığı aşamadır:</p>
                <ul>
                    <li><strong>Tanklar</strong> sayfasında tanklar tanımlanır (Tank: TANK-01, TANK-02 vb.).</li>
                    <li><strong>Esanslar</strong> sayfasında yeni bir esans tanımlanır (örneğin Gül Esansı, Lavanta
                        Esansı).</li>
                    <li>Her esans için hangi tankta saklanacağı belirlenir (tanklar önceden tanımlanmış olmalıdır).</li>
                    <li>Esans tanımı oluşturulduktan sonra, <strong>Ürün Ağaçları</strong> sayfasında "Esans Ağaçları"
                        sekmesi altından esansın bileşenleri tanımlanır (örneğin: Yüzde 60 Saf Alkol, Yüzde 30 Saf Su,
                        Yüzde 10 Gül Özü).</li>
                    <li>Esans ağacı; esansın hangi malzemelerden (yukarıda tanımlanan) hangi oranlarda üretileceği
                        bilgilerini içerir.</li>
                    <li>Demlenme süresi gibi özel üretim süreçleri de burada belirlenir.</li>
                </ul>

                <h3>3. Ürün Tanımı ve Ürün Ağacı Oluşturma</h3>
                <p>Elde edilecek nihai ürünün tanımlandığı ve üretim reçetesinin çıkarıldığı aşamadır:</p>
                <ul>
                    <li><strong>Ürünler</strong> sayfasında nihai ürün tanımlanır (örneğin: 50 ml Gül Parfümü).</li>
                    <li>Ürün için stok miktarı, kritik seviye, satış fiyatı, depo ve raf bilgileri girilir (depolar ve
                        raflar önceden tanımlanmış olmalıdır).</li>
                    <li>Ürün tanımı oluşturulduktan sonra, <strong>Ürün Ağaçları</strong> sayfasında "Ürün Ağaçları"
                        sekmesi altından ürünün bileşenleri tanımlanır (örneğin: 10 ml Gül Esansı, 1 adet 50 ml Şişe, 1
                        adet Pompa).</li>
                    <li>Esans içeren ürünler için, ürün ağacında esans bileşeni tanımlanır ve daha önce tanımlanmış olan
                        esans ağacı kullanılır.</li>
                    <li>Ürün ağacı, ürünün hangi malzeme ve esanslardan hangi miktarlarda üretileceğini gösterir.</li>
                </ul>

                <h3>4. Mal Kabul ve Stok Hareketleri</h3>
                <p>Tedarikten gelen malzemelerin sisteme giriş yapıldığı ve stok seviyelerinin güncellendiği aşamadır:
                </p>
                <ul>
                    <li><strong>Manuel Stok Operasyonları</strong> sayfasında "Mal Kabul" işlemi yapılır.</li>
                    <li>Mevcut çerçeve sözleşmelerden uygun olanı sistem tarafından otomatik seçilir.</li>
                    <li>Malzeme stok miktarı artırılır, stok hareket kaydı oluşturulur.</li>
                    <li>Mal kabul işlemi, malzeme fiyat bilgilerinin sistemde güncel olmasında kritik rol oynar (maliyet
                        hesaplamaları için).</li>
                    <li>Mal kabul sırasında tedarikçiden gelen fatura bilgileri de sisteme işlenebilir.</li>
                </ul>

                <h3>5. İş Merkezleri, Üretim Planlama (Esans ve Montaj)</h3>
                <p>Esans ve nihai ürün üretim işlemlerinin yapıldığı aşamadır:</p>
                <ul>
                    <li><strong>İş Merkezleri</strong> sayfasında üretim yapılacak iş merkezleri tanımlanır (İş Merkezi:
                        Montaj Bölümü, Karışım Ünitesi vb.).</li>
                    <li>Esans üretimi için <strong>Esans İş Emirleri</strong> sayfasına gidilir (esans ve esans ağacı
                        önceden tanımlanmış olmalıdır).</li>
                    <li>Yeni esans iş emri oluşturulur: Hangi esans üretilecek, ne kadar üretilecek, hangi tankta
                        üretilecek bilgileri girilir.</li>
                    <li>İş emri <strong>"Başlat"</strong> durumuna getirilir: Bu işlem esnasında gerekli malzeme stoğu
                        otomatik olarak düşülür (örneğin: Saf Alkol, Saf Su ve gerekli özler).</li>
                    <li>İş emri <strong>"Tamamla"</strong> durumuna getirilir: Esans stoğu artırılır, tank bilgisi
                        güncellenir.</li>
                    <li>Ürün montajı için <strong>Montaj İş Emirleri</strong> sayfası kullanılır. Montaj iş emri
                        oluşturulur: Hangi ürün, ne kadar üretilecek (ürün ve ürün ağacı önceden tanımlanmış olmalıdır).
                    </li>
                    <li>Bu aşamada üretim yapılacak iş merkezi de belirlenir (iş merkezleri önceden tanımlanmış
                        olmalıdır).</li>
                    <li>Montaj iş emri <strong>"Başlat"</strong> komutu verilir: Bu işlem esnasında gerekli bileşenlerin
                        stoğu düşülür (örneğin: Esans, Şişe, Pompa).</li>
                    <li>Montaj iş emri <strong>"Tamamla"</strong> durumuna getirilir: Nihai ürün stoğu artırılır.</li>
                </ul>

                <h3>6. Satış ve Sipariş Süreci</h3>
                <p>Müşteri siparişlerinin işlendiği ve satış sürecinin yürütüldüğü aşamadır:</p>
                <ul>
                    <li>Önce <strong>Müşteriler</strong> sayfasında müşteri tanımlanır (firma bilgileri, iletişim
                        bilgileri).</li>
                    <li>Müşteriye giriş yetkisi verilirse, <strong>Müşteri Paneli</strong> aracılığıyla kendi
                        siparişlerini oluşturabilir.</li>
                    <li>Var olan müşteriler sistemde tanımlıdır, yeni müşteri tanımı yapılmadan sipariş verilemez.</li>
                    <li>Müşteri panelinde ürün kataloğu görüntülenir, stok durumuna göre sepete ürün eklenir.</li>
                    <li><strong>Müşteri Siparişleri</strong> sayfasında oluşturulan siparişler görünür. Siparişlerin
                        başlangıç durumu <strong>"Beklemede"</strong> durumudur.</li>
                    <li><strong>"Beklemede"</strong> durumunda olan sipariş onaylanmamıştır. Bu durumda sipariş
                        hazırlanmaz ve stok etkilemez.</li>
                    <li>Sipariş <strong>"Onaylandı"</strong> durumuna getirilir: Bu işlem hazırlık sürecini başlatır ama
                        stok düşüşüne neden olmaz. Bu durum, siparişin hazırlandığını ve fatura düzenlemesi için uygun
                        olduğunu gösterir.</li>
                    <li>Siparişin <strong>"Tamamlandı"</strong> durumuna getirilmesi için siparişteki ürünlerin stokta
                        mevcut olması gerekir.</li>
                    <li><strong>"Tamamlandı"</strong> durumuna getirildiğinde, siparişteki ürünlerin stoğu sistem
                        otomatik olarak düşer.</li>
                    <li>Bir siparişin <strong>"Tamamlandı"</strong> olarak işaretlenmesi için, önce
                        <strong>"Onaylandı"</strong> durumuna getirilmiş olması gerekir.
                    </li>
                    <li><strong>"İptal Edildi"</strong> durumuna getirilen siparişler için stok ekleme işlemi yapılır
                        (geri planlama).</li>
                    <li>İlgili ürünlerin stokta yeterli miktarda bulunmaması durumunda, üretim planlama ekibi tarafından
                        ilgili esans ve montaj iş emirleri planlanır.</li>
                </ul>

                <h3>7. Stok Takibi ve Manuel Hareketler</h3>
                <p>Stok seviyelerinin takip edildiği ve gerekli düzeltmelerin yapıldığı aşamadır:</p>
                <ul>
                    <li>Tüm stok hareketleri <strong>Stok Hareketleri</strong> sayfasından takip edilebilir.</li>
                    <li>Stok sayımı sırasında fazla çıkan ürünler için <strong>"Sayım Fazlası"</strong> hareketi
                        girilir.</li>
                    <li>Stok sayımı sırasında eksik çıkan ürünler için <strong>"Fire / Sayım Eksigi"</strong> hareketi
                        girilir.</li>
                    <li><strong>Önemli:</strong> "Fire" seçeneği ile yapılan çıkışlar, sistem tarafından otomatik olarak
                        maliyetlendirilir ve "Fire Gideri" olarak giderlere eklenir. "Sayım Eksiği" ise sadece stoktan
                        düşer, gider oluşturmaz.</li>
                    <li>Farklı depolar veya raflar arasında <strong>Transfer</strong> işlemleri yapılır (depolar ve
                        raflar önceden tanımlanmış olmalıdır).</li>
                    <li>Stok seviyeleri <strong>Kritik Stok Raporları</strong> ile takip edilir.</li>
                    <li>Stok seviyeleri düşük olan ürünler için otomatik uyarı sistemlerinden yararlanılır.</li>
                    <li>Manuel stok hareketlerine <strong>Manuel Stok Hareket</strong> sayfasından erişilir.</li>
                </ul>

                <h3>8. Sistem Entegrasyonu ve Otomasyon</h3>
                <p>Sistemin otomatik çalışan bileşenlerini ve entegrasyon noktalarını gösterir:</p>
                <ul>
                    <li>Esans iş emri başlatıldığında gerekli malzeme stoğu otomatik düşülür.</li>
                    <li>Esans iş emri tamamlandığında esans stoğu otomatik artar.</li>
                    <li>Montaj iş emri başlatıldığında gerekli bileşenlerin stoğu (esans, ambalaj malzemeleri) otomatik
                        düşülür.</li>
                    <li>Montaj iş emri tamamlandığında nihai ürün stoğu otomatik artar.</li>
                    <li>Müşteri siparişi "Tamamlandı" yapıldığında nihai ürünler stoğundan otomatik düşülür.</li>
                    <li>Müşteri siparişi iptal edildiğinde ilgili ürünler stoğa otomatik olarak eklenir.</li>
                    <li>Maliyet hesaplamaları ürün ağacı ve çerçeve sözleşme fiyatları ile yapılır.</li>
                    <li>Tüm süreçlerde stok hareket kayıtları otomatik olarak oluşturulur ve takip edilir.</li>
                    <li>Sistem, olası hataları ve eksiklikleri otomatik olarak saptayabilir (örneğin: Ürün ağacı olmayan
                        ürünler).</li>
                </ul>

                <div class="info-box">
                    <strong><i class="fas fa-info-circle"></i> İş Akışı Sırası:</strong> Ürün üretiminde iş akışı sırası
                    önemlidir: Önce lokasyon (depo/raf), tedarikçi, tank, iş merkezi gibi altyapı tanımlamaları
                    yapılmalıdır, sonra malzeme/esans tanımlanabilir, ardından ürün ve ürün ağacı oluşturulabilir, daha
                    sonra üretim planlaması yapılabilir ve son olarak satış süreci başlatılabilir.
                </div>
            </section>

            <!-- API Dökümantasyonu -->
            <section id="api" class="section-card">
                <h2 class="section-title"><i class="fas fa-code"></i> API ve Entegrasyonlar</h2>
                <p>Sistem, dış uygulamalarla entegrasyon yapabilmek için JSON tabanlı API endpoint'lerine sahiptir. Tüm API işlemleri oturum kontrolleriyle korunmuştur ve sadece yetkili kullanıcılar tarafından erişilebilir.</p>

                <h3>1. Genel API Kullanımı</h3>
                <p>API endpoint'leri <code>/api_islemleri</code> dizininde yer alır. Tüm API'ler JSON formatında çalışır ve yanıt verir.</p>

                <ul>
                    <li><strong>Yetkilendirme:</strong> Session kontrolleri ile sağlanır. API'leri kullanmak için kullanıcı girişi zorunludur.</li>
                    <li><strong>Yanıt Formatı:</strong> Tüm API'ler JSON formatında yanıt verir.</li>
                    <li><strong>Hata Yönetimi:</strong> Hatalar JSON formatında <code>{status: 'error', message: 'Hata açıklaması'}</code> şeklinde döner.</li>
                </ul>

                <h3>2. Ana API Endpoint'leri</h3>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Endpoint</th>
                                <th>İşlevi</th>
                                <th>Parametreler</th>
                                <th>Yanıt</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code>/api_islemleri/musteriler_islemler.php</code></td>
                                <td>Müşteri CRUD işlemleri</td>
                                <td>action (get_customers, add_customer, update_customer, delete_customer)</td>
                                <td>Müşteri listesi veya işlem sonucu</td>
                            </tr>
                            <tr>
                                <td><code>/api_islemleri/order_operations.php</code></td>
                                <td>Müşteri sipariş işlemleri</td>
                                <td>action (submit_order), ürün kodları, adetler</td>
                                <td>Sipariş oluşturuldu mesajı</td>
                            </tr>
                            <tr>
                                <td><code>/api_islemleri/cart_operations.php</code></td>
                                <td>Sepet işlemleri</td>
                                <td>action (add_to_cart, remove_from_cart, get_cart_contents)</td>
                                <td>Sepet içeriği veya işlem sonucu</td>
                            </tr>
                            <tr>
                                <td><code>/api_islemleri/get_employees_ajax.php</code></td>
                                <td>Personel listeleme</td>
                                <td>page, limit, search</td>
                                <td>Personel listesi ve sayfalama bilgisi</td>
                            </tr>
                            <tr>
                                <td><code>/api_islemleri/urunler_islemler.php</code></td>
                                <td>Ürün CRUD işlemleri</td>
                                <td>action (get_products, add_product, update_product, delete_product)</td>
                                <td>Ürün listesi veya işlem sonucu</td>
                            </tr>
                            <tr>
                                <td><code>/api_islemleri/sistem_ozet_api.php</code></td>
                                <td>Sistem özet bilgileri</td>
                                <td>Yok</td>
                                <td>Sistemdeki veri özetleri</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <h3>3. Müşteri Paneli API'leri</h3>
                <p>Müşteri paneli ile ilgili API'ler sepet ve sipariş işlemlerini içerir:</p>
                <ul>
                    <li><strong>Cart Operations:</strong> Sepet işlemleri (ürün ekle, kaldır, içeriği getir)</li>
                    <li><strong>Order Operations:</strong> Sipariş işlemleri (sipariş oluştur)</li>
                    <li><strong>Search Products:</strong> Ürün arama ve listeleme</li>
                </ul>

                <h3>4. Yetkilendirme ve Güvenlik</h3>
                <p>Tüm API'ler aşağıdaki güvenlik kontrollerini içerir:</p>
                <ul>
                    <li>Oturum kontrolü (session)</li>
                    <li>Kullanıcı türü kontrolü (personel veya müşteri)</li>
                    <li>Yetki kontrolü (erkek erişim için)</li>
                    <li>Günlük kayıt (log_islem fonksiyonu ile)</li>
                </ul>
            </section>

            <!-- Komut Satırı Erişimi -->
            <section id="komut-satiri" class="section-card">
                <h2 class="section-title"><i class="fas fa-terminal"></i> Komut Satırı Erişimi</h2>
                <p>Sistem komut satırı üzerinden de erişilebilir. Bu erişim, otomasyon, test ve hata ayıklama amaçlı kullanılabilir.</p>

                <h3>1. Siteye Erişim</h3>
                <p>Terminal veya komut satırı üzerinden sisteme erişmek için aşağıdaki komutu kullanın:</p>
                <pre><code>curl http://localhost/projem/</code></pre>

                <h3>2. Giriş İşlemi</h3>
                <p>Curl komutu ile giriş yapmak için aşağıdaki komutu kullanabilirsiniz:</p>
                <pre><code>curl -X POST -d "username=admin@parfum.com&password=12345" http://localhost/projem/login.php</code></pre>
                <div class="info-box">
                    <strong><i class="fas fa-info-circle"></i> Not:</strong> Buradaki kullanıcı bilgileri örnek amaçlıdır. Gerçek sistemdeki doğru kullanıcı adı ve şifre kullanılmalıdır.
                </div>

                <h3>3. Veritabanına Erişim</h3>
                <p>MySQL veritabanına doğrudan erişim için aşağıdaki komutu kullanabilirsiniz:</p>
                <pre><code>mysql -h localhost -u root parfum_erp</code></pre>
                <p>Bu komut, sistemde MySQL sunucusunun localhost'ta çalıştığını ve parfum_erp adında bir veritabanı olduğunu varsayar.</p>

                <h3>4. Sunucu Hataları</h3>
                <p>Sunucu hatalarını kontrol etmek için aşağıdaki dizindeki log dosyalarını inceleyebilirsiniz:</p>
                <pre><code>C:\xampp\apache\logs</code></pre>
            </section>

            <!-- Yedekleme ve Kurtarma -->
            <section id="yedekleme" class="section-card">
                <h2 class="section-title"><i class="fas fa-database"></i> Yedekleme ve Kurtarma İşlemleri</h2>
                <p>Sistem, veri güvenliğini sağlamak için otomatik yedekleme ve acil durum kurtarma işlemleri içerir.</p>

                <h3>1. Otomatik Yedekleme</h3>
                <p>Sistem her gün 00:00 itibariyle otomatik olarak veritabanı yedekleri alır. Yedekler <code>/yedekler</code> dizinine kaydedilir ve Telegram aracılığıyla bildirilir.</p>
                <ul>
                    <li><strong>Yedekleme Zamanı:</strong> Günlük saat 00:00</li>
                    <li><strong>Yedek Formatı:</strong> SQL dosyası</li>
                    <li><strong>Yedek Konumu:</strong> <code>/yedekler</code> dizini</li>
                    <li><strong>Bildirim:</strong> Telegram kanalına yedekleme raporu gönderilir</li>
                </ul>

                <h3>2. Elle Yedekleme</h3>
                <p>İsteğe bağlı olarak elle yedekleme işlemi "Ayarlar" sayfasından yapılabilir.</p>

                <h3>3. Acil Durum Kurtarma</h3>
                <p>Acil durum kurtarma işlemleri için özel bir kullanıcı bilgisi kullanılır:</p>
                <ul>
                    <li><strong>Kullanıcı Adı:</strong> restore@sistem.com</li>
                    <li><strong>Şifre:</strong> _!ERp*R3sT0rE_99!</li>
                </ul>
                <div class="warning-box">
                    <strong><i class="fas fa-exclamation-triangle"></i> Uyarı:</strong> Kurtarma işlemi sistemdeki tüm verilerin en son yedekleme noktasına geri döndürülmesine neden olur. Bu işlem geri alınamaz!
                </div>
            </section>

            <!-- SSS -->
            <section id="sss" class="section-card">
                <h2 class="section-title"><i class="fas fa-question-circle"></i> Sıkça Sorulan Sorular</h2>

                <div class="faq-item">
                    <div class="faq-question" onclick="toggleFaq(this)">
                        Stok miktarı neden eksiye düşer?
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        Sistem operasyonel süreklilik için eksi stoğa izin verir. Bu genellikle, fiziksel olarak malın
                        geldiği ama sisteme "Mal Kabul" işleminin henüz girilmediği durumlarda, o malzemenin üretimde
                        kullanılmasıyla oluşur. Çözüm: Geçmiş tarihli Mal Kabul kaydı girmektir.
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question" onclick="toggleFaq(this)">
                        Maliyetler neden 0 (sıfır) görünüyor?
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        Maliyet hesaplanabilmesi için iki şart gereklidir:
                        1. Ürünün reçetesi (Ürün Ağacı) tanımlı olmalı.
                        2. Reçetedeki malzemelerin sistemde en az bir kere "Mal Kabul" işlemi yapılmış olmalı (Fiyat
                        bilgisi için).
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question" onclick="toggleFaq(this)">
                        Personel yetkilerini nasıl kısıtlarım?
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        "Personeller" sayfasına gidin, ilgili personelin yanındaki "Yetki" (Kalkan ikonu) butonuna
                        tıklayın. Açılan sayfadan personelin görmesini istemediğiniz modüllerin tikini kaldırıp
                        kaydedin.
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question" onclick="toggleFaq(this)">
                        Siparişi onayladım ama stok düşmedi?
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        Bu normaldir. "Onaylandı" durumu sadece hazırlık sürecini başlatır. Stok düşüşü, sipariş durumu
                        <strong>"Tamamlandı"</strong> yapıldığında gerçekleşir.
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question" onclick="toggleFaq(this)">
                        Sistem API'lerine nasıl erişebilirim?
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        API'ler <code>/api_islemleri</code> dizininde yer alır. Kullanıcı oturumu açık olmalıdır.
                        JSON formatında istekler gönderilir ve JSON yanıtlar alınır. Her işlem için yetkilendirme
                        kontrolleri yapılır.
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question" onclick="toggleFaq(this)">
                        Komut satırı üzerinden sisteme nasıl erişirim?
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        curl komutu ile siteye erişebilir ve giriş yapabilirsiniz. Veritabanına doğrudan erişim için mysql komutlarını kullanabilirsiniz. Detaylar için "Komut Satırı Erişimi" bölümüne bakın.
                    </div>
                </div>
            </section>

        </main>
    </div>

    <script>
        // FAQ Toggle Logic
        function toggleFaq(element) {
            const answer = element.nextElementSibling;
            const icon = element.querySelector('i');

            // Close other open FAQs
            document.querySelectorAll('.faq-answer.open').forEach(item => {
                if (item !== answer) {
                    item.classList.remove('open');
                    item.previousElementSibling.querySelector('i').classList.replace('fa-chevron-up', 'fa-chevron-down');
                }
            });

            // Toggle current
            answer.classList.toggle('open');
            if (answer.classList.contains('open')) {
                icon.classList.replace('fa-chevron-down', 'fa-chevron-up');
            } else {
                icon.classList.replace('fa-chevron-up', 'fa-chevron-down');
            }
        }

        // Smooth Scroll for Sidebar Links
        document.querySelectorAll('.nav-link').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();

                // Update active state
                document.querySelectorAll('.nav-link').forEach(link => link.classList.remove('active'));
                this.classList.add('active');

                const targetId = this.getAttribute('href');
                const targetSection = document.querySelector(targetId);

                if (targetSection) {
                    const headerOffset = 90;
                    const elementPosition = targetSection.getBoundingClientRect().top;
                    const offsetPosition = elementPosition + window.pageYOffset - headerOffset;

                    window.scrollTo({
                        top: offsetPosition,
                        behavior: "smooth"
                    });
                }
            });
        });

        // Highlight active section on scroll with improved offset calculation
        function updateActiveNavOnScroll() {
            let currentSection = '';
            const sections = document.querySelectorAll('.section-card');
            // Use same offset as smooth scroll function (90px for header)
            const scrollPos = window.scrollY + 90;

            sections.forEach(section => {
                const sectionTop = section.offsetTop;
                const sectionHeight = section.offsetHeight;
                const sectionBottom = sectionTop + sectionHeight;

                // Check if current scroll position is within this section
                if (scrollPos >= sectionTop && scrollPos < sectionBottom) {
                    currentSection = '#' + section.getAttribute('id');
                }
            });

            // If no section is found, select the last one that's above the scroll position
            if (!currentSection) {
                for (let i = sections.length - 1; i >= 0; i--) {
                    const section = sections[i];
                    if (scrollPos >= section.offsetTop) {
                        currentSection = '#' + section.getAttribute('id');
                        break;
                    }
                }
            }

            document.querySelectorAll('.nav-link').forEach(link => {
                link.classList.remove('active');
                if (link.getAttribute('href') === currentSection) {
                    link.classList.add('active');
                }
            });
        }

        // Initial update on page load
        updateActiveNavOnScroll();

        // Update on scroll with throttle for performance
        let ticking = false;
        window.addEventListener('scroll', () => {
            if (!ticking) {
                requestAnimationFrame(() => {
                    updateActiveNavOnScroll();
                    ticking = false;
                });
                ticking = true;
            }
        });
    </script>
</body>

</html>