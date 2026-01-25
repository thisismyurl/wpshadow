#!/bin/bash
set -e

echo "🚀 Setting up WordPress Plugin Development Environment..."

# Install WP-CLI
echo "📦 Installing WP-CLI..."
curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
chmod +x wp-cli.phar
sudo mv wp-cli.phar /usr/local/bin/wp

# Wait for WordPress to be ready
echo "⏳ Waiting for WordPress to be ready..."
until wp core is-installed --allow-root 2>/dev/null; do
    sleep 2
done

# Install Composer dependencies if composer.json exists
if [ -f "composer.json" ]; then
    echo "📦 Installing Composer dependencies..."
    composer install --no-interaction
fi

# Install WordPress Coding Standards
echo "📋 Installing WordPress Coding Standards..."
composer global require --dev wp-coding-standards/wpcs:"^3.0" phpcompatibility/phpcompatibility-wp:"*" phpstan/phpstan:"^1.10" automattic/vipwpcs:"^3.0"

# Configure PHPCS
echo "⚙️ Configuring PHPCS..."
phpcs --config-set installed_paths ~/.composer/vendor/wp-coding-standards/wpcs,~/.composer/vendor/phpcompatibility/phpcompatibility-wp,~/.composer/vendor/automattic/vipwpcs
phpcs --config-set default_standard WordPress

# Install Node dependencies if package.json exists
if [ -f "package.json" ]; then
    echo "📦 Installing Node dependencies..."
    npm install
fi

# Set permissions
echo "🔐 Setting correct permissions..."
sudo chown -R www-data:www-data /var/www/html/wp-content/plugins/wpshadow

# Activate the plugin
echo "🔌 Activating WP Shadow plugin..."
wp plugin activate wpshadow --allow-root || echo "Plugin activation will complete after WordPress setup"

echo "✅ Setup complete!"
echo ""
echo "📍 Access your development environment:"
echo "   WordPress:    http://localhost:8080"
echo "   phpMyAdmin:   http://localhost:8081"
echo "   MySQL:        localhost:3306"
echo ""
echo "🛠️ Useful commands:"
echo "   wp --version               Check WP-CLI version"
echo "   phpcs --version            Check PHPCS version"
echo "   phpcs -i                   List installed standards"
echo "   phpcs --standard=WordPress Check coding standards"
echo "   phpcbf --standard=WordPress Auto-fix coding standards"
echo ""
