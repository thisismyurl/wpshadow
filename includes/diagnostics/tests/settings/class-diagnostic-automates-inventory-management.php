<?php
/**
 * Inventory Management Automated Diagnostic
 *
 * Tests whether the site uses automated inventory management to prevent oversells and
 * stockouts. Proper inventory control is critical for customer satisfaction and operations.
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
 * Diagnostic_Automates_Inventory_Management Class
 *
 * Diagnostic #6: Inventory Management Automated from Specialized & Emerging Success Habits.
 * Checks if the site has automated inventory tracking.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Automates_Inventory_Management extends Diagnostic_Base {

	protected static $slug = 'automates-inventory-management';
	protected static $title = 'Inventory Management Automated';
	protected static $description = 'Tests whether the site uses automated inventory management';
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

		// Check stock management enabled.
		$stock_enabled = get_option( 'woocommerce_manage_stock', 'yes' );
		if ( 'yes' === $stock_enabled ) {
			++$score;
			$score_details[] = __( '✓ Stock management enabled', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ Stock management disabled', 'wpshadow' );
			$recommendations[] = __( 'Enable WooCommerce stock management to track inventory levels', 'wpshadow' );
		}

		// Check products with stock tracking.
		$products_with_stock = get_posts(
			array(
				'post_type'      => 'product',
				'posts_per_page' => 50,
				'post_status'    => 'publish',
				'meta_query'     => array(
					array(
						'key'     => '_manage_stock',
						'value'   => 'yes',
						'compare' => '=',
					),
				),
			)
		);

		if ( count( $products_with_stock ) >= 10 ) {
			$score += 2;
			$score_details[] = sprintf(
				/* translators: %d: number of products */
				__( '✓ %d+ products with stock tracking', 'wpshadow' ),
				count( $products_with_stock )
			);
		} elseif ( ! empty( $products_with_stock ) ) {
			++$score;
			$score_details[]   = sprintf( __( '◐ %d product(s) tracking stock', 'wpshadow' ), count( $products_with_stock ) );
			$recommendations[] = __( 'Enable stock management for all physical products', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No products tracking stock', 'wpshadow' );
			$recommendations[] = __( 'Configure stock quantities for all inventory items', 'wpshadow' );
		}

		// Check low stock notifications.
		$low_stock_threshold = get_option( 'woocommerce_notify_low_stock_amount', 2 );
		if ( $low_stock_threshold > 0 ) {
			++$score;
			$score_details[] = __( '✓ Low stock alerts configured', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No low stock notifications', 'wpshadow' );
			$recommendations[] = __( 'Set low stock threshold to receive reorder alerts', 'wpshadow' );
		}

		// Check inventory management plugins.
		$inventory_plugins = array(
			'stock-sync/stock-sync.php',
			'atum-stock-manager-for-woocommerce/atum-stock-manager-for-woocommerce.php',
		);

		$has_inventory_plugin = false;
		foreach ( $inventory_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_inventory_plugin = true;
				++$score;
				$score_details[] = __( '✓ Advanced inventory plugin active', 'wpshadow' );
				break;
			}
		}

		if ( ! $has_inventory_plugin ) {
			$score_details[]   = __( '✗ No advanced inventory system', 'wpshadow' );
			$recommendations[] = __( 'Consider ATUM or similar for advanced inventory features (forecasting, multi-location)', 'wpshadow' );
		}

		$score_percentage = ( $score / $max_score ) * 100;

		if ( $score_percentage < 30 ) {
			$severity     = 'medium';
			$threat_level = 30;
		} elseif ( $score_percentage < 60 ) {
			$severity     = 'low';
			$threat_level = 20;
		} else {
			return null;
		}

		return array(
			'id'               => self::$slug,
			'title'            => self::$title,
			'description'      => sprintf(
				/* translators: %d: score percentage */
				__( 'Inventory management score: %d%%. Overselling damages reputation and costs 3x more to fix than prevent. Automated inventory reduces stockouts by 65%% and prevents 95%% of oversell incidents. Real-time tracking enables data-driven purchasing decisions.', 'wpshadow' ),
				$score_percentage
			),
			'severity'         => $severity,
			'threat_level'     => $threat_level,
			'auto_fixable'     => false,
			'kb_link'          => 'https://wpshadow.com/kb/inventory-management',
			'details'          => $score_details,
			'recommendations'  => $recommendations,
			'impact'           => __( 'Automated inventory prevents customer disappointment, maintains accurate product availability, and streamlines reordering.', 'wpshadow' ),
		);
	}
}
