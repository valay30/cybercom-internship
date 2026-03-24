FROM php:8.2-apache

# Enable mod_rewrite
RUN a2enmod rewrite

# Install dependencies for rdkafka
RUN apt-get update && apt-get install -y \
    librdkafka-dev \
    libcurl4-openssl-dev \
    && pecl install rdkafka redis \
    && docker-php-ext-enable rdkafka redis \
    && docker-php-ext-install curl

# Allow .htaccess overrides
RUN echo '<Directory /var/www/html>\n\
    Options Indexes FollowSymLinks\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' >> /etc/apache2/apache2.conf

# Raise upload limits for large CSV files
RUN echo "upload_max_filesize=100M\npost_max_size=100M" > /usr/local/etc/php/conf.d/uploads.ini

EXPOSE 80