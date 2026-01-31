<?php
/**
 * Optimole Image Replacement Diagnostic
 *
 * Optimole Image Replacement detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.763.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Optimole Image Replacement Diagnostic Class
 *
 * @since 1.763.0000
 */
class Diagnostic_OptimoleImageReplacement extends Diagnostic_Base {

	protected static $slug = 'optimole-image-replacement';
	protected static $title = 'Optimole Image Replacement';
	protected static $description = 'Optimole Image Replacement detected';
	protected static $family = 'functionality';

	public static function check() {
		// Check for Optimole
		$optimole_active = defined( 'OPTIMOLE_VERSION' ) || class_exists( 'Optml_Main' );
		if ( ! $optimole_active ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: API key connected
		$api_key = get_option( 'optimole_api_key', '' );
		if ( empty( $api_key ) ) {
			$issues[] = __( 'Optimole API key not connected (service inactive)', 'wpshadow' );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Optimole not connected', 'wpshadow' ),
				'severity'    => 60,
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/optimole-image-replacement',
			);
		}
		
		// Check 2: Image quality setting
		$quality = get_option( 'optimole_image_quality', 'auto' );
		if ( 'high' === $quality ) {
			$issues[] = __( 'Image quality set to high (larger file sizes)', 'wpshadow' );
		} elseif ( 'low' === $quality ) {
			$issues[] = __( 'Image quality set to low (visual degradation)', 'wpshadow' );
		}
		
		// Check 3: Lazy loading
		$lazy_load = get_option( 'optimole_lazy_load', true );
		if ( ! $lazy_load ) {
			$issues[] = __( 'Lazy loading disabled (slower page loads)', 'wpshadow' );
		}
		
		// Check 4: WebP conversion
		$webp_enabled = get_option( 'optimole_enable_webp', true );
		if ( ! $webp_enabled ) {
			$issues[] = __( 'WebP format disabled (missing optimization)', 'wpshadow' );
		}
		
		// Check 5: Retina images
		$retina = get_option( 'optimole_enable_retina', false );
		if ( $retina ) {
			$issues[] = __( 'Retina images enabled (2x bandwidth usage)', 'wpshadow' );
		}
		
		// Check 6: Exclusion rules
		$exclusions = get_option( 'optimole_exclusions', array() );
		if ( count( $exclusions ) > 10 ) {
			$issues[] = sprintf( __( '%d URL exclusions (reduced optimization coverage)', 'wpshadow' ), count( $exclusions ) );
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
				/* translators: %s: list of image optimization issues */
				__( 'Optimole image replacement has %d configuration issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => true,
			'kb_link'     => 'https://wpshadow.com/kb/optimole-image-replacement',
		);
	}
}
