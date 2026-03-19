<?php
/**
 * Redis/Cache Active Diagnostic
 *
 * Checks whether an external object cache is active.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Redis_Cache_Active Class
 *
 * Verifies that object caching is enabled.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Redis_Cache_Active extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'redis-cache-active';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Redis/Cache Active';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether object caching is active';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$object_cache = wp_using_ext_object_cache();
		$dropin       = WP_CONTENT_DIR . '/object-cache.php';

		if ( ! $object_cache && ! file_exists( $dropin ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'No external object cache detected. Redis or Memcached can improve performance.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/redis-cache-active',
				'meta'         => array(
					'object_cache_enabled' => $object_cache,
					'dropin_exists'        => file_exists( $dropin ),
				),
			);
		}

		return null;
	}
}