<?php
/**
 * Optimole Lazy Load Configuration Diagnostic
 *
 * Optimole Lazy Load Configuration detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.764.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Optimole Lazy Load Configuration Diagnostic Class
 *
 * @since 1.764.0000
 */
class Diagnostic_OptimoleLazyLoadConfiguration extends Diagnostic_Base {

	protected static $slug = 'optimole-lazy-load-configuration';
	protected static $title = 'Optimole Lazy Load Configuration';
	protected static $description = 'Optimole Lazy Load Configuration detected';
	protected static $family = 'functionality';

	public static function check() {
		// Check for Optimole plugin
		$has_optimole = defined( 'OPTML_VERSION' ) ||
		                class_exists( 'Optml_Main' ) ||
		                get_option( 'optml_api_key', '' ) !== '';
		
		if ( ! $has_optimole ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Lazy load enabled
		$lazy_load = get_option( 'optml_lazy_load', 'yes' );
		if ( 'no' === $lazy_load ) {
			return null; // Feature not in use
		}
		
		// Check 2: Placeholder type
		$placeholder = get_option( 'optml_lazy_placeholder', 'blur' );
		if ( 'none' === $placeholder ) {
			$issues[] = __( 'No placeholder (layout shift)', 'wpshadow' );
		}
		
		// Check 3: Threshold
		$threshold = get_option( 'optml_lazy_threshold', 300 );
		if ( $threshold < 100 ) {
			$issues[] = __( 'Low threshold (images load too early)', 'wpshadow' );
		}
		
		// Check 4: Exclusion rules
		$exclusions = get_option( 'optml_lazy_exclusions', array() );
		if ( empty( $exclusions ) ) {
			$issues[] = __( 'No exclusions (critical images lazy-loaded)', 'wpshadow' );
		}
		
		// Check 5: Background images
		$bg_images = get_option( 'optml_lazy_bg_images', 'yes' );
		if ( 'no' === $bg_images ) {
			$issues[] = __( 'Background images not lazy-loaded', 'wpshadow' );
		}
		
		// Check 6: Video lazy load
		$lazy_video = get_option( 'optml_lazy_video', 'no' );
		if ( 'no' === $lazy_video ) {
			$issues[] = __( 'Videos not lazy-loaded (large files)', 'wpshadow' );
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
				/* translators: %s: list of Optimole lazy load configuration issues */
				__( 'Optimole lazy load has %d configuration issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/optimole-lazy-load-configuration',
		);
	}
}
