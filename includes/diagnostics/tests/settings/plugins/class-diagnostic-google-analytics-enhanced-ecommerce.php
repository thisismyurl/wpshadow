<?php
/**
 * Google Analytics Enhanced Ecommerce Diagnostic
 *
 * Google Analytics Enhanced Ecommerce misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1340.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Google Analytics Enhanced Ecommerce Diagnostic Class
 *
 * @since 1.1340.0000
 */
class Diagnostic_GoogleAnalyticsEnhancedEcommerce extends Diagnostic_Base {

	protected static $slug = 'google-analytics-enhanced-ecommerce';
	protected static $title = 'Google Analytics Enhanced Ecommerce';
	protected static $description = 'Google Analytics Enhanced Ecommerce misconfigured';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! function_exists( 'ga_load_options' ) || defined( 'MONSTERINSIGHTS_VERSION' ) ) {
			return null;
		}
		
		$issues = array();
		$threat_level = 0;

		// Check if WooCommerce is active
		if ( ! class_exists( 'WooCommerce' ) ) {
			return null;
		}

		// Get Google Analytics settings
		$ga_options = get_option( 'ga_options', array() );

		// Check tracking ID
		$tracking_id = isset( $ga_options['tracking_id'] ) ? $ga_options['tracking_id'] : '';
		if ( empty( $tracking_id ) ) {
			$issues[] = 'tracking_id_not_configured';
			$threat_level += 25;
		}

		// Check enhanced ecommerce
		$enhanced_ecommerce = isset( $ga_options['enhanced_ecommerce'] ) ? $ga_options['enhanced_ecommerce'] : false;
		if ( ! $enhanced_ecommerce ) {
			$issues[] = 'enhanced_ecommerce_disabled';
			$threat_level += 30;
		}

		// Check transaction tracking
		$track_transactions = isset( $ga_options['track_transactions'] ) ? $ga_options['track_transactions'] : false;
		if ( ! $track_transactions ) {
			$issues[] = 'transaction_tracking_disabled';
			$threat_level += 20;
		}

		// Check product impressions
		$track_impressions = isset( $ga_options['track_product_impressions'] ) ? $ga_options['track_product_impressions'] : false;
		if ( ! $track_impressions ) {
			$issues[] = 'product_impression_tracking_disabled';
			$threat_level += 15;
		}

		// Check add to cart tracking
		$track_add_to_cart = isset( $ga_options['track_add_to_cart'] ) ? $ga_options['track_add_to_cart'] : false;
		if ( ! $track_add_to_cart ) {
			$issues[] = 'add_to_cart_tracking_disabled';
			$threat_level += 15;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %s: list of ecommerce tracking issues */
				__( 'Google Analytics Enhanced Ecommerce is misconfigured: %s. This causes incomplete sales data and poor ROI tracking.', 'wpshadow' ),
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
				'kb_link'     => 'https://wpshadow.com/kb/google-analytics-enhanced-ecommerce',
			);
		}
		
		return null;
	}
}
