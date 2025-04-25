FROM php:8.2-fpm

# Set working directory
WORKDIR /var/www

# Install dependencies
RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libzip-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    locales \
    zip \
    jpegoptim optipng pngquant gifsicle \
    vim \
    unzip \
    git \
    curl \
    libpq-dev

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install extensions
RUN docker-php-ext-install pdo pdo_pgsql pgsql zip exif pcntl
RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install gd

# Install composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy backend files
COPY backend/ /var/www/

# Set permissions
RUN chown -R www-data:www-data /var/www

# Install dependencies
WORKDIR /var/www
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Generate application key if not exists
RUN php artisan key:generate --force

# Expose port for the application
EXPOSE $PORT

# Start the application
CMD php artisan migrate --force && \
    php artisan db:seed --force && \
    php artisan serve --host=0.0.0.0 --port=$PORT 