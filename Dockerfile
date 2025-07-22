FROM php:8.2-apache

# 安裝 mysqli 擴充套件
RUN docker-php-ext-install mysqli

# 啟用 Apache rewrite 模組（支援 .htaccess）
RUN a2enmod rewrite

# 複製整個專案（根目錄）到 Apache 網頁根目錄
COPY . /var/www/html/

# 可選：設定權限，避免權限錯誤
RUN chown -R www-data:www-data /var/www/html
