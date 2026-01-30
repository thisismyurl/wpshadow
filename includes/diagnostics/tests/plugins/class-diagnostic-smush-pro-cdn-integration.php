<?php
/**
 * Smush Pro Cdn Integration Diagnostic
 *
 * Smush Pro Cdn Integration detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.757.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Smush Pro Cdn Integration Diagnostic Class
 *
 * @since 1.757.0000
 */
class Diagnostic_SmushProCdnIntegration extends Diagnostic_Base {

	protected static $slug = 'smush-pro-cdn-integration';
	protected static $title = 'Smush Pro Cdn Integration';
	protected static $description = 'Smush Pro Cdn Integration detected';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'WP_SMUSH_VERSION' ) ) {
			return null;
		}
		
		// Check for Pro version
		if ( ! class_exists( 'WP_Smush_Pro' ) && ! defined( 'WP_SMUSH_PRO' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: CDN enabled
		$cdn_status = get_option( 'wp-smush-cdn_status', 0 );
		if ( ! $cdn_status ) {
			return null; // CDN not enabled
		}
		
		// Check 2: API key
		$api_key = get_option( 'wp-smush-api_key', '' );
		if ( empty( $api_key ) ) {
			$issues[] = __( 'CDN API key missing', 'wpshadow' );
		}
		
		// Check 3: WebP delivery
		$webp_enabled = get_option( 'wp-smush-webp', 0 );
		if ( ! $webp_enabled ) {
			$issues[] = __( 'WebP delivery disabled (larger image sizes)', 'wpshadow' );
		}
		
		// Check 4: Auto-resize
		$auto_resize = get_option( 'wp-smush-resize', 0 );
		if ( ! $auto_resize ) {
			$issues[] = __( 'Auto-resize disabled (oversized images)', 'wpshadow' );
		}
		
		// Check 5: CDN caching
		$cdn_cache = get_option( 'wp-smush-cdn_cache_time', 31536000 );
		if ( $cdn_cache < 2592000 ) {
			$issues[] = sprintf( __( 'CDN cache TTL %d days (frequent refetches)', 'wpshadow' ), $cdn_cache / 86400 );
		}
		
		// Check 6: Image serving
		$serve_method = get_option( 'wp-smush-cdn_serve_method', 'rewrite' );
		if ( 'javascript' === $serve_method ) {
			$issues[] = __( 'JavaScript image serving (render-blocking)', 'wpshadow' );
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
				/* translators: %s: list of CDN integration issues */
				__( 'Smush Pro CDN has %d integration issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/smush-pro-cdn-integration',
		);
	}
}
