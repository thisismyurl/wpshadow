<?php
/**
 * Divi Builder Google Fonts Diagnostic
 *
 * Divi loading too many font weights.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.353.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Divi Builder Google Fonts Diagnostic Class
 *
 * @since 1.353.0000
 */
class Diagnostic_DiviBuilderGoogleFonts extends Diagnostic_Base {

	protected static $slug = 'divi-builder-google-fonts';
	protected static $title = 'Divi Builder Google Fonts';
	protected static $description = 'Divi loading too many font weights';
	protected static $family = 'performance';

	public static function check() {
		if ( ! function_exists( 'et_divi_fonts_url' ) ) {
			return null;
		}
		
		$issues = array();

		// Check 1: Verify font loading strategy
		$font_loading = get_option( 'et_divi_font_loading_strategy', '' );
		if ( 'swap' !== $font_loading && 'async' !== $font_loading ) {
			$issues[] = __( 'Font loading strategy not optimized', 'wpshadow' );
		}

		// Check 2: Check font weight limits
		$font_weights = get_option( 'et_divi_google_font_weights', array() );
		if ( count( $font_weights ) > 4 ) {
			$issues[] = __( 'Too many font weights loaded', 'wpshadow' );
		}

		// Check 3: Verify font-display property
		$font_display = get_option( 'et_divi_font_display', '' );
		if ( 'swap' !== $font_display ) {
			$issues[] = __( 'Font-display not set to swap', 'wpshadow' );
		}

		// Check 4: Check local font hosting option
		$local_fonts = get_option( 'et_divi_local_google_fonts', '' );
		if ( 'on' !== $local_fonts ) {
			$issues[] = __( 'Local Google Fonts hosting not enabled', 'wpshadow' );
		}

		// Check 5: Verify font subsetting
		$font_subset = get_option( 'et_divi_font_character_set', 'latin' );
		if ( empty( $font_subset ) ) {
			$issues[] = __( 'Font subsetting not configured', 'wpshadow' );
		}

		// Check 6: Check font preload configuration
		$preload_fonts = get_option( 'et_divi_preload_fonts', '' );
		if ( 'on' !== $preload_fonts ) {
			$issues[] = __( 'Font preloading not enabled', 'wpshadow' );
		}
		// Check transient support
		if ( ! function_exists( 'set_transient' ) ) {
			$issues[] = __( 'Transient functions unavailable', 'wpshadow' );
		}
		return null;
	}
}
