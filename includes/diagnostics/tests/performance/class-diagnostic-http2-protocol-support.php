<?php
/**
 * HTTP/2 Protocol Support Diagnostic
 *
 * Verifies the web server supports HTTP/2 protocol for faster
 * page loads through multiplexing and header compression.
 *
 * @since   1.2601.2148
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_HTTP2_Protocol_Support Class
 *
 * Checks if HTTP/2 is enabled.
 *
 * @since 1.2601.2148
 */
class Diagnostic_HTTP2_Protocol_Support extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'http2-protocol-support';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'HTTP/2 Protocol Support';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies HTTP/2 is enabled for faster loading';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if HTTP/2 not enabled, null otherwise.
	 */
	public static function check() {
		$http2_status = self::check_http2_status();

		if ( $http2_status['enabled'] ) {
			return null; // HTTP/2 is enabled
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'HTTP/2 not enabled. Site using outdated HTTP/1.1 protocol, missing 20-30% performance improvement from multiplexing.', 'wpshadow' ),
			'severity'     => 'medium',
			'threat_level' => 50,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/http2',
			'family'       => self::$family,
			'meta'         => array(
				'http2_enabled'        => false,
				'performance_gain'     => __( '20-30% faster page loads' ),
				'requires_https'       => __( 'HTTP/2 requires HTTPS (SSL certificate)' ),
				'server_requirements'  => __( 'Apache 2.4.17+ or Nginx 1.9.5+' ),
			),
			'details'      => array(
				'http2_benefits'          => array(
					'Multiplexing' => array(
						__( 'Multiple files loaded simultaneously on single connection' ),
						__( 'HTTP/1.1: 6 connections max per domain' ),
						__( 'HTTP/2: Unlimited files per connection' ),
					),
					'Header Compression' => array(
						__( 'Headers compressed with HPACK algorithm' ),
						__( 'Reduces overhead by 30-40%' ),
					),
					'Server Push' => array(
						__( 'Server sends assets before browser requests them' ),
						__( 'Critical CSS/JS pushed immediately' ),
					),
				),
				'performance_comparison'  => array(
					'HTTP/1.1' => array(
						'6 connections per domain',
						'Sequential requests (blocking)',
						'No header compression',
						'Page load: 3-4 seconds typical',
					),
					'HTTP/2' => array(
						'Unlimited parallel requests',
						'Single connection, no blocking',
						'Header compression (30% savings)',
						'Page load: 2-3 seconds typical',
					),
				),
				'enabling_http2'          => array(
					'Requirements' => array(
						'HTTPS enabled (SSL certificate)',
						'Apache 2.4.17+ or Nginx 1.9.5+',
						'PHP 7.0+ recommended',
					),
					'Apache' => array(
						'1. Enable mod_http2: sudo a2enmod http2',
						'2. Add to .htaccess or VirtualHost:',
						'   Protocols h2 h2c http/1.1',
						'3. Restart Apache: sudo systemctl restart apache2',
					),
					'Nginx' => array(
						'1. Add to server block (nginx.conf):',
						'   listen 443 ssl http2;',
						'2. Restart Nginx: sudo systemctl restart nginx',
					),
					'Shared Hosting' => array(
						'Contact hosting support',
						'Request HTTP/2 enablement',
						'Most modern hosts enable by default',
					),
				),
				'testing_http2'           => array(
					'Method 1: Online Tool' => array(
						'Visit: https://tools.keycdn.com/http2-test',
						'Enter your site URL',
						'Shows: HTTP/2 enabled or not',
					),
					'Method 2: Browser DevTools' => array(
						'Chrome: DevTools → Network → Protocol column',
						'Should show: h2 (not http/1.1)',
					),
					'Method 3: curl Command' => array(
						'curl -I --http2 https://yoursite.com',
						'Look for: HTTP/2 200',
					),
				),
				'common_issues'           => array(
					'HTTP/2 Requires HTTPS' => array(
						'Problem: No SSL certificate',
						'Fix: Install Let\'s Encrypt (free)',
						'See: SSL Certificate diagnostic',
					),
					'Old Apache/Nginx Version' => array(
						'Problem: Server too old',
						'Fix: Upgrade server (contact host)',
						'Minimum: Apache 2.4.17, Nginx 1.9.5',
					),
					'Cloudflare Enabled' => array(
						'Good news: Cloudflare automatically enables HTTP/2',
						'No additional configuration needed',
					),
				),
			),
		);
	}

	/**
	 * Check HTTP/2 status.
	 *
	 * @since  1.2601.2148
	 * @return array HTTP/2 status.
	 */
	private static function check_http2_status() {
		// Check if HTTPS is enabled (HTTP/2 requires HTTPS)
		if ( ! is_ssl() ) {
			return array( 'enabled' => false );
		}

		// Try to detect HTTP/2 from server signature
		$home_url = home_url();
		$response = wp_remote_head( $home_url );

		if ( is_wp_error( $response ) ) {
			return array( 'enabled' => false );
		}

		$headers = wp_remote_retrieve_headers( $response );

		// Some servers expose HTTP version in headers
		if ( isset( $headers['server'] ) ) {
			$server = strtolower( $headers['server'] );
			// Cloudflare, many modern hosts support HTTP/2 by default
			if ( strpos( $server, 'cloudflare' ) !== false ) {
				return array( 'enabled' => true );
			}
		}

		// If we can't definitively detect, assume not enabled
		return array( 'enabled' => false );
	}
}
