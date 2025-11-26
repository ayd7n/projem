<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IDO KOZMETIK - Sistem Hatası</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Ubuntu', sans-serif;
            background: #f5f5f5;
            color: #333;
            padding: 20px;
        }

        .error-container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .error-header {
            background: #d32f2f;
            color: white;
            padding: 20px 30px;
            border-bottom: 3px solid #b71c1c;
        }

        .error-header h1 {
            font-size: 24px;
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .error-icon {
            width: 30px;
            height: 30px;
        }

        .error-header p {
            font-size: 14px;
            opacity: 0.9;
        }

        .error-body {
            padding: 30px;
        }

        .error-message {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px 20px;
            margin-bottom: 30px;
            border-radius: 4px;
        }

        .error-message h2 {
            color: #856404;
            font-size: 18px;
            margin-bottom: 8px;
        }

        .error-message p {
            color: #856404;
            font-size: 14px;
            line-height: 1.6;
        }

        .section {
            margin-bottom: 30px;
        }

        .section h3 {
            font-size: 16px;
            color: #555;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 2px solid #e0e0e0;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        .data-table th {
            background: #f5f5f5;
            padding: 10px;
            text-align: left;
            font-weight: 600;
            border: 1px solid #ddd;
        }

        .data-table td {
            padding: 8px 10px;
            border: 1px solid #ddd;
        }

        .data-table tr:nth-child(even) {
            background: #fafafa;
        }

        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: 600;
        }

        .status-error {
            background: #ffebee;
            color: #c62828;
        }

        .status-warning {
            background: #fff3e0;
            color: #e65100;
        }

        .timestamp {
            color: #999;
            font-size: 12px;
            font-style: italic;
        }

        .server-info {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            color: #666;
            margin-top: 20px;
        }

        .server-info div {
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-header">
            <h1>
                <svg class="error-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
                </svg>
                SISTEM HATASI
            </h1>
            <p>Veritabanı Bağlantı Hatası - Servis Geçici Olarak Kullanılamıyor</p>
        </div>

        <div class="error-body">
            <div class="error-message">
                <h2>⚠️ Kritik Hata: Veritabanı Senkronizasyon Sorunu</h2>
                <p>
                    Sistem veritabanı ile bağlantı kuramadı. Lütfen sistem yöneticinizle iletişime geçin.<br>
                    <strong>Hata Kodu:</strong> ERR_DB_SYNC_FAILED_0x8472<br>
                    <strong>Zaman:</strong> <span class="timestamp">2018-03-15 14:23:47</span>
                </p>
            </div>

            <div class="section">
                <h3>📦 Son Ürün Kayıtları (2018)</h3>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Ürün Kodu</th>
                            <th>Ürün Adı</th>
                            <th>Kategori</th>
                            <th>Stok</th>
                            <th>Durum</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>PRF-2018-001</td>
                            <td>Lavanta Esans 50ml</td>
                            <td>Esanslar</td>
                            <td>--</td>
                            <td><span class="status-badge status-error">HATA</span></td>
                        </tr>
                        <tr>
                            <td>PRF-2018-045</td>
                            <td>Gül Kolonyası 250ml</td>
                            <td>Kolonyalar</td>
                            <td>--</td>
                            <td><span class="status-badge status-error">HATA</span></td>
                        </tr>
                        <tr>
                            <td>PRF-2018-089</td>
                            <td>Limon Esansı 100ml</td>
                            <td>Esanslar</td>
                            <td>--</td>
                            <td><span class="status-badge status-warning">UYARI</span></td>
                        </tr>
                        <tr>
                            <td>PRF-2018-112</td>
                            <td>Vanilya Aroması</td>
                            <td>Aromalar</td>
                            <td>--</td>
                            <td><span class="status-badge status-error">HATA</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="section">
                <h3>📋 Son Sipariş Kayıtları (2017)</h3>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Sipariş No</th>
                            <th>Müşteri</th>
                            <th>Tarih</th>
                            <th>Tutar</th>
                            <th>Durum</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>SIP-2017-1247</td>
                            <td>ABC Kozmetik Ltd.</td>
                            <td>2017-11-23</td>
                            <td>--</td>
                            <td><span class="status-badge status-error">SENKRON HATASI</span></td>
                        </tr>
                        <tr>
                            <td>SIP-2017-1298</td>
                            <td>XYZ Parfümeri</td>
                            <td>2017-12-05</td>
                            <td>--</td>
                            <td><span class="status-badge status-error">SENKRON HATASI</span></td>
                        </tr>
                        <tr>
                            <td>SIP-2017-1301</td>
                            <td>Güzellik Merkezi A.Ş.</td>
                            <td>2017-12-12</td>
                            <td>--</td>
                            <td><span class="status-badge status-warning">VERİ EKSİK</span></td>
                        </tr>
                        <tr>
                            <td>SIP-2017-1356</td>
                            <td>Toptan Kozmetik</td>
                            <td>2017-12-28</td>
                            <td>--</td>
                            <td><span class="status-badge status-error">SENKRON HATASI</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="server-info">
                <div><strong>SERVER ERROR LOG:</strong></div>
                <div>[2018-03-15 14:23:47] ERROR: Failed to connect to database server</div>
                <div>[2018-03-15 14:23:47] ERROR: mysqli_connect(): (HY000/2002): Connection timed out</div>
                <div>[2018-03-15 14:23:48] WARNING: Attempting reconnection... (Attempt 1/3)</div>
                <div>[2018-03-15 14:23:51] ERROR: Reconnection failed</div>
                <div>[2018-03-15 14:23:51] ERROR: Database synchronization corrupted</div>
                <div>[2018-03-15 14:23:51] CRITICAL: Unable to retrieve current data</div>
                <div>[2018-03-15 14:23:51] INFO: Displaying last cached data from 2017-2018</div>
                <div style="margin-top: 10px; color: #d32f2f;"><strong>⚠️ UYARI: Gösterilen veriler güncel değildir!</strong></div>
            </div>
        </div>
    </div>
</body>
</html>
