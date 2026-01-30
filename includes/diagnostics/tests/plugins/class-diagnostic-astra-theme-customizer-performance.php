<?php
/**
 * Astra Theme Customizer Performance Diagnostic
 *
 * Astra Theme Customizer Performance needs optimization.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1291.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Astra Theme Customizer Performance Diagnostic Class
 *
 * @since 1.1291.0000
 */
class Diagnostic_AstraThemeCustomizerPerformance extends Diagnostic_Base {

	protected static $slug = 'astra-theme-customizer-performance';
	protected static $title = 'Astra Theme Customizer Performance';
	protected static $description = 'Astra Theme Customizer Performance needs optimization';
	protected static $family = 'performance';

	public static function check() {
		// Check for Astra theme
		if ( get_template() !== 'astra' && ! class_exists( 'Astra_Theme_Options' ) ) {
			return null;
		}

		global $wpdb;
		$issues = array();

		// Check 1: Customizer option count
		$theme_mods = get_theme_mods();
		if ( count( $theme_mods ) > 200 ) {
			$issues[] = sprintf( __( '%d theme mods (slow customizer)', 'wpshadow' ), count( $theme_mods ) );
		}

		// Check 2: Dynamic CSS caching
		$cache_css = get_option( 'astra_cache_dynamic_css', 'no' );
		if ( 'no' === $cache_css ) {
			$issues[] = __( 'Dynamic CSS not cached (repeated generation)', 'wpshadow' );
		}

		// Check 3: Google Fonts loading
		$preload_fonts = get_option( 'astra_preload_fonts', 'no' );
		if ( 'no' === $preload_fonts ) {
			$issues[] = __( 'Fonts not preloaded (render blocking)', 'wpshadow' );
		}

		// Check 4: Font display strategy
		$font_display = get_option( 'astra_font_display', 'auto' );
		if ( 'auto' === $font_display ) {
			$issues[] = __( 'Font display auto (invisible text)', 'wpshadow' );
		}

		// Check 5: Asset generation
		$generate_assets = get_option( 'astra_generate_assets', 'yes' );
		if ( 'no' === $generate_assets ) {
			$issues[] = __( 'Assets not generated (inline styles)', 'wpshadow' );
		}

		// Check 6: Legacy mode
		$legacy_mode = get_option( 'astra_legacy_typography', 'yes' );
		if ( 'yes' === $legacy_mode ) {
			$issues[] = __( 'Legacy mode enabled (extra CSS)', 'wpshadow' );
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
				/* translators: %s: list of Astra customizer performance issues */
				__( 'Astra customizer has %d performance issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/astra-theme-customizer-performance',
		);
	}
}
