<?php
/**
 * Hosting Performance Optimization Treatment
 *
 * Checks if hosting is optimized for performance.
 *
 * @package WPShadow\Treatments
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

/**
 * Treatment: Hosting Performance Optimization
 *
 * Detects hosting-level performance optimization opportunities.
 */
class Treatment_Hosting_Performance_Optimization extends Treatment_Base {

	/**
	 * Treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'hosting-performance-optimization';

	/**
	 * Treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Hosting Performance Optimization';

	/**
	 * Treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for hosting-level performance optimizations';

	/**
	 * Treatment family
	 *
	 * @var string
	 */
	protected static $family = 'hosting';

	/**
	 * Run the treatment check
	 *
	 * @return array|null Finding array if issues detected, null otherwise
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Hosting_Performance_Optimization' );
	}
}
