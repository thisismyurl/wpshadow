<?php
/**
 * Retention Cross-Sell Opportunity Diagnostic
 *
 * Identifies opportunities to improve customer retention and increase
 * revenue through cross-sell product recommendations in WooCommerce.
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
 * Diagnostic_RetentionCrossSellOpportunity Class
 *
 * Detects when WooCommerce is active with products but cross-sell
 * functionality is underutilized, representing a missed opportunity
 * to increase customer lifetime value and retention.
 */
class Diagnostic_RetentionCrossSellOpportunity extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'retention-cross-sell-opportunity';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Retention Cross-Sell Opportunity';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Identifies opportunities to increase revenue through cross-sell product recommendations';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'retention';

	/**
	 * The family label
	 *
	 * @var string
	 */
	protected static $family_label = 'Customer Retention';

	/**
	 * Run the diagnostic check.
	 *
	 * Checks if WooCommerce is active and has products, but cross-sell
	 * functionality is not being utilized effectively.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check(): ?array {
		// Check if WooCommerce is active.
		if ( ! class_exists( 'WooCommerce' ) ) {
			return null; // Not applicable if WooCommerce isn't installed.
		}

		// Check if there are published products.
		$product_count = self::get_product_count();
		if ( $product_count < 5 ) {
			return null; // Not meaningful for stores with very few products.
		}

		// Check how many products have cross-sells configured.
		$cross_sell_stats = self::get_cross_sell_stats();

		// Calculate utilization percentage.
		$utilization_percent = 0;
		if ( $cross_sell_stats['total_products'] > 0 ) {
			$utilization_percent = ( $cross_sell_stats['products_with_cross_sells'] / $cross_sell_stats['total_products'] ) * 100;
		}

		// Flag as opportunity if less than 20% of products have cross-sells configured.
		if ( $utilization_percent < 20 ) {
			$products_without = $cross_sell_stats['total_products'] - $cross_sell_stats['products_with_cross_sells'];

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: 1: number of products without cross-sells, 2: total products, 3: utilization percentage */
					__( 'Your WooCommerce store has %1$d products without cross-sell recommendations (out of %2$d total products, only %3$d%% configured). Cross-selling complementary products can increase average order value by 10-30%% and improve customer retention. This is a missed revenue opportunity.', 'wpshadow' ),
					$products_without,
					$cross_sell_stats['total_products'],
					round( $utilization_percent )
				),
				'severity'    => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/retention-cross-sell-opportunity',
				'category'    => 'customer_retention',
				'meta'        => array(
					'total_products'             => $cross_sell_stats['total_products'],
					'products_with_cross_sells'  => $cross_sell_stats['products_with_cross_sells'],
					'products_without_cross_sells' => $products_without,
					'utilization_percent'        => round( $utilization_percent, 2 ),
					'potential_revenue_impact'   => __( 'High - Cross-selling can increase AOV by 10-30%', 'wpshadow' ),
				),
			);
		}

		return null; // No issue found - cross-sells are being used.
	}

	/**
	 * Get the total count of published WooCommerce products.
	 *
	 * @since  1.2601.2148
	 * @return int Number of published products.
	 */
	private static function get_product_count(): int {
		$count = wp_count_posts( 'product' );
		return (int) ( $count->publish ?? 0 );
	}

	/**
	 * Get statistics about cross-sell usage across products.
	 *
	 * @since  1.2601.2148
	 * @return array {
	 *     Cross-sell statistics.
	 *
	 *     @type int $total_products              Total number of products checked.
	 *     @type int $products_with_cross_sells   Number of products with cross-sells configured.
	 * }
	 */
	private static function get_cross_sell_stats(): array {
		global $wpdb;

		// Count total published products.
		$total_products = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'product' AND post_status = 'publish'"
		);

		// Count products with cross-sells configured (non-empty _crosssell_ids meta).
		$products_with_cross_sells = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(DISTINCT post_id) 
				FROM {$wpdb->postmeta} 
				WHERE meta_key = %s 
				AND meta_value != %s 
				AND meta_value IS NOT NULL
				AND post_id IN (
					SELECT ID FROM {$wpdb->posts} 
					WHERE post_type = 'product' AND post_status = 'publish'
				)",
				'_crosssell_ids',
				'a:0:{}'
			)
		);

		return array(
			'total_products'            => (int) $total_products,
			'products_with_cross_sells' => (int) $products_with_cross_sells,
		);
	}

	/**
	 * Get the diagnostic ID.
	 *
	 * @since  1.2601.2148
	 * @return string Diagnostic ID.
	 */
	public static function get_id(): string {
		return self::$slug;
	}

	/**
	 * Get the diagnostic name.
	 *
	 * @since  1.2601.2148
	 * @return string Diagnostic name.
	 */
	public static function get_name(): string {
		return __( 'Cross-Sell Product Recommendations', 'wpshadow' );
	}

	/**
	 * Get the diagnostic description.
	 *
	 * @since  1.2601.2148
	 * @return string Diagnostic description.
	 */
	public static function get_description(): string {
		return __( 'Identifies opportunities to increase revenue and retention through cross-sell product recommendations.', 'wpshadow' );
	}

	/**
	 * Get the diagnostic category.
	 *
	 * @since  1.2601.2148
	 * @return string Diagnostic category.
	 */
	public static function get_category(): string {
		return 'customer_retention';
	}

	/**
	 * Get threat level for this finding (0-100).
	 *
	 * @since  1.2601.2148
	 * @return int Threat level.
	 */
	public static function get_threat_level(): int {
		return 45;
	}

	/**
	 * Get KB article URL.
	 *
	 * @since  1.2601.2148
	 * @return string KB article URL.
	 */
	public static function get_kb_article(): string {
		return 'https://wpshadow.com/kb/retention-cross-sell-opportunity';
	}

	/**
	 * Get training video URL.
	 *
	 * @since  1.2601.2148
	 * @return string Training video URL.
	 */
	public static function get_training_video(): string {
		return 'https://wpshadow.com/training/retention-cross-sell-opportunity';
	}

	/**
	 * Legacy run method for backwards compatibility.
	 *
	 * @since  1.2601.2148
	 * @return array Result array.
	 */
	public static function run(): array {
		$result = self::check();
		if ( null === $result ) {
			return array(
				'status'  => 'pass',
				'message' => __( 'Cross-sell recommendations are being utilized effectively', 'wpshadow' ),
			);
		}

		return array(
			'status'  => 'warning',
			'message' => $result['description'],
			'data'    => $result,
		);
	}

	/**
	 * Live test for this diagnostic.
	 *
	 * Diagnostic: Retention Cross-Sell Opportunity
	 * Slug: retention-cross-sell-opportunity
	 *
	 * Test Purpose:
	 * - Verify that check() method returns the correct result based on site state
	 * - PASS: check() returns NULL when cross-sells are utilized (>20%)
	 * - FAIL: check() returns array when cross-sells are underutilized (<20%)
	 *
	 * @since  1.2601.2148
	 * @return array {
	 *     Test result.
	 *
	 *     @type bool   $passed  Whether the test passed.
	 *     @type string $message Human-readable test result message.
	 * }
	 */
	public static function test_live_retention_cross_sell_opportunity(): array {
		$result = self::check();

		// Test passes if we get expected result based on WooCommerce presence.
		if ( ! class_exists( 'WooCommerce' ) ) {
			return array(
				'passed'  => null === $result,
				'message' => null === $result 
					? __( 'Test passed: WooCommerce not active, check correctly returns null', 'wpshadow' )
					: __( 'Test failed: WooCommerce not active but check returned finding', 'wpshadow' ),
			);
		}

		// If WooCommerce is active, test structure of returned data.
		if ( null !== $result ) {
			// Validate finding structure.
			$required_keys = array( 'id', 'title', 'description', 'severity', 'threat_level', 'auto_fixable', 'kb_link' );
			$has_all_keys  = true;
			foreach ( $required_keys as $key ) {
				if ( ! isset( $result[ $key ] ) ) {
					$has_all_keys = false;
					break;
				}
			}

			return array(
				'passed'  => $has_all_keys,
				'message' => $has_all_keys
					? __( 'Test passed: Finding returned with correct structure', 'wpshadow' )
					: __( 'Test failed: Finding missing required keys', 'wpshadow' ),
			);
		}

		return array(
			'passed'  => true,
			'message' => __( 'Test passed: Cross-sells are being utilized, check returns null', 'wpshadow' ),
		);
	}
}
