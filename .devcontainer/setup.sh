#!/bin/bash

# WPShadow Development Environment Setup
# =======================================
# Commandment #1: Helpful Neighbor Experience
# Commandment #7: Ridiculously Good for Free
# Commandment #8: Inspire Confidence
#
# This script sets up your complete WordPress development environment
# automatically. It's like having an experienced developer set up your
# workstation - everything configured correctly from the start.
#
# Why this script exists:
# - Saves 30+ minutes of manual setup
# - Ensures consistent configuration across all developers
# - Installs and configures industry-standard tools
# - Sets correct permissions automatically
#
# What you'll get:
# - WP-CLI for command-line WordPress management
# - WordPress Coding Standards (PHPCS)
# - Static analysis tools (PHPStan)
# - Fully configured WordPress instance
#
# Estimated time: 3-5 minutes
# Learn more: https://docs.wpshadow.com/dev-environment/setup
#
# =======================================

set -e

# Source our helpful error handler
source "$(dirname "$0")/lib/helpful-errors.sh"

echo ""
echo "🚀 Setting up WordPress Plugin Development Environment..."
echo ""
echo "⏱️  This will take about 3-5 minutes. Perfect time for a ☕"
echo ""

# Install WP-CLI
# --------------
# Why: WP-CLI is the WordPress command-line interface. It lets you manage
# WordPress without using a web browser - much faster for developers!
#
# Examples:
#   wp plugin list          - List all plugins
#   wp user create          - Create a new user
#   wp db export            - Backup the database
#
# Learn more: https://docs.wpshadow.com/tools/wp-cli/basics (15 min)
echo "📦 Installing WP-CLI..."
progress_message "Downloading WP-CLI from official source..."

if curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar 2>&1; then
    chmod +x wp-cli.phar
    sudo mv wp-cli.phar /usr/local/bin/wp
    success_message "WP-CLI installed successfully!" "Try: wp --version"
else
    helpful_error \
        "Failed to download WP-CLI" \
        "This usually happens when GitHub is temporarily unavailable. The installer will retry automatically." \
        "https://docs.wpshadow.com/troubleshooting/wp-cli-install"
    exit 1
fi

# Wait for WordPress to be ready
# -------------------------------
# Why: WordPress runs in a separate Docker container. We need to wait
# until MySQL is ready and WordPress is fully installed before we can
# activate plugins or make changes.
#
# Technical: We use WP-CLI to check if WordPress core is installed,
# checking every 2 seconds until it's ready.
echo ""
echo "⏳ Waiting for WordPress to be ready..."
progress_message "Checking WordPress installation status..."

WAIT_COUNT=0
until wp core is-installed --allow-root 2>/dev/null; do
    sleep 2
    WAIT_COUNT=$((WAIT_COUNT + 1))
    
    if [ $WAIT_COUNT -gt 60 ]; then
        helpful_error \
            "WordPress took too long to start (over 2 minutes)" \
            "This might mean Docker is low on resources or MySQL failed to start. Try rebuilding the container." \
            "https://docs.wpshadow.com/troubleshooting/wp-timeout"
        exit 1
    fi
done

educational_tip "WordPress is ready! The database is running and core files are configured."

# Install Composer dependencies if composer.json exists
# ------------------------------------------------------
# Why: Composer manages PHP dependencies - third-party libraries and
# development tools your plugin needs. It's like npm for PHP.
#
# What it installs: PHPUnit (testing), PHPStan (static analysis),
# PHPCS (coding standards), and any plugin dependencies.
#
# Learn more: https://docs.wpshadow.com/tools/composer/basics (10 min)
if [ -f "composer.json" ]; then
    echo ""
    echo "📦 Installing Composer dependencies..."
    progress_message "This includes testing tools, code standards, and more..."
    
    if composer install --no-interaction 2>&1; then
        success_message "Composer dependencies installed!"
    else
        helpful_error \
            "Composer install failed" \
            "This might be due to dependency conflicts or network issues. Check composer.json for syntax errors." \
            "https://docs.wpshadow.com/troubleshooting/composer"
        exit 1
    fi
fi

# Install WordPress Coding Standards
# -----------------------------------
# Why: WordPress has specific coding standards (based on PHP_CodeSniffer).
# Following these standards ensures your code is:
# - Consistent with WordPress core and other plugins
# - More readable for other developers
# - More likely to be accepted on WordPress.org
#
# What's installed:
# - WPCS: WordPress Coding Standards
# - PHPCompatibilityWP: Checks PHP version compatibility
# - PHPStan: Finds bugs without running code
# - VIPWPCS: WordPress VIP standards (enterprise level)
#
# Learn more: https://docs.wpshadow.com/tools/phpcs/getting-started (10 min)
echo ""
echo "📋 Installing WordPress Coding Standards..."
progress_message "Installing PHPCS, WordPress standards, and analysis tools..."

if composer global require --dev \
    wp-coding-standards/wpcs:"^3.0" \
    phpcompatibility/phpcompatibility-wp:"*" \
    phpstan/phpstan:"^1.10" \
    automattic/vipwpcs:"^3.0" 2>&1; then
    success_message "WordPress Coding Standards installed!" "Time saved: ~15 minutes of manual setup"
