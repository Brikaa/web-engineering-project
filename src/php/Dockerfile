FROM php:8.3.1RC3-cli-alpine3.18

RUN docker-php-ext-install mysqli
WORKDIR /app
COPY . .
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" && mv php.conf "$PHP_INI_DIR/conf.d/php.conf"

EXPOSE 9000
CMD ["php", "-S", "0.0.0.0:9000"]
