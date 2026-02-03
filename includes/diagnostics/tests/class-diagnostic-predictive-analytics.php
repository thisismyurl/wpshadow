<?php
/**
 * Predictive Analytics Diagnostic
 *
 * Tests whether the site uses predictive analytics and machine learning for forecasting.
 *
 * @since   1.26034.0200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Predictive Analytics Diagnostic Class
 *
 * Predictive analytics uses historical data and machine learning to forecast trends,
 * customer behavior, inventory needs, and revenue opportunities.
 *
 * @since 1.26034.0200
 */
class Diagnostic_Predictive_Analytics extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'predictive-analytics';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Predictive Analytics Implementation';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether the site uses predictive analytics and machine learning for forecasting';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'analytics';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26034.0200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$analytics_score = 0;
		$max_score = 7;

		// Check for analytics platforms with ML capabilities.
		$advanced_analytics = self::check_advanced_analytics_platforms();
		if ( $advanced_analytics ) {
			$analytics_score++;
		} else {
			$issues[] = __( 'No advanced analytics platform with ML capabilities detected', 'wpshadow' );
		}

		// Check for predictive analytics plugins.
		$predictive_plugins = array(
			'predictive-analytics/predictive-analytics.php' => 'Predictive Analytics',
			'woocommerce-predictive-analytics/woocommerce-predictive-analytics.php' => 'WooCommerce Predictive',
		);

		$has_predictive_plugin = false;
		foreach ( $predictive_plugins as $plugin_path => $plugin_name ) {
			if ( is_plugin_active( $plugin_path ) ) {
				$has_predictive_plugin = true;
				$analytics_score++;
				break;
			}
		}

		if ( ! $has_predictive_plugin ) {
			$issues[] = __( 'No predictive analytics plugin installed', 'wpshadow' );
		}

		// Check for customer behavior prediction.
		$behavior_prediction = self::check_behavior_prediction();
		if ( $behavior_prediction ) {
			$analytics_score++;
		} else {
			$issues[] = __( 'No customer behavior prediction or churn modeling', 'wpshadow' );
		}

		// Check for revenue forecasting.
		$revenue_forecasting = self::check_revenue_forecasting();
		if ( $revenue_forecasting ) {
			$analytics_score++;
		} else {
			$issues[] = __( 'No revenue forecasting or sales prediction', 'wpshadow' );
		}

		// Check for inventory prediction (e-commerce).
		if ( class_exists( 'WooCommerce' ) ) {
			$inventory_prediction = self::check_inventory_prediction();
			if ( $inventory_prediction ) {
				$analytics_score++;
			} else {
				$issues[] = __( 'No inventory demand forecasting', 'wpshadow' );
			}
		} else {
			$analytics_score++; // Not applicable, give credit.
		}

		// Check for A/B testing with statistical significance.
		$ab_testing = self::check_ab_testing();
		if ( $ab_testing ) {
			$analytics_score++;
		} else {
			$issues[] = __( 'No A/B testing platform with statistical analysis', 'wpshadow' );
		}

		// Check for data science tools integration.
		$data_science_tools = self::check_data_science_integration();
		if ( $data_science_tools ) {
			$analytics_score++;
		} else {
			$issues[] = __( 'No integration with data science tools (Python, R, TensorFlow)', 'wpshadow' );
		}

		// Determine severity based on predictive analytics implementation.
		$analytics_percentage = ( $analytics_score / $max_score ) * 100;

		if ( $analytics_percentage < 30 ) {
			// Minimal or no predictive analytics.
			$severity = 'low';
			$threat_level = 30;
		} elseif ( $analytics_percentage < 60 ) {
			// Basic predictive analytics.
			$severity = 'low';
			$threat_level = 20;
		} else {
			// Good predictive analytics implementation - no issue.
			return null;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %d: Predictive analytics implementation percentage */
				__( 'Predictive analytics at %d%%. ', 'wpshadow' ),
				(int) $analytics_percentage
			) . implode( '. ', $issues ) . ' ' . __( 'Predictive analytics helps optimize inventory, reduce churn, and forecast revenue', 'wpshadow' );

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => $description,
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/predictive-analytics',
			);
		}

		return null;
	}

	/**
	 * Check for advanced analytics platforms.
	 *
	 * @since  1.26034.0200
	 * @return bool True if advanced analytics exists, false otherwise.
	 */
	private static function check_advanced_analytics_platforms() {
		// Check for Google Analytics 4 with ML insights.
		$ga4_plugins = array(
			'google-analytics-for-wordpress/googleanalytics.php',
			'google-site-kit/google-site-kit.php',
		);

		foreach ( $ga4_plugins as $plugin_path ) {
			if ( is_plugin_active( $plugin_path ) ) {
				return true;
			}
		}

		// Check for other advanced analytics platforms.
		$advanced_platforms = array(
			'matomo/matomo.php',
			'independent-analytics/independent-analytics.php',
		);

		foreach ( $advanced_platforms as $plugin_path ) {
			if ( is_plugin_active( $plugin_path ) ) {
				return true;
			}
		}

		return apply_filters( 'wpshadow_has_advanced_analytics', false );
	}

	/**
	 * Check for behavior prediction capabilities.
	 *
	 * @since  1.26034.0200
	 * @return bool True if behavior prediction exists, false otherwise.
	 */
	private static function check_behavior_prediction() {
		// Check for customer journey or behavior analysis plugins.
		$behavior_plugins = array(
			'metorik/metorik.php',
			'customer-lifetime-value/customer-lifetime-value.php',
		);

		foreach ( $behavior_plugins as $plugin_path ) {
			if ( is_plugin_active( $plugin_path ) ) {
				return true;
			}
		}

		return apply_filters( 'wpshadow_has_behavior_prediction', false );
	}

	/**
	 * Check for revenue forecasting.
	 *
	 * @since  1.26034.0200
	 * @return bool True if revenue forecasting exists, false otherwise.
	 */
	private static function check_revenue_forecasting() {
		// Check for business intelligence or reporting plugins.
		if ( class_exists( 'WooCommerce' ) ) {
			// WooCommerce Advanced Reports or similar.
			$reporting_plugins = array(
				'woocommerce-admin/woocommerce-admin.php',
				'metorik/metorik.php',
			);

			foreach ( $reporting_plugins as $plugin_path ) {
				if ( is_plugin_active( $plugin_path ) ) {
					return true;
				}
			}
		}

		return apply_filters( 'wpshadow_has_revenue_forecasting', false );
	}

	/**
	 * Check for inventory prediction.
	 *
	 * @since  1.26034.0200
	 * @return bool True if inventory prediction exists, false otherwise.
	 */
	private static function check_inventory_prediction() {
		// Check for inventory management with predictive features.
		$inventory_plugins = array(
			'woocommerce-stock-manager/woocommerce-stock-manager.php',
			'atum-stock-manager-for-woocommerce/atum-stock-manager-for-woocommerce.php',
		);

		foreach ( $inventory_plugins as $plugin_path ) {
			if ( is_plugin_active( $plugin_path ) ) {
				return true;
			}
		}

		return apply_filters( 'wpshadow_has_inventory_prediction', false );
	}

	/**
	 * Check for A/B testing platforms.
	 *
	 * @since  1.26034.0200
	 * @return bool True if A/B testing exists, false otherwise.
	 */
	private static function check_ab_testing() {
		// Check for A/B testing plugins.
		$ab_testing_plugins = array(
			'nelio-ab-testing/nelio-ab-testing.php',
			'simple-ab-testing/simple-ab-testing.php',
			'google-optimize/google-optimize.php',
		);

		foreach ( $ab_testing_plugins as $plugin_path ) {
			if ( is_plugin_active( $plugin_path ) ) {
				return true;
			}
		}

		return apply_filters( 'wpshadow_has_ab_testing', false );
	}

	/**
	 * Check for data science tools integration.
	 *
	 * @since  1.26034.0200
	 * @return bool True if data science integration exists, false otherwise.
	 */
	private static function check_data_science_integration() {
		// Check for REST API usage that might indicate external analytics.
		$routes = rest_get_server()->get_routes();
		$has_analytics_endpoint = false;

		foreach ( $routes as $route => $handlers ) {
			if ( strpos( $route, '/analytics' ) !== false || 
				 strpos( $route, '/ml' ) !== false ||
				 strpos( $route, '/predict' ) !== false ) {
				$has_analytics_endpoint = true;
				break;
			}
		}

		return apply_filters( 'wpshadow_has_data_science_integration', $has_analytics_endpoint );
	}
}
