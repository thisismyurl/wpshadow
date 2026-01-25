#!/bin/bash
set -e

echo "🔄 Post-start configuration..."

# WordPress URL configuration (default to port 8080, can be overridden)
WP_URL="${WP_URL:-http://localhost:8080}"
WP_ADMIN_USER="${WP_ADMIN_USER:-admin}"
WP_ADMIN_PASSWORD="${WP_ADMIN_PASSWORD:-admin}"
WP_ADMIN_EMAIL="${WP_ADMIN_EMAIL:-admin@example.com}"

# Wait for WordPress to be available
until curl -s http://localhost:80 > /dev/null 2>&1; do
    echo "⏳ Waiting for WordPress..."
    sleep 2
done

# Check if WordPress is already installed
if ! wp core is-installed --allow-root 2>/dev/null; then
    echo "🎉 Installing WordPress..."
    wp core install \
        --url="$WP_URL" \
        --title="WP Shadow Development" \
        --admin_user="$WP_ADMIN_USER" \
        --admin_password="$WP_ADMIN_PASSWORD" \
        --admin_email="$WP_ADMIN_EMAIL" \
        --allow-root
    
    echo "✅ WordPress installed!"
    echo "   URL: $WP_URL"
    echo "   Username: $WP_ADMIN_USER"
    echo "   Password: $WP_ADMIN_PASSWORD"
else
    echo "✅ WordPress already installed"
fi

# Activate WP Shadow plugin
wp plugin activate wpshadow --allow-root || echo "⚠️ Plugin activation pending"

echo "🎊 Post-start configuration complete!"
