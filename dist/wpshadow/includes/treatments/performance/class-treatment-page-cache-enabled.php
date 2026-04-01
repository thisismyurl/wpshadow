<?php
/**
 * Page Cache Enabled Treatment
 *
 * Checks if page caching is enabled and working properly.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Page Cache Enabled Treatment Class
 *
 * Verifies page caching is active. Page caching is the single most
 * impactful performance optimization (50-90% reduction).
 *
 * @since 0.6093.1200
 */
class Treatment_Page_Cache_Enabled extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'page-cache-enabled';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Page Cache Enabled';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if page caching is enabled and working';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * Checks for common cache plugins and cache headers.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Page_Cache_Enabled' );
	}
}
