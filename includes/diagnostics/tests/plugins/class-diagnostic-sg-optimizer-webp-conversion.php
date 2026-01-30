<?php
/**
 * Sg Optimizer Webp Conversion Diagnostic
 *
 * Sg Optimizer Webp Conversion not optimized.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.911.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sg Optimizer Webp Conversion Diagnostic Class
 *
 * @since 1.911.0000
 */
class Diagnostic_SgOptimizerWebpConversion extends Diagnostic_Base {

	protected static $slug = 'sg-optimizer-webp-conversion';
	protected static $title = 'Sg Optimizer Webp Conversion';
	protected static $description = 'Sg Optimizer Webp Conversion not optimized';
	protected static $family = 'performance';

	public static function check() {
		// Check for SG Optimizer or SiteGround hosting
		$has_sg_optimizer = defined( 'SG_OPTIMIZER_VERSION' ) ||
		                    get_option( 'siteground_optimizer_options', false ) !== false ||
		                    ( defined( 'SITEGROUND_PLUGIN_VERSION' ) );
		
		if ( ! $has_sg_optimizer ) {
			return null;
		}
		
		$issues = array();
		$sg_options = get_option( 'siteground_optimizer_webp_support', array() );
		
		// Check 1: WebP generation enabled
		$webp_enabled = isset( $sg_options['enabled'] ) ? $sg_options['enabled'] : 'off';
		if ( 'off' === $webp_enabled ) {
			return null; // Not using WebP
		}
		
		// Check 2: Fallback images
		$fallback = get_option( 'siteground_optimizer_webp_fallback', 'yes' );
		if ( 'no' === $fallback ) {
			$issues[] = __( 'No fallback images (older browsers unsupported)', 'wpshadow' );
		}
		
		// Check 3: Browser detection
		$browser_check = get_option( 'siteground_optimizer_browser_check', 'yes' );
		if ( 'no' === $browser_check ) {
			$issues[] = __( 'Browser detection disabled (incompatible images)', 'wpshadow' );
		}
		
		// Check 4: Conversion quality
		$quality = get_option( 'siteground_optimizer_webp_quality', 80 );
		if ( $quality < 70 ) {
			$issues[] = sprintf( __( '%d%% quality (visible degradation)', 'wpshadow' ), $quality );
		}
		
		// Check 5: Bulk conversion status
		global $wpdb;
		$total_images = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'attachment' AND post_mime_type LIKE 'image/%'"
		);
		
		$webp_count = get_option( 'siteground_optimizer_webp_converted_count', 0 );
		
		if ( $total_images > 0 && $webp_count < ( $total_images * 0.5 ) ) {
			$issues[] = sprintf( __( 'Only %d of %d images converted', 'wpshadow' ), $webp_count, $total_images );
		}
		
		// Check 6: Storage overhead
		$keep_originals = get_option( 'siteground_optimizer_keep_originals', 'yes' );
		if ( 'yes' === $keep_originals && $webp_count > 500 ) {
			$issues[] = __( 'Keeping originals (2x storage usage)', 'wpshadow' );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 45;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 58;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 52;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of WebP conversion issues */
				__( 'SG Optimizer WebP has %d conversion issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/sg-optimizer-webp-conversion',
		);
	}
}
