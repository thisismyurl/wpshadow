#!/bin/bash
set -e

echo "🔄 Starting WordPress environment..."

# Wait for MySQL to be ready
echo "⏳ Waiting for MySQL..."
until wp db check --allow-root 2>/dev/null; do
    sleep 2
done

# Check if WordPress is installed
if ! wp core is-installed --allow-root 2>/dev/null; then
    echo "📦 Installing WordPress..."
    wp core install \
        --url="http://localhost:8080" \
        --title="WPShadow Development" \
        --admin_user="admin" \
        --admin_password="admin" \
        --admin_email="admin@example.com" \
        --allow-root
fi

# Activate the plugin
echo "🔌 Activating WPShadow plugin..."
wp plugin activate wpshadow --allow-root 2>/dev/null || true

# Set debug mode
wp config set WP_DEBUG true --raw --allow-root
wp config set WP_DEBUG_LOG true --raw --allow-root
wp config set WP_DEBUG_DISPLAY false --raw --allow-root

echo "✅ WordPress is ready!"
echo "🔐 Login: admin / admin"
