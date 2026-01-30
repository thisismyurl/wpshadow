<?php
/**
 * Ewww Image Optimizer Webp Diagnostic
 *
 * Ewww Image Optimizer Webp detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.752.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Ewww Image Optimizer Webp Diagnostic Class
 *
 * @since 1.752.0000
 */
class Diagnostic_EwwwImageOptimizerWebp extends Diagnostic_Base {

	protected static $slug = 'ewww-image-optimizer-webp';
	protected static $title = 'Ewww Image Optimizer Webp';
	protected static $description = 'Ewww Image Optimizer Webp detected';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! get_option( 'ewww_image_optimizer_enabled', '' ) && ! defined( 'EWWW_IMAGE_OPTIMIZER_PLUGIN_FILE' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: WebP conversion enabled
		$webp_enabled = get_option( 'ewww_image_optimizer_webp_conversion', 0 );
		if ( ! $webp_enabled ) {
			$issues[] = 'WebP conversion not enabled';
		}
		
		// Check 2: WebP quality setting
		$webp_quality = absint( get_option( 'ewww_image_optimizer_webp_quality', 0 ) );
		if ( $webp_quality <= 0 ) {
			$issues[] = 'WebP quality not configured';
		}
		
		// Check 3: Browser detection
		$browser_detect = get_option( 'ewww_image_optimizer_webp_browser_detection', 0 );
		if ( ! $browser_detect ) {
			$issues[] = 'Browser WebP support detection not enabled';
		}
		
		// Check 4: Fallback images
		$fallback = get_option( 'ewww_image_optimizer_webp_fallback', 0 );
		if ( ! $fallback ) {
			$issues[] = 'WebP fallback not enabled';
		}
		
		// Check 5: Picture tag support
		$picture_tag = get_option( 'ewww_image_optimizer_webp_picture_tag', 0 );
		if ( ! $picture_tag ) {
			$issues[] = 'Picture tag support not enabled';
		}
		
		// Check 6: CloudFlare Polish integration
		$cloudflare_polish = get_option( 'ewww_image_optimizer_cloudflare_webp', 0 );
		if ( ! $cloudflare_polish ) {
			$issues[] = 'CloudFlare Polish integration not checked';
		}
		
		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 40;
			$threat_multiplier = 6;
			$max_threat = 70;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );
			
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d WebP optimization issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/ewww-image-optimizer-webp',
			);
		}
		
		return null;
	}
}
