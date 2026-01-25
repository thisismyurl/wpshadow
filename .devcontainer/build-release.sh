#!/bin/bash

echo "📦 Building release package for WordPress.org submission..."

# Create build directory
rm -rf build
mkdir -p build/wpshadow

# Copy plugin files
echo "Copying plugin files..."
rsync -av \
  --exclude='build' \
  --exclude='node_modules' \
  --exclude='vendor' \
  --exclude='.git' \
  --exclude='.github' \
  --exclude='.devcontainer' \
  --exclude='tests' \
  --exclude='bin' \
  --exclude='.gitignore' \
  --exclude='.gitattributes' \
  --exclude='composer.json' \
  --exclude='composer.lock' \
  --exclude='package.json' \
  --exclude='package-lock.json' \
  --exclude='phpunit.xml.dist' \
  --exclude='docker-compose.yml' \
  --exclude='.env' \
  . build/wpshadow/

# Create zip file
echo "Creating ZIP archive..."
cd build
zip -r wpshadow.zip wpshadow/
cd ..

echo "✅ Release package created: build/wpshadow.zip"
echo "Ready for WordPress.org submission!"
