#!/bin/bash
# Quick WordPress test environment setup

set -e

TESTDIR="/tmp/wpshadow-test"
PORT=${1:-8000}

# Clean up if exists
rm -rf "$TESTDIR"
mkdir -p "$TESTDIR/html"

cd "$TESTDIR"

# Download WordPress
echo "📥 Downloading WordPress..."
curl -s https://wordpress.org/latest.zip -o wordpress.zip
unzip -q wordpress.zip
mv wordpress/* html/
rm -rf wordpress wordpress.zip

# Copy plugin
echo "📋 Copying plugin..."
cp -r /workspaces/wpshadow html/wp-content/plugins/wpshadow

# Create wp-config.php for testing
echo "⚙️  Setting up wp-config..."
cat > html/wp-config.php << 'EOF'
<?php
define( 'DB_NAME', ':memory:' );
define( 'DB_USER', 'root' );
define( 'DB_PASSWORD', '' );
define( 'DB_HOST', 'localhost' );
define( 'DB_CHARSET', 'utf8mb4' );
define( 'DB_COLLATE', '' );

define( 'AUTH_KEY',         'put your unique phrase here' );
define( 'SECURE_AUTH_KEY',  'put your unique phrase here' );
define( 'LOGGED_IN_KEY',    'put your unique phrase here' );
define( 'NONCE_KEY',        'put your unique phrase here' );
define( 'AUTH_SALT',        'put your unique phrase here' );
define( 'SECURE_AUTH_SALT', 'put your unique phrase here' );
define( 'LOGGED_IN_SALT',   'put your unique phrase here' );
define( 'NONCE_SALT',       'put your unique phrase here' );

$table_prefix = 'wp_';

define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );

if ( ! defined( 'ABSPATH' ) ) {
    define( 'ABSPATH', __DIR__ . '/' );
}

require_once ABSPATH . 'wp-settings.php';
EOF

# Start PHP server
echo ""
echo "✅ WordPress test environment ready!"
echo "🚀 Starting PHP server on http://localhost:$PORT"
echo "📦 Plugin: /wp-content/plugins/wpshadow"
echo "📁 Root: $TESTDIR/html"
echo ""
echo "Press Ctrl+C to stop"
echo ""

cd html
php -S localhost:$PORT

EOF