else
    helpful_error \
        "Failed to install WordPress Coding Standards" \
        "This might be a temporary Packagist (PHP package registry) issue. Try running setup again." \
        "https://docs.wpshadow.com/troubleshooting/phpcs-install"
    exit 1
fi

# Configure PHPCS
# ----------------
# Why: PHPCS needs to know where to find the WordPress coding standards
# we just installed. This configuration tells it where to look.
#
# Technical: We're setting the 'installed_paths' to include multiple
# standard directories, and setting WordPress as the default standard.
echo ""
echo "⚙️ Configuring PHPCS..."
progress_message "Setting up WordPress as default coding standard..."

if phpcs --config-set installed_paths ~/.composer/vendor/wp-coding-standards/wpcs,~/.composer/vendor/phpcompatibility/phpcompatibility-wp,~/.composer/vendor/automattic/vipwpcs 2>&1 && \
   phpcs --config-set default_standard WordPress 2>&1; then
    educational_tip "PHPCS is now configured! Run 'phpcs' to check your code anytime."
else
    helpful_error \
        "Failed to configure PHPCS" \
        "The tools installed but configuration failed. You can manually set it later." \
        "https://docs.wpshadow.com/troubleshooting/phpcs-config"
    # Don't exit - this isn't critical
fi

# Install Node dependencies if package.json exists
# -------------------------------------------------
# Why: If your plugin has JavaScript, CSS builds, or uses modern
# frontend tools, you'll need Node.js dependencies.
#
# Common use cases:
# - Webpack for bundling JavaScript
# - Sass/SCSS compilation
# - ESLint for JavaScript linting
# - Build scripts for production assets
#
# Learn more: https://docs.wpshadow.com/tools/npm/basics (10 min)
if [ -f "package.json" ]; then
    echo ""
    echo "📦 Installing Node dependencies..."
    progress_message "Installing JavaScript/CSS build tools..."
    
    if npm install 2>&1; then
        success_message "Node dependencies installed!"
    else
        helpful_error \
            "npm install failed" \
            "This might be due to network issues or package.json syntax errors." \
            "https://docs.wpshadow.com/troubleshooting/npm"
        # Don't exit - node deps might not be critical
    fi
fi

# Set permissions
# ----------------
# Why: WordPress needs to write to the plugin directory for things like:
# - Saving settings
# - Creating/updating files
# - Installing dependencies
#
# Technical: We set the owner to 'www-data' (the web server user) so
# WordPress can modify files through the web interface.
#
# Security note: This is safe in a dev container but wouldn't be done
# this way in production.
echo ""
echo "🔐 Setting correct permissions..."
progress_message "Configuring file ownership for WordPress..."

if sudo chown -R www-data:www-data /var/www/html/wp-content/plugins/wpshadow 2>&1; then
    educational_tip "Permissions set! WordPress can now manage plugin files."
else
    helpful_error \
        "Failed to set permissions" \
        "This might not be critical. Try activating the plugin - if it works, you're fine!" \
        "https://docs.wpshadow.com/troubleshooting/permissions"
    # Don't exit - continue anyway
fi

# Activate the plugin
# -------------------
# Why: Automatically activate the WPShadow plugin so you can start
# developing immediately. No need to log into WordPress admin!
#
# Technical: Uses WP-CLI to activate. If WordPress isn't fully ready,
# we show a friendly message instead of failing.
echo ""
echo "🔌 Activating WP Shadow plugin..."

if wp plugin activate wpshadow --allow-root 2>&1; then
    success_message "WP Shadow plugin activated!" "Start developing right away!"
else
    educational_tip "Plugin activation will complete when WordPress finishes setup. You can manually activate it later if needed."
fi

# Initialize KPI tracking
echo ""
echo "📊 Initializing development KPI tracking..."
source "$(dirname "$0")/lib/dev-kpis.sh"
init_kpis
educational_tip "Your development progress is now being tracked! Run 'composer kpi' anytime to see your stats."

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "✅ Setup Complete!"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "🎉 Your development environment is ready!"
echo ""
echo "📍 Access Points:"
echo "   WordPress:    http://localhost:8080"
echo "   phpMyAdmin:   http://localhost:8081"
echo "   MySQL:        localhost:3306"
echo ""
echo "🛠️ Quick Start Commands:"
echo "   composer kpi                   See your development progress"
echo "   composer phpcs                 Check coding standards"
echo "   composer test                  Run tests"
echo "   wp plugin list                 List all plugins"
echo ""
echo "📚 Learning Resources:"
echo "   • Quick Start: .devcontainer/README.md"
echo "   • Full Catalog: .devcontainer/LEARNING_RESOURCES.md"
echo "   • Knowledge Base: https://docs.wpshadow.com"
echo ""
echo "🆘 Need Help?"
echo "   • Forum: https://forum.wpshadow.com"
echo "   • Office Hours: Tuesdays 2pm UTC (Free!)"
echo ""
echo "💡 Pro Tip: Check your progress anytime with 'composer kpi'"
echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
