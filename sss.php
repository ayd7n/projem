<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sık Sorulan Sorular - IDO KOZMETIK</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #4a0e63;
            --secondary: #7c2a99;
            --accent: #d4af37;
            --bg-color: #f4f6f9;
            --card-bg: #ffffff;
            --border-color: #e9ecef;
            --text-primary: #2d3436;
            --text-secondary: #636e72;
            --shadow-sm: 0 2px 8px rgba(0, 0, 0, 0.05);
            --shadow: 0 10px 25px rgba(74, 14, 99, 0.1);
            --shadow-hover: 0 15px 35px rgba(74, 14, 99, 0.15);
            --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            --gradient-primary: linear-gradient(135deg, var(--primary), var(--secondary));
        }

        body {
            font-family: 'Ubuntu', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-primary);
            margin: 0;
            padding-top: 70px;
            background-image: radial-gradient(circle at 10% 20%, rgba(124, 42, 153, 0.03) 0%, rgba(74, 14, 99, 0.03) 90%);
        }

        /* Top Navbar */
        .top-bar-wrapper {
            background: var(--gradient-primary);
            height: 70px;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            box-shadow: 0 4px 20px rgba(74, 14, 99, 0.2);
            display: flex;
            align-items: center;
            padding: 0 2rem;
            backdrop-filter: blur(10px);
        }

        .brand {
            color: var(--accent);
            font-size: 1.5rem;
            font-weight: 700;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
            letter-spacing: -0.5px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
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
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-home:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
            color: var(--accent);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
            box-sizing: border-box;
        }

        .page-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .page-header h1 {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 1rem;
            color: var(--primary);
            letter-spacing: -1px;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .page-header p {
            font-size: 1.1rem;
            color: var(--text-secondary);
            max-width: 700px;
            margin: 0 auto;
            line-height: 1.6;
        }

        .faq-category {
            margin-bottom: 2.5rem;
            background: var(--card-bg);
            border-radius: 14px;
            padding: 1.5rem;
            box-shadow: var(--shadow-sm);
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.6s ease-out, transform 0.6s ease-out;
        }

        .faq-category.animate-in {
            opacity: 1;
            transform: translateY(0);
        }

        .category-header {
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 1.2rem;
            padding-left: 0.8rem;
            border-left: 4px solid var(--accent);
            display: flex;
            align-items: center;
            gap: 0.8rem;
            color: var(--primary);
            background: linear-gradient(to right, rgba(74, 14, 99, 0.05), transparent);
            padding: 0.7rem 0.8rem;
            border-radius: 0 8px 8px 0;
        }

        .category-header i {
            color: var(--secondary);
        }

        /* FAQ Accordion */
        .faq-item {
            border: 1px solid var(--border-color);
            border-radius: 10px;
            margin-bottom: 0.8rem;
            overflow: hidden;
            transition: var(--transition);
        }

        .faq-item:hover {
            box-shadow: var(--shadow-sm);
        }

        .faq-question {
            padding: 1.2rem 1.5rem;
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
            padding: 0 1.5rem;
            max-height: 0;
            overflow: hidden;
            transition: all 0.3s ease-out;
            background: white;
        }

        .faq-answer.open {
            padding: 1.2rem 1.5rem;
            max-height: 1000px;
            border-top: 1px solid var(--border-color);
        }

        /* Info Boxes */
        .info-box {
            background: rgba(74, 14, 99, 0.05);
            border-left: 4px solid var(--primary);
            padding: 1.2rem;
            border-radius: 0 8px 8px 0;
            margin: 1.2rem 0;
        }

        .warning-box {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 1.2rem;
            border-radius: 0 8px 8px 0;
            margin: 1.2rem 0;
            color: #856404;
        }

        /* Content styling */
        h3 {
            color: var(--secondary);
            margin-top: 1.5rem;
            margin-bottom: 1rem;
            font-size: 1.2rem;
        }

        h4 {
            color: #2d3436;
            margin-top: 1.2rem;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        p {
            line-height: 1.7;
            color: var(--text-secondary);
            margin-bottom: 1rem;
        }

        ul,
        ol {
            color: var(--text-secondary);
            line-height: 1.7;
            padding-left: 1.5rem;
            margin-bottom: 1rem;
        }

        li {
            margin-bottom: 0.4rem;
        }

        pre {
            background: #f8f9fa;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 1rem;
            overflow-x: auto;
            margin: 1rem 0;
        }

        code {
            font-family: 'Courier New', monospace;
            background: rgba(74, 14, 99, 0.05);
            padding: 0.2rem 0.4rem;
            border-radius: 4px;
            color: #d63384;
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
        <div class="page-header">
            <h1>Sık Sorulan Sorular</h1>
            <p>Sistemimizi kullanırken karşılaşabileceğiniz soruların cevaplarını bu sayfada bulabilirsiniz.</p>
        </div>
            <div class="faq-category" id="genel">
                <h2 class="category-header"><i class="fas fa-star"></i> Genel Sorular</h2>

                <div class="faq-item">
                    <div class="faq-question">
                        <span>Sistemimize nasıl erişebilirim?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Sistemimize web tarayıcınız aracılığıyla erişebilirsiniz. Tarayıcınızda sistem adresini açarak giriş yapabilirsiniz.</p>
                        <p>Giriş için kullanıcı adı ve şifreniz gerekecektir. Yetkililerden giriş bilgilerinizi alabilirsiniz.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <span>Sistemimiz ne işe yarar?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>IDO KOZMETIK Kurumsal Kaynak Planlama (ERP) sistemi, hammadde tedariğinden nihai ürün satışına kadar olan tüm süreci entegre bir şekilde yönetmenizi sağlar.</p>
                        <p>Sistem aşağıdaki süreçleri kapsar:</p>
                        <ul>
                            <li>Ürün ve malzeme tanımı</li>
                            <li>Hammadde tedarik ve kontrol</li>
                            <li>Üretim planlama ve takibi</li>
                            <li>Stok takibi ve yönetimi</li>
                            <li>Müşteri ilişkileri ve sipariş yönetimi</li>
                            <li>Finansal hesaplamalar ve raporlama</li>
                        </ul>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <span>Sistemdeki temel modüller nelerdir?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Sistemimiz aşağıdaki temel modüllerden oluşur:</p>
                        <ul>
                            <li><strong>CRM:</strong> Müşteri ilişkileri ve tedarikçi yönetimi</li>
                            <li><strong>Stok & Envanter:</strong> Ürün ve hammaddelerin takibi</li>
                            <li><strong>Satış & Sipariş:</strong> Müşteri siparişlerinin yönetimi</li>
                            <li><strong>Üretim:</strong> Üretim planlama ve üretim emirleri</li>
                            <li><strong>Finans & Maliyet:</strong> Maliyet hesaplamaları ve finansal işlemler</li>
                            <li><strong>Raporlama:</strong> İşletme performansı için detaylı raporlar</li>
                        </ul>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <span>Sistemde "Entegre Stok Yönetimi" nedir?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Sistemimiz "Entegre Stok Yönetimi" prensibiyle çalışır. Bu prensip şu şekilde açıklanabilir:</p>
                        <ul>
                            <li>Bir üretim emri verildiğinde hammaddeler stoktan otomatik olarak düşer</li>
                            <li>Üretim tamamlandığında nihai ürün stoğa otomatik olarak eklenir</li>
                            <li>Bu süreçte minimum manuel müdahale gerekir</li>
                            <li>Tüm işlemler sistematik ve takip edilebilir bir şekilde yürütülür</li>
                        </ul>
                        <div class="info-box">
                            <i class="fas fa-info-circle"></i> <strong>Avantaj:</strong> Hata oranını azaltır ve verimliliği artırır.
                        </div>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <span>Ana sayfada neler bulunur?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Navigation (Ana Sayfa) sayfasında aşağıdaki modüllere erişim sağlayabilirsiniz:</p>
                        <ul>
                            <li>Ürün Tanımları</li>
                            <li>Malzeme Tanımları</li>
                            <li>Çerçeve Sözleşmeler</li>
                            <li>Sipariş Tanımları</li>
                            <li>Stok Hareketleri</li>
                            <li>İş Merkezleri ve Lokasyonlar</li>
                            <li>Ürün Ağaçları</li>
                            <li>Finansal Raporlar</li>
                            <li>Yönetim Araçları</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="faq-category" id="yonetim">
                <h2 class="category-header"><i class="fas fa-user-shield"></i> Sistem Yönetimi</h2>

                <div class="faq-item">
                    <div class="faq-question">
                        <span>Sistem yönetimi nedir ve neleri içerir?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Sistem yönetimi bölümü, sistemin genel ayarlarının, kullanıcı yetkilerinin ve veri güvenliğinin yönetildiği alandır.</p>
                        <p>Burada yönetebileceğiniz unsurlar:</p>
                        <ul>
                            <li>Personel ve kullanıcı yönetimi</li>
                            <li>Kullanıcı yetkilendirme işlemleri</li>
                            <li>Sistem ayarları ve yapılandırmalar</li>
                            <li>Döviz kurları güncellemeleri</li>
                            <li>Yedekleme ve kurtarma işlemleri</li>
                            <li>Sistem bakım modu ayarları</li>
                        </ul>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <span>Personel ve yetki yönetimi nasıl yapılır?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Sisteme erişecek her kullanıcı için bir personel kaydı oluşturulmalıdır.</p>
                        <ul>
                            <li><strong>Personel Ekleme:</strong> Ad, Soyad, Pozisyon, Departman ve iletişim bilgileri girilir.</li>
                            <li><strong>Şifre İşlemleri:</strong> Personel eklerken veya düzenlerken "Şifre" alanından personelin sisteme giriş şifresi belirlenir. Boş bırakılırsa mevcut şifre değişmez.</li>
                            <li><strong>Yetkilendirme:</strong> Her personelin erişebileceği sayfalar ve yapabileceği işlemler (Ekleme, Silme, Düzenleme) detaylı olarak "Yetki Yönetimi" ekranından ayarlanır.</li>
                            <li><strong>Admin Kullanıcısı:</strong> Sistemde silinemeyen ve tüm yetkilere sahip özel bir "Admin" hesabı bulunur.</li>
                        </ul>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <span>Sistem ayarları neleri içerir?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <ul>
                            <li><strong>Bakım Modu:</strong> Sistemde güncelleme yapılırken "Bakım Modu" açılabilir. Bu modda sadece yöneticiler sisteme giriş yapabilir.</li>
                            <li><strong>Döviz Kurları:</strong> Maliyet hesaplamalarında kullanılmak üzere USD ve EUR kurları buradan güncellenir. Kurlar manuel girilebilir veya "Otomatik Çek" butonu ile güncel piyasa verileri alınabilir.</li>
                            <li><strong>Yedekleme:</strong> Veri kaybını önlemek için veritabanı yedekleri alınabilir. Yedekler bilgisayara indirilebilir veya sistemden geri yüklenebilir.</li>
                        </ul>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <span>Sistemin teknik gereksinimleri nelerdir?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Sistemin düzgün çalışması için gerekli teknik altyapı sistem yöneticileri tarafından sağlanmaktadır. Bu altyapı sayesinde:</p>
                        <ul>
                            <li>Veri güvenliği ve yedeklemeleri sağlanır</li>
                            <li>Raporlama işlemleri hızlı ve güvenli bir şekilde yapılır</li>
                            <li>Entegrasyonlar sorunsuz çalışır</li>
                        </ul>
                        <p>Bu konularda herhangi bir işlem yapmanıza gerek yoktur, sistem yöneticileri gerekli altyapıyı sağlar.</p>
                    </div>
                </div>
            </div>

            <div class="faq-category" id="crm">
                <h2 class="category-header"><i class="fas fa-users"></i> CRM (Müşteri İlişkileri)</h2>

                <div class="faq-item">
                    <div class="faq-question">
                        <span>CRM sistemi nedir ve ne işe yarar?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>CRM (Customer Relationship Management) sistemi, müşteriler ve tedarikçilerle olan ilişkileri yönetmeyi sağlayan bir modüldür.</p>
                        <p>CRM sistemi sayesinde:</p>
                        <ul>
                            <li>Müşteri ve tedarikçi bilgilerini merkezi olarak tutabilirsiniz</li>
                            <li>İletişim geçmişi ve notlar oluşturabilirsiniz</li>
                            <li>Sözleşme süreçlerini takip edebilirsiniz</li>
                            <li>Müşteri profillerine göre hizmet sunabilirsiniz</li>
                        </ul>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <span>Müşteri ve tedarikçi farkı nedir?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <ul>
                            <li><strong>Müşteriler:</strong> Ürün satışı yapılan firmalar veya şahıslardır. Sipariş oluşturabilmek için önce müşteri kaydı yapılmalıdır.</li>
                            <li><strong>Tedarikçiler:</strong> Hammadde veya ambalaj malzemesi satın alınan firmalardır. "Mal Kabul" işlemi yapabilmek için tedarikçi ile aktif bir sözleşme (veya sistemde tanım) olması gerekir.</li>
                        </ul>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <span>Yeni müşteri nasıl eklenir?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Yeni müşteri eklemek için aşağıdaki adımları izleyin:</p>
                        <ol>
                            <li><strong>Navigasyon:</strong> Sol menüden "Müşteriler" sayfasına gidin.</li>
                            <li><strong>Yeni Kayıt:</strong> "Yeni Müşteri Ekle" butonuna tıklayın.</li>
                            <li><strong>Bilgileri Gir:</strong> Müşteri adı, vergi numarası, adres, iletişim bilgileri gibi gerekli alanları doldurun.</li>
                            <li><strong>Kaydet:</strong> "Kaydet" butonuna tıklayarak müşteriyi sisteme ekleyin.</li>
                        </ol>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <span>Müşteri bilgilerini nasıl güncelleyebilirim?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Bir müşterinin bilgilerini güncellemek için:</p>
                        <ul>
                            <li>Müşteri listesinden ilgili müşteriyi bulun</li>
                            <li>"Düzenle" butonuna tıklayın</li>
                            <li>Güncellemek istediğiniz alanları değiştirin</li>
                            <li>"Kaydet" butonuna tıklayarak değişiklikleri onaylayın</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="faq-category" id="musteri-paneli">
                <h2 class="category-header"><i class="fas fa-user-tag"></i> Müşteri Paneli</h2>

                <div class="faq-item">
                    <div class="faq-question">
                        <span>Müşteri paneli nedir?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Müşteri paneli, müşterilerinizin kendi bilgilerini görüntüleyebileceği, siparişlerini takip edebileceği ve sistemle etkileşime girebileceği bir alandır.</p>
                        <p>Bu panel sayesinde:</p>
                        <ul>
                            <li>Müşteriler kendi siparişlerini takip edebilir</li>
                            <li>Sipariş durumlarını görebilirler</li>
                            <li>Müşteri hizmetleriyle daha efektif iletişim kurabilirler</li>
                        </ul>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <span>Müşteri paneline erişim nasıl sağlanır?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Müşteriler, özel atanan kullanıcı adı ve şifre ile müşteri paneline erişim sağlarlar. Bu bilgiler sistem yöneticisi tarafından verilir.</p>
                        <p>Sistemde bazı müşteriler için panel erişimi aktif edilir ve gerekli bilgiler müşteriye iletilir.</p>
                    </div>
                </div>
            </div>

            <div class="faq-category" id="stok">
                <h2 class="category-header"><i class="fas fa-boxes"></i> Stok & Envanter</h2>

                <div class="faq-item">
                    <div class="faq-question">
                        <span>Stok yönetimi nasıl çalışır?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Stok yönetimi sistemi, ürünlerin ve hammaddelerin miktarlarının takip edildiği alandır.</p>
                        <p>Temel işleyiş şu şekildedir:</p>
                        <ul>
                            <li>Stok girişi yapıldığında ürün miktarı artar</li>
                            <li>Stok çıkışı yapıldığında ürün miktarı azalır</li>
                            <li>Üretim emri verildiğinde, gerekli hammaddeler otomatik olarak düşer</li>
                            <li>Üretim tamamlandığında nihai ürün stoğa eklenir</li>
                        </ul>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <span>Stok seviyelerini nasıl kontrol edebilirim?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Stok seviyelerini kontrol etmek için aşağıdaki yolları kullanabilirsiniz:</p>
                        <ul>
                            <li><strong>Stok Raporları:</strong> Kritik stok seviyeleri, stok durumu raporları</li>
                            <li><strong>Stok Hareketleri:</strong> Belirli bir ürünün tarihçe takibi</li>
                            <li><strong>Dashboard:</strong> Anlık stok durumunu gösteren özet panel</li>
                        </ul>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <span>Stok seviyesi düşüğünde nasıl uyarı alabilirim?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Sistemde kritik stok seviyesi aşıldığında otomatik olarak uyarılar oluşturulabilir. Bu uyarılar:</p>
                        <ul>
                            <li>E-posta ile gönderilebilir</li>
                            <li>Telegram bildirimi olarak gönderilebilir</li>
                            <li>Sistem içi bildirim olarak görünebilir</li>
                        </ul>
                        <p>Bildirim ayarları "Telegram Ayarlar" ve "Ayarlar" bölümlerinden yapılandırılabilir.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <span>Stok hareketlerini nasıl takip edebilirim?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Stok hareketlerini takip etmek için "Stok Hareket Raporu" sayfasını kullanabilirsiniz. Bu sayfada:</p>
                        <ul>
                            <li>Bir ürünün tüm giriş ve çıkış hareketlerini görebilirsiniz</li>
                            <li>Tarih aralığına göre filtreleme yapabilirsiniz</li>
                            <li>Hareket türlerine göre (alım, üretim, satış) sınıflandırma yapabilirsiniz</li>
                            <li>Hangi kullanıcı tarafından yapıldığını görebilirsiniz</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="faq-category" id="lokasyon">
                <h2 class="category-header"><i class="fas fa-map-marker-alt"></i> Lokasyonlar</h2>

                <div class="faq-item">
                    <div class="faq-question">
                        <span>Lokasyonlar sistemi nedir?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Lokasyonlar sistemi, farklı depo ve üretim alanlarını tanımlamak ve stokların bu alanlar arasında taşınmasını sağlamak için kullanılır.</p>
                        <p>Bu sistem sayesinde:</p>
                        <ul>
                            <li>Farklı depolardaki stok miktarlarını ayrı ayrı takip edebilirsiniz</li>
                            <li>Stok transferlerini gerçekleştirebilirsiniz</li>
                            <li>Üretim ve dağıtım planlaması yapabilirsiniz</li>
                        </ul>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <span>Yeni lokasyon nasıl eklenir?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Yeni lokasyon eklemek için:</p>
                        <ol>
                            <li>Sol menüden "Lokasyonlar" sayfasına gidin</li>
                            <li>"Yeni Lokasyon Ekle" butonuna tıklayın</li>
                            <li>Lokasyon adı, tanımı ve diğer bilgileri girin</li>
                            <li>Kayıt butonuna tıklayarak lokasyonu sisteme ekleyin</li>
                        </ol>
                    </div>
                </div>
            </div>

            <div class="faq-category" id="urun-agaci">
                <h2 class="category-header"><i class="fas fa-project-diagram"></i> Ürün Ağaçları</h2>

                <div class="faq-item">
                    <div class="faq-question">
                        <span>Ürün ağaçları nedir?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Ürün ağaçları (Bill of Materials - BOM), bir ürünün hangi alt bileşenlerden oluştuğunu gösteren yapıdır.</p>
                        <p>Ürün ağaçları sayesinde:</p>
                        <ul>
                            <li>Bir ürünün üretiminde hangi hammaddelerin ve alt ürünlerin kullanılacağını belirleyebilirsiniz</li>
                            <li>Gerekli malzeme miktarlarını otomatik olarak hesaplayabilirsiniz</li>
                            <li>Üretim planlaması yapabilirsiniz</li>
                            <li>Maliyet hesaplamaları yapabilirsiniz</li>
                        </ul>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <span>Ürün ağacı nasıl oluşturulur?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Yeni bir ürün ağacı oluşturmak için:</p>
                        <ol>
                            <li><strong>Navigasyon:</strong> Sol menüden "Ürün Ağaçları" sayfasına gidin.</li>
                            <li><strong>Yeni Ağacı Oluştur:</strong> "Yeni Ürün Ağacı Oluştur" butonuna tıklayın.</li>
                            <li><strong>Ana Ürünü Seçin:</strong> Hangi ürün için ağaç oluşturacağınızı seçin.</li>
                            <li><strong>Bileşenleri Ekle:</strong> Ürünün hangi alt bileşenlerden oluştuğunu ve miktarlarını ekleyin.</li>
                            <li><strong>Kaydet:</strong> Ağacı "Kaydet" butonuyla onaylayın.</li>
                        </ol>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <span>Ürün ağaçları nasıl güncellenir?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Var olan bir ürün ağacını güncellemek için:</p>
                        <ul>
                            <li>"Ürün Ağaçları" sayfasından ilgili ağaç kaydını bulun</li>
                            <li>"Düzenle" butonuna tıklayın</li>
                            <li>Güncellemek istediğiniz bileşenleri değiştirin veya yeni bileşenler ekleyin</li>
                            <li>"Kaydet" butonuna tıklayarak değişiklikleri uygulayın</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="faq-category" id="satis">
                <h2 class="category-header"><i class="fas fa-shopping-cart"></i> Satış & Sipariş</h2>

                <div class="faq-item">
                    <div class="faq-question">
                        <span>Satış ve sipariş süreci nasıl işler?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Satış ve sipariş süreci aşağıdaki adımları içerir:</p>
                        <ol>
                            <li>Müşteri kaydı sisteme eklenir</li>
                            <li>Yeni sipariş oluşturulur</li>
                            <li>Sipariş detayları (ürünler, miktarlar, teslimat tarihi) girilir</li>
                            <li>Stok durumu kontrol edilir</li>
                            <li>Üretim planı yapılır (eğer stokta yoksa)</li>
                            <li>Sipariş tamamlandığında kargo veya teslimat yapılır</li>
                        </ol>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <span>Yeni sipariş nasıl oluşturulur?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Yeni sipariş oluşturmak için:</p>
                        <ol>
                            <li><strong>Navigasyon:</strong> Sol menüden "Yeni Müşteri Siparişi" sayfasına gidin.</li>
                            <li><strong>Müşteri Seçin:</strong> Siparişi verecek müşteriyi seçin.</li>
                            <li><strong>Ürünleri Seçin:</strong> Satış yapılacak ürünleri ve miktarlarını girin.</li>
                            <li><strong>Teslimat ve Ödeme:</strong> Teslimat tarihi ve ödeme şeklini belirleyin.</li>
                            <li><strong>Onayla:</strong> Siparişi onaylayarak işlemi tamamlayın.</li>
                        </ol>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <span>Sipariş durumu nasıl takip edilir?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Sipariş durumunu takip etmek için:</p>
                        <ul>
                            <li>"Müşteri Siparişleri" sayfasını ziyaret edin</li>
                            <li>Sipariş listesinden ilgili kaydı bulun</li>
                            <li>Sipariş durumunu (Hazırlanıyor, Üretimde, Kargoda, Tamamlandı) kontrol edin</li>
                            <li>Detaylı bilgi için "Detay" butonuna tıklayın</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="faq-category" id="uretim">
                <h2 class="category-header"><i class="fas fa-industry"></i> Üretim İşleyişi</h2>

                <div class="faq-item">
                    <div class="faq-question">
                        <span>Üretim süreci nasıl planlanır?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Üretim süreci şu adımlarla planlanır:</p>
                        <ol>
                            <li>Siparişler veya stok ihtiyaçları analiz edilir</li>
                            <li>Üretim emri oluşturulur</li>
                            <li>Gerekli hammaddelerin stokta olup olmadığı kontrol edilir</li>
                            <li>Üretim planı iş merkezlerine göre hazırlanır</li>
                            <li>Üretim başlatılır ve takibi yapılır</li>
                        </ol>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <span>Üretim emri nasıl verilir?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Üretim emri vermek için:</p>
                        <ol>
                            <li>"Esas İş Emirleri" veya "Montaj İş Emirleri" sayfasına gidin</li>
                            <li>"Yeni İş Emri" oluştur butonuna tıklayın</li>
                            <li>Üretilecek ürünü, miktarını ve teslim tarihini girin</li>
                            <li>Üretim için gerekli hammaddeleri kontrol edin</li>
                            <li>Emri onaylayarak üretimi başlatın</li>
                        </ol>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <span>Üretim takibi nasıl yapılır?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Üretim takibi için aşağıdaki sayfaları kullanabilirsiniz:</p>
                        <ul>
                            <li>"Esas İş Emirleri": Üretim emirlerinin durumu</li>
                            <li>"Montaj İş Emirleri": Montaj süreçlerinin takibi</li>
                            <li>"Bileşeni Eksik Eşanslar": Üretim için eksik malzeme olan ürünler</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="faq-category" id="is-merkezleri">
                <h2 class="category-header"><i class="fas fa-industry"></i> İş Merkezleri</h2>

                <div class="faq-item">
                    <div class="faq-question">
                        <span>İş merkezleri nedir?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>İş merkezleri, üretimde kullanılan farklı departman, makine veya ekipmanları temsil eden yapılarlardır.</p>
                        <p>Bu yapılar sayesinde:</p>
                        <ul>
                            <li>Üretim süreçlerini farklı merkezlere göre planlayabilirsiniz</li>
                            <li>Kapasite yönetimi yapabilirsiniz</li>
                            <li>Maliyetleri iş merkezlerine göre dağıtabilirsiniz</li>
                            <li>Verimlilik analizleri yapabilirsiniz</li>
                        </ul>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <span>Yeni iş merkezi nasıl eklenir?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Yeni iş merkezi eklemek için:</p>
                        <ol>
                            <li>"İş Merkezleri" sayfasına gidin</li>
                            <li>"Yeni İş Merkezi Ekle" butonuna tıklayın</li>
                            <li>İş merkezi adı, açıklaması ve diğer gerekli bilgileri girin</li>
                            <li>Kayıt butonuna tıklayarak iş merkezini sisteme ekleyin</li>
                        </ol>
                    </div>
                </div>
            </div>

            <div class="faq-category" id="stok-operasyon">
                <h2 class="category-header"><i class="fas fa-exchange-alt"></i> Manuel Stok Operasyonları</h2>

                <div class="faq-item">
                    <div class="faq-question">
                        <span>Manuel stok işlemleri ne zaman yapılır?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Manuel stok işlemleri, otomatik sistemlerin yetersiz kaldığı durumlarda yapılır:</p>
                        <ul>
                            <li>Stok sayım fazlası veya eksiği için düzeltme</li>
                            <li>Bozulma, kayıp veya hırsızlık durumlarında</li>
                            <li>İade işlemleri sonrası stok düzeltme</li>
                            <li>Yanlış giriş düzeltmeleri</li>
                        </ul>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <span>Manuel stok hareketi nasıl yapılır?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Manuel stok hareketi yapmak için:</p>
                        <ol>
                            <li>"Manuel Stok Hareket" sayfasına gidin</li>
                            <li>İşlem türünü (giriş/çıkış) seçin</li>
                            <li>Ürünü, miktarı ve açıklamayı girin</li>
                            <li>İşlem tarihini belirleyin</li>
                            <li>"Kaydet" butonuna tıklayarak hareketi gerçekleştirin</li>
                        </ol>
                    </div>
                </div>
            </div>

            <div class="faq-category" id="stok-hareketleri">
                <h2 class="category-header"><i class="fas fa-exchange-alt"></i> Stok Hareketleri</h2>

                <div class="faq-item">
                    <div class="faq-question">
                        <span>Stok hareketleri neleri içerir?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Stok hareketleri, ürünlerin stoğa giriş ve çıkışlarını takip etmeyi sağlar:</p>
                        <ul>
                            <li>Alım kayıtları</li>
                            <li>Üretim girişleri</li>
                            <li>Satış çıkışları</li>
                            <li>Transfer hareketleri</li>
                            <li>Manuel düzeltmeler</li>
                            <li>İade hareketleri</li>
                        </ul>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <span>Stok hareketlerini nasıl analiz edebilirim?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Stok hareketlerini analiz etmek için "Stok Hareket Raporu" sayfasını kullanabilirsiniz:</p>
                        <ul>
                            <li>Tarih aralığına göre filtreleme</li>
                            <li>Ürün bazında detaylı analiz</li>
                            <li>Hareket türüne göre sınıflandırma</li>
                            <li>Kullanıcı bazında işlem takibi</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="faq-category" id="finans">
                <h2 class="category-header"><i class="fas fa-wallet"></i> Finans & Maliyet</h2>

                <div class="faq-item">
                    <div class="faq-question">
                        <span>Maliyet hesaplamaları nasıl yapılır?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Maliyet hesaplamaları, ürünlerin üretim ve satış maliyetlerini belirlemek için yapılır:</p>
                        <ul>
                            <li>Ham madde maliyeti</li>
                            <li>İşçilik maliyeti</li>
                            <li>Genel üretim giderleri</li>
                            <li>İş merkezi maliyetleri</li>
                        </ul>
                        <p>Sistemde "Maliyet Hesaplama" modülü bu işlemleri otomatik olarak gerçekleştirebilir.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <span>Finansal raporlar nasıl alınır?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Finansal raporlar aşağıdaki sayfalardan alınabilir:</p>
                        <ul>
                            <li>"Maliyet Raporu": Ürünlerin maliyet detayları</li>
                            <li>"İşletme Maliyeti Raporu": Genel işletme maliyetleri</li>
                            <li>"Satış Raporları": Müşteri bazında satış analizleri</li>
                            <li>"Gider Raporları": Gider kalemlerinin analizi</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="faq-category" id="gider">
                <h2 class="category-header"><i class="fas fa-money-bill-wave"></i> Gider Yönetimi</h2>

                <div class="faq-item">
                    <div class="faq-question">
                        <span>Gider yönetimi sistemi nedir?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Gider yönetimi sistemi, şirketin sabit ve değişken giderlerini takip etmeyi ve yönetmeyi sağlar.</p>
                        <p>Gider kalemleri:</p>
                        <ul>
                            <li>Kira ve aidat giderleri</li>
                            <li>Elektrik, su, doğalgaz</li>
                            <li>Personel maaş ve primleri</li>
                            <li>Tedarikçi ödemeleri</li>
                            <li>Diğer işletme giderleri</li>
                        </ul>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <span>Gider kaydı nasıl yapılır?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Gider kaydı oluşturmak için:</p>
                        <ol>
                            <li>"Gider Yönetimi" sayfasına gidin</li>
                            <li>"Yeni Gider Ekle" butonuna tıklayın</li>
                            <li>Gider türünü, açıklamasını, tutarını ve tarihini girin</li>
                            <li>"Kaydet" butonuyla işlemi tamamlayın</li>
                        </ol>
                    </div>
                </div>
            </div>

            <div class="faq-category" id="loglar">
                <h2 class="category-header"><i class="fas fa-clipboard-list"></i> Sistem Günlükleri</h2>

                <div class="faq-item">
                    <div class="faq-question">
                        <span>Sistem günlükleri (loglar) nedir?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Sistem günlükleri, kullanıcıların sisteme yaptığı tüm işlemleri kaydeder:</p>
                        <ul>
                            <li>Giriş ve çıkış kayıtları</li>
                            <li>Veri ekleme, güncelleme ve silme işlemleri</li>
                            <li>Finansal işlemler</li>
                            <li>Stok hareketleri</li>
                        </ul>
                        <p>Bu kayıtlar güvenlik ve denetim amaçlıdır.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <span>Log raporları nasıl alınır?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Log raporları almak için:</p>
                        <ol>
                            <li>"Log Raporları" sayfasına gidin</li>
                            <li>Tarih aralığı ve diğer filtreleri belirleyin</li>
                            <li>"Rapor Al" butonuna tıklayın</li>
                            <li>İlgili işlemleri inceleyin</li>
                        </ol>
                    </div>
                </div>
            </div>

            <div class="faq-category" id="cerceve-sozlesmeler">
                <h2 class="category-header"><i class="fas fa-file-contract"></i> Çerçeve Sözleşmeler</h2>

                <div class="faq-item">
                    <div class="faq-question">
                        <span>Çerçeve sözleşmeler nedir?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Çerçeve sözleşmeler, tedarikçilerle yapılan malzeme alım anlaşmalarının yönetildiği alandır.</p>
                        <p>Bu sözleşmeler sayesinde:</p>
                        <ul>
                            <li>Tedarikçiyle anlaşmalı fiyatlar belirlenir</li>
                            <li>Sipariş miktarları ve teslimat tarihleri planlanır</li>
                            <li>Malzeme kalite standartları tanımlanır</li>
                        </ul>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <span>Yeni çerçeve sözleşme nasıl oluşturulur?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Yeni çerçeve sözleşme oluşturmak için:</p>
                        <ol>
                            <li>"Çerçeve Sözleşmeler" sayfasına gidin</li>
                            <li>"Yeni Sözleşme Oluştur" butonuna tıklayın</li>
                            <li>Tedarikçiyi seçin ve sözleşme detaylarını girin</li>
                            <li>Malzemeleri ve fiyat bilgilerini ekleyin</li>
                            <li>Sözleşmeyi onaylayarak işlemi tamamlayın</li>
                        </ol>
                    </div>
                </div>
            </div>

            <div class="faq-category" id="raporlama">
                <h2 class="category-header"><i class="fas fa-chart-bar"></i> Raporlama</h2>

                <div class="faq-item">
                    <div class="faq-question">
                        <span>Sistemde hangi türde raporlar alınabilir?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Sistemde aşağıdaki türde raporlar alınabilir:</p>
                        <ul>
                            <li>Stok durum raporları</li>
                            <li>Satış performans raporları</li>
                            <li>Maliyet analiz raporları</li>
                            <li>Ürün bazlı üretim raporları</li>
                            <li>Müşteri bazlı satış raporları</li>
                            <li>Finansal özet raporlar</li>
                        </ul>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <span>Otomatik raporlar alınabilir mi?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Evet, sistemde bazı raporlar belirli aralıklarla otomatik olarak oluşturulabilir. Bu özellik:</p>
                        <ul>
                            <li>Günlük stok durum raporları</li>
                            <li>Haftalık satış özetleri</li>
                            <li>Aylık maliyet analizleri</li>
                            <li>Yıllık performans raporları</li>
                        </ul>
                        <p>Otomatik raporlar e-posta ile ilgili kişilere gönderilebilir.</p>
                    </div>
                </div>
            </div>

            <div class="faq-category" id="raporlar">
                <h2 class="category-header"><i class="fas fa-chart-pie"></i> Raporlar</h2>

                <div class="faq-item">
                    <div class="faq-question">
                        <span>Detaylı analiz raporları nelerdir?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Detaylı analiz raporları arasında şunlar yer alır:</p>
                        <ul>
                            <li>"Analiz Dinamik": Farklı kriterlerle detaylı analiz</li>
                            <li>"En Çok Satan Ürünler": Satış performansı analizi</li>
                            <li>"Kritik Stok Raporları": Stok seviyesi düşük ürünler</li>
                            <li>"Müşteri Satış Raporu": Müşteri bazlı satış analizi</li>
                            <li>"Tedarikçi Ödeme Raporları": Tedarikçilere yapılan ödemeler</li>
                        </ul>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <span>Raporları Excel'e nasıl aktarabilirim?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Raporları Excel formatına aktarmak için:</p>
                        <ul>
                            <li>İlgili rapor sayfasına gidin</li>
                            <li>"Excel'e Aktar" butonuna tıklayın</li>
                            <li>Dosya otomatik olarak indirilecektir</li>
                        </ul>
                        <p>Bu işlem için php-mbstring eklentisinin yüklü olması gerekir.</p>
                    </div>
                </div>
            </div>

            <div class="faq-category" id="siparis-takibi">
                <h2 class="category-header"><i class="fas fa-clipboard-list"></i> Sipariş Takibi</h2>

                <div class="faq-item">
                    <div class="faq-question">
                        <span>Sipariş takibi nasıl yapılır?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Sipariş takibi için aşağıdaki sayfalar kullanılabilir:</p>
                        <ul>
                            <li>"Müşteri Siparişleri": Tüm siparişlerin listesi</li>
                            <li>"Sipariş Detayı": Belirli bir siparişin detaylı takibi</li>
                            <li>"Ürün Sipariş Durumu": Belirli bir ürünün sipariş durumu</li>
                        </ul>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <span>Sipariş durumu nasıl güncellenir?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Sipariş durumunu güncellemek için:</p>
                        <ol>
                            <li>"Müşteri Siparişleri" sayfasına gidin</li>
                            <li>Güncellemek istediğiniz siparişi bulun</li>
                            <li>"Düzenle" veya "Durum Güncelle" butonuna tıklayın</li>
                            <li>Yeni durumu seçin ve açıklamayı ekleyin</li>
                            <li>Değişikliği kaydedin</li>
                        </ol>
                    </div>
                </div>
            </div>

            <div class="faq-category" id="malzeme-siparisleri">
                <h2 class="category-header"><i class="fas fa-box-open"></i> Malzeme Siparişleri</h2>

                <div class="faq-item">
                    <div class="faq-question">
                        <span>Malzeme siparişleri nasıl yönetilir?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Malzeme siparişleri, hammaddelerin tedarikçilerden alınması için kullanılır:</p>
                        <ul>
                            <li>Tedarikçiyle çerçeve sözleşme yapılır</li>
                            <li>Üretim planına göre malzeme ihtiyaçları belirlenir</li>
                            <li>Malzeme siparişleri oluşturulur</li>
                            <li>Siparişler takip edilir ve teslim alındığında kapanır</li>
                        </ul>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <span>Malzeme siparişi nasıl oluşturulur?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Malzeme siparişi oluşturmak için:</p>
                        <ol>
                            <li><strong>Navigasyon:</strong> "Malzeme Siparişleri" sayfasına gidin.</li>
                            <li><strong>Yeni Sipariş:</strong> "Yeni Malzeme Siparişi" butonuna tıklayın.</li>
                            <li><strong>Tedarikçi Seçin:</strong> Sipariş verilecek tedarikçiyi seçin.</li>
                            <li><strong>Malzeme Ekle:</strong> Gerekli malzemeleri ve miktarlarını girin.</li>
                            <li><strong>Onayla:</strong> Siparişi onaylayarak işlemi tamamlayın.</li>
                        </ol>
                    </div>
                </div>
            </div>

            <div class="faq-category" id="urun-akisi">
                <h2 class="category-header"><i class="fas fa-project-diagram"></i> Ürün Akışı</h2>

                <div class="faq-item">
                    <div class="faq-question">
                        <span>Ürün akışı nedir?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Ürün akışı, bir ürünün hammaddedan nihai ürüne dönüşmesine kadar olan tüm süreçlerini takip eden yapıdır:</p>
                        <ul>
                            <li>Ham madde temini</li>
                            <li>Üretim planlama</li>
                            <li>İş merkezlerinde işleme</li>
                            <li>Kalite kontrol</li>
                            <li>Stoklama</li>
                            <li>Satış ve teslimat</li>
                        </ul>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <span>Ürün akışını nasıl analiz edebilirim?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Ürün akışını analiz etmek için şu sayfaları kullanabilirsiniz:</p>
                        <ul>
                            <li>"Ürün Ağaçları": Ürünün bileşenlerini gösterir</li>
                            <li>"Stok Hareket Raporu": Ürünün hareketlerini takip eder</li>
                            <li>"Üretim Emirleri": Üretim süreçlerini gösterir</li>
                            <li>"Sipariş Takibi": Sipariş bazında ürün akışını gösterir</li>
                        </ul>
                    </div>
                </div>
            </div>



            <div class="faq-category" id="yardim-destek">
                <h2 class="category-header"><i class="fas fa-headset"></i> Yardım & Destek</h2>

                <div class="faq-item">
                    <div class="faq-question">
                        <span>Yardıma ihtiyacım olduğunda ne yapmalıyım?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Herhangi bir konuda yardıma ihtiyacınız olduğunda aşağıdaki yolları deneyebilirsiniz:</p>
                        <ul>
                            <li>Sistemdeki "Sık Sorulan Sorular" (bu sayfa) bölümünü inceleyin</li>
                            <li>Sistem yöneticinizle iletişime geçin</li>
                            <li>Eğitim materyallerini gözden geçirin</li>
                            <li>Meslektaşlarınızdan destek alın</li>
                        </ul>
                        <p>Yardım talebiniz olması durumunda, sorunun ne zaman oluştuğunu ve hangi adımları uyguladığınızı belirtmeniz çözüm sürecini hızlandıracaktır.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <span>Sistemde bir hata ile karşılaşırsam ne yapmalıyım?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Sistemde bir hata ile karşılaşırsanız:</p>
                        <ol>
                            <li>Sayfayı yenilemeyi deneyin (F5 tuşu)</li>
                            <li>Tarayıcınızın çerezlerini temizleyin</li>
                            <li>Farklı bir tarayıcıda sistemi açmayı deneyin</li>
                            <li>Hata devam ederse sistem yöneticinize bilgi verin</li>
                        </ol>
                        <div class="info-box">
                            <i class="fas fa-info-circle"></i> <strong>Not:</strong> Sistemde teknik sorunlar sistem yöneticileri tarafından takip edilmektedir.
                        </div>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <span>Eğitim materyallerine nasıl erişebilirim?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Eğitim materyalleri ve kullanıcı klavuzları sistem yöneticinizden temin edilebilir. Bu materyaller:</p>
                        <ul>
                            <li>Sistem kullanımı konularını kapsar</li>
                            <li>Sık kullanılan işlevlerin nasıl yapılacağını anlatır</li>
                            <li>Yeni çıkan özellikler hakkında bilgi verir</li>
                        </ul>
                        <p>Güncel eğitim materyalleri periyodik olarak sunulmaktadır.</p>
                    </div>
                </div>
            </div>

            <div class="faq-category" id="yedekleme">
                <h2 class="category-header"><i class="fas fa-database"></i> Yedekleme ve Kurtarma</h2>

                <div class="faq-item">
                    <div class="faq-question">
                        <span>Veri güvenliği ve yedekleme işlemleri nasıl yapılır?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Veri güvenliği ve yedekleme işlemleri sistem yöneticileri tarafından otomatik olarak yapılmaktadır. Bu işlemler sayesinde:</p>
                        <ul>
                            <li>Verileriniz düzenli aralıklarla güvenli bir şekilde yedeklenir</li>
                            <li>Olası sistem hatalarında verileriniz korunur</li>
                            <li>Her zaman güncel bir veri kopyası sistematik olarak tutulur</li>
                        </ul>
                        <p>Sizlerin bu işlemler için herhangi bir eylem gerçekleştirmesine gerek yoktur, sistem yöneticileri gerekli önlemleri almaktadır.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <span>Acil durum kurtarma işlemi nasıl yapılır?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Acil durum kurtarma işlemi için:</p>
                        <ol>
                            <li>Yedekleme sayfasına gidin</li>
                            <li>Son alınan güvenilir yedek dosyasını seçin</li>
                            <li>"Geri Yükle" butonuna tıklayın</li>
                            <li>İşlem sırasında sistem bakım moduna alınmalıdır</li>
                        </ol>
                        <div class="warning-box">
                            <i class="fas fa-exclamation-triangle"></i> <strong>Uyarı:</strong> Geri yükleme işlemi mevcut tüm verilerin üzerine yazacaktır. Devam etmeden önce kullanıcıları bilgilendirin.
                        </div>
                    </div>
                </div>
            </div>

            <div class="faq-category" id="hata-cozum">
                <h2 class="category-header"><i class="fas fa-bug"></i> Hata Giderme</h2>

                <div class="faq-item">
                    <div class="faq-question">
                        <span>Sistemde bir sorunla karşılaşırsam ne yapmalıyım?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Sistemde bir sorunla karşılaşırsanız aşağıdaki adımları izleyin:</p>
                        <ul>
                            <li>Sayfayı yenileyin (F5 tuşu)</li>
                            <li>Tarayıcınızın çerezlerini temizleyin</li>
                            <li>Farklı bir tarayıcı ile sistemi açmayı deneyin</li>
                            <li>Sorun devam ederse sistem yöneticinize bildirin</li>
                        </ul>
                        <p>Sistemde meydana gelen hatalar sistem yöneticileri tarafından otomatik olarak izlenmekte ve çözüm süreci başlatılmaktadır.</p>
                        <div class="info-box">
                            <i class="fas fa-info-circle"></i> <strong>Not:</strong> Sorunu bildirirken ne zaman ve hangi işlem sırasında oluştuğunu belirtmeniz çözüm sürecini hızlandırır.
                        </div>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <span>Giriş yapamıyorum, ne yapmalıyım?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Giriş yapamıyorsanız aşağıdaki adımları deneyin:</p>
                        <ul>
                            <li>Kullanıcı adı ve şifrenizin doğru olduğundan emin olun</li>
                            <li>Büyük/küçük harfe duyarlı olduğundan emin olun</li>
                            <li>Tarayıcınızın çerezlerini temizleyin</li>
                            <li>Farklı bir tarayıcı ile deneyin</li>
                            <li>Giriş bilgilerinizin geçerli olduğundan emin olun</li>
                        </ul>
                        <p>Şifrenizi unuttuysanız sistem yöneticinizden yeni bir şifre talep edebilirsiniz.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <span>Sayfalar yavaş açılıyor, ne yapabilirim?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Sayfaların yavaş açılmasının birkaç nedeni olabilir:</p>
                        <ul>
                            <li>Bağlantı hızınız düşük olabilir</li>
                            <li>Aynı anda çok sayıda işlem yapılıyor olabilir</li>
                            <li>İnternet bağlantınızı kontrol edin</li>
                            <li>Farklı bir tarayıcı kullanmayı deneyin</li>
                            <li>Tarayıcı çerezlerinizi temizleyin</li>
                        </ul>
                        <p>Sistem performansı genel olarak sistem yöneticileri tarafından optimize edilmektedir.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <span>Sistemde "Sunucu Hatası" mesajı alıyorum, ne yapmalıyım?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer">
                        <p>Bu tür hatalar genellikle sistem yöneticileri tarafından otomatik olarak tespit edilir ve düzeltilir. Siz şu adımları deneyebilirsiniz:</p>
                        <ul>
                            <li>Sayfayı yenileyin (F5 tuşu)</li>
                            <li>Tarayıcınızı kapatıp tekrar açın</li>
                            <li>Bilgisayarınızı yeniden başlatın</li>
                            <li>Sorun devam ederse sistem yöneticinize bildirin</li>
                        </ul>
                        <p>Teknik hatalar sistem yöneticileri tarafından izlenmekte ve gerekli önlemler alınmaktadır.</p>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // FAQ accordion functionality
        document.querySelectorAll('.faq-question').forEach(question => {
            question.addEventListener('click', () => {
                const answer = question.nextElementSibling;
                const isOpen = answer.classList.contains('open');

                // Close all answers
                document.querySelectorAll('.faq-answer').forEach(ans => {
                    ans.classList.remove('open');
                });

                // Toggle current answer
                if(!isOpen) {
                    answer.classList.add('open');
                }
            });
        });

        // Animation for categories as they come into view
        const observerOptions = {
            root: null,
            rootMargin: '0px',
            threshold: 0.1
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-in');
                }
            });
        }, observerOptions);

        document.querySelectorAll('.faq-category').forEach(category => {
            observer.observe(category);
        });
    </script>
</body>
</html>
