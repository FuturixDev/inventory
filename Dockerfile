FROM php:8.2-apache

RUN docker-php-ext-install mysqli
RUN a2enmod rewrite

# ⛳ 正確複製整個 repo（專案檔案都在根目錄）
COPY . /var/www/html/

RUN chown -R www-data:www-data /var/www/html
