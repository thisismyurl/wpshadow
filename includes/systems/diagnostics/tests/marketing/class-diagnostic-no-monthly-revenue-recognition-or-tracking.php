<?php
/**
 * Monthly Revenue Recognition and Tracking Diagnostic
 *
 * Detects when monthly revenue is not properly tracked or recognized
 * for accurate financial reporting and growth analysis.
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
 * Diagnostic: No Monthly Revenue Recognition or Tracking
 *
 * Checks whether the site has implemented systems to track and
 * recognize monthly revenue for financial reporting and analysis.
 *
 * @since 1.6035.2148
 */
class Diagnostic_No_Monthly_Revenue_Recognition_Or_Tracking extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-monthly-revenue-recognition-tracking';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Monthly Revenue Recognition & Tracking';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether monthly revenue is being tracked and recognized for financial reporting';

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
		// Check for WooCommerce or other revenue tracking plugins
		$has_woocommerce = class_exists( 'WooCommerce' );
		$has_revenue_tracking = false;

		// Check for revenue tracking plugins
		if ( is_plugin_active( 'woocommerce-bookings/woocommerce-bookings.php' ) ||
			is_plugin_active( 'woocommerce-subscriptions/woocommerce-subscriptions.php' ) ||
			is_plugin_active( 'memberpress/memberpress.php' ) ||
			is_plugin_active( 'restrict-content-pro/restrict-content-pro.php' ) ) {
			$has_revenue_tracking = true;
		}

		// Check for custom revenue tracking options
		$has_revenue_records = get_option( 'wpshadow_monthly_revenue_tracking' );
		$revenue_dashboard = get_option( 'wpshadow_revenue_dashboard' );

		if ( ! $has_woocommerce && ! $has_revenue_tracking && ! $has_revenue_records ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __(
					'Your site isn\'t tracking monthly revenue yet. Think of this like not looking at your bank statements—you\'re missing insights about your growth. Monthly revenue tracking helps you understand seasonal patterns, measure marketing ROI, and make data-driven decisions about where to invest your time and money.',
					'wpshadow'
				),
				'severity'      => 'high',
				'threat_level'  => 75,
				'auto_fixable'  => false,
				'business_impact' => array(
					'metric'         => 'Financial Clarity',
					'potential_gain' => 'Unknown revenue trends',
					'roi_explanation' => 'Monthly tracking reveals patterns that help you optimize pricing, predict cash flow, and identify which products/services are most profitable.',
				),
				'kb_link'       => 'https://wpshadow.com/kb/monthly-revenue-recognition',
			);
		}

		return null;
	}
}
