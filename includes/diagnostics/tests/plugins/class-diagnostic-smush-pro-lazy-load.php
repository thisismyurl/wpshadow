<?php
/**
 * Smush Pro Lazy Load Diagnostic
 *
 * Smush Pro Lazy Load detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.758.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Smush Pro Lazy Load Diagnostic Class
 *
 * @since 1.758.0000
 */
class Diagnostic_SmushProLazyLoad extends Diagnostic_Base {

	protected static $slug = 'smush-pro-lazy-load';
	protected static $title = 'Smush Pro Lazy Load';
	protected static $description = 'Smush Pro Lazy Load detected';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'WP_SMUSH_VERSION' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Lazy load enabled
		$lazy_enabled = get_option( 'wp_smush_lazy_load', false );
		if ( ! $lazy_enabled ) {
			$issues[] = 'Lazy load not enabled';
		}
		
		// Check 2: Exclusions configured
		$exclusions = get_option( 'wp_smush_lazy_load_exclusions', array() );
		if ( empty( $exclusions ) ) {
			$issues[] = 'No lazy load exclusions configured';
		}
		
		// Check 3: Native lazy load enabled
		$native_lazy = get_option( 'wp_smush_native_lazy_load', false );
		if ( ! $native_lazy ) {
			$issues[] = 'Native lazy load disabled';
		}
		
		// Check 4: Placeholder images
		$placeholders = get_option( 'wp_smush_lazy_load_placeholders', false );
		if ( ! $placeholders ) {
			$issues[] = 'Placeholder images disabled';
		}
		
		// Check 5: Fade-in effect enabled
		$fade_in = get_option( 'wp_smush_lazy_load_fadein', false );
		if ( ! $fade_in ) {
			$issues[] = 'Fade-in effect disabled';
		}
		
		// Check 6: Viewport threshold configured
		$threshold = get_option( 'wp_smush_lazy_load_threshold', 0 );
		if ( $threshold <= 0 ) {
			$issues[] = 'Viewport threshold not configured';
		}
		
		if ( ! empty( $issues ) ) {
			$threat_level = min( 65, 35 + ( count( $issues ) * 5 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'Smush Pro lazy load issues: ' . implode( ', ', $issues ),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/smush-pro-lazy-load',
			);
		}
		

		// Feature availability checks
		if ( ! function_exists( 'add_action' ) ) {
			$issues[] = __( 'WordPress hooks unavailable', 'wpshadow' );
		}
		if ( empty( $GLOBALS['wpdb'] ) ) {
			$issues[] = __( 'Database not initialized', 'wpshadow' );
		}
		// Verify core functionality
		if ( ! function_exists( 'get_post' ) ) {
			$issues[] = __( 'Post functionality not available', 'wpshadow' );
		}
		return null;
	}
}
