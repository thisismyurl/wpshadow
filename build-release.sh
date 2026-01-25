#!/bin/bash

##
# WPShadow Release Build Script
#
# Creates a production-ready WordPress plugin ZIP file for installation.
# Respects .distignore rules to exclude development files.
##

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Get plugin version from main file
VERSION=$(grep "Version:" wpshadow.php | head -1 | awk '{print $3}')
PLUGIN_SLUG="wpshadow"
BUILD_DIR="build"
RELEASE_DIR="$BUILD_DIR/$PLUGIN_SLUG"
RELEASE_ZIP="$PLUGIN_SLUG-$VERSION.zip"

echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}WPShadow Release Builder${NC}"
echo -e "${GREEN}========================================${NC}"
echo -e "Version: ${YELLOW}$VERSION${NC}"
echo ""

# Clean previous build
if [ -d "$BUILD_DIR" ]; then
    echo -e "${YELLOW}Cleaning previous build...${NC}"
    rm -rf "$BUILD_DIR"
fi

# Create build directory
echo -e "${YELLOW}Creating build directory...${NC}"
mkdir -p "$RELEASE_DIR"

# Copy files using rsync, respecting .distignore
echo -e "${YELLOW}Copying plugin files...${NC}"

# Use rsync with exclude-from to respect .distignore patterns
rsync -av --exclude-from='.distignore' \
    --exclude='build/' \
    --exclude='build/**' \
    --exclude='.git/' \
    --exclude='.git/**' \
    --exclude='*.zip' \
    --exclude='.venv/' \
    --exclude='.venv/**' \
    --exclude='wp-content/' \
    --exclude='wp-content/**' \
    --exclude='.copilot/' \
    --exclude='.copilot/**' \
    --exclude='kb-articles/' \
    --exclude='kb-articles/**' \
    --exclude='kb-articles-content-output.json' \
    --exclude='.kb-index.json' \
    --exclude='KB_PUBLISHING_SUMMARY.md' \
    --exclude='*.py' \
    --exclude='.env' \
    --exclude="'+str(data.get(state)))" \
    ./ "$RELEASE_DIR/"

# Remove any remaining development files
echo -e "${YELLOW}Cleaning development files...${NC}"
find "$RELEASE_DIR" -name ".DS_Store" -delete 2>/dev/null || true
find "$RELEASE_DIR" -name "Thumbs.db" -delete 2>/dev/null || true
find "$RELEASE_DIR" -name "*.swp" -delete 2>/dev/null || true
find "$RELEASE_DIR" -name "*.swo" -delete 2>/dev/null || true
find "$RELEASE_DIR" -name "*~" -delete 2>/dev/null || true

# Create the ZIP file
echo -e "${YELLOW}Creating release package...${NC}"
cd "$BUILD_DIR"
zip -r "../$RELEASE_ZIP" "$PLUGIN_SLUG" -q
cd ..

# Get file size
FILE_SIZE=$(du -h "$RELEASE_ZIP" | cut -f1)

# Summary
echo ""
echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}Release Build Complete!${NC}"
echo -e "${GREEN}========================================${NC}"
echo -e "Package: ${YELLOW}$RELEASE_ZIP${NC}"
echo -e "Size: ${YELLOW}$FILE_SIZE${NC}"
echo -e "Version: ${YELLOW}$VERSION${NC}"
echo ""
echo -e "${GREEN}Installation Instructions:${NC}"
echo "1. Upload to WordPress: Plugins > Add New > Upload Plugin"
echo "2. Or extract to: wp-content/plugins/"
echo "3. Activate through the WordPress admin panel"
echo ""
echo -e "${GREEN}Ready for testing!${NC}"
echo ""
