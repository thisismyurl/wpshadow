#!/bin/bash
#
# WPShadow FTP Deployment Script
# Deploys plugin to GreenGeeks via FTP
#

set -e

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

CONFIG_FILE=".deploy-ftp.env"

if [ ! -f "$CONFIG_FILE" ]; then
    echo -e "${RED}Error: $CONFIG_FILE not found${NC}"
    echo "Copy .deploy-ftp.env.example to .deploy-ftp.env and configure it"
    exit 1
fi

source "$CONFIG_FILE"

if [ -z "$FTP_HOST" ] || [ -z "$FTP_USER" ] || [ -z "$FTP_PASSWORD" ] || [ -z "$FTP_REMOTE_PATH" ]; then
    echo -e "${RED}Error: Missing required FTP configuration${NC}"
    exit 1
fi

echo -e "${BLUE}═══════════════════════════════════════${NC}"
echo -e "${BLUE}    WPShadow FTP Deployment${NC}"
echo -e "${BLUE}═══════════════════════════════════════${NC}"
echo ""
echo -e "${YELLOW}Configuration:${NC}"
echo "  Host: $FTP_HOST"
echo "  User: $FTP_USER"
echo "  Remote: $FTP_REMOTE_PATH"
echo ""

# Update version number with current timestamp in Toronto timezone
# Format: 1.[y][ddd].[hh][mm]
# y = last digit of year (2026 -> 6)
# ddd = julian date with leading zeros (001-366)
# hh = hours with leading zeros (00-23) in Toronto time
# mm = minutes with leading zeros (00-59) in Toronto time

TORONTO_TIME=$(TZ=America/Toronto date +"%y %j %H %M")
read YEAR_DIGIT JULIAN_DAY HOUR MINUTE <<< "$TORONTO_TIME"
NEW_VERSION="1.${YEAR_DIGIT}${JULIAN_DAY}.${HOUR}${MINUTE}"
echo -e "${YELLOW}Updating version to: ${GREEN}$NEW_VERSION${NC}"
echo -e "${BLUE}(Toronto time: $(TZ=America/Toronto date '+%Y-%m-%d %H:%M:%S %Z'))${NC}"

# Update version in main plugin file
sed -i "s/Version: 1\.[0-9][0-9][0-9]\.[0-9]\+/Version: $NEW_VERSION/" wpshadow.php
sed -i "s/define( 'WPSHADOW_VERSION', '1\.[0-9][0-9][0-9]\.[0-9]\+' );/define( 'WPSHADOW_VERSION', '$NEW_VERSION' );/" wpshadow.php

# Update version in readme.txt
sed -i "s/Stable tag: 1\.[0-9][0-9][0-9]\.[0-9]\+/Stable tag: $NEW_VERSION/" readme.txt

echo -e "${GREEN}✓${NC} Version updated to $NEW_VERSION"
echo ""

# Auto-commit all changes
if ! git diff-index --quiet HEAD --; then
    echo -e "${YELLOW}Committing changes...${NC}"
    git add .
    git commit -m "Deploy v$NEW_VERSION" || true
    echo -e "${GREEN}✓${NC} Changes committed"
else
    echo -e "${GREEN}✓${NC} No uncommitted changes"
fi

echo ""
echo -e "${YELLOW}Detecting changed files...${NC}"

# Get list of changed files since last tag or all files if no tag exists
if git describe --tags &>/dev/null; then
    LAST_TAG=$(git describe --tags --abbrev=0)
    echo -e "${BLUE}Last deployment tag: ${YELLOW}$LAST_TAG${NC}"
    CHANGED_FILES=$(git diff --name-only "$LAST_TAG" HEAD 2>/dev/null || echo "")
    if [ -z "$CHANGED_FILES" ]; then
        echo -e "${YELLOW}No changes detected since $LAST_TAG${NC}"
        echo "Use: ${BLUE}git tag -a v$NEW_VERSION -m \"Deploy v$NEW_VERSION\"${NC} to mark current deployment"
        exit 0
    fi
else
    echo -e "${YELLOW}No previous deployment tags found - deploying all files${NC}"
    CHANGED_FILES=$(git ls-files)
fi

# Exclude certain paths from deployment
EXCLUDE_PATTERNS=("docs/" "node_modules/" "vendor/" "tests/" "test-results/" "dev-tools/" "build/" ".devcontainer/" ".github/" ".git/" ".tmp/" ".copilot/")
FILTERED_FILES=()

