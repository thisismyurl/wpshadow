#!/bin/bash
#
# WPShadow Real-Time File Watcher for Auto-Deployment
# Watches for local file changes and automatically deploys to FTP
# Usage: ./watch-and-deploy.sh
#

set -e

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m'

echo -e "${BLUE}═══════════════════════════════════════${NC}"
echo -e "${BLUE}  WPShadow Real-Time File Watcher${NC}"
echo -e "${BLUE}═══════════════════════════════════════${NC}"
echo ""

# Check if inotify-tools is installed
if ! command -v inotifywait &> /dev/null; then
    echo -e "${RED}Error: inotify-tools is required but not installed${NC}"
    echo "Install with: apk add inotify-tools"
    exit 1
fi

echo -e "${YELLOW}Configuration:${NC}"
echo "  Watching: includes/ assets/ wpshadow.php readme.txt"
echo "  Ignoring: .git, node_modules, .tmp, build, tests"
echo ""
echo -e "${GREEN}✓${NC} Watcher started... waiting for changes"
echo -e "${CYAN}(Press Ctrl+C to stop)${NC}"
echo ""

# Watch directories for changes and deploy
inotifywait -m -r \
    -e modify,create,delete \
    --exclude '(\.(git|tmp|log|lock|cache)|node_modules|vendor|tests|build)' \
    --format '%w%f' \
    includes/ assets/ wpshadow.php readme.txt 2>/dev/null | while read file; do

    echo ""
    echo -e "${YELLOW}[$(date '+%Y-%m-%d %H:%M:%S')]${NC} File changed: ${CYAN}$file${NC}"
    echo -e "${YELLOW}Deploying in 2 seconds... (press Ctrl+C to cancel)${NC}"
    sleep 2

    # Run deployment
    if ./deploy-ftp.sh; then
        echo ""
        echo -e "${GREEN}✓ Deployment successful${NC}"
        echo -e "${BLUE}Watching for next changes...${NC}"
    else
        echo ""
        echo -e "${RED}✗ Deployment failed - see output above${NC}"
        echo -e "${BLUE}Watching for next changes...${NC}"
    fi
    echo ""
done
