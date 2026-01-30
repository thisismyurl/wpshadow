<?php
/**
 * Elementor Pro Custom Fonts Loading Diagnostic
 *
 * Elementor Pro Custom Fonts Loading issues found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.794.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Elementor Pro Custom Fonts Loading Diagnostic Class
 *
 * @since 1.794.0000
 */
class Diagnostic_ElementorProCustomFontsLoading extends Diagnostic_Base {

	protected static $slug = 'elementor-pro-custom-fonts-loading';
	protected static $title = 'Elementor Pro Custom Fonts Loading';
	protected static $description = 'Elementor Pro Custom Fonts Loading issues found';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'ELEMENTOR_VERSION' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Verify custom fonts enabled
		$custom_fonts = get_option( 'elementor_custom_fonts', array() );
		if ( empty( $custom_fonts ) ) {
			$issues[] = 'No custom fonts configured';
		}
		
		// Check 2: Check for font display swap
		$font_display = get_option( 'elementor_font_display', '' );
		if ( 'swap' !== $font_display ) {
			$issues[] = 'Font display not set to swap';
		}
		
		// Check 3: Verify font preload
		$font_preload = get_option( 'elementor_font_preload', 0 );
		if ( ! $font_preload ) {
			$issues[] = 'Font preload not enabled';
		}
		
		// Check 4: Check for too many font files
		$font_count = is_array( $custom_fonts ) ? count( $custom_fonts ) : 0;
		if ( $font_count > 6 ) {
			$issues[] = 'Too many custom fonts configured (over 6)';
		}
		
		// Check 5: Verify Google Fonts optimization
		$google_fonts = get_option( 'elementor_google_fonts', 1 );
		if ( $google_fonts ) {
			$issues[] = 'Google Fonts still enabled (may impact performance)';
		}
		
		// Check 6: Check for inline CSS fonts
		$inline_fonts = get_option( 'elementor_inline_fonts', 0 );
		if ( ! $inline_fonts ) {
			$issues[] = 'Inline font CSS not enabled';
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
					'Found %d Elementor Pro custom font issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/elementor-pro-custom-fonts-loading',
			);
		}
		
		return null;
	}
}
