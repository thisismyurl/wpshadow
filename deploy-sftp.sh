#!/bin/bash
#
# WPShadow SFTP Deployment Script
# Syncs local plugin files to remote WordPress installation via SFTP
#

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Load SFTP configuration
CONFIG_FILE=".sftp-config.env"

if [ ! -f "$CONFIG_FILE" ]; then
    echo -e "${RED}Error: $CONFIG_FILE not found${NC}"
    echo "Copy .sftp-config.env.example to .sftp-config.env and configure it"
    exit 1
fi

source "$CONFIG_FILE"

# Validate required variables
if [ -z "$SFTP_HOST" ] || [ -z "$SFTP_USER" ] || [ -z "$SFTP_REMOTE_PATH" ]; then
    echo -e "${RED}Error: Missing required SFTP configuration${NC}"
    echo "Please configure SFTP_HOST, SFTP_USER, and SFTP_REMOTE_PATH in $CONFIG_FILE"
    exit 1
fi

# Build rsync exclude arguments
EXCLUDE_ARGS=""
for pattern in $EXCLUDE_PATTERNS; do
    EXCLUDE_ARGS="$EXCLUDE_ARGS --exclude=$pattern"
done

echo -e "${GREEN}Starting SFTP deployment...${NC}"
echo "Host: $SFTP_HOST"
echo "User: $SFTP_USER"
echo "Remote path: $SFTP_REMOTE_PATH"
echo ""

# Perform sync based on auth method
if [ -n "$SFTP_KEY_FILE" ] && [ -f "$SFTP_KEY_FILE" ]; then
    echo -e "${YELLOW}Using SSH key authentication${NC}"
    rsync -avz --delete \
        -e "ssh -p ${SFTP_PORT:-22} -i $SFTP_KEY_FILE -o StrictHostKeyChecking=no" \
        $EXCLUDE_ARGS \
        ./ "$SFTP_USER@$SFTP_HOST:$SFTP_REMOTE_PATH/"
elif [ -n "$SFTP_PASSWORD" ]; then
    echo -e "${YELLOW}Using password authentication (consider using SSH keys instead)${NC}"
    
    # Install sshpass if not available
    if ! command -v sshpass &> /dev/null; then
        echo "Installing sshpass..."
        sudo apk add --no-cache sshpass
    fi
    
    sshpass -p "$SFTP_PASSWORD" rsync -avz --delete \
        -e "ssh -p ${SFTP_PORT:-22} -o StrictHostKeyChecking=no" \
        $EXCLUDE_ARGS \
        ./ "$SFTP_USER@$SFTP_HOST:$SFTP_REMOTE_PATH/"
else
    echo -e "${RED}Error: No authentication method configured${NC}"
    echo "Set either SFTP_KEY_FILE or SFTP_PASSWORD in $CONFIG_FILE"
    exit 1
fi

echo ""
echo -e "${GREEN}✓ Deployment complete!${NC}"
echo "Your changes are now live at: $SFTP_HOST"
