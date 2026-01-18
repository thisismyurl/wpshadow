#!/bin/bash
set -e

# Call the original WordPress entrypoint
docker-entrypoint.sh "$@" &
ORIGINAL_PID=$!

# Wait a bit for WordPress to start
sleep 2

# Add our extra config to wp-config.php if not already there
if [ -f /var/www/html/wp-config.php ] && ! grep -q "wp-config-extra.php" /var/www/html/wp-config.php; then
    # Insert our require statement before the "That's all, stop editing!" line
    sed -i "/\/\* That's all, stop editing!/i require_once( dirname( __FILE__ ) . '/wp-config-extra.php' );" /var/www/html/wp-config.php
fi

# Keep the original process running
wait $ORIGINAL_PID
