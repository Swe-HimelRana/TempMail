FROM php:8.2-apache-bullseye

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libc-client-dev \
    libkrb5-dev \
    libzip-dev \
    zip \
    unzip \
    sqlite3 \
    libsqlite3-dev \
    git \
    && rm -r /var/lib/apt/lists/*

# Configure and install PHP extensions
RUN docker-php-ext-configure imap --with-kerberos --with-imap-ssl \
    && docker-php-ext-install imap zip pdo pdo_sqlite

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy composer.json and lock first for caching
COPY composer.json composer.lock ./

# Install dependencies
RUN composer install --no-dev --optimize-autoloader

# Copy application files
COPY . .

# Change DocumentRoot to /var/www/html/public
RUN sed -i 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf

# Ensure data directory exists and set permissions
RUN mkdir -p /var/www/html/data && chown -R www-data:www-data /var/www/html/data

# Expose port 80
EXPOSE 80

