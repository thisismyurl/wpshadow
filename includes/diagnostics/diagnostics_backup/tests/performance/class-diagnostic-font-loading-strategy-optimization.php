<?php
/**
 * Font Loading Strategy Optimization Diagnostic
 *
 * Validates fonts use optimal loading strategy to prevent invisible text.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26029.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Font Loading Strategy Optimization Class
 *
 * Tests font loading.
 *
 * @since 1.26029.0000
 */
class Diagnostic_Font_Loading_Strategy_Optimization extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'font-loading-strategy-optimization';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Font Loading Strategy Optimization';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates fonts use optimal loading strategy to prevent invisible text';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26029.0000
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$font_check = self::check_font_loading();
		
		if ( $font_check['has_issues'] ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( ' ', $font_check['issues'] ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/font-loading-strategy-optimization',
				'meta'         => array(
					'google_fonts_detected' => $font_check['google_fonts_detected'],
					'font_display_swap'     => $font_check['font_display_swap'],
					'font_preload'          => $font_check['font_preload'],
					'recommendations'       => $font_check['recommendations'],
				),
			);
		}

		return null;
	}

	/**
	 * Check font loading strategy.
	 *
	 * @since  1.26029.0000
	 * @return array Check results.
	 */
	private static function check_font_loading() {
		global $wp_styles;

		$check = array(
			'has_issues'           => false,
			'issues'               => array(),
			'google_fonts_detected' => false,
			'font_display_swap'    => false,
			'font_preload'         => false,
			'recommendations'      => array(),
		);

		// Check for Google Fonts in enqueued styles.
		if ( ! empty( $wp_styles->queue ) ) {
			foreach ( $wp_styles->queue as $handle ) {
				if ( ! isset( $wp_styles->registered[ $handle ] ) ) {
					continue;
				}

				$style = $wp_styles->registered[ $handle ];

				if ( ! empty( $style->src ) && false !== strpos( $style->src, 'fonts.googleapis.com' ) ) {
					$check['google_fonts_detected'] = true;

					// Check if display=swap is used.
					if ( false !== strpos( $style->src, 'display=swap' ) ) {
						$check['font_display_swap'] = true;
					}
				}
			}
		}

		// Check theme's style.css for @font-face declarations.
		$stylesheet_path = get_stylesheet_directory() . '/style.css';
		
		if ( file_exists( $stylesheet_path ) ) {
			$stylesheet_content = file_get_contents( $stylesheet_path );

			// Check for @font-face without font-display.
			if ( false !== strpos( $stylesheet_content, '@font-face' ) ) {
				if ( false === strpos( $stylesheet_content, 'font-display' ) ) {
					$check['has_issues'] = true;
					$check['issues'][] = __( '@font-face declarations found without font-display property (causes FOIT)', 'wpshadow' );
					$check['recommendations'][] = __( 'Add font-display: swap to all @font-face rules', 'wpshadow' );
				}
			}
		}

		// Detect issues with Google Fonts.
		if ( $check['google_fonts_detected'] && ! $check['font_display_swap'] ) {
			$check['has_issues'] = true;
			$check['issues'][] = __( 'Google Fonts loaded without display=swap (causes invisible text during load)', 'wpshadow' );
			$check['recommendations'][] = __( 'Add &display=swap to Google Fonts URL', 'wpshadow' );
		}

		// Check for font preloading in <head>.
		$homepage_id = (int) get_option( 'page_on_front' );
		
		if ( $homepage_id > 0 || is_front_page() ) {
			// Can't easily check <head> from here, but we can check if preload filter exists.
			if ( ! has_filter( 'wp_resource_hints' ) ) {
				$check['has_issues'] = true;
				$check['issues'][] = __( 'No font preloading detected (delays text rendering)', 'wpshadow' );
				$check['recommendations'][] = __( 'Preload critical fonts using <link rel="preload">', 'wpshadow' );
			}
		}

		return $check;
	}
}
