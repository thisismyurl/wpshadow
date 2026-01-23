#!/bin/bash
# Test WordPress loading with WPShadow plugin

set -e

echo "🧪 Testing WordPress Load..."

# Check if Docker container is running
if ! docker ps --format '{{.Names}}' | grep -q "wpshadow-test"; then
    echo "❌ wpshadow-test container not running"
    echo "Start it with: docker-compose up -d"
    exit 1
fi

# Test 1: Basic WordPress load
echo "→ Test 1: WordPress bootstrap"
if docker exec wpshadow-test php -r 'require("/var/www/html/wp-load.php"); echo "✅ WordPress loaded\n";' 2>&1 | grep -q "✅"; then
    echo "✅ WordPress loads successfully"
else
    echo "❌ WordPress failed to load"
    exit 1
fi

# Test 2: Admin init hooks
echo "→ Test 2: Admin hooks"
if docker exec wpshadow-test bash -c 'cd /var/www/html && php -d error_reporting=E_ALL -r "define(\"WP_USE_THEMES\", false); require(\"./wp-load.php\"); do_action(\"admin_menu\"); do_action(\"admin_init\"); echo \"✅ Admin hooks OK\n\";"' 2>&1 | grep -q "✅"; then
    echo "✅ Admin hooks work"
else
    echo "❌ Admin hooks failed"
    exit 1
fi

# Test 3: Plugin active check
echo "→ Test 3: WPShadow plugin status"
if docker exec wpshadow-test wp plugin is-active wpshadow --allow-root > /dev/null 2>&1; then
    echo "✅ WPShadow plugin is active"
else
    echo "⚠️  WPShadow plugin not active - activating..."
    docker exec wpshadow-test wp plugin activate wpshadow --allow-root
fi

# Test 4: No fatal errors in debug log
echo "→ Test 4: Checking for fatal errors"
if docker exec wpshadow-test bash -c 'if [ -f /var/www/html/wp-content/debug.log ]; then tail -50 /var/www/html/wp-content/debug.log | grep -i "fatal\|parse error" && exit 1 || exit 0; else exit 0; fi'; then
    echo "✅ No fatal errors in debug log"
else
    echo "❌ Fatal errors found in debug log"
    exit 1
fi

echo ""
echo "✅ All WordPress load tests passed!"
echo ""
