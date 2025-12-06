$session = New-Object Microsoft.PowerShell.Commands.WebRequestSession
Invoke-WebRequest -Uri 'http://localhost/projem/login.php' -Method POST -Body @{username='admin@parfum.com';password='12345'} -SessionVariable session
$content = Invoke-WebRequest -Uri 'http://localhost/projem/analiz_dinamik.php' -WebSession $session
$content.Content
