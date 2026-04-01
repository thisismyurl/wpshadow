<?php
/**
 * No Multivariate Testing Strategy Diagnostic
 *
 * Detects when multivariate testing is not being used,
 * missing optimization beyond simple A/B tests.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Content
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic: No Multivariate Testing Strategy
 *
 * Checks whether multivariate testing is
 * being used for complex optimization.
 *
 * @since 0.6093.1200
 */
class Diagnostic_No_Multivariate_Testing_Strategy extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-multivariate-testing-strategy';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Multivariate Testing Strategy';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether MVT is implemented';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'content';

	/**
	 * Whether this diagnostic is auto-fixable
	 *
	 * @var bool
	 */
	protected static $auto_fixable = false;

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if MVT strategy is documented
		$has_mvt = get_option( 'wpshadow_multivariate_testing_strategy' );

		if ( ! $has_mvt ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __(
					'You\'re not using multivariate testing, which means missing complex optimization. A/B tests compare 2 versions. MVT tests multiple elements simultaneously (headline, image, CTA, layout). This reveals: element interactions, optimal combinations, compound effects. Example: testing 3 headlines + 2 images + 2 CTAs = 12 combinations tested at once. MVT requires more traffic than A/B testing but finds global optimum faster. Tools: Google Optimize, VWO, Optimizely.',
					'wpshadow'
				),
				'severity'      => 'low',
				'threat_level'  => 40,
				'auto_fixable'  => false,
				'business_impact' => array(
					'metric'         => 'Conversion Optimization Depth',
					'potential_gain' => 'Find optimal combinations faster than sequential A/B tests',
					'roi_explanation' => 'Multivariate testing reveals element interactions and compound effects, finding global optimum.',
				),
				'kb_link'       => 'https://wpshadow.com/kb/multivariate-testing-strategy?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
