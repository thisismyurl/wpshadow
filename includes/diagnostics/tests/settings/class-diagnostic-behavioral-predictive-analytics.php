<?php
/**
 * Diagnostic: Predictive Analytics
 *
 * Tests whether the site uses predictive analytics to forecast trends, demand,
 * and customer behavior for data-driven decisions.
 *
 * Issue: https://github.com/thisismyurl/wpshadow/issues/4554
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Behavioral
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Predictive Analytics Diagnostic
 *
 * Checks for predictive analytics tools. Forecasting future trends enables
 * proactive decisions - inventory planning, content strategy, churn prevention.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Behavioral_Predictive_Analytics extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'uses-predictive-analytics';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Predictive Analytics';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether site uses predictive analytics to forecast trends';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'behavioral';

	/**
	 * Check for predictive analytics implementation.
	 *
	 * Looks for advanced analytics and forecasting tools.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if missing, null if present.
	 */
	public static function check() {
		// Check for advanced analytics plugins.
		$analytics_plugins = array(
			'google-analytics-for-wordpress/googleanalytics.php' => 'MonsterInsights',
			'metorik-helper/metorik-helper.php'                   => 'Metorik',
		);

		$has_advanced_analytics = false;
		foreach ( $analytics_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$has_advanced_analytics = true;
				break;
			}
		}

		// Check for WooCommerce analytics.
		if ( class_exists( 'WooCommerce' ) && function_exists( 'wc_admin_url' ) ) {
			// WooCommerce Admin has predictive features.
			$has_advanced_analytics = true;
		}

		if ( $has_advanced_analytics ) {
			return null; // Has analytics platform capable of forecasting.
		}

		// Only recommend for e-commerce/subscription sites with scale.
		$needs_forecasting = false;
		
		if ( class_exists( 'WooCommerce' ) ) {
			// E-commerce needs inventory/demand forecasting.
			$product_count = wp_count_posts( 'product' )->publish;
			if ( $product_count > 20 ) {
				$needs_forecasting = true;
			}
		}

		// Check for subscriptions (need churn prediction).
		if ( class_exists( 'WC_Subscriptions' ) || class_exists( 'MeprUser' ) ) {
			$needs_forecasting = true;
		}

		if ( ! $needs_forecasting ) {
			return null; // Small site, less critical.
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => __(
				'No predictive analytics detected. Forecasting tools predict future trends from historical data - sales demand, content performance, churn risk, seasonal patterns. Enables proactive decisions: stock before demand spikes, create content on rising topics, retain at-risk members. Start with WooCommerce Analytics, Google Analytics forecasting, or Metorik for advanced predictions.',
				'wpshadow'
			),
			'severity'     => 'low',
			'threat_level' => 26,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/predictive-analytics',
		);
	}
}
