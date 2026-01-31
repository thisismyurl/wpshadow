<?php
/**
 * Smush Pro Webp Conversion Diagnostic
 *
 * Smush Pro Webp Conversion detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.760.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Smush Pro Webp Conversion Diagnostic Class
 *
 * @since 1.760.0000
 */
class Diagnostic_SmushProWebpConversion extends Diagnostic_Base {

	protected static $slug = 'smush-pro-webp-conversion';
	protected static $title = 'Smush Pro Webp Conversion';
	protected static $description = 'Smush Pro Webp Conversion detected';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'WP_SMUSH_VERSION' ) ) {
			return null;
		}
		
		$issues = array();

		// Check 1: Verify WebP conversion is enabled
		$webp_enabled = get_option( 'smush_webp_mod', false );
		if ( ! $webp_enabled ) {
			$issues[] = __( 'Smush WebP conversion not enabled', 'wpshadow' );
		}

		// Check 2: Check fallback image configuration
		$fallback_images = get_option( 'smush_webp_fallback', false );
		if ( ! $fallback_images ) {
			$issues[] = __( 'WebP fallback images not configured', 'wpshadow' );
		}

		// Check 3: Verify browser compatibility detection
		$browser_detection = get_option( 'smush_webp_browser_detection', false );
		if ( ! $browser_detection ) {
			$issues[] = __( 'Browser compatibility detection not enabled', 'wpshadow' );
		}

		// Check 4: Check conversion quality settings
		$conversion_quality = get_option( 'smush_webp_quality', 0 );
		if ( $conversion_quality < 80 || $conversion_quality > 95 ) {
			$issues[] = __( 'WebP conversion quality not optimally configured', 'wpshadow' );
		}

		// Check 5: Verify bulk conversion status
		$bulk_conversion = get_option( 'smush_webp_bulk_enabled', false );
		if ( ! $bulk_conversion ) {
			$issues[] = __( 'Bulk WebP conversion not enabled', 'wpshadow' );
		}

		// Check 6: Check automatic conversion on upload
		$auto_conversion = get_option( 'smush_webp_auto_on_upload', false );
		if ( ! $auto_conversion ) {
			$issues[] = __( 'Automatic WebP conversion on upload not enabled', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 80, 50 + ( count( $issues ) * 5 ) );
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: Comma-separated list of issues */
					__( 'Smush Pro WebP conversion issues detected: %s', 'wpshadow' ),
					implode( ', ', $issues )
				),
				'severity'     => 'medium',
				'threat_level' => $threat_level,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/smush-pro-webp-conversion',
			);
		}

		return null;
	}
}
