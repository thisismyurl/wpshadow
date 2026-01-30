<?php
/**
 * Enfold Theme Google Fonts Loading Diagnostic
 *
 * Enfold Theme Google Fonts Loading needs optimization.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1311.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enfold Theme Google Fonts Loading Diagnostic Class
 *
 * @since 1.1311.0000
 */
class Diagnostic_EnfoldThemeGoogleFontsLoading extends Diagnostic_Base {

	protected static $slug = 'enfold-theme-google-fonts-loading';
	protected static $title = 'Enfold Theme Google Fonts Loading';
	protected static $description = 'Enfold Theme Google Fonts Loading needs optimization';
	protected static $family = 'performance';

	public static function check() {
		// Check for Enfold theme
		$theme = wp_get_theme();
		$is_enfold = ( 'Enfold' === $theme->name || 'Enfold' === $theme->parent_theme );

		if ( ! $is_enfold ) {
			return null;
		}

		$issues = array();

		// Check 1: Google Fonts enabled
		$google_fonts = get_option( 'avia_google_fonts', 'enabled' );
		if ( 'enabled' === $google_fonts ) {
			$issues[] = __( 'Google Fonts loading externally (GDPR, performance)', 'wpshadow' );
		}

		// Check 2: Font count
		$font_families = get_option( 'avia_custom_fonts', array() );
		if ( is_array( $font_families ) && count( $font_families ) > 5 ) {
			$issues[] = sprintf( __( '%d font families (slow page load)', 'wpshadow' ), count( $font_families ) );
		}

		// Check 3: Font display
		$font_display = get_option( 'avia_font_display', 'auto' );
		if ( 'swap' !== $font_display ) {
			$issues[] = __( 'Font display not set to swap (render blocking)', 'wpshadow' );
		}

		// Check 4: Font preloading
		$preload_fonts = get_option( 'avia_preload_fonts', 'no' );
		if ( 'no' === $preload_fonts ) {
			$issues[] = __( 'Fonts not preloaded (flash of unstyled text)', 'wpshadow' );
		}

		// Check 5: Subsetting
		$font_subsetting = get_option( 'avia_font_subsetting', 'no' );
		if ( 'no' === $font_subsetting ) {
			$issues[] = __( 'No font subsetting (larger font files)', 'wpshadow' );
		}

		// Check 6: Local hosting
		$local_fonts = get_option( 'avia_local_fonts', 'no' );
		if ( 'no' === $local_fonts ) {
			$issues[] = __( 'Fonts not hosted locally (external dependencies)', 'wpshadow' );
		}

		if ( empty( $issues ) ) {
			return null;
		}

		$threat_level = 55;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 67;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 61;
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				__( 'Enfold Google Fonts has %d performance issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/enfold-theme-google-fonts-loading',
		);
	}
}
