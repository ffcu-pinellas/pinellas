FROM php:8.2-apache

# Install system packages & PHP extensions
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    curl \
    libonig-dev \
    libxml2-dev \
    ca-certificates \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Configure Apache DocumentRoot to /var/www/html and enable AllowOverride All
RUN { \
    echo '<Directory /var/www/html>'; \
    echo '    Options -Indexes +FollowSymLinks'; \
    echo '    AllowOverride All'; \
    echo '    Require all granted'; \
    echo '</Directory>'; \
    echo '<Directory /var/www/html/storage>'; \
    echo '    Require all denied'; \
    echo '</Directory>'; \
    echo '<Directory /var/www/html/bootstrap>'; \
    echo '    Require all denied'; \
    echo '</Directory>'; \
    echo '<FilesMatch "^\.(env|git)">'; \
    echo '    Require all denied'; \
    echo '</FilesMatch>'; \
} > /etc/apache2/conf-available/laravel.conf \
    && a2enconf laravel

WORKDIR /var/www/html

# Copy application files
COPY . .

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --optimize-autoloader --no-interaction \
    && mkdir -p /var/www/html/assets/global/images /var/www/html/public/assets/global/images \
    && chown -R www-data:www-data /var/www/html/assets /var/www/html/public/assets /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 777 /var/www/html/assets /var/www/html/public/assets /var/www/html/storage /var/www/html/bootstrap/cache

# Setup entrypoint
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 80 10000

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
CMD ["apache2-foreground"]
