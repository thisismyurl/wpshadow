#!/bin/bash
# WP-CLI Installation Script for WordPress Container
# This script is executed when the container starts to install WP-CLI

set -e

echo "Installing WP-CLI..."

# Install WP-CLI
curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
chmod +x wp-cli.phar
mv wp-cli.phar /usr/local/bin/wp

# Verify installation
if command -v wp &> /dev/null; then
    echo "✓ WP-CLI installed successfully"
    wp --version
else
    echo "✗ WP-CLI installation failed"
    exit 1
fi
