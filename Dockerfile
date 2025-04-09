# Verwende das PHP-Image mit Apache
FROM php:8.4-apache

# Installiere PDO, PDO_MySQL und MySQLi
RUN apt-get update && \
    apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql mysqli

# Aktiviert Apache-Modul rewrite (falls erforderlich)
RUN service apache2 restart
RUN a2enmod rewrite