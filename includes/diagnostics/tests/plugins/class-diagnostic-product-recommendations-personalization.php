<?php
/**
 * Product Recommendations Personalization Diagnostic
 *
 * Product Recommendations Personalization issue found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1244.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Product Recommendations Personalization Diagnostic Class
 *
 * @since 1.1244.0000
 */
class Diagnostic_ProductRecommendationsPersonalization extends Diagnostic_Base {

	protected static $slug = 'product-recommendations-personalization';
	protected static $title = 'Product Recommendations Personalization';
	protected static $description = 'Product Recommendations Personalization issue found';
	protected static $family = 'functionality';

	public static function check() {
		// Check for WooCommerce product recommendation plugins
		$has_recommendations = class_exists( 'WooCommerce' ) &&
		                       ( get_option( 'wc_product_recommendations_enabled', '' ) !== '' ||
		                         defined( 'WC_RECOMMENDATIONS_VERSION' ) );

		if ( ! $has_recommendations ) {
			return null;
		}

		$issues = array();

		// Check 1: Tracking enabled
		$tracking = get_option( 'wc_recommendations_tracking', 'no' );
		if ( 'no' === $tracking ) {
			$issues[] = __( 'Tracking disabled (no personalization)', 'wpshadow' );
		}

		// Check 2: Cache recommendations
		$cache = get_option( 'wc_recommendations_cache', 'no' );
		if ( 'no' === $cache ) {
			$issues[] = __( 'Recommendations not cached (slow queries)', 'wpshadow' );
		}

		// Check 3: Recommendation algorithm
		$algorithm = get_option( 'wc_recommendations_algorithm', 'popularity' );
		if ( 'popularity' === $algorithm ) {
			$issues[] = __( 'Using popularity only (not personalized)', 'wpshadow' );
		}

		// Check 4: Minimum data threshold
		$min_data = get_option( 'wc_recommendations_min_data', 0 );
		if ( $min_data === 0 ) {
			$issues[] = __( 'No minimum data threshold (poor recommendations)', 'wpshadow' );
		}

		// Check 5: Fallback products
		$fallback = get_option( 'wc_recommendations_fallback', '' );
		if ( empty( $fallback ) ) {
			$issues[] = __( 'No fallback products (empty recommendations)', 'wpshadow' );
		}

		// Check 6: Privacy compliance
		$anonymize = get_option( 'wc_recommendations_anonymize', 'no' );
		if ( 'no' === $anonymize ) {
			$issues[] = __( 'Data not anonymized (GDPR risk)', 'wpshadow' );
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
				__( 'Product recommendations have %d issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/product-recommendations-personalization',
		);
	}
}
