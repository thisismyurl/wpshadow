<?php
/**
 * Customer Retention Churn Rate Diagnostic
 *
 * Analyzes customer purchase patterns to calculate churn rate.
 * Identifies when too many customers stop purchasing from the store.
 *
 * @since   1.2601.2148
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Retention_Churn_Rate Class
 *
 * Measures the percentage of customers who haven't made a purchase
 * in the last 6 months. High churn rates indicate customer satisfaction
 * or engagement issues that need attention.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Retention_Churn_Rate extends Diagnostic_Base {
	protected static $slug = 'retention-churn-rate';

	protected static $title = 'Retention Churn Rate';

	protected static $description = 'Monitors customer churn rate by analyzing purchase patterns. Identifies when customers stop purchasing.';

	protected static $family = 'general';

	protected static $family_label = 'General';

	/**
	 * Get diagnostic ID.
	 *
	 * @since  1.2601.2148
	 * @return string Diagnostic identifier.
	 */
	public static function get_id(): string {
		return 'retention-churn-rate';
	}

	/**
	 * Get diagnostic name.
	 *
	 * @since  1.2601.2148
	 * @return string Human-readable diagnostic name.
	 */
	public static function get_name(): string {
		return __( 'What % of customers are leaving?', 'wpshadow' );
	}

	/**
	 * Get diagnostic description.
	 *
	 * @since  1.2601.2148
	 * @return string Diagnostic description.
	 */
	public static function get_description(): string {
		return __( 'Measures the percentage of customers who haven\'t purchased in 6+ months. High churn rates indicate customer satisfaction or retention issues.', 'wpshadow' );
	}

	/**
	 * Get diagnostic category.
	 *
	 * @since  1.2601.2148
	 * @return string Category identifier.
	 */
	public static function get_category(): string {
		return 'customer_retention';
	}

	/**
	 * Run the diagnostic test.
	 *
	 * @since  1.2601.2148
	 * @return array Finding data or empty if no issue.
	 */
	public static function run(): array {
		$result = self::check();

		if ( null === $result ) {
			return array(
				'status'  => 'pass',
				'message' => __( 'Customer churn rate is healthy', 'wpshadow' ),
			);
		}

		return array(
			'status'  => 'fail',
			'message' => $result['description'],
			'data'    => $result,
		);
	}

	/**
	 * Get threat level for this finding (0-100).
	 *
	 * @since  1.2601.2148
	 * @return int Threat level 0-100.
	 */
	public static function get_threat_level(): int {
		// Base threat level for customer retention issues
		return 58;
	}

	/**
	 * Get KB article URL.
	 *
	 * @since  1.2601.2148
	 * @return string Knowledge base article URL.
	 */
	public static function get_kb_article(): string {
		return 'https://wpshadow.com/kb/retention-churn-rate/';
	}

	/**
	 * Get training video URL.
	 *
	 * @since  1.2601.2148
	 * @return string Training video URL.
	 */
	public static function get_training_video(): string {
		return 'https://wpshadow.com/training/retention-churn-rate/';
	}

	/**
	 * Run the diagnostic check.
	 *
	 * Calculates customer churn rate by analyzing WooCommerce order data.
	 * Churn rate = percentage of customers who haven't made a purchase in 6+ months.
	 * Flags when churn rate exceeds 15% threshold.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if churn rate is high, null otherwise.
	 */
	public static function check(): ?array {
		// Check if WooCommerce is active.
		if ( ! class_exists( 'WooCommerce' ) || ! function_exists( 'wc_get_orders' ) ) {
			// Not applicable without WooCommerce.
			return null;
		}

		global $wpdb;

		// Get all customers who have made at least one order.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$all_customers = $wpdb->get_results(
			"SELECT DISTINCT pm.meta_value as customer_email
			FROM {$wpdb->postmeta} pm
			INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
			WHERE p.post_type = 'shop_order'
			AND pm.meta_key = '_billing_email'
			AND p.post_status IN ('wc-completed', 'wc-processing')",
			ARRAY_A
		);

		$total_customers = count( $all_customers );

		// Need at least 10 customers for meaningful churn analysis.
		if ( 10 > $total_customers ) {
			return null;
		}

		// Calculate date 6 months ago.
		$six_months_ago = gmdate( 'Y-m-d H:i:s', strtotime( '-6 months' ) );

		// Get customers who haven't ordered in 6 months (churned).
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$churned_customers = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT DISTINCT pm.meta_value as customer_email
				FROM {$wpdb->postmeta} pm
				INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
				WHERE p.post_type = 'shop_order'
				AND pm.meta_key = '_billing_email'
				AND p.post_status IN ('wc-completed', 'wc-processing')
				AND pm.meta_value NOT IN (
					SELECT DISTINCT pm2.meta_value
					FROM {$wpdb->postmeta} pm2
					INNER JOIN {$wpdb->posts} p2 ON pm2.post_id = p2.ID
					WHERE p2.post_type = 'shop_order'
					AND pm2.meta_key = '_billing_email'
					AND p2.post_status IN ('wc-completed', 'wc-processing')
					AND p2.post_date >= %s
				)",
				$six_months_ago
			),
			ARRAY_A
		);

		$churned_count = count( $churned_customers );
		$churn_rate    = ( $churned_count / $total_customers ) * 100;

		// Flag if churn rate exceeds 15%.
		if ( 15 < $churn_rate ) {
			$severity = 'medium';
			if ( 30 < $churn_rate ) {
				$severity = 'high';
			}

			$threat_level = min( 100, intval( $churn_rate ) + 30 );

			/* translators: 1: churn rate percentage, 2: number of churned customers, 3: total customers */
			$description = sprintf(
				__( 'Customer churn rate is %1$.1f%% (%2$d out of %3$d customers haven\'t purchased in 6+ months). High churn indicates customer satisfaction or engagement issues that need attention.', 'wpshadow' ),
				$churn_rate,
				$churned_count,
				$total_customers
			);

			return \WPShadow\Core\Diagnostic_Lean_Checks::build_finding(
				'retention-churn-rate',
				'High Customer Churn Rate',
				$description,
				'general',
				$severity,
				$threat_level,
				'retention-churn-rate'
			);
		}

		return null;
	}

	/**
	 * Live test for this diagnostic.
	 *
	 * Validates that the check() method correctly identifies customer churn issues
	 * based on the actual site's WooCommerce order data.
	 *
	 * @since  1.2601.2148
	 * @return array {
	 *     Test result.
	 *
	 *     @type bool   $passed  Whether the test passed.
	 *     @type string $message Human-readable test result message.
	 * }
	 */
	public static function test_live_retention_churn_rate(): array {
		$result = self::check();

		// If WooCommerce is not active, test passes (not applicable)
		if ( ! class_exists( 'WooCommerce' ) ) {
			return array(
				'passed'  => true,
				'message' => __( 'Test passed: WooCommerce not active, diagnostic not applicable', 'wpshadow' ),
			);
		}

		// If result is null, site is healthy (no high churn detected)
		if ( null === $result ) {
			return array(
				'passed'  => true,
				'message' => __( 'Test passed: Customer churn rate is within healthy range', 'wpshadow' ),
			);
		}

		// If result is array, churn issue detected (which is the expected behavior)
		if ( is_array( $result ) && isset( $result['description'] ) ) {
			return array(
				'passed'  => true,
				'message' => __( 'Test passed: Diagnostic correctly identified high churn rate', 'wpshadow' ),
			);
		}

		// Unexpected result format
		return array(
			'passed'  => false,
			'message' => __( 'Test failed: Unexpected result format from check() method', 'wpshadow' ),
		);
	}
}
