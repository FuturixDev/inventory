# 使用 PHP 8.2 搭配 Apache
FROM php:8.2-apache

# 安裝 mysqli extension
RUN docker-php-ext-install mysqli

# 啟用 Apache rewrite 模組
RUN a2enmod rewrite

# 複製所有專案檔案到 Apache 預設網頁根目錄
COPY . /var/www/html/

# 可選：設定資料夾權限（避免某些主機權限問題）
RUN chown -R www-data:www-data /var/www/html
