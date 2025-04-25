FROM php:8.2-cli

WORKDIR /app

RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libpq-dev

RUN docker-php-ext-install pdo pdo_pgsql mbstring exif pcntl bcmath gd

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY backend/ /app/

RUN composer install --optimize-autoloader --no-interaction --no-progress

RUN php artisan key:generate --force

EXPOSE $PORT

CMD php artisan serve --host=0.0.0.0 --port=$PORT 