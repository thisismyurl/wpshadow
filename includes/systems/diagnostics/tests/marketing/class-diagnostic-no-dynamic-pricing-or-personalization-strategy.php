<?php
/**
 * Dynamic Pricing and Personalization Strategy Diagnostic
 *
 * Detects when dynamic pricing or personalization strategies
 * are not implemented to optimize revenue per visitor.
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
 * Diagnostic: No Dynamic Pricing or Personalization Strategy
 *
 * Checks whether the site uses dynamic pricing or personalization
 * to optimize revenue based on visitor behavior and context.
 *
 * @since 1.6035.2148
 */
class Diagnostic_No_Dynamic_Pricing_Or_Personalization_Strategy extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-dynamic-pricing-personalization-strategy';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Dynamic Pricing & Personalization';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether dynamic pricing or personalization is implemented';

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
		// Check for dynamic pricing plugins
		$has_dynamic_pricing = is_plugin_active( 'woocommerce-dynamic-pricing/woocommerce-dynamic-pricing.php' ) ||
			is_plugin_active( 'flexible-product-fields/flexible-product-fields.php' );

		// Check for personalization plugins
		$has_personalization = is_plugin_active( 'pathwright/pathwright.php' ) ||
			is_plugin_active( 'unbounce/unbounce.php' ) ||
			is_plugin_active( 'personalize-by-segment/personalize-by-segment.php' );

		// Check for custom personalization
		$has_custom_personalization = get_option( 'wpshadow_personalization_enabled' );

		if ( ! $has_dynamic_pricing && ! $has_personalization && ! $has_custom_personalization ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __(
					'You\'re charging everyone the same price, which often leaves money on the table. Dynamic pricing adjusts based on context: loyalty customers get discounts, bulk orders get better rates, high-willingness-to-pay customers see premium options. Personalization shows different offers to different people based on their history and behavior. Together, these can increase revenue per visitor by 10-20% without changing your product.',
					'wpshadow'
				),
				'severity'      => 'medium',
				'threat_level'  => 50,
				'auto_fixable'  => false,
				'business_impact' => array(
					'metric'         => 'Revenue Per Visitor',
					'potential_gain' => '+10-20% revenue per visitor',
					'roi_explanation' => 'Dynamic pricing and personalization optimize revenue by showing the right offer to the right person at the right time, maximizing conversions and order value.',
				),
				'kb_link'       => 'https://wpshadow.com/kb/dynamic-pricing-personalization',
			);
		}

		return null;
	}
}
