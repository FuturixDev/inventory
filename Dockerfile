FROM php:8.2-apache

# 安裝 mysqli 擴充 + mod_rewrite
RUN apt-get update && \
    docker-php-ext-install mysqli && \
    a2enmod rewrite

# 複製專案到 Apache 根目錄
COPY . /var/www/html/

# 可選：設定權限
RUN chown -R www-data:www-data /var/www/html
