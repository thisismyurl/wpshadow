<?php
/**
 * Optimole Quality Settings Diagnostic
 *
 * Optimole Quality Settings detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.767.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Optimole Quality Settings Diagnostic Class
 *
 * @since 1.767.0000
 */
class Diagnostic_OptimoleQualitySettings extends Diagnostic_Base {

	protected static $slug = 'optimole-quality-settings';
	protected static $title = 'Optimole Quality Settings';
	protected static $description = 'Optimole Quality Settings detected';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'OPTIMOLE_VERSION' ) ) {
			return null;
		}
		
		$api_key = get_option( 'optimole_api_key', '' );
		if ( empty( $api_key ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Compression quality
		$quality = get_option( 'optimole_quality', 'auto' );
		if ( 'high' === $quality ) {
			$issues[] = __( 'High quality setting (minimal compression)', 'wpshadow' );
		} elseif ( is_numeric( $quality ) && $quality > 85 ) {
			$issues[] = sprintf( __( 'Quality: %d%% (files still large)', 'wpshadow' ), $quality );
		}
		
		// Check 2: Auto quality disabled
		if ( 'auto' !== $quality ) {
			$issues[] = __( 'Auto quality disabled (not optimizing per image)', 'wpshadow' );
		}
		
		// Check 3: Resize settings
		$auto_resize = get_option( 'optimole_auto_resize', true );
		if ( ! $auto_resize ) {
			$issues[] = __( 'Auto-resize disabled (serving full-size images)', 'wpshadow' );
		}
		
		// Check 4: WebP conversion
		$webp_enabled = get_option( 'optimole_webp', true );
		if ( ! $webp_enabled ) {
			$issues[] = __( 'WebP disabled (missing format optimization)', 'wpshadow' );
		}
		
		// Check 5: Lazy load
		$lazy_load = get_option( 'optimole_lazy_load', true );
		if ( ! $lazy_load ) {
			$issues[] = __( 'Lazy loading disabled (initial page weight high)', 'wpshadow' );
		}
		
		// Check 6: Quality by device type
		$quality_mobile = get_option( 'optimole_quality_mobile', 'auto' );
		if ( 'auto' !== $quality_mobile && $quality_mobile === $quality ) {
			$issues[] = __( 'Same quality for mobile (not optimized for slower connections)', 'wpshadow' );
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
				/* translators: %s: list of quality setting issues */
				__( 'Optimole quality settings have %d optimization opportunities: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/optimole-quality-settings',
		);
	}
}
