#!/bin/bash
set -e

echo "🔄 Post-start configuration..."

# Wait for WordPress to be available
until curl -s http://localhost:80 > /dev/null 2>&1; do
    echo "⏳ Waiting for WordPress..."
    sleep 2
done

# Check if WordPress is already installed
if ! wp core is-installed --allow-root 2>/dev/null; then
    echo "🎉 Installing WordPress..."
    wp core install \
        --url="http://localhost:8080" \
        --title="WP Shadow Development" \
        --admin_user="admin" \
        --admin_password="admin" \
        --admin_email="admin@example.com" \
        --allow-root
    
    echo "✅ WordPress installed!"
    echo "   URL: http://localhost:8080"
    echo "   Username: admin"
    echo "   Password: admin"
else
    echo "✅ WordPress already installed"
fi

# Activate WP Shadow plugin
wp plugin activate wpshadow --allow-root || echo "⚠️ Plugin activation pending"

echo "🎊 Post-start configuration complete!"
