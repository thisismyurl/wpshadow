#!/bin/bash

# WPShadow Docker Environment Setup Script
# This script helps you set up the complete development environment

set -e

echo "╔═══════════════════════════════════════════════════════════════════════════╗"
echo "║           WPShadow Docker Development Environment Setup                   ║"
echo "╚═══════════════════════════════════════════════════════════════════════════╝"
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check if Docker is running
if ! docker info > /dev/null 2>&1; then
    echo -e "${RED}✗ Docker is not running. Please start Docker first.${NC}"
    exit 1
fi

echo -e "${GREEN}✓ Docker is running${NC}"

# Check if docker-compose is available
if ! command -v docker-compose &> /dev/null; then
    echo -e "${RED}✗ docker-compose not found. Please install it first.${NC}"
    exit 1
fi

echo -e "${GREEN}✓ docker-compose is available${NC}"

# Check if theme directory exists
if [ ! -d "/workspaces/theme-wpshadow" ]; then
    echo -e "${YELLOW}! Theme directory not found at /workspaces/theme-wpshadow${NC}"
    echo "  Creating placeholder directory..."
    mkdir -p /workspaces/theme-wpshadow
    
    # Create a basic theme structure
    mkdir -p /workspaces/theme-wpshadow/{css,js,inc,templates}
    
    cat > /workspaces/theme-wpshadow/style.css << 'EOF'
/*
Theme Name: WPShadow Theme
Theme URI: https://wpshadow.com
Author: Christopher Ross
Author URI: https://thisismyurl.com
Description: Official theme for WPShadow.com - WordPress Site Management Made Easy
Version: 1.0.0
License: GNU General Public License v2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Text Domain: wpshadow-theme
*/

/* Theme styles will go here */
EOF

    cat > /workspaces/theme-wpshadow/index.php << 'EOF'
<?php
/**
 * Main template file
 *
 * @package WPShadow_Theme
 */

get_header();
?>

<main id="main" class="site-main">
    <h1>WPShadow Theme</h1>
    <p>Theme is installed. Add your template files here.</p>
    
    <?php
    if ( have_posts() ) :
        while ( have_posts() ) :
            the_post();
            the_title( '<h2>', '</h2>' );
            the_content();
        endwhile;
    endif;
    ?>
</main>

<?php
get_footer();
EOF

    cat > /workspaces/theme-wpshadow/functions.php << 'EOF'
<?php
/**
 * WPShadow Theme functions and definitions
 *
 * @package WPShadow_Theme
 */

// Theme setup
function wpshadow_theme_setup() {
    // Add theme support
    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption' ) );
    
    // Register navigation menus
    register_nav_menus( array(
        'primary' => __( 'Primary Menu', 'wpshadow-theme' ),
        'footer'  => __( 'Footer Menu', 'wpshadow-theme' ),
    ) );
}
add_action( 'after_setup_theme', 'wpshadow_theme_setup' );

// Enqueue scripts and styles
function wpshadow_theme_scripts() {
    wp_enqueue_style( 'wpshadow-theme-style', get_stylesheet_uri(), array(), '1.0.0' );
}
add_action( 'wp_enqueue_scripts', 'wpshadow_theme_scripts' );
EOF

    cat > /workspaces/theme-wpshadow/header.php << 'EOF'
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<header class="site-header">
    <div class="container">
        <h1><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php bloginfo( 'name' ); ?></a></h1>
        <nav class="main-navigation">
            <?php
            wp_nav_menu( array(
                'theme_location' => 'primary',
                'fallback_cb'    => false,
            ) );
            ?>
        </nav>
    </div>
</header>
EOF

    cat > /workspaces/theme-wpshadow/footer.php << 'EOF'
<footer class="site-footer">
    <div class="container">
        <p>&copy; <?php echo date( 'Y' ); ?> <?php bloginfo( 'name' ); ?>. All rights reserved.</p>
        <?php
        wp_nav_menu( array(
            'theme_location' => 'footer',
            'fallback_cb'    => false,
        ) );
        ?>
    </div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
EOF

    echo -e "${GREEN}✓ Created basic theme structure${NC}"
else
    echo -e "${GREEN}✓ Theme directory exists${NC}"
fi

# Start Docker containers
echo ""
echo "Starting Docker containers..."
docker-compose up -d

echo ""
echo "Waiting for services to be ready..."
sleep 10

# Check if containers are running
if docker-compose ps | grep -q "Up"; then
    echo -e "${GREEN}✓ Containers are running${NC}"
else
    echo -e "${RED}✗ Some containers failed to start. Check logs with: docker-compose logs${NC}"
    exit 1
fi

echo ""
echo "╔═══════════════════════════════════════════════════════════════════════════╗"
echo "║                        Setup Complete! 🎉                                  ║"
echo "╚═══════════════════════════════════════════════════════════════════════════╝"
echo ""
echo "Your WPShadow development environment is ready:"
echo ""
echo -e "${GREEN}Main Site (WPShadow.com):${NC}"
echo "  URL:      http://localhost:8080"
echo "  Admin:    http://localhost:8080/wp-admin"
echo "  Database: wpshadow_site"
echo ""
echo -e "${GREEN}Test Site (Plugin Testing):${NC}"
echo "  URL:      https://fictional-space-bassoon-qr65q7qqx4p2xvgr-9000.app.github.dev/"
echo "  Admin:    https://fictional-space-bassoon-qr65q7qqx4p2xvgr-9000.app.github.dev/wp-admin"
echo "  Database: wpshadow_test"
echo ""
echo -e "${GREEN}Supporting Services:${NC}"
echo "  phpMyAdmin: http://localhost:8081"
echo "  MailHog:    http://localhost:8025"
echo ""
echo -e "${YELLOW}Next Steps:${NC}"
echo "  1. Complete WordPress setup for both sites:"
echo "     • Main Site:  http://localhost:8080"
echo "     • Test Site:  https://fictional-space-bassoon-qr65q7qqx4p2xvgr-9000.app.github.dev/"
echo ""
echo "  2. Activate theme on main site:"
echo "     docker-compose exec wordpress-site wp --allow-root theme activate theme-wpshadow"
echo ""
echo "  3. Activate plugin on both sites:"
echo "     docker-compose exec wordpress-site wp --allow-root plugin activate wpshadow"
echo "     docker-compose exec wordpress-test wp --allow-root plugin activate wpshadow"
echo ""
echo -e "${YELLOW}Useful Commands:${NC}"
echo "  View logs:    docker-compose logs -f"
echo "  Stop:         docker-compose down"
echo "  Restart:      docker-compose restart"
echo ""
echo "For more information, see docker-compose.README.md"
echo ""
