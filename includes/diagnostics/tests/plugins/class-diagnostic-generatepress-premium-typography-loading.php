<?php
/**
 * Generatepress Premium Typography Loading Diagnostic
 *
 * Generatepress Premium Typography Loading needs optimization.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1298.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Generatepress Premium Typography Loading Diagnostic Class
 *
 * @since 1.1298.0000
 */
class Diagnostic_GeneratepressPremiumTypographyLoading extends Diagnostic_Base {

	protected static $slug = 'generatepress-premium-typography-loading';
	protected static $title = 'Generatepress Premium Typography Loading';
	protected static $description = 'Generatepress Premium Typography Loading needs optimization';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'GP_PREMIUM_VERSION' ) && ! function_exists( 'generate_get_option' ) ) {
			return null;
		}

		$issues = array();
		$settings = get_option( 'generate_settings', array() );

		// Check 1: Verify typography module enabled
		$typography = isset( $settings['typography'] ) ? (bool) $settings['typography'] : false;
		if ( ! $typography ) {
			$issues[] = 'Typography module not enabled';
		}

		// Check 2: Check for Google Fonts usage
		$google_fonts = isset( $settings['google_font'] ) ? (bool) $settings['google_font'] : false;
		if ( $google_fonts ) {
			$issues[] = 'Google Fonts still enabled (performance impact)';
		}

		// Check 3: Verify font display swap
		$font_display = isset( $settings['font_display'] ) ? $settings['font_display'] : '';
		if ( 'swap' !== $font_display ) {
			$issues[] = 'Font display not set to swap';
		}

		// Check 4: Check for local font hosting
		$local_fonts = get_option( 'generate_local_fonts', 0 );
		if ( ! $local_fonts ) {
			$issues[] = 'Local font hosting not enabled';
		}

		// Check 5: Verify subset loading
		$subsets = get_option( 'generate_font_subsets', '' );
		if ( empty( $subsets ) ) {
			$issues[] = 'Font subset loading not configured';
		}

		// Check 6: Check for preloading
		$preload_fonts = get_option( 'generate_preload_fonts', 0 );
		if ( ! $preload_fonts ) {
			$issues[] = 'Font preloading not enabled';
		}

		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 45;
			$threat_multiplier = 6;
			$max_threat = 75;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d GeneratePress typography issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/generatepress-premium-typography-loading',
			);
		}

		return null;
	}
}
