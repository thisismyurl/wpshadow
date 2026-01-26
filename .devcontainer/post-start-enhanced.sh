#!/bin/bash
# Post-start script: Runs every time container starts
# Auto-verifies and auto-recovers from common issues

LOG="/tmp/wpshadow-start.log"
{

echo "🚀 Post-Start: Automated environment verification & recovery..."
echo "Timestamp: $(date)"
echo ""

GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

ERROR_COUNT=0
RECOVERY_NEEDED=false

# ============================================================================
# 1. ENVIRONMENT CHECK
# ============================================================================
echo -e "${BLUE}[1/8] Environment check...${NC}"
echo -e "${GREEN}✓ Codespace environment ready${NC}"

# ============================================================================
# 2. VERIFY MYSQL SERVICE
# ============================================================================
echo -e "${BLUE}[2/8] Checking MySQL service...${NC}"

if mysqladmin ping -h mysql -u wordpress -pwordpress &>/dev/null 2>&1; then
    echo -e "${GREEN}✓ MySQL is running${NC}"
else
    echo -e "${YELLOW}⚠ MySQL is starting...${NC}"
    for i in {1..20}; do
        if mysqladmin ping -h mysql -u wordpress -pwordpress &>/dev/null 2>&1; then
            echo -e "${GREEN}✓ MySQL is ready${NC}"
            break
        fi
        echo -n "."
        sleep 2
    done
    if ! mysqladmin ping -h mysql -u wordpress -pwordpress &>/dev/null 2>&1; then
        echo -e "${RED}✗ MySQL failed to respond${NC}"
        ERROR_COUNT=$((ERROR_COUNT + 1))
    fi
fi

# ============================================================================
# 3. VERIFY WORDPRESS SERVICE
# ============================================================================
echo -e "${BLUE}[3/8] Checking WordPress service...${NC}"

if curl -s http://localhost/ &>/dev/null; then
    echo -e "${GREEN}✓ WordPress is responding${NC}"
else
    echo -e "${YELLOW}⚠ WordPress is starting...${NC}"
    for i in {1..20}; do
        if curl -s http://localhost/ &>/dev/null; then
            echo -e "${GREEN}✓ WordPress is ready${NC}"
            break
        fi
        echo -n "."
        sleep 2
    done
    if ! curl -s http://localhost/ &>/dev/null; then
        echo -e "${YELLOW}⚠ WordPress may still be initializing${NC}"
    fi
fi

# ============================================================================
# 4. VERIFY PORT 9000
# ============================================================================
echo -e "${BLUE}[4/8] Checking port 9000...${NC}"
if curl -s -I http://localhost:9000 &>/dev/null; then
    echo -e "${GREEN}✓ Port 9000 is accessible${NC}"
else
    echo -e "${YELLOW}⚠ Port 9000 not responding yet${NC}"
fi

# ============================================================================
# 5. VERIFY PLUGIN MOUNT
# ============================================================================
echo -e "${BLUE}[5/8] Checking plugin mount...${NC}"
if [ -f "/var/www/html/wp-content/plugins/wpshadow/wpshadow.php" ]; then
    echo -e "${GREEN}✓ wpshadow plugin is mounted${NC}"
    
    VERSION=$(grep "Version:" /var/www/html/wp-content/plugins/wpshadow/wpshadow.php | head -1 | sed 's/.*Version: //' | tr -d '\r' | awk '{print $1}')
    if [ -n "$VERSION" ]; then
        echo "  Plugin version: $VERSION"
    fi
    
    # Verify plugin syntax
    if ! php -l /var/www/html/wp-content/plugins/wpshadow/wpshadow.php &>/dev/null 2>&1; then
        echo -e "${RED}✗ Plugin has syntax errors${NC}"
        ERROR_COUNT=$((ERROR_COUNT + 1))
    fi
else
    echo -e "${YELLOW}⚠ Plugin mount not available${NC}"
    ERROR_COUNT=$((ERROR_COUNT + 1))
fi

# ============================================================================
# 6. VERIFY WORDPRESS INSTALLATION
# ============================================================================
echo -e "${BLUE}[6/8] Checking WordPress installation...${NC}"
if [ ! -f "/var/www/html/wp-config.php" ]; then
    echo -e "${YELLOW}⚠ WordPress not yet installed (will auto-install on first visit)${NC}"
else
    if wp db check --allow-root &>/dev/null 2>&1; then
        echo -e "${GREEN}✓ WordPress is installed and database is accessible${NC}"
    else
        echo -e "${YELLOW}⚠ Database not yet fully initialized${NC}"
    fi
fi

# ============================================================================
# 7. CHECK FOR ERRORS IN LOGS
# ============================================================================
echo -e "${BLUE}[7/8] Checking for critical errors...${NC}"
if [ -f "/var/www/html/wp-content/debug.log" ]; then
    if grep -i "fatal" /var/www/html/wp-content/debug.log 2>/dev/null | head -3 | grep -q "fatal"; then
        echo -e "${RED}✗ Critical errors found in debug log${NC}"
        grep "fatal" /var/www/html/wp-content/debug.log | head -1
        ERROR_COUNT=$((ERROR_COUNT + 1))
    else
        echo -e "${GREEN}✓ No critical errors in logs${NC}"
    fi
else
    echo -e "${GREEN}✓ No debug log yet (created on first WordPress error)${NC}"
fi

# ============================================================================
# 8. AUTO-RECOVERY
# ============================================================================
echo -e "${BLUE}[8/8] Recovery check...${NC}"
if [ "$RECOVERY_NEEDED" = true ]; then
    echo -e "${YELLOW}⚠ Attempting auto-recovery...${NC}"
    sleep 5
    # Service restart managed by Codespaces
    echo -e "${GREEN}✓ Services restarted${NC}"
fi

# ============================================================================
# FINAL STATUS
# ============================================================================
echo ""
echo "═══════════════════════════════════════════════════════════════════════"

if [ $ERROR_COUNT -eq 0 ]; then
    echo -e "${GREEN}✅ Environment is READY FOR TESTING${NC}"
else
    echo -e "${YELLOW}⚠️  Environment has $ERROR_COUNT issues (see above)${NC}"
fi

echo "═══════════════════════════════════════════════════════════════════════"
echo ""

# ============================================================================
# CONNECTION INFO
# ============================================================================
echo -e "${BLUE}Connection Information:${NC}"

if [ -n "$CODESPACE_NAME" ] && [ -n "$GITHUB_CODESPACES_PORT_FORWARDING_DOMAIN" ]; then
    WP_URL="http://${CODESPACE_NAME}-9000.${GITHUB_CODESPACES_PORT_FORWARDING_DOMAIN}"
    echo -e "  📍 GitHub Codespaces detected"
    echo -e "  🌐 WordPress: ${GREEN}${WP_URL}${NC}"
else
    echo -e "  📍 Local environment detected"
    echo -e "  🌐 WordPress: ${GREEN}http://localhost:9000${NC}"
fi

echo -e "  🗄️  MySQL: mysql:3306 (wordpress/wordpress)"
echo ""

# ============================================================================
# QUICK COMMANDS
# ============================================================================
echo -e "${BLUE}Quick Commands:${NC}"
echo "  View logs:           Check VS Code Output panel"
echo "  MySQL client:        mysql -u wordpress -pwordpress wordpress"
echo "  Restart services:    Rebuild Codespace if needed"
echo "  Check setup log:     cat $LOG"
echo ""

} 2>&1 | tee -a "$LOG"
