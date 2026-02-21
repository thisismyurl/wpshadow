<?php
/**
 * Page Cache Configuration Treatment
 *
 * Tests if page caching is properly configured for frontend performance.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.7034.1100
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Page Cache Configuration Treatment Class
 *
 * Validates that page caching is enabled and properly configured
 * for optimal frontend performance.
 *
 * @since 1.7034.1100
 */
class Treatment_Page_Cache_Configuration extends Treatment_Base {

	/**
	 * Treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'page-cache-configuration';

	/**
	 * Treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Page Cache Configuration';

	/**
	 * Treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if page caching is properly configured for frontend performance';

	/**
	 * Treatment family
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * Tests if page caching is enabled via plugin or server-level
	 * configuration, and validates cache headers are set properly.
	 *
	 * @since  1.7034.1100
	 * @return array|null Finding array if issue detected, null if all clear.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Page_Cache_Configuration' );
	}
}
