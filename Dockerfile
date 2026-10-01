FROM php:8.3-cli

RUN docker-php-ext-install pdo pdo_mysql

WORKDIR /app
COPY . /app

EXPOSE 8080

CMD ["sh", "-c", "exec php -S 0.0.0.0:${PORT:-8080} -t /app"]
