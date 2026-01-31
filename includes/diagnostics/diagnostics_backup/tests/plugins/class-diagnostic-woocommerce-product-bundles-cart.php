<?php
/**
 * Woocommerce Product Bundles Cart Diagnostic
 *
 * Woocommerce Product Bundles Cart issues detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.676.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Woocommerce Product Bundles Cart Diagnostic Class
 *
 * @since 1.676.0000
 */
class Diagnostic_WoocommerceProductBundlesCart extends Diagnostic_Base {

	protected static $slug = 'woocommerce-product-bundles-cart';
	protected static $title = 'Woocommerce Product Bundles Cart';
	protected static $description = 'Woocommerce Product Bundles Cart issues detected';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! class_exists( 'WooCommerce' ) || ! class_exists( 'WC_Product_Bundle' ) ) {
			return null;
		}
		
		global $wpdb;
		$issues = array();
		
		// Check 1: Bundle products exist
		$bundle_count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} p
				 INNER JOIN {$wpdb->term_relationships} tr ON p.ID = tr.object_id
				 INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
				 INNER JOIN {$wpdb->terms} t ON tt.term_id = t.term_id
				 WHERE p.post_type = %s AND tt.taxonomy = %s AND t.slug = %s",
				'product',
				'product_type',
				'bundle'
			)
		);
		
		if ( $bundle_count === 0 ) {
			return null;
		}
		
		// Check 2: Bundle validation on add to cart
		$validate_bundles = get_option( 'wc_pb_validate_on_add_to_cart', true );
		if ( ! $validate_bundles ) {
			$issues[] = __( 'Bundle validation disabled (inventory/pricing errors)', 'wpshadow' );
		}
		
		// Check 3: Cart item grouping
		$group_mode = get_option( 'wc_pb_cart_item_grouping', 'parent' );
		if ( 'none' === $group_mode ) {
			$issues[] = __( 'Cart items not grouped (confusing checkout)', 'wpshadow' );
		}
		
		// Check 4: Min/max quantity enforcement
		$enforce_quantities = get_option( 'wc_pb_enforce_min_max_quantities', true );
		if ( ! $enforce_quantities ) {
			$issues[] = __( 'Min/max quantities not enforced (overselling risk)', 'wpshadow' );
		}
		
		// Check 5: Stock sync
		$sync_stock = get_option( 'wc_pb_sync_bundle_stock', true );
		if ( ! $sync_stock ) {
			$issues[] = __( 'Bundle stock not synced with components (overselling)', 'wpshadow' );
		}
		
		// Check 6: Price display
		$price_display = get_option( 'wc_pb_cart_price_display', 'total' );
		if ( 'components' === $price_display && $bundle_count > 10 ) {
			$issues[] = __( 'Component prices shown separately (cart clutter)', 'wpshadow' );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 50;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 62;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 56;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of bundle cart issues */
				__( 'WooCommerce Product Bundles cart has %d issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/woocommerce-product-bundles-cart',
		);
	}
}
