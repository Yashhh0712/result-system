FROM php:8.2-apache

RUN apt-get update && apt-get install -y ca-certificates \
    && update-ca-certificates \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

COPY . /var/www/html/
COPY .htaccess /var/www/html/.htaccess

EXPOSE 8080
CMD ["php", "-S", "0.0.0.0:8080", "-t", "/var/www/html", "/var/www/html/router.php"]
