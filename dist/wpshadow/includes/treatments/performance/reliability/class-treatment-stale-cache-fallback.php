<?php
/**
 * Stale Cache Fallback Treatment
 *
 * Checks whether stale cache data is available when updates fail.
 *
 * @package    WPShadow
 * @subpackage Treatments\Reliability
 * @since 0.6093.1200
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
 * @since 0.6093.1200
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
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Stale_Cache_Fallback' );
	}
}
