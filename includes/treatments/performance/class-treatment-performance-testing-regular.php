<?php
/**
 * Performance Testing Regular Treatment
 *
 * Tests if performance is monitored regularly through various
 * performance testing tools and monitoring systems.
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
 * Performance Testing Regular Treatment Class
 *
 * Evaluates whether the site has regular performance monitoring
 * and testing practices in place.
 *
 * @since 0.6093.1200
 */
class Treatment_Performance_Testing_Regular extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'tests-performance-regularly';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Performance Testing Regular';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if performance is monitored regularly';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the performance testing regular treatment check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if performance testing issues detected, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Performance_Testing_Regular' );
	}
}
