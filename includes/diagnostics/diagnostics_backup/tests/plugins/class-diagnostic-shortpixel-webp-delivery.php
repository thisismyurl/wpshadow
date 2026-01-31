<?php
/**
 * Shortpixel Webp Delivery Diagnostic
 *
 * Shortpixel Webp Delivery detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.748.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Shortpixel Webp Delivery Diagnostic Class
 *
 * @since 1.748.0000
 */
class Diagnostic_ShortpixelWebpDelivery extends Diagnostic_Base {

	protected static $slug = 'shortpixel-webp-delivery';
	protected static $title = 'Shortpixel Webp Delivery';
	protected static $description = 'Shortpixel Webp Delivery detected';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'SHORTPIXEL_PLUGIN_VERSION' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: WebP delivery enabled
		$webp = get_option( 'shortpixel_webp_delivery_enabled', 0 );
		if ( ! $webp ) {
			$issues[] = 'WebP delivery not enabled';
		}

		// Check 2: Browser detection
		$browser = get_option( 'shortpixel_browser_webp_support_detection', 0 );
		if ( ! $browser ) {
			$issues[] = 'Browser WebP support detection not enabled';
		}

		// Check 3: Fallback images
		$fallback = get_option( 'shortpixel_webp_fallback_images_enabled', 0 );
		if ( ! $fallback ) {
			$issues[] = 'WebP fallback images not enabled';
		}

		// Check 4: Picture element support
		$picture = get_option( 'shortpixel_picture_element_enabled', 0 );
		if ( ! $picture ) {
			$issues[] = 'Picture element support not enabled';
		}

		// Check 5: Lazy loading
		$lazy = get_option( 'shortpixel_lazy_loading_enabled', 0 );
		if ( ! $lazy ) {
			$issues[] = 'Lazy loading not enabled for WebP';
		}

		// Check 6: CDN integration
		$cdn = get_option( 'shortpixel_cdn_integration_enabled', 0 );
		if ( ! $cdn ) {
			$issues[] = 'CDN integration not configured';
		}

		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 35;
			$threat_multiplier = 6;
			$max_threat = 65;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d WebP delivery issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/shortpixel-webp-delivery',
			);
		}

		return null;
	}
}
