<?php
/**
 * Cache Invalidation Strategy Not Configured Diagnostic
 *
 * Checks if cache invalidation strategy is in place.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2310
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Cache Invalidation Strategy Not Configured Diagnostic Class
 *
 * Detects missing cache invalidation.
 *
 * @since 1.2601.2310
 */
class Diagnostic_Cache_Invalidation_Strategy_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'cache-invalidation-strategy-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Cache Invalidation Strategy Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if cache invalidation is configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2310
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for cache plugins
		$cache_plugins = array(
			'w3-total-cache/w3-total-cache.php',
			'wp-super-cache/wp-cache.php',
			'wp-fastest-cache/wp-fastest-cache.php',
		);

		$cache_active = false;
		foreach ( $cache_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$cache_active = true;
				break;
			}
		}

		if ( ! $cache_active ) {
			return null; // No caching, no invalidation needed
		}

		// Check if cache invalidation hooks are registered
		if ( ! has_action( 'publish_post' ) && ! has_action( 'edit_post' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Cache is active but invalidation strategy is not configured. Stale cached content may be served after edits.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 50,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/cache-invalidation-strategy-not-configured',
			);
		}

		return null;
	}
}
