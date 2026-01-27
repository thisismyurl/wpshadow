<?php
/**
 * Emergency Plugin Update via GitHub
 * 
 * This file can be run via WP-CLI or directly
 * Purpose: Pull latest plugin code from GitHub when needed
 * 
 * Usage:
 *   wp eval-file update-from-github.php
 *   or via browser: https://wpshadow.com/wp-content/plugins/wpshadow/update-from-github.php
 */

// Require WordPress
if ( ! defined( 'ABSPATH' ) ) {
    require_once dirname( dirname( dirname( __FILE__ ) ) ) . '/wp-load.php';
}

// Only allow from CLI or admin
if ( ! defined( 'WP_CLI' ) && ( ! is_user_logged_in() || ! current_user_can( 'manage_options' ) ) ) {
    wp_die( 'Unauthorized' );
}

// Only allow if not in production (safety check)
if ( isset( $_ENV['ENVIRONMENT'] ) && 'production' === $_ENV['ENVIRONMENT'] ) {
    wp_die( 'Updates disabled in production' );
}

echo "Starting WPShadow plugin update from GitHub...\n";

// Plugin directory
$plugin_dir = dirname( __FILE__ );
$plugin_repo = 'https://github.com/thisismyurl/wpshadow.git';

// Check if git is available
if ( ! shell_exec( 'command -v git' ) ) {
    echo "ERROR: git is not available on this server\n";
    exit( 1 );
}

// Update via git
echo "Pulling latest changes from GitHub...\n";
$output = shell_exec( "cd '$plugin_dir' && git pull origin main 2>&1" );
echo $output;

// Clear any caches
if ( function_exists( 'wp_cache_flush' ) ) {
    wp_cache_flush();
    echo "Cleared WordPress cache\n";
}

// Verify the fix worked
$file_to_check = $plugin_dir . '/includes/admin/ajax/First_Scan_Handler.php';
if ( file_exists( $file_to_check ) ) {
    $content = file_get_contents( $file_to_check );
    $open_braces = substr_count( $content, '{' );
    $close_braces = substr_count( $content, '}' );
    
    echo "\n✓ Plugin updated successfully!\n";
    echo "File: $file_to_check\n";
    echo "Braces check: { = $open_braces, } = $close_braces\n";
    
    if ( $open_braces === $close_braces ) {
        echo "✓ Syntax appears correct!\n";
    } else {
        echo "✗ WARNING: Brace mismatch detected!\n";
    }
}

echo "\nUpdate complete.\n";
exit( 0 );
