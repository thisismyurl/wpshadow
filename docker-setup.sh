#!/bin/bash
# WPShadow Docker Setup Script
# This script sets up and manages the WordPress testing environment

set -e

COLOR_GREEN='\033[0;32m'
COLOR_BLUE='\033[0;34m'
COLOR_YELLOW='\033[1;33m'
COLOR_RED='\033[0;31m'
COLOR_RESET='\033[0m'

echo -e "${COLOR_BLUE}🐳 WPShadow Docker Environment Setup${COLOR_RESET}"
echo ""

# Function to check if Docker is running
check_docker() {
    if ! docker info > /dev/null 2>&1; then
        echo -e "${COLOR_RED}❌ Docker is not running. Please start Docker and try again.${COLOR_RESET}"
        exit 1
    fi
    echo -e "${COLOR_GREEN}✅ Docker is running${COLOR_RESET}"
}

# Function to start containers
start_containers() {
    echo -e "${COLOR_BLUE}Starting containers...${COLOR_RESET}"
    docker-compose up -d
    echo ""
    echo -e "${COLOR_GREEN}✅ Containers started${COLOR_RESET}"
    echo ""
}

# Function to wait for WordPress to be ready
wait_for_wordpress() {
    echo -e "${COLOR_BLUE}Waiting for WordPress to be ready...${COLOR_RESET}"
    
    local max_attempts=60
    local attempt=0
    
    while [ $attempt -lt $max_attempts ]; do
        if docker exec wpshadow-dev curl -f -s http://localhost/wp-admin/install.php > /dev/null 2>&1; then
            echo -e "${COLOR_GREEN}✅ WordPress is ready!${COLOR_RESET}"
            return 0
        fi
        
        attempt=$((attempt + 1))
        echo -n "."
        sleep 2
    done
    
    echo -e "${COLOR_RED}❌ WordPress failed to start within the expected time${COLOR_RESET}"
    return 1
}

# Function to install WP-CLI
install_wpcli() {
    echo -e "${COLOR_BLUE}Installing WP-CLI...${COLOR_RESET}"
    
    docker exec wpshadow-dev bash -c "
        if ! command -v wp &> /dev/null; then
            curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
            chmod +x wp-cli.phar
            mv wp-cli.phar /usr/local/bin/wp
            echo '✅ WP-CLI installed'
        else
            echo '✅ WP-CLI already installed'
        fi
    "
}

# Function to configure WordPress
configure_wordpress() {
    echo -e "${COLOR_BLUE}Configuring WordPress...${COLOR_RESET}"
    
    # Check if WordPress is already installed
    if docker exec wpshadow-dev wp core is-installed --allow-root 2>/dev/null; then
        echo -e "${COLOR_YELLOW}⚠️  WordPress is already installed${COLOR_RESET}"
        echo -e "${COLOR_BLUE}Skipping WordPress installation...${COLOR_RESET}"
    else
        echo -e "${COLOR_BLUE}Installing WordPress...${COLOR_RESET}"
        docker exec wpshadow-dev wp core install \
            --url="http://localhost:8000" \
            --title="WPShadow Test Site" \
            --admin_user="admin" \
            --admin_password="admin" \
            --admin_email="admin@wpshadow.test" \
            --allow-root
        
        echo -e "${COLOR_GREEN}✅ WordPress installed${COLOR_RESET}"
    fi
}

# Function to activate plugins
activate_plugins() {
    echo -e "${COLOR_BLUE}Activating plugins...${COLOR_RESET}"
    
    # List available plugins
    docker exec wpshadow-dev wp plugin list --allow-root
    
    # Activate WPShadow
    if docker exec wpshadow-dev wp plugin is-installed wpshadow --allow-root 2>/dev/null; then
        docker exec wpshadow-dev wp plugin activate wpshadow --allow-root
        echo -e "${COLOR_GREEN}✅ WPShadow activated${COLOR_RESET}"
    else
        echo -e "${COLOR_YELLOW}⚠️  WPShadow plugin not found${COLOR_RESET}"
    fi
    
    # Activate WPShadow Pro
    if docker exec wpshadow-dev wp plugin is-installed wpshadow-pro --allow-root 2>/dev/null; then
        docker exec wpshadow-dev wp plugin activate wpshadow-pro --allow-root
        echo -e "${COLOR_GREEN}✅ WPShadow Pro activated${COLOR_RESET}"
    else
        echo -e "${COLOR_YELLOW}⚠️  WPShadow Pro plugin not found${COLOR_RESET}"
    fi
}

# Function to display access information
display_info() {
    echo ""
    echo -e "${COLOR_GREEN}╔════════════════════════════════════════════════════════╗${COLOR_RESET}"
    echo -e "${COLOR_GREEN}║         🎉 Setup Complete! 🎉                         ║${COLOR_RESET}"
    echo -e "${COLOR_GREEN}╚════════════════════════════════════════════════════════╝${COLOR_RESET}"
    echo ""
    echo -e "${COLOR_BLUE}Access your WordPress site:${COLOR_RESET}"
    echo -e "  🌐 WordPress:   ${COLOR_YELLOW}http://localhost:8000${COLOR_RESET}"
    echo -e "  👤 Username:    ${COLOR_YELLOW}admin${COLOR_RESET}"
    echo -e "  🔑 Password:    ${COLOR_YELLOW}admin${COLOR_RESET}"
    echo ""
    echo -e "${COLOR_BLUE}Database Management:${COLOR_RESET}"
    echo -e "  🗄️  phpMyAdmin:  ${COLOR_YELLOW}http://localhost:8080${COLOR_RESET}"
    echo -e "  👤 Username:    ${COLOR_YELLOW}wordpress${COLOR_RESET}"
    echo -e "  🔑 Password:    ${COLOR_YELLOW}wordpress${COLOR_RESET}"
    echo ""
    echo -e "${COLOR_BLUE}Useful Commands:${COLOR_RESET}"
    echo -e "  View logs:      ${COLOR_YELLOW}docker-compose logs -f${COLOR_RESET}"
    echo -e "  Stop:           ${COLOR_YELLOW}docker-compose stop${COLOR_RESET}"
    echo -e "  Restart:        ${COLOR_YELLOW}docker-compose restart${COLOR_RESET}"
    echo -e "  Clean up:       ${COLOR_YELLOW}docker-compose down -v${COLOR_RESET}"
    echo -e "  WP-CLI:         ${COLOR_YELLOW}docker exec wpshadow-dev wp --allow-root [command]${COLOR_RESET}"
    echo ""
}

# Main execution
main() {
    check_docker
    start_containers
    wait_for_wordpress
    install_wpcli
    configure_wordpress
    activate_plugins
    display_info
}

# Run main function
main
