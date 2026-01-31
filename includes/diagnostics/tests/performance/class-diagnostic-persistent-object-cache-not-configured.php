<?php
/**
 * Persistent Object Cache Not Configured Diagnostic
 *
 * Checks if persistent caching is configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2352
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Persistent Object Cache Not Configured Diagnostic Class
 *
 * Detects missing persistent object cache.
 *
 * @since 1.2601.2352
 */
class Diagnostic_Persistent_Object_Cache_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'persistent-object-cache-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Persistent Object Cache Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if persistent caching is configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if persistent cache is configured
		if ( ! wp_using_ext_object_cache() ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Persistent object cache is not configured. Enable Redis or Memcached for significant performance improvements on high-traffic sites.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 20,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/persistent-object-cache-not-configured',
			);
		}

		return null;
	}
}
