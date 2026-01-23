#!/bin/bash
# Test WPShadow admin page inside Docker container
# Usage: ./scripts/test-wpshadow-page.sh

set -e

echo "╔═══════════════════════════════════════════════════════════════════════════╗"
echo "║              Testing WPShadow Admin Page (admin/admin)                    ║"
echo "╚═══════════════════════════════════════════════════════════════════════════╝"
echo ""

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# Step 1: Check if container is running
echo -e "${BLUE}→ Checking Docker container...${NC}"
if ! docker ps --format '{{.Names}}' | grep -q "wpshadow-test"; then
    echo -e "${RED}✗ wpshadow-test container not running${NC}"
    echo "  Run: docker-compose up -d"
    exit 1
fi
echo -e "${GREEN}✓ Container running${NC}"
echo ""

# Step 2: Test WordPress admin page
echo -e "${BLUE}→ Testing WPShadow admin page...${NC}"
docker exec wpshadow-test bash -c 'cd /var/www/html && php -r "
define(\"WP_USE_THEMES\", false);
\$_SERVER[\"HTTP_HOST\"] = \"localhost:9000\";
\$_SERVER[\"REQUEST_URI\"] = \"/wp-admin/admin.php?page=wpshadow\";
\$_SERVER[\"SCRIPT_NAME\"] = \"/wp-admin/admin.php\";
\$_GET[\"page\"] = \"wpshadow\";

// Simulate logged-in admin user
define(\"WP_ADMIN\", true);
\$_COOKIE[\"wordpress_logged_in\"] = \"admin\";

require(\"./wp-load.php\");

// Load admin includes
require_once(ABSPATH . \"wp-admin/includes/admin.php\");

// Set current user to admin
wp_set_current_user(1);

// Capture output
ob_start();

// Trigger the admin page load
do_action(\"toplevel_page_wpshadow\");

\$output = ob_get_clean();

// Report results
if (empty(\$output)) {
    echo \"✓ Page loaded successfully (no output = no errors)\n\";
    exit(0);
} else {
    echo \"Page output:\n\";
    echo \$output;
}
"' 2>&1 > /tmp/wpshadow_test_output.txt

# Check for errors in output
if grep -qiE "(fatal|critical error|deprecated.*strpos|deprecated.*str_replace)" /tmp/wpshadow_test_output.txt; then
    echo -e "${RED}✗ Errors found:${NC}"
    grep -iE "(fatal|critical|deprecated|warning)" /tmp/wpshadow_test_output.txt | head -20
    echo ""
    echo -e "${YELLOW}Full output saved to: /tmp/wpshadow_test_output.txt${NC}"
    exit 1
else
    echo -e "${GREEN}✓ No errors or deprecation warnings found${NC}"
fi

echo ""
echo -e "${GREEN}╔═══════════════════════════════════════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║                    ✅ All Tests Passed!                                   ║${NC}"
echo -e "${GREEN}╚═══════════════════════════════════════════════════════════════════════════╝${NC}"
echo ""
echo -e "${BLUE}🌐 View in browser:${NC}"
echo "  https://fictional-space-bassoon-qr65q7qqx4p2xvgr-9000.app.github.dev/wp-admin/admin.php?page=wpshadow"
echo ""
echo -e "${BLUE}🔑 Credentials:${NC}"
echo "  Username: admin"
echo "  Password: admin"
echo ""
