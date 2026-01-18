#!/bin/bash
set -e

# First, run the standard WordPress entrypoint
docker-entrypoint.sh "$@" &
WP_PID=$!

# Wait a bit for WordPress to initialize
sleep 2

# Modify wp-config.php to include our local config if not already done
if ! grep -q "wp-config-local.php" /var/www/html/wp-config.php; then
    sed -i "s/\/\* That's all, stop editing!/require_once( dirname( __FILE__ ) . '\/wp-config-local.php' );\n\n/* That's all, stop editing!/" /var/www/html/wp-config.php
fi

# Keep the original process running
wait $WP_PID
