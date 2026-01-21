<?php
/**
 * WPShadow Test Environment - WordPress Configuration Override
 * 
 * This file handles GitHub Codespaces port forwarding and ensures WordPress
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * uses the correct URLs when accessed through the Codespaces domain.
 * 
 * It is automatically mounted into the WordPress container at:
 *   /var/www/html/wp-config-extra.php
 * 
 * And included in wp-config.php via:
 *   require_once('/var/www/html/wp-config-extra.php');
 * 
 * IMPORTANT:
 * - Update the URLs below with YOUR Codespaces domain name
 * - Find it in VS Code PORTS tab or check CODESPACE_NAME env var
 * - Current domain: stunning-fishstick-j69p5j559jqcpw79
 * - Change if you get a different Codespace
 * 
 * @package WPShadow-Testing
 */

// ============================================================
// GitHub Codespaces Port Forwarding Configuration
// ============================================================
// GitHub Codespaces uses a proxy that forwards HTTPS traffic to port 80.
// Force HTTPS and set the correct host

// Always force HTTPS for Codespaces
$_SERVER['HTTPS'] = 'on';

// Force the correct Codespaces hostname
$_SERVER['HTTP_HOST'] = 'fictional-space-bassoon-qr65q7qqx4p2xvgr-9000.app.github.dev';
$_SERVER['SERVER_NAME'] = 'fictional-space-bassoon-qr65q7qqx4p2xvgr-9000.app.github.dev';
$_SERVER['SERVER_PORT'] = '443';

// ============================================================
// WordPress Site URL Configuration
// ============================================================
// These must match the actual Codespaces domain where you access WordPress.
// If WordPress redirects to the wrong URL (e.g., port 443), update these.

// CHANGE THESE TO YOUR CODESPACES DOMAIN:
define('WP_HOME', 'https://fictional-space-bassoon-qr65q7qqx4p2xvgr-9000.app.github.dev');
define('WP_SITEURL', 'https://fictional-space-bassoon-qr65q7qqx4p2xvgr-9000.app.github.dev');

// ============================================================
// Database URLs (from docker-compose-test.yml)
// ============================================================
// Database credentials are set via environment variables in docker-compose-test.yml:
// - WORDPRESS_DB_HOST: db (Docker service name, resolves to MySQL container)
// - WORDPRESS_DB_USER: wordpress
// - WORDPRESS_DB_PASSWORD: wordpress
// - WORDPRESS_DB_NAME: wordpress
//
// These are automatically applied by WordPress and don't need to be defined here.
// ============================================================





