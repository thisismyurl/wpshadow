#!/bin/bash
#
# WPShadow Testing Environment - Setup Validator & Quick Start
# =============================================================
# This script validates that all testing setup files are in place
# and can quickly start the test environment.
#
# Usage:
#   ./validate-test-setup.sh              # Validate setup only
#   ./validate-test-setup.sh install      # Validate + install WordPress
#   ./validate-test-setup.sh start        # Start containers (assumes WordPress is installed)
#   ./validate-test-setup.sh reset        # Full reset (delete all data and reinstall)
#

set -e

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ACTION="${1:-validate}"

# Color codes for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}=== WPShadow Testing Environment ===${NC}"
echo ""

# ============================================================
# VALIDATION CHECKS
# ============================================================

echo -e "${YELLOW}Checking setup files...${NC}"

check_file() {
    if [ -f "$1" ]; then
        echo -e "${GREEN}✓${NC} $1"
        return 0
    else
        echo -e "${RED}✗${NC} $1 - MISSING"
        return 1
    fi
}

VALID=true
check_file "$SCRIPT_DIR/docker-compose-test.yml" || VALID=false
check_file "$SCRIPT_DIR/wp-config-extra.php" || VALID=false
check_file "$SCRIPT_DIR/TESTING_SETUP.md" || VALID=false
check_file "$SCRIPT_DIR/wpshadow.php" || VALID=false

echo ""

if [ "$VALID" = false ]; then
    echo -e "${RED}✗ Setup validation failed. Missing required files.${NC}"
    echo -e "${YELLOW}Run from the /workspaces/wpshadow directory${NC}"
    exit 1
fi

echo -e "${GREEN}✓ All setup files present${NC}"
echo ""

# ============================================================
# CHECK DOCKER
# ============================================================

echo -e "${YELLOW}Checking Docker...${NC}"

if ! command -v docker &> /dev/null; then
    echo -e "${RED}✗ Docker not installed${NC}"
    exit 1
fi

if ! docker ps &> /dev/null; then
    echo -e "${RED}✗ Docker daemon not running${NC}"
    exit 1
fi

echo -e "${GREEN}✓ Docker is running${NC}"
echo ""

# ============================================================
# VALIDATE CODESPACES DOMAIN
# ============================================================

echo -e "${YELLOW}Checking Codespaces configuration...${NC}"

DOMAIN=$(grep -o 'https://[^/]*' "$SCRIPT_DIR/wp-config-extra.php" | head -1 | sed 's|https://||')

if [ -z "$DOMAIN" ]; then
    echo -e "${RED}✗ Could not find domain in wp-config-extra.php${NC}"
    exit 1
fi

echo -e "${GREEN}✓ Codespaces domain: $DOMAIN${NC}"

# Check if it's the placeholder
if [[ "$DOMAIN" == *"stunning-fishstick"* ]]; then
    echo -e "${YELLOW}⚠ Using default placeholder domain${NC}"
    echo -e "${YELLOW}  If you have a different Codespace, update wp-config-extra.php${NC}"
fi

echo ""

# ============================================================
# SHOW CURRENT CONTAINER STATUS
# ============================================================

echo -e "${YELLOW}Current container status:${NC}"
docker-compose -f "$SCRIPT_DIR/docker-compose-test.yml" ps 2>/dev/null || echo "No containers running"

echo ""

# ============================================================
# HANDLE ACTIONS
# ============================================================

