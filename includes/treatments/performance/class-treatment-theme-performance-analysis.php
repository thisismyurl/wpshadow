<?php
/**
 * Theme Performance Analysis Treatment
 *
 * Evaluates theme performance and checks for optimization opportunities
 * including asset loading, bloat, and modern best practices.
 *
 * @since   1.6033.2084
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme Performance Analysis Treatment Class
 *
 * Analyzes theme performance:
 * - Theme asset count and size
 * - Render-blocking resources
 * - Block theme vs classic theme
 * - Theme optimization
 *
 * @since 1.6033.2084
 */
class Treatment_Theme_Performance_Analysis extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'theme-performance-analysis';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Theme Performance Analysis';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Evaluates theme performance and optimization opportunities';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6033.2084
	 * @return array|null Finding array if issues found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Theme_Performance_Analysis' );
	}
}
