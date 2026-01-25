#!/bin/bash
set -e

echo "📦 Building WPShadow Release Package..."

# Get plugin version
VERSION=$(grep "Version:" wpshadow.php | awk '{print $3}')
BUILD_DIR="build"
RELEASE_DIR="$BUILD_DIR/wpshadow"

# Clean previous build
echo "🧹 Cleaning previous build..."
rm -rf "$BUILD_DIR"
mkdir -p "$RELEASE_DIR"

# Copy plugin files
echo "📋 Copying plugin files..."
rsync -av \
    --exclude='.git*' \
    --exclude='node_modules' \
    --exclude='vendor' \
    --exclude='tests' \
    --exclude='bin' \
    --exclude='.devcontainer' \
    --exclude='build' \
    --exclude='*.log' \
    --exclude='.env' \
    --exclude='composer.lock' \
    --exclude='package-lock.json' \
    --exclude='phpunit.xml*' \
    --exclude='phpstan.neon' \
    --exclude='.phpunit.cache' \
    --exclude='docs' \
    --exclude='kb-articles' \
    --exclude='tools' \
    --exclude='scripts' \
    --exclude='.venv' \
    --exclude='*.py' \
    --exclude='*.sh' \
    --exclude='docker-compose.yml' \
    --exclude='TESTING_GUIDE.md' \
    --exclude='IMPLEMENTATION_SUMMARY.md' \
    --exclude='KB_PUBLISHING_SUMMARY.md' \
    --exclude='RELEASE_SUMMARY.md' \
    --exclude='CHECKSUMS.txt' \
    --exclude='.kb-index.json' \
    --exclude='kb-articles-content-output.json' \
    ./ "$RELEASE_DIR/"

# Install production dependencies
echo "📦 Installing production dependencies..."
cd "$RELEASE_DIR"
if [ -f "composer.json" ]; then
    composer install --no-dev --optimize-autoloader --no-interaction
fi

# Return to root
cd ../..

# Create ZIP file
echo "🗜️ Creating release ZIP..."
cd "$BUILD_DIR"
zip -r "wpshadow-${VERSION}.zip" wpshadow/
cd ..

echo "✅ Release package created: $BUILD_DIR/wpshadow-${VERSION}.zip"
echo ""
echo "📊 Package contents:"
unzip -l "$BUILD_DIR/wpshadow-${VERSION}.zip" | head -20
