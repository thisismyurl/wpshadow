<?php
/**
 * No Customer Retention Metrics Dashboard Diagnostic
 *
 * Checks if customer retention metrics are being tracked and displayed.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\BusinessPerformance
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Customer Retention Metrics Diagnostic
 *
 * Detects when customer retention metrics aren't being tracked. Retention is more
 * profitable than acquisition—keeping customers 5x cheaper than getting new ones.
 * Without tracking retention, you're flying blind on your most profitable metric.
 *
 * @since 1.6093.1200
 */
class Diagnostic_No_Customer_Retention_Metrics_Dashboard extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-customer-retention-metrics-dashboard';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Customer Retention Metrics Being Tracked';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if customer retention rates and churn analysis are tracked and visible';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'marketing';

	/**
	 * Run the diagnostic check
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$has_retention_tracking = self::check_retention_tracking();

		if ( ! $has_retention_tracking ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Customer retention metrics aren\'t being tracked. This is a critical blind spot. Keeping existing customers is 5x cheaper than acquiring new ones, yet most businesses focus only on acquisition. Without retention tracking, you can\'t see churn patterns, customer lifetime value, or repeat purchase rates. Start tracking: Monthly retention rate, customer churn rate, customer lifetime value, repeat purchase percentage.', 'wpshadow' ),
				'severity'    => 'high',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/customer-retention-metrics',
				'details'     => array(
					'tracking_enabled'        => false,
					'metrics_to_track'        => self::get_recommended_metrics(),
					'business_impact'         => __( '10% improvement in retention = 25-95% revenue increase (Harvard Business School)', 'wpshadow' ),
					'recommendation'          => __( 'Implement retention tracking and create a dashboard to monitor customer churn', 'wpshadow' ),
				),
			);
		}

		return null; // No issue found
	}

	/**
	 * Check if retention tracking is implemented
	 *
	 * @since 1.6093.1200
	 * @return bool True if tracking is in place
	 */
	private static function check_retention_tracking(): bool {
		// Check if WooCommerce or E-commerce solution exists
		if ( class_exists( 'WooCommerce' ) ) {
			// Check for retention plugins or custom tracking
			$plugins = get_plugins();

			// Look for retention/analytics plugins
			$retention_keywords = array( 'retention', 'churn', 'lifetime value', 'ltv', 'subscription' );

			foreach ( $plugins as $plugin_file => $plugin_data ) {
				$plugin_name = strtolower( $plugin_data['Name'] );
				foreach ( $retention_keywords as $keyword ) {
					if ( strpos( $plugin_name, $keyword ) !== false ) {
						return true;
					}
				}
			}

			return false;
		}

		// For non-commerce sites, check for analytics integration
		if ( self::has_analytics_tracking() ) {
			return true; // At least tracking visitors
		}

		return false;
	}

	/**
	 * Check if any analytics tracking is enabled
	 *
	 * @since 1.6093.1200
	 * @return bool True if analytics enabled
	 */
	private static function has_analytics_tracking(): bool {
		// Check for Google Analytics
		if ( function_exists( 'get_option' ) ) {
			$ga_id = get_option( 'google_analytics_id' );
			if ( ! empty( $ga_id ) ) {
				return true;
			}
		}

		// Check for Jetpack Analytics
		if ( class_exists( 'Jetpack' ) ) {
			return true;
		}

		// Check for MonsterInsights
		if ( function_exists( 'monsterinsights_get_option' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Get list of recommended retention metrics
	 *
	 * @since 1.6093.1200
	 * @return array Array of recommended metrics with descriptions
	 */
	private static function get_recommended_metrics(): array {
		return array(
			array(
				'metric'       => 'Monthly Retention Rate',
				'formula'      => '(Customers Month End - New Customers) / Customers Month Start',
				'target'       => '90%+',
				'description'  => 'Percentage of customers from last month still active',
			),
			array(
				'metric'       => 'Churn Rate',
				'formula'      => 'Customers Lost This Month / Customers Start of Month',
				'target'       => '<10%',
				'description'  => 'Percentage of customers who left (inverse of retention)',
			),
			array(
				'metric'       => 'Customer Lifetime Value (LTV)',
				'formula'      => 'Average Revenue Per Customer × Average Customer Lifespan',
				'target'       => 'Context-dependent',
				'description'  => 'Total revenue expected from a customer over their lifetime',
			),
			array(
				'metric'       => 'Repeat Purchase Rate',
				'formula'      => 'Customers with 2+ purchases / Total customers',
				'target'       => '40%+',
				'description'  => 'Percentage of customers making repeat purchases',
			),
			array(
				'metric'       => 'Net Retention Revenue',
				'formula'      => '(Revenue End - Revenue Lost) / Revenue Start',
				'target'       => '100%+',
				'description'  => 'Revenue growth from existing customers (including expansions and cancellations)',
			),
		);
	}
}
