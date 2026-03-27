<?php
/**
 * Performance Budget Set Treatment
 *
 * Tests if team has defined performance targets and
 * monitoring infrastructure for tracking against budgets.
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
 * Performance Budget Set Treatment Class
 *
 * Evaluates whether the site has defined performance budgets
 * and monitoring to track compliance.
 *
 * @since 1.6093.1200
 */
class Treatment_Performance_Budget_Set extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'sets-performance-budget';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Performance Budget Set';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if team has defined performance targets';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the performance budget set treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if performance budget issues detected, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Performance_Budget_Set' );
	}
}
