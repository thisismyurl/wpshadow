<?php
/**
 * Performance Baseline Treatment
 *
 * Checks if performance metrics are being tracked and baselines established.
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
 * Performance Baseline Treatment Class
 *
 * Verifies that performance monitoring is in place with established
 * baselines for tracking improvements and regressions.
 *
 * @since 1.6093.1200
 */
class Treatment_Performance_Baseline extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'performance-baseline';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Performance Baseline Monitoring';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if performance metrics are being tracked and baselines established';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'developer';

	/**
	 * Run the performance baseline treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if baseline issues detected, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Performance\Diagnostic_Performance_Baseline' );
	}
}
