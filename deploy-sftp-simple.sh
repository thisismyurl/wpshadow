#!/bin/bash
#
# Simple FTP Upload Script using SFTP (via ssh/scp)
# Deploys single or multiple files to wpshadow.com
#

set -e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# Configuration
REMOTE_HOST="wpshadow.com"
REMOTE_USER="thisismyurl"
REMOTE_PATH="public_html/wpshadow/wp-content/plugins/wpshadow"

# Get SSH key from environment or use default
SSH_KEY="${SSH_KEY:-$HOME/.ssh/id_rsa}"

if [ ! -f "$SSH_KEY" ]; then
    echo -e "${RED}Error: SSH key not found at $SSH_KEY${NC}"
    echo "Set SSH_KEY environment variable or create ~/.ssh/id_rsa"
    exit 1
fi

# Determine what to deploy
if [ -z "$1" ]; then
    echo -e "${BLUE}WPShadow SFTP Deploy${NC}"
    echo ""
    echo "Usage: $0 <file-or-directory>"
    echo "Example: $0 includes/admin/ajax/First_Scan_Handler.php"
    echo ""
    echo "Recent commits:"
    git log --oneline | head -5
    exit 1
fi

FILE_TO_DEPLOY="$1"

if [ ! -e "$FILE_TO_DEPLOY" ]; then
    echo -e "${RED}Error: File not found: $FILE_TO_DEPLOY${NC}"
    exit 1
fi

echo -e "${BLUE}═══════════════════════════════════════${NC}"
echo -e "${BLUE}  WPShadow SFTP Deploy${NC}"
echo -e "${BLUE}═══════════════════════════════════════${NC}"
echo ""
echo -e "${YELLOW}Deploying:${NC} $FILE_TO_DEPLOY"
echo -e "${YELLOW}To:${NC} $REMOTE_USER@$REMOTE_HOST:$REMOTE_PATH"
echo ""

# Deploy file(s)
if [ -d "$FILE_TO_DEPLOY" ]; then
    # Directory sync
    echo -e "${YELLOW}Syncing directory...${NC}"
    scp -r -i "$SSH_KEY" "$FILE_TO_DEPLOY" "$REMOTE_USER@$REMOTE_HOST:~/$REMOTE_PATH/"
else
    # Single file
    echo -e "${YELLOW}Uploading file...${NC}"
    # Get directory path
    DIR=$(dirname "$FILE_TO_DEPLOY")
    mkdir -p "/tmp/wpshadow-deploy/$DIR"
    cp "$FILE_TO_DEPLOY" "/tmp/wpshadow-deploy/$FILE_TO_DEPLOY"
    scp -i "$SSH_KEY" "/tmp/wpshadow-deploy/$FILE_TO_DEPLOY" "$REMOTE_USER@$REMOTE_HOST:~/$REMOTE_PATH/$FILE_TO_DEPLOY"
fi

echo ""
echo -e "${GREEN}✓${NC} Deployment complete!"
echo ""
echo -e "${YELLOW}Testing on server...${NC}"
sleep 2
curl -s "https://wpshadow.com/wp-admin/admin.php?page=wpshadow" 2>&1 | grep -i "parse error\|fatal" && {
    echo -e "${RED}✗${NC} Errors still present"
    exit 1
} || {
    echo -e "${GREEN}✓${NC} Page loads successfully!"
}
