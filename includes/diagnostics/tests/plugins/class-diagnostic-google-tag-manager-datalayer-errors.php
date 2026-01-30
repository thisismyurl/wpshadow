<?php
/**
 * Google Tag Manager Datalayer Errors Diagnostic
 *
 * Google Tag Manager Datalayer Errors misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1345.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Google Tag Manager Datalayer Errors Diagnostic Class
 *
 * @since 1.1345.0000
 */
class Diagnostic_GoogleTagManagerDatalayerErrors extends Diagnostic_Base {

	protected static $slug = 'google-tag-manager-dataLayer-errors';
	protected static $title = 'Google Tag Manager Datalayer Errors';
	protected static $description = 'Google Tag Manager Datalayer Errors misconfigured';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'GTM4WP_VERSION' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: GTM container ID
		$container_id = get_option( 'gtm4wp-options' );
		if ( empty( $container_id['gtm-code'] ) ) {
			$issues[] = __( 'GTM container ID not configured', 'wpshadow' );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Google Tag Manager container ID missing', 'wpshadow' ),
				'severity'    => self::calculate_severity( 50 ),
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/google-tag-manager-dataLayer-errors',
			);
		}
		
		// Check 2: Container loading method
		$placement = isset( $container_id['gtm-placement'] ) ? $container_id['gtm-placement'] : 'head';
		if ( 'footer' === $placement ) {
			$issues[] = __( 'GTM in footer (delayed tracking, data loss)', 'wpshadow' );
		}
		
		// Check 3: DataLayer enabled
		$datalayer_enabled = isset( $container_id['include-datalayer'] ) ? $container_id['include-datalayer'] : false;
		if ( ! $datalayer_enabled ) {
			$issues[] = __( 'DataLayer disabled (no structured data)', 'wpshadow' );
		}
		
		// Check 4: E-commerce tracking
		if ( class_exists( 'WooCommerce' ) ) {
			$ecommerce_tracking = isset( $container_id['integrate-woocommerce-track-enhanced-ecommerce'] ) ? 
			                       $container_id['integrate-woocommerce-track-enhanced-ecommerce'] : false;
			if ( ! $ecommerce_tracking ) {
				$issues[] = __( 'Enhanced e-commerce tracking disabled (revenue tracking lost)', 'wpshadow' );
			}
		}
		
		// Check 5: Event tracking
		$track_events = isset( $container_id['events'] ) ? $container_id['events'] : array();
		if ( empty( $track_events ) ) {
			$issues[] = __( 'No event tracking configured (limited insights)', 'wpshadow' );
		}
		
		// Check 6: User data in dataLayer
		$include_user_data = isset( $container_id['include-user-id'] ) ? $container_id['include-user-id'] : false;
		if ( $include_user_data ) {
			$issues[] = __( 'User ID in dataLayer (GDPR concern)', 'wpshadow' );
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
				/* translators: %s: list of GTM dataLayer issues */
				__( 'Google Tag Manager has %d dataLayer issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/google-tag-manager-dataLayer-errors',
		);
	}
}
