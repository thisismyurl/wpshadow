<?php
/**
 * Browser Caching Headers Diagnostic
 *
 * Verifies proper Cache-Control and Expires headers are set to
 * enable browser caching and reduce repeat page load times.
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
 * Diagnostic_Browser_Caching_Headers Class
 *
 * Checks if browser caching headers are properly configured.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Browser_Caching_Headers extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'browser-caching-headers';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Browser Caching Headers';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies browser caching headers are set';

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
	 * @return array|null Finding array if caching headers missing, null otherwise.
	 */
	public static function check() {
		$cache_status = self::check_caching_headers();

		if ( $cache_status['configured'] ) {
			return null; // Browser caching is configured
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'Browser caching headers not set. Repeat visitors download all assets again, wasting bandwidth and slowing page loads by 50-70%.', 'wpshadow' ),
			'severity'     => 'high',
			'threat_level' => 70,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/browser-caching',
			'family'       => self::$family,
			'meta'         => array(
				'caching_configured'   => false,
				'repeat_visit_impact'  => __( 'Repeat visitors 50-70% slower' ),
				'bandwidth_waste'      => __( 'Re-downloading unchanged assets' ),
				'setup_time'           => __( '5-10 minutes' ),
			),
			'details'      => array(
				'how_browser_caching_works' => array(
					__( 'Server tells browser: "Keep this file for X days"' ),
					__( 'Browser stores images, CSS, JS locally' ),
					__( 'Next visit: Browser uses cached copy instead of downloading' ),
					__( 'Repeat visits 50-70% faster' ),
				),
				'caching_headers'         => array(
					'Cache-Control' => array(
						'max-age=31536000: Cache for 1 year',
						'public: Allow CDN/proxy caching',
						'immutable: File never changes',
						'Example: Cache-Control: max-age=31536000, public',
					),
					'Expires' => array(
						'Legacy header for old browsers',
						'Example: Expires: Thu, 31 Dec 2026 23:59:59 GMT',
					),
				),
				'recommended_durations'   => array(
					'Images (JPG, PNG, WebP)' => '1 year (31536000 seconds)',
					'CSS files' => '1 year (versioned) or 1 week (unversioned)',
					'JavaScript files' => '1 year (versioned) or 1 week (unversioned)',
					'Fonts (WOFF, WOFF2)' => '1 year (31536000 seconds)',
					'HTML pages' => 'No cache or short (300 seconds)',
				),
				'enabling_browser_caching' => array(
					'Apache (.htaccess)' => array(
						'Add to .htaccess:',
						'<IfModule mod_expires.c>',
						'  ExpiresActive On',
						'  ExpiresByType image/jpg "access plus 1 year"',
						'  ExpiresByType image/png "access plus 1 year"',
						'  ExpiresByType text/css "access plus 1 week"',
						'  ExpiresByType application/javascript "access plus 1 week"',
						'</IfModule>',
					),
					'Nginx (nginx.conf)' => array(
						'Add to server block:',
						'location ~* \\.(jpg|jpeg|png|gif|ico|css|js)$ {',
						'  expires 1y;',
						'  add_header Cache-Control "public, immutable";',
						'}',
					),
					'WordPress Plugin' => array(
						'WP Rocket: Automatic caching headers',
						'W3 Total Cache: Browser Cache → Enable',
						'WP Super Cache: Cache headers enabled by default',
					),
				),
				'testing_cache_headers'   => array(
					'Method 1: Browser DevTools' => array(
						'Open DevTools → Network',
						'Load page',
						'Click any image/CSS file',
						'Headers tab → Look for: Cache-Control, Expires',
					),
					'Method 2: Online Tool' => array(
						'Visit: GTmetrix.com',
						'Enter URL → PageSpeed tab',
						'Check: "Leverage browser caching" section',
					),
					'Method 3: curl Command' => array(
						'curl -I https://yoursite.com/image.jpg',
						'Look for: cache-control: max-age=31536000',
					),
				),
				'cache_busting'           => array(
					'Why Version Assets?' => array(
						__( 'Problem: Browser caches old CSS with 1-year expiry' ),
						__( 'Solution: Change filename when file changes' ),
						__( 'Example: style.css?ver=1.2.3 or style.v2.css' ),
					),
					'WordPress Automatic' => array(
						__( 'WordPress adds ?ver= automatically' ),
						__( 'Enqueue scripts with version parameter' ),
						__( 'wp_enqueue_style( "style", "style.css", array(), "1.2" );' ),
					),
				),
			),
		);
	}

	/**
	 * Check caching headers.
	 *
	 * @since  1.2601.2148
	 * @return array Caching header status.
	 */
	private static function check_caching_headers() {
		// Check if caching plugin is active
		$cache_plugins = array(
			'wp-rocket/wp-rocket.php',
			'w3-total-cache/w3-total-cache.php',
			'wp-super-cache/wp-cache.php',
			'wp-fastest-cache/wpFastestCache.php',
		);

		foreach ( $cache_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return array( 'configured' => true );
			}
		}

		// Test actual headers on an asset
		$upload_dir = wp_upload_dir();
		$test_url   = $upload_dir['baseurl'];

		if ( $test_url ) {
			$response = wp_remote_head( $test_url );
			if ( ! is_wp_error( $response ) ) {
				$headers = wp_remote_retrieve_headers( $response );
				if ( isset( $headers['cache-control'] ) || isset( $headers['expires'] ) ) {
					return array( 'configured' => true );
				}
			}
		}

		return array( 'configured' => false );
	}
}
