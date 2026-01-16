#!/bin/bash
# WPShadow Testing Quick Reference

# Colors for output
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${BLUE}=====================================${NC}"
echo -e "${BLUE}WPShadow Testing Quick Reference${NC}"
echo -e "${BLUE}=====================================${NC}\n"

echo -e "${YELLOW}DOCKER COMMANDS${NC}"
echo "  docker-compose up -d          # Start services"
echo "  docker-compose ps             # View status"
echo "  docker-compose logs -f        # View logs"
echo "  docker-compose down -v        # Stop & remove all (including data)"
echo "  docker exec -it wpshadow-dev bash  # Access container\n"

echo -e "${YELLOW}TEST COMMANDS${NC}"
echo "  composer test                 # Run all tests"
echo "  composer test -- --filter=PluginBootstrapTest  # Run specific test"
echo "  composer test -- --coverage-html=coverage/  # Generate coverage report\n"

echo -e "${YELLOW}CODE QUALITY${NC}"
echo "  composer phpcs                # Check WordPress standards"
echo "  composer phpcbf               # Auto-fix violations"
echo "  composer phpstan              # Static analysis\n"

echo -e "${YELLOW}WORDPRESS CLI${NC}"
echo "  docker exec wpshadow-dev wp plugin list        # List plugins"
echo "  docker exec wpshadow-dev wp plugin activate plugin-wpshadow  # Activate free"
echo "  docker exec wpshadow-dev wp user create admin test@example.com --role=administrator --user_pass=pass123\n"

echo -e "${YELLOW}QUICK START${NC}"
echo -e "  ${GREEN}1. docker-compose up -d${NC}"
echo -e "  ${GREEN}2. composer install${NC}"
echo -e "  ${GREEN}3. composer test${NC}"
echo -e "  ${GREEN}4. Visit http://localhost:8000${NC}\n"

echo -e "${YELLOW}DOCUMENTATION${NC}"
echo "  See TESTING.md for comprehensive guide"
echo "  See DOCKER_TESTING_SETUP.md for setup details"
