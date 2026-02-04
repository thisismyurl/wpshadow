<?php
/**
 * No Cohort or Retention Analysis Diagnostic
 *
 * Detects when cohort analysis is not performed,
 * missing insights about customer retention trends.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Marketing
 * @since      1.6035.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic: No Cohort or Retention Analysis
 *
 * Checks whether cohort retention analysis is being
 * performed to track customer loyalty over time.
 *
 * @since 1.6035.2148
 */
class Diagnostic_No_Cohort_Or_Retention_Analysis extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-cohort-retention-analysis';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Cohort & Retention Analysis';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether cohort retention analysis is performed';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'marketing';

	/**
	 * Whether this diagnostic is auto-fixable
	 *
	 * @var bool
	 */
	protected static $auto_fixable = false;

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6035.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for analytics/cohort plugins
		$has_cohort_analysis = get_option( 'wpshadow_cohort_analysis' );

		if ( ! $has_cohort_analysis ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __(
					'You\'re not analyzing cohorts or retention, which means you don\'t know if customers stay or leave. Cohort analysis groups customers by signup date, then tracks: how many came back day 2? week 2? month 2? This reveals: if product gets better (retention improving), if marketing attracts right people (retention stable), if changes help or hurt (retention trending up or down). Most successful companies obsess over retention because it\'s more profitable than acquisition.',
					'wpshadow'
				),
				'severity'      => 'high',
				'threat_level'  => 65,
				'auto_fixable'  => false,
				'business_impact' => array(
					'metric'         => 'Customer Loyalty & Growth',
					'potential_gain' => 'Identify retention trends early',
					'roi_explanation' => 'Cohort analysis reveals retention patterns, enabling early detection of product/market issues before they become critical.',
				),
				'kb_link'       => 'https://wpshadow.com/kb/cohort-retention-analysis',
			);
		}

		return null;
	}
}
