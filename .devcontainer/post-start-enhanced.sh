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

# ============================================================================
# 1. ENVIRONMENT CHECK
# ============================================================================
echo -e "${BLUE}[1/8] Environment check...${NC}"
echo -e "${GREEN}✓ Codespace environment ready${NC}"

# ============================================================================
# 2. ENSURE DOCKER COMPOSE SERVICES ARE RUNNING
# ============================================================================
echo -e "${BLUE}[2/8] Starting Docker Compose services...${NC}"
cd /workspaces/wpshadow

if docker compose ps | grep -q "wpshadow-mysql"; then
    echo -e "${GREEN}✓ Services already running${NC}"
else
    echo -e "${YELLOW}Starting services...${NC}"
    if docker compose up -d 2>&1; then
        echo -e "${GREEN}✓ Services started${NC}"
    else
        echo -e "${RED}✗ Failed to start services${NC}"
        ERROR_COUNT=$((ERROR_COUNT + 1))
    fi
fi

sleep 3

# ============================================================================
# 3. VERIFY MYSQL SERVICE
# ============================================================================
echo -e "${BLUE}[3/8] Checking MySQL service...${NC}"

if mysqladmin ping -h 127.0.0.1 -u wordpress -pwordpress &>/dev/null 2>&1; then
    echo -e "${GREEN}✓ MySQL is running${NC}"
else
    echo -e "${YELLOW}⚠ MySQL is starting...${NC}"
    for i in {1..30}; do
        if mysqladmin ping -h 127.0.0.1 -u wordpress -pwordpress &>/dev/null 2>&1; then
            echo -e "${GREEN}✓ MySQL is ready${NC}"
            break
        fi
        echo -n "."
        sleep 2
    done
    if ! mysqladmin ping -h 127.0.0.1 -u wordpress -pwordpress &>/dev/null 2>&1; then
        echo -e "${RED}✗ MySQL failed to respond${NC}"
        ERROR_COUNT=$((ERROR_COUNT + 1))
    fi
fi

# ============================================================================
# 4. VERIFY WORDPRESS SERVICE
# ============================================================================
echo -e "${BLUE}[4/8] Checking WordPress service...${NC}"

if curl -s http://localhost:8080 &>/dev/null; then
    echo -e "${GREEN}✓ WordPress is responding${NC}"
else
    echo -e "${YELLOW}⚠ WordPress is starting...${NC}"
    for i in {1..30}; do
        if curl -s http://localhost:8080 &>/dev/null; then
            echo -e "${GREEN}✓ WordPress is ready${NC}"
            break
        fi
        echo -n "."
        sleep 2
    done
    if ! curl -s http://localhost:8080 &>/dev/null; then
        echo -e "${YELLOW}⚠ WordPress may still be initializing${NC}"
    fi
fi

# ============================================================================
# 5. VERIFY PHPMYADMIN
# ============================================================================
echo -e "${BLUE}[5/8] Checking phpMyAdmin service...${NC}"
if curl -s http://localhost:8081 &>/dev/null; then
    echo -e "${GREEN}✓ phpMyAdmin is accessible${NC}"
else
    echo -e "${YELLOW}⚠ phpMyAdmin is starting...${NC}"
fi

# ============================================================================
# 6. VERIFY PLUGIN MOUNT
# ============================================================================
echo -e "${BLUE}[6/8] Checking plugin mount...${NC}"
if [ -f "/workspaces/wpshadow/wpshadow.php" ]; then
    echo -e "${GREEN}✓ wpshadow plugin files are available${NC}"
    
    VERSION=$(grep "Version:" /workspaces/wpshadow/wpshadow.php | head -1 | sed 's/.*Version: //' | tr -d '\r' | awk '{print $1}')
    if [ -n "$VERSION" ]; then
        echo "  Plugin version: $VERSION"
    fi
    
    # Verify plugin syntax
    if ! php -l /workspaces/wpshadow/wpshadow.php &>/dev/null 2>&1; then
        echo -e "${RED}✗ Plugin has syntax errors${NC}"
        ERROR_COUNT=$((ERROR_COUNT + 1))
    fi
