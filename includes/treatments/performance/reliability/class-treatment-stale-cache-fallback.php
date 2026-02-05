<?php
/**
 * Stale Cache Fallback Treatment
 *
 * Checks whether stale cache data is available when updates fail.
 *
 * @package    WPShadow
 * @subpackage Treatments\Reliability
 * @since      1.6035.1400
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Stale Cache Fallback Treatment Class
 *
 * Verifies cache fallback strategies when fresh data is unavailable.
 *
 * @since 1.6035.1400
 */
class Treatment_Stale_Cache_Fallback extends Treatment_Base {

	/**
	 * The treatment slug.
	 *
	 * @var string
	 */
	protected static $slug = 'stale-cache-fallback';

	/**
	 * The treatment title.
	 *
	 * @var string
	 */
	protected static $title = 'Stale Cache Not Used When Fresh Data Unavailable';

	/**
	 * The treatment description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether stale cache data can be used as a fallback';

	/**
	 * The family this treatment belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'reliability';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6035.1400
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$stats  = array();

		$has_object_cache = function_exists( 'wp_using_ext_object_cache' ) && wp_using_ext_object_cache();

		$cache_plugins = array(
			'w3-total-cache/w3-total-cache.php' => 'W3 Total Cache',
			'wp-super-cache/wp-cache.php'       => 'WP Super Cache',
			'wp-rocket/wp-rocket.php'           => 'WP Rocket',
			'litespeed-cache/litespeed-cache.php' => 'LiteSpeed Cache',
			'cache-enabler/cache-enabler.php'   => 'Cache Enabler',
		);

		$active_cache = array();
		foreach ( $cache_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$active_cache[] = $plugin_name;
			}
		}

		$stats['object_cache'] = $has_object_cache ? 'enabled' : 'disabled';
		$stats['cache_plugins'] = ! empty( $active_cache ) ? implode( ', ', $active_cache ) : 'none';

		if ( ! $has_object_cache && empty( $active_cache ) ) {
			$issues[] = __( 'No cache system detected that can provide stale fallback data', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'When a data refresh fails, showing slightly older information is better than showing nothing. A cache fallback keeps your site usable during outages.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/stale-cache-fallback',
				'context'      => array(
					'stats'  => $stats,
					'issues' => $issues,
				),
			);
		}

		return null;
	}
}
