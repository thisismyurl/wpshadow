<?php
/**
 * Product Recommendations Algorithm Diagnostic
 *
 * Product Recommendations Algorithm issue found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1242.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Product Recommendations Algorithm Diagnostic Class
 *
 * @since 1.1242.0000
 */
class Diagnostic_ProductRecommendationsAlgorithm extends Diagnostic_Base {

	protected static $slug = 'product-recommendations-algorithm';
	protected static $title = 'Product Recommendations Algorithm';
	protected static $description = 'Product Recommendations Algorithm issue found';
	protected static $family = 'functionality';

	public static function check() {
		// Check for WooCommerce and recommendation plugins
		if ( ! class_exists( 'WooCommerce' ) ) {
			return null;
		}
		
		$has_recommendations = get_option( 'wc_recommendations_enabled', false ) ||
		                       class_exists( 'WC_Product_Recommendations' ) ||
		                       defined( 'YITH_WCRP_VERSION' );
		
		if ( ! $has_recommendations ) {
			return null;
		}
		
		global $wpdb;
		$issues = array();
		
		// Check 1: Algorithm type
		$algorithm = get_option( 'wc_recommendations_algorithm', 'collaborative' );
		if ( 'random' === $algorithm ) {
			$issues[] = __( 'Random recommendations (no personalization)', 'wpshadow' );
		}
		
		// Check 2: Minimum data threshold
		$min_interactions = get_option( 'wc_recommendations_min_interactions', 5 );
		if ( $min_interactions < 3 ) {
			$issues[] = __( 'Low interaction threshold (inaccurate recommendations)', 'wpshadow' );
		}
		
		// Check 3: Cache duration
		$cache_duration = get_option( 'wc_recommendations_cache_duration', 3600 );
		if ( $cache_duration < 1800 ) { // 30 minutes
			$issues[] = __( 'Short cache duration (frequent recalculation)', 'wpshadow' );
		}
		
		// Check 4: Fallback products
		$has_fallback = get_option( 'wc_recommendations_fallback', false );
		if ( ! $has_fallback ) {
			$issues[] = __( 'No fallback products (empty recommendations possible)', 'wpshadow' );
		}
		
		// Check 5: Diversity setting
		$diversity = get_option( 'wc_recommendations_diversity', 0.5 );
		if ( $diversity < 0.3 ) {
			$issues[] = __( 'Low diversity (repetitive recommendations)', 'wpshadow' );
		}
		
		// Check 6: Real-time vs batch processing
		$processing_mode = get_option( 'wc_recommendations_processing', 'realtime' );
		if ( 'realtime' === $processing_mode ) {
			$product_count = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s AND post_status = 'publish'",
					'product'
				)
			);
			
			if ( $product_count > 1000 ) {
				$issues[] = sprintf( __( 'Real-time processing with %d products (slow)', 'wpshadow' ), $product_count );
			}
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
				/* translators: %s: list of algorithm issues */
				__( 'Product recommendations algorithm has %d issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/product-recommendations-algorithm',
		);
	}
}
