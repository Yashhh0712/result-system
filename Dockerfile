FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    libssl-dev \
    && docker-php-ext-configure openssl \
    && docker-php-ext-install pdo pdo_mysql sockets \
    && rm -rf /var/lib/apt/lists/*

RUN curl -sS https://letsencrypt.org/certs/isrgrootx1.pem -o /etc/ssl/certs/tidb-ca.pem \
    && a2enmod rewrite

COPY . /var/www/html/
COPY .htaccess /var/www/html/.htaccess

EXPOSE 8080
CMD ["php", "-S", "0.0.0.0:8080", "-t", "/var/www/html", "/var/www/html/router.php"]
