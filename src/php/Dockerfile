FROM php:8.3.1RC3-cli-alpine3.18

RUN docker-php-ext-install mysqli
WORKDIR /app
COPY . .
RUN mv php.ini "$PHP_INI_DIR/conf.d/php.ini"

EXPOSE 9000
CMD ["php", "-S", "0.0.0.0:9000"]