while IFS= read -r file; do
    SKIP=false
    
    # Skip hidden files
    if [[ "$file" == .* ]]; then
        SKIP=true
    fi
    
    # Skip excluded patterns
    for pattern in "${EXCLUDE_PATTERNS[@]}"; do
        if [[ "$file" == "$pattern"* ]]; then
            SKIP=true
            break
        fi
    done
    
    # Skip config files
    if [[ "$file" =~ \.(env|log|example)$ ]] || [[ "$file" =~ deploy- ]] || [[ "$file" =~ ^(phpcs|phpunit|playwright|package|composer|run-tests|validate-tests|build-release) ]]; then
        SKIP=true
    fi
    
    if [ "$SKIP" = false ]; then
        FILTERED_FILES+=("$file")
    fi
done <<< "$CHANGED_FILES"

if [ ${#FILTERED_FILES[@]} -eq 0 ]; then
    echo -e "${YELLOW}No deployable files changed${NC}"
    exit 0
fi

echo -e "${GREEN}✓${NC} Found ${#FILTERED_FILES[@]} file(s) to deploy:"
for file in "${FILTERED_FILES[@]}"; do
    echo "  ${BLUE}→${NC} $file"
done
echo ""

echo -e "${YELLOW}Building plugin...${NC}"

# Create build directory with only changed files
BUILD_DIR="build/wpshadow"
rm -rf build/
mkdir -p "$BUILD_DIR"

# Copy only changed files preserving directory structure
for file in "${FILTERED_FILES[@]}"; do
    mkdir -p "$BUILD_DIR/$(dirname "$file")"
    cp "$file" "$BUILD_DIR/$file"
done

echo -e "${GREEN}✓${NC} Build prepared (${#FILTERED_FILES[@]} file(s))"
echo ""

# Deploy via FTP
echo -e "${YELLOW}Deploying to FTP server...${NC}"

# Create temporary mirror script for lftp
MIRROR_SCRIPT=$(mktemp)
cat > "$MIRROR_SCRIPT" << EOF
set ftp:ssl-allow no
set ftp:passive-mode on
open -u "$FTP_USER,$FTP_PASSWORD" $FTP_HOST
cd "$FTP_REMOTE_PATH" || exit 1
mirror --reverse --delete --verbose=3 --parallel=4 \
    --exclude=.git/ \
    --exclude=.github/ \
    --exclude=.devcontainer/ \
    --exclude=.copilot/ \
    --exclude=.tmp/ \
    --exclude=.distignore \
    --exclude=.editorconfig \
    --exclude=.gitignore \
    --exclude=docs/ \
    --exclude=node_modules/ \
    --exclude=vendor/ \
    --exclude=tests/ \
    --exclude=*.log \
    "$BUILD_DIR/" .
bye
EOF

# Check if lftp is available, if not use curl
if command -v lftp &> /dev/null; then
    echo -e "${BLUE}Using lftp for efficient mirroring...${NC}"
    lftp -f "$MIRROR_SCRIPT"
    rm "$MIRROR_SCRIPT"
else
    echo -e "${BLUE}Using curl for FTP upload...${NC}"
    rm "$MIRROR_SCRIPT"
    
    # Function to upload file via curl
    upload_file() {
        local local_file="$1"
        local remote_file="$2"
        
        curl -s -T "$local_file" \
            --user "$FTP_USER:$FTP_PASSWORD" \
            --ftp-create-dirs \
            "ftp://$FTP_HOST$FTP_REMOTE_PATH/$remote_file" || echo "Failed: $remote_file"
    }
    
    export -f upload_file
    export FTP_USER FTP_PASSWORD FTP_HOST FTP_REMOTE_PATH
    
    # Upload only changed files
    cd "$BUILD_DIR"
    for file in "${FILTERED_FILES[@]}"; do
        remote_path="${file#./}"
        echo "  Uploading: $remote_path"
        upload_file "$file" "$remote_path"
    done
    cd - > /dev/null
fi

echo ""
echo -e "${GREEN}═══════════════════════════════════════${NC}"
echo -e "${GREEN}    ✓ Deployment Complete!${NC}"
echo -e "${GREEN}═══════════════════════════════════════${NC}"
echo ""
echo -e "${YELLOW}Details:${NC}"
echo "  Version: ${GREEN}$NEW_VERSION${NC}"
echo "  Location: ${BLUE}$FTP_HOST$FTP_REMOTE_PATH${NC}"
echo "  Build: ${BUILD_DIR}"
echo ""
echo -e "${YELLOW}Next steps:${NC}"
echo "  1. Visit: https://wpshadow.com/wp-admin/plugins.php"
echo "  2. Verify the plugin updated to version $NEW_VERSION"
echo ""
