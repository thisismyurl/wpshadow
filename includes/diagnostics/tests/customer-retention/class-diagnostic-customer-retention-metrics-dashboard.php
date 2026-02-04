<?php
/**
 * Customer Retention Metrics Dashboard Diagnostic
 *
 * Detects when businesses aren't tracking customer retention metrics.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\CustomerRetention
 * @since      1.6035.2315
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics\CustomerRetention;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Customer Retention Metrics Dashboard Diagnostic Class
 *
 * Checks if the site tracks important customer retention KPIs.
 *
 * @since 1.6035.2315
 */
class Diagnostic_Customer_Retention_Metrics_Dashboard extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'customer-retention-metrics-dashboard';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'No Customer Retention Metrics Tracked';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects when sites don\'t track customer retention KPIs';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'customer-retention';

	/**
	 * Run the diagnostic check
	 *
	 * @since  1.6035.2315
	 * @return array|null Finding array or null if no issues found.
	 */
	public static function check() {
		// Check for analytics/CRM plugins that track retention.
		$retention_plugins = array(
			'metorik-helper/metorik-helper.php'      => 'Metorik (WooCommerce Analytics)',
			'google-analytics-for-wordpress/googleanalytics.php' => 'MonsterInsights (GA4)',
			'hubspot/hubspot.php'                    => 'HubSpot CRM',
			'mailchimp-for-woocommerce/mailchimp-woocommerce.php' => 'Mailchimp (Customer Data)',
			'klaviyo/klaviyo.php'                    => 'Klaviyo (Customer Analytics)',
		);

		$tracking_tools = array();
		foreach ( $retention_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$tracking_tools[] = $name;
			}
		}

		// If multiple tracking tools active, assume retention is tracked.
		if ( count( $tracking_tools ) >= 2 ) {
			return null;
		}

		// Check if business type benefits from retention tracking.
		$business_plugins = array(
			'woocommerce/woocommerce.php'            => 'E-commerce',
			'memberpress/memberpress.php'            => 'Membership',
			'lifterlms/lifterlms.php'                => 'Online Courses',
			'easy-digital-downloads/easy-digital-downloads.php' => 'Digital Products',
		);

		$business_type = array();
		foreach ( $business_plugins as $plugin => $type ) {
			if ( is_plugin_active( $plugin ) ) {
				$business_type[] = $type;
			}
		}

		// If no business plugins, less critical.
		if ( empty( $business_type ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'You can\'t improve what you don\'t measure. Without tracking customer retention metrics, you\'re flying blind on your most important business driver', 'wpshadow' ),
			'severity'     => 'high',
			'threat_level' => 65,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/retention-metrics',
			'context'      => array(
				'business_type'      => $business_type,
				'tracking_tools'     => $tracking_tools,
				'has_dashboard'      => count( $tracking_tools ) >= 1,
				'impact'             => __( 'A 5% increase in retention can increase profits by 25-95%. But 70% of businesses don\'t track customer retention at all.', 'wpshadow' ),
				'key_metrics'        => array(
					__( 'Customer Retention Rate (CRR): % of customers who return', 'wpshadow' ),
					__( 'Repeat Purchase Rate: % of customers who buy again', 'wpshadow' ),
					__( 'Customer Lifetime Value (CLV): Total revenue per customer', 'wpshadow' ),
					__( 'Churn Rate: % of customers who stop buying', 'wpshadow' ),
					__( 'Time Between Purchases: How often customers return', 'wpshadow' ),
					__( 'Net Promoter Score (NPS): Willingness to recommend', 'wpshadow' ),
				),
				'recommendation'     => array(
					__( 'Set up a customer retention dashboard', 'wpshadow' ),
					__( 'Track repeat purchase rate monthly', 'wpshadow' ),
					__( 'Calculate customer lifetime value (CLV)', 'wpshadow' ),
					__( 'Monitor churn rate and identify causes', 'wpshadow' ),
					__( 'Segment customers by retention risk', 'wpshadow' ),
					__( 'Set retention goals and track progress', 'wpshadow' ),
					__( 'Use cohort analysis to see trends', 'wpshadow' ),
				),
				'business_value'     => __( 'Increasing retention by 5% boosts profits 25-95%', 'wpshadow' ),
				'cost_benefit'       => __( 'Retaining customers costs 5x less than acquiring new ones', 'wpshadow' ),
			),
		);
	}
}
