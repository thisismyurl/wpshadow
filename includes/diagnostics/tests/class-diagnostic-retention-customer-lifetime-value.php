<?php
/**
 * Customer Lifetime Value Diagnostic
 *
 * Checks if Customer Lifetime Value (CLV) tracking and analysis is in place
 * for e-commerce sites to ensure retention strategy is data-driven.
 *
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_RetentionCustomerLifetimeValue Class
 *
 * Verifies that sites with customer/order data have proper Customer Lifetime Value
 * tracking mechanisms in place. CLV is critical for retention strategy and helps
 * identify high-value customers who warrant special attention.
 *
 * @since 1.2601.2148
 */
class Diagnostic_RetentionCustomerLifetimeValue extends Diagnostic_Base {

	/**
	 * The diagnostic slug/ID
	 *
	 * @var string
	 */
	protected static $slug = 'retention-customer-lifetime-value';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Customer Lifetime Value Tracking';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies Customer Lifetime Value tracking is available for retention strategy';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'retention';

	/**
	 * Display name for the family
	 *
	 * @var string
	 */
	protected static $family_label = 'Customer Retention';

	/**
	 * Run the diagnostic check.
	 *
	 * Checks if the site has:
	 * 1. E-commerce functionality (WooCommerce, EDD, etc.)
	 * 2. Customer order data
	 * 3. CLV tracking capability or metadata
	 *
	 * @since 1.2601.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check(): ?array {
		// Check if WooCommerce is active (most common e-commerce platform).
		$has_woocommerce = class_exists( 'WooCommerce' );

		// Check if Easy Digital Downloads is active.
		$has_edd = class_exists( 'Easy_Digital_Downloads' );

		// If no e-commerce platform detected, this diagnostic is not applicable.
		if ( ! $has_woocommerce && ! $has_edd ) {
			return null;
		}

		// Check for orders/customer data.
		$has_customer_data = self::has_customer_order_data( $has_woocommerce, $has_edd );

		// If no customer data exists yet, diagnostic is not applicable.
		if ( ! $has_customer_data ) {
			return null;
		}

		// Check if CLV tracking is in place.
		$has_clv_tracking = self::has_clv_tracking_capability( $has_woocommerce, $has_edd );

		// If CLV tracking exists, no issue found.
		if ( $has_clv_tracking ) {
			return null;
		}

		// Issue found: E-commerce active with customer data but no CLV tracking.
		$platform_name = $has_woocommerce ? 'WooCommerce' : 'Easy Digital Downloads';

		return array(
			'finding_id'   => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %s: E-commerce platform name */
				__( 'Your site has %s with customer orders, but Customer Lifetime Value (CLV) tracking is not configured. CLV helps identify your most valuable customers and optimize retention strategy. Consider installing a CLV tracking plugin or implementing custom CLV calculations to measure average customer value over time, repeat purchase behavior, and segment customers by value tier.', 'wpshadow' ),
				esc_html( $platform_name )
			),
			'category'     => 'retention',
			'severity'     => 'medium',
			'threat_level' => 45,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/retention-customer-lifetime-value/',
			'timestamp'    => current_time( 'mysql' ),
		);
	}

	/**
	 * Check if site has customer order data.
	 *
	 * @since 1.2601.2148
	 * @param bool $has_woocommerce Whether WooCommerce is active.
	 * @param bool $has_edd         Whether Easy Digital Downloads is active.
	 * @return bool True if customer data exists.
	 */
	private static function has_customer_order_data( bool $has_woocommerce, bool $has_edd ): bool {
		global $wpdb;

		if ( $has_woocommerce ) {
			// Check for WooCommerce orders (WC 3.0+ uses custom tables).
			if ( function_exists( 'wc_get_orders' ) ) {
				$orders = wc_get_orders(
					array(
						'limit'  => 1,
						'status' => array( 'wc-completed', 'wc-processing', 'wc-on-hold' ),
					)
				);
				if ( ! empty( $orders ) ) {
					return true;
				}
			}

			// Fallback: Check posts table for shop_order post type.
			$order_count = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s AND post_status IN ('wc-completed', 'wc-processing', 'wc-on-hold') LIMIT 1",
					'shop_order'
				)
			);

			if ( $order_count && $order_count > 0 ) {
				return true;
			}
		}

		if ( $has_edd ) {
			// Check for Easy Digital Downloads payments.
			$payment_count = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s AND post_status IN ('publish', 'complete') LIMIT 1",
					'edd_payment'
				)
			);

			if ( $payment_count && $payment_count > 0 ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if CLV tracking capability exists.
	 *
	 * Looks for:
	 * - Custom CLV metadata on customers
	 * - CLV tracking plugins
	 * - Custom CLV implementations
	 *
	 * @since 1.2601.2148
	 * @param bool $has_woocommerce Whether WooCommerce is active.
	 * @param bool $has_edd         Whether Easy Digital Downloads is active.
	 * @return bool True if CLV tracking exists.
	 */
	private static function has_clv_tracking_capability( bool $has_woocommerce, bool $has_edd ): bool {
		global $wpdb;

		// Check for common CLV-related user meta keys.
		$clv_meta_keys = array(
			'customer_lifetime_value',
			'_customer_lifetime_value',
			'clv',
			'_clv',
			'total_customer_value',
			'customer_value',
			'lifetime_value',
		);

		foreach ( $clv_meta_keys as $meta_key ) {
			$meta_exists = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$wpdb->usermeta} WHERE meta_key = %s LIMIT 1",
					$meta_key
				)
			);

			if ( $meta_exists && $meta_exists > 0 ) {
				return true;
			}
		}

		// Check for WooCommerce customer metadata (WC 3.0+).
		if ( $has_woocommerce ) {
			$wc_clv = $wpdb->get_var(
				"SELECT COUNT(*) FROM {$wpdb->usermeta} 
				WHERE meta_key LIKE '%_order_count%' 
				OR meta_key LIKE '%_lifetime%' 
				LIMIT 1"
			);

			if ( $wc_clv && $wc_clv > 0 ) {
				return true;
			}
		}

		// Check for known CLV tracking plugins.
		$clv_plugins = array(
			'metorik/metorik.php',
			'customer-lifetime-value/customer-lifetime-value.php',
			'woocommerce-customer-analytics/woocommerce-customer-analytics.php',
		);

		foreach ( $clv_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return true;
			}
		}

		// Check for custom database tables that might track CLV.
		$custom_tables = array(
			$wpdb->prefix . 'customer_analytics',
			$wpdb->prefix . 'customer_lifetime_value',
			$wpdb->prefix . 'clv_tracking',
		);

		foreach ( $custom_tables as $table ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$table_exists = $wpdb->get_var(
				$wpdb->prepare(
					'SHOW TABLES LIKE %s',
					$table
				)
			);

			if ( $table_exists ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Get diagnostic ID.
	 *
	 * @since 1.2601.2148
	 * @return string Diagnostic ID.
	 */
	public static function get_id(): string {
		return 'retention-customer-lifetime-value';
	}

	/**
	 * Get diagnostic name.
	 *
	 * @since 1.2601.2148
	 * @return string Diagnostic name.
	 */
	public static function get_name(): string {
		return __( 'Is Customer Lifetime Value being tracked?', 'wpshadow' );
	}

	/**
	 * Get diagnostic description.
	 *
	 * @since 1.2601.2148
	 * @return string Diagnostic description.
	 */
	public static function get_description(): string {
		return __( 'Verifies that sites with e-commerce functionality have Customer Lifetime Value tracking in place for retention strategy optimization.', 'wpshadow' );
	}

	/**
	 * Get diagnostic category.
	 *
	 * @since 1.2601.2148
	 * @return string Category identifier.
	 */
	public static function get_category(): string {
		return 'customer_retention';
	}

	/**
	 * Run the diagnostic test.
	 *
	 * @since 1.2601.2148
	 * @return array Finding data or empty if no issue.
	 */
	public static function run(): array {
		$result = self::check();
		return is_array( $result ) ? $result : array();
	}

	/**
	 * Get threat level for this finding (0-100).
	 *
	 * @since 1.2601.2148
	 * @return int Threat level.
	 */
	public static function get_threat_level(): int {
		return 45;
	}

	/**
	 * Get KB article URL.
	 *
	 * @since 1.2601.2148
	 * @return string Knowledge base article URL.
	 */
	public static function get_kb_article(): string {
		return 'https://wpshadow.com/kb/retention-customer-lifetime-value/';
	}

	/**
	 * Get training video URL.
	 *
	 * @since 1.2601.2148
	 * @return string Training video URL.
	 */
	public static function get_training_video(): string {
		return 'https://wpshadow.com/training/retention-customer-lifetime-value/';
	}

	/**
	 * Live test for this diagnostic.
	 *
	 * Diagnostic: Customer Lifetime Value Tracking
	 * Slug: retention-customer-lifetime-value
	 *
	 * Test Purpose:
	 * - Verify that check() method returns the correct result based on site state
	 * - PASS: check() returns NULL when diagnostic condition is NOT met (site is healthy)
	 * - FAIL: check() returns array when diagnostic condition IS met (issue found)
	 * - Description: Verifies Customer Lifetime Value tracking is available for retention strategy
	 *
	 * @since 1.2601.2148
	 * @return array {
	 *     Test result information.
	 *
	 *     @type bool   $passed  Whether the test passed.
	 *     @type string $message Human-readable test result message.
	 * }
	 */
	public static function test_live_retention_customer_lifetime_value(): array {
		$result = self::check();

		// Check if e-commerce is active.
		$has_ecommerce = class_exists( 'WooCommerce' ) || class_exists( 'Easy_Digital_Downloads' );

		// If no e-commerce, test should pass (diagnostic returns null).
		if ( ! $has_ecommerce ) {
			$expected_null = true;
			$actual_null   = is_null( $result );

			return array(
				'passed'  => ( $expected_null === $actual_null ),
				'message' => $actual_null
					? 'No e-commerce platform detected, diagnostic correctly returns null'
					: 'No e-commerce platform but diagnostic incorrectly returned a finding',
			);
		}

		// E-commerce exists - check if customer data exists.
		$has_customer_data = self::has_customer_order_data(
			class_exists( 'WooCommerce' ),
			class_exists( 'Easy_Digital_Downloads' )
		);

		// If no customer data, test should pass (diagnostic returns null).
		if ( ! $has_customer_data ) {
			$expected_null = true;
			$actual_null   = is_null( $result );

			return array(
				'passed'  => ( $expected_null === $actual_null ),
				'message' => $actual_null
					? 'No customer data yet, diagnostic correctly returns null'
					: 'No customer data but diagnostic incorrectly returned a finding',
			);
		}

		// E-commerce with customer data exists - check CLV tracking.
		$has_clv = self::has_clv_tracking_capability(
			class_exists( 'WooCommerce' ),
			class_exists( 'Easy_Digital_Downloads' )
		);

		$diagnostic_found_issue = is_array( $result );

		// If CLV tracking exists, diagnostic should return null (no issue).
		// If CLV tracking missing, diagnostic should return array (issue found).
		$expected_issue = ! $has_clv;
		$test_passes    = ( $expected_issue === $diagnostic_found_issue );

		$message = $test_passes
			? sprintf(
				'CLV tracking status correctly detected: %s',
				$has_clv ? 'tracking exists (no issue)' : 'tracking missing (issue found)'
			)
			: sprintf(
				'Mismatch: CLV tracking is %s but diagnostic %s',
				$has_clv ? 'present' : 'absent',
				$diagnostic_found_issue ? 'found issue' : 'found no issue'
			);

		return array(
			'passed'  => $test_passes,
			'message' => $message,
		);
	}
}