case "$ACTION" in
    validate)
        echo -e "${GREEN}✓ Setup validation passed${NC}"
        echo -e "${YELLOW}Run with 'install' to set up WordPress:${NC}"
        echo -e "  ${BLUE}./validate-test-setup.sh install${NC}"
        ;;
    
    start)
        echo -e "${YELLOW}Starting containers...${NC}"
        docker-compose -f "$SCRIPT_DIR/docker-compose-test.yml" up -d
        sleep 3
        echo -e "${GREEN}✓ Containers started${NC}"
        echo -e "${YELLOW}Access WordPress at:${NC}"
        echo -e "  ${BLUE}https://$DOMAIN/wp-admin${NC}"
        echo -e "${YELLOW}Credentials: admin / admin123${NC}"
        ;;
    
    install)
        echo -e "${YELLOW}Starting fresh WordPress installation...${NC}"
        
        # Clean start
        docker-compose -f "$SCRIPT_DIR/docker-compose-test.yml" down --volumes 2>/dev/null || true
        
        # Start containers
        docker-compose -f "$SCRIPT_DIR/docker-compose-test.yml" up -d
        echo -e "${YELLOW}Waiting 15 seconds for MySQL to initialize...${NC}"
        sleep 15
        
        # Test database connection
        echo -e "${YELLOW}Testing database connection...${NC}"
        docker exec wpshadow-test-wordpress php -r "
        \$mysqli = mysqli_connect('db', 'wordpress', 'wordpress', 'wordpress');
        if (\$mysqli) {
          echo 'Connected';
        } else {
          echo 'Failed: ' . mysqli_connect_error();
          exit(1);
        }
        " > /dev/null && echo -e "${GREEN}✓ Database connected${NC}" || {
            echo -e "${RED}✗ Database connection failed${NC}"
            exit 1
        }
        
        # Install WordPress
        echo -e "${YELLOW}Installing WordPress...${NC}"
        curl -s -X POST "http://localhost:9000/wp-admin/install.php?step=1" \
          -d "language=en_US" > /dev/null
        curl -s -X POST "http://localhost:9000/wp-admin/install.php?step=2" \
          -d "weblog_title=WPShadow+Test&user_name=admin&admin_email=admin@test.com&admin_password=admin123&admin_password2=admin123&pw_weak=on&submit=Install+WordPress" > /dev/null
        
        sleep 2
        
        # Update database URLs
        echo -e "${YELLOW}Configuring database URLs...${NC}"
        docker exec wpshadow-test-db mysql -u wordpress -pwordpress wordpress -e \
          "UPDATE wp_options SET option_value='https://$DOMAIN' WHERE option_name IN ('siteurl', 'home');" 2>/dev/null
        
        # Restart to apply config
        docker restart wpshadow-test-wordpress > /dev/null
        sleep 3
        
        # Verify
        TITLE=$(curl -s http://localhost:9000/ | grep -o "<title>[^<]*</title>" || echo "")
        if [[ "$TITLE" == *"WPShadow"* ]]; then
            echo -e "${GREEN}✓ WordPress installed successfully${NC}"
            echo ""
            echo -e "${YELLOW}Access WordPress at:${NC}"
            echo -e "  ${BLUE}https://$DOMAIN/wp-admin${NC}"
            echo -e "${YELLOW}Credentials:${NC}"
            echo -e "  Username: ${BLUE}admin${NC}"
            echo -e "  Password: ${BLUE}admin123${NC}"
        else
            echo -e "${RED}✗ Installation may have failed${NC}"
            echo -e "${YELLOW}Check logs with: docker logs wpshadow-test-wordpress${NC}"
            exit 1
        fi
        ;;
    
    reset)
        echo -e "${YELLOW}⚠ Performing full reset - deleting all data${NC}"
        docker-compose -f "$SCRIPT_DIR/docker-compose-test.yml" down --volumes
        sleep 2
        echo -e "${YELLOW}Running installation...${NC}"
        exec "$0" install
        ;;
    
    stop)
        echo -e "${YELLOW}Stopping containers...${NC}"
        docker-compose -f "$SCRIPT_DIR/docker-compose-test.yml" down
        echo -e "${GREEN}✓ Stopped${NC}"
        ;;
    
    logs)
        docker-compose -f "$SCRIPT_DIR/docker-compose-test.yml" logs -f --tail=50
        ;;
    
    *)
        echo -e "${RED}Unknown action: $ACTION${NC}"
        echo ""
        echo "Usage:"
        echo "  ./validate-test-setup.sh validate      # Check setup files"
        echo "  ./validate-test-setup.sh install       # Full install with WordPress"
        echo "  ./validate-test-setup.sh start         # Start containers"
        echo "  ./validate-test-setup.sh stop          # Stop containers"
        echo "  ./validate-test-setup.sh reset         # Full reset and reinstall"
        echo "  ./validate-test-setup.sh logs          # View logs"
        exit 1
        ;;
esac

echo ""
