#!/bin/bash
# Post-attach script - runs every time you attach to the container

set -e

echo "╔═══════════════════════════════════════════════════════════════════════════╗"
echo "║              WPShadow Development Environment - Auto Start                ║"
echo "╚═══════════════════════════════════════════════════════════════════════════╝"
echo ""

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# Check if Docker daemon is available
if ! docker info > /dev/null 2>&1; then
    echo -e "${RED}✗ Docker daemon not available${NC}"
    exit 1
fi

# Navigate to workspace root
cd /workspaces/wpshadow

# Check if containers are already running
if docker ps --format '{{.Names}}' | grep -q "wpshadow-test"; then
    echo -e "${GREEN}✓ WPShadow Docker containers already running${NC}"
    
    # Show container status
    echo ""
    echo -e "${BLUE}📦 Container Status:${NC}"
    docker ps --format "table {{.Names}}\t{{.Status}}\t{{.Ports}}" | grep -E "(wpshadow|NAMES)"
    
else
    echo -e "${YELLOW}→ Starting WPShadow Docker containers...${NC}"
    
    # Start containers using docker-compose
    if [ -f "docker-compose.yml" ]; then
        docker-compose up -d
        
        # Wait for containers to be healthy
        echo -e "${YELLOW}→ Waiting for WordPress to be ready...${NC}"
        sleep 10
        
        # Check if containers are running
        if docker ps --format '{{.Names}}' | grep -q "wpshadow-test"; then
            echo -e "${GREEN}✓ Containers started successfully!${NC}"
        else
            echo -e "${RED}✗ Failed to start containers${NC}"
            exit 1
        fi
    else
        echo -e "${RED}✗ docker-compose.yml not found${NC}"
        exit 1
    fi
fi

echo ""
echo -e "${GREEN}╔═══════════════════════════════════════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║                    🚀 WPShadow Environment Ready!                         ║${NC}"
echo -e "${GREEN}╚═══════════════════════════════════════════════════════════════════════════╝${NC}"
echo ""
echo -e "${BLUE}🌐 Access URLs:${NC}"
echo "  Test Site:  https://fictional-space-bassoon-qr65q7qqx4p2xvgr-9000.app.github.dev/"
echo "  Main Site:  https://fictional-space-bassoon-qr65q7qqx4p2xvgr-8080.app.github.dev/"
echo ""
echo -e "${BLUE}🔑 Credentials:${NC}"
echo "  Username: admin"
echo "  Password: admin"
echo ""
echo -e "${BLUE}💬 Chat Agent:${NC}"
echo "  Default agent: @wpshadow (WPShadow Agent)"
echo "  Type '@wpshadow' in chat to invoke the agent"
echo ""
echo -e "${BLUE}📚 Key Documentation:${NC}"
echo "  - DOCKER_TESTING_ENVIRONMENT.md (Testing guide)"
echo "  - .github/agents/WPShadow Agent.agent.md (Agent profile)"
echo "  - docs/PRODUCT_PHILOSOPHY.md (11 commandments)"
echo ""
echo -e "${BLUE}🧪 Quick Test:${NC}"
echo '  curl -s "http://localhost:9000/wp-admin/" | grep -q "wordpress" && echo "✅ WordPress ready" || echo "❌ Not ready"'
echo ""
