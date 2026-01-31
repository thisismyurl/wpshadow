<?php
/**
 * Woocommerce Composite Products Inventory Diagnostic
 *
 * Woocommerce Composite Products Inventory issues detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.672.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Woocommerce Composite Products Inventory Diagnostic Class
 *
 * @since 1.672.0000
 */
class Diagnostic_WoocommerceCompositeProductsInventory extends Diagnostic_Base {

	protected static $slug = 'woocommerce-composite-products-inventory';
	protected static $title = 'Woocommerce Composite Products Inventory';
	protected static $description = 'Woocommerce Composite Products Inventory issues detected';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return null;
		}
		
		// Check if Composite Products is active
		if ( ! class_exists( 'WC_Composite_Products' ) && ! defined( 'WC_CP_VERSION' ) ) {
			return null;
		}

		$issues = array();
		$threat_level = 0;

		global $wpdb;

		// Check for composite products
		$composite_products = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} p
				 INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
				 WHERE p.post_type = %s
				 AND pm.meta_key = %s",
				'product',
				'_bto_data'
			)
		);

		if ( $composite_products > 0 ) {
			// Check inventory sync
			$sync_enabled = get_option( 'wc_cp_sync_component_stock', 'yes' );
			if ( $sync_enabled === 'no' ) {
				$issues[] = 'inventory_sync_disabled';
				$threat_level += 30;
			}

			// Check stock management
			$manage_stock = get_option( 'wc_cp_manage_composite_stock', 'yes' );
			if ( $manage_stock === 'no' ) {
				$issues[] = 'stock_management_disabled';
				$threat_level += 25;
			}

			// Check for out of stock components
			$out_of_stock = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$wpdb->postmeta}
					 WHERE meta_key = %s
					 AND meta_value = %s",
					'_stock_status',
					'outofstock'
				)
			);
			if ( $out_of_stock > 10 ) {
				$issues[] = 'components_out_of_stock';
				$threat_level += 20;
			}
		}

		// Check low stock alerts
		$low_stock_alerts = get_option( 'wc_cp_low_stock_alerts', 'yes' );
		if ( $low_stock_alerts === 'no' ) {
			$issues[] = 'low_stock_alerts_disabled';
			$threat_level += 15;
		}

		// Check backorder handling
		$allow_backorders = get_option( 'wc_cp_allow_component_backorders', 'no' );
		if ( $allow_backorders === 'yes' ) {
			$issues[] = 'backorders_enabled_without_tracking';
			$threat_level += 15;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %s: list of inventory issues */
				__( 'WooCommerce Composite Products inventory has problems: %s. This causes overselling and stock discrepancies.', 'wpshadow' ),
				implode( ', ', array_map( function( $issue ) {
					return ucwords( str_replace( '_', ' ', $issue ) );
				}, $issues ) )
			);

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => $description,
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/woocommerce-composite-products-inventory',
			);
		}
		
		return null;
	}
}
