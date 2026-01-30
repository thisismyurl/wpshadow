<?php
/**
 * Woocommerce Composite Products Performance Diagnostic
 *
 * Woocommerce Composite Products Performance issues detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.673.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Woocommerce Composite Products Performance Diagnostic Class
 *
 * @since 1.673.0000
 */
class Diagnostic_WoocommerceCompositeProductsPerformance extends Diagnostic_Base {

	protected static $slug = 'woocommerce-composite-products-performance';
	protected static $title = 'Woocommerce Composite Products Performance';
	protected static $description = 'Woocommerce Composite Products Performance issues detected';
	protected static $family = 'performance';

	public static function check() {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return null;
		}
		
		// Check for Composite Products extension
		if ( ! class_exists( 'WC_Composite_Products' ) ) {
			return null;
		}
		
		global $wpdb;
		$issues = array();
		
		// Check 1: Count composite products
		$composite_count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} p 
				INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id 
				WHERE p.post_type = %s AND pm.meta_key = %s",
				'product',
				'_bto_data'
			)
		);
		
		if ( $composite_count === 0 ) {
			return null;
		}
		
		// Check 2: Complex composites (many components)
		$complex_composites = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT p.ID, pm.meta_value 
				FROM {$wpdb->posts} p 
				INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id 
				WHERE p.post_type = %s AND pm.meta_key = %s",
				'product',
				'_bto_data'
			)
		);
		
		$high_complexity = 0;
		foreach ( $complex_composites as $composite ) {
			$data = maybe_unserialize( $composite->meta_value );
			if ( is_array( $data ) && count( $data ) > 6 ) {
				$high_complexity++;
			}
		}
		
		if ( $high_complexity > 0 ) {
			$issues[] = sprintf( __( '%d composites with >6 components (slow calculation)', 'wpshadow' ), $high_complexity );
		}
		
		// Check 3: Configuration caching
		$cache_enabled = get_option( 'woocommerce_composite_cache_enabled', 'no' );
		if ( 'no' === $cache_enabled ) {
			$issues[] = __( 'Configuration caching disabled (repeated queries)', 'wpshadow' );
		}
		
		// Check 4: Ajax price calculation
		$ajax_prices = get_option( 'woocommerce_composite_ajax_prices', 'yes' );
		if ( 'no' === $ajax_prices && $high_complexity > 3 ) {
			$issues[] = __( 'Ajax pricing disabled with complex products (page load slow)', 'wpshadow' );
		}
		
		// Check 5: Stock management overhead
		$manage_stock = get_option( 'woocommerce_composite_manage_stock', 'yes' );
		if ( 'yes' === $manage_stock && $composite_count > 50 ) {
			$issues[] = sprintf( __( '%d composites with stock management (inventory overhead)', 'wpshadow' ), $composite_count );
		}
		
		// Check 6: Thumbnail regeneration
		$regen_thumbs = get_option( 'woocommerce_composite_regen_images', 'no' );
		if ( 'yes' === $regen_thumbs ) {
			$issues[] = __( 'Image regeneration enabled (processing overhead)', 'wpshadow' );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 55;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 68;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 62;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of performance issues */
				__( 'WooCommerce Composite Products has %d performance issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/woocommerce-composite-products-performance',
		);
	}
}
