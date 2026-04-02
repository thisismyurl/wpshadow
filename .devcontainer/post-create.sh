#!/bin/bash
# Post-create script: Runs once when container is created
# Fully automated WordPress + Plugin setup with zero manual intervention

set -e

LOG="/tmp/wpshadow-setup.log"
{

echo "🔧 Post-Create: Automated wpshadow environment setup..."
echo "Log: $LOG"
echo "Timestamp: $(date)"

# ============================================================================
# 0. START DOCKER COMPOSE SERVICES
# ============================================================================
echo ""
echo "🐳 Starting Docker Compose services..."
cd /workspaces/wpshadow

echo "Checking Docker availability..."
docker --version || { echo "❌ Docker not available"; exit 1; }

# ============================================================================
# 0.5 CONFIGURE GIT CREDENTIALS (if GH_TOKEN secret is set)
# ============================================================================
if [ -n "$GH_TOKEN" ]; then
    echo ""
    echo "🔐 Configuring Git credentials from GH_TOKEN secret..."
    git config --global credential.helper store
    echo "https://${GH_TOKEN}@github.com" >> ~/.git-credentials 2>/dev/null || true
    chmod 600 ~/.git-credentials
    echo "✅ Git credentials configured (PAT stored locally)"
else
    echo "ℹ️  GH_TOKEN secret not found - using default GitHub Codespaces authentication"
fi

echo "Starting services with docker compose..."
if docker compose up -d 2>&1; then
    echo "✓ Docker Compose services started"
else
    echo "⚠️  Docker compose up had issues, continuing..."
fi

sleep 5

# ============================================================================
# 1. WAIT FOR MYSQL
# ============================================================================
echo ""
echo "⏳ Waiting for MySQL to be ready..."
for i in {1..120}; do
    if mysqladmin ping -h 127.0.0.1 -u wordpress -pwordpress &>/dev/null 2>&1; then
        echo "✅ MySQL is ready"
        break
    fi
    if [ $i -eq 120 ]; then
        echo "❌ MySQL failed to start after 4 minutes"
        echo "Docker status:"
        docker compose ps
        exit 1
    fi
    echo -n "."
    sleep 2
done

# ============================================================================
# 2. WAIT FOR WORDPRESS SERVICE
# ============================================================================
echo ""
echo "⏳ Waiting for WordPress service to be ready..."
for i in {1..120}; do
    if curl -s http://localhost:8080 &>/dev/null; then
        echo "✅ WordPress service is ready"
        break
    fi
    if [ $i -eq 120 ]; then
        echo "⚠️  WordPress took over 4 minutes, continuing anyway..."
    fi
    echo -n "."
    sleep 2
done

# ============================================================================
# 3. INSTALL COMPOSER DEPENDENCIES
# ============================================================================
if [ -f "composer.json" ]; then
    echo ""
    echo "📦 Installing Composer dependencies..."
    if command -v composer &>/dev/null; then
        composer install --no-interaction --prefer-dist 2>&1 | grep -i "installed\|updated" || true
        echo "✅ Composer dependencies installed"
    else
        echo "⚠️  Composer not found, skipping"
    fi
fi

# ============================================================================
# 4. CREATE DIRECTORIES
# ============================================================================
echo "📁 Setting up directory structure..."
mkdir -p /workspaces/wpshadow/wp-content/plugins/wpshadow
mkdir -p /workspaces/wpshadow/wp-content/debug

# ============================================================================
# 5. VERIFY WORDPRESS INSTALLATION
# ============================================================================
echo ""
echo "🔍 Checking WordPress installation..."
if curl -s http://localhost:8080/wp-admin/ | grep -q "wp-login" || [ $? -eq 0 ]; then
    echo "✓ WordPress is responding"
else
    echo "⏳ WordPress will complete initialization on first access"
fi

# ============================================================================
# 6. INSTALL NODE DEPENDENCIES IF EXISTS
# ============================================================================
if [ -f "package.json" ]; then
    echo ""
    echo "📦 Installing Node dependencies..."
    if command -v npm &>/dev/null; then
        npm install 2>&1 | tail -5 || true
        echo "✅ Node dependencies installed"
    else
        echo "⚠️  npm not found, skipping"
    fi
fi

# ============================================================================
# FINAL: Make all scripts executable
# ============================================================================
echo ""
echo "🔧 Making all scripts executable..."
find /workspaces/wpshadow/scripts -type f \( -name "*.sh" -o -name "*.py" \) -exec chmod +x {} \; 2>/dev/null || true
find /workspaces/wpshadow/dev-tools -type f \( -name "*.sh" -o -name "*.py" \) -exec chmod +x {} \; 2>/dev/null || true
echo "✓ All scripts are executable"

echo ""
echo "✅ Post-create setup complete"
echo "Timestamp: $(date)"
echo ""
echo "📊 Services Status:"
docker compose ps

} 2>&1 | tee -a "$LOG"
