<?php
/**
 * No Conversion Rate Optimization (CRO) Testing Diagnostic
 *
 * Detects when A/B testing and conversion optimization
 * is not being performed systematically.
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
 * Diagnostic: No Conversion Rate Optimization Testing
 *
 * Checks whether A/B testing and CRO is being
 * performed to improve conversion rates.
 *
 * @since 1.6035.2148
 */
class Diagnostic_No_Conversion_Rate_Optimization_Testing extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-conversion-rate-optimization-testing';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Conversion Rate Optimization (CRO) Testing';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether CRO and A/B testing is being performed';

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
		// Check for CRO/testing plugins
		$has_cro = is_plugin_active( 'google-optimize/google-optimize.php' ) ||
			is_plugin_active( 'vwo/visual-website-optimizer.php' ) ||
			is_plugin_active( 'unbounce/unbounce.php' );

		if ( ! $has_cro ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __(
					'You\'re not doing conversion rate optimization testing, which means you\'re guessing at what works. Every tiny improvement compounds: if CTR improves 5%, conversion improves 2%, average order value improves 3%... you just increased revenue by 10%. A/B testing shows you what actually works vs what you think works. Test headlines, buttons, colors, copy, page layout. Small improvements lead to massive gains over time.',
					'wpshadow'
				),
				'severity'      => 'medium',
				'threat_level'  => 55,
				'auto_fixable'  => false,
				'business_impact' => array(
					'metric'         => 'Conversion Optimization',
					'potential_gain' => '+5-20% conversion rate improvement',
					'roi_explanation' => 'A/B testing reveals high-impact improvements. Compounding small wins (5% + 2% + 3%) leads to +10%+ revenue growth.',
				),
				'kb_link'       => 'https://wpshadow.com/kb/conversion-rate-optimization-testing',
			);
		}

		return null;
	}
}
