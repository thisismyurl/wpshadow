<?php
/**
 * Time to Interactive (TTI) Treatment
 *
 * Measures Time to Interactive for Core Web Vitals.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6033.2054
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Time to Interactive Treatment Class
 *
 * Measures factors affecting TTI. TTI is when the page becomes
 * fully interactive and responsive to user input.
 *
 * @since 1.6033.2054
 */
class Treatment_Time_To_Interactive extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'time-to-interactive';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Time to Interactive (TTI)';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Measures Time to Interactive (Core Web Vital)';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * Checks factors affecting TTI:
	 * - JavaScript execution time
	 * - Main thread work
	 * - Network requests
	 *
	 * Thresholds:
	 * - Good: <3.8s
	 * - Needs Improvement: 3.8-7.3s
	 * - Poor: >7.3s
	 *
	 * @since  1.6033.2054
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Time_To_Interactive' );
	}
}
