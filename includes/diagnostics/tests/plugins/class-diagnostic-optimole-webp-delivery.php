<?php
/**
 * Optimole Webp Delivery Diagnostic
 *
 * Optimole Webp Delivery detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.765.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Optimole Webp Delivery Diagnostic Class
 *
 * @since 1.765.0000
 */
class Diagnostic_OptimoleWebpDelivery extends Diagnostic_Base {

	protected static $slug = 'optimole-webp-delivery';
	protected static $title = 'Optimole Webp Delivery';
	protected static $description = 'Optimole Webp Delivery detected';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'OPTML_VERSION' ) && ! class_exists( 'Optml_Main' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Verify Optimole is enabled
		$enabled = get_option( 'optml_enabled', 0 );
		if ( ! $enabled ) {
			$issues[] = 'Optimole not enabled';
		}

		// Check 2: Check for WebP delivery
		$webp_enabled = get_option( 'optml_webp', 0 );
		if ( ! $webp_enabled ) {
			$issues[] = 'WebP delivery not enabled';
		}

		// Check 3: Verify lazy loading
		$lazy_load = get_option( 'optml_lazyload', 0 );
		if ( ! $lazy_load ) {
			$issues[] = 'Lazy loading not enabled';
		}

		// Check 4: Check for responsive images
		$responsive = get_option( 'optml_responsive', 0 );
		if ( ! $responsive ) {
			$issues[] = 'Responsive image optimization not enabled';
		}

		// Check 5: Verify CDN delivery
		$cdn_enabled = get_option( 'optml_cdn', 0 );
		if ( ! $cdn_enabled ) {
			$issues[] = 'CDN delivery not enabled';
		}

		// Check 6: Check for quality optimization
		$quality = get_option( 'optml_quality', 0 );
		if ( $quality <= 0 ) {
			$issues[] = 'Image quality optimization not configured';
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
					'Found %d Optimole WebP delivery issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/optimole-webp-delivery',
			);
		}

		return null;
	}
}
