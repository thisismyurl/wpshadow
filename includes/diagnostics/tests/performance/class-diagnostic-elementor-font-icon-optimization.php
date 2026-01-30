<?php
/**
 * Elementor Font and Icon Library Optimization Diagnostic
 *
 * Ensure font and icon libraries not bloating page weight.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Performance
 * @since      1.6030.1245
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Elementor Font and Icon Optimization Diagnostic Class
 *
 * @since 1.6030.1245
 */
class Diagnostic_ElementorFontIconOptimization extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'elementor-font-icon-optimization';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Elementor Font and Icon Library Optimization';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Ensure font and icon libraries not bloating page weight';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6030.1245
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if Elementor is active
		if ( ! defined( 'ELEMENTOR_VERSION' ) && ! class_exists( '\Elementor\Plugin' ) ) {
			return null;
		}

		$issues = array();
		global $wpdb;

		// Check 1: Check Font Awesome loaded globally
		$load_fa_globally = get_option( 'elementor_load_fa4_shim', 'yes' );

		if ( 'yes' === $load_fa_globally ) {
			$issues[] = 'Font Awesome loaded globally (should be per-page)';
		}

		// Check 2: Verify Google Fonts optimization enabled
		$google_fonts = get_option( 'elementor_google_fonts', 'yes' );
		$font_display = get_option( 'elementor_font_display', 'auto' );

		if ( 'yes' === $google_fonts ) {
			// Check for font usage
			$google_fonts_count = $wpdb->get_var(
				"SELECT COUNT(*)
				FROM {$wpdb->postmeta}
				WHERE meta_key = '_elementor_data'
				AND meta_value LIKE '%google%fonts%'"
			);

			if ( $google_fonts_count > 50 ) {
				$issues[] = sprintf( '%d pages using Google Fonts (consider local hosting)', $google_fonts_count );
			}
		}

		if ( 'swap' !== $font_display && 'fallback' !== $font_display ) {
			$issues[] = 'font-display not optimized (should use swap or fallback)';
		}

		// Check 3: Test for unused icon libraries loaded
		$icon_libraries = array( 'fontawesome', 'eicons', 'fa-regular', 'fa-solid', 'fa-brands' );
		$used_icons = array();

		foreach ( $icon_libraries as $library ) {
			$count = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*)
					FROM {$wpdb->postmeta}
					WHERE meta_key = '_elementor_data'
					AND meta_value LIKE %s",
					'%' . $wpdb->esc_like( $library ) . '%'
				)
			);

			if ( $count > 0 ) {
				$used_icons[ $library ] = $count;
			}
		}

		if ( count( $used_icons ) > 2 ) {
			$issues[] = sprintf( '%d different icon libraries loaded', count( $used_icons ) );
		}

		// Check 4: Check for excessive font variations loaded
		$font_weights = array( '100', '200', '300', '400', '500', '600', '700', '800', '900' );
		$loaded_weights = 0;

		foreach ( $font_weights as $weight ) {
			$count = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*)
					FROM {$wpdb->postmeta}
					WHERE meta_key = '_elementor_data'
					AND meta_value LIKE %s",
					'%"font_weight":"' . $weight . '"%'
				)
			);

			if ( $count > 0 ) {
				$loaded_weights++;
			}
		}

		if ( $loaded_weights > 4 ) {
			$issues[] = sprintf( '%d font weights used (limit to 3-4 for performance)', $loaded_weights );
		}

		// Check 5: Verify custom fonts uploaded
		$custom_fonts = get_option( 'elementor_custom_fonts', array() );
		$using_google_fonts = 'yes' === $google_fonts;

		if ( empty( $custom_fonts ) && $using_google_fonts ) {
			$issues[] = 'using external fonts instead of custom uploaded fonts';
		}

		// Check 6: Verify no loading entire Font Awesome for 2 icons
		$icon_usage_count = $wpdb->get_var(
			"SELECT COUNT(*)
			FROM {$wpdb->postmeta}
			WHERE meta_key = '_elementor_data'
			AND meta_value LIKE '%\"icon\":%'"
		);

		if ( $icon_usage_count < 10 && 'yes' === $load_fa_globally ) {
			$issues[] = sprintf( 'loading entire Font Awesome library for only %d icons', $icon_usage_count );
		}

		// Return finding if issues exist
		if ( ! empty( $issues ) ) {
			$threat_level = min( 85, 50 + ( count( $issues ) * 6 ) );

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'Elementor font/icon optimization issues: ' . implode( ', ', $issues ),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/elementor-font-icon-optimization',
			);
		}

		return null;
	}
}
