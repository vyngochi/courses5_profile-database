FROM php:8.1-cli

WORKDIR /app

COPY . /app

RUN docker-php-ext-install pdo pdo_mysql

EXPOSE 10000

CMD ["php", "-S", "0.0.0.0:10000"]