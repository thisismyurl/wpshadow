#!/bin/bash

# Install debug plugins
echo "🔧 Installing debug plugins..."
wp plugin install query-monitor --activate --allow-root
wp plugin install debug-bar --activate --allow-root
wp plugin install wp-crontrol --activate --allow-root
wp plugin install user-switching --activate --allow-root

echo "Debug plugins installed:"
echo "  - Query Monitor: See all queries, hooks, performance"
echo "  - Debug Bar: Debugging information in admin bar"
echo "  - WP Crontrol: Manage cron events"
echo "  - User Switching: Quick user switching for testing"
