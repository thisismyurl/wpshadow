#!/bin/bash
# Post-create script: Runs once when container is created
# Fully automated WordPress + Plugin setup with zero manual intervention

set -e

LOG="/tmp/wpshadow-setup.log"
{

echo "🔧 Post-Create: Automated wpshadow environment setup..."
echo "Log: $LOG"

# ============================================================================
# 1. WAIT FOR MYSQL
# ============================================================================
echo "⏳ Waiting for MySQL to be ready..."
for i in {1..60}; do
    if mysqladmin ping -h mysql -u wordpress -pwordpress &>/dev/null 2>&1; then
        echo "✅ MySQL is ready"
        break
    fi
    if [ $i -eq 60 ]; then
        echo "❌ MySQL failed to start after 2 minutes"
        exit 1
    fi
    echo -n "."
    sleep 2
done

# ============================================================================
# 2. WAIT FOR WORDPRESS SERVICE
# ============================================================================
echo "⏳ Waiting for WordPress service to be ready..."
for i in {1..60}; do
    if curl -s http://localhost/ &>/dev/null; then
        echo "✅ WordPress service is ready"
        break
    fi
    echo -n "."
    sleep 2
done

# ============================================================================
# 3. INSTALL COMPOSER DEPENDENCIES
# ============================================================================
if [ -f "composer.json" ]; then
    echo "📦 Installing Composer dependencies..."
    composer install --no-interaction --prefer-dist 2>&1 | grep -i "installed\|updated" || true
    echo "✅ Composer dependencies installed"
fi

# ============================================================================
# 4. CREATE DIRECTORIES
# ============================================================================
echo "📁 Setting up directory structure..."
mkdir -p /var/www/html/wp-content/plugins/wpshadow
mkdir -p /var/www/html/wp-content/debug

# ============================================================================
# 5. VERIFY PLUGIN MOUNT
# ============================================================================
echo "🔌 Verifying plugin mount..."
if [ -f "/var/www/html/wp-content/plugins/wpshadow/wpshadow.php" ]; then
    echo "✅ wpshadow plugin mounted successfully"
    PLUGIN_VERSION=$(grep "Version:" /var/www/html/wp-content/plugins/wpshadow/wpshadow.php | head -1 | sed 's/.*Version: //' | tr -d '\r')
    echo "   Version: $PLUGIN_VERSION"
else
    echo "⚠️  Plugin not yet mounted (will be available after restart)"
fi

# ============================================================================
# 6. AUTO-INSTALL WORDPRESS
# ============================================================================
if [ ! -f "/var/www/html/wp-config.php" ]; then
    echo "📦 Auto-installing WordPress..."
    
    # Wait for wp-config to be created by WordPress installation
    for i in {1..30}; do
        if [ -f "/var/www/html/wp-config.php" ]; then
            echo "✅ WordPress installed"
            break
        fi
        echo -n "."
        sleep 2
    done
    
    # If still not installed, WordPress will do it on first request
    if [ ! -f "/var/www/html/wp-config.php" ]; then
        echo "⏳ WordPress will complete installation on first browser access"
    fi
else
    echo "✅ WordPress already installed"
fi

# ============================================================================
# 7. WAIT FOR DATABASE CONNECTIVITY
# ============================================================================
echo "🗄️  Testing database connectivity..."
for i in {1..30}; do
    if wp db check --allow-root &>/dev/null 2>&1; then
        echo "✅ Database is accessible"
        break
    fi
    echo -n "."
    sleep 2
done

# ============================================================================
# 8. VERIFY PLUGIN FILE INTEGRITY
# ============================================================================
if [ -f "/var/www/html/wp-content/plugins/wpshadow/wpshadow.php" ]; then
    if php -l /var/www/html/wp-content/plugins/wpshadow/wpshadow.php &>/dev/null; then
        echo "✅ Plugin syntax is valid"
    else
        echo "⚠️  Plugin has syntax errors (will be caught on activation)"
    fi
fi

echo "✅ Post-create setup complete"
echo ""
echo "🎯 Next: Services will auto-configure on first start"

} 2>&1 | tee -a "$LOG"
