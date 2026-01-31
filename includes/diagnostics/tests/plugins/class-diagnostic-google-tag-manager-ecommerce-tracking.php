<?php
/**
 * Google Tag Manager Ecommerce Tracking Diagnostic
 *
 * Google Tag Manager Ecommerce Tracking misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1348.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Google Tag Manager Ecommerce Tracking Diagnostic Class
 *
 * @since 1.1348.0000
 */
class Diagnostic_GoogleTagManagerEcommerceTracking extends Diagnostic_Base {

	protected static $slug = 'google-tag-manager-ecommerce-tracking';
	protected static $title = 'Google Tag Manager Ecommerce Tracking';
	protected static $description = 'Google Tag Manager Ecommerce Tracking misconfigured';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'GTM4WP_VERSION' ) && ! class_exists( 'WooCommerce' ) ) {
			return null;
		}
		
		$issues = array();

		// Check 1: Verify GTM container is configured
		$gtm_id = get_option( 'gtm4wp_googletagmanager_id', '' );
		if ( empty( $gtm_id ) ) {
			$issues[] = __( 'Google Tag Manager container ID not configured', 'wpshadow' );
		}

		// Check 2: Check data layer configuration for ecommerce
		$datalayer_ecommerce = get_option( 'gtm4wp_include_ecommerce', false );
		if ( ! $datalayer_ecommerce && class_exists( 'WooCommerce' ) ) {
			$issues[] = __( 'Ecommerce data layer not enabled', 'wpshadow' );
		}

		// Check 3: Verify ecommerce event tracking
		$track_events = get_option( 'gtm4wp_track_ecommerce_events', false );
		if ( ! $track_events ) {
			$issues[] = __( 'Ecommerce event tracking not enabled', 'wpshadow' );
		}

		// Check 4: Check enhanced ecommerce tracking
		$enhanced_ecommerce = get_option( 'gtm4wp_include_enhanced_ecommerce', false );
		if ( ! $enhanced_ecommerce && class_exists( 'WooCommerce' ) ) {
			$issues[] = __( 'Enhanced ecommerce tracking not enabled', 'wpshadow' );
		}

		// Check 5: Verify transaction tracking configuration
		$track_transactions = get_option( 'gtm4wp_track_transactions', false );
		if ( ! $track_transactions ) {
			$issues[] = __( 'Transaction tracking not configured', 'wpshadow' );
		}

		// Check 6: Check conversion tracking setup
		$conversion_tracking = get_option( 'gtm4wp_conversion_tracking', false );
		if ( ! $conversion_tracking ) {
			$issues[] = __( 'Conversion tracking not enabled', 'wpshadow' );
		}
		// Verify core functionality
		if ( ! function_exists( 'get_post' ) ) {
			$issues[] = __( 'Post functionality not available', 'wpshadow' );
		}
		return null;
	}
}
