<?php
/**
 * Bundle Deals Optimized Diagnostic
 *
 * Tests whether the site creates strategic product bundles that outperform individual
 * item sales. Effective bundling increases average order value and moves slower inventory.
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
 * Diagnostic_Optimizes_Product_Bundles Class
 *
 * Diagnostic #12: Bundle Deals Optimized from Specialized & Emerging Success Habits.
 * Checks if the site offers strategic product bundles.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Optimizes_Product_Bundles extends Diagnostic_Base {

	protected static $slug = 'optimizes-product-bundles';
	protected static $title = 'Bundle Deals Optimized';
	protected static $description = 'Tests whether the site creates strategic product bundles that outperform individual sales';
	protected static $family = 'ecommerce-optimization';

	public static function check() {
		$score          = 0;
		$max_score      = 5;
		$score_details  = array();
		$recommendations = array();

		// Check WooCommerce active.
		if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			return null;
		}

		// Check bundle plugins.
		$bundle_plugins = array(
			'woocommerce-product-bundles/woocommerce-product-bundles.php',
			'yith-woocommerce-product-bundles/init.php',
			'wpc-product-bundles/wpc-product-bundles.php',
		);

		$has_bundle_plugin = false;
		foreach ( $bundle_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_bundle_plugin = true;
				++$score;
				$score_details[] = __( '✓ Product bundling plugin active', 'wpshadow' );
				break;
			}
		}

		if ( ! $has_bundle_plugin ) {
			$score_details[]   = __( '✗ No bundling plugin detected', 'wpshadow' );
			$recommendations[] = __( 'Install WooCommerce Product Bundles or similar plugin to create bundle deals', 'wpshadow' );
		}

		// Check bundle products.
		$bundle_products = get_posts(
			array(
				'post_type'      => 'product',
				'posts_per_page' => 20,
				'post_status'    => 'publish',
				's'              => 'bundle kit set package',
			)
		);

		if ( count( $bundle_products ) >= 3 ) {
			$score += 2;
			$score_details[] = sprintf(
				/* translators: %d: number of bundle products */
				__( '✓ %d+ bundle products found', 'wpshadow' ),
				count( $bundle_products )
			);
		} elseif ( ! empty( $bundle_products ) ) {
			++$score;
			$score_details[]   = sprintf( __( '◐ %d bundle product(s) found', 'wpshadow' ), count( $bundle_products ) );
			$recommendations[] = __( 'Create at least 3-5 strategic bundles (starter kits, complete sets, themed packages)', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No bundle products found', 'wpshadow' );
			$recommendations[] = __( 'Group complementary products into bundles with discount pricing (e.g., "Complete Starter Kit" vs individual items)', 'wpshadow' );
		}

		// Check bundle discounts/savings messaging.
		$savings_content = get_posts(
			array(
				'post_type'      => 'product',
				'posts_per_page' => 10,
				'post_status'    => 'publish',
				's'              => 'save savings discount bundle value',
			)
		);

		if ( ! empty( $savings_content ) ) {
			++$score;
			$score_details[] = __( '✓ Bundle savings messaging present', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No bundle value proposition found', 'wpshadow' );
			$recommendations[] = __( 'Highlight savings on bundles (e.g., "Save 20% when bundled" or "$50 value for $39")', 'wpshadow' );
		}

		// Check frequently bought together.
		$fbt_plugins = array(
			'woocommerce-frequently-bought-together/woocommerce-frequently-bought-together.php',
			'frequently-bought-together-for-woocommerce/frequently-bought-together-for-woocommerce.php',
		);

		$has_fbt = false;
		foreach ( $fbt_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_fbt = true;
				++$score;
				$score_details[] = __( '✓ "Frequently bought together" feature active', 'wpshadow' );
				break;
			}
		}

		if ( ! $has_fbt ) {
			$score_details[]   = __( '✗ No dynamic bundling suggestions', 'wpshadow' );
			$recommendations[] = __( 'Add "Frequently Bought Together" recommendations to suggest complementary products', 'wpshadow' );
		}

		$score_percentage = ( $score / $max_score ) * 100;

		if ( $score_percentage < 30 ) {
			$severity     = 'medium';
			$threat_level = 25;
		} elseif ( $score_percentage < 60 ) {
			$severity     = 'low';
			$threat_level = 15;
		} else {
			return null;
		}

		return array(
			'id'               => self::$slug,
			'title'            => self::$title,
			'description'      => sprintf(
				/* translators: %d: score percentage */
				__( 'Product bundling score: %d%%. Strategic bundles increase average order value by 30-50%% and move 20%% more inventory. Bundles convert 2x better than individual products when savings are clearly communicated.', 'wpshadow' ),
				$score_percentage
			),
			'severity'         => $severity,
			'threat_level'     => $threat_level,
			'auto_fixable'     => false,
			'kb_link'          => 'https://wpshadow.com/kb/product-bundles',
			'details'          => $score_details,
			'recommendations'  => $recommendations,
			'impact'           => __( 'Product bundling creates perceived value, simplifies customer decisions, and dramatically increases transaction size.', 'wpshadow' ),
		);
	}
}
