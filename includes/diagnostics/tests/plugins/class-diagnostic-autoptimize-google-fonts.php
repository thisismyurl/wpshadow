<?php
/**
 * Autoptimize Google Fonts Diagnostic
 *
 * Autoptimize Google Fonts not optimized.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.916.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Autoptimize Google Fonts Diagnostic Class
 *
 * @since 1.916.0000
 */
class Diagnostic_AutoptimizeGoogleFonts extends Diagnostic_Base {

	protected static $slug = 'autoptimize-google-fonts';
	protected static $title = 'Autoptimize Google Fonts';
	protected static $description = 'Autoptimize Google Fonts not optimized';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'AUTOPTIMIZE_PLUGIN_VERSION' ) ) {
			return null;
		}

		$issues = array();

		// Check if Google Fonts optimization is enabled
		$fonts_enabled = get_option( 'autoptimize_optimize_fonts', '0' );
		if ( '0' === $fonts_enabled ) {
			$issues[] = 'Google Fonts optimization disabled';
		}

		// Check for local font hosting
		$local_fonts = get_option( 'autoptimize_font_local_hosting', '0' );
		if ( '0' === $local_fonts && '1' === $fonts_enabled ) {
			$issues[] = 'fonts loaded from Google CDN (not locally cached)';
		}

		// Check font display strategy
		$font_display = get_option( 'autoptimize_font_display', '' );
		if ( empty( $font_display ) || 'auto' === $font_display ) {
			$issues[] = 'font display strategy not optimized (use swap or optional)';
		}

		// Check for preconnect to fonts.gstatic.com
		$preconnect = get_option( 'autoptimize_font_preconnect', '0' );
		if ( '0' === $preconnect && '0' === $local_fonts ) {
			$issues[] = 'no preconnect to Google Fonts domains (slower font loading)';
		}

		// Check for font subsetting
		$subset = get_option( 'autoptimize_font_subset', '' );
		if ( empty( $subset ) && '1' === $fonts_enabled ) {
			$issues[] = 'font subsetting not configured (loading unused characters)';
		}

		// Check cache directory for stored fonts
		if ( '1' === $local_fonts ) {
			$cache_dir = WP_CONTENT_DIR . '/cache/autoptimize/fonts/';
			if ( ! is_dir( $cache_dir ) || ! is_writable( $cache_dir ) ) {
				$issues[] = 'font cache directory missing or not writable';
			}
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 65, 35 + ( count( $issues ) * 6 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'Autoptimize Google Fonts optimization issues: ' . implode( ', ', $issues ),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/autoptimize-google-fonts',
			);
		}

		return null;
	}
}
