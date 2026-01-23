#!/bin/bash
# WPShadow Development Environment - One-Time Setup
# Run this once to configure your ultimate dev environment

set -e

# Colors
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m'

echo "╔══════════════════════════════════════════════════════════════════╗"
echo "║      🚀 WPShadow Development Environment Setup                  ║"
echo "╚══════════════════════════════════════════════════════════════════╝"
echo ""

# === 1. Git Hooks ===
echo -e "${BLUE}→ Setting up Git hooks...${NC}"
if [ ! -d ".git" ]; then
    echo -e "${YELLOW}⚠  Not a git repository${NC}"
else
    # Configure git to use custom hooks directory
    git config core.hooksPath .githooks
    chmod +x .githooks/*
    echo -e "${GREEN}✓ Git hooks configured${NC}"
fi
echo ""

# === 2. Shell Aliases ===
echo -e "${BLUE}→ Setting up shell aliases...${NC}"
if ! grep -q "dev-aliases.sh" ~/.bashrc 2>/dev/null; then
    echo "" >> ~/.bashrc
    echo "# WPShadow Development Aliases" >> ~/.bashrc
    echo "source /workspaces/wpshadow/scripts/dev-aliases.sh" >> ~/.bashrc
    echo -e "${GREEN}✓ Added aliases to ~/.bashrc${NC}"
    echo -e "  Run: ${YELLOW}source ~/.bashrc${NC} to activate"
else
    echo -e "${GREEN}✓ Aliases already configured${NC}"
fi
echo ""

# === 3. Composer Cache ===
echo -e "${BLUE}→ Configuring composer cache...${NC}"
COMPOSER_CACHE_DIR_DEFAULT="/workspaces/.composer/cache"
mkdir -p "$COMPOSER_CACHE_DIR_DEFAULT"
export COMPOSER_CACHE_DIR="$COMPOSER_CACHE_DIR_DEFAULT"
if ! grep -q "COMPOSER_CACHE_DIR" ~/.bashrc 2>/dev/null; then
    echo "export COMPOSER_CACHE_DIR=$COMPOSER_CACHE_DIR_DEFAULT" >> ~/.bashrc
    echo -e "${GREEN}✓ Added COMPOSER_CACHE_DIR to ~/.bashrc${NC}"
else
    echo -e "${GREEN}✓ Composer cache already configured${NC}"
fi
echo ""

# === 4. Composer Dependencies ===
echo -e "${BLUE}→ Checking composer dependencies...${NC}"
if [ ! -d "vendor" ]; then
    echo -e "${YELLOW}→ Installing composer dependencies...${NC}"
    composer install --no-interaction
    echo -e "${GREEN}✓ Composer dependencies installed${NC}"
else
    echo -e "${GREEN}✓ Composer dependencies already installed${NC}"
fi
echo ""

# === 5. Docker Environment ===
echo -e "${BLUE}→ Checking Docker environment...${NC}"
if command -v docker &> /dev/null; then
    if docker info > /dev/null 2>&1; then
        echo -e "${GREEN}✓ Docker is running${NC}"

        # Check if containers exist
        if ! docker ps -a --format '{{.Names}}' | grep -q "wpshadow-test"; then
            echo -e "${YELLOW}→ Starting Docker containers...${NC}"
            docker-compose up -d
            sleep 10
            echo -e "${GREEN}✓ Docker containers started${NC}"
        else
            echo -e "${GREEN}✓ Docker containers already exist${NC}"
        fi
    else
        echo -e "${YELLOW}⚠  Docker is installed but not running${NC}"
        echo -e "  Start it and run: ${YELLOW}docker-compose up -d${NC}"
    fi
else
    echo -e "${YELLOW}⚠  Docker not found - install it for WordPress testing${NC}"
fi
echo ""

# === 6. VS Code Extensions ===
echo -e "${BLUE}→ Recommended VS Code extensions...${NC}"
if command -v code &> /dev/null; then
    EXTENSIONS=(
        "bmewburn.vscode-intelephense-client"
        "github.copilot"
        "github.copilot-chat"
        "eamodio.gitlens"
        "editorconfig.editorconfig"
    )

    for ext in "${EXTENSIONS[@]}"; do
        if code --list-extensions | grep -q "$ext"; then
            echo -e "${GREEN}✓ $ext${NC}"
        else
            echo -e "${YELLOW}→ Installing $ext...${NC}"
            code --install-extension "$ext" --force
        fi
    done
else
    echo -e "${YELLOW}⚠  'code' command not available${NC}"
    echo -e "  Install recommended extensions from .vscode/extensions.json"
fi
echo ""

# === 7. Quick Test ===
echo -e "${BLUE}→ Running initial health check...${NC}"
./scripts/guardian-check.sh || echo -e "${YELLOW}Some issues found - review above${NC}"
echo ""

# === Summary ===
echo "╔══════════════════════════════════════════════════════════════════╗"
echo "║              ✅ Setup Complete!                                   ║"
echo "╚══════════════════════════════════════════════════════════════════╝"
echo ""
echo -e "${GREEN}Your WPShadow development environment is ready!${NC}"
echo ""
echo -e "${BLUE}Quick Start:${NC}"
echo "  1. Reload shell: source ~/.bashrc"
echo "  2. Run guardian check: guardian"
echo "  3. Start Docker: dup"
echo "  4. Check sync: gsync"
echo ""
echo -e "${BLUE}Available Commands:${NC}"
echo "  guardian    - Full environment check"
echo "  gsync       - Check git sync"
echo "  gcommit     - Quick commit & push"
echo "  phpcs       - Check coding standards"
echo "  quicktest   - Test WordPress load"
echo "  wp          - WordPress CLI"
echo ""
echo -e "${BLUE}Documentation:${NC}"
echo "  philosophy  - View product philosophy"
echo "  roadmap     - View development roadmap"
echo "  status      - View technical status"
echo ""
echo "Happy coding! 🎉"
echo ""
