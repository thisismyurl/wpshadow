<?php
/**
 * Shortpixel Api Quota Diagnostic
 *
 * Shortpixel Api Quota detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.744.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Shortpixel Api Quota Diagnostic Class
 *
 * @since 1.744.0000
 */
class Diagnostic_ShortpixelApiQuota extends Diagnostic_Base {

	protected static $slug = 'shortpixel-api-quota';
	protected static $title = 'Shortpixel Api Quota';
	protected static $description = 'Shortpixel Api Quota detected';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'SHORTPIXEL_PLUGIN_VERSION' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: API key configured
		$api_key = get_option( 'wp-short-pixel-apiKey', '' );
		if ( empty( $api_key ) ) {
			$issues[] = __( 'ShortPixel API key not configured', 'wpshadow' );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'ShortPixel not connected', 'wpshadow' ),
				'severity'    => self::calculate_severity( 80 ),
				'threat_level' => 80,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/shortpixel-api-quota',
			);
		}
		
		// Check 2: API quota status
		$quota_data = get_option( 'wp-short-pixel-quota-data', array() );
		$quota_remaining = isset( $quota_data['APICallsQuotaNumeric'] ) ? intval( $quota_data['APICallsQuotaNumeric'] ) : 0;
		
		if ( $quota_remaining < 100 ) {
			$issues[] = sprintf( __( 'API quota low: %d images remaining', 'wpshadow' ), $quota_remaining );
		}
		
		// Check 3: Compression level
		$compression = get_option( 'wp-short-pixel-compression', 1 );
		if ( 0 === $compression ) { // Lossy
			$issues[] = __( 'Lossy compression (visual quality loss)', 'wpshadow' );
		}
		
		// Check 4: Backup originals
		$keep_backups = get_option( 'wp-short-backup', 1 );
		if ( ! $keep_backups ) {
			$issues[] = __( 'Original images not backed up (irreversible)', 'wpshadow' );
		}
		
		// Check 5: WebP generation
		$webp_enabled = get_option( 'wp-short-create-webp', 0 );
		if ( ! $webp_enabled ) {
			$issues[] = __( 'WebP generation disabled (missing optimization)', 'wpshadow' );
		}
		
		// Check 6: Resize large images
		$resize_enabled = get_option( 'wp-short-pixel-resize', 0 );
		$resize_width = get_option( 'wp-short-pixel-resize-width', 0 );
		
		if ( $resize_enabled && $resize_width < 1920 ) {
			$issues[] = sprintf( __( 'Max width: %dpx (may be too small for retina)', 'wpshadow' ), $resize_width );
		}
		
		// Check 7: Auto-optimize on upload
		$auto_optimize = get_option( 'wp-short-pixel-auto-media-library', 1 );
		if ( ! $auto_optimize ) {
			$issues[] = __( 'Auto-optimization on upload disabled (manual work)', 'wpshadow' );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 70;
		if ( count( $issues ) >= 5 ) {
			$threat_level = 84;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 77;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of ShortPixel issues */
				__( 'ShortPixel API has %d configuration issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/shortpixel-api-quota',
		);
	}
}
