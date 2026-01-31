<?php
/**
 * Beaver Builder Responsive Settings Diagnostic
 *
 * Beaver Builder mobile settings missing.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.343.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Beaver Builder Responsive Settings Diagnostic Class
 *
 * @since 1.343.0000
 */
class Diagnostic_BeaverBuilderResponsiveSettings extends Diagnostic_Base {

	protected static $slug = 'beaver-builder-responsive-settings';
	protected static $title = 'Beaver Builder Responsive Settings';
	protected static $description = 'Beaver Builder mobile settings missing';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! class_exists( 'FLBuilder' ) ) {
			return null;
		}
		
		$issues = array();

		// Check 1: Verify responsive editing is enabled
		$responsive_enabled = get_option( 'fl_builder_responsive_enabled', '' );
		if ( 'enabled' !== $responsive_enabled ) {
			$issues[] = __( 'Responsive editing not enabled', 'wpshadow' );
		}

		// Check 2: Check responsive breakpoints configuration
		$medium_breakpoint = get_option( '_fl_builder_medium_breakpoint', 0 );
		$mobile_breakpoint = get_option( '_fl_builder_mobile_breakpoint', 0 );
		if ( $medium_breakpoint === 0 || $mobile_breakpoint === 0 ) {
			$issues[] = __( 'Responsive breakpoints not configured', 'wpshadow' );
		}

		// Check 3: Verify mobile preview enabled
		$mobile_preview = get_option( 'fl_builder_mobile_preview', false );
		if ( ! $mobile_preview ) {
			$issues[] = __( 'Mobile preview not enabled in editor', 'wpshadow' );
		}

		// Check 4: Check device-specific settings enabled
		$device_settings = get_option( 'fl_builder_device_specific_settings', false );
		if ( ! $device_settings ) {
			$issues[] = __( 'Device-specific settings not enabled', 'wpshadow' );
		}

		// Check 5: Verify responsive images enabled
		$responsive_images = get_option( 'fl_builder_responsive_images', '' );
		if ( 'enabled' !== $responsive_images ) {
			$issues[] = __( 'Responsive images not enabled', 'wpshadow' );
		}

		// Check 6: Check mobile optimization mode
		$mobile_optimization = get_option( 'fl_builder_mobile_optimization', false );
		if ( ! $mobile_optimization ) {
			$issues[] = __( 'Mobile optimization mode not enabled', 'wpshadow' );
		}
		// Verify core functionality
		if ( ! function_exists( 'get_post' ) ) {
			$issues[] = __( 'Post functionality not available', 'wpshadow' );
		}
		return null;
	}
}
