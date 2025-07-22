FROM php:8.2-apache

# 安裝 mysqli 擴充與必要套件
RUN apt-get update && \
    apt-get install -y libpng-dev libjpeg-dev libonig-dev libxml2-dev zip unzip && \
    docker-php-ext-install mysqli && \
    a2enmod rewrite

# 複製專案進 Web 根目錄
COPY . /var/www/html/

# 設定目錄權限（可選）
RUN chown -R www-data:www-data /var/www/html
