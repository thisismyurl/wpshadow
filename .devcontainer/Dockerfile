# Use the official WordPress image as base
FROM wordpress:latest

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip \
    sudo \
    wget \
    libzip-dev \
    libxml2-dev \
    && rm -rf /var/lib/apt/lists/*

# Install required PHP extensions
RUN docker-php-ext-install \
    zip \
    && docker-php-ext-enable zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Grant sudo privileges to www-data user
RUN echo "www-data ALL=(ALL) NOPASSWD:ALL" >> /etc/sudoers

# Ensure proper permissions for Composer
RUN mkdir -p /var/www/.composer && chown -R www-data:www-data /var/www/.composer

# Set working directory
WORKDIR /var/www/html