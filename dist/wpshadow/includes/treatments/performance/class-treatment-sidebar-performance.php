<?php
/**
 * Sidebar Performance Treatment
 *
 * Monitors sidebar implementation for performance issues including
 * unnecessary rendering and complex queries.
 *
 * @since 1.6093.1200
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sidebar Performance Treatment Class
 *
 * Verifies sidebar optimization:
 * - Sidebar rendering context
 * - Unused sidebars
 * - Sidebar on single posts vs archives
 * - Sidebar conditional loading
 *
 * @since 1.6093.1200
 */
class Treatment_Sidebar_Performance extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'sidebar-performance';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Sidebar Performance';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks sidebar implementation for performance optimization';

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
	 * @return array|null Finding array if issues found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Sidebar_Performance' );
	}
}
