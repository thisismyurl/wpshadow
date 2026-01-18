#!/bin/bash
set -e

# Call the original WordPress Docker entrypoint
/docker-entrypoint.sh "$@" &
ORIGINAL_PID=$!

# Wait for WordPress to initialize
sleep 3

# Add our extra config to wp-config.php if not already there
if [ -f /var/www/html/wp-config.php ] && ! grep -q "wp-config-extra.php" /var/www/html/wp-config.php; then
    # Insert our require statement before the "That's all, stop editing!" line
    sed -i "/\/\* That's all, stop editing!/i require_once( dirname( __FILE__ ) . '/wp-config-extra.php' );" /var/www/html/wp-config.php
fi

# Copy mu-plugins into wp-content if the source directory exists
if [ -d /var/www/html/wp-content/plugins/wpshadow/assets/mu-plugins ]; then
    mkdir -p /var/www/html/wp-content/mu-plugins
    cp -r /var/www/html/wp-content/plugins/wpshadow/assets/mu-plugins/* /var/www/html/wp-content/mu-plugins/ 2>/dev/null || true
    chmod -R 755 /var/www/html/wp-content/mu-plugins
fi

# Keep the original process running in foreground
