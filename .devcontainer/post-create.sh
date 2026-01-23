#!/bin/bash
# Post-create script - runs once when container is created

set -e

echo "╔═══════════════════════════════════════════════════════════════════════════╗"
echo "║              WPShadow Development Environment - Post Create               ║"
echo "╚═══════════════════════════════════════════════════════════════════════════╝"
echo ""

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

echo -e "${YELLOW}→ Setting up WPShadow development environment...${NC}"

# Install Composer dependencies if needed
if [ -f "composer.json" ] && [ ! -d "vendor" ]; then
    echo -e "${YELLOW}→ Installing Composer dependencies...${NC}"
    composer install --no-interaction --prefer-dist || echo "Composer install skipped"
fi

# Make setup script executable
if [ -f "setup-docker.sh" ]; then
    chmod +x setup-docker.sh
fi

# Check if Docker containers are already running
if docker ps --format '{{.Names}}' | grep -q "wpshadow-test"; then
    echo -e "${GREEN}✓ WPShadow Docker containers already running${NC}"
else
    echo -e "${YELLOW}→ Docker containers not running yet (will auto-start on attach)${NC}"
fi

echo -e "${GREEN}✓ Post-create setup complete!${NC}"
echo ""
echo "📚 Documentation:"
echo "  - Docker Testing: DOCKER_TESTING_ENVIRONMENT.md"
echo "  - Agent Profile: .github/agents/WPShadow Agent.agent.md"
echo "  - Product Philosophy: docs/PRODUCT_PHILOSOPHY.md"
echo ""
