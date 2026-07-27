FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    ca-certificates \
    libssl-dev \
    && docker-php-ext-install mysqli pdo_mysql \
    && update-ca-certificates \
    && rm -rf /var/lib/apt/lists/*

COPY . /var/www/html/

RUN chown -R www-data:www-data /var/www/html && \
    a2enmod rewrite

EXPOSE 80
