@echo off
REM --- Ayarlar ---
set "KEY=sunucukeyi.key"
set "USER=ubuntu"
set "HOST=168.138.94.18"
set "SSH_EXE=%SystemRoot%\System32\OpenSSH\ssh.exe"

REM --- Kontroller ---
if not exist "%SSH_EXE%" (
  echo SSH executable bulunamadi: %SSH_EXE%
  echo Windows OpenSSH yüklü olmayabilir. Powershell veya Putty kullanabilirsiniz.
  pause
  exit /b 1
)

if not exist "%KEY%" (
  echo SSH anahtar dosyasi bulunamadi: "%KEY%"
  echo Lütfen anahtar yolunu kontrol edin.
  pause
  exit /b 1
)

echo Baglaniyor %USER%@%HOST% using key "%KEY%" ...
"%SSH_EXE%" -i "%KEY%" %USER%@%HOST%

echo.
echo SSH oturumu sona erdi.
pause