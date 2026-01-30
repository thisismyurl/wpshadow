<?php
/**
 * Optimole Cdn Performance Diagnostic
 *
 * Optimole Cdn Performance detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.766.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Optimole Cdn Performance Diagnostic Class
 *
 * @since 1.766.0000
 */
class Diagnostic_OptimoleCdnPerformance extends Diagnostic_Base {

	protected static $slug = 'optimole-cdn-performance';
	protected static $title = 'Optimole Cdn Performance';
	protected static $description = 'Optimole Cdn Performance detected';
	protected static $family = 'performance';

	public static function check() {
		if ( ! class_exists( 'Optimole\\Plugin' ) && ! defined( 'OPTIMOLE_VERSION' ) ) {
			return null;
		}

		$issues = array();

		// Check if Optimole is connected
		$api_key = get_option( 'optimole_api_key', '' );
		if ( empty( $api_key ) ) {
			$issues[] = 'Optimole API key not configured';
		}

		// Check image optimization quality settings
		$quality = get_option( 'optimole_quality', 'auto' );
		if ( 'high' === $quality ) {
			$issues[] = 'image quality set to high (reduces CDN performance benefit)';
		}

		// Check for lazy load configuration
		$lazy_load = get_option( 'optimole_lazyload_enabled', '1' );
		if ( '0' === $lazy_load ) {
			$issues[] = 'lazy loading disabled (impacts page speed)';
		}

		// Check for WebP format enablement
		$webp_enabled = get_option( 'optimole_webp_enabled', '1' );
		if ( '0' === $webp_enabled ) {
			$issues[] = 'WebP format disabled (missing compression benefits)';
		}

		// Check for CDN domain configuration
		$cdn_url = get_option( 'optimole_cdn_url', '' );
		if ( empty( $cdn_url ) && ! empty( $api_key ) ) {
			$issues[] = 'CDN URL not set despite API connection';
		}

		// Check for image count and quota
		$image_count = get_option( 'optimole_image_count', 0 );
		$quota_limit = get_option( 'optimole_quota_limit', 0 );
		if ( $image_count > 0 && $quota_limit > 0 ) {
			$usage_percent = ( $image_count / $quota_limit ) * 100;
			if ( $usage_percent > 90 ) {
				$issues[] = 'Optimole quota nearly exhausted (' . round( $usage_percent ) . '% used)';
			}
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 75, 45 + ( count( $issues ) * 7 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'Optimole CDN performance issues: ' . implode( ', ', $issues ),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/optimole-cdn-performance',
			);
		}

		return null;
	}
}
