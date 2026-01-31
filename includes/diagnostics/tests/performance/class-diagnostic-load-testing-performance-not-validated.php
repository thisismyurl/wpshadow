<?php
/**
 * Load Testing Performance Not Validated Diagnostic
 *
 * Checks if load testing is validated.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2352
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Load Testing Performance Not Validated Diagnostic Class
 *
 * Detects missing load testing.
 *
 * @since 1.2601.2352
 */
class Diagnostic_Load_Testing_Performance_Not_Validated extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'load-testing-performance-not-validated';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Load Testing Performance Not Validated';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if load testing is validated';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if load test results are stored
		if ( ! get_option( 'site_load_test_results' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Load testing performance is not validated. Run load tests using tools like Apache Bench or Gatling to identify bottlenecks.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 20,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/load-testing-performance-not-validated',
			);
		}

		return null;
	}
}
