# PHP Extensions Required

The following PHP extensions are required for full functionality of the application:

## Required Extensions:
- php-curl - For Telegram notifications in backup functionality
- php-mbstring - For Excel export functionality
- php-mysql - For database connectivity (already installed)

## Installation:
```bash
sudo apt-get update
sudo apt-get install -y php-curl php-mbstring
```

After installing extensions, restart the PHP service:
```bash
sudo systemctl restart php8.1-fpm
```