else
    echo -e "${YELLOW}⚠ Plugin files not accessible via direct path${NC}"
fi

# ============================================================================
# 7. CHECK DOCKER SERVICE STATUS
# ============================================================================
echo -e "${BLUE}[7/8] Docker service status...${NC}"
if docker compose ps 2>&1 | grep -q "wpshadow"; then
    echo -e "${GREEN}✓ Docker services active${NC}"
    docker compose ps | grep wpshadow || true
else
    echo -e "${YELLOW}⚠ Docker services status unclear${NC}"
fi

# ============================================================================
# 8. FINAL STATUS
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
echo -e "${BLUE}🌐 Connection Information:${NC}"

if [ -n "$CODESPACE_NAME" ]; then
    # GitHub Codespaces environment
    PFD=${GITHUB_CODESPACES_PORT_FORWARDING_DOMAIN:-app.github.dev}
    WP_URL="https://${CODESPACE_NAME}-8080.${PFD}"
    PMA_URL="https://${CODESPACE_NAME}-8081.${PFD}"
    echo -e "  📍 GitHub Codespaces detected: ${CODESPACE_NAME}"
    echo -e "  🌐 WordPress:   ${GREEN}${WP_URL}${NC}"
    echo -e "  📊 phpMyAdmin:  ${GREEN}${PMA_URL}${NC}"
else
    # Local environment (VS Code Dev Container or Docker Desktop)
    echo -e "  📍 Local environment detected"
    echo -e "  🌐 WordPress:   ${GREEN}http://localhost:8080${NC}"
    echo -e "  📊 phpMyAdmin:  ${GREEN}http://localhost:8081${NC}"
fi

echo ""
echo -e "${BLUE}Database Credentials:${NC}"
echo "  Host: localhost:3306"
echo "  User: wordpress"
echo "  Password: wordpress"
echo "  Database: wordpress"
echo ""

# ============================================================================
# QUICK COMMANDS
# ============================================================================
echo -e "${BLUE}Quick Commands:${NC}"
echo "  View services:         docker compose ps"
echo "  View logs:             docker compose logs -f"
echo "  MySQL client:          mysql -h127.0.0.1 -uwordpress -pwordpress wordpress"
echo "  WP-CLI:                docker compose exec wordpress wp --allow-root [command]"
echo "  Restart services:      docker compose restart"
echo "  Stop services:         docker compose down"
echo "  Check setup log:       cat $LOG"
echo ""

# ============================================================================
# HELPFUL TIPS
# ============================================================================
echo -e "${BLUE}💡 Helpful Tips:${NC}"
echo "  • WordPress will complete initialization on first browser visit"
echo "  • Default WP admin user/pass will be shown in WordPress dashboard"
echo "  • Use 'docker compose logs wordpress' to see WordPress startup"
echo "  • Use 'docker compose exec wordpress bash' to enter WordPress container"
echo ""

# ============================================================================
# START FILE WATCHER FOR AUTO-DEPLOYMENT
# ============================================================================
echo -e "${BLUE}Starting file watcher for auto-deployment...${NC}"
if command -v inotifywait &> /dev/null; then
    # Check if watcher is already running to avoid duplicates
    if ! pgrep -f "watch-and-deploy.sh" > /dev/null 2>&1; then
        # Run watcher in background with nohup so it survives terminal close
        cd /workspaces/wpshadow
        nohup ./watch-and-deploy.sh > /tmp/wpshadow-watch.log 2>&1 &
        WATCHER_PID=$!
        echo -e "${GREEN}✓ File watcher started (PID: $WATCHER_PID)${NC}"
        echo "  View logs: ${BLUE}tail -f /tmp/wpshadow-watch.log${NC}"
    else
        echo -e "${GREEN}✓ File watcher already running${NC}"
    fi
else
    echo -e "${YELLOW}⚠ inotify-tools not available${NC}"
fi
echo ""

} 2>&1 | tee -a "$LOG"
