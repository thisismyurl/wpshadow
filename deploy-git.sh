#!/bin/bash
#
# WPShadow Git Deployment Script for GreenGeeks
# Deploys plugin via Git push to remote server
#

set -e

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

CONFIG_FILE=".deploy-git.env"

if [ ! -f "$CONFIG_FILE" ]; then
    echo -e "${RED}Error: $CONFIG_FILE not found${NC}"
    echo "Copy .deploy-git.env.example to .deploy-git.env and configure it"
    exit 1
fi

source "$CONFIG_FILE"

if [ -z "$REMOTE_HOST" ] || [ -z "$REMOTE_USER" ] || [ -z "$REMOTE_WP_PATH" ]; then
    echo -e "${RED}Error: Missing required configuration${NC}"
    exit 1
fi

echo -e "${GREEN}WPShadow Git Deployment${NC}"
echo "Remote: $REMOTE_USER@$REMOTE_HOST"
echo "Path: $REMOTE_WP_PATH"
echo ""

# Check if we have uncommitted changes
if ! git diff-index --quiet HEAD --; then
    echo -e "${YELLOW}Warning: You have uncommitted changes${NC}"
    read -p "Commit them now? (y/n) " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        read -p "Commit message: " commit_msg
        git add .
        git commit -m "$commit_msg"
    else
        echo "Deploying without local changes..."
    fi
fi

# Initialize remote git repo if needed
echo -e "${YELLOW}Setting up remote repository...${NC}"
ssh -p ${REMOTE_PORT:-22} ${SSH_KEY_FILE:+-i $SSH_KEY_FILE} "$REMOTE_USER@$REMOTE_HOST" << 'ENDSSH'
    cd "$REMOTE_WP_PATH" 2>/dev/null || {
        echo "Creating directory..."
        mkdir -p "$REMOTE_WP_PATH"
        cd "$REMOTE_WP_PATH"
    }
    
    if [ ! -d .git ]; then
        echo "Initializing git repository..."
        git init
        git config receive.denyCurrentBranch updateInstead
    fi
ENDSSH

# Configure SSH for Git
export GIT_SSH_COMMAND="ssh -i $SSH_KEY_FILE -o StrictHostKeyChecking=no"

# Add remote if not exists
REMOTE_NAME="greengeeks"
if ! git remote get-url $REMOTE_NAME &>/dev/null; then
    echo -e "${YELLOW}Adding remote '$REMOTE_NAME'...${NC}"
    git remote add $REMOTE_NAME "ssh://$REMOTE_USER@$REMOTE_HOST:${REMOTE_PORT:-22}$REMOTE_WP_PATH"
fi

# Push to remote
echo -e "${YELLOW}Pushing to GreenGeeks...${NC}"
git push $REMOTE_NAME ${DEPLOY_BRANCH:-main} --force

echo ""
echo -e "${GREEN}✓ Deployment complete!${NC}"
echo "Plugin deployed to: $REMOTE_HOST$REMOTE_WP_PATH"
