<?php
/**
 * Uncode Theme Adaptive Images Diagnostic
 *
 * Uncode Theme Adaptive Images needs optimization.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1330.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Uncode Theme Adaptive Images Diagnostic Class
 *
 * @since 1.1330.0000
 */
class Diagnostic_UncodeThemeAdaptiveImages extends Diagnostic_Base {

	protected static $slug = 'uncode-theme-adaptive-images';
	protected static $title = 'Uncode Theme Adaptive Images';
	protected static $description = 'Uncode Theme Adaptive Images needs optimization';
	protected static $family = 'functionality';

	public static function check() {
		// Check for Uncode theme
		$theme = wp_get_theme();
		if ( 'Uncode' !== $theme->name && 'Uncode' !== $theme->parent_theme ) {
			return null;
		}
		
		global $wpdb;
		$issues = array();
		
		// Check 1: Adaptive images enabled
		$adaptive_enabled = get_option( 'uncode_adaptive_images', false );
		if ( ! $adaptive_enabled ) {
			return null;
		}
		
		// Check 2: Image breakpoints configured
		$breakpoints = get_option( 'uncode_adaptive_breakpoints', array() );
		if ( empty( $breakpoints ) || count( $breakpoints ) < 3 ) {
			$issues[] = __( 'Insufficient responsive breakpoints configured (recommend 3-5)', 'wpshadow' );
		}
		
		// Check 3: WebP format support
		$webp_enabled = get_option( 'uncode_adaptive_webp', false );
		if ( ! $webp_enabled ) {
			$issues[] = __( 'WebP format not enabled (missing optimization opportunity)', 'wpshadow' );
		}
		
		// Check 4: Lazy loading integration
		$lazy_load = get_option( 'uncode_adaptive_lazy_load', false );
		if ( ! $lazy_load ) {
			$issues[] = __( 'Adaptive images without lazy loading', 'wpshadow' );
		}
		
		// Check 5: Image processing queue
		$pending_images = get_option( 'uncode_adaptive_queue', array() );
		if ( is_array( $pending_images ) && count( $pending_images ) > 50 ) {
			$issues[] = sprintf( __( '%d images in processing queue (background generation slow)', 'wpshadow' ), count( $pending_images ) );
		}
		
		// Check 6: Storage optimization
		$quality = get_option( 'uncode_adaptive_quality', 85 );
		if ( $quality > 90 ) {
			$issues[] = sprintf( __( 'Image quality: %d%% (reduce for better performance)', 'wpshadow' ), $quality );
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
				__( 'Uncode adaptive images have %d optimization issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => true,
			'kb_link'     => 'https://wpshadow.com/kb/uncode-theme-adaptive-images',
		);
	}
}
