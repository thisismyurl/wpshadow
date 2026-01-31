<?php
/**
 * Browser Caching Headers Not Optimized Diagnostic
 *
 * Checks if browser caching headers are configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2325
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Browser Caching Headers Not Optimized Diagnostic Class
 *
 * Detects missing browser cache headers.
 *
 * @since 1.2601.2325
 */
class Diagnostic_Browser_Caching_Headers_Not_Optimized extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'browser-caching-headers-not-optimized';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Browser Caching Headers Not Optimized';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if browser cache headers are set';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2325
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for cache control plugins
		$cache_plugins = array(
			'wp-super-cache/wp-cache.php',
			'w3-total-cache/w3-total-cache.php',
			'wp-fastest-cache/wpFastestCache.php',
		);

		$cache_active = false;
		foreach ( $cache_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$cache_active = true;
				break;
			}
		}

		if ( ! $cache_active ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Browser cache headers are not optimized. Browsers can cache static assets to reduce load times.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 40,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/browser-caching-headers-not-optimized',
			);
		}

		return null;
	}
}
