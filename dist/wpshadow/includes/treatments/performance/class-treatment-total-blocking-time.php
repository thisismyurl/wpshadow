<?php
/**
 * Total Blocking Time (TBT) Treatment
 *
 * Measures Total Blocking Time for Core Web Vitals.
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
 * Total Blocking Time Treatment Class
 *
 * Measures factors affecting TBT (Total Blocking Time).
 * TBT measures responsiveness during page load.
 *
 * @since 0.6093.1200
 */
class Treatment_Total_Blocking_Time extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'total-blocking-time';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Total Blocking Time (TBT)';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Measures Total Blocking Time (Core Web Vital)';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * Checks factors affecting TBT:
	 * - JavaScript execution time
	 * - Long tasks (>50ms)
	 * - Main thread blocking
	 *
	 * Thresholds:
	 * - Good: <200ms
	 * - Needs Improvement: 200-600ms
	 * - Poor: >600ms
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Total_Blocking_Time' );
	}
}
