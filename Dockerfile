FROM php:8.5-cli

RUN docker-php-ext-install pdo_sqlite

WORKDIR /var/www/html

COPY api/ ./api/

EXPOSE 8000

CMD ["php", "-S", "0.0.0.0:8000", "-t", "/var/www/html"]
