<?php
/**
 * Customer Lifetime Value (CLV) Analysis Diagnostic
 *
 * Detects when customer lifetime value is not being calculated or analyzed
 * to understand long-term customer worth and profitability.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Marketing
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic: No Customer Lifetime Value Analysis
 *
 * Checks whether the site tracks and analyzes customer lifetime value
 * for understanding profitability and retention ROI.
 *
 * @since 0.6093.1200
 */
class Diagnostic_No_Customer_Lifetime_Value_CLV_Analysis extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-customer-lifetime-value-analysis';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Customer Lifetime Value (CLV) Analysis';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether customer lifetime value is being tracked and analyzed';

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
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for CLV tracking
		$has_clv_plugin = false;
		$has_clv_custom = get_option( 'wpshadow_clv_tracking' );

		// Check for CRM or analytics plugins that track CLV
		if ( is_plugin_active( 'woocommerce-crm/woocommerce-crm.php' ) ||
			is_plugin_active( 'fluentcrm/fluent-crm.php' ) ||
			is_plugin_active( 'herbivore/herbivore.php' ) ) {
			$has_clv_plugin = true;
		}

		// Check for WooCommerce Customer Insights extension
		if ( function_exists( 'wc_get_customer' ) ) {
			$has_woocommerce = true;
		} else {
			$has_woocommerce = false;
		}

		if ( ! $has_clv_plugin && ! $has_clv_custom && ! $has_woocommerce ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __(
					'You\'re not analyzing customer lifetime value yet. This is like not knowing which customers are most valuable to your business. CLV analysis helps you understand that a customer who spends $50 today might be worth $500 over time—which changes how much you should invest in keeping them happy. This directly impacts your profitability.',
					'wpshadow'
				),
				'severity'      => 'high',
				'threat_level'  => 70,
				'auto_fixable'  => false,
				'business_impact' => array(
					'metric'         => 'Customer Profitability',
					'potential_gain' => 'Identify high-value customers',
					'roi_explanation' => 'CLV analysis reveals that a 5% retention improvement of high-value customers is worth 25-95% more profit than acquiring new ones.',
				),
				'kb_link'       => 'https://wpshadow.com/kb/customer-lifetime-value-analysis?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
