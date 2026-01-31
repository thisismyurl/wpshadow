<?php
/**
 * Performance Budget Not Defined Diagnostic
 *
 * Checks if performance budget is defined.
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
 * Performance Budget Not Defined Diagnostic Class
 *
 * Detects missing performance budget.
 *
 * @since 1.2601.2352
 */
class Diagnostic_Performance_Budget_Not_Defined extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'performance-budget-not-defined';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Performance Budget Not Defined';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if performance budget is defined';

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
		// Check for performance budget definition
		if ( ! get_option( 'performance_budget_defined' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Performance budget is not defined. Set targets for page load time (<3s), FCP (<1.8s), LCP (<2.5s), and CLS (<0.1) to maintain consistent performance.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 40,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/performance-budget-not-defined',
			);
		}

		return null;
	}
}
