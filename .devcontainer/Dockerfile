FROM wordpress:latest

# Install additional PHP extensions needed for development tools
# - zip: Required by Composer for package management
# - intl: Internationalization functions for WordPress
# - opcache: PHP opcode caching for better performance
# - gd: Image processing (already in base but ensuring it's configured)
RUN apt-get update && apt-get install -y \
    libzip-dev \
    libicu-dev \
    unzip \
    git \
    && docker-php-ext-install \
    zip \
    intl \
    opcache \
    && rm -rf /var/lib/apt/lists/*

# Install Composer globally
# This ensures composer is available for setup.sh and development
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Verify installations
RUN php -m && composer --version

# Set recommended PHP.ini settings for development
RUN { \
    echo 'upload_max_filesize = 64M'; \
    echo 'post_max_size = 64M'; \
    echo 'memory_limit = 256M'; \
    echo 'max_execution_time = 300'; \
    echo 'max_input_vars = 3000'; \
    echo 'display_errors = On'; \
    echo 'error_reporting = E_ALL'; \
    } > /usr/local/etc/php/conf.d/wordpress-dev.ini