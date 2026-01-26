#!/bin/bash
#
# WPShadow Deployment Setup
# Configures SSH keys and deployment settings from Codespace secrets
#
# Usage:
#   1. Add these secrets to your GitHub Codespace:
#      - GREENGEEKS_SSH_PRIVATE_KEY (paste private key content)
#      - GREENGEEKS_SSH_PUBLIC_KEY (paste public key content)
#   
#   2. This script runs automatically on container start
#   3. Or run manually: bash .devcontainer/setup-deployment.sh
#

set -e

echo "🔐 Setting up deployment configuration..."

# Create SSH directory if it doesn't exist
mkdir -p ~/.ssh
chmod 700 ~/.ssh

# Setup SSH keys from Codespace secrets
if [ -n "$GREENGEEKS_SSH_PRIVATE_KEY" ]; then
    echo "✅ Found GREENGEEKS_SSH_PRIVATE_KEY secret"
    echo "$GREENGEEKS_SSH_PRIVATE_KEY" > ~/.ssh/greengeeks_rsa
    chmod 600 ~/.ssh/greengeeks_rsa
    echo "✅ Private key configured"
else
    echo "⚠️  GREENGEEKS_SSH_PRIVATE_KEY secret not found"
    echo "   Add it in: Settings → Codespaces → Secrets"
fi

if [ -n "$GREENGEEKS_SSH_PUBLIC_KEY" ]; then
    echo "✅ Found GREENGEEKS_SSH_PUBLIC_KEY secret"
    echo "$GREENGEEKS_SSH_PUBLIC_KEY" > ~/.ssh/greengeeks_rsa.pub
    chmod 644 ~/.ssh/greengeeks_rsa.pub
    echo "✅ Public key configured"
fi

# Create deployment config if SSH keys exist
if [ -f ~/.ssh/greengeeks_rsa ]; then
    if [ ! -f .deploy-git.env ]; then
        echo "📝 Creating deployment configuration..."
        cat > .deploy-git.env << 'EOF'
# Git Deployment Configuration for GreenGeeks
# Auto-generated from Codespace secrets

REMOTE_HOST=mtl202.greengeeks.net
REMOTE_USER=sailmar1
REMOTE_PORT=22
SSH_KEY_FILE=~/.ssh/greengeeks_rsa

# Path to WordPress plugins directory on GreenGeeks
# Site URL: https://wpshadow.com/
# Site root: /public_html/wpshadow
REMOTE_WP_PATH=/home/sailmar1/public_html/wpshadow/wp-content/plugins/wpshadow

# Git branch to deploy
DEPLOY_BRANCH=main
EOF
        echo "✅ Deployment config created"
    else
        echo "✅ Deployment config already exists"
    fi
    
    # Add git remote if not exists
    if ! git remote get-url greengeeks &>/dev/null; then
        git remote add greengeeks "ssh://sailmar1@mtl202.greengeeks.net:22/home/sailmar1/public_html/wpshadow/wp-content/plugins/wpshadow" 2>/dev/null || true
        echo "✅ Git remote 'greengeeks' added"
    fi
    
    echo ""
    echo "🎉 Deployment ready! Use: ./deploy-git.sh"
else
    echo ""
    echo "ℹ️  To enable auto-deployment:"
    echo "   1. Go to: https://github.com/settings/codespaces"
    echo "   2. Add secrets:"
    echo "      - GREENGEEKS_SSH_PRIVATE_KEY"
    echo "      - GREENGEEKS_SSH_PUBLIC_KEY"
    echo "   3. Rebuild your Codespace"
fi

echo ""
