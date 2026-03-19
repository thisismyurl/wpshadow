<?php
/**
 * Object Cache Configuration Treatment
 *
 * Checks if persistent object caching is configured properly.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Object Cache Configuration Treatment Class
 *
 * Verifies persistent object cache (Redis/Memcached) is properly configured.
 * Persistent object caching dramatically reduces database load.
 *
 * @since 1.6093.1200
 */
class Treatment_Object_Cache_Configuration extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'object-cache-configuration';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Object Cache Configuration';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks persistent object cache configuration';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * Checks for persistent object cache and proper configuration.
	 * Without persistent cache, all cached data is lost between requests.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Object_Cache_Configuration' );
	}
}
