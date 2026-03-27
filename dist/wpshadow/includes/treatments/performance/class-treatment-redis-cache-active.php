<?php
/**
 * Redis/Cache Active Treatment
 *
 * Checks whether an external object cache is active.
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
 * Treatment_Redis_Cache_Active Class
 *
 * Verifies that object caching is enabled.
 *
 * @since 1.6093.1200
 */
class Treatment_Redis_Cache_Active extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'redis-cache-active';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Redis/Cache Active';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether object caching is active';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Redis_Cache_Active' );
	}
}