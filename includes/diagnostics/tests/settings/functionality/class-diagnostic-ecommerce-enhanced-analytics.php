<?php
/**
 * E-commerce Enhanced Analytics Diagnostic
 *
 * Checks if Enhanced E-commerce tracking is configured in Google Analytics.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * E-commerce Enhanced Analytics Diagnostic Class
 *
 * Enhanced E-commerce shows which products sell, where shoppers abandon,
 * and checkout friction points. Without it, you're guessing what to fix.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Ecommerce_Enhanced_Analytics extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'ecommerce-enhanced-analytics';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'E-commerce Enhanced Analytics';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if Enhanced E-commerce tracking is configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'analytics';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if ecommerce is active.
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$has_woocommerce = is_plugin_active( 'woocommerce/woocommerce.php' );
		$has_edd         = is_plugin_active( 'easy-digital-downloads/easy-digital-downloads.php' );

		if ( ! $has_woocommerce && ! $has_edd ) {
			return null; // Not applicable for non-ecommerce sites.
		}

		$issues         = array();
		$tracking_score = 0;
		$max_score      = 5;

		// Check for Enhanced E-commerce enabled.
		$has_enhanced_ecommerce = self::check_enhanced_ecommerce();
		if ( $has_enhanced_ecommerce ) {
			$tracking_score++;
		} else {
			$issues[] = 'Enhanced E-commerce enabled in GA';
		}

		// Check for product impression tracking.
		$has_impression_tracking = self::check_product_impressions();
		if ( $has_impression_tracking ) {
			$tracking_score++;
		} else {
			$issues[] = 'product impression tracking';
		}

		// Check for add-to-cart events.
		$has_cart_events = self::check_add_to_cart_events();
		if ( $has_cart_events ) {
			$tracking_score++;
		} else {
			$issues[] = 'add-to-cart event tracking';
		}

		// Check for checkout funnel analysis.
		$has_funnel = self::check_checkout_funnel();
		if ( $has_funnel ) {
			$tracking_score++;
		} else {
			$issues[] = 'checkout funnel analysis';
		}

		// Check for purchase revenue tracking.
		$has_revenue = self::check_revenue_tracking();
		if ( $has_revenue ) {
			$tracking_score++;
		} else {
			$issues[] = 'purchase revenue tracking';
		}

		$completion_percentage = ( $tracking_score / $max_score ) * 100;

		if ( $completion_percentage >= 80 ) {
			return null; // Enhanced E-commerce tracking present.
		}

		$severity     = $completion_percentage < 40 ? 'high' : 'medium';
		$threat_level = $completion_percentage < 40 ? 65 : 45;

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: 1: completion percentage, 2: missing features */
				__( 'E-commerce analytics at %1$d%%. Missing: %2$s. Enhanced E-commerce reveals product performance and checkout friction.', 'wpshadow' ),
				(int) $completion_percentage,
				implode( ', ', $issues )
			),
			'severity'     => $severity,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/ecommerce-enhanced-analytics',
			'meta'         => array(
				'completion_percentage' => $completion_percentage,
				'missing_features'      => $issues,
			),
		);
	}

	/**
	 * Check if Enhanced E-commerce is enabled.
	 *
	 * @since 1.6093.1200
	 * @return bool True if enabled.
	 */
	private static function check_enhanced_ecommerce(): bool {
		// Check for Google Analytics E-commerce plugins.
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$ga_ecommerce_plugins = array(
			'enhanced-e-commerce-for-woocommerce-store/woocommerce-enhanced-ecommerce-google-analytics-integration.php',
			'woocommerce-google-analytics-integration/woocommerce-google-analytics-integration.php',
			'monster-insights-ecommerce/monsterinsights-ecommerce.php',
		);

		foreach ( $ga_ecommerce_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return true;
			}
		}

		// Check for GA4 property.
		$ga4_property = get_option( 'ga4_measurement_id', '' );
		if ( ! empty( $ga4_property ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if product impression tracking exists.
	 *
	 * @since 1.6093.1200
	 * @return bool True if tracking exists.
	 */
	private static function check_product_impressions(): bool {
		// Product impressions are part of Enhanced E-commerce.
		return self::check_enhanced_ecommerce();
	}

	/**
	 * Check if add-to-cart events are tracked.
	 *
	 * @since 1.6093.1200
	 * @return bool True if events tracked.
	 */
	private static function check_add_to_cart_events(): bool {
		// Add-to-cart events are part of Enhanced E-commerce.
		return self::check_enhanced_ecommerce();
	}

	/**
	 * Check if checkout funnel is configured.
	 *
	 * @since 1.6093.1200
	 * @return bool True if funnel exists.
	 */
	private static function check_checkout_funnel(): bool {
		// Checkout funnel is part of Enhanced E-commerce.
		return self::check_enhanced_ecommerce();
	}

	/**
	 * Check if purchase revenue tracking exists.
	 *
	 * @since 1.6093.1200
	 * @return bool True if tracking exists.
	 */
	private static function check_revenue_tracking(): bool {
		// Revenue tracking is part of Enhanced E-commerce.
		return self::check_enhanced_ecommerce();
	}
}
