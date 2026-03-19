<?php
/**
 * No Net Promoter Score (NPS) Tracking Diagnostic
 *
 * Detects when NPS is not being tracked,
 * missing a key metric for customer satisfaction and growth.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Marketing
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic: No Net Promoter Score (NPS) Tracking
 *
 * Checks whether NPS is being tracked to measure
 * customer satisfaction and loyalty.
 *
 * @since 1.6093.1200
 */
class Diagnostic_No_Net_Promoter_Score_NPS_Tracking extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-net-promoter-score-tracking';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Net Promoter Score (NPS) Tracking';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether NPS is being tracked';

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
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for NPS tracking
		$has_nps_tracking = is_plugin_active( 'delighted/delighted.php' ) ||
			is_plugin_active( 'promoter/promoter.php' ) ||
			get_option( 'wpshadow_nps_tracking' );

		if ( ! $has_nps_tracking ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __(
					'You\'re not tracking Net Promoter Score, which is like navigating without a compass. NPS is simple: "How likely are you to recommend us? (0-10)". Scores 9-10 are Promoters (advocates), 7-8 are Passives (satisfied but not vocal), 0-6 are Detractors (unhappy, likely to damage reputation). Your NPS = % Promoters - % Detractors. Tracking NPS identifies satisfaction trends, reveals what\'s working, and predicts churn. Companies improving NPS by 10 points see 50%+ growth acceleration.',
					'wpshadow'
				),
				'severity'      => 'medium',
				'threat_level'  => 50,
				'auto_fixable'  => false,
				'business_impact' => array(
					'metric'         => 'Customer Satisfaction & Growth',
					'potential_gain' => 'Predict churn and growth',
					'roi_explanation' => '10-point NPS improvement correlates to 50%+ growth acceleration and predicts customer churn before it happens.',
				),
				'kb_link'       => 'https://wpshadow.com/kb/net-promoter-score-tracking',
			);
		}

		return null;
	}
}
