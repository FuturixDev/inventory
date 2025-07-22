FROM php:8.2-apache

# 啟用 mysqli
RUN docker-php-ext-install mysqli

# 啟用 mod_rewrite（.htaccess 會用到）
RUN a2enmod rewrite

# 複製子目錄中的網站內容（你整包網站放在 inventory-system 資料夾）
COPY inventory-system/ /var/www/html/

# 設定 .htaccess 可用
RUN chown -R www-data:www-data /var/www/html
RUN chmod -R 755 /var/www/html
