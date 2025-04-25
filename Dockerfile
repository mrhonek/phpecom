FROM php:8.2-apache

WORKDIR /var/www/html

# Install dependencies
RUN apt-get update && apt-get install -y \
    libpq-dev \
    zip \
    unzip \
    git

# Enable Apache modules and PHP extensions
RUN a2enmod rewrite
RUN docker-php-ext-install pdo pdo_pgsql

# Copy backend files
COPY backend/public/ /var/www/html/

# Set permissions
RUN chown -R www-data:www-data /var/www/html

# Expose port
EXPOSE $PORT

# Configure Apache for dynamic port
RUN echo 'Listen ${PORT}' > /etc/apache2/ports.conf
RUN sed -i 's/80/${PORT}/g' /etc/apache2/sites-available/000-default.conf

# Start Apache
CMD ["apache2-foreground"] 