<?php
// Set HTTP status code to 503 Service Unavailable
header('HTTP/1.1 503 Service Unavailable');
header('Retry-After: 3600'); // Tell search engines to check back in 1 hour
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bakımdayız - IDO KOZMETIK</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4a0e63;
            --text-primary: #212529;
            --text-secondary: #6c757d;
            --bg-color: #f8f9fa;
        }
        body {
            font-family: 'Ubuntu', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-primary);
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            text-align: center;
        }
        .container {
            max-width: 600px;
            margin: 2rem;
            padding: 3rem;
            background: white;
            border-radius: 12px;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
        }
        h1 {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 1rem;
        }
        p {
            font-size: 1.1rem;
            color: var(--text-secondary);
            line-height: 1.6;
            margin-bottom: 2rem;
        }
        .icon {
            font-size: 4rem;
            color: var(--primary);
            margin-bottom: 2rem;
        }
        .logout-btn {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            background-color: var(--primary);
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 500;
            transition: background-color 0.3s ease;
        }
        .logout-btn:hover {
            background-color: #3a0b4f; /* A darker shade of primary */
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">
            <!-- Using a simple unicode gear icon as FontAwesome isn't included -->
            &#9881;
        </div>
        <h1>Sistem Bakımda</h1>
        <p>
            Uygulamamızı daha iyi hale getirmek için kısa bir süreliğine bakım çalışması yapıyoruz.
            Anlayışınız için teşekkür eder, en kısa sürede tekrar hizmetinizde olmayı dileriz.
        </p>
        <a href="logout.php" class="logout-btn">Çıkış Yap</a>
    </div>
</body>
</html>
