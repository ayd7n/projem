@echo off
echo MySQL Repair Script
echo ===================
echo.
echo This script will reset your MySQL installation.
echo IMPORTANT: This will DELETE all existing databases!
echo Make sure to backup your important data first.
echo.
set /p confirm="Type 'YES' if you want to proceed: "
if /I NOT "%confirm%"=="YES" goto cancel

echo.
echo Stopping any running MySQL services...
net stop mysql 2>nul
taskkill /f /im mysqld.exe 2>nul

echo.
echo Backing up current data directory...
if exist "C:\xampp\mysql\data_backup" rmdir /s /q "C:\xampp\mysql\data_backup"
if exist "C:\xampp\mysql\data" move "C:\xampp\mysql\data" "C:\xampp\mysql\data_backup"
echo Backup created as C:\xampp\mysql\data_backup

echo.
echo Creating new data directory...
mkdir "C:\xampp\mysql\data"

echo.
echo Installing MySQL with default settings...
"C:\xampp\mysql\bin\mysqld" --initialize-insecure --user=mysql --datadir="C:\xampp\mysql\data"

if %errorlevel% neq 0 (
    echo.
    echo Error occurred during MySQL reinstallation!
    echo Please check the error messages above.
    pause
    exit /b 1
)

echo.
echo MySQL has been successfully reinstalled.
echo You can now start MySQL through XAMPP Control Panel.
echo Remember to recreate your databases through phpMyAdmin.

echo.
echo Starting XAMPP Control Panel for you...
start "" "C:\xampp\xampp-control.exe"

pause
exit /b 0

:cancel
echo.
echo Operation cancelled by user.
pause