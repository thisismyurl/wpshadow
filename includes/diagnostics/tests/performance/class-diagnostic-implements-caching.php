<?php
/**
 * Caching Strategy Implemented Diagnostic
 *
 * Tests if caching layers are configured.
 *
 * @since 1.6093.1200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Caching Strategy Implemented Diagnostic Class
 *
 * Verifies that page/object caching is enabled.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Implements_Caching extends Diagnostic_Base {

	protected static $slug = 'implements-caching';
	protected static $title = 'Caching Strategy Implemented';
	protected static $description = 'Tests if caching layers are configured';
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$page_cache_plugins = array(
			'wp-super-cache/wp-cache.php',
			'w3-total-cache/w3-total-cache.php',
			'litespeed-cache/litespeed-cache.php',
			'wp-rocket/wp-rocket.php',
			'cache-enabler/cache-enabler.php',
		);

		foreach ( $page_cache_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return null;
			}
		}

		if ( wp_using_ext_object_cache() ) {
			return null;
		}

		$advanced_cache = WP_CONTENT_DIR . '/advanced-cache.php';
		if ( file_exists( $advanced_cache ) ) {
			return null;
		}

		$manual_flag = get_option( 'wpshadow_caching_strategy' );
		if ( $manual_flag ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'No caching strategy detected. Page and object caching can dramatically improve site speed.', 'wpshadow' ),
			'severity'     => 'high',
			'threat_level' => 60,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/caching-strategy-implemented',
			'persona'      => 'publisher',
		);
	}
}